(function () {
    'use strict';

    var reportPages = {
        admin_setup_menu: true,
        advert_squares: true,
        books: true,
        buy: true,
        dayshifts: true,
        email_invoice: true,
        email_invoice_history: true,
        export_to_email: true,
        invoicement: true,
        limit_card_mz: true,
        missing_documents: true,
        objects_traning_window: true,
        online_payments: true,
        order_info: true,
        order_inventory: true,
        ppp: true,
        register: true,
        sales: true,
        setup_ppp: true,
        setup_schedule_month_norms: true,
        tech_settings: true,
        tech_timing: true,
        view_money_nomenclatures_overview: true,
        view_states2: true,
        working_card_techs: true
    };

    var supportingPages = {
        add_notification: true,
        admin_salary_total_filter: true,
        admin_set_setup_menu: true,
        advert_squares_add: true,
        import_nomenclatures: true,
        finance_operations_tabs: true,
        set_curent_user_password: true,
        set_export_to_email: true,
        set_invoice_mail_scheme: true,
        set_limit_card_operation: true,
        set_object_schedule_settings: true,
        set_open_fuel_list: true,
        set_ppp_element: true,
        set_schedule_month_norm: true,
        set_setup_books_add: true,
        set_setup_books_del: true,
        set_setup_books_set: true,
        set_setup_contracts: true,
        set_setup_object_singles: true,
        set_setup_object_taxes: true,
        set_setup_object_user: true,
        set_setup_objects: true,
        set_setup_ppp: true,
        set_setup_tech_request: true,
        set_stop_road_list: true,
        set_tech_settings: true,
        set_tech_timing: true,
        setup_request_nomenclature: true,
        setup_schedule_month_norms_main: true,
        setup_visible_tabs: true,
        shifts_count_filter: true,
        states_filter: true,
        states_filter_fields: true,
        tech_planning: true,
        tech_settings: true,
        tech_support_planning: true,
        tech_support_request: true,
        tech_support_requests_filter: true,
        tech_timing: true,
        upload_image: true,
        waste_note: true
    };

    var workspacePages = {
        buy: true,
        order_info: true,
        order_inventory: true,
        ppp: true,
        sales: true,
        view_money_nomenclatures_overview: true,
        view_states2: true
    };

    var iconMap = {
        'images/plus.gif': 'plus',
        'images/confirm.gif': 'check',
        'images/cancel.gif': 'close',
        'images/edit.gif': 'edit',
        'images/erase.gif': 'delete',
        'images/bin.gif': 'delete',
        'images/mright.gif': 'right',
        'images/mleft.gif': 'left',
        'images/search2.gif': 'filter',
        'images/reload.gif': 'refresh',
        'images/cal.gif': 'calendar',
        'images/pdf.gif': 'file-pdf',
        'images/pdf2.gif': 'file-pdf',
        'images/excel.gif': 'file-excel',
        'images/setup.gif': 'settings',
        'images/refresh_ppp.gif': 'refresh',
        'images/history.gif': 'document',
        'images/glyphicons/forw_right.png': 'right',
        'images/glyphicons/forw_left.png': 'left',
        'images/glyphicons/cancel.png': 'close',
        'images/glyphicons/edit2.png': 'edit',
        'images/glyphicons/del2.png': 'delete',
        'images/glyphicons/cal.png': 'calendar',
        'images/glyphicons/refresh.png': 'refresh'
    };

    function normalizedSource(image) {
        var source = image.getAttribute('src') || '';
        var marker = source.toLowerCase().indexOf('images/');
        return marker >= 0 ? source.substring(marker).toLowerCase() : source.toLowerCase();
    }

    function replaceLegacyIcon(image) {
        var icon = iconMap[normalizedSource(image)];
        var replacement;
        var attribute;
        var index;

        if (!icon || !image.parentNode) {
            return;
        }

        replacement = document.createElement('span');
        replacement.className = 'ui-icon ui-icon-' + icon;
        replacement.setAttribute('aria-hidden', 'true');

        for (index = 0; index < image.attributes.length; index += 1) {
            attribute = image.attributes[index];
            if (attribute.name !== 'src' && attribute.name !== 'width' && attribute.name !== 'height'
                && attribute.name !== 'border' && attribute.name !== 'align' && attribute.name !== 'class') {
                replacement.setAttribute(attribute.name, attribute.value);
            }
        }

        if (image.onclick || image.getAttribute('onclick')) {
            replacement.className += ' ui-clickable-icon';
            replacement.removeAttribute('aria-hidden');
            replacement.setAttribute('role', 'button');
            replacement.setAttribute('tabindex', '0');
            replacement.setAttribute('aria-label', image.getAttribute('title') || image.getAttribute('alt') || 'Действие');
            replacement.onkeydown = function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    replacement.click();
                }
            };
        }

        image.parentNode.replaceChild(replacement, image);
    }

    function modernizeIcons(root) {
        var images = root.querySelectorAll ? root.querySelectorAll('img[src]') : [];
        var index;

        for (index = 0; index < images.length; index += 1) {
            replaceLegacyIcon(images[index]);
        }
    }

    function decorateActionButton(button) {
        var text;
        var iconName = '';
        var icon;

        if (button.querySelector('.ui-icon')) {
            button.classList.add('ui-final-action');
            return;
        }

        text = (button.textContent || '').toLowerCase();
        if (/изтрий|премах/.test(text)) {
            iconName = 'delete';
        } else if (/затвори|откаж|отвори/.test(text)) {
            iconName = /отвори/.test(text) ? 'check' : 'close';
        } else if (/запиши|промени|потвърди|деактивирай/.test(text)) {
            iconName = 'save';
        }

        button.classList.add('ui-final-action');
        if (iconName) {
            icon = document.createElement('span');
            icon.className = 'ui-icon ui-icon-' + iconName;
            icon.setAttribute('aria-hidden', 'true');
            button.insertBefore(icon, button.firstChild);
        }
    }

    function installSupportingActionBar(page, form) {
        var buttons;
        var actionBar;
        var firstAction;
        var secondAction;
        var existing;
        var index;

        if (!supportingPages[page] || !form) {
            return;
        }

        existing = form.querySelectorAll('.fixed-bottom, .modal-footer.fixed-bottom, .ui-final-actions');
        if (existing.length) {
            for (index = 0; index < existing.length; index += 1) {
                existing[index].classList.add('ui-final-actions');
                Array.prototype.forEach.call(existing[index].querySelectorAll('button'), decorateActionButton);
            }
            return;
        }

        buttons = form.querySelectorAll('button');
        if (buttons.length < 2) {
            return;
        }

        firstAction = buttons[buttons.length - 2];
        secondAction = buttons[buttons.length - 1];
        actionBar = document.createElement('nav');
        actionBar.className = 'ui-final-actions';
        actionBar.setAttribute('aria-label', 'Действия');
        decorateActionButton(firstAction);
        decorateActionButton(secondAction);
        actionBar.appendChild(firstAction);
        actionBar.appendChild(secondAction);
        form.appendChild(actionBar);
    }

    function modernizeScreen() {
        var page = document.body.getAttribute('data-page') || '';
        var form;
        var result;
        var captions;
        var filters;
        var pageData;
        var fixedActions;
        var index;

        if (!reportPages[page] && !supportingPages[page]) {
            return;
        }

        document.body.classList.add('ui-final-migrated', 'ui-final-page-' + page.replace(/_/g, '-'));
        form = document.getElementById('form1') || document.querySelector('form');

        if (form) {
            form.classList.add('ui-final-screen');
            if (reportPages[page]) {
                form.classList.add(workspacePages[page] ? 'ui-final-workspace' : 'ui-final-report');
            } else {
                form.classList.add('ui-final-editor');
            }
        }

        result = document.getElementById('result');
        if (result) {
            result.classList.add('ui-final-result');
        }

        captions = document.querySelectorAll('.page_caption');
        for (index = 0; index < captions.length; index += 1) {
            captions[index].classList.add('ui-final-heading');
        }

        filters = document.querySelectorAll('table.search');
        for (index = 0; index < filters.length; index += 1) {
            filters[index].classList.add('ui-final-filter');
        }

        pageData = document.querySelectorAll('table.page_data');
        for (index = 0; index < pageData.length; index += 1) {
            pageData[index].classList.add('ui-final-page-data');
        }

        fixedActions = document.querySelectorAll('.fixed-bottom, .modal-footer.fixed-bottom');
        for (index = 0; index < fixedActions.length; index += 1) {
            fixedActions[index].classList.add('ui-final-actions');
        }

        installSupportingActionBar(page, form);
        Array.prototype.forEach.call(document.querySelectorAll('.ui-final-actions button'), decorateActionButton);
        modernizeIcons(document);
    }

    function start() {
        modernizeScreen();

        if (window.MutationObserver && document.body.classList.contains('ui-final-migrated')) {
            new MutationObserver(function (mutations) {
                var index;
                for (index = 0; index < mutations.length; index += 1) {
                    modernizeIcons(mutations[index].target);
                }
            }).observe(document.body, {childList: true, subtree: true});
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
