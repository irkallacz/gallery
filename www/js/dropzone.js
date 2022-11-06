let photoUpload = new window.Dropzone('div#photoUpload', {
	url: document.getElementById('photoUpload').dataset.url,
	maxFilesize: 10 * 1024 * 1024,
	acceptedFiles: 'image/*',
    dictDefaultMessage: 'Sem přesuňte soubory pro jejich nahrání'
});

photoUpload.on('thumbnail', (file, dataUrl) => {
	if ((dataUrl instanceof Event)) {
		file.previewElement.querySelector('.dz-preview img').src = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgNDggNDgiPgo8cGF0aCBmaWxsPSIjRjU3QzAwIiBkPSJNNDAsNDFIOGMtMi4yLDAtNC0xLjgtNC00VjExYzAtMi4yLDEuOC00LDQtNGgzMmMyLjIsMCw0LDEuOCw0LDR2MjZDNDQsMzkuMiw0Mi4yLDQxLDQwLDQxeiIvPgo8Y2lyY2xlIGZpbGw9IiNGRkY5QzQiIGN4PSIzNSIgY3k9IjE2IiByPSIzIi8+Cjxwb2x5Z29uIGZpbGw9IiM5NDJBMDkiIHBvaW50cz0iMjAsMTYgOSwzMiAzMSwzMiIvPgo8cG9seWdvbiBmaWxsPSIjQkYzNjBDIiBwb2ludHM9IjMxLDIyIDIzLDMyIDM5LDMyIi8+Cjwvc3ZnPgo=';
	}
})