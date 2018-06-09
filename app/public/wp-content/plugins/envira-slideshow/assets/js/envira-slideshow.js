;(function ( $, window, document, envira_gallery ) {

	/******* FANCYBOX *********/

	var envira_playing = false;

	$(document).on( 'envirabox_api_before_show', function( e, obj, instance, current ){

		if ( instance.SlideShow.isActive === true ) {

			envira_playing = true;

		}

	});

	$(document).on( 'envirabox_api_before_show', function( e, obj, instance, current ){

		if ( obj.get_config('slideshow_hover') === 1 ) {

			$('.envirabox-inner img.envirabox-image, .envirabox-inner iframe').on( {
				mouseenter: function() {
					if ( instance.SlideShow.isActive === true ) {
						/* it was on, so remember that */
	                } else {
	                    envira_playing = false;
	                }
					instance.SlideShow.stop();
				},
				mouseleave: function() {
                    /* was envira playing when you entered the area? if so, restore */
                    if ( envira_playing == true ) {
	                    instance.SlideShow.start();
	                }
				}
			});


        }


	});

})( jQuery , window, document, envira_gallery );