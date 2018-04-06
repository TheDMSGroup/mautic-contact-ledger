Mautic.loadCampaignRevenueWidget = function () {
    mQuery('#campaign-revenue-table:not(.table-initialized):first').addClass('table-initialized').each(function() {
        mQuery.getScriptCachedOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js', function () {
            mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css', function () {
                mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/dataTables.fontAwesome.css', function () {
                    mQuery('#campaign-revenue-table').DataTable(datatableRequest);
                }); //getScriptsCachedOnce - fonteawesome css
            });//getScriptsCachedOnce - datatables css
        });  //getScriptsCachedOnce - datatables js
    });
}; //loadCampaignRevenueWidget

// getScriptCachedOnce for faster page loads in the backend.
mQuery.getScriptCachedOnce = function (url, callback) {
    if (
        typeof window.getScriptCachedOnce !== 'undefined'
        && window.getScriptCachedOnce.indexOf(url) !== -1
    ) {
        callback();
        return mQuery(this);
    } else {
        return mQuery.ajax({
            url: url,
            dataType: 'script',
            cache: true
        }).done(function () {
            if (typeof window.getScriptCachedOnce === 'undefined') {
                window.getScriptCachedOnce = [];
            }
            window.getScriptCachedOnce.push('url');
            callback();
        });
    }
};

// getScriptCachedOnce for faster page loads in the backend.
mQuery.getCssOnce = function (url, callback) {
    if (document.createStyleSheet){
        document.createStyleSheet(url);
    }
    else {
        mQuery("head").append(mQuery("<link rel='stylesheet' href='" + url + "' type='text/css' />"));
    }
    callback();
};

mQuery(document).ready(function () {
    Mautic.loadCampaignRevenueWidget();
});
mQuery(document).ajaxComplete(function (event, xhr, settings) {
    Mautic.loadCampaignRevenueWidget();
});