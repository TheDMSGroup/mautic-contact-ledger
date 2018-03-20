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

<?php if (isset($data['entries']) && $data['entries']) : ?>
    <?php
    $headerArr = ["active", "name", "received", "converted", "revenue", "cost", "gm", "margin", "ecpm"];
    $entries   = $data['entries'];
    $baseUrl   = $view['router']->path('mautic_dashboard_index');
    ?>
    <div class="chart-wrapper">
        <div class="pt-sd pr-md pb-md pl-md">
            <div id="campaign-revenue-table" style="height:<?php echo $data['height']; ?>px">
                <!-- Revenue By Campaign -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="dashboard-campaign-revenue"
                           style="z-index: 2; position: relative;">
                        <thead>
                        <tr>
                            <?php foreach ($headerArr as $header): ?>
                                <th>
                                    <a title="<?php echo $view['translator']->trans(
                                        'mautic.contactledger.dashboard.revenue.header.'.$header
                                    ); ?>">
                                        <?php echo $view['translator']->trans(
                                            'mautic.contactledger.dashboard.revenue.header.'.$header
                                        ); ?>
                                    </a>
                                </th>
                            <?php endforeach; ?>
                            <?php
                           // todo Table Column Sorting, for each column do the pattern below
                           // instead of hardcoded headers
                           //
                           //  echo $view->render(
                           //      'MauticCoreBundle:Helper:tableheader.html.php',
                           //      [
                           //          'orderBy'    => 'message',
                           //          'text'       => 'mautic.contactsource.timeline.message',
                           //          'class'      => 'timeline-name',
                           //          'sessionVar' => 'contactsource.'.$contactSource->getId().'.timeline',
                           //          'baseUrl'    => $baseUrl,
                           //          'target'     => '#campaign-revenue-table',
                           //      ]
                           //  );
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($entries as $counter => $entry): ?>
                            <?php
                            $counter   += 1; // prevent 0
                            $status    = isset($entry['is_published']) && $entry['is_published'] ? 'published' : '';
                            $icon      = isset($entry['is_published']) && $entry['is_published'] ? 'fa-toggle-on' : 'fa-toggle-off';
                            $name      = (isset($entry['name'])) ? $entry['name'] : "";
                            $received  = (isset($entry['received'])) ? $entry['received'] : "";
                            $converted = (isset($entry['converted'])) ? $entry['converted'] : "";
                            $revenue   = (isset($entry['revenue'])) ? $entry['revenue'] : "";
                            $cost      = (isset($entry['cost'])) ? $entry['cost'] : "";
                            $gm        = (isset($entry['gm'])) ? $entry['gm'] : "";
                            $margin    = (isset($entry['margin'])) ? floatval($entry['margin']) : "";
                            $ecpm      = (isset($entry['ecpm'])) ? floatval($entry['ecpm']) : "";

                            $rowStripe = (0 === $counter % 2) ? ' timeline-row-highlighted' : '';

                            // todo Create a JS event that includes Mautic.togglePublishedStatus to clear the event cache.
                            ?>
                            <tr class="timeline-row<?php echo $rowStripe; ?>">
                                <td class="timeline-icon">
                                    <a data-toggle="ajax"><i
                                                class="fa fa-fw fa-lg <?php echo $icon; ?> text-success has-click-event campaign-publish-icon<?php echo $entry['campaign_id']; ?>"
                                        data-toggle="tooltip" data-container="body" data-placement="right"
                                        data-status="<?php echo $status; ?>" title=""
                                        onclick="Mautic.togglePublishStatus(event, '.campaign-publish-icon<?php echo $entry['campaign_id']; ?>', 'campaign', <?php echo $entry['campaign_id']; ?>, '', false);"
                                        data-original-title="<?php echo ucfirst($status); ?>"></i>
                                    </a>
                                </td>
                                <td class="campaign-revenue-name"><?php echo $name; ?></td>
                                <td class="campaign-revenue-received"><?php echo $received; ?></td>
                                <td class="campaign-revenue-converted"><?php echo $converted; ?></td>
                                <td class="campaign-revenue-revenue">$<?php echo $revenue; ?></td>
                                <td class="campaign-revenue-cost">$<?php echo $cost; ?></td>
                                <td class="campaign-revenue-gm">$<?php echo $gm; ?></td>
                                <td class="campaign-revenue-margin"><?php echo number_format($margin, 2, '.',','); ?>%</td>
                                <td class="campaign-revenue-ecpm">$<?php echo number_format($ecpm, 4, '.', ','); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="timeline-row<?php echo $rowStripe; ?>">
                            <td colspan="2" class="timeline-icon">
                                <?php echo $view['translator']->trans(
                                    'mautic.contactledger.dashboard.revenue.summary'
                                ); ?>
                            </td>
                            <td class="campaign-revenue-received"><?php echo $data['summary']['receivedTotal']; ?></td>
                            <td class="campaign-revenue-converted"><?php echo $data['summary']['convertedTotal']; ?></td>
                            <td class="campaign-revenue-revenue">$<?php echo $data['summary']['revenueTotal']; ?></td>
                            <td class="campaign-revenue-cost">$<?php echo $data['summary']['costTotal']; ?></td>
                            <td class="campaign-revenue-gm">$<?php echo $data['summary']['gmTotal']; ?></td>
                            <td class="campaign-revenue-margin"><?php echo number_format($data['summary']['marginTotal'], 2, '.', ','); ?>%</td>
                            <td class="campaign-revenue-ecpm">$<?php echo number_format($data['summary']['ecpmTotal'], 4, '.', ','); ?></td>
                        </tr>
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
