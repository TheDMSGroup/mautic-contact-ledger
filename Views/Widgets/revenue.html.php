<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<?php if (isset($data) && $data) : ?>
    <?php
        $headerArr = ["active", "name", "received", "converted", "revenue", "cost", "gm", "margin", "ecpm"]
    ?>
    <div class="chart-wrapper">
        <div class="pt-sd pr-md pb-md pl-md">
            <div id="campaign-revenue-table" style="height:<?php echo $chartHeight; ?>px">
                <!-- Revenue By Campaign -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="dashboard-campaign-revenue" style="z-index: 2; position: relative;">
                        <thead>
                            <tr>
                                <?php foreach ($headerArr as $header): ?>
                                    <th>
                                        <a class="btn btn-sm btn-nospin btn-default" data-activate-details="all" data-toggle="tooltip"
                                           title="<?php $view['translator']->trans(
                                               'mautic.contactledger.dashboard.revenue.'.$header
                                           ); ?>">
                                            <?php $view['translator']->trans(
                                                'mautic.contactledger.dashboard.revenue.'.$header
                                            ); ?>
                                        </a>
                                    </th>
                                <?php endforeach; ?>
                                <?php
                                // todo Table Column Sorting, for each column do the pattern below
                                // instead of hardcoded headers

                                // echo $view->render(
                                //     'MauticCoreBundle:Helper:tableheader.html.php',
                                //     [
                                //         'orderBy'    => 'message',
                                //         'text'       => 'mautic.contactsource.timeline.message',
                                //         'class'      => 'timeline-name',
                                //         'sessionVar' => 'contactsource.'.$contactSource->getId().'.timeline',
                                //         'baseUrl'    => $baseUrl,
                                //         'target'     => '#campaign-revenue-table',
                                //     ]
                                // );
                                 ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data as $counter => $data): ?>
                            <?php
                            $counter += 1; // prevent 0
                            $icon       = (isset($data['active']) && $data['active']) ? 'fa facheck-circle' : '';
                            $name = (isset($data['name'])) ? $data['name']: "";
                            $received    = (isset($data['received'])) ? $data['received'] : "";
                            $converted    = (isset($data['converted'])) ? $data['converted'] : "";
                            $revenue    = (isset($data['revenue'])) ? $data['revenue'] : "";
                            $cost    = (isset($data['cost'])) ? $data['cost'] : "";
                            $gm    = (isset($data['gm'])) ? $data['gm'] : "";
                            $margin    = (isset($data['margin'])) ? $data['margin'] : "";
                            $ecpm    = (isset($data['ecpm '])) ? $data['ecpm '] : "";

                            $rowStripe = (0 === $counter % 2) ? ' timeline-row-highlighted' : '';
                            ?>
                            <tr class="timeline-row<?php echo $rowStripe; ?>">
                                <td class="timeline-icon">
                                    <i class="<?php echo $icon;?>"
                                </td>
                                <td class="campaign-revenue-name"><?php echo $name; ?></td>
                                <td class="campaign-revenue-received"><?php echo $received; ?></td>
                                <td class="campaign-revenue-converted"><?php echo $converted; ?></td>
                                <td class="campaign-revenue-revenue"><?php echo $revenue; ?></td>
                                <td class="campaign-revenue-cost"><?php echo $cost; ?></td>
                                <td class="campaign-revenue-gm"><?php echo $gm; ?></td>
                                <td class="campaign-revenue-margin"><?php echo $margin; ?></td>
                                <td class="campaign-revenue-ecpm"><?php echo $ecpm; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php echo $view->render(
                    'MauticCoreBundle:Helper:pagination.html.php',
                    [
                        'page'       => $data['page'],
                        'fixedPages' => $data['maxPages'],
                        'fixedLimit' => true,
                        'baseUrl'    => $baseUrl,
                        'target'     => '#campaign-revenue-table',
                        'totalItems' => $data['total'],
                    ]
                ); ?>
                <!--/ Revenue By Campaign -->
            </div>
        </div>
    </div>
<?php endif; ?>
