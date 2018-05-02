<?php
/**
 * Created by PhpStorm.
 * User: scottshipman
 * Date: 5/2/18
 * Time: 8:56 AM
 */

namespace MauticPlugin\MauticContactLedgerBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class DashboardCronWarmerCommand extends ModeratedCommand implements ContainerAwareInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('mautic:ledger:cache:warm')
            ->setDescription(
                'Warm the Dashboard widget cron for default date settings'
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

        $output->writeln("<info>Warming cache for Dashboard widgets...</info>");
        $timeStart = microtime(true);

        $cache_dir = $container->getParameter('kernel.cache_dir');
        $paramsEST    = $this->getDateParams('America/New_York');
        $paramsUTC    = $this->getDateParams('UTC');
        $repo      = $container->get('mautic.contactledger.model.ledgerentry')->getRepository();

        // Warm Just Campaign level widget for EST and UTC
        $output->writeln("<info>    1) Warming Campaign Performance data.</info>");
        $output->writeln("<info>        Using Params ".$paramsEST['dateFrom']." and ".$paramsEST['dateTo']." America/New_York.</info>");
        $repo->getDashboardRevenueWidgetData($paramsEST, false, $cache_dir);
        $output->writeln("<info>        Using Params ".$paramsUTC['dateFrom']." and ".$paramsUTC['dateTo']." UTC.</info>");
        $repo->getDashboardRevenueWidgetData($paramsUTC, false, $cache_dir);

        // Warm Just Campaign By Source widget data for EST and UTC
        $output->writeln("<info>    2) Warming Detailed Campaign By Source data.</info>");
        $output->writeln("<info>        Using Params ".$paramsEST['dateFrom']." and ".$paramsEST['dateTo']." America/New_York.</info>");
        $repo->getDashboardRevenueWidgetData($paramsEST, true, $cache_dir);
        $output->writeln("<info>        Using Params ".$paramsUTC['dateFrom']." and ".$paramsUTC['dateTo']." UTC.</info>");
        $repo->getDashboardRevenueWidgetData($paramsUTC, false, $cache_dir);

        $this->completeRun();

        $output->writeln("<info>Complete With No Errors.</info>");
        $timeEnd     = microtime(true);
        $elapsedTime = $timeEnd - $timeStart;
        $output->writeln("<info>Total Execution Time: $elapsedTime.</info>");

        return 0;

    }

    /**
     * Get date params from session / or set defaults
     * and convert User timezone to UTC before sending to Queries.
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getDateParams($timezone)
    {
        $params    =[];
        $from = new \DateTime('midnight', new \DateTimeZone($timezone));
        $from->sub(new \DateInterval('P30D'));
        $to    = new \DateTime('midnight', new \DateTimeZone($timezone));

        $from->setTimezone(new \DateTimeZone('UTC'));

        $to->add(new \DateInterval('P1D'))
            ->sub(new \DateInterval('PT1S'))
            ->setTimezone(new \DateTimeZone('UTC'));

        $params['dateFrom'] = $from->format('Y-m-d H:i:s');

        $params['dateTo'] = $to->format('Y-m-d H:i:s');

        return $params;
    }

}