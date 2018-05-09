<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticContactLedgerBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use MauticPlugin\MauticContactLedgerBundle\Event\ReportStatsGeneratorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ReportStatsCommand extends ModeratedCommand implements ContainerAwareInterface
{
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
        $container = $this->getContainer();
        $em        = $container->get('doctrine.orm.entity_manager');

        if (!$this->checkRunStatus($input, $output)) {
            $output->writeln('<error>Something failed in CheckRunStatus.</error>');
            //return 0;
        }

        $output->writeln('<info>Generating Report Stats...</info>');
        $timeStart = microtime(true);

        $cache_dir  = $container->getParameter('kernel.cache_dir');
        $dateParams = $this->getDateParams('America/New_York');
        $output->writeln(
            '<info>        Using Params '.$dateParams['dateFrom'].' and '.$dateParams['dateTo'].' UTC.</info>'
        );


        // Dispatch event to get data from various bundles
        $event = new ReportStatsGeneratorEvent($em, $dateParams, 'MauticContactLedgerBundle:CampaignSourceStats');
        $this->dispatcher->dispatch('mautic.contactledger.reportstats.generate', $event);

        // save entities to DB
        $output->writeln('<info>        Pesisting data.</info>');


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
     * @return array
     *
     * @throws \Exception
     */
    private function getDateParams($timezone)
    {
        $params = [];
        $from   = new \DateTime('midnight', new \DateTimeZone($timezone));
        $from->sub(new \DateInterval('P30D'));
        $to = new \DateTime('midnight', new \DateTimeZone($timezone));

        $from->setTimezone(new \DateTimeZone('UTC'));

        $to->add(new \DateInterval('P1D'))
            ->sub(new \DateInterval('PT1S'))
            ->setTimezone(new \DateTimeZone('UTC'));

        $params['dateFrom'] = $from->format('Y-m-d H:i:s');

        $params['dateTo'] = $to->format('Y-m-d H:i:s');

        return $params;
    }
}
