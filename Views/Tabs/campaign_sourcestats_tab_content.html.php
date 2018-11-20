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
<script>
    var campaignId =  <?php echo $campaign->getId(); ?>;
    mQuery.getScript(mauticBaseUrl + 'plugins/MauticContactLedgerBundle/Assets/js/sourcestats.js');
</script>
<div class="tab-pane fade in bdr-w-0 page-list" id="sourcestats-container">
    <table id="sourcestats-table" class="table table-striped table-bordered dataTable no-footer"></table>
</div>