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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ReportReprocessCommand extends ModeratedCommand implements ContainerAwareInterface
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
        $this->setName('mautic:ledger:report:reprocess')
            ->setDescription(
                'Reprocess stats in contact_ledger_campaign_source_stats table because logic/schema has changed. You must run a migration to set column \'reprocess_flag\' to 1 before this works.'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchLimit = 50; // change this as needed.
        $batchCount = 0;
        $container  = $this->getContainer();
        $this->em   = $container->get('doctrine.orm.entity_manager');
        $params     = [
            'cacheDir' => $container->getParameter('kernel.cache_dir'),
        ];

        $output->writeln('<info>***** Reprocessing Report Stats *****</info>');
        $timeStart = microtime(true);

        if (!$this->checkRunStatus($input, $output)) {
            $output->writeln('<error>Something failed in CheckRunStatus.</error>');

            return 0;
        }

        do {
            // 1) Get MAX(date_added) records where reprocess_flag = 1
            $batchStart = microtime(true);
            $params     = $this->getDateParams($params);

            if ('invalid' == $params['dateFrom']) {
                $output->writeln(
                    '<comment>Exiting without Running. Reprocess Cron has re-written all history (no more reprocessFlag = true). </comment>'
                );
                $batchCount = $batchLimit;
            } else {
                $output->writeln(
                    '<comment>Batch Index: '.(int) $batchCount."  --> Using Parameters:\n \tDate => ".$params['dateFrom'].' and '.$params['dateTo']." UTC,\n \tQuery Cache Directory => ".$params['cacheDir'].'</comment>'
                );

                // 2) Get entities that match date_added
                $entitiesToReprocess = $this->getEntitiesToReprocess($params);

                // 3) reprocess data using this new date criteria
                $statData = $this->getCampaignSourceStatsData($params);

                foreach ($statData as $stat) {
                    $entity = $this->mapArrayToEntity($stat, $params['dateTo']);
                    $entity->setReprocessFlag(false);
                    $this->em->persist($entity);
                }

                // 4) purge records from step 2
                foreach ($entitiesToReprocess as $entityToDelete) {
                    $this->em->remove($entityToDelete);
                }

                // 5) flush and repeat
                $this->em->flush();
                $timeContext = microtime(true);
                $contextTime = $timeContext - $timeStart;
                $batchRun    = $timeContext - $batchStart;
                $output->writeln(
                    "<comment>\t⌛ Batch Run Time: ".$batchRun.'  --> Total Elapsed time so far: '.$contextTime.'.</comment>'
                );
            }
            ++$batchCount;
        } while ($batchCount < $batchLimit);

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
    private function getDateParams($params)
    {
        // first get oldest date from the table implied in context
        $repo               = $this->em->getRepository('MauticContactLedgerBundle:CampaignSourceStats');
        $maxDateToReprocess = $repo->getMaxDateToReprocess();

        if ($maxDateToReprocess instanceof CampaignSourceStats) {
            /**
             * @var \DateTime
             */
            $to = $maxDateToReprocess->getDateAdded();
            $to = is_string($to) ? new \DateTime($to) : $to;

            // set from  to minus 4 minute 59 sec increment
            /**
             * @var \DateTime
             */
            $from = clone $to;
            $from->sub(new \DateInterval('PT4M'));
            $from->sub(new \DateInterval('PT59S'));

            $params['dateFrom'] = $from->format('Y-m-d H:i:s');

            $params['dateTo'] = $to->format('Y-m-d H:i:s');
        } else {
            $params['dateFrom'] = $params['dateTo'] = 'invalid';
        }

        return $params;
    }

    private function getEntitiesToReprocess($params)
    {
        // first get oldest date from the table implied in context
        $repo                = $this->em->getRepository('MauticContactLedgerBundle:CampaignSourceStats');
        $entitiesToReprocess = $repo->getEntitiesToReprocess($params);

        return $entitiesToReprocess;
    }

    private function getCampaignSourceStatsData($params)
    {
        $repo     = $this->em->getRepository(\MauticPlugin\MauticContactLedgerBundle\Entity\LedgerEntry::class);
        $statData = $repo->getCampaignSourceStatsData($params, true, $params['cacheDir'], false);

        return $statData;
    }

    /**
     * @param $stat
     * @param $context
     *
     * @return CampaignSourceStats
     */
    private function mapArrayToEntity($stat, $dateTo)
    {
        $fieldsMap = [
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
        ];

        //TODO: contexts are hardcoded and so is namespace for dynamic class creation. Need to register context objects

        $entity = new CampaignSourceStats();
        foreach ($fieldsMap as $entityParam => $statKey) {
            if (null == $stat[$statKey]) {
                $stat[$statKey] = '';
            }
            $entity->__set($entityParam, $stat[$statKey]);
        }
        $entity->setDateAdded($dateTo);

        return $entity;
    }
}
