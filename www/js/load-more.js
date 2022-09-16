button = document.getElementById('load-more-button');

if (button) {
	let observer = new IntersectionObserver(function (entries, observer) {
		entries.forEach(function(entry)  {
			if (entry.isIntersecting) {
				entry.target.click();
				observer.disconnect();
				window.setTimeout(function() {
					let button = document.getElementById('load-more-button');
					if (button) {
						observer.observe(button);
					}
				}, 1000);
			}
		});
	});

	observer.observe(button);
}
