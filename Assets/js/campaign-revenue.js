Mautic.loadCampaignRevenueWidget = function (params) {
    var $campaigntarget = mQuery('#campaign-revenue-table');
    if($campaigntarget.length) {
        mQuery('#campaign-revenue-table:not(.table-initialized):first').addClass('table-initialized').each(function () {
            mQuery.getScriptCachedOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js', function () {
                mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css', function () {
                    mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/dataTables.fontAwesome.css', function () {
                        mQuery('#campaign-revenue-table').DataTable({
                            'ajax': {
                                'url': mauticAjaxUrl + params,
                                'dataSrc': 'data'
                            },
                            'columns': [
                                {'data': 'label'},
                                {'data': 'cost'},
                                {'data': 'revenue'},
                                {'data': 'profit'}
                            ],
                            'columnDefs': [
                                {
                                    render: function (data, type, row) {
                                        return parseFloat(data, 10).toFixed(2);
                                    },
                                    targets: [1, 2, 3]
                                }
                            ],
                            'aaSorting': [],
                            'autoFill': true,
                            dom: '<<lBf>rtip>',
                            buttons: [
                                'excelHtml5',
                                'csvHtml5'
                            ],
                            'footerCallback': function (row, data, start, end, display) {
                                // Add table footer if it doesnt exist
                                var container = mQuery('#campaign-revenue-table');

                                if (data && data.length === 0) {
                                    return;
                                }
                                try {
                                    var api = this.api();
                                    // Remove the formatting to get integer data for
                                    // summation
                                    var intVal = function (i) {
                                        return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                                    };

                                    var total = mQuery('#' + container[0].id + ' thead th').length;
                                    var footer1 = mQuery(container).find('tfoot tr:nth-child(1)');
                                    var footer2 = mQuery(container).find('tfoot tr:nth-child(2)');
                                    for (var i = 1; i < total; i++) {
                                        var pageSum = api
                                            .column(i, {page: 'current'})
                                            .data()
                                            .reduce(function (a, b) {
                                                return intVal(a) + intVal(b);
                                            }, 0);
                                        var sum = api
                                            .column(i)
                                            .data()
                                            .reduce(function (a, b) {
                                                return intVal(a) + intVal(b);
                                            }, 0);
                                        var title = mQuery(container).find('thead th:nth-child(' + (i + 1) + ')').text();
                                        footer1.find('td:nth-child(' + (i + 1) + ')').html(FormatFooter(title, pageSum, i));
                                        footer2.find('td:nth-child(' + (i + 1) + ')').html(FormatFooter(title, sum, i));
                                    }
                                }
                                catch (e) {
                                    console.log(e);
                                }
                            } // FooterCallback
                        }); // datatable
                        mQuery('#campaign-revenue-table_wrapper .dt-buttons').css({float: "right", marginLeft: "10px"});
                    }); //getScriptsCachedOnce - fonteawesome css
                });//getScriptsCachedOnce - datatables css
            });  //getScriptsCachedOnce - datatables js
        });
    }
    // hide other instances of the daterange form since we inject our own
    mQuery('form[name="daterange"]:not(:first)').css('display','none');
}; //loadCampaignRevenueWidget

function FormatFooter (column, value, index) {
    column = column.trim();
    var numFormat = mQuery.fn.dataTable.render.number(',', '.', 0).display;
    var curFormat = mQuery.fn.dataTable.render.number(',', '.', 2, '$').display;
    var curPreciseFormat = mQuery.fn.dataTable.render.number(',', '.', 4, '$').display;
    if (column === 'Revenue' || column === 'Cost' || column === 'Profit') {
        return curFormat(value);
    }
    return numFormat(value);
}

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