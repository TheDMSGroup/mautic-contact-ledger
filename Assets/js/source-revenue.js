Mautic.loadSourceRevenueWidget = function () {
    var $sourcetarget = mQuery('#source-revenue');

    if ("undefined" === mQuery.fn.dataTable) {
        mQuery.getScript(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datatables.min.js', function () {
            mQuery.getScript(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/js/datetime-moment.js');
            mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/datatables.min.css');
            mQuery.getCssOnce(mauticBasePath + '/' + mauticAssetPrefix + 'plugins/MauticContactLedgerBundle/Assets/css/dataTables.fontAwesome.css');
        });
    }

    if ($sourcetarget.length) {
        mQuery('#source-revenue:first:not(.table-initialized)').addClass('table-initialized').each(function () {
            // dependent files loaded, now get the data and render
            mQuery.ajax({
                url: mauticAjaxUrl,
                type: 'POST',
                data: {
                    action: 'plugin:mauticContactLedger:sourceRevenue',
                    groupby: $sourcetarget.data('groupby'),
                },
                cache: true,
                dataType: 'json',
                success: function (response) {
                    if(mQuery(window).width() < 415){
                        var spaginate = 60;
                        var spageStyle = 'full';
                    } else {
                        var spaginate = 0;
                        var spageStyle = 'simple_numbers';
                    }

                    var rowCount = Math.floor(($sourcetarget.data('height') - (235 + spaginate)) / 40);
                    var colAdjust = $sourcetarget.data('groupby') == 'Source Category' ? 4 : 6;
                    var order = $sourcetarget.data('groupby') == 'Source Category' ? [[2, 'asc'], [3, 'asc']] : [[2, 'asc'], [4, 'asc'], [5, 'asc']];
                    var hideCols = $sourcetarget.data('groupby') == 'Source Category' ? [1] : [1, 3];
                    var hiddenCount = $sourcetarget.data('groupby') == 'Source Category' ? 1 : 3;
                    var margin = $sourcetarget.data('groupby') == 'Source Category' ? 1 : 2;
                    var footerAdjust = $sourcetarget.data('groupby') == 'Source Category' ? 2 : 4;
                    var colSpan = $sourcetarget.data('groupby') == 'Source Category' ? 3 : 4;
                    var headerAdjust = $sourcetarget.data('groupby') == 'Source Category' ? 1 : 2;
                    var titleAdjust = $sourcetarget.data('groupby') == 'Source Category' ? 0 : 1;

                    mQuery('#source-revenue').DataTable({
                        language: {
                            emptyTable: 'No results found for this date range and filters.'
                        },
                        data: response.rows,
                        autoFill: true,
                        columns: response.columns,
                        order: order,
                        bLengthChange: false,
                        lengthMenu: [[rowCount]],
                        dom: '<<lBf>rtip>',
                        buttons: [
                            'excelHtml5',
                            'csvHtml5'
                        ],
                        pagingType: spageStyle,

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
                                targets: [Number(7) + Number(hiddenCount), Number(8) + Number(hiddenCount), Number(9) + Number(hiddenCount), Number(11) + Number(hiddenCount)]
                            },
                            {
                                render: function (data, type, row) {
                                    return renderMarginPercentage(data);
                                },
                                targets: [Number(11) + Number(margin)]
                            },
                            {visible: false, targets: hideCols},
                            {width: '5%', targets: [0]},
                        ],

                        footerCallback: function (row, data, start, end, display) {
                            if (data && data.length === 0 || typeof data[0] === 'undefined') {
                                mQuery('#source-builder-overlay').hide();
                                return;
                            }
                            try {
                                // Add table footer if it doesnt
                                // exist
                                var container = mQuery('#source-revenue');
                                var columns = data[0].length;
                                if (mQuery('tr.detailPageTotal').length === 0) {
                                    var footer = mQuery('<tfoot></tfoot>');
                                    var tr = mQuery('<tr class=\'detailPageTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                                    var tr2 = mQuery('<tr class=\'detailGrandTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                                    tr.append(mQuery('<td colspan="'+colSpan+'">Page totals</td>'));
                                    tr2.append(mQuery('<td colspan="'+colSpan+'">Grand totals</td>'));
                                    for (var i = colAdjust; i < columns; i++) {
                                        tr.append(mQuery('<td class=\'td-right\'></td>'));
                                        tr2.append(mQuery('<td class=\'td-right\'></td>'));
                                    }
                                    footer.append(tr);
                                    footer.append(tr2);
                                    container.append(footer);
                                }

                                var api = this.api();

                                // Remove the formatting to get
                                // integer data for summation
                                var intVal = function (i) {
                                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                                };

                                var total = mQuery('#' + container[0].id + ' thead th').length;
                                var footer1 = mQuery(container).find('tfoot tr:nth-child(1)');
                                var footer2 = mQuery(container).find('tfoot tr:nth-child(2)');
                                // i = non summed columns to skip: 4 or 6 (includes hidden cols), add # of hidden cols to total
                                for (var i = colAdjust; i < total + headerAdjust; i++) {
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
                                    var title = mQuery(container).find('thead th:nth-child(' + (i-titleAdjust) + ')').text();
                                    footer1.find('td:nth-child(' + (i - footerAdjust) + ')').html(FormatFooter(title, pageSum, i)); // nth child minus 2 or 4
                                    footer2.find('td:nth-child(' + (i - footerAdjust) + ')').html(FormatFooter(title, sum, i)); // nth child minus 2 or 4
                                }
                                mQuery('#source-builder-overlay').hide();
                            }
                            catch (e) {
                                console.log(e);
                            }
                        } // FooterCallback
                    }); //.DataTables
                    mQuery('#source-revenue_wrapper .dt-buttons').css({
                        float: 'right',
                        marginLeft: '10px'
                    });
                    mQuery('#source-revenue').css('width', 'auto');
                    mQuery('#source-revenue').css('display', 'block');
                    mQuery('#source-revenue').css('overflow-x', 'scroll');
                    if(mQuery(window).width() < 415)
                    {
                        mQuery("#source-revenue_filter").css("float","left");
                        mQuery("#source-revenue_filter").css("max-width","40%");
                        mQuery('.dt-buttons.btn-group').css("max-width", "35%");
                        mQuery('#source-revenue_paginate').css({
                            fontSize: '.7em',
                            marginLeft: '-10%'
                        });

                    } else {
                        mQuery('#source-revenue_paginate').css('margin-top', '-32px');
                    }
                } //success
            });//ajax
        });
    }

    function renderPublishToggle (id, active) {
        if (active == 1) {
            var icon = 'fa-toggle-on';
            var status = 'published';
        }
        else if (active = 0) {
            var icon = 'fa-toggle-off';
            var status = 'unpublished';
        }
        else {
            // no campaign, so leave empty.
            return 'N/A';
        }
        var UpperStatus = status.charAt(0).toUpperCase() + status.substring(1);
        return '<a data-toggle="ajax"><i title="' + UpperStatus + '"class="fa fa-fw fa-lg ' + icon + ' text-success has-click-event campaign-publish-icon' + id + '" data-toggle="tooltip" data-container="body" data-placement="right" data-status="' + status + '" onclick="Mautic.togglePublishStatus(event, \'.campaign-publish-icon' + id + '\', \'campaign\', ' + id + ', \'\', false);" data-original-title="' + UpperStatus + '"></i></a>';
    }

    function renderCampaignName (row) {
        if (row[1] !== '') {
            return '<a href="./campaigns/view/' + row[1] + '" class="campaign-name-link" title="' + row[2] + '">' + row[2] + '</a>';
        }
        return row[2];
    }

    function renderMarginPercentage (data) {
        if (Number(data) != parseInt(Number(data))) {
            return Number(data).toFixed(2) + '%';
        }
        return data + '%';
    }

    function renderSourceName (row) {
        if ($sourcetarget.data('groupby') == 'Source Name') {
            if (row[3] !== '') {
                return '<a href="./contactsource/view/' + row[3] + '" class="campaign-name-link" title="' + row[4] + '">' + row[4] + '</a>';
            }
            return row[3];
        }

        return row[4];
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
}; //loadSourceRevenueWidget

// getScriptCachedOnce for faster page loads in the backend.
mQuery.getScriptCachedOnce = function (url, callback) {
    if (
        typeof window.getScriptCachedOnce !== 'undefined'
        && window.getScriptCachedOnce.indexOf(url) !== -1
    ) {
        callback();
        return mQuery(this);
    }
    else {
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
    if (document.createStyleSheet) {
        document.createStyleSheet(url);
    }
    else {
        mQuery('head').append(mQuery('<link rel=\'stylesheet\' href=\'' + url + '\' type=\'text/css\' />'));
    }
    callback();
};




