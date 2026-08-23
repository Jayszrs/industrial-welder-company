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

    /* Consistent image uploader with drag state and instant preview. */
    document.querySelectorAll('input[type="file"][name="image"], input[type="file"][data-image-upload]').forEach(function (input) {
        if (input.closest('.aimage-upload')) return;

        var upload = document.createElement('div');
        upload.className = 'aimage-upload';
        input.parentNode.insertBefore(upload, input);
        upload.appendChild(input);
        input.classList.add('aimage-upload__input');

        var copy = document.createElement('div');
        copy.className = 'aimage-upload__copy';
        copy.innerHTML = '<span class="aimage-upload__icon" aria-hidden="true">+</span>' +
            '<strong>' + (input.multiple ? 'Choose multiple images or drop them here' : 'Choose an image or drop it here') + '</strong>' +
            '<span>JPG, PNG, or WEBP · maximum 5MB</span>';
        upload.appendChild(copy);

        var preview = document.createElement('img');
        preview.className = 'aimage-upload__preview';
        preview.alt = '';
        preview.hidden = true;
        upload.appendChild(preview);

        var objectUrl = null;
        input.addEventListener('change', function () {
            var files = input.files ? Array.prototype.slice.call(input.files) : [];
            var file = files[0];
            if (!file) {
                preview.hidden = true;
                upload.classList.remove('has-preview');
                return;
            }
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.hidden = false;
            var totalSize = files.reduce(function (sum, selectedFile) { return sum + selectedFile.size; }, 0);
            copy.querySelector('strong').textContent = input.multiple ? files.length + ' images selected' : file.name;
            copy.querySelector('span:last-child').textContent = Math.max(1, Math.round(totalSize / 1024)) + ' KB total · click to replace';
            upload.classList.add('has-preview');
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            input.addEventListener(eventName, function () { upload.classList.add('is-dragging'); });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            input.addEventListener(eventName, function () { upload.classList.remove('is-dragging'); });
        });
    });
})();
