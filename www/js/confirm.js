document.querySelectorAll('.confirm').forEach(function (el) {
	console.log(el);
	el.addEventListener('click', function (ev) {
		return confirm(el.getAttribute('data-confirm'));
	});
});
