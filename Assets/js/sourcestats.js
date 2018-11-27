Mautic.loadCampaignSourceStatsTable = function (campaignId) {
    var $sourcetarget = mQuery('#sourcestats-table');
    if ($sourcetarget.length) {
        mQuery.ajax({
            url: mauticAjaxUrl,
            type: 'POST',
            data: {
                action: 'plugin:mauticContactLedger:sourceStatsTab',
                campaignId: campaignId
            },
            cache: true,
            dataType: 'json',
            success: function (response) {
                var hideCols = [0];
                var colSpan = 2;

                mQuery('#sourcestats-table').DataTable({
                    language: {
                        emptyTable: 'No results found for this date range and filters.'
                    },
                    data: response.rows,
                    autoFill: true,
                    columns: response.columns,
                    order: [[1, 'asc'], [2, 'asc']],
                    bLengthChange: false,
                    dom: '<<lBf>rtip>',
                    bAutoWidth: false,
                    buttons: [
                        'excelHtml5',
                        'csvHtml5'
                    ],
                    columnDefs: [
                        {
                            render: function (data, type, row) {
                                return rendersourceName(row);
                            },
                            targets: 1
                        },
                        {
                            render: function (data, type, row) {
                                return '$' + data;
                            },
                            targets: [Number(6), Number(7)]
                        },

                        {visible: false, targets: hideCols},
                        {width: '25%', targets: [1]}
                    ],

                    footerCallback: function (row, data, start, end, display) {
                        if (data && data.length === 0 || typeof data[0] === 'undefined') {
                            return;
                        }
                        try {
                            // Add table footer if it doesnt
                            // exist
                            var container = mQuery('#sourcestats-table');
                            var columns = data[0].length;
                            if (mQuery('tr.sourceStatPageTotal').length === 0) {
                                var footer = mQuery('<tfoot></tfoot>');
                                var tr = mQuery('<tr class=\'sourceStatPageTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                                var tr2 = mQuery('<tr class=\'sourceStatGrandTotal\' style=\'font-weight: 600; background: #fafafa;\'></tr>');
                                tr.append(mQuery('<td colspan="' + colSpan + '">Page totals</td>'));
                                tr2.append(mQuery('<td colspan="'+colSpan+'">Grand totals</td>'));
                                for (var i = 3; i < columns; i++) {
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
                            var footer = mQuery(container).find('tfoot tr:nth-child(1)');
                            var footer2 = mQuery(container).find('tfoot tr:nth-child(2)');

                            for (var i = 3; i <= total; i++) {
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
                                var title = mQuery(container).find('thead th:nth-child(' + (i) + ')').text();
                                footer.find('td:nth-child(' + (i -1) + ')').html(FormatFooter(title, pageSum, i));
                                footer2.find('td:nth-child(' + (i - 1) + ')').html(FormatFooter(title, sum, i));

                            }
                        }
                        catch (e) {
                            console.log(e);
                        }
                    } // FooterCallback
                }); //.DataTables
                mQuery('#sourcestats-table_wrapper .dt-buttons').css({
                    float: 'right',
                    marginLeft: '10px'
                });
                mQuery('#sourcestats-table').parent('div').css('overflow-x', 'scroll');
                mQuery('#sourcestats-table').css('min-width', '100%');
                mQuery('#sourcestats-container').addClass('table-done');

            } //success
        });//ajax
    }

    function rendersourceName (row) {

            if (row[1] !== '') {
                return '<a href="'+mauticBaseUrl+'s/contactsource/view/' + row[0] + '" class="campaign-name-link" title="' + row[1] + '">' + row[1] + '</a>';
            }
            return row[1];
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
}

mQuery(document).ready(function () {
    if (!mQuery('#sourcestats-container').hasClass('table-done')) {
        Mautic.loadCampaignSourceStatsTable(campaignId);
    }
});

