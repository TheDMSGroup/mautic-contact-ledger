<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Digital Media Solutions, LLC
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<?php
echo $view['assets']->includeScript('plugins/MauticContactLedgerBundle/Assets/js/source-revenue.js', 'loadSourceRevenueWidget');
$params = $data['params'];
?>


<div class="chart-wrapper">
    <div class="pt-sd pr-md pb-md pl-md">
        <div id="campaign-source-revenue-table" style="height:<?php echo $data['height']; ?>px">
            <!-- Revenue By Campaign + Source -->
            <div class="responsive-table">
                <div id="source-builder-overlay">
                    <div style="position: relative; top: <?php echo $data['height'] / 3; ?>px; left: 45%; index: 1024;display:inline-block; opacity: .5;">
                        <i class="fa fa-spinner fa-spin fa-4x"></i>
                    </div>
                </div>
                <table id="source-revenue" class="table table-striped table-bordered" width="100%" data-height="<?php echo $data['height']; ?>">
                </table>
            </div>
            <!--/ Revenue By Campaign + Source -->
        </div>
    </div>
</div>


