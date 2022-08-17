//sortable
let el = document.getElementById('edit-list')
let sortable = new window.Sortable(el, {
	multiDrag: true,
	selectedClass: 'selected',
	fallbackTolerance: 3,
	handle: 'img',
});

//select all
document.getElementById('select-all').addEventListener('click', function () {
	document.querySelectorAll('input.selector').forEach(function (element) {
		element.checked = ! element.checked;
	});
});
