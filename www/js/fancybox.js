window.Fancybox.bind('[data-fancybox="album"]', {
	infinite: false,
	Thumbs: {
		autoStart: false
	},
	Toolbar: {
		display: [
			"zoom",
			"slideshow",
			"fullscreen",
			"download",
			"thumbs",
			"close",
		],
	},
	on: {
		initCarousel: (fancybox, slide) => {
			fancybox.Carousel.plugins.Autoplay.stop();
		},
	},
});