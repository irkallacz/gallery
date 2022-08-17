import netteForms from 'nette-forms';
window.Nette = netteForms;
netteForms.initOnLoad();

import Dropzone from "dropzone";
import "dropzone/dist/dropzone.css";
Dropzone.autoDiscover = false;
window.Dropzone = Dropzone;

import Sortable, { MultiDrag } from 'sortablejs';
Sortable.mount(new MultiDrag());
window.Sortable = Sortable;

import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox.css";
window.Fancybox = Fancybox;

import { Carousel } from "@fancyapps/ui";
import { Autoplay } from "@fancyapps/ui/dist/carousel.autoplay.esm.js";
import "@fancyapps/ui/dist/carousel.css";
Carousel.Plugins.Autoplay = Autoplay;
window.Carousel = Carousel;

import naja from 'naja';
window.naja = naja;
