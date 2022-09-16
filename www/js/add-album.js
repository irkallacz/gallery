let dialog = document.getElementById('album-form');
document.getElementById('show-album-form-button').addEventListener('click', function () {
	dialog.showModal();
});

document.getElementById('close-album-form-button').addEventListener('click', function () {
	dialog.close();
});