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
<script src="/plugins/MauticContactLedgerBundle/Assets/js/source-revenue.js"></script>
<?php
$params = $data['params'];
?>


<div class="chart-wrapper">
    <div class="pt-sd pr-md pb-md pl-md">
        <div id="campaign-source-revenue-table" style="height:<?php echo $data['height']; ?>px">
            <!-- Revenue By Campaign + Source -->
            <div class="responsive-table">
                <div id="source-builder-overlay"><div style="position: relative; top: <?php echo $data['height'] / 3; ?>px; left: 45%; index: 1024;display:inline-block; opacity: .5;"><i class="fa fa-spinner fa-spin fa-4x"></i></div></div>
                <table id="source-revenue" class="table table-striped table-bordered" width="100%">
                </table>
            </div>
            <!--/ Revenue By Campaign + Source -->
        </div>
    </div>
</div>
<script>
    var detailWidgetHeight = <?php echo $data['height']; ?>;
    mQuery('#source-builder-overlay').show();
    Mautic.loadSourceRevenueWidget(detailWidgetHeight);
</script>

