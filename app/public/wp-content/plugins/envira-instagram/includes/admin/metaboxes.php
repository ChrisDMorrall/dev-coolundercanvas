<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Instagram
 * @author  Envira Team
 */
class Envira_Instagram_Metaboxes {

    /**
     * Holds the class object.
     *
     * @since 1.0.0 
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

		// Load the base class object.
        $this->base = Envira_Instagram::get_instance();

        // Actions and Filters
        add_action( 'envira_gallery_metabox_scripts', array( $this, 'meta_box_scripts' ) );
        add_filter( 'envira_gallery_types', array( $this, 'add_type' ), 9999, 2 );

        add_action( 'envira_gallery_display_instagram', array( $this, 'images_display' ) );
        add_action( 'envira_gallery_preview_instagram', array( $this, 'preview_display' ) );
        add_filter( 'envira_albums_metabox_gallery_inject_images', array( $this, 'albums_inject_images_for_cover_image_selection' ), 10, 3 );

        add_filter( 'envira_gallery_save_settings', array( $this, 'save' ), 10, 2 );
        add_action( 'envira_gallery_flush_caches', array( $this, 'flush_caches' ), 10, 2 );
        
    }

    /**
     * Enqueues JS for the metabox
     *
     * @since 1.0.0
     */
    public function meta_box_scripts() {

        wp_enqueue_script( $this->base->plugin_slug . '-metabox-script', plugins_url( 'assets/js/min/metabox-min.js', $this->base->file ), array( 'jquery', 'jquery-ui-sortable' ), $this->base->version, true );

    }
    
    /**
	 * Registers a new Gallery Type
	 *
	 * @since 1.0.0
	 *
	 * @param array $types Gallery Types
	 * @param WP_Post $post WordPress Post
	 * @return array Gallery Types
	 */
    public function add_type( $types, $post ) {

        // Don't add the type if it's a default or dynamic gallery
        $data = Envira_Gallery::get_instance()->get_gallery( $post->ID );
        if ( 'defaults' === Envira_Gallery_Shortcode::get_instance()->get_config( 'type', $data ) ||
             'dynamic' === Envira_Gallery_Shortcode::get_instance()->get_config( 'type', $data ) ) {
            return $types;
        }

        // Add Instagram as a Gallery Type.
        $types['instagram'] = __( 'Instagram', 'envira-instagram' );
	    return $types;
	    
    }
    
    /**
	 * Display output for the Images Tab
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WordPress Post
	 */
	public function images_display( $post ) {

		// Get instances and auth
		$instance = Envira_Gallery_Metaboxes::get_instance();
		$common = Envira_Instagram_Common::get_instance();
		$auth = $common->get_instagram_auth();
		
		if ( empty( $auth['token'] ) ) {
			// Tell the user they need to oAuth with Instagram, and give them the option to do that now.
			// Determine which screen we're on (i.e. New Gallery or Edit Gallery)
			if ( 'auto-draft' == $post->post_status ) {
				$connect_url = $common->get_oauth_url( 'post-new.php?post_type=envira' );
			} else {
				// Note: the missing 'action=edit' parameter is deliberate. Instagram strips this URL argument in the oAuth
				// process, and would then throw a 400 redirect_uri mismatch error.
				// Envira's API will append the 'action-edit' parameter on the redirect back to this site, ensuring everything
				// works correctly.
				$connect_url = $common->get_oauth_url( 'post.php?post=' . $post->ID );
			}
			?>
			<div class="envira-external-req">
			  
				<h2><?php _e( 'Instagram Authorization Setup', 'envira-instagram' ); ?></h2>
				<p><?php _e( 'Before you can create Instagram galleries, you need to authenticate Envira with your Instagram account.', 'envira-instagram' ); ?></p>
				<p>
					<a href="<?php echo $connect_url; ?>" class="button button-primary">
						<?php _e( 'Click Here to Authenticate Envira with Instagram', 'envira-instagram' ); ?>
					</a>
				</p>
			</div>			  
			<?php
		} else {
			?>
            <div id="envira-instagram">
                <p class="envira-intro">
                    <?php _e( 'Instagram Settings', 'envira-instagram' ); ?>
                    <small>

                        <?php _e( 'The settings below adjust the Instagram options for the gallery.', 'envira-instagram' ); ?>

                        <?php if ( apply_filters('envira_whitelabel', false )  ): ?>
                            <?php do_action('envira_instagram_whitelabel_tab_helptext'); ?>
                        <?php else: ?>

                        
                        <br />
                        <?php _e( 'Need some help?', 'envira-instagram' ); ?>
                        <a href="http://enviragallery.com/docs/instagram-addon/" class="envira-doc" target="_blank">
                            <?php _e( 'Read the Documentation', 'envira-instagram' ); ?>
                        </a>
                        or
                        <a href="https://www.youtube.com/embed/Um3R-ZCwl2U/?rel=0" class="envira-video" target="_blank">
                            <?php _e( 'Watch a Video', 'envira-instagram' ); ?>
                        </a>

                        <?php endif; ?>

                    </small>
                </p>

                <table class="form-table">
                    <tbody>
                        <tr id="envira-config-instagram-type-box">
                            <th scope="row">
                                <label for="envira-config-instagram-type"><?php _e( 'Feed Type', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-config-instagram-type" name="_envira_gallery[instagram_type]">
                                    <?php foreach ( (array) $common->instagram_types() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'instagram_type', $instance->get_config_default( 'instagram_type' ) ) ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e( 'The type of images to pull from Instagram.', 'envira-instagram' ); ?></p>
                            </td>
                        </tr>
                        <tr id="envira-config-instagram-number-box">
                            <th scope="row">
                                <label for="envira-config-instagram-number"><?php _e( 'Number of Instagram Photos', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-instagram-number" type="number" name="_envira_gallery[instagram_number]" value="<?php echo $instance->get_config( 'instagram_number', $instance->get_config_default( 'instagram_number' ) ); ?>" />
                                <p class="description"><?php _e( 'The number of images to pull from your Instagram feed.', 'envira-instagram' ); ?></p>
                            </td>
                        </tr>

                        <?php

                        // account for the fact that instagram no longer has full, revert to selected 'standard_resolution'

                        $image_resolution_config = $instance->get_config( 'instagram_res' ) != 'full' ? $instance->get_config( 'instagram_res', $instance->get_config_default( 'instagram_res' ) ) : 'standard_resolution';

                        ?>

                        <tr id="soliloquy-config-instagram-res-box">
                            <th scope="row">
                                <label for="envira-config-instagram-res"><?php _e( 'Image Resolution', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-config-instagram-res" name="_envira_gallery[instagram_res]">
                                    <?php foreach ( (array) $common->instagram_resolutions() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $image_resolution_config ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e( 'Determines the image resolution and size to use from Instagram.', 'envira-instagram' ); ?></p>
                            </td>
                        </tr>
                        <tr id="envira-config-instagram-link-box">
                            <th scope="row">
                                <label for="envira-config-instagram-link"><?php _e( 'Link to Photo?', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-config-instagram-link" name="_envira_gallery[instagram_link]">
                                    <?php foreach ( (array) $this->get_instagram_link_options() as $i => $data ) : ?>
                                        <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'instagram_link', $instance->get_config_default( 'instagram_link' ) ) ); ?>><?php echo $data['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php /* <input id="envira-config-instagram-link" type="checkbox" name="_envira_gallery[instagram_link]" value="<?php echo $instance->get_config( 'instagram_link', $instance->get_config_default( 'instagram_link' ) ); ?>" <?php checked( $instance->get_config( 'instagram_link', $instance->get_config_default( 'instagram_link' ) ), 1 ); ?> /> */ ?>
                                <p class="description"><?php _e( 'Links the photo to its original page on Instagram or directly to the image. If special link is selected image will appear in a lightbox if that is enabled.', 'envira-instagram' ); ?></p>
                            </td>
                        </tr>
                        <tr id="envira-config-instagram-link-target-box">
                            <th scope="row">
                                <label for="envira-config-instagram-link-target"><?php _e( 'Open In New Tab?', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-instagram-link-target" type="checkbox" name="_envira_gallery[instagram_link_target]" value="<?php echo $instance->get_config( 'instagram_link_target', $instance->get_config_default( 'instagram_link_target' ) ); ?>" <?php checked( $instance->get_config( 'instagram_link_target', $instance->get_config_default( 'instagram_link_target' ) ), 1 ); ?> />
                                <span class="description"><?php _e( 'Opens the link to Instagram in a new browser tab', 'envira-instagram' ); ?></span>
                            </td>
                        </tr>

                        <tr id="envira-config-instagram-caption-box">
                            <th scope="row">
                                <label for="envira-config-instagram-caption"><?php _e( 'Use Photo Caption?', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-instagram-caption" type="checkbox" name="_envira_gallery[instagram_caption]" value="<?php echo $instance->get_config( 'instagram_caption', $instance->get_config_default( 'instagram_caption' ) ); ?>" <?php checked( $instance->get_config( 'instagram_caption', $instance->get_config_default( 'instagram_caption' ) ), 1 ); ?> />
                                <span class="description"><?php _e( 'Displays the photo caption from Instagram on the slide.', 'envira-instagram' ); ?></span>
                            </td>
                        </tr>
                        <tr id="envira-config-instagram-caption-limit-box">
                            <th scope="row">
                                <label for="envira-config-instagram-caption-limit"><?php _e( 'Limit Caption Length', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-instagram-caption-limit" type="number" name="_envira_gallery[instagram_caption_length]" value="<?php echo $instance->get_config( 'instagram_caption_length', $instance->get_config_default( 'instagram_caption_length' ) ); ?>" />
                                <p class="description"><?php _e( 'Limits the number of words to display for each caption.', 'envira-instagram' ); ?></p>
                            </td>
                        </tr>
                        <tr id="envira-config-instagram-cache-box">
                            <th scope="row">
                                <label for="envira-config-instagram-cache"><?php _e( 'Cache Data from Instagram?', 'envira-instagram' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-instagram-cache" type="checkbox" name="_envira_gallery[instagram_cache]" value="<?php echo $instance->get_config( 'instagram_cache', $instance->get_config_default( 'instagram_cache' ) ); ?>" <?php checked( $instance->get_config( 'instagram_cache', $instance->get_config_default( 'instagram_cache' ) ), 1 ); ?> />
                                <span class="description"><?php _e( 'Caches the data from Instagram to improve performance (recommended).', 'envira-instagram' ); ?></span>
                            </td>
                        </tr>
                        <?php do_action( 'envira_instagram_box', $post ); ?>
                    </tbody>
                </table>

            </div>
            <?php
        }
		    
    }

    /**
     * Helper method for retrieving link options.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_instagram_link_options() {

        $instance = Envira_Instagram_Common::get_instance();
        return $instance->get_instagram_link_options();

    }

    /**
     * Outputs a preview of the Instagram Gallery, based on the Gallery Settings.
     *
     * @since 1.0.5
     *
     * @param   array   $data       Gallery
     * @return  string              Preview HTML Output
     */
    public function preview_display( $data ) {

        if ( !isset( $data['id'] ) ) { 
            return;
        }

        // Inject Instagram Images into Gallery.
        $data['gallery'] = Envira_Instagram_Shortcode::get_instance()->_get_instagram_data( $data['id'], $data );

        // Output the preview.
        ?>
        <p class="envira-intro">
            <?php _e( 'Instagram Gallery Preview', 'envira-instagram' ); ?>
        </p>
        <ul id="envira-gallery-preview-output" class="envira-gallery-images-output grid">
            <?php 
            if ( ! empty( $data['gallery'] ) ) {
                foreach ( $data['gallery'] as $id => $item ) {
                    ?>
                    <li class="envira-gallery-image">
                        <img src="<?php echo esc_url( $item['thumb'] ); ?>" />
                        <div class="meta">
                            <div class="title"><?php echo ( isset( $item['title'] ) ? $item['title'] : '' ); ?></div>
                        </div>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php

    }

    /**
     * Returns an array of Instagram images for the given Gallery ID, allowing the Albums Addon
     * to display the images so that the user can choose an image as the cover for that Gallery
     * within an Album
     *
     * @since 1.0.6
     *
     * @param   array   $images         Gallery Images
     * @param   int     $gallery_id     Gallery ID
     * @param   array   $gallery_data   Gallery Data
     * @return  array                   Gallery Images
     */
    public function albums_inject_images_for_cover_image_selection( $images, $gallery_id, $gallery_data ) {

        // Bail if not an Instagram Gallery
        if ( 'instagram' != Envira_Gallery_Shortcode::get_instance()->get_config( 'type', $gallery_data ) ) {
            return $images;
        }

        // Attempt to get images from Instagram for the Gallery.
        $instagram_images = Envira_Instagram_Shortcode::get_instance()->_get_instagram_data( $gallery_id, $gallery_data );

        // If this failed, return the original supplied images
        if ( ! $instagram_images ) {
            return $images;
        }

        // Instagram images were returned, so return them to the Albums Addon for cover image selection.
        return $instagram_images;

    }

    /**
     * Saves the addon's settings for Galleries.
     *
     * @since 1.0.0
     *
     * @param array $settings  Array of settings to be saved.
     * @param int $pos_tid     The current post ID.
     * @return array $settings Amended array of settings to be saved.
     */
    public function save( $settings, $post_id ) {

        // If not saving an Instagram gallery, do nothing.
        if ( ! isset( $_POST['_envira_gallery']['type_instagram'] ) ) {
            return $settings;
        }

        // If Instagram isn't authorized, but the user has chosen the Instagram gallery type, we won't have any settings to save
        // Get instances and auth
        $common = Envira_Instagram_Common::get_instance();
        $auth = $common->get_instagram_auth();
        if ( empty( $auth['token'] ) ) {
            return $settings;
        }

        // Save the settings.
        $settings['config']['instagram_type']    = esc_attr( $_POST['_envira_gallery']['instagram_type'] );
        $settings['config']['instagram_number']  = absint( $_POST['_envira_gallery']['instagram_number'] );
        $settings['config']['instagram_res']     = esc_attr( $_POST['_envira_gallery']['instagram_res'] );
        $settings['config']['instagram_link']    = sanitize_text_field( $_POST['_envira_gallery']['instagram_link'] );
        $settings['config']['instagram_link_target'] = isset( $_POST['_envira_gallery']['instagram_link_target'] ) ? 1 : 0;
        $settings['config']['instagram_caption'] = isset( $_POST['_envira_gallery']['instagram_caption'] ) ? 1 : 0;
        $settings['config']['instagram_caption_length'] = absint( $_POST['_envira_gallery']['instagram_caption_length'] );        
        $settings['config']['instagram_cache']   = isset( $_POST['_envira_gallery']['instagram_cache'] ) ? 1 : 0;

        // Return
        return $settings;
    
    }

    /**
     * Flush Gallery cache on save
     *
     * @since 1.0.0
     *
     * @param int $post_id Post ID
     * @param string $slug Post Slug
     */
    function flush_caches( $post_id, $slug ) {

        delete_transient( '_envira_instagram_' . $post_id );
        delete_transient( '_envira_instagram_' . $slug );

    }
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Instagram_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Instagram_Metaboxes ) ) {
            self::$instance = new Envira_Instagram_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metaboxes class.
$envira_instagram_metaboxes = Envira_Instagram_Metaboxes::get_instance();