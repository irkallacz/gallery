let photoUpload = new window.Dropzone('div#photoUpload', {
	url: document.getElementById('photoUpload').dataset.url,
	maxFilesize: 10 * 1024 * 1024,
	acceptedFiles: 'image/*',
    dictDefaultMessage: 'Sem přesuňte soubory pro jejich nahrání'
});