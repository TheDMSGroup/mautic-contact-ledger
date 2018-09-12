<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Command;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\CoreBundle\Helper\CacheStorageHelper;
use MauticPlugin\MauticContactLedgerBundle\Entity\CampaignSourceStats;
use MauticPlugin\MauticContactLedgerBundle\Event\ReportStatsGeneratorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SourceStatsCommand extends ModeratedCommand implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var \DateTime
     */
    protected $dateContext;

    /**
     * @var \DateTime
     */
    protected $dateLimit;

    /**
     * @var CacheStorageHelper
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $withCache;

    /**
     * @var string
     */
    protected $expire;

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('mautic:ledger:source:stats')
            ->addOption(
                '--date-limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'DateTime string to stop processing. Ex -90 days, 2018-04-01 Default = -90 days',
                null
            )
            ->addOption(
                '--with-cache',
                null,
                InputOption::VALUE_OPTIONAL,
                'true or false (0 or 1). Defaults to true. Run from last cached date or ignore (reset) cache.',
                null
            )
            ->setDescription(
                'generate stats in distinct table for reporting purposes'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->expire     = empty($input->getOption('date-limit')) ? '-90 days' : $input->getOption(
            'date-limit'
        ); // change this as needed.
        $this->withCache  = empty($input->getOption('with-cache')) ? true : $input->getOption(
            'with-cache'
        );
        $this->container  = $this->getContainer();
        $this->dispatcher = $this->container->get('event_dispatcher');
        $this->em         = $this->container->get('doctrine.orm.entity_manager');
        $this->cache      = new CacheStorageHelper(
            'db',
            'SourceStats',
            $this->container->get('doctrine.dbal.default_connection')
        );
        $this->dateLimit  = new \DateTime(date('Y-m-d H:i:s', strtotime($this->expire)));

        $now = new \DateTime();
        $now->sub(new \DateInterval('PT15M'));

        if ($this->dateLimit >= $now) {
            $output->writeln(
                '<error>date-limit option must resolve to a date/time earlier than now minus 15 minutes</error>'
            );

            return 0;
        }

        // this gets passed into the final query so it can be cached. It can be large
        $params = [
            'cacheDir' => $this->container->getParameter('kernel.cache_dir'),
        ];
        $this->setDateContext();
        $repeat = true;

        $output->writeln('<info>***** Generating Client Report Stats *****</info>');
        $timeStart = microtime(true);

        if (!$this->checkRunStatus($input, $output)) {
            $output->writeln('<error>Something failed in CheckRunStatus.</error>');

            return 0;
        }

        do {
            $context = 'CampaignSourceStats';
            $params  = $this->getDateParams($params);
            $output->writeln(
                "<comment>--> Using Parameters:\n \tWith Cache => ".$this->withCache.", \n \tExpire Term => ".$this->expire.", \n \tDate => ".$params['dateFrom'].' and '.$params['dateTo']." UTC,\n \tQuery Cache Directory => ".$params['cacheDir'].'</comment>'
            );

            // Does a record exist for date range?
            /** @var CampaignSourceStatsRepository */
            $repo             = $this->em->getRepository('MauticContactLedgerBundle:CampaignSourceStats');
            $existingEntities = $repo->getExistingEntitiesByDate($params);

            if (empty($existingEntities)) {
                // Dispatch event and process result to report stats table
                $event = new ReportStatsGeneratorEvent($this->em, $params, $context);
                $this->dispatcher->dispatch('mautic.contactledger.sourcestats.generate', $event);

                // save entities to DB
                foreach ($event->getStatsCollection() as $subscriber) {
                    $output->writeln(
                        '<info>--> Pesisting data for '.$context.' using date '.$this->dateContext->format(
                            'Y-m-d H:i:s'
                        ).'.</info>'
                    );
                    if (isset($subscriber[$context]) && !empty($subscriber[$context])) {
                        foreach ($subscriber[$context] as $stat) {
                            $entity = $this->mapArrayToEntity($stat, $context, $this->dateContext);
                            $this->em->persist($entity);
                        }
                    }
                    $this->em->flush();
                }
            } else {
                $output->writeln('<comment>--> Data already Exists: '.$this->dateContext->format('Y-m-d H:i:s').'.</comment>');

                if ($this->withCache) {
                    // we have processed this time block already, so jump to last cached time block
                    // if there is no cached value, just proceed using current DateTime context
                    $lastTimeBlock = $this->cache->get('SourceStats') instanceof \DateTime ? $this->cache->get(
                        'SourceStats'
                    ) : $this->dateContext;
                    $this->setDateContext($lastTimeBlock);
                }
            }
            $lapTime     = microtime(true);
            $elapsedTime = $lapTime - $timeStart;
            $output->writeln('<comment>--> Elapsed time so far: '.$elapsedTime.'.</comment>');
            if ($this->dateContext <= $this->dateLimit || $elapsedTime >= 250) { // max out after 4 mins 50 seconds and let next cron kick off. based on 5 min cron run
                // stop looping
                $repeat = false;
            } else {
                $dateShift = $this->dateContext->sub(new \DateInterval('PT5M'));
                $this->setDateContext($dateShift);
            }
        } while (true == $repeat);
        // cache last date processed
        $this->cache->set('SourceStats', $this->dateContext, null);
        $output->writeln(
            '<info>Last Date Cache value written to DB: '.$this->dateContext->format('Y-m-d H:i:s').'.</info>'
        );
        $this->completeRun();

        $output->writeln('<info>Complete With No Errors.</info>');
        $timeEnd     = microtime(true);
        $elapsedTime = $timeEnd - $timeStart;
        $output->writeln("<info>Total Execution Time: $elapsedTime.</info>");

        return 0;
    }

    /**
     * This becomes the dateAdded value to use in report stats table
     * This will be the default To date,  which a From date is calculated by subtracting 4 mins 59s.
     *
     * @param \DateTime|null $dateContext
     *
     * @throws \Exception
     */
    private function setDateContext(\DateTime $dateContext = null)
    {
        if (!$dateContext) { // we havent set context so we need the default to be: now minus 15 mins, rounded to 5 min increment.
            $now = new \DateTime();
            $now->sub(new \DateInterval('PT15M'));
            // round down to 5 minute increment
            $now->setTime($now->format('H'), floor($now->format('i') / 5) * 5, 0);
            $dateContext = $now;
        }
        $this->dateContext = $dateContext;
    }

    /**
     * Get the last Date record and add 1 sec for From and 5 mins for To.
     *
     * @param string
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getDateParams($params)
    {
        /**
         * @var \DateTime
         */
        $to = $this->dateContext;
        /**
         * @var \DateTime
         */
        $from = clone $to;
        $from->sub(new \DateInterval('PT4M'));
        $from->sub(new \DateInterval('PT59S'));

        $params['dateFrom'] = $from->format('Y-m-d H:i:s');

        $params['dateTo'] = $to->format('Y-m-d H:i:s');

        return $params;
    }

    /**
     * @param $stat
     * @param $context
     *
     * @return CampaignSourceStats
     */
    private function mapArrayToEntity($stat, $context, $dateTo)
    {
        $fieldsMap = [
            'CampaignSourceStats'   => [
                'campaignId'      => 'campaign_id',
                'received'        => 'received',
                'declined'        => 'rejected',
                'converted'       => 'converted',
                'scrubbed'        => 'scrubbed',
                'cost'            => 'cost',
                'revenue'         => 'revenue',
                'contactSourceId' => 'contactsource_id',
                'grossIncome'     => 'gross_income',
                'margin'          => 'gross_margin',
                'ecpm'            => 'ecpm',
                'utmSource'       => 'utm_source',
            ],
            'CampaignSourceBudgets' => [
            ],
        ];

        $class  = '\MauticPlugin\MauticContactLedgerBundle\Entity\\'.$context;
        $entity = new $class();
        foreach ($fieldsMap[$context] as $entityParam => $statKey) {
            if (null == $stat[$statKey]) {
                $stat[$statKey] = '';
            }
            $entity->__set($entityParam, $stat[$statKey]);
        }
        $entity->setDateAdded($dateTo);
        $entity->setReprocessFlag(false);

        return $entity;
    }
}
