(function (global) {
    "use strict";

    function elementChildren(node, tagName) {
        var result = [];
        var children = node ? node.childNodes : [];
        var expected = tagName ? tagName.toLowerCase() : null;

        for (var i = 0; i < children.length; i++) {
            if (children[i].nodeType === 1 && (!expected || children[i].nodeName.toLowerCase() === expected)) {
                result.push(children[i]);
            }
        }

        return result;
    }

    function firstChild(node, tagName) {
        var children = elementChildren(node, tagName);
        return children.length ? children[0] : null;
    }

    function nodeText(node) {
        return node ? (node.textContent || "") : "";
    }

    function childText(node, tagName) {
        return nodeText(firstChild(node, tagName));
    }

    function createElement(tagName, attributes, text) {
        var node = document.createElement(tagName);
        var name;

        attributes = attributes || {};
        for (name in attributes) {
            if (Object.prototype.hasOwnProperty.call(attributes, name) && attributes[name] !== null && attributes[name] !== undefined) {
                node.setAttribute(name, attributes[name]);
            }
        }

        if (text !== null && text !== undefined) {
            node.appendChild(document.createTextNode(text));
        }

        return node;
    }

    function copyAttributes(source, target) {
        if (!source || !source.attributes) {
            return;
        }

        for (var i = 0; i < source.attributes.length; i++) {
            target.setAttribute(source.attributes[i].name, source.attributes[i].value);
        }
    }

    function prepareTechPlanningScheduleCell(cell, rowIndex) {
        var background = cell.style.getPropertyValue("background");
        var backgroundColor = cell.style.getPropertyValue("background-color");
        var planningCall = cell.getAttribute("onclick") || "";
        var planningCellId = cell.id ? cell.id.split(",") : [];

        // bootstrap-intelli.css applies a generic !important background to every
        // result cell. Promote the schedule's own background so free, selected,
        // leave and already planned slots keep their legacy colours.
        if (background) {
            cell.style.setProperty("background", background, "important");
        } else if (backgroundColor) {
            cell.style.setProperty("background-color", backgroundColor, "important");
        } else {
            cell.style.setProperty("background-color", rowIndex % 2 === 0 ? "#FFFFFF" : "#F0F0F0", "important");
        }

        if (/^\s*planning\s*\(/i.test(planningCall) && planningCellId.length === 3) {
            cell.className = (cell.className ? cell.className + " " : "") + "planning-slot";
            (function (person, column, row) {
                cell.onclick = function () {
                    if (typeof global.planning === "function") {
                        global.planning(person, column, row);
                    }
                };
            }(planningCellId[0], planningCellId[1], planningCellId[2]));
        }
    }

    function appendIcon(parent, className) {
        parent.appendChild(createElement("i", { "class": className, "aria-hidden": "true" }));
    }

    function optionFromContainer(container, attributeName, fallback) {
        var value = container ? container.getAttribute(attributeName) : null;
        return value ? value : fallback;
    }

    function defaultOption(defaults, name, fallback) {
        return defaults[name] !== undefined && defaults[name] !== null ? defaults[name] : fallback;
    }

    function getOptions(container, defaults) {
        defaults = defaults || {};

        return {
            profile: defaultOption(defaults, "profile", "general"),
            prefix: optionFromContainer(container, "rpc_prefix", defaultOption(defaults, "prefix", "")),
            resultArea: defaultOption(defaults, "resultArea", container ? container.id : "result"),
            resize: optionFromContainer(container, "rpc_resize", defaultOption(defaults, "resize", "on")),
            actionScript: optionFromContainer(container, "rpc_action_script", defaultOption(defaults, "actionScript", "")),
            excelPanel: optionFromContainer(container, "rpc_excel_panel", defaultOption(defaults, "excelPanel", "on")),
            paging: optionFromContainer(container, "rpc_paging", defaultOption(defaults, "paging", "on")),
            editReport: optionFromContainer(container, "rpc_edit_report", defaultOption(defaults, "editReport", "off")),
            invoiceToolbar: optionFromContainer(container, "rpc_invoice_toolbar", defaultOption(defaults, "invoiceToolbar", "off")),
            adminInvoiceToolbar: optionFromContainer(container, "rpc_admin_invoice_toolbar", defaultOption(defaults, "adminInvoiceToolbar", "off")),
            invoiceServicesToolbar: optionFromContainer(container, "rpc_invoice_services_toolbar", defaultOption(defaults, "invoiceServicesToolbar", "off")),
            transferObjects: container && container.hasAttribute("rpc_transfer_objects") ? "on" : "off",
            autonumber: optionFromContainer(container, "rpc_autonumber", defaultOption(defaults, "autonumber", "on")),
            offset: optionFromContainer(container, "rpc_offset", defaultOption(defaults, "offset", 0))
        };
    }

    var supportedProfiles = {
        general: true,
        techPlanningRequest: true,
        techPlanningSchedule: true,
        limitCardPersons: true,
        personSchedule: true
    };

    function getProfile(profile) {
        return supportedProfiles[profile] ? profile : null;
    }

    function isSupportedProfile(profile) {
        return getProfile(profile) !== null;
    }

    function setRequestId(id) {
        var localInput = document.getElementById("id_request");
        if (localInput) {
            localInput.value = id;
        }

        try {
            var parentInput = global.parent && global.parent.document ? global.parent.document.getElementById("id_request") : null;
            if (parentInput) {
                parentInput.value = id;
            }
        } catch (error) {
            // A cross-origin parent must not prevent the report row from being selected locally.
        }
    }

    function installTechPlanningRequestPointer(options) {
        var prefix = options.prefix;

        global[prefix + "setPointer"] = function (row, rowNumber, action, rowId) {
            if (action !== "click") {
                return;
            }

            var tableBody = document.getElementById(prefix + "tbl_result");
            var rows = tableBody ? tableBody.getElementsByTagName("tr") : [];
            for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
                if (rows[rowIndex].getAttribute("data-rpc-report-row") !== "1") {
                    continue;
                }

                if (rows[rowIndex]._rpcOriginalStyle !== undefined) {
                    rows[rowIndex].style.cssText = rows[rowIndex]._rpcOriginalStyle;
                }

                var cells = rows[rowIndex].cells || [];
                for (var cellIndex = 0; cellIndex < cells.length; cellIndex++) {
                    if (cells[cellIndex]._rpcOriginalStyle !== undefined) {
                        cells[cellIndex].style.cssText = cells[cellIndex]._rpcOriginalStyle;
                    }
                }
            }

            row.style.backgroundColor = "#2c2c61";
            for (var selectedCellIndex = 0; selectedCellIndex < row.cells.length; selectedCellIndex++) {
                row.cells[selectedCellIndex].style.color = "#ffffff";
                row.cells[selectedCellIndex].style.fontWeight = "bold";
            }

            setRequestId(rowId);
        };
    }

    function installReportFunctions(options) {
        var prefix = options.prefix;
        var rowsName = prefix + "r_rows";

        global[rowsName] = [];

        global[prefix + "setPointer"] = function (row, rowNumber, action) {
            var reportRows = global[rowsName];
            if (typeof reportRows[rowNumber] === "undefined") {
                reportRows[rowNumber] = false;
            }

            if (action === "over") {
                if (!reportRows[rowNumber]) {
                    row.style.setProperty("background-color", "#355c7d", "important");
                }
            } else if (action === "out") {
                if (!reportRows[rowNumber]) {
                    row.style.cssText = row._rpcOriginalStyle || "";
                } else {
                    row.style.setProperty("background-color", "#612c2c", "important");
                }
            } else if (action === "click") {
                reportRows[rowNumber] = !reportRows[rowNumber];
                global[prefix + "setPointer"](row, rowNumber, "out");
            }
        };

        if (options.profile === "techPlanningRequest") {
            installTechPlanningRequestPointer(options);
        }

        global[prefix + "xslValidatePage"] = function (currentPage, lastPage) {
            if (currentPage.value < 1 || currentPage.value > lastPage) {
                alert("Недопустим номер на страница!");
                currentPage.focus();
                currentPage.select();
                return false;
            }
            return true;
        };

        global[prefix + "xslLoadDirectXML"] = function (action) {
            global.rpc_prefix = options.prefix;
            global.rpc_result_area = options.resultArea;
            global.rpc_action_script = options.actionScript;
            return global.loadDirect(action !== undefined ? action : "result");
        };

        global[prefix + "xslLoadXML"] = function (action) {
            global.rpc_prefix = options.prefix;
            global.rpc_result_area = options.resultArea;
            global.rpc_action_script = options.actionScript;
            return global.loadXMLDoc(action !== undefined ? action : "result");
        };

        global[prefix + "SortField"] = function (fieldName) {
            var sortField = document.getElementById(prefix + "sfield");
            var sortType = document.getElementById(prefix + "stype");

            if (sortField.value === fieldName) {
                sortType.value = sortType.value == 1 ? 0 : 1;
            } else {
                sortField.value = fieldName;
                sortType.value = "0";
            }

            return global[prefix + "xslLoadXML"]();
        };

        global.check = function (checkPrefix) {
            var inputs = document.getElementsByTagName("input");
            var all = document.getElementById(checkPrefix + "all");
            var checked = all ? all.checked : false;

            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type === "checkbox" && inputs[i].name.substr(0, checkPrefix.length) === checkPrefix) {
                    inputs[i].checked = checked;
                }
            }
            return true;
        };
    }

    function appendPagingLink(parent, prefix, page, text, className) {
        var link = createElement("a", { "class": className || "btn text-primary", href: "#" }, text);
        link.onclick = function () {
            document.getElementById(prefix + "current_page").value = page;
            return global[prefix + "xslLoadXML"]();
        };
        parent.appendChild(link);
    }

    function renderPaging(parent, paging, options) {
        if (options.paging !== "on") {
            return;
        }

        var prefix = options.prefix;
        parent.id = prefix + "paging";
        if (!paging) {
            return;
        }

        var currentPage = parseInt(childText(paging, "current_page"), 10) || 0;
        var lastPage = parseInt(childText(paging, "page_total"), 10) || 0;
        var rowsPerPage = parseInt(childText(paging, "rows_per_page"), 10) || 0;
        var rowsTotal = parseInt(childText(paging, "rows_total"), 10) || 0;
        var rangeStart = (currentPage - 1) * rowsPerPage + 1;
        var rangeEnd = Math.min((currentPage - 1) * rowsPerPage + rowsPerPage, rowsTotal);
        var row = createElement("div", { "class": "row py-1" });
        var summary = createElement("div", { "class": "col-6 col-sm-6 col-lg-6 pt-2" });
        var controls = createElement("div", { "class": "col-6 col-sm-6 col-lg-6" });
        var currentInput = createElement("input", {
            type: "hidden",
            size: "3",
            id: prefix + "current_page",
            name: prefix + "current_page",
            value: currentPage,
            style: "width: 30px; text-align: center; background-color: transparent;"
        });

        currentInput.onkeypress = function (event) {
            return typeof global.formatDigits === "function" ? global.formatDigits(event) : true;
        };
        currentInput.onchange = function () {
            return global[prefix + "xslValidatePage"](this, lastPage);
        };
        summary.appendChild(currentInput);
        summary.appendChild(document.createTextNode("резултати " + rangeStart + " - " + rangeEnd + " от " + rowsTotal));

        if (currentPage > 1) {
            appendPagingLink(controls, prefix, 1, "1");
        }
        if (currentPage > 3) {
            appendPagingLink(controls, prefix, 2, "2");
        }
        if (currentPage > 4) {
            controls.appendChild(document.createTextNode("..."));
        }
        if (currentPage - 2 > 0) {
            appendPagingLink(controls, prefix, currentPage - 1, String(currentPage - 1));
        }

        appendPagingLink(controls, prefix, currentPage, String(currentPage), "btn btn-outline-primary disabled");

        if (lastPage - 2 > currentPage) {
            appendPagingLink(controls, prefix, currentPage + 1, String(currentPage + 1));
            controls.appendChild(document.createTextNode("..."));
        }
        if (lastPage - 1 > currentPage) {
            appendPagingLink(controls, prefix, lastPage - 1, String(lastPage - 1));
        }
        if (currentPage !== lastPage) {
            appendPagingLink(controls, prefix, lastPage, String(lastPage));
        }

        row.appendChild(summary);
        row.appendChild(controls);
        parent.appendChild(row);
    }

    function renderExportPanel(parent, options) {
        if (options.excelPanel !== "on") {
            return;
        }

        var prefix = options.prefix;
        var group = createElement("div", { "class": "btn-group btn-sm" });
        var excel = createElement("button", { "class": "btn btn-sm btn-success", type: "button" });
        var pdf = createElement("button", { "class": "btn btn-sm btn-danger ml-1", type: "button" });

        parent.id = prefix + "result_foother";
        appendIcon(excel, "far fa-file-excel fa-lg");
        excel.appendChild(document.createTextNode("  EXCEL"));
        excel.onclick = function () { return global[prefix + "xslLoadDirectXML"]("export_to_xls"); };

        appendIcon(pdf, "far fa-file-pdf fa-lg");
        pdf.appendChild(document.createTextNode("  PDF"));
        pdf.onclick = function () { return global[prefix + "xslLoadDirectXML"]("export_to_pdf"); };

        group.appendChild(excel);
        group.appendChild(pdf);
        parent.appendChild(group);
    }

    function findActionElement(xml, refId) {
        var actions = xml.getElementsByTagName("action");
        if (!actions.length) {
            return null;
        }

        var elements = actions[0].getElementsByTagName("e");
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].getAttribute("id") === refId) {
                return elements[i];
            }
        }
        return null;
    }

    function actionElementValue(xml, refId, fallback) {
        var element = findActionElement(xml, refId);
        if (!element) {
            return fallback !== undefined ? fallback : "";
        }

        var attributeValue = element.getAttribute("value");
        if (attributeValue !== null) {
            return attributeValue;
        }
        return nodeText(element);
    }

    function renderFieldControl(field, cell, rowId, options, disabled) {
        var data = firstChild(field, "data");
        var templates = elementChildren(data);
        var fragment = document.createDocumentFragment();
        var fieldName = childText(field, "name");
        var cellId = cell ? cell.getAttribute("id") : "";
        var value = nodeText(cell);

        for (var i = 0; i < templates.length; i++) {
            var template = templates[i];
            var tagName = template.nodeName.toLowerCase();

            if (tagName === "select") {
                var select = createElement("select");
                copyAttributes(template, select);
                if (disabled) {
                    select.disabled = true;
                }
                select.name = options.prefix + fieldName + "[" + (cellId || rowId) + "]";
                select.value = value;

                var optionNodes = elementChildren(template, "option");
                for (var optionIndex = 0; optionIndex < optionNodes.length; optionIndex++) {
                    var option = createElement("option");
                    copyAttributes(optionNodes[optionIndex], option);
                    option.appendChild(document.createTextNode(nodeText(optionNodes[optionIndex])));
                    option.selected = value === option.getAttribute("value");
                    select.appendChild(option);
                }
                fragment.appendChild(select);
            } else if (tagName === "input") {
                var input = createElement("input", { "class": "edit" });
                copyAttributes(template, input);
                if (disabled) {
                    input.disabled = true;
                }
                input.name = options.prefix + fieldName + "[" + (cellId || rowId) + "]";
                if (!cellId) {
                    input.id = input.name;
                }
                if (template.getAttribute("type") === "checkbox") {
                    input.className = "clear";
                }
                input.value = value;
                input.checked = value === "1";
                fragment.appendChild(input);
            } else {
                fragment.appendChild(document.createTextNode(nodeText(template)));
            }
        }

        return fragment;
    }

    function iconClass(image) {
        var icons = [
            ["pdf", "ui-icon ui-icon-file-pdf text-danger"],
            ["mail", "ui-icon ui-icon-mail text-primary"],
            ["confirm", "ui-icon ui-icon-check text-success"],
            ["cancel", "ui-icon ui-icon-delete text-danger"],
            ["edit", "ui-icon ui-icon-edit text-dark"],
            ["house", "ui-icon ui-icon-home text-primary"],
            ["dots", "ui-icon ui-icon-more text-secondary"],
            ["minus", "ui-icon ui-icon-minus text-danger"],
            ["plus", "ui-icon ui-icon-plus text-primary"],
            ["search", "ui-icon ui-icon-search text-secondary"],
            ["cal", "ui-icon ui-icon-calendar text-primary"],
            ["excel", "ui-icon ui-icon-file-excel text-success"],
            ["sound", "ui-icon ui-icon-volume text-success"],
            ["info", "ui-icon ui-icon-info text-info"],
            ["delete", "ui-icon ui-icon-delete text-danger"]
        ];

        for (var i = 0; i < icons.length; i++) {
            if (image.indexOf(icons[i][0]) !== -1) {
                return icons[i][1];
            }
        }
        return "";
    }

    function renderButton(field, cell, rowId) {
        var caption = childText(field, "btn");
        var link = childText(field, "link");
        var image = childText(field, "img");
        var button = createElement("button", {
            id: caption ? "b90" : "b25",
            type: "button",
            "class": "btn btn-outline-secondary btn-sm"
        });
        var callId = cell.getAttribute("id") || rowId;
        var cssClass = iconClass(image);

        if (link) {
            button.onclick = function () {
                if (typeof global[link] === "function") {
                    global[link](callId);
                }
                return false;
            };
        }

        if (image) {
            if (cssClass) {
                appendIcon(button, cssClass);
            } else {
                button.appendChild(createElement("img", { src: image, alt: "" }));
            }
        }
        if (caption) {
            if (image) {
                button.appendChild(document.createTextNode(" "));
            }
            button.appendChild(document.createTextNode(caption));
        }
        return button;
    }

    function renderCellContent(xml, field, cell, rowId, options) {
        var fieldType = field.getAttribute("type") || "";
        var fieldName = childText(field, "name");
        var value = nodeText(cell);
        var refId = field.getAttribute("ref") || "";
        var ref = refId ? findActionElement(xml, refId) : null;
        var id = options.prefix + fieldName + "[" + rowId + "]";

        if (fieldType === "select") {
            var select = createElement("select", { id: id, name: id });
            copyAttributes(ref, select);
            var optionsList = elementChildren(ref, "option");
            for (var i = 0; i < optionsList.length; i++) {
                var option = createElement("option");
                copyAttributes(optionsList[i], option);
                option.appendChild(document.createTextNode(nodeText(optionsList[i])));
                option.selected = value === option.getAttribute("value");
                select.appendChild(option);
            }
            select.value = value;
            return select;
        }

        if (fieldType === "text") {
            var input = createElement("input", { type: "text", id: id, name: id, value: value });
            copyAttributes(ref, input);
            input.id = id;
            input.name = id;
            input.value = value;
            return input;
        }

        if (fieldType === "hidden") {
            return createElement("input", { type: "hidden", id: id, name: id, value: value });
        }

        if (fieldType === "span") {
            var span = createElement("span", { id: id, "class": fieldName }, value);
            copyAttributes(ref, span);
            span.id = id;
            span.className = fieldName;
            return span;
        }

        if (firstChild(field, "data")) {
            return renderFieldControl(field, cell, rowId, options, false);
        }

        if (firstChild(field, "btn")) {
            return renderButton(field, cell, rowId);
        }

        var image = childText(field, "img");
        if (image && value !== "0" && value !== "") {
            var imageCssClass = iconClass(image);
            if (imageCssClass) {
                return createElement("i", {
                    "class": imageCssClass,
                    title: value !== "1" ? value : "",
                    "aria-hidden": "true"
                });
            }
            return createElement("img", { src: image, title: value !== "1" ? value : "", alt: "" });
        }

        var linkName = childText(field, "link");
        if (linkName) {
            var link = createElement("a", { href: "#" }, value);
            var callId = cell.getAttribute("id") || rowId;
            link.onclick = function () {
                if (typeof global[linkName] === "function") {
                    global[linkName](callId);
                }
                return false;
            };
            return link;
        }

        return document.createTextNode(value !== "0" ? value : "");
    }

    function renderOperation(title, options) {
        var wrapper = createElement("div", { "class": "col-12 col-sm-12 col-lg-12 my-1 pl-0", id: options.prefix + "operations" });
        var group = createElement("div", { "class": "btn-group input-group-sm" });
        var prepend = createElement("div", { "class": "input-group-prepend" });
        var select = createElement("select", { "class": options.prefix + "form-control", id: options.prefix + "sel", name: options.prefix + "sel" });
        var button = createElement("button", {
            "class": "btn btn-sm btn-dark",
            id: options.prefix + "rpc_btn_action",
            name: options.prefix + "rpc_btn_action",
            type: "button"
        });

        prepend.appendChild(createElement("span", { "class": "far fa-check-square fa-fw", "data-fa-transform": "right-22 down-10" }));
        group.appendChild(prepend);
        group.appendChild(document.createTextNode(title));
        select.appendChild(createElement("option", { value: " " }, " "));
        group.appendChild(select);
        appendIcon(button, "fa fa-check");
        button.appendChild(document.createTextNode(" Изпълни"));
        button.onclick = function () {
            var handler = global[options.prefix + "just_do_it"];
            return typeof handler === "function" ? handler() : false;
        };
        group.appendChild(button);
        wrapper.appendChild(group);
        return wrapper;
    }

    function renderInvoiceToolbar(kind, options) {
        var wrapper = createElement("div");
        var table = createElement("table", { cellspacing: "0", cellpadding: "5" });
        var row = createElement("tr");
        var iconCell = createElement("td", { style: "white-space:nowrap" });
        var selectCell = createElement("td");
        var actionCell = createElement("td");
        var select = createElement("select", {
            id: options.prefix + "rpcInvoiceSelect",
            name: options.prefix + "rpcInvoiceSelect",
            style: kind === "invoice" ? "width:230px" : "width:230px;font-size:11px;"
        });
        var action = createElement("button", {
            id: options.prefix + "rpcBtnInvoiceAction",
            name: options.prefix + "rpcBtnInvoiceAction",
            type: "button",
            style: "width:120px"
        }, kind === "invoice" ? "Подготви ДП" : "изпълни");

        appendIcon(iconCell, "ui-icon ui-icon-check text-success");
        selectCell.appendChild(select);
        action.onclick = function () {
            var handler = global[options.prefix + "rpcInvoiceAction"];
            return typeof handler === "function" ? handler() : false;
        };
        actionCell.appendChild(action);
        row.appendChild(iconCell);
        row.appendChild(selectCell);

        if (kind === "invoice") {
            var refreshCell = createElement("td");
            var refresh = createElement("button", { type: "button", style: "width:20px" });
            appendIcon(refresh, "ui-icon ui-icon-refresh");
            refresh.onclick = function () {
                var handler = global[options.prefix + "rpcBtnInvoiceDo"];
                return typeof handler === "function" ? handler() : false;
            };
            refreshCell.appendChild(refresh);
            row.appendChild(refreshCell);

            var dateCell = createElement("td");
            var dateId = options.prefix + "rpcInvoiceDateID";
            var dateInput = createElement("input", { id: dateId, name: dateId, type: "text", size: "10", maxlength: "10", title: "DD/MM/YYYY" });
            dateInput.onkeypress = function (event) { return global.formatDate(event); };
            var calendar = createElement("span", {
                "class": "ui-icon ui-icon-calendar text-primary",
                role: "button",
                tabindex: "0",
                title: "Изберете дата"
            });
            calendar.onclick = function () { return global.displayCalendarFor(dateId); };
            calendar.onkeypress = function (event) {
                if (event.key === "Enter" || event.key === " ") {
                    return global.displayCalendarFor(dateId);
                }
            };
            dateCell.appendChild(dateInput);
            dateCell.appendChild(calendar);
            row.appendChild(dateCell);
        }

        row.appendChild(actionCell);
        table.appendChild(row);
        wrapper.appendChild(table);
        return wrapper;
    }

    function appendToolRows(tbody, fields, options) {
        for (var i = 0; i < fields.length; i++) {
            var input = firstChild(firstChild(fields[i], "data"), "input");
            if (!input || input.getAttribute("type") !== "checkbox") {
                continue;
            }

            var colspan = fields.length + 1;
            if (input.getAttribute("exception") !== "true") {
                var operationRow = createElement("tr");
                operationRow.appendChild(createElement("td"));
                var operationCell = createElement("td", { colspan: colspan });
                operationCell.appendChild(renderOperation(childText(fields[i], "title"), options));
                operationRow.appendChild(operationCell);
                tbody.appendChild(operationRow);
            }

            var toolbarKinds = [
                [options.invoiceToolbar, "invoice"],
                [options.adminInvoiceToolbar, "admin"],
                [options.invoiceServicesToolbar, "services"]
            ];
            for (var toolbarIndex = 0; toolbarIndex < toolbarKinds.length; toolbarIndex++) {
                if (toolbarKinds[toolbarIndex][0] === "on") {
                    var toolbarRow = createElement("tr");
                    toolbarRow.appendChild(createElement("td"));
                    var toolbarCell = createElement("td", { colspan: colspan });
                    toolbarCell.appendChild(renderInvoiceToolbar(toolbarKinds[toolbarIndex][1], options));
                    toolbarRow.appendChild(toolbarCell);
                    tbody.appendChild(toolbarRow);
                }
            }
        }
    }

    function appendPersonScheduleShiftHidden(parent, name, shiftId, value) {
        var id = name + "[" + shiftId + "]";
        parent.appendChild(createElement("input", {
            type: "hidden",
            id: id,
            name: id,
            value: value || ""
        }));
    }

    function renderPersonScheduleShift(option) {
        var shiftId = option ? (option.getAttribute("id") || "0") : "0";
        var code = option ? (option.getAttribute("code") || "") : "";
        var description = option ? (option.getAttribute("description") || "") : "";
        var from = option ? (option.getAttribute("shiftFromShort") || "") : "";
        var to = option ? (option.getAttribute("shiftToShort") || "") : "";
        var duration = option ? (option.getAttribute("paidDuration") || "") : "";
        var stake = option ? (option.getAttribute("custStake") || "") : "";
        var cell = createElement("td");

        cell.style.display = option ? (option.getAttribute("visible") || "") : "";
        appendPersonScheduleShiftHidden(cell, "shiftStake", shiftId, stake);
        appendPersonScheduleShiftHidden(cell, "shiftDuration", shiftId, option ? option.getAttribute("shiftDuration") : "");
        appendPersonScheduleShiftHidden(cell, "shiftCoefDuration", shiftId, option ? option.getAttribute("shiftCoefDuration") : "00:00");
        appendPersonScheduleShiftHidden(cell, "shiftIsLeave", shiftId, option ? option.getAttribute("shiftIsLeave") : "0");

        var buttonId = "btnShift[" + shiftId + "]";
        var button = createElement("button", {
            type: "button",
            id: buttonId,
            name: buttonId,
            "class": "btnShift btn btn-sm " + (code ? "btn-primary" : "btn-danger") + " mx-2",
            "data-shift-code": code || "#0",
            title: code ? ("[" + code + "] - " + description + "\nНачало: " + from + "\nКрай: " + to + "\nПродължителност: " + duration + "\nСтавка: " + stake) : "Премахни смяната"
        }, code || "\u00a0");
        button.onclick = function (event) {
            return typeof global.onShiftClick === "function" ? global.onShiftClick(event || global.event) : false;
        };
        cell.appendChild(button);

        var tooltip = createElement("div", { id: "btnShiftTooltip[" + shiftId + "]", style: "display: none;" });
        var tooltipTable = createElement("table");
        var rows = [
            ["", (code ? code + " " : "") + description],
            ["начало:", from],
            ["край:", to],
            ["продължителност:", duration],
            ["ставка:", stake]
        ];
        for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
            var row = createElement("tr");
            if (rowIndex === 0) {
                row.appendChild(createElement("td", { colspan: "2" }, rows[rowIndex][1]));
            } else {
                row.appendChild(createElement("td", null, rows[rowIndex][0]));
                row.appendChild(createElement("td", null, rows[rowIndex][1]));
            }
            tooltipTable.appendChild(row);
        }
        tooltip.appendChild(tooltipTable);
        cell.appendChild(tooltip);
        return cell;
    }

    function appendPersonScheduleHeader(table, fields) {
        var header = createElement("tr");
        for (var fieldIndex = 0; fieldIndex < fields.length; fieldIndex++) {
            var field = fields[fieldIndex];
            var fieldName = childText(field, "name");
            var heading = createElement("th");
            copyAttributes(field, heading);
            heading.id = "c[" + fieldName + "]";
            heading.appendChild(document.createTextNode(childText(field, "title")));
            header.appendChild(heading);
        }
        table.appendChild(header);
    }

    function renderPersonScheduleTable(xml, result, options) {
        var fields = elementChildren(firstChild(result, "fields"), "c");
        var rows = elementChildren(firstChild(result, "data"), "r");
        var container = createElement("div", {
            id: "container",
            "class": "result_data",
            style: "height: 380px; overflow-x: auto; overflow-y: auto;"
        });
        var table = createElement("table", { id: "tableResult", "class": "result table table-sm table-dark" });

        appendPersonScheduleHeader(table, fields);
        for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
            var rowNode = rows[rowIndex];
            var rowId = rowNode.getAttribute("id") || "";
            var row = createElement("tr", { id: rowId });
            copyAttributes(rowNode, row);
            row.id = rowId;
            var cells = elementChildren(rowNode, "c");

            for (var fieldIndex = 0; fieldIndex < fields.length; fieldIndex++) {
                var field = fields[fieldIndex];
                var fieldName = childText(field, "name");
                var cellNode = cells[fieldIndex] || null;
                var cell = createElement("td", { id: "c[" + fieldName + "][" + rowId + "]" });
                copyAttributes(cellNode, cell);
                cell.id = "c[" + fieldName + "][" + rowId + "]";
                if (!cellNode || !cellNode.getAttribute("class")) {
                    cell.className = fieldName;
                }

                var clickHandler = cellNode ? (cellNode.getAttribute("onclick") || "") : "";
                if (/^\s*onDayClick\s*\(/i.test(clickHandler)) {
                    cell.onclick = function (event) {
                        return typeof global.onDayClick === "function" ? global.onDayClick(event || global.event) : false;
                    };
                }

                if (fieldName === "shift_hours" && rowId !== "__TOTAL__" && cellNode && nodeText(cellNode)) {
                    var rawHours = cellNode.getAttribute("value");
                    if (rawHours) {
                        cell.innerHTML = rawHours;
                    } else {
                        cell.appendChild(document.createTextNode(nodeText(cellNode)));
                    }
                } else if (cellNode && nodeText(cellNode)) {
                    cell.appendChild(renderCellContent(xml, field, cellNode, rowId, options));
                } else {
                    cell.appendChild(document.createTextNode("\u00a0"));
                }
                row.appendChild(cell);

                var hiddenCell = createElement("td", { style: "display: none;" });
                if (fieldName === "shift_hours" && rowId !== "__TOTAL__") {
                    hiddenCell.appendChild(createElement("input", {
                        type: "hidden",
                        id: "real_hours[" + rowId + "]",
                        name: "real_hours[" + rowId + "]",
                        value: actionElementValue(xml, "real_hours[" + rowId + "]", "")
                    }));
                }
                if (rowId !== "__TOTAL__") {
                    hiddenCell.appendChild(createElement("input", {
                        type: "hidden",
                        id: "sid[" + fieldName + "][" + rowId + "]",
                        value: actionElementValue(xml, "sid[" + fieldName + "][" + rowId + "]", "0")
                    }));
                }
                row.appendChild(hiddenCell);
            }
            table.appendChild(row);
        }
        appendPersonScheduleHeader(table, fields);
        container.appendChild(table);
        return container;
    }

    function personScheduleButton(className, icon, text, handler) {
        var button = createElement("button", { type: "button", "class": className });
        appendIcon(button, icon);
        if (text) {
            button.appendChild(document.createTextNode(" " + text));
        }
        button.onclick = handler;
        return button;
    }

    function renderPersonScheduleToolbar(xml) {
        var toolbar = createElement("div", { "class": "row fixed-bottom px-3 py-2" });
        var printColumn = createElement("div", { "class": "col-2" });
        var printGroup = createElement("div", { "class": "input-group input-group-sm" });
        var prepend = createElement("div", { "class": "input-group-prepend" });
        prepend.appendChild(createElement("span", { "class": "fas fa-print fa-fw", "data-fa-transform": "right-22 down-10", title: "Печат" }));
        printGroup.appendChild(prepend);
        var printType = createElement("select", { "class": "form-control", id: "nIDPrintType", name: "nIDPrintType" });
        printType.appendChild(createElement("option", { value: "1" }, " за офиса "));
        printType.appendChild(createElement("option", { value: "2" }, " за счетоводство "));
        printType.appendChild(createElement("option", { value: "3" }, " в ексел "));
        printGroup.appendChild(printType);
        printGroup.appendChild(personScheduleButton("btn btn-sm btn-secondary", "fas fa-print", "Печат", function () {
            return typeof global.onPrint === "function" ? global.onPrint() : false;
        }));
        printColumn.appendChild(printGroup);
        toolbar.appendChild(printColumn);

        var actionsColumn = createElement("div", { "class": "col-10" });
        var actions = createElement("div", { "class": "input-group input-group-sm ml-1" });
        var objectId = actionElementValue(xml, "nIDObject", "0");
        actions.appendChild(personScheduleButton("btn btn-sm btn-info", "far fa-users", "Служители", function () {
            return typeof global.dialogObjectPersonnelSchedule === "function" ? global.dialogObjectPersonnelSchedule("nID=" + objectId) : false;
        }));
        actions.appendChild(personScheduleButton("btn btn-sm btn-success mx-1", "far fa-sync", "Смяна", function () {
            return typeof global.dialogObjectDuty === "function" ? global.dialogObjectDuty("nID=" + objectId) : false;
        }));
        actions.appendChild(personScheduleButton("btn btn-sm btn-success mx-1", "far fa-save", "Запази", function () {
            return typeof global.onSave === "function" ? global.onSave() : false;
        }));
        actions.appendChild(personScheduleButton("btn btn-sm btn-danger mx-1", "far fa-eraser", "Почисти", function () {
            return typeof global.cleanShifts === "function" ? global.cleanShifts() : false;
        }));
        actions.appendChild(personScheduleButton("btn btn-sm btn-warning mx-1", "far fa-angle-double-left", "", function () {
            return typeof global.invalidate === "function" ? global.invalidate() : false;
        }));
        actions.appendChild(personScheduleButton("btn btn-sm btn-warning mx-1", "far fa-angle-double-right", "", function () {
            return typeof global.validate === "function" ? global.validate() : false;
        }));
        actionsColumn.appendChild(actions);
        toolbar.appendChild(actionsColumn);
        return toolbar;
    }

    function renderPersonSchedule(xml, result, container, options) {
        container.appendChild(createElement("input", { type: "hidden", id: "selectedShift", name: "selectedShift", value: "" }));
        container.appendChild(createElement("input", { type: "hidden", id: "nResultIDObject", name: "nResultIDObject", value: actionElementValue(xml, "nResultIDObject", "0") }));
        container.appendChild(createElement("input", { type: "hidden", id: "nResultYear", name: "nResultYear", value: actionElementValue(xml, "nResultYear", "") }));
        container.appendChild(createElement("input", { type: "hidden", id: "nResultMonth", name: "nResultMonth", value: actionElementValue(xml, "nResultMonth", "") }));
        container.appendChild(createElement("div", { id: "divTitle", "class": "table-light text-center h3 lead position-relative" }, actionElementValue(xml, "divTitle", "")));

        var layout = createElement("table", { "class": "ps_layout" });
        var shiftsLayoutRow = createElement("tr");
        var shiftsLayoutCell = createElement("td", { style: "vertical-align: top;", align: "center" });
        var shiftsTable = createElement("table", { id: "tableShifts", "class": "shifts" });
        var shiftsElement = findActionElement(xml, "object_shifts");
        var shifts = elementChildren(shiftsElement, "option");
        var shiftsRow = createElement("tr");
        for (var shiftIndex = 0; shiftIndex < shifts.length; shiftIndex++) {
            if (shiftIndex > 0 && shiftIndex % 13 === 0) {
                shiftsTable.appendChild(shiftsRow);
                shiftsRow = createElement("tr");
            }
            shiftsRow.appendChild(renderPersonScheduleShift(shifts[shiftIndex]));
        }
        shiftsRow.appendChild(renderPersonScheduleShift(null));
        shiftsTable.appendChild(shiftsRow);
        shiftsLayoutCell.appendChild(shiftsTable);
        shiftsLayoutRow.appendChild(shiftsLayoutCell);
        layout.appendChild(shiftsLayoutRow);

        var scheduleRow = createElement("tr");
        var scheduleCell = createElement("td");
        scheduleCell.appendChild(renderPersonScheduleTable(xml, result, options));
        scheduleRow.appendChild(scheduleCell);
        layout.appendChild(scheduleRow);
        container.appendChild(layout);
        container.appendChild(renderPersonScheduleToolbar(xml));

        var scheduleContainer = document.getElementById("container");
        if (scheduleContainer) {
            scheduleContainer.style.width = (global.innerWidth || document.body.offsetWidth) + "px";
        }
        if (typeof global.onInit === "function") {
            global.onInit();
        }
        return true;
    }

    function renderTable(xml, result, options) {
        var prefix = options.prefix;
        var fieldsNode = firstChild(result, "fields");
        var fields = elementChildren(fieldsNode, "c");
        var data = firstChild(result, "data");
        var rows = elementChildren(data, "r");
        var paging = firstChild(result, "paging");
        var currentPage = parseInt(childText(paging, "current_page"), 10) || 1;
        var rowsPerPage = parseInt(childText(paging, "rows_per_page"), 10) || 0;
        var pageStart = paging && firstChild(paging, "current_page") ? (currentPage - 1) * rowsPerPage : 0;
        var specializedPlanning = options.profile === "techPlanningRequest";
        var planningSchedule = options.profile === "techPlanningSchedule";
        var limitCardPersons = options.profile === "limitCardPersons";
        var containerClass = planningSchedule ? "w-100 pt-1 result_data" : (limitCardPersons ? "result_data" : (specializedPlanning ? "container-fluid body-content" : "container-fluid body-content pb-5"));
        var tableClass = planningSchedule ? "result table-sm w-100 table-borderless mt-1" : (limitCardPersons ? "result" : (specializedPlanning ? "table table-sm table-striped table-dark" : "table table-sm table-striped table-dark mb-5"));
        var containerId = limitCardPersons ? prefix + "container" : prefix + "result_data";
        var tableId = limitCardPersons ? prefix + "tableResult" : (planningSchedule ? prefix + "tbl_result" : null);
        var container = createElement("div", { id: containerId, "class": containerClass });
        var table = createElement("table", { id: tableId, "class": tableClass });
        var tbody = createElement("tbody", { id: planningSchedule || limitCardPersons ? null : prefix + "tbl_result" });
        if (planningSchedule) {
            container.style.overflow = "auto";
        } else if (limitCardPersons) {
            container.style.height = "150px";
            container.style.overflowX = "auto";
            container.style.overflowY = "auto";
            table.setAttribute("cellspacing", "0");
        }
        var title = firstChild(result, "title");
        var titleRows = elementChildren(title, "r");

        for (var titleIndex = 0; titleIndex < titleRows.length; titleIndex++) {
            var titleRow = createElement("tr", { "class": planningSchedule ? null : "bg-primary text-center intelliheader" });
            var titleCells = elementChildren(titleRows[titleIndex], "c");
            for (var titleCellIndex = 0; titleCellIndex < titleCells.length; titleCellIndex++) {
                var heading = createElement("th");
                copyAttributes(titleCells[titleCellIndex], heading);
                heading.appendChild(document.createTextNode(nodeText(titleCells[titleCellIndex]) === " " ? "\u00a0" : nodeText(titleCells[titleCellIndex])));
                titleRow.appendChild(heading);
            }
            tbody.appendChild(titleRow);
        }

        var header = createElement("tr", { id: limitCardPersons ? null : prefix + "main", "class": planningSchedule || limitCardPersons ? null : "bg-primary intelliheader" });
        if (options.autonumber === "on" && !limitCardPersons) {
            header.appendChild(createElement("th", null, "#"));
        }
        var sortFieldValue = childText(paging, "sfield");
        var sortTypeValue = childText(paging, "stype");
        for (var fieldIndex = 0; fieldIndex < fields.length; fieldIndex++) {
            var field = fields[fieldIndex];
            var fieldName = childText(field, "name");
            var fieldHeader = createElement("th");
            copyAttributes(field, fieldHeader);
            fieldHeader.title = childText(field, "title");
            if (field.getAttribute("type") === "hidden") {
                fieldHeader.style.display = "none";
            }

            var caption = limitCardPersons ? (childText(field, "title") || childText(field, "caption")) : childText(field, "caption");
            if (paging && firstChild(paging, "sfield")) {
                var sortLink = createElement("a", { href: "#" }, caption);
                (function (name) {
                    sortLink.onclick = function () { return global[prefix + "SortField"](name); };
                }(fieldName));
                fieldHeader.appendChild(sortLink);
            } else {
                fieldHeader.appendChild(document.createTextNode(caption));
            }

            if (sortFieldValue === fieldName) {
                fieldHeader.className = "active";
                fieldHeader.appendChild(document.createTextNode("\u00a0"));
                appendIcon(fieldHeader, sortTypeValue === "1" ? "fas fa-caret-up pull-right" : "fas fa-caret-down pull-right");
            }
            header.appendChild(fieldHeader);
        }
        tbody.appendChild(header);

        var total = firstChild(result, "total");
        if (total) {
            var totalRow = createElement("tr", { "class": planningSchedule ? "total" : "bg-seccess" });
            var totalCells = elementChildren(total, "c");
            for (var totalIndex = 0; totalIndex < totalCells.length; totalIndex++) {
                totalRow.appendChild(createElement("td", null, nodeText(totalCells[totalIndex])));
            }
            tbody.appendChild(totalRow);
        }

        if (rows.length && options.editReport === "on") {
            var newRow = createElement("tr", { id: prefix + "new_row", style: "visibility: hidden; display: none;" });
            var statusCell = createElement("td", null, "0");
            statusCell.appendChild(createElement("input", { type: "hidden", value: "new", disabled: "disabled", name: prefix + "status[]" }));
            newRow.appendChild(statusCell);
            var firstRowCells = elementChildren(rows[0], "c");
            for (var newIndex = 0; newIndex < firstRowCells.length; newIndex++) {
                var newCell = createElement("td");
                if (firstChild(fields[newIndex], "data")) {
                    newCell.appendChild(renderFieldControl(fields[newIndex], firstRowCells[newIndex], "", options, true));
                }
                newRow.appendChild(newCell);
            }
            tbody.appendChild(newRow);
        }

        for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
            var rowNode = rows[rowIndex];
            var rowId = rowNode.getAttribute("id") || "";
            var row = createElement("tr");
            copyAttributes(rowNode, row);
            row.setAttribute("data-rpc-report-row", "1");
            if (planningSchedule) {
                row.setAttribute("bgcolor", (rowIndex + 1) % 2 === 0 ? "#F0F0F0" : "white");
            } else if (!limitCardPersons) {
                (function (number, currentRow, currentRowId) {
                    currentRow.onmouseover = function () { global[prefix + "setPointer"](this, number, "over", currentRowId); };
                    currentRow.onmouseout = function () { global[prefix + "setPointer"](this, number, "out", currentRowId); };
                    currentRow.onmousedown = function () { global[prefix + "setPointer"](this, number, "click", currentRowId); };
                }(rowIndex + 1, row, rowId));
            }

            if (options.autonumber === "on" && !limitCardPersons) {
                var numberCell = createElement("td", { align: "right" }, String(rowIndex + 1 + pageStart));
                if (options.editReport === "on") {
                    numberCell.appendChild(createElement("input", { type: "hidden", value: "old", name: prefix + "status[" + rowId + "]" }));
                }
                row.appendChild(numberCell);
            }

            var cells = elementChildren(rowNode, "c");
            for (var cellIndex = 0; cellIndex < cells.length; cellIndex++) {
                var cell = createElement("td");
                var cellField = fields[cellIndex];
                if (!cellField) {
                    continue;
                }
                if (firstChild(cellField, "btn")) {
                    cell.setAttribute("width", childText(cellField, "btn") ? "90" : "25");
                }
                copyAttributes(cells[cellIndex], cell);
                if (limitCardPersons) {
                    cell.id = "c[" + childText(cellField, "name") + "][" + rowId + "]";
                    if (!cells[cellIndex].getAttribute("class")) {
                        cell.className = childText(cellField, "name");
                    }
                }
                if (planningSchedule) {
                    prepareTechPlanningScheduleCell(cell, rowIndex);
                }
                if (cellField.getAttribute("type") === "hidden") {
                    cell.style.display = "none";
                }
                var cellContent = renderCellContent(xml, cellField, cells[cellIndex], rowId, options);
                if (limitCardPersons && !nodeText(cells[cellIndex]) && cellContent.nodeType === 3) {
                    cellContent.nodeValue = "\u00a0";
                }
                cell.appendChild(cellContent);
                row.appendChild(cell);
            }
            row._rpcOriginalStyle = row.style.cssText;
            for (var originalCellIndex = 0; originalCellIndex < row.cells.length; originalCellIndex++) {
                row.cells[originalCellIndex]._rpcOriginalStyle = row.cells[originalCellIndex].style.cssText;
            }
            tbody.appendChild(row);
        }

        if (!limitCardPersons) {
            appendToolRows(tbody, fields, options);
        }
        table.appendChild(tbody);
        container.appendChild(table);
        return container;
    }

    var resizeListeners = {};

    function resizeListenerKey(options) {
        return options.resultArea + "::" + options.prefix;
    }

    function removeResizeListener(options) {
        var key = resizeListenerKey(options);
        var listener = resizeListeners[key];
        var handler = listener && listener.handler ? listener.handler : listener;

        if (handler && global.removeEventListener) {
            global.removeEventListener("resize", handler, false);
        }
        if (listener && listener.observer && typeof listener.observer.disconnect === "function") {
            listener.observer.disconnect();
        }
        delete resizeListeners[key];
    }

    function markResultColumnHeaders(result) {
        if (!result || !result.getElementsByTagName) {
            return 0;
        }

        var tables = result.getElementsByTagName("table");
        var marked = 0;

        for (var tableIndex = 0; tableIndex < tables.length; tableIndex++) {
            var table = tables[tableIndex];
            if (table.getAttribute("data-rpc-sticky-header") === "off") {
                continue;
            }

            var rows = table.rows || [];
            var columnHeader = null;

            // DBResponse may emit one or more report-title rows before the real
            // column captions. The last consecutive row containing TH cells is
            // the column header; totals and data rows start with TD cells.
            for (var rowIndex = 0; rowIndex < rows.length; rowIndex++) {
                var cells = rows[rowIndex].cells || [];
                var hasHeaderCell = false;
                for (var cellIndex = 0; cellIndex < cells.length; cellIndex++) {
                    if (cells[cellIndex].nodeName.toLowerCase() === "th") {
                        hasHeaderCell = true;
                        break;
                    }
                }

                if (hasHeaderCell) {
                    columnHeader = rows[rowIndex];
                } else if (columnHeader) {
                    break;
                }
            }

            if (columnHeader && columnHeader.classList) {
                columnHeader.classList.add("rpc-result-column-header");
                marked++;
            }
        }

        return marked;
    }

    function markResultScrollContainer(result, options) {
        var resultData = document.getElementById(options.prefix + "result_data");

        if (result && result.classList) {
            result.classList.add("rpc-result-scroll-host");
        }
        if (resultData && resultData.classList) {
            resultData.classList.add("rpc-result-scroll-content");
        }
        markResultColumnHeaders(result);

        return resultData;
    }

    function resultViewportBottom(result, viewportHeight) {
        var resultTop = result.getBoundingClientRect ? result.getBoundingClientRect().top : result.offsetTop;
        var bottom = viewportHeight;
        var fixedElements = document.querySelectorAll ? document.querySelectorAll(".fixed-bottom, .rpc-result-toolbar") : [];

        for (var i = 0; i < fixedElements.length; i++) {
            var element = fixedElements[i];
            if (element === result || !element.getBoundingClientRect) {
                continue;
            }

            var rect = element.getBoundingClientRect();
            if (rect.height > 0 && rect.top > resultTop && rect.top < bottom && rect.bottom >= viewportHeight - 2) {
                bottom = rect.top;
            }
        }

        return bottom;
    }

    function installGeneralResultResizer(options) {
        removeResizeListener(options);

        var result = document.getElementById(options.resultArea);
        if (!result) {
            return;
        }

        markResultScrollContainer(result, options);

        // Migrated screens with rpc_resize="off" have their own bounded flex or
        // fixed-height result area. Keep that sizing and only provide the common
        // overflow classes above.
        if (options.resize === "off") {
            if (result.getAttribute("data-rpc-auto-resized") === "1") {
                result.style.removeProperty("max-height");
                result.style.removeProperty("overflow");
                result.removeAttribute("data-rpc-auto-resized");
            }
            return;
        }

        var resize = function () {
            var currentResult = document.getElementById(options.resultArea);
            if (!currentResult) {
                return true;
            }

            markResultScrollContainer(currentResult, options);

            var viewportHeight = global.innerHeight || document.documentElement.clientHeight || document.body.offsetHeight;
            var resultRect = currentResult.getBoundingClientRect ? currentResult.getBoundingClientRect() : null;
            var resultTop = resultRect ? resultRect.top : currentResult.offsetTop;
            var availableBottom = resultViewportBottom(currentResult, viewportHeight);
            var availableHeight = availableBottom - Math.max(resultTop, 0) - 2;

            if (isFinite(availableHeight) && availableHeight > 0) {
                // max-height keeps short reports compact and gives long reports a
                // single automatic vertical/horizontal scrollbar only when needed.
                currentResult.style.setProperty("max-height", Math.max(availableHeight, 30) + "px", "important");
                currentResult.style.setProperty("overflow", "auto", "important");
                currentResult.setAttribute("data-rpc-auto-resized", "1");
            }
            return true;
        };

        resize();
        if (global.addEventListener) {
            var listener = { handler: resize, observer: null };
            global.addEventListener("resize", resize, false);

            if (typeof global.ResizeObserver === "function") {
                listener.observer = new global.ResizeObserver(resize);
                listener.observer.observe(document.documentElement);
            }
            resizeListeners[resizeListenerKey(options)] = listener;
        }
    }

    function installTechPlanningRequestResizer(options) {
        removeResizeListener(options);

        var resize = function () {
            var result = document.getElementById(options.resultArea);
            var resultData = document.getElementById(options.prefix + "result_data");
            if (!result || !resultData) {
                return true;
            }

            markResultScrollContainer(result, options);

            var footer = document.getElementById(options.prefix + "result_foother");
            var paging = document.getElementById(options.prefix + "paging");
            var total = document.getElementById(options.prefix + "result_total");
            var operations = document.getElementById(options.prefix + "result_operations");
            var footerHeight = footer ? footer.offsetHeight : 0;
            var operationsHeight = operations ? operations.offsetHeight : 0;
            var availableHeight;

            if (options.resize === "off") {
                availableHeight = result.offsetHeight - footerHeight -
                    (paging ? paging.offsetHeight : 0) -
                    (total ? total.offsetHeight : 0) - operationsHeight - 2;
            } else {
                var viewportHeight = global.innerHeight || document.documentElement.clientHeight || document.body.offsetHeight;
                availableHeight = viewportHeight - resultData.offsetTop - footerHeight - operationsHeight;
            }

            if (isFinite(availableHeight) && availableHeight > 0) {
                resultData.style.height = Math.max(availableHeight, 30) + "px";
                resultData.style.overflow = "auto";
            }
            return true;
        };

        resize();
        if (options.resize !== "off" && global.addEventListener) {
            resizeListeners[resizeListenerKey(options)] = resize;
            global.addEventListener("resize", resize, false);
        }
    }

    function render(xml, container, options) {
        if (!xml || !xml.documentElement || !container) {
            return false;
        }

        options = options || getOptions(container, {});
        var result = firstChild(xml.documentElement, "result");
        var data = result ? firstChild(result, "data") : null;
        if (!result) {
            return false;
        }

        installReportFunctions(options);
        removeResizeListener(options);
        container.innerHTML = "";
        if (container.classList) {
            container.classList.add("rpc-result-scroll-host");
        }
        container.appendChild(createElement("input", { type: "hidden" }));

        var limitCardPersons = options.profile === "limitCardPersons";
        if (options.profile === "personSchedule") {
            var personScheduleRendered = renderPersonSchedule(xml, result, container, options);
            installGeneralResultResizer(options);
            return personScheduleRendered;
        }

        if (!data || !nodeText(data)) {
            if (options.profile === "techPlanningSchedule") {
                var emptySchedule = createElement("div", { "class": "text-center" });
                emptySchedule.appendChild(createElement("p", { style: "color: red; font-weight: bold;" }, "\u0420\u0435\u0437\u0443\u043b\u0442\u0430\u0442\u044a\u0442 \u0435 \u043f\u0440\u0430\u0437\u0435\u043d!"));
                container.appendChild(emptySchedule);
                installGeneralResultResizer(options);
                return true;
            }
            if (limitCardPersons) {
                container.appendChild(createElement("div", { id: options.prefix + "divTitle" }));
                container.appendChild(createElement("hr"));
                container.appendChild(renderTable(xml, result, options));
                installGeneralResultResizer(options);
                return true;
            }
            var message = createElement("div", { "class": "alert alert-danger alert-dismissable col-sm-11 transparent-half ml-4" });
            message.appendChild(createElement("button", { type: "button", "class": "close", "data-dismiss": "alert", "aria-hidden": "true" }, "×"));
            appendIcon(message, "fas fa-info-circle fa-2x");
            message.appendChild(createElement("h5", null, " Съобщение: "));
            message.appendChild(document.createTextNode("Няма намерени резултати по зададените критерии за търсене."));
            container.appendChild(message);
            installGeneralResultResizer(options);
            return true;
        }

        var paging = firstChild(result, "paging");
        if (!limitCardPersons) {
            container.appendChild(createElement("div", { id: options.prefix + "totals", "class": "total" }));
            container.appendChild(createElement("input", { type: "hidden", id: options.prefix + "sfield", name: options.prefix + "sfield", value: childText(paging, "sfield") }));
            container.appendChild(createElement("input", { type: "hidden", id: options.prefix + "stype", name: options.prefix + "stype", value: childText(paging, "stype") }));
        }

        if (limitCardPersons) {
            container.appendChild(createElement("div", { id: options.prefix + "divTitle" }));
            container.appendChild(createElement("hr"));
            container.appendChild(renderTable(xml, result, options));
        } else if (options.profile === "techPlanningRequest") {
            if (options.paging === "on") {
                var specializedPaging = createElement("div", { "class": "result_paging" });
                renderPaging(specializedPaging, paging, options);
                container.appendChild(specializedPaging);
            }
            container.appendChild(renderTable(xml, result, options));
            if (options.excelPanel === "on") {
                var specializedExport = createElement("div", { "class": "result_foother text-right" });
                renderExportPanel(specializedExport, options);
                container.appendChild(specializedExport);
            }
            installTechPlanningRequestResizer(options);
        } else {
            if (options.paging === "on" || options.excelPanel === "on") {
                var nav = createElement("nav", { "class": "navbar fixed-bottom navbar-expand-lg navbar-dark bg-dark flex-row py-md-0 rpc-result-toolbar" });
                var pagingPanel = createElement("div", { "class": "col-9 col-sm-9 col-lg-9 text-white rpc-result-paging" });
                var exportPanel = createElement("div", { "class": "col-3 col-sm-3 col-lg-3 text-right rpc-result-exports" });
                renderPaging(pagingPanel, paging, options);
                renderExportPanel(exportPanel, options);
                nav.appendChild(pagingPanel);
                nav.appendChild(exportPanel);
                container.appendChild(nav);
            }
            container.appendChild(renderTable(xml, result, options));
            installGeneralResultResizer(options);
        }

        if (limitCardPersons) {
            installGeneralResultResizer(options);
        }
        return true;
    }

    global.RpcResultRenderer = {
        getOptions: getOptions,
        getProfile: getProfile,
        isSupportedProfile: isSupportedProfile,
        prepareStickyHeaders: markResultColumnHeaders,
        render: render
    };
}(window));
