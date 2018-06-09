jQuery( document ).ready( function( $ ) {
	
	/**
	* Prevent right click on Envira Images
	*/
	$( document ).on( 'contextmenu dragstart', '.envirabox-wrap, .envira-gallery-image, .envirabox-image, #envirabox-thumbs img, .envirabox-nav, .envira-gallery-item .caption, .envira-video-play-icon, .envirabox-inner, .envira-title, .envira-caption, .envira-gallery-position-overlay, .envira-download-button, .envira-printing-button, .envira-social-buttons, .envira-album-title, .envira-album-caption, .envira-album-image-count', function() { 
		return false; 
	} );

	$('img.envira-gallery-image').on('dragstart', false);

	/**
	* Monitor which keys are being pressed
	*/
	var envira_protection_keys = {
		'alt': false,
		'shift': false,
		'meta': false,
	};
	$( document ).on( 'keydown', function( e ) {

		// Alt Key Pressed
		if ( e.altKey ) {
			envira_protection_keys.alt = true;
		}

		// Shift Key Pressed
		if ( e.shiftKey ) {
			envira_protection_keys.shift = true;
		}

		// Meta Key Pressed (e.g. Mac Cmd)
		if ( e.metaKey ) {
			envira_protection_keys.meta = true;
		}


	} );
	$( document ).on( 'keyup', function( e ) {

		// Alt Key Released
		if ( ! e.altKey ) {
			envira_protection_keys.alt = false;
		}

		// Shift Key Released
		if ( e.shiftKey ) {
			envira_protection_keys.shift = false;
		}

		// Meta Key Released (e.g. Mac Cmd)
		if ( ! e.metaKey ) {
			envira_protection_keys.meta = false;
		}

	} );
    
	/**
	* Prevent automatic download when Alt + left click
	*/
	$( document ).on( 'click', '.envira-gallery-image, .envirabox-image, #envirabox-thumbs img, .envirabox-nav, .envira-gallery-item .caption', function( e ) {

		if ( envira_protection_keys.alt || envira_protection_keys.shift || envira_protection_keys.meta ) {
			// User is trying to download - stop!
			e.preventDefault();
			return false;
		}

	} );

} );