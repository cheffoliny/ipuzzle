(function () {
    'use strict';

    function scrollLeft() {
        return window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0;
    }

    function scrollTop() {
        return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    }

    function viewportWidth() {
        return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    }

    function viewportHeight() {
        return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    }

    function positionBelowInput() {
        var popup = this.mGetCalendarElement();
        var input = this.mGetInputElement ? this.mGetInputElement() : null;
        var trigger = document.getElementById(this.click_element_id);
        var anchor = input || trigger;
        var bounds;
        var left;
        var top;
        var minimum = 5;
        var gap = 6;
        var pageLeft = scrollLeft();
        var pageTop = scrollTop();
        var rightEdge = pageLeft + viewportWidth() - minimum;
        var bottomEdge = pageTop + viewportHeight() - minimum;
        var width;
        var height;
        var above;

        if (!popup || !anchor || !anchor.getBoundingClientRect) {
            return;
        }

        bounds = anchor.getBoundingClientRect();
        width = popup.offsetWidth;
        height = popup.offsetHeight;
        left = bounds.left + pageLeft;
        top = bounds.bottom + pageTop + gap;

        if (left + width > rightEdge) {
            left = Math.max(minimum + pageLeft, rightEdge - width);
        }

        if (top + height > bottomEdge) {
            above = bounds.top + pageTop - height - gap;
            top = above >= pageTop + minimum ? above : Math.max(pageTop + minimum, bottomEdge - height);
        }

        popup.style.left = left + 'px';
        popup.style.top = top + 'px';
    }

    function activateWithKeyboard(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            this.click();
        }
    }

    function prepareTrigger(calendar, trigger) {
        var label;
        var tagName;

        if (!trigger) {
            return;
        }

        label = trigger.getAttribute('aria-label') || trigger.getAttribute('title') || calendar.tool_tip || 'Изберете дата';
        trigger.setAttribute('aria-label', label);
        trigger.removeAttribute('title');
        trigger.classList.add('ui-calendar-trigger');

        tagName = trigger.tagName.toLowerCase();
        if (tagName !== 'button' && tagName !== 'input') {
            trigger.setAttribute('role', 'button');
            if (!trigger.hasAttribute('tabindex')) {
                trigger.setAttribute('tabindex', '0');
            }
            if (trigger.getAttribute('data-calendar-keyboard') !== '1') {
                trigger.addEventListener('keydown', activateWithKeyboard, false);
                trigger.setAttribute('data-calendar-keyboard', '1');
            }
        }
    }

    function initializeCalendarUi() {
        var calendars = window.dlcalendar_aAllCalendars || [];
        var index;
        var calendar;
        var popup;
        var trigger;

        for (index = 0; index < calendars.length; index += 1) {
            calendar = window.dlcalendar_getCalendarObject(calendars[index]);
            if (!calendar) {
                continue;
            }

            popup = calendar.mGetCalendarElement();
            trigger = document.getElementById(calendar.click_element_id);

            if (popup) {
                popup.classList.add('ui-calendar-popup');
            }

            prepareTrigger(calendar, trigger);
            calendar.mPosition = positionBelowInput;
        }
    }

    initializeCalendarUi();
    window.initializeCalendarUi = initializeCalendarUi;
}());
