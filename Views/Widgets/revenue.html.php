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
<script src="/plugins/MauticContactLedgerBundle/Assets/js/global-revenue.js"></script>

<?php
$params = $data['params'];
?>

<div class="chart-wrapper">
    <div class="pt-sd pr-md pb-md pl-md">
        <div id="campaign-revenue-table" style="height:<?php echo $data['height']; ?>px">
            <!-- Revenue By Campaign -->
            <div class="responsive-table">
                <table id="global-revenue" class="table table-striped table-bordered" width="100%">
                </table>
            </div>
            <!--/ Revenue By Campaign -->
        </div>
    </div>
</div>
<script>
    Mautic.loadGlobalRevenueWidget(<?php echo $data['height']; ?>);
</script>