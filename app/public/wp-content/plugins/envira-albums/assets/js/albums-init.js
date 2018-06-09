var enviraLazy = window.enviraLazy;

class Envira_Album{

    constructor( config, galleries, envirabox_config ){

        var self = this;

		//Setup our Vars
		self.data = config;
		self.galleries = galleries;
		self.id = this.get_config('album_id');
		self.envirabox_config = envirabox_config;

		//Log if ENVIRA_DEBUG enabled
		self.log(self.data);
		self.log(self.galleries);
		self.log(self.envirabox_config);
		self.log(self.id);

		//self init
		self.init();
    }
	init() {

		var self = this;

		//Justified Gallery Setup
		if (self.get_config('columns') == 0) {

			self.justified();

			if (self.get_config('lazy_loading')) {

				$(document).on('envira_pagination_ajax_load_completed', function() {

					$('#envira-gallery-' + self.id).on('jg.complete', function(e) {

						e.preventDefault();

						self.load_images();

					});

				});

				self.load_images();

			}

			if (self.get_config('justified_gallery_theme')) {

				//self.overlay_themes();

			}

			$(document).trigger('envira_gallery_api_justified', self.data);

		}

		//Lazy loading setup
		if ( self.get_config('lazy_loading') ) {

			self.load_images();

			$(window).scroll(function(e) {

				self.load_images();

			});

		}

		//Enviratope Setup
		if ( parseInt( self.get_config('columns') ) > 0 && self.get_config('isotope') ) {

			self.enviratopes();
			//Lazy loading setup
			if (self.get_config('lazy_loading')) {

				$( '#envira-gallery-' + self.id ).one('layoutComplete', function(e, laidOutItems) {

					self.load_images();

				});

			}
		} else if (  parseInt( self.get_config('columns') ) > 0) {

			self.load_images();

		}

		//Lightbox setup
		if (self.get_config('lightbox_enabled') || self.get_config('lightbox') ) {

			self.lightbox();

		}

		$(document).trigger('envira_gallery_api_init', self);

	}

	/**
	 * LazyLoading
	 *
	 * @since 1.7.1
	 */
	load_images() {

		var self = this;

		self.log('running: ' + '#envira-gallery-' + self.id );

		enviraLazy.run('#envira-gallery-' + self.id );

		if ( $('#envira-gallery-' + self.id).hasClass('enviratope') ) {

			$('#envira-gallery-' + self.id).enviraImagesLoaded()
				.done(function() {
					$('#envira-gallery-' + self.id).enviratope('layout');
				})
				.progress(function() {
					$('#envira-gallery-' + self.id).enviratope('layout');
				});

		}
	}

	/**
	 * Outputs the gallery init script in the footer.
	 *
	 * @since 1.7.1
	 */
	justified() {

		var self = this;

		$('#envira-gallery-' + this.id).enviraJustifiedGallery({
			rowHeight: this.get_config('justified_row_height'),
			maxRowHeight: -1,
			waitThumbnailsLoad: true,
			selector: '> div > div',
			lastRow: this.get_config('justified_last_row'),
			border: 0,
			margins: this.get_config('justified_margins'),


		});

		$(document).trigger('envira_gallery_api_start_justified', self.data);

		$('#envira-gallery-' + this.id).css('opacity', '1');

	}
	justified_norewind() {

		$('#envira-gallery-' + self.id).enviraJustifiedGallery('norewind');

	}
	/**
	 * Outputs the gallery init script in the footer.
	 *
	 * @since 1.7.1
	 */
	enviratopes() {

			var self = this;


			var envira_isotopes_config = {

				itemSelector: '.envira-gallery-item',
				masonry: {
					columnWidth: '.envira-gallery-item'
				}

			};
			$(document).trigger('envira_gallery_api_enviratope_config', envira_isotopes_config);

			// Initialize Isotope
			$('#envira-gallery-' + self.id).enviratope(envira_isotopes_config);
			// Re-layout Isotope when each image loads
			$('#envira-gallery-' + self.id).enviraImagesLoaded()
				.done(function() {
					$('#envira-gallery-' + self.id).enviratope('layout');
				})
				.progress(function() {
					$('#envira-gallery-' + self.id).enviratope('layout');
				});

			$(document).trigger('envira_gallery_api_enviratope', self);

		}
    lightbox(){

		var self            = this,
			thumbs           = self.get_config('thumbnails') ? { autoStart: true, hideOnClose: true, position: self.get_lightbox_config('thumbs_position') } : false,
			slideshow        = self.get_config('slideshow') ? { autoStart: self.get_config('autoplay'),speed: self.get_config('ss_speed') } : false,
			fullscreen       = self.get_config('fullscreen') && self.get_config('open_fullscreen') ? { autoStart: true } : true,
			animationEffect  = self.get_config('lightbox_open_close_effect') == 'zomm-in-out' ? 'zoom-in-out' : self.get_config('lightbox_open_close_effect'),
			transitionEffect = self.get_config('effect') == 'zomm-in-out' ? 'zoom' : self.get_config('effect'),
			lightbox_images = [];
			self.lightbox_options = {
				selector:           '[data-envirabox="' + self.id + '"]',
				loop:               self.get_config('loop'), // Enable infinite gallery navigation
				margin:             self.get_lightbox_config('margins'), // Space around image, ignored if zoomed-in or viewport width is smaller than 800px
				gutter:             self.get_lightbox_config('gutter'), // Horizontal space between slides
				keyboard:           self.get_config('keyboard'), // Enable keyboard navigation
				arrows:             self.get_lightbox_config('arrows'), // Should display navigation arrows at the screen edges
				arrow_position:     self.get_lightbox_config('arrow_position'),
				infobar:            self.get_lightbox_config('infobar'), // Should display infobar (counter and arrows at the top)
				toolbar:            self.get_lightbox_config('toolbar'), // Should display toolbar (buttons at the top)
				idleTime:           60, // Detect "idle" time in seconds
				smallBtn:           self.get_lightbox_config('show_smallbtn'),
				protect:            false, // Disable right-click and use simple image protection for images
				image:              { preload: false },
				animationEffect:    animationEffect,
				animationDuration:  300, // Duration in ms for open/close animation
			    btnTpl : {
			        smallBtn   :        self.get_lightbox_config('small_btn_template'),
				},
				zoomOpacity:        'auto',
				transitionEffect:   transitionEffect, // Transition effect between slides
				transitionDuration: 200, // Duration in ms for transition animation
				baseTpl:            self.get_lightbox_config('base_template'), // Base template for layout
				spinnerTpl:         '<div class="envirabox-loading"></div>', // Loading indicator template
				errorTpl:           self.get_lightbox_config('error_template'), // Error message template
				fullScreen:         self.get_config('fullscreen') ? fullscreen : false,
				touch:              { vertical: true, momentum: true }, // Set `touch: false` to disable dragging/swiping
				hash:               false,
				insideCap:          self.get_lightbox_config('inner_caption'),
				capPosition:        self.get_lightbox_config('caption_position'),
				media : {
			        youtube : {
			            params : {
			                autoplay : 0
			            }
			        }
			    },
			    wheel:              self.get_config('mousewheel') ? 'auto' : false,
				slideShow:          slideshow,
				thumbs:             thumbs,
		        mobile : {
		            clickContent : function( current, event ) {
		                return current.type === 'image' ? 'toggleControls' : false;
		            },
		            clickSlide : function( current, event ) {
		                return current.type === 'image' ? 'toggleControls' : 'close';
		            },
		            dblclickContent : false,
		            dblclickSlide :false,
		        },
				// Clicked on the content
				clickContent: false,
				clickSlide: 'toggleControls', // Clicked on the slide
				clickOutside: 'close', // Clicked on the background (backdrop) element

		        // Same as previous two, but for double click
		        dblclickContent : false,
		        dblclickSlide   : false,
		        dblclickOutside : false,

				// Callbacks
				//==========
				onInit: function(instance, current) {

					$( document ).trigger( 'envirabox_api_on_init', [ self, instance, current ]  );
				},

				beforeLoad: function(instance, current) {

					$(document).trigger('envirabox_api_before_load', [ self, instance, current ]  );

				},
				afterLoad: function(instance, current) {

					$(document).trigger('envirabox_api_after_load', [ self, instance, current ]  );

				},

				beforeShow: function(instance, current) {

					/* override title in legacy to display gallery and not album title */
					if ( $('#envirabox-buttons-title').length > 0 && current.gallery_title ) {
						document.getElementById("envirabox-buttons-title").innerHTML = '<span>' + current.gallery_title + '</span>';
					}

					$(document).trigger('envirabox_api_before_show', [ self, instance, current ]  );

				},
				afterShow: function(instance, current) {

					if ( prepend == undefined || prepend_cap == undefined){

						var prepend     = false,
							prepend_cap = false;

					}

					if ( prepend != true ){

						$('.envirabox-position-overlay').each(function(){
							$(this).prependTo( current.$content );
						});

						prepend = true;
					}

					/* support older albums or if someone overrides the keyboard configuration via a filter, etc. */
					
					if( self.get_config('keyboard') !== undefined && self.get_config('keyboard') === 0 ) {

						$(window).keypress(function(event){

						    if([32, 37, 38, 39, 40].indexOf(event.keyCode) > -1) {
						        event.preventDefault();
						    }

						});

					}

					/* legacy theme we hide certain elements initially to prevent user seeing them for a second in the upper left until the CSS fully loads */
					$('.envirabox-caption').show();
					$('.envirabox-navigation').show();
					$('.envirabox-navigation-inside').show();

					$(document).trigger('envirabox_api_after_show', [ self, instance, current ] );

				},

				beforeClose: function(instance, current) {

					$(document).trigger('envirabox_api_before_close', [ self, instance, current ]  );

				},
				afterClose: function(instance, current) {

					$(document).trigger('envirabox_api_after_close', [ self, instance, current ]  );

				},

				onActivate: function(instance, current) {

					$( document ).trigger('envirabox_api_on_activate', [ self, instance, current ] );

				},
				onDeactivate: function( instance, current ) {

					$( document ).trigger('envirabox_api_on_deactivate', [ self, instance, current ] );

				},

			};

		$(document).trigger('envirabox_options', self );

        $('#envira-gallery-wrap-' + self.id + ' .envira-gallery-link' ).on("click", function(e){

	        e.preventDefault();
			e.stopPropagation();

			var $this               = $( this ),
				images              = [],
				$envira_images      = $this.data('gallery-images'),
				sorted_ids          = $this.data('gallery-sort-ids'), // sort by sort ids, not by output of gallery-images, because retaining object key order between unserialisation and serialisation in JavaScript is never guaranteed.
				// backup plan in case there isn't gallery-sort-ids (maybe something cached?) or this option wasn't selected in the album settings
				sorting_factor      = sorted_ids !== undefined && self.data.gallery_sort == 'gallery' ? 'id' : 'image',
				sorting_factor_data = sorted_ids !== undefined && self.data.gallery_sort == 'gallery' ? sorted_ids : $envira_images,
				active              = $.envirabox.getInstance();
				$.each( sorting_factor_data, function(i){
					if ( sorting_factor == 'id' ) {
						var envira_image = $envira_images[this];
						envira_image.opts.caption = envira_image.caption;
					} else {
						var envira_image = this;
						this.opts.caption = this.caption;
					}

					if ( envira_image.link !== undefined ) {
	                    envira_image.link.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

					    if (RegExp.$3.indexOf('youtu') > -1) {
					        // youtube
							var video_id_regExp = /^.*((youtu.be\/|vimeo.com\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/,
							    match = envira_image.link.match(video_id_regExp);
							if (match && match[7].length == 11) {
							    // Do anything for being valid
							    envira_image.video = true;
							    envira_image.type = 'iframe';
							    envira_image.src = ('https://www.youtube.com/embed/' + match[7] + '?autoplay=0');
							}
					    } else if (RegExp.$3.indexOf('vimeo') > -1) {
					        // vimeo
							var video_id_regExp = /^.*((youtu.be\/|vimeo.com\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/,
							    match = envira_image.link.match(video_id_regExp);
							if (match && match[7]){
							    envira_image.video = true;
							    envira_image.type = 'iframe';
							    envira_image.src = ('https://player.vimeo.com/video/' + match[7]);
							}
					    } else if (envira_image.link.indexOf('mp4') > -1) {
							    envira_image.video = true;
								envira_image.type = 'video';
								envira_image.src = envira_image.link;
								envira_image.opts.videoFormat = 'video/mp4';
						}
					}
					images.push( envira_image );
				});

				if ( active ) {
					return;
				}
			$.envirabox.open( images, self.lightbox_options );

        });

    }
	/**
	 * Get a config option based off of a key.
	 *
	 * @since 1.7.1
	 */
	get_config(key) {

		return this.data[key];

	}

	/**
	 * Helper method to get config by key.
	 *
	 * @since 1.7.1
	 */
	get_lightbox_config(key) {

		return this.envirabox_config[key];

	}

	/**
	 * Helper method to get image from id
	 *
	 * @since 1.7.1
	 */
	get_image(id) {

		return this.images[id];

	}

	/**
	 * Helper method for logging if ENVIRA_DEBUG is true.
	 *
	 * @since 1.7.1
	 */
	log(log) {

		//Bail if debug or log is not set.
		if (envira_gallery.debug == undefined || !envira_gallery.debug || log == undefined) {

			return;

		}
		console.log(log);

	}


}

module.exports = Envira_Album;