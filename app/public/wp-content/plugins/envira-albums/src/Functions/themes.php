<?php

/**
 * envira_load_lightbox_config function.
 *
 * @access public
 * @param mixed $theme
 * @return void
 */
function envira_album_load_lightbox_config( $album_id, $raw = false ){

	$data       = envira_get_album( $album_id );
	$data['id'] = $album_id;

	$lightbox_themes = envira_get_lightbox_themes();
	$key             = array_search( envira_get_config( 'lightbox_theme', $data ) , array_column( $lightbox_themes, 'value'));
	$current_theme   = $lightbox_themes[$key];

	if ( !empty( $current_theme['config'] ) && is_array( $current_theme['config'] ) ){

		$current_theme['config']['base_template'] = function_exists( $current_theme['config']['base_template'] ) ? call_user_func( $current_theme['config']['base_template'], $data ) : envirabox_default_template( $data );

		$config = $current_theme['config'];

	} else {

		$config = envirabox_allbum_default_config( $album_id );

	}

	$config['load_all']         = apply_filters( 'envira_load_all_images_lightbox', false, $data );
	$config['error_template']   = envirabox_error_template( $data );

	// If supersize is enabled lets override settings
	if ( envira_get_config( 'supersize', $data ) == 1 ){

		$config['margins'] = array( 0,0 );

	}

	$legacy_themes                = envirabox_legecy_themes();
	$config['thumbs_position']    = in_array( $current_theme['value'], $legacy_themes ) ? envira_get_config( 'thumbnails_position', $data ) : 'lock';
	$config['arrow_position']     = in_array( $current_theme['value'], $legacy_themes ) ? envira_get_config( 'arrows_position', $data ) : false;
	$config['arrows']     		  = in_array( $current_theme['value'], $legacy_themes ) ? envira_get_config( 'arrows', $data ) : true;
	$config['toolbar']            = in_array( $current_theme['value'], $legacy_themes ) ? false : true;
	$config['infobar']            = in_array( $current_theme['value'], $legacy_themes ) ? true : false;
	$config['show_smallbtn']      = in_array( $current_theme['value'], $legacy_themes ) ? true : false;
	$config['inner_caption']      = in_array( $current_theme['value'], $legacy_themes ) ? true : false;
	$config['caption_position']   = in_array( $current_theme['value'], $legacy_themes ) ? envira_get_config( 'title_display', $data ) : false;
	$config['small_btn_template'] = '<a data-envirabox-close class="envirabox-item envirabox-close envirabox-button--close" title="' . __('Close', 'envira-gallery' ).'" href="#"></a>';

	return json_encode( $config );

}
/**
 * envira_default_lightbox_config function.
 *
 * @access public
 * @return void
 */
function envirabox_allbum_default_config( $album_id ){

	$data = envira_get_album( $album_id );

	$config = array(
		'arrows'    => 'true',
		'margins'   => array( 220, 0 ), //top/bottom, left/right
		'template'  => envirabox_default_template( $data ),
		'thumbs_position' => 'bottom',
	);

	return apply_filters( 'envirabox_default_config', $config, $data, $album_id );

}