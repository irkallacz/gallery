window.Fancybox.bind('[data-fancybox="album"]', {
	infinite: false,
	Thumbs: {
		autoStart: false
	},
	on: {
		initCarousel: (fancybox, slide) => {
			fancybox.Carousel.plugins.Autoplay.stop();
		},
	},
});