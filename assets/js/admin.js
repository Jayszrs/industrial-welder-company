(function () {
    'use strict';

    document.querySelectorAll('form.confirm-delete').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var msg = form.getAttribute('data-confirm') || 'Are you sure you want to delete this item? This cannot be undone.';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });
})();
