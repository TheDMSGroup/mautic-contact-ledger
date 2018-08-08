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
use MauticPlugin\MauticContactLedgerBundle\Entity\CampaignSourceStats;
use MauticPlugin\MauticContactLedgerBundle\Event\ReportStatsGeneratorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ReportStatsCommand extends ModeratedCommand implements ContainerAwareInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('mautic:ledger:report:stats')
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
        $container        = $this->getContainer();
        $this->dispatcher = $container->get('event_dispatcher');
        $this->em         = $container->get('doctrine.orm.entity_manager');
        $params           = [
            'cacheDir' => $container->getParameter('kernel.cache_dir'),
        ];
        $repeat           = true;

        $output->writeln('<info>***** Generating Report Stats *****</info>');
        $timeStart = microtime(true);

        if (!$this->checkRunStatus($input, $output)) {
            $output->writeln('<error>Something failed in CheckRunStatus.</error>');

            return 0;
        }

        do {
            // TODO: right now, contexts are hardcoded. need to define a way to register context by bundle
            foreach (['CampaignSourceStats', 'CampaignSourceBudgets'] as $context) {
                $params = $this->getDateParams($params, $context);

                $output->writeln(
                    "<comment>--> Using Parameters:\n \tContext => ".$context.", \n \tDate => ".$params['dateFrom'].' and '.$params['dateTo']." UTC,\n \tQuery Cache Directory => ".$params['cacheDir'].'</comment>'
                );

                if (null == $params['dateFrom']) {
                    // How soon is now? less than 15 mins? Dont run.
                    $output->writeln(
                        '<comment>Exiting without Running Context. Report Cron caught up to current time. </comment>'
                    );
                } else {
                    if ('invalid' == $params['dateFrom']) {
                        // Class for context does not exist. Fail gracefully.
                        $output->writeln(
                            '<error>Exiting without Running Context. No class exists for context: '.$context.'. </error>'
                        );
                    } else {
                        // Dispatch event to get data from various bundles
                        $event = new ReportStatsGeneratorEvent($this->em, $params, $context);
                        $this->dispatcher->dispatch('mautic.contactledger.reportstats.generate', $event);

                        // save entities to DB
                        $updatedParams = $event->getParams();
                        $dateToLog     = $updatedParams['dateTo'];
                        foreach ($event->getStatsCollection() as $subscriber) {
                            $output->writeln(
                                '<info>--> Pesisting data for '.$context.' using date '.$dateToLog.'.</info>'
                            );
                            if (isset($subscriber[$context]) && !empty($subscriber[$context])) {
                                foreach ($subscriber[$context] as $stat) {
                                    $entity = $this->mapArrayToEntity($stat, $context, $dateToLog);
                                    $this->em->persist($entity);
                                }
                            } else {
                                $output->writeln(
                                    '<comment>--> No data for '.$context.' using date '.$dateToLog.'.</comment>');
                                $repeat = false;
                            }
                        }
                        $this->em->flush();
                        $timeContext = microtime(true);
                        $contextTime = $timeContext - $timeStart;
                        $output->writeln('<comment>--> Elapsed time so far: '.$contextTime.'.</comment>');
                    }
                }
            }
            $now          = new \DateTime();
            $latestParams = $event->getParams();
            $dateTo       = $latestParams['dateTo'];
            $lastPass     = new \DateTime($dateTo);
            $now->sub(new \DateInterval('PT15M'));
            if ($now <= $lastPass) {
                // stop looping
                $repeat = false;
            }
        } while (true == $repeat);

        $this->completeRun();

        $output->writeln('<info>Complete With No Errors.</info>');
        $timeEnd     = microtime(true);
        $elapsedTime = $timeEnd - $timeStart;
        $output->writeln("<info>Total Execution Time: $elapsedTime.</info>");

        return 0;
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
    private function getDateParams($params, $context)
    {
        // make sure class exists for $context
        if (class_exists('\MauticPlugin\MauticContactLedgerBundle\Entity\\'.$context)) {
            // first get oldest date from the table implied in context
            $repo = $this->em->getRepository('MauticContactLedgerBundle:'.$context);
            if (empty($lastEntity = $repo->getLastEntity())) {
                // this should only ever happen once, the very first cron run per context
                $repo       = $this->em->getRepository('MauticContactSourceBundle:Stat');
                $lastEntity = $repo->findBy([], ['id' => 'ASC'], 1, 0);
                $lastEntity = $lastEntity[0];
            }

            /**
             * @var \DateTime
             */
            $from = $lastEntity->getDateAdded();
            $from = is_string($from) ? new \DateTime($from) : $from;

            // round down to 5 minute increment
            $from->setTime($from->format('H'), floor($from->format('i') / 5) * 5, 0);
            $from->add(new \DateInterval('PT1S'));

            // How soon is now? less than 15 mins? Dont run.
            $now = new \DateTime();
            $now->sub(new \DateInterval('PT15M'));
            if ($from > $now) {
                // within 15 mins
                $params['dateFrom'] = $params['dateTo'] = null;

                return $params;
            }
            /**
             * @var \DateTime
             */
            $to = clone $from;
            $to->add(new \DateInterval('PT4M'));
            $to->add(new \DateInterval('PT59S'));

            $params['dateFrom'] = $from->format('Y-m-d H:i:s');

            $params['dateTo'] = $to->format('Y-m-d H:i:s');

            return $params;
        }

        $params['dateFrom'] = $params['dateTo'] = 'invalid';

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
                'reprocessFlag'   => 'reprocess_flag'
            ],
            'CampaignSourceBudgets' => [
            ],
        ];

        //TODO: contexts are hardcoded and so is namespace for dynamic class creation. Need to register context objects

        $class  = '\MauticPlugin\MauticContactLedgerBundle\Entity\\'.$context;
        $entity = new $class();
        foreach ($fieldsMap[$context] as $entityParam => $statKey) {
            $entity->__set($entityParam, $stat[$statKey]);
        }
        $entity->setDateAdded($dateTo);

        return $entity;
    }
}
