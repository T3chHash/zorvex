(function () {
    function restoreToOriginalParent(pop) {
        var home = pop.__fxHome;
        if (!home) return;
        pop.style.position = '';
        pop.style.left = '';
        pop.style.right = '';
        pop.style.top = '';
        pop.style.bottom = '';
        if (home.nextSibling) {
            home.parent.insertBefore(pop, home.nextSibling);
        } else {
            home.parent.appendChild(pop);
        }
    }

    function closeAll(except) {
        document.querySelectorAll('.fx-compact-popover.is-visible').forEach(function (pop) {
            if (pop === except) return;
            pop.classList.remove('is-visible');
            var trigger = pop.__fxTrigger;
            if (trigger) {
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
            restoreToOriginalParent(pop);
        });
    }

    function position(trigger, pop) {
        pop.style.left = '';
        pop.style.right = '';
        pop.style.top = '';
        pop.style.bottom = '';
        pop.classList.remove('fx-pop-above');

        var triggerRect = trigger.getBoundingClientRect();
        var popRect = pop.getBoundingClientRect();
        var viewportW = document.documentElement.clientWidth;
        var viewportH = document.documentElement.clientHeight;

        var spaceBelow = viewportH - triggerRect.bottom;
        var openAbove = spaceBelow < popRect.height + 12 && triggerRect.top > popRect.height + 12;

        if (openAbove) {
            pop.style.top = (triggerRect.top - popRect.height - 6) + 'px';
            pop.classList.add('fx-pop-above');
        } else {
            pop.style.top = (triggerRect.bottom + 6) + 'px';
        }

        var left = triggerRect.left;
        if (left + popRect.width > viewportW - 8) {
            left = Math.max(8, triggerRect.right - popRect.width);
        }
        pop.style.left = left + 'px';
    }

    function openPopover(trigger, pop) {
        closeAll(pop);
        if (!pop.__fxHome) {
            pop.__fxHome = { parent: pop.parentNode, nextSibling: pop.nextSibling };
        }
        pop.__fxTrigger = trigger;
        pop.style.position = 'fixed';
        document.body.appendChild(pop);
        pop.classList.add('is-visible');
        trigger.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        position(trigger, pop);
    }

    function closePopover(trigger, pop) {
        pop.classList.remove('is-visible');
        trigger.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        restoreToOriginalParent(pop);
    }

    function togglePopover(trigger, pop) {
        if (pop.classList.contains('is-visible')) {
            closePopover(trigger, pop);
        } else {
            openPopover(trigger, pop);
        }
    }

    function init(root) {
        (root || document).querySelectorAll('.fx-compact-more').forEach(function (trigger) {
            if (trigger.__fxBound) return;
            var pop = trigger.nextElementSibling;
            if (!pop || !pop.classList.contains('fx-compact-popover')) return;
            trigger.__fxBound = true;

            trigger.setAttribute('role', 'button');
            trigger.setAttribute('tabindex', '0');
            trigger.setAttribute('aria-expanded', 'false');

            var wrap = trigger.closest('.fx-compact-badges');
            var openedByPointer = false;
            var lastPointerOpenAt = 0;

            if (wrap) {
                wrap.addEventListener('mouseenter', function (ev) {
                    if (ev.sourceCapabilities && ev.sourceCapabilities.firesTouchEvents) return;
                    openedByPointer = true;
                    lastPointerOpenAt = Date.now();
                    openPopover(trigger, pop);
                });
                wrap.addEventListener('mouseleave', function () {
                    if (!openedByPointer) return;
                    openedByPointer = false;
                    closePopover(trigger, pop);
                });
            }

            trigger.addEventListener('click', function (ev) {
                ev.stopPropagation();
                var justOpenedByHover = openedByPointer && (Date.now() - lastPointerOpenAt) < 500;
                openedByPointer = false;
                if (justOpenedByHover) {
                    if (!pop.classList.contains('is-visible')) openPopover(trigger, pop);
                    return;
                }
                togglePopover(trigger, pop);
            });
            trigger.addEventListener('keydown', function (ev) {
                if (ev.key === 'Enter' || ev.key === ' ') {
                    ev.preventDefault();
                    openedByPointer = false;
                    togglePopover(trigger, pop);
                }
            });
        });
    }

    document.addEventListener('click', function () { closeAll(); });
    document.addEventListener('scroll', function () { closeAll(); }, true);
    window.addEventListener('resize', function () { closeAll(); });

    document.addEventListener('DOMContentLoaded', function () { init(document); });
    window.faoximaInitCompactBadges = init;
})();
