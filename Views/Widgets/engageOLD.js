//////////////////////////
// Initialize AdminPlus //
//////////////////////////

// Sidebars
AdminPlus.Sidebar.init();

var Tabs = function (items, callback) {
    var self = this;
    self.items = ko.observableArray(items);
    self.active = ko.observable("");
    self.active.subscribe(function () {
        if ($("a.nav-tabs-dropdown:visible").length > 0)
            $("a.nav-tabs-dropdown").removeClass("open").next("ul").slideToggle();
    });
    self.dropdown = function (vm, elem) {
        $(elem.target).toggleClass("open").next("ul").slideToggle();
    };
    self.callback = callback;
    self.items.subscribe(function (e) {
        if (callback)
            callback(e);
    });
};

var TabItem = function (name, active, cid) {
    var self = this;
    self.name = name;
    self.active = ko.observable(active);
    self.cid = cid;
    self.isActive = ko.computed(function () {
        return self.active() ? "active" : "";
    });
    self.click = function () {
    };

};

var Breadcrumb = function (items) {
    var self = this;
    self.items = ko.observableArray(items);
};

var BreadcrumbItem = function (name, active, icon, cid, url, hideCaption) {
    var self = this;
    self.name = ko.observable(name);
    self.active = ko.observable(active);
    self.icon = ko.observable(icon);
    self.hideCaption = ko.observable(hideCaption);
    self.cid = ko.observable(cid);
    self.url = ko.observable(url);
    self.isActive = ko.computed(function () {
        return self.active() ? "active" : "";
    });
    self.click = function (e) {
        App.Navigate(e.cid(), e.url(), true);
    };
};

var TimeBoard = function (value, container, callback) {
    var self = this;
    self.initialized = false;
    self.selected = ko.observable(value);
    self.container = container || "timeBoard";
    self.callback = callback;
    self.from = moment().subtract(1, "days").hours(0).minutes(0).seconds(0).format("MM/DD/YYYY hh:mm A");
    self.to = moment().hours(0).minutes(0).seconds(0).format("MM/DD/YYYY hh:mm A");
    self.refresh = function() {
        if (callback)
            callback(value);
    };
    self.Select = function (m, e) {
        var value = parseInt($(e.currentTarget).attr("data-id"));
        if (value === 6) {
            self.init();
        }
        self.selected(value);
        App.timeboardSelected = value;
        if (value !== 6) {
            if (callback)
                callback(value);
        }
    };
    self.isDateRangeVisible = ko.computed(function () {
        return self.selected() === 6;
    });
    self.init = function (value) {
        if (self.initialized)
            return;
        var date1 = moment().subtract(1, "days").hours(0).minutes(0).seconds(0);
        var date2 = moment().hours(0).minutes(0).seconds(0);

        var fromPicker = $(container + " div[title='from']>div");
        if (fromPicker.length === 0) {
            fromPicker = $(container + " div[data-original-title='from']>div");
        }
        var toPicker = $(container + " div[title='to']>div");
        if (toPicker.length === 0) {
            toPicker = $(container + " div[data-original-title='to']>div");
        }

        fromPicker.datetimepicker({ defaultDate: new Date(date1) });
        toPicker.datetimepicker({ defaultDate: new Date(date2), useCurrent: false });
        fromPicker.on("dp.change",
            function (e) {
                self.from = e.date.format("MM/DD/YYYY hh:mm A");
                toPicker.data("DateTimePicker").minDate(e.date);

            });
        toPicker.on("dp.change",
            function (e) {
                self.to = e.date.format("MM/DD/YYYY hh:mm A");
                fromPicker.data("DateTimePicker").maxDate(e.date);
            });
        $(container + ' [data-toggle="tooltip"]').tooltip();
        self.initialized = true;
    };
    self.Apply = function () {
        if (callback)
            callback();
    };
};

/////////////////////////////////////
/////////////////////////////////////

window.App = {
    view: "0",
    intervalId: 0,
    title: { caption: ko.observable("") },
    timeBoard: new TimeBoard(0),
    validator: null,
    current: "",
    container: "",
    table: null,
    viewModel: null,
    GoBack: "",
    breadcrumb: null,
    logoutConfirmed: false,
    timeboardSelected: 0,
    reloadLogs: true,
    fn: {
        tryParseJSON: function (jsonString) {
            try {
                var o = JSON.parse(jsonString);
                if (o && typeof o === "object") {
                    return o;
                }
            }
            catch (e) { }
            return false;
        },
        guid: function guid() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            return s4() + s4() + "-" + s4() + "-" + s4() + "-" + s4() + "-" + s4() + s4() + s4();
        },
        logout: function (e) {

            e.preventDefault();
            $("#logoutForm").submit();
        },
        convertDatatableServerRequest: function (d) {
            var result = {
                draw: d.draw,
                page: d.start,
                pageSize: d.length,
                search: d.search.value,
                searchRegex: d.search.regex,
                columns: []
            };
            for (var i = 0; i < d.columns.length; i++) {
                result.columns.push({
                    name: d.columns[i].data,
                    search: []
                });
            }
            for (var j = 0; j < d.order.length; j++) {
                result.columns[d.order[j].column].orderIndex = j;
                result.columns[d.order[j].column].orderAsc = d.order[j].dir === "asc";
            }
            return result;
        },
        capitalize: function (string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        createLogTab: function (id, link, container) {
            App.reloadLogs = false;
            var tabId = "tab_" + id;
            if ($("#" + tabId).length > 0) {
                $('.nav-tabs a[href="#' + tabId + '"]').tab('show');
                return;
            }
            var newTab = new TabItem('<span class="fa fa-book"></span> Log ' + id + " <span class='fa fa-times clickable' data-id='" + id + "'></span>", false, tabId);
            App.tabs.items.push(newTab);
            $("div.tab-content").append($('<div class="tab-pane" aria-expanded="true" id="' + tabId + '"></div>'));

            App.Navigate(tabId, link, false);
            $('.nav-tabs a[href="#' + tabId + '"]').tab('show');


            $("#tabs span.clickable[data-id='" + id + "']").click(function () {
                var index = -1;
                for (var i2 = 0; i2 < App.tabs.items().length; i2++) {
                    var cid = App.tabs.items()[i2].cid.trim();
                    var cid2 = tabId;
                    if (cid === cid2) {
                        index = i2;
                        break;
                    }
                }
                if (index > -1) {
                    App.tabs.items.splice(index, 1);
                    $("#tab_" + id).remove();
                    var t = App.tabs.items()[App.tabs.items().length - 1];
                    $('.nav-tabs a[href="#' + t.cid + '"]').tab('show');
                }
            });
            $('container [data-toggle="tooltip"]').tooltip({ delay: 100 });
        },
        tables: {
            registerEvents: function (container, endpoint, config) {
                $(container + ' [data-role="link"]').off('click').on('click', function (e) {
                    e.preventDefault();
                    var link = $(this).attr("href");
                    App.Navigate("main", link, true);
                });

                $(container + ' span[data-role="edit"]').off('click').on('click', function () {
                    var self = this;
                    var row = $(self).closest('tr');
                    var id = App.table.row(row).id();
                    if (!id) {
                        var parent = row.prev("tr.parent");
                        id = App.table.row(parent).id();
                    }
                    App.GoBack = config.edit.back;
                    config.ajax.idParam = config.ajax.idParam || "id";
                    var link = config.edit.url.indexOf('?') === -1 ? config.edit.url + "?id=" + id : config.edit.url + "&" + config.ajax.idParam + "=" + id;
                    App.Navigate("main", link, false);
                });

                $(container + ' span[data-role="delete"]').off('click').on('click', function () {
                    var self = this;
                    var row = $(self).parents("tr");
                    var data = App.table.row(row).data();
                    var id = App.table.row(row).id();
                    if (!id) {
                        var parent = row.prev("tr.parent");
                        //id = App.table.row(parent).id();
                        data = App.table.row(parent).data();
                    }
                    swal({
                        title: "Are you sure?",
                        text: "You can not recover this " + config.name.singular + " after it's been deleted.",
                        type: "warning",
                        showCancelButton: true,
                        cancelButtonText: "No",
                        confirmButtonColor: "#039BE5",
                        confirmButtonText: "Yes",
                        closeOnConfirm: false
                    }, function () {
                        NProgress.start();
                        $.ajax({
                            url: endpoint,
                            data: data,
                            type: "DELETE"
                        }).done(function () {
                            App.table.row(row).remove().draw();
                            swal({
                                title: App.fn.capitalize(config.name.singular) + " deleted.",
                                type: "success",
                                timer: 1000,
                                showConfirmButton: false
                            });
                        })
                            .fail(function () {
                                swal({
                                    title: App.fn.capitalize(config.name.singular) + " was not deleted.",
                                    text: "There was an error deleting the " + config.name.singular + ".",
                                    type: "error",
                                    showConfirmButton: true
                                });
                            })
                            .always(function () {
                                NProgress.done();
                            });
                    });
                });

                $(container + ' [data-toggle="tooltip"]').tooltip({ delay: 100 });
            },
            createTable: function (container, endpoint, config) {
                config.rowId = config.rowId || "Id";
                var table = $(container);

                if (App.table) {
                    App.table.destroy();
                    App.table = null;
                }

                table.empty();
                if (config.summary === true && config.columns) {
                    var footer = $("<tfoot></tfoot>");
                    var tr = $("<tr class='pageTotal'></tr>");
                    var tr2 = $("<tr class='grandTotal'></tr>");
                    tr.append($("<td colspan='2'>Page totals</td>"));
                    tr2.append($("<td colspan='2'>Grand totals</td>"));
                    for (var i = 2; i < config.columns.length; i++) {
                        tr.append($("<td class='td-right'></td>"));
                        tr2.append($("<td class='td-right'></td>"));
                    }
                    footer.append(tr);
                    footer.append(tr2);
                    table.append(footer);
                }
                $(container).on('preXhr.dt',
                    function () {
                        NProgress.start();
                    }).on('xhr.dt',
                    function () {
                        NProgress.done();
                    }).on('draw.dt',
                    function () {
                        App.fn.tables.registerEvents(container, endpoint, config);
                        $('[data-toggle="tooltip"]').tooltip({ delay: 100 });
                        $(container + ' i[data-role="activate"]').off('click').on('click',
                            function () {
                                var self = this;
                                var row = $(self).parents('tr');
                                var action;
                                var data = App.table.row(row).data();
                                if ($(self).hasClass("text-success")) {
                                    action = " deactivate";
                                    data.IsActive = false;
                                } else {
                                    action = " activate";
                                    data.IsActive = true;
                                }
                                swal({
                                        title: "Are you sure that?",
                                        text: "You want to " + action + " this " + config.name.singular + ".",
                                        type: "warning",
                                        showCancelButton: true,
                                        cancelButtonText: "No",
                                        confirmButtonColor: "#039BE5",
                                        confirmButtonText: "Yes",
                                        closeOnConfirm: false
                                    },
                                    function () {
                                        NProgress.start();
                                        $.ajax({
                                            url: endpoint + "?mode=3",
                                            type: 'PUT',
                                            data: data
                                        }).done(function () {
                                            App.table.row(row).data(data).draw();
                                            swal({
                                                title: App.fn.capitalize(config.name.singular) +
                                                " " +
                                                action +
                                                "d.",
                                                type: "success",
                                                timer: 1000,
                                                showConfirmButton: false
                                            });
                                        })
                                            .fail(function () {
                                                swal({
                                                    title: App.fn.capitalize(config.name.singular) +
                                                    " " +
                                                    action +
                                                    "d.",
                                                    text: "There was an error updating the " +
                                                    config.name.singular +
                                                    ".",
                                                    type: "error",
                                                    showConfirmButton: true
                                                });
                                            })
                                            .always(function () {
                                                NProgress.done();
                                            });
                                    });
                            });
                    }).on('responsive-display.dt', function (e, datatable, row, showHide, update) {
                    if (showHide !== true)
                        return;

                    App.fn.tables.registerEvents(container, endpoint, config);
                });

                var total = config.summary === true ? config.columns.length : config.columns.length - 1;
                var columns = [];
                for (var j = 0; j < total; j++) {
                    columns[j] = j;
                }
                App.table = $(container).DataTable({
                    dom: "<'row'<'col-sm-6'lB><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    buttons: [
                        {
                            extend: "copyHtml5",
                            text:
                                '<i class="fa fa-files-o fa-md" data-toggle="tooltip" data-placement="top" title="Copy"></i>',
                            titleAttr: "Copy",
                            exportOptions: {
                                columns: columns,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (data.indexOf("fa-toggle-on") !== -1) {
                                            return data.indexOf("fa-rotate-180") !== -1 ? "No" : "Yes";
                                        }
                                        if (data.indexOf("<a") !== -1) {
                                            $(data).replaceWith(function () {
                                                data = $(this).text();
                                            });
                                        }
                                        return data;
                                    }
                                }
                            }
                        },
                        {
                            extend: "excelHtml5",
                            text:
                                '<i class="fa fa-file-excel-o fa-md m-l-1" data-toggle="tooltip" data-placement="top" title="Excel"></i>',
                            titleAttr: "Excel",
                            exportOptions: {
                                orientation: "landscape",
                                pageSize: "LETTER",
                                columns: columns,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (data.indexOf("fa-toggle-on") !== -1) {
                                            return data.indexOf("fa-rotate-180") !== -1 ? "No" : "Yes";
                                        }
                                        if (data.indexOf("<a") !== -1) {
                                            $(data).replaceWith(function () {
                                                data = $(this).text();
                                            });
                                        }
                                        return data;
                                    }
                                }
                            }
                        },
                        {
                            extend: "csvHtml5",
                            text:
                                '<i class="fa fa-file-text-o fa-md m-l-1" data-toggle="tooltip" data-placement="top" title="CSV"></i>',
                            titleAttr: "CSV",
                            exportOptions: {
                                orientation: "landscape",
                                pageSize: "LETTER",
                                columns: columns,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (data.indexOf("fa-toggle-on") !== -1) {
                                            return data.indexOf("fa-rotate-180") !== -1 ? "No" : "Yes";
                                        }
                                        if (data.indexOf("<a") !== -1) {
                                            $(data).replaceWith(function () {
                                                data = $(this).text();
                                            });
                                        }
                                        return data;
                                    }
                                }
                            }
                        },
                        {
                            extend: "pdfHtml5",
                            text:
                                '<i class="fa fa-file-pdf-o fa-md m-l-1" data-toggle="tooltip" data-placement="top" title="PDF"></i>',
                            titleAttr: "PDF",
                            orientation: "landscape",
                            pageSize: "LETTER",
                            exportOptions: {
                                columns: columns,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (data.indexOf("fa-toggle-on") !== -1) {
                                            return data.indexOf("fa-rotate-180") !== -1 ? "No" : "Yes";
                                        }
                                        if (data.indexOf("<a") !== -1) {
                                            $(data).replaceWith(function () {
                                                data = $(this).text();
                                            });
                                        }
                                        return data;
                                    }
                                }
                            }
                        },
                        {
                            extend: "print",
                            text:
                                '<i class="fa fa-print fa-md m-l-1" data-toggle="tooltip" data-placement="top" title="Print"></i>',
                            titleAttr: "PRINT",
                            exportOptions: {
                                orientation: "landscape",
                                pageSize: "LETTER",
                                columns: columns,
                                format: {
                                    body: function (data, row, column, node) {
                                        if (data.indexOf("fa-toggle-on") !== -1) {
                                            return data.indexOf("fa-rotate-180") !== -1 ? "No" : "Yes";
                                        }
                                        if (data.indexOf("<a") !== -1) {
                                            $(data).replaceWith(function () {
                                                data = $(this).text();
                                            });
                                        }
                                        return data;
                                    }
                                }
                            }
                        }
                    ],
                    fixedHeader: true,
                    processing: false,
                    responsive: true,
                    autoWidth: false,
                    lengthMenu: [[1, 10, 25, 50, 100, -1], [1, 10, 25, 50, 100, "All"]],
                    displayLength: 25,
                    footerCallback: function (row, data, start, end, display) {
                        if (data && data.length === 0)
                            return;
                        try {
                            var api = this.api();
                            var total = $(container + " thead th").length;
                            var footer2 = $(container).find("tfoot tr:nth-child(2)");
                            for (var i = 2; i < total; i++) {
                                var pageSum = api
                                    .column(i, { page: "current" })
                                    .data()
                                    .reduce(function (a, b) {
                                        return a + b;
                                    }, 0);

                                var sum = api
                                    .column(i)
                                    .data()
                                    .reduce(function (a, b) {
                                        return a + b;
                                    }, 0);
                                var title = $(container).find("thead th:nth-child(" + (i + 1) + ")").text();
                                $(api.column(i).footer(0)).html(App.Functions.Formatting.FormatFooter(title, pageSum, i));
                                footer2.find("td:nth-child(" + (i) + ")").html(App.Functions.Formatting.FormatFooter(title, sum, i));
                            }
                        } catch (e) {

                        }
                    },
                    order: config.order || [[1, "asc"]],
                    ajax: config.ajax,
                    rowId: config.rowId || "Id",
                    columns: config.columns,
                    language: {
                        info: "Showing _START_ to _END_ of _TOTAL_ " + config.name.plural,
                        infoEmpty: "",
                        emptyTable: "There are no " + config.name.plural + " available.",
                        lengthMenu: "Display _MENU_ " + config.name.plural + "",
                        processing: '<div class="loader"></div>',
                        paginate: {
                            previous: "<i class='fa fa-caret-left'></i>",
                            next: "<i class='fa fa-caret-right'></i>"
                        }
                    }
                });
            }
        }
    },
    Options: {
        GetName: function (value, array) {
            for (var i = 0; i < array.length; i++) {
                if (array[i].v === value)
                    return array[i].t;
            }
            return "";
        },
        GetValue: function (text, array) {
            text = String(text).toLowerCase().trim();
            for (var i = 0; i < array.length; i++) {
                if (array[i].t === text)
                    return array[i].v;
            }
            return "";
        },
        Formats: [{ v: "0", t: "xml" }, { v: "1", t: "json" }, { v: "2", t: "qs" }, { v: "3", t: "text" }, { v: "4", t: "urlencodedform" }],
        Operators: [{ v: 0, t: "Not" }, { v: 1, t: "And" }, { v: 2, t: "Or" }, { v: 3, t: "Xor" }, { v: 4, t: "Implies" }, { v: 5, t: "Equivalent" }, { v: 6, t: "Null" }],
        Comparison: [{ v: 0, t: "Is Equal To" }, { v: 1, t: "Is Higher Than" }, { v: 2, t: "Is Higher Or Equal Than" }, { v: 3, t: "Is Lower Than" }, { v: 4, t: "Is Lower Or Equal Than" }, { v: 5, t: "Is Not Equal To" }, { v: 6, t: "Contains" }, { v: 7, t: "Does Not Contain" }, { v: 8, t: "Starts With" }, { v: 9, t: "Ends With" }],
        DataTypes: [{ v: 0, t: "String" }, { v: 1, t: "Numeric" }, { v: 2, t: "DateTime" }, { v: 3, t: "Boolean" }, { v: 4, t: "Enumeration" }]
    },
    SaveHistory: function (container, url) {
        history.pushState({ id: container, url: url }, null, "#" + url.substring(1));
    },
    Navigate: function (container, url, saveHistory) {
        if (container === "main") {
            this.Current = url;
        }
        $("div.tooltip").tooltip("hide");
        NProgress.start();
        $("#" + container).empty();
        $("#" + container).load(url, "", function (response, status, xhr) {
            NProgress.done();
            if (status === "success") {
                if (saveHistory)
                    history.pushState({ id: container, url: url }, null, "#");
            }
        });
    },
    Functions: {
        CreateTooltips() {
            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip();
            }, 500);
        },
        Formatting: {
            TableTitle: function (container, summary) {
                var title = "Engage: ";
                if (container.indexOf("source") !== -1) {
                    title += "Sources";
                    if (summary === true) {
                        title += " - Summary";

                }
                return title;
            },
            FormatFooter(column, value, index) {
                column = column.trim();
                var numFormat = $.fn.dataTable.render.number(",", ".", 0).display;
                var curFormat = $.fn.dataTable.render.number(",", ".", 2, "$").display;
                if (column === "%") {
                    return " - ";
                }
                if (column === "Revenue" || column === "Cost" || column === "Profit" || column === "Ecpm") {
                    return curFormat(value);
                }
                return numFormat(value);
            },
            FormatCurrency: function (value) {
                if (typeof value === "string" || value instanceof String)
                    value = parseFloat(value);
                return "$" + value.toFixed(2);
            },
            GetDayName: function (value) {
                if (typeof value === "string" || value instanceof String) {
                    value = parseInt(value);
                }

                switch (value) {
                    case 0: { return "Sunday"; }
                    case 1: { return "Monday"; }
                    case 2: { return "Tuesday"; }
                    case 3: { return "Wednesday"; }
                    case 4: { return "Thursday"; }
                    case 5: { return "Friday"; }
                    case 6: { return "Saturday"; }
                    default:
                        return "";
                }
            },
            GetFormatName: function (value) {
                switch (value) {
                    case 0: { return "XML"; }
                    case 1: { return "JSON"; }
                    case 2: { return "Query String"; }
                    case 3: { return "Text"; }
                    case 4: { return "Form-urlencoded"; }
                    default:
                        return "";
                }
            },
            GetMethodName: function (value) {
                switch (value) {
                    case 0: { return "GET"; }
                    case 1: { return "POST"; }
                    case 2: { return "PUST"; }
                    case 3: { return "DELETE"; }
                    default:
                        return "";
                }
            },
            GetResponseName: function (value) {
                switch (value) {
                    case 0: { return "XML"; }
                    case 1: { return "JSON"; }
                    case 2: { return "Query String"; }
                    case 3: { return "Text"; }
                    case 4: { return "Form-urlencoded"; }
                    default:
                        return "";
                }
            }
        },
        GetTabConfig: function () {
            var self = this;

        },
        ClickTab: function (config) {

        }
    }
}


var lineChartData = {
    labels: ["Mon", "Tue", "Wed", "Thu", "Fri"],
    datasets: [
        {
            label: "Simply Jobs",
            borderColor: '#43A047 ',
            backgroundColor: '#43A047 ',
            fill: false,
            data: [
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor()
            ]
        },
        {
            label: "Health Market Advisor",
            borderColor: '#039BE5 ',
            backgroundColor: '#039BE5 ',
            fill: false,
            data: [
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor()

            ]
        },
        {
            label: "VA Guaranteed Loans",
            borderColor: '#373a3c',
            backgroundColor: '#373a3c',
            fill: false,
            data: [
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor(),
                randomScalingFactor()
            ]
        }]
};

function GetChartData(borderColor, bgColor, isTransparent, zeroStart) {
    return {
        labels: ["", "Mon", "Tue", "Wed", "Thu", "Fri"],
        datasets: [
            {
                label: " ",
                borderColor: borderColor,
                backgroundColor: isTransparent ? transparentize2(bgColor) : bgColor,
                fill: bgColor ? true : false,
                data: [
                    zeroStart ? 0 : randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor(),
                    randomScalingFactor()
                ]
            }
        ]
    }
}

Chart.defaults.global.tooltips.displayColors = false;


function InitCharts() {
    var ctx1 = document.getElementById("canvas1").getContext("2d");
    var ctx2 = document.getElementById("canvas2").getContext("2d");
    var ctx3 = document.getElementById("canvas3").getContext("2d");
    var ctx4 = document.getElementById("canvas4").getContext("2d");
    var ctx5 = document.getElementById("canvas5").getContext("2d");
    var ctx6 = document.getElementById("canvas6").getContext("2d");
    var ctx7 = document.getElementById("canvas7").getContext("2d");
    var ctx11 = document.getElementById("canvas11").getContext("2d");
    var ctx12 = document.getElementById("canvas12").getContext("2d");
    var ctx13 = document.getElementById("canvas13").getContext("2d");
    window.Chart1 = Chart.Line(ctx1, {
        data: lineChartData,
        options: {
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: {
                display: false
            },
            //elements: {
            //    line: {
            //        tension: 0 // disables bezier curves
            //    }
            //}
            scales: {
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: true,
                    position: "left",
                    id: "y-axis-1"
                }, {
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: true,
                    position: "right",
                    id: "y-axis-3",

                    // grid line settings
                    gridLines: {
                        drawOnChartArea: false // only want the grid lines for one axis to show up
                    }
                }]
            }
        }
    });
    window.Chart2 = Chart.Line(ctx2, {
        data: GetChartData('#039BE5 ', '#039BE5', true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            //elements: {
            //    line: {
            //        tension: 0 // disables bezier curves
            //    }
            //}
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart3 = Chart.Line(ctx3, {
        data: GetChartData('#039BE5', '#039BE5', true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart4 = Chart.Line(ctx4, {
        data: GetChartData('#039BE5', '#039BE5', true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart5 = Chart.Line(ctx5, {
        data: GetChartData('#039BE5', '#039BE5', true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart6 = Chart.Line(ctx6, {
        data: GetChartData('#039BE5', '#039BE5', true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart7 = Chart.Line(ctx7, {
        data: GetChartData('#039BE5', '#039BE5', true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart11 = Chart.Line(ctx11, {
        data: GetChartData('darkgreen', null, false, true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart12 = Chart.Line(ctx12, {
        data: GetChartData('darkgreen', null, false, true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });
    window.Chart13 = Chart.Line(ctx13, {
        data: GetChartData('#c3491c', null, false, true),
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            hoverMode: 'index',
            stacked: false,
            title: { display: false },
            scales: {
                xAxes: [{ display: false }],
                yAxes: [{
                    type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    display: false,
                    position: "left",
                    id: "y-axis-1"
                }]
            }
        }
    });

    $('#datatable1').DataTable({
        "createdRow": function (row, data, index) {
            if (data[5].indexOf('-') !== -1) {
                $('td', row).eq(5).append(' <i class="fa fa-caret-down text-danger"> </i>');
            }
            else {
                $('td', row).eq(5).append(' <i class="fa fa-caret-up text-success"> </i>');
            }
        },
        "lengthMenu": [5, 10, 25, 50, 100],
        "data": [
            [
                "Addiction Advisor",
                "16,341",
                ".45",
                ".23",
                "53.2%",
                "+6%"
            ],
            [
                "St. Leo University",
                "1,024",
                ".78",
                ".65",
                "43.6%",
                "+1%"
            ],
            [
                "Simply Jobs",
                "123,234",
                ".83",
                ".42",
                "44.6%",
                "+2%"
            ],
            [
                "Health Market Advisor",
                "12,234",
                "1.04",
                "1.12",
                "54.5%",
                "-2%"
            ],
            [
                "Classes and Careers",
                "1,234",
                ".64",
                ".33",
                "39.6%",
                "+4%"
            ],
            [
                "Addiction Advisor",
                "9,222",
                ".36",
                ".23",
                "34.6%",
                "-1%"
            ]
        ]
    });

    $('#datatable2').DataTable({
        "createdRow": function (row, data, index) {
            if (data[2].indexOf('-') !== -1) {
                $('td', row).eq(2).append(' <i class="fa fa-caret-down text-danger"> </i>');
            }
            else {
                $('td', row).eq(2).append(' <i class="fa fa-caret-up text-success"> </i>');
            }
        },
        "lengthMenu": [5, 10, 25, 50, 100],
        "data": [
            [
                "Simply Jobs",
                "123,244",
                "9%"
            ],
            [
                "Health Market Advisor",
                "2,662",
                "12%"
            ],
            [
                "Classes and Careers",
                "13,214",
                "-2%"
            ],
            [
                "VA Guaranteed Loans",
                "7,321",
                "4%"
            ],
            [
                "Classes and Careers",
                "932",
                "12%"
            ]
        ],
        fnDrawCallback: function () {
            $("#datatable2 thead").css("visibility", "hidden");
        }
    });
};

lineChartData.datasets.forEach(function (dataset) {
    dataset.data = dataset.data.map(function () {
        return randomScalingFactor();
    });
});

//window.Chart1.update();

function randomScalingFactor() {
    return Math.round(Math.random() * 100);
};

function transparentize2(color, opacity) {
    var alpha = opacity === undefined ? 0.3 : 1 - opacity;
    return Chart.helpers.color(color).alpha(alpha).rgbString();
}

