(function initializeSelectControls() {
    'use strict';
    var selectNumber = 0;
    var openControl = null;
    var controls = [];

    // Đóng danh sách và giữ focus khi người dùng dùng bàn phím.
    function closeSelect(control, restoreFocus) {
        control.popup.hidden = true;
        control.wrapper.classList.remove('is-open');
        control.trigger.setAttribute('aria-expanded', 'false');
        if (openControl === control) {
            openControl = null;
        }
        if (restoreFocus) {
            control.trigger.focus();
        }
    }

    function visibleOptions(control) {
        return Array.from(control.options.querySelectorAll('button:not([hidden]):not(:disabled)'));
    }

    // Giá trị vẫn ghi vào select gốc, các module cũ tiếp tục nhận sự kiện change.
    function chooseOption(control, optionIndex) {
        control.select.selectedIndex = optionIndex;
        control.select.dispatchEvent(new Event('change', { bubbles: true }));
        refreshSelect(control);
        closeSelect(control, true);
    }

    function filterOptions(control) {
        var query = control.search.value.trim().toLocaleLowerCase('vi');
        var shown = 0;
        control.options.querySelectorAll('button').forEach(function filterOption(button) {
            button.hidden = !button.textContent.toLocaleLowerCase('vi').includes(query);
            if (!button.hidden) {
                shown += 1;
            }
        });
        control.empty.hidden = shown > 0;
    }

    function moveOptionFocus(control, event) {
        var buttons = visibleOptions(control);
        if (buttons.length === 0) {
            return;
        }
        var currentIndex = buttons.indexOf(document.activeElement);
        var targetIndex = currentIndex;
        if (event.key === 'ArrowDown') {
            targetIndex = Math.min(currentIndex + 1, buttons.length - 1);
        } else if (event.key === 'ArrowUp') {
            targetIndex = Math.max(currentIndex - 1, 0);
        } else if (event.key === 'Home') {
            targetIndex = 0;
        } else if (event.key === 'End') {
            targetIndex = buttons.length - 1;
        } else {
            return;
        }
        event.preventDefault();
        buttons[targetIndex].focus();
    }

    function openSelect(control) {
        if (control.select.disabled) {
            return;
        }
        if (openControl && openControl !== control) {
            closeSelect(openControl, false);
        }
        control.search.value = '';
        filterOptions(control);
        var triggerBounds = control.trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - triggerBounds.bottom - 16;
        var spaceAbove = triggerBounds.top - 16;
        control.popup.style.top = 'calc(100% + 6px)';
        control.popup.style.bottom = 'auto';
        var availableSpace = spaceBelow;
        if (spaceBelow < 260 && spaceAbove > spaceBelow) {
            control.popup.style.top = 'auto';
            control.popup.style.bottom = 'calc(100% + 6px)';
            availableSpace = spaceAbove;
        }
        control.options.style.maxHeight = Math.min(240, Math.max(100, availableSpace - 76)) + 'px';
        control.popup.hidden = false;
        control.wrapper.classList.add('is-open');
        control.trigger.setAttribute('aria-expanded', 'true');
        openControl = control;
        if (!control.search.hidden) {
            control.search.focus();
        } else {
            var buttons = visibleOptions(control);
            var selected = control.options.querySelector('[aria-selected="true"]');
            if (selected && !selected.disabled) {
                selected.focus();
            } else if (buttons.length > 0) {
                buttons[0].focus();
            }
        }
    }

    // Đồng bộ cả khi locations.js thay danh sách phường/xã bằng AJAX.
    function refreshSelect(control) {
        var selected = control.select.options[control.select.selectedIndex];
        control.trigger.textContent = 'Chọn';
        if (selected) {
            control.trigger.textContent = selected.textContent.trim();
        }
        control.trigger.setAttribute('aria-label', control.label + ': ' + control.trigger.textContent);
        control.trigger.disabled = control.select.disabled;
        control.options.textContent = '';
        Array.from(control.select.options).forEach(function buildOption(option, index) {
            if (option.hidden) {
                return;
            }
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'hs-select__option';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', String(index === control.select.selectedIndex));
            button.textContent = option.textContent;
            button.dataset.optionIndex = String(index);
            button.disabled = option.disabled;
            button.addEventListener('click', function onOptionClick(event) {
                event.preventDefault();
                chooseOption(control, index);
            });
            control.options.appendChild(button);
        });
        control.search.hidden = control.select.options.length < 9;
        filterOptions(control);
        if (control.select.disabled) {
            closeSelect(control, false);
        }
    }

    function enhanceSelect(select) {
        if (select.multiple || select.dataset.selectEnhanced === 'true') {
            return;
        }
        select.dataset.selectEnhanced = 'true';
        selectNumber += 1;
        var label = select.getAttribute('aria-label');
        if (!label && select.labels && select.labels.length > 0) {
            var labelCopy = select.labels[0].cloneNode(true);
            labelCopy.querySelectorAll('select').forEach(function removeNativeFromLabel(element) { element.remove(); });
            label = labelCopy.textContent.trim();
        }
        if (!label) {
            label = 'Chọn một mục';
        }
        var wrapper = document.createElement('div');
        wrapper.className = 'hs-select';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.classList.add('hs-select__native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'hs-select__trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-label', label);
        if (select.hasAttribute('aria-describedby')) {
            trigger.setAttribute('aria-describedby', select.getAttribute('aria-describedby'));
        }
        var popup = document.createElement('div');
        popup.className = 'hs-select__popup';
        popup.hidden = true;
        popup.id = 'hs-select-popup-' + selectNumber;
        trigger.setAttribute('aria-controls', popup.id);
        var search = document.createElement('input');
        search.type = 'search';
        search.className = 'hs-select__search';
        search.placeholder = 'Tìm trong danh sách';
        search.setAttribute('aria-label', 'Tìm trong ' + label);
        var options = document.createElement('div');
        options.className = 'hs-select__options';
        options.setAttribute('role', 'listbox');
        options.setAttribute('aria-label', label);
        var empty = document.createElement('p');
        empty.className = 'hs-select__empty';
        empty.textContent = 'Không có lựa chọn phù hợp.';
        empty.setAttribute('role', 'status');
        popup.append(search, options, empty);
        wrapper.append(trigger, popup);
        var control = { label: label, select: select, wrapper: wrapper, trigger: trigger, popup: popup,
            search: search, options: options, empty: empty };
        controls.push(control);
        trigger.addEventListener('click', function onTriggerClick(event) {
            event.preventDefault();
            if (popup.hidden) {
                openSelect(control);
            } else {
                closeSelect(control, false);
            }
        });
        trigger.addEventListener('keydown', function onTriggerKeyDown(event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                openSelect(control);
            }
        });
        wrapper.addEventListener('keydown', function onPopupKeyDown(event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                closeSelect(control, true);
            } else if (event.key === 'Enter' && event.target === search) {
                event.preventDefault();
                var matchingOptions = visibleOptions(control);
                if (matchingOptions.length > 0) {
                    matchingOptions[0].click();
                }
            } else if (event.key === 'Tab') {
                closeSelect(control, false);
            } else if (!popup.hidden) {
                moveOptionFocus(control, event);
            }
        });
        search.addEventListener('input', function onSearchInput() { filterOptions(control); });
        select.addEventListener('change', function onNativeChange() { refreshSelect(control); });
        select.addEventListener('focus', function onNativeFocus() { trigger.focus(); });
        select.addEventListener('invalid', function onNativeInvalid(event) {
            event.preventDefault();
            trigger.focus();
        });
        if (select.form) {
            select.form.addEventListener('reset', function onFormReset() {
                window.setTimeout(function refreshAfterReset() { refreshSelect(control); }, 0);
            });
        }
        var observer = new MutationObserver(function onOptionsMutation() { refreshSelect(control); });
        observer.observe(select, { childList: true, subtree: true, attributes: true });
        refreshSelect(control);
    }

    document.querySelectorAll('select').forEach(enhanceSelect);
    document.addEventListener('hs:refresh-selects', function onRefreshSelects() { controls.forEach(refreshSelect); });
    document.addEventListener('click', function onDocumentClick(event) {
        if (openControl && !openControl.wrapper.contains(event.target)) {
            closeSelect(openControl, false);
        }
    });
})();
