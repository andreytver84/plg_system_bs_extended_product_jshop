(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        var button = target.closest('.bsep .sppb-panel-heading');

        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var group = button.closest('.sppb-panel-group');
        var panel = button.nextElementSibling;
        var wasOpen = button.classList.contains('active');
        var keepOpen = button.getAttribute('data-keep-open') === '1';

        if (group && !keepOpen) {
            group.querySelectorAll('.sppb-panel-heading').forEach(function (heading) {
                if (!(heading instanceof HTMLButtonElement)) {
                    return;
                }

                heading.classList.remove('active');
                heading.setAttribute('aria-expanded', 'false');

                var collapse = heading.nextElementSibling;

                if (collapse instanceof HTMLElement) {
                    collapse.style.display = 'none';
                }
            });
        }

        if (wasOpen || !(panel instanceof HTMLElement)) {
            return;
        }

        button.classList.add('active');
        button.setAttribute('aria-expanded', 'true');
        panel.style.display = 'block';
    }, true);
})();
