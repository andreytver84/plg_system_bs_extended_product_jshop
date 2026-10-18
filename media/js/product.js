(function () {
    'use strict';

    function sameOrigin(href) {
        try {
            return new URL(href, window.location.href).origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }

    function bindLightbox(root) {
        var dialog = root.querySelector('[data-bsep-dialog]');
        if (!dialog || typeof dialog.showModal !== 'function') {
            return;
        }

        var img = dialog.querySelector('.bsep-lightbox__img');
        var captionEl = dialog.querySelector('.bsep-lightbox__caption');
        var closeBtn = dialog.querySelector('[data-bsep-close]');

        function closeDialog() {
            if (dialog.open) {
                dialog.close();
            }
        }

        root.querySelectorAll('[data-bsep-lightbox]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                var href = link.getAttribute('href') || '';
                if (!sameOrigin(href)) {
                    return;
                }

                event.preventDefault();
                img.removeAttribute('src');
                img.setAttribute('src', href);
                img.setAttribute('alt', link.getAttribute('data-bsep-caption') || '');
                captionEl.textContent = link.getAttribute('data-bsep-caption') || '';
                dialog.showModal();
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeDialog);
        }

        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                closeDialog();
            }
        });
    }

    document.querySelectorAll('[data-bsep]').forEach(bindLightbox);
})();
