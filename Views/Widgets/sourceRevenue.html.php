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
<script>
    var detailWidgetHeight = <?php echo $data['height']; ?> ;
</script>

<div class="chart-wrapper">
    <div class="pt-sd pr-md pb-md pl-md">
        <div id="campaign-source-revenue-table" style="height:<?php echo $data['height']; ?>px">
            <!-- Revenue By Campaign + Source -->
            <div class="responsive-table">
                <table id="source-revenue" class="table table-striped table-bordered" width="100%">
                </table>
            </div>
            <!--/ Revenue By Campaign + Source -->
        </div>
    </div>
</div>