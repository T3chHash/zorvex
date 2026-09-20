(function (global) {
    'use strict';

    var registry = {};
    var badgeEl = null;
    var badgeCountEl = null;
    var clearBtnEl = null;
    var deleteBtnEl = null;
    var activeDeleteTarget = null;

    function ensureBadge() {
        if (badgeEl) return badgeEl;
        badgeEl = document.createElement('div');
        badgeEl.id = 'fx-bulk-badge';
        badgeEl.className = 'fx-bulk-badge';
        badgeEl.innerHTML =
            '<span class="fx-bulk-badge__count">0</span>' +
            '<span class="fx-bulk-badge__label">مورد انتخاب شده</span>' +
            '<button type="button" class="fx-bulk-badge__clear" id="fx-bulk-clear">لغو انتخاب</button>' +
            '<button type="button" class="fx-bulk-badge__delete" id="fx-bulk-delete">حذف انتخاب‌شده‌ها</button>';
        badgeCountEl = badgeEl.querySelector('.fx-bulk-badge__count');
        clearBtnEl = badgeEl.querySelector('.fx-bulk-badge__clear');
        deleteBtnEl = badgeEl.querySelector('.fx-bulk-badge__delete');
        clearBtnEl.addEventListener('click', clearAllScopes);
        deleteBtnEl.addEventListener('click', function () {
            if (activeDeleteTarget) activeDeleteTarget.click();
        });
        document.body.appendChild(badgeEl);
        return badgeEl;
    }

    function totalCount() {
        var total = 0;
        for (var k in registry) {
            if (Object.prototype.hasOwnProperty.call(registry, k)) {
                total += registry[k].set.size;
            }
        }
        return total;
    }

    function pickDeleteTarget() {
        for (var k in registry) {
            if (!Object.prototype.hasOwnProperty.call(registry, k)) continue;
            var entry = registry[k];
            if (entry.set.size > 0 && entry.deleteButtons && entry.deleteButtons.length) {
                return entry.deleteButtons[0];
            }
        }
        return null;
    }

    function updateBadge() {
        ensureBadge();
        var total = totalCount();
        badgeCountEl.textContent = String(total);
        activeDeleteTarget = pickDeleteTarget();
        deleteBtnEl.style.display = activeDeleteTarget ? 'inline-flex' : 'none';
        if (total > 0) {
            badgeEl.classList.add('show');
        } else {
            badgeEl.classList.remove('show');
        }
    }

    function clearMasterToggles(root) {
        var seen = [];
        function collect(ctx) {
            if (!ctx || !ctx.querySelectorAll) return;
            var found = ctx.querySelectorAll('input[type="checkbox"][id^="check-all"], input[type="checkbox"][onclick*="faoximaToggleAll"]');
            for (var i = 0; i < found.length; i++) {
                if (seen.indexOf(found[i]) === -1) seen.push(found[i]);
            }
        }
        collect(root);
        collect(document);
        seen.forEach(function (cb) {
            cb.checked = false;
            cb.indeterminate = false;
        });
    }

    function clearAllScopes() {
        for (var k in registry) {
            if (!Object.prototype.hasOwnProperty.call(registry, k)) continue;
            var entry = registry[k];
            var root = entry.scopeRoot || document;
            var nodes = root.querySelectorAll(entry.checkboxSelector);
            nodes.forEach(function (cb) {
                if (cb.checked) {
                    cb.checked = false;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            clearMasterToggles(root);
            entry.set.clear();
            clearStorage(entry.key);
            updateButtons(k);
        }
        updateBadge();
    }

    function updateButtons(scope) {
        var entry = registry[scope];
        if (!entry || !entry.deleteButtons) return;
        entry.deleteButtons.forEach(function (btn) {
            btn.style.display = 'none';
        });
    }

    function loadSet(key) {
        var out = new Set();
        try {
            var raw = sessionStorage.getItem(key);
            if (raw) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) {
                    arr.forEach(function (v) { out.add(String(v)); });
                }
            }
        } catch (e) {}
        return out;
    }

    function persistSet(key, set) {
        try {
            sessionStorage.setItem(key, JSON.stringify(Array.from(set)));
        } catch (e) {}
    }

    function clearStorage(key) {
        try {
            sessionStorage.removeItem(key);
        } catch (e) {}
    }

    function restoreCheckedState(entry) {
        var root = entry.scopeRoot || document;
        var nodes = root.querySelectorAll(entry.checkboxSelector);
        nodes.forEach(function (cb) {
            if (cb.disabled) return;
            if (entry.set.has(cb.value)) cb.checked = true;
        });
    }

    function init(config) {
        var scope = config.scope || 'default';
        var key = 'fx_bulk_sel:' + location.pathname + ':' + scope;
        var scopeRoot = config.scopeRoot || document;
        var deleteButtons = [];
        if (config.deleteButtonSelector) {
            deleteButtons = Array.prototype.slice.call(document.querySelectorAll(config.deleteButtonSelector));
        }

        var entry = {
            key: key,
            set: new Set(),
            scopeRoot: scopeRoot,
            checkboxSelector: config.checkboxSelector,
            deleteButtons: deleteButtons
        };
        registry[scope] = entry;

        if (Array.isArray(config.clearOnQueryFlags) && config.clearOnQueryFlags.length) {
            var params = new URLSearchParams(location.search);
            var shouldClear = config.clearOnQueryFlags.some(function (flag) {
                var v = params.get(flag);
                return v === 'ok' || v === '1';
            });
            if (shouldClear) clearStorage(key);
        }

        entry.set = loadSet(key);

        restoreCheckedState(entry);
        updateBadge();
        updateButtons(scope);

        scopeRoot.addEventListener('change', function (e) {
            var cb = e.target;
            if (!cb || cb.disabled) return;
            if (!cb.matches || !cb.matches(config.checkboxSelector)) return;
            if (cb.checked) entry.set.add(cb.value); else entry.set.delete(cb.value);
            persistSet(key, entry.set);
            updateBadge();
            updateButtons(scope);
        });

        if (config.formEl) {
            config.formEl.addEventListener('submit', function () {
                var liveChecked = {};
                var liveNodes = config.formEl.querySelectorAll(config.checkboxSelector + ':checked');
                liveNodes.forEach(function (cb) { liveChecked[cb.value] = true; });
                entry.set.forEach(function (val) {
                    if (liveChecked[val]) return;
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = val;
                    config.formEl.appendChild(input);
                });
            }, true);
        }

        return {
            add: function (value) {
                entry.set.add(String(value));
                persistSet(key, entry.set);
                updateBadge();
                updateButtons(scope);
            },
            remove: function (value) {
                entry.set.delete(String(value));
                persistSet(key, entry.set);
                updateBadge();
                updateButtons(scope);
            },
            clear: function () {
                entry.set.clear();
                clearStorage(key);
                updateBadge();
                updateButtons(scope);
            },
            count: function () {
                return entry.set.size;
            },
            key: key
        };
    }

    function getPersistedIds(key) {
        return Array.from(loadSet(key));
    }

    global.FxBulkSelect = { init: init, getPersistedIds: getPersistedIds };
})(window);
