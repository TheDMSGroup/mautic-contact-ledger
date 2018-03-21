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
<script src="/plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js"></script>

<?php
echo $view['assets']->includeStylesheet('plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css');
?>

<?php
    $params = $data['params'];
?>

<?php if (isset($data['entries']) && $data['entries']) : ?>
    <div class="chart-wrapper">
        <div class="pt-sd pr-md pb-md pl-md">
            <div id="campaign-revenue-table" style="height:<?php echo $data['height']; ?>px">
                <!-- Revenue By Campaign -->
                <div id="global-revenue" class="table-responsive">

                </div>
                <!--/ Revenue By Campaign -->
            </div>
        </div>
    </div>
<?php endif; ?>
<script>
    mQuery(document).ready(function() {
        console.log('gettin the json');
        mQuery.ajax({
            url: mauticAjaxUrl,
            type: 'POST',
            data: {
                action: 'plugin:mauticContactLedger:globalRevenue',
            },
            cache: true,
            dataType: 'json',
            success: function (response) {
                console.log(response);
                mQuery('#global-revenue').DataTable( {
                    data: response.dataRows,
                    columns: response.dataHeader
                } );
            }
        });
    } );
</script>