Mautic.loadSourceRevenueWidget = function (detailWidgetHeight) {
    var $sourcetarget = mQuery('#source-revenue');
    if($sourcetarget.length) {
        mQuery('#source-revenue:first:not(.table-initialized)').addClass('table-initialized').each(function () {
            mQuery.getScriptCachedOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js', function () {
                mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css', function () {
                    mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/dataTables.fontAwesome.css', function () {
                        // dependent files loaded, now get the data and render
                        mQuery.ajax({
                            url: mauticAjaxUrl,
                            type: 'POST',
                            data: {
                                action: 'plugin:mauticContactLedger:sourceRevenue',
                            },
                            cache: true,
                            dataType: 'json',
                            success: function (response) {
                                var rowCount = Math.floor((detailWidgetHeight - 220) / 40);
                                mQuery('#source-revenue').DataTable({
                                    language: {
                                        emptyTable: "No results found for this date range and filters."
                                    },
                                    data: response.rows,
                                    autoFill: true,
                                    columns: response.columns,
                                    order: [[2, 'asc'], [4, 'asc']],
                                    bLengthChange: false,
                                    lengthMenu: [[rowCount]],
                                    columnDefs: [
                                        {
                                            render: function (data, type, row) {
                                                return renderPublishToggle(row[1], row[0]);
                                            },
                                            targets: 0
                                        },
                                        {
                                            render: function (data, type, row) {
                                                return renderCampaignName(row);
                                            },
                                            targets: 2
                                        },
                                        {
                                            render: function (data, type, row) {
                                                return renderSourceName(row);
                                            },
                                            targets: 4
                                        },
                                        {
                                            render: function (data, type, row) {
                                                return '$' + data;
                                            },
                                            targets: [9, 10, 11, 13]
                                        },
                                        {
                                            render: function (data, type, row) {
                                                return data + '%';
                                            },
                                            targets: 12
                                        },
                                        {visible: false, targets: [1, 3]},
                                        {width: '5%', targets: [0]},
                                        {width: '20%', targets: [2]},
                                        {width: '15%', targets: [4]}
                                    ],

                                    footerCallback: function (row, data, start, end, display) {
                                        // Add table footer if it doesnt exist
                                        var container = mQuery('#source-revenue');
                                        var columns = data[0].length;
                                        if (mQuery('tr.detailPageTotal').length == 0) {
                                            var footer = mQuery('<tfoot></tfoot>');
                                            var tr = mQuery('<tr class=\'detailPageTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                                            var tr2 = mQuery('<tr class=\'detailGrandTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                                            tr.append(mQuery('<td colspan=\'3\'>Page totals</td>'));
                                            tr2.append(mQuery('<td colspan=\'3\'>Grand totals</td>'));
                                            for (var i = 2; i < columns; i++) {
                                                tr.append(mQuery('<td class=\'td-right\'></td>'));
                                                tr2.append(mQuery('<td class=\'td-right\'></td>'));
                                            }
                                            footer.append(tr);
                                            footer.append(tr2);
                                            container.append(footer);
                                            var tableBody = mQuery('#' + container[0].id + ' tbody');
                                        }

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
                                            for (var i = 2; i < total - 1; i++) {
                                                var pageSum = api
                                                    .column(i + 3, {page: 'current'})
                                                    .data()
                                                    .reduce(function (a, b) {
                                                        return intVal(a) + intVal(b);
                                                    }, 0);
                                                var sum = api
                                                    .column(i + 3)
                                                    .data()
                                                    .reduce(function (a, b) {
                                                        return intVal(a) + intVal(b);
                                                    }, 0);
                                                var title = mQuery(container).find('thead th:nth-child(' + (i + 2) + ')').text();
                                                footer1.find('td:nth-child(' + (i) + ')').html(FormatFooter(title, pageSum, i));
                                                footer2.find('td:nth-child(' + (i) + ')').html(FormatFooter(title, sum, i));
                                            }
                                        }
                                        catch (e) {
                                            console.log(e);
                                        }
                                    } // FooterCallback
                                }); //.DataTables
                            } //success
                        }); //ajax
                    }); //getScriptsCachedOnce - fonteawesome css
                });//getScriptsCachedOnce - datatables css
            });  //getScriptsCachedOnce - datatables js
        });
    }
}; //loadSourceRevenueWidget

function renderPublishToggle (id, active) {
    if (active == 1) {
        var icon = 'fa-toggle-on';
        var status = 'published';
    }
    else {
        var icon = 'fa-toggle-off';
        var status = 'unpublished';
    }
    var UpperStatus = status.charAt(0).toUpperCase() + status.substring(1);
    return '<a data-toggle="ajax"><i title="' + UpperStatus + '"class="fa fa-fw fa-lg ' + icon + ' text-success has-click-event campaign-publish-icon' + id + '" data-toggle="tooltip" data-container="body" data-placement="right" data-status="' + status + '" onclick="Mautic.togglePublishStatus(event, \'.campaign-publish-icon' + id + '\', \'campaign\', ' + id + ', \'\', false);" data-original-title="' + UpperStatus + '"></i></a>';
}

function renderCampaignName (row) {
    return '<a href="./campaigns/view/'+ row[1] +'" class="campaign-name-link" title="'+ row[2] + '">'+ row[2] + '</a>';
}

function renderSourceName (row) {
    return '<a href="./contactsource/view/'+ row[3] +'" class="campaign-name-link" title="'+ row[4] + '">'+ row[4] + '</a>';
}

function FormatFooter (column, value, index) {
    column = column.trim();
    var numFormat = mQuery.fn.dataTable.render.number(',', '.', 0).display;
    var curFormat = mQuery.fn.dataTable.render.number(',', '.', 2, '$').display;
    var curPreciseFormat = mQuery.fn.dataTable.render.number(',', '.', 4, '$').display;
    if (column === 'Margin') {
        return ' - ';
    }
    if (column === 'Revenue' || column === 'Cost' || column === 'GM') {
        return curFormat(value);
    }
    if (column === 'eCPM') {
        return curPreciseFormat(value);
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

mQuery(document).ready(function () {
    Mautic.loadSourceRevenueWidget();
});

