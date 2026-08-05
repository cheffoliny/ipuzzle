(function (global) {
    "use strict";

    var instance = null;

    function byId(id) {
        return document.getElementById(id);
    }

    function cleanOptionLabel(option) {
        var label = option ? (option.textContent || option.innerText || "") : "";
        return label.replace(/^\s*-+\s*/, "").replace(/\s*-+\s*$/, "") || "Всички състояния";
    }

    function StatusMultiselect(config) {
        this.select = byId(config.selectId);
        this.control = byId(config.controlId);
        this.trigger = byId(config.triggerId);
        this.summary = byId(config.summaryId);
        this.panel = byId(config.panelId);
        this.options = byId(config.optionsId);

        if (!this.select || !this.control || !this.trigger || !this.summary || !this.panel || !this.options) {
            return;
        }

        this.bindEvents();
        this.refresh();
    }

    StatusMultiselect.prototype.isReady = function () {
        return !!this.select;
    };

    StatusMultiselect.prototype.bindEvents = function () {
        var self = this;

        this.trigger.onclick = function (event) {
            if (event && event.preventDefault) {
                event.preventDefault();
            }
            self.toggle();
            return false;
        };

        this.trigger.onkeydown = function (event) {
            event = event || global.event;
            var key = event.key || event.keyCode;
            if (key === "Enter" || key === " " || key === "ArrowDown" || key === 13 || key === 32 || key === 40) {
                if (event.preventDefault) {
                    event.preventDefault();
                }
                if (event.stopPropagation) {
                    event.stopPropagation();
                }
                self.open();
            } else if (key === "Escape" || key === 27) {
                self.close(true);
            }
        };

        this.panel.onkeydown = function (event) {
            event = event || global.event;
            if (event.key === "Escape" || event.keyCode === 27) {
                if (event.preventDefault) {
                    event.preventDefault();
                }
                self.close(true);
            }
        };

        this.select.onchange = function () {
            self.refresh();
        };

        document.addEventListener("mousedown", function (event) {
            if (!self.panel.hidden && !self.control.contains(event.target)) {
                self.close(false);
            }
        }, false);
    };

    StatusMultiselect.prototype.ensureSelection = function () {
        var options = this.select.options;
        var allOption = null;
        var selectedSpecific = 0;

        for (var i = 0; i < options.length; i++) {
            if (String(options[i].value) === "0") {
                allOption = options[i];
            } else if (options[i].selected) {
                selectedSpecific++;
            }
        }

        if (allOption) {
            allOption.selected = selectedSpecific === 0;
        }
    };

    StatusMultiselect.prototype.updateSummary = function () {
        var selected = [];
        var options = this.select.options;

        for (var i = 0; i < options.length; i++) {
            if (options[i].selected) {
                selected.push(cleanOptionLabel(options[i]));
            }
        }

        this.summary.textContent = selected.length ? selected.join(", ") : "Всички състояния";
        this.trigger.title = this.summary.textContent;
    };

    StatusMultiselect.prototype.renderOptions = function () {
        var self = this;
        var nativeOptions = this.select.options;

        while (this.options.firstChild) {
            this.options.removeChild(this.options.firstChild);
        }

        for (var i = 0; i < nativeOptions.length; i++) {
            (function (option, index) {
                var label = document.createElement("label");
                var checkbox = document.createElement("input");
                var text = document.createElement("span");

                label.className = "ui-setup-objects-status-option" + (option.selected ? " is-selected" : "");
                checkbox.type = "checkbox";
                checkbox.checked = option.selected;
                checkbox.setAttribute("data-option-index", index);
                checkbox.setAttribute("aria-label", cleanOptionLabel(option));
                checkbox.onchange = function () {
                    self.changeOption(index, this.checked);
                };
                text.textContent = cleanOptionLabel(option);

                label.appendChild(checkbox);
                label.appendChild(text);
                self.options.appendChild(label);
            }(nativeOptions[i], i));
        }
    };

    StatusMultiselect.prototype.notifyChange = function () {
        if (typeof global.Event === "function") {
            this.select.dispatchEvent(new global.Event("change", { bubbles: true }));
        } else {
            this.refresh();
        }
    };

    StatusMultiselect.prototype.changeOption = function (index, checked) {
        var options = this.select.options;
        var option = options[index];
        if (!option) {
            return;
        }

        if (String(option.value) === "0") {
            for (var i = 0; i < options.length; i++) {
                options[i].selected = String(options[i].value) === "0";
            }
        } else {
            option.selected = checked;
            for (var optionIndex = 0; optionIndex < options.length; optionIndex++) {
                if (String(options[optionIndex].value) === "0") {
                    options[optionIndex].selected = false;
                    break;
                }
            }
            this.ensureSelection();
        }

        this.notifyChange();
    };

    StatusMultiselect.prototype.selectAll = function () {
        var options = this.select.options;
        for (var i = 0; i < options.length; i++) {
            options[i].selected = String(options[i].value) === "0";
        }
        this.notifyChange();
    };

    StatusMultiselect.prototype.refresh = function () {
        if (!this.isReady()) {
            return false;
        }
        this.ensureSelection();
        this.updateSummary();
        this.renderOptions();
        return true;
    };

    StatusMultiselect.prototype.open = function () {
        this.refresh();
        this.panel.hidden = false;
        this.trigger.setAttribute("aria-expanded", "true");
        this.control.classList.add("is-open");

        var checked = this.options.querySelector("input:checked");
        var first = this.options.querySelector("input");
        if (checked && checked.focus) {
            checked.focus();
        } else if (first && first.focus) {
            first.focus();
        }
    };

    StatusMultiselect.prototype.close = function (restoreFocus) {
        this.panel.hidden = true;
        this.trigger.setAttribute("aria-expanded", "false");
        this.control.classList.remove("is-open");
        if (restoreFocus && this.trigger.focus) {
            this.trigger.focus();
        }
    };

    StatusMultiselect.prototype.toggle = function () {
        if (this.panel.hidden) {
            this.open();
        } else {
            this.close(true);
        }
    };

    global.SetupObjectsStatusMultiselect = {
        init: function (config) {
            instance = new StatusMultiselect(config);
            return instance && instance.isReady();
        },
        refresh: function () {
            return instance ? instance.refresh() : false;
        },
        toggle: function () {
            if (instance) {
                instance.toggle();
            }
        },
        close: function (restoreFocus) {
            if (instance) {
                instance.close(!!restoreFocus);
            }
        },
        selectAll: function () {
            if (instance) {
                instance.selectAll();
            }
        }
    };
}(window));
