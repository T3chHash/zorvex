(function () {
    'use strict';

    var PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
    var ARABIC_DIGITS = '٠١٢٣٤٥٦٧٨٩';

    function normalizeDigits(value) {
        var s = String(value == null ? '' : value);
        var out = '';
        for (var i = 0; i < s.length; i++) {
            var ch = s.charAt(i);
            var pIdx = PERSIAN_DIGITS.indexOf(ch);
            var aIdx = ARABIC_DIGITS.indexOf(ch);
            if (pIdx !== -1) out += String(pIdx);
            else if (aIdx !== -1) out += String(aIdx);
            else out += ch;
        }
        return out;
    }

    function rawDigits(value) {
        return normalizeDigits(value).replace(/[^\d]/g, '');
    }

    function groupDigits(digits) {
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function formatValue(value) {
        return groupDigits(rawDigits(value));
    }

    function wireInput(el) {
        if (!el || el.dataset.fxMoneyWired) return;
        el.dataset.fxMoneyWired = '1';
        el.setAttribute('inputmode', 'numeric');
        el.setAttribute('autocomplete', 'off');
        if (el.tagName === 'INPUT' && el.type !== 'text') el.type = 'text';

        if (el.value) el.value = formatValue(el.value);

        el.addEventListener('input', function () {
            var before = el.value;
            var caret = el.selectionStart == null ? before.length : el.selectionStart;
            var digitsBeforeCaret = rawDigits(before.slice(0, caret)).length;

            var formatted = formatValue(before);
            el.value = formatted;

            var pos = 0, seen = 0;
            while (pos < formatted.length && seen < digitsBeforeCaret) {
                if (/\d/.test(formatted.charAt(pos))) seen++;
                pos++;
            }
            try { el.setSelectionRange(pos, pos); } catch (e) {}
        });
    }

    function rawValueOf(el) {
        return rawDigits(el ? el.value : '');
    }

    function unwireInput(el) {
        if (!el || !el.dataset.fxMoneyWired) return;
        el.value = rawValueOf(el);
        delete el.dataset.fxMoneyWired;
        el.removeAttribute('data-money');
    }

    function setMoneyMode(el, enabled) {
        if (!el) return;
        if (enabled) {
            el.setAttribute('data-money', '');
            wireInput(el);
        } else {
            unwireInput(el);
        }
    }

    function sanitizeForm(form) {
        if (!form) return;
        var els = form.querySelectorAll('[data-money]');
        for (var i = 0; i < els.length; i++) {
            els[i].value = rawValueOf(els[i]);
        }
    }

    function init(root) {
        var scope = root || document;
        var els = scope.querySelectorAll('[data-money]');
        for (var i = 0; i < els.length; i++) wireInput(els[i]);

        var forms = scope.querySelectorAll('form');
        for (var j = 0; j < forms.length; j++) {
            var form = forms[j];
            if (form.dataset.fxMoneySubmitWired) continue;
            form.dataset.fxMoneySubmitWired = '1';
            form.addEventListener('submit', function (ev) {
                sanitizeForm(ev.target);
            });
        }
    }

    window.FaoximaMoneyInput = {
        init: init,
        wire: wireInput,
        unwire: unwireInput,
        setMode: setMoneyMode,
        rawValue: rawValueOf,
        format: formatValue,
        sanitizeForm: sanitizeForm
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();
