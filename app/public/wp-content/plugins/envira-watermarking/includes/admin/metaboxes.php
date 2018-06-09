<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Watermarking
 * @author  Envira Team
 */
class Envira_Watermarking_Metaboxes {

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

        // Get instances
        $this->base = Envira_Watermarking::get_instance();

        add_action( 'envira_gallery_metabox_scripts', array( $this, 'metabox_scripts' ) );
		add_action( 'envira_albums_metabox_scripts', array( $this, 'metabox_scripts' ) );

		// Envira Gallery
        add_action( 'envira_gallery_metabox_styles', array( $this, 'css' ) );
        add_filter( 'envira_gallery_tab_nav', array( $this, 'tabs' ) );
		add_action( 'envira_gallery_tab_watermarking', array( $this, 'watermarking_box' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_settings_save' ), 10, 2 );

    }

    /**
     * Initializes scripts for the metabox admin.
     *
     * @since 1.0.0
     *
     * @param string $key The user license key.
     */
    public function metabox_scripts() {

        // Conditional Fields
        wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );

        wp_enqueue_script( $this->base->plugin_slug . '-metabox', plugins_url( 'assets/js/metabox.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
    }

    /**
     * Enqueue JS for the Metabox
     */
    public function js() {

        
        
    }

    /**
     * Enqueue CSS for the Metabox
     */
    public function css() {

        wp_enqueue_style( $this->base->plugin_slug . '-metabox', plugins_url( 'assets/css/metabox.css', $this->base->file ) );
        
    }

    /**
     * Adds a tab for this Addon
     *
     * @since 1.0.0
     *
     * @param array $tabs Tabs
     * @return array Tabs
     */
    public function tabs( $tabs ) {

        $instance_meta = Envira_Gallery_Metaboxes::get_instance();

        if ( $instance_meta->get_config( 'type' ) == 'dynamic' ) {
            return $tabs;
        }

        $tabs['watermarking'] = __( 'Watermarking', 'envira-watermarking' );
        return $tabs;
        
    }
   
    /**
     * Adds addon settings UI to the Watermarking tab
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    public function watermarking_box( $post ) {
        
        // Setup instance and meta key
        $instance = Envira_Gallery_Metaboxes::get_instance();
        $common = Envira_Watermarking_Common::get_instance();
        $key = '_envira_gallery';
        ?>
        <div id="envira-watermarking">
            <p class="envira-intro">
                <?php _e( 'Watermarking Gallery Settings', 'envira-watermarking' ); ?>

                    <small>

                        <?php _e( 'The settings below adjust the Watermarking options for the Gallery output.', 'envira-watermarking' ); ?>

                        <?php if ( apply_filters('envira_whitelabel', false )  ): ?>
                            <?php do_action('envira_watermarking_whitelabel_tab_helptext'); ?>
                        <?php else: ?>

                    <br />
                    <?php _e( 'Need some help?', 'envira-watermarking' ); ?>
                    <a href="http://enviragallery.com/docs/watermarking-addon/" class="envira-doc" target="_blank">
                        <?php _e( 'Read the Documentation', 'envira-watermarking' ); ?>
                    </a>
                    or
                    <a href="https://www.youtube.com/embed/Us4HAa__FEQ/?rel=0" class="envira-video" target="_blank">
                        <?php _e( 'Watch a Video', 'envira-watermarking' ); ?>
                    </a>

                        <?php endif; ?>

                    </small>


            </p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-watermarking-enabled-box">
                        <th scope="row">
                            <label for="envira-watermarking-enabled"><?php _e( 'Enabled?', 'envira-watermarking' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-watermarking-enabled" type="checkbox" name="<?php echo $key; ?>[watermarking]" value="1" <?php checked( $instance->get_config( 'watermarking', $instance->get_config_default( 'watermarking' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables the Watermarking of images uploaded to this Gallery.', 'envira-watermarking' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-watermarking-image-box">
                        <th scope="row">
                            <label for="envira-watermarking-image"><?php _e( 'Watermark', 'envira-watermarking' ); ?></label>
                        </th>
                        <td>
                            <!-- Stores ID and image URL -->
                            <input type="hidden" id="envira-watermarking-image-ID" name="<?php echo $key ?>[watermarking_image_id]" value="<?php echo $instance->get_config( 'watermarking_image_id', $instance->get_config_default( 'watermarking_image_id' ) ); ?>" />
            
                            <!-- Existing Image -->
                            <span class="image envira-watermarking-image">
                                <?php
                                $image_id = $instance->get_config( 'watermarking_image_id', $instance->get_config_default( 'watermarking_image_id' ) );
                                if ( ! empty( $image_id ) ) {
                                    // Get image
                                    $image = wp_get_attachment_image_src( $image_id, 'thumbnail' );
                                    if ( is_array( $image ) ) {
                                        ?>
                                        <img src="<?php echo $image[0]; ?>" />
                                        <?php
                                    }
                                }
                                ?>
                            </span>

                            <span class="wp-media-buttons envira-watermarking-button" style="float:none;">
                                <a href="#" class="button insert-media-url add_media" data-id="envira-watermarking-image-ID">
                                    <span class="wp-media-buttons-icon"></span>
                                    <?php 
                                    
                                    if ( ! empty( $image_id ) ) {
                                        _e( 'Change Watermark', 'envira-watermarking' );
                                    } else {
                                        _e( 'Choose Watermark', 'envira-watermarking' );    
                                    }
                                    ?>
                                </a>
                            </span>

                            <span class="description"><?php _e( 'The image to use as the watermark.', 'envira-watermarking' ); ?></span>
                        </td>
                    </tr>  
                    <tr id="envira-watermarking-position-box">
                        <th scope="row">
                            <label for="envira-watermarking-position"><?php _e( 'Position', 'envira-watermarking' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-watermarking-position" name="_envira_gallery[watermarking_position]">
                                <?php foreach ( (array) $common->get_positions() as $i => $data ) : ?>
                                    <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'watermarking_position', $instance->get_config_default( 'watermarking_position' ) ) ); ?>><?php echo $data['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Define where to position the watermark over the image.', 'envira-watermarking' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-watermarking-margin-box">
                        <th scope="row">
                            <label for="envira-watermarking-margin"><?php _e( 'Margin', 'envira-watermarking' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-watermarking-margin" type="number" name="<?php echo $key; ?>[watermarking_margin]" value="<?php echo $instance->get_config( 'watermarking_margin', $instance->get_config_default( 'watermarking_margin' ) ); ?>" /> <span class="envira-unit"><?php _e( 'px', 'envira-watermarking' ); ?></span>
                            <p class="description"><?php _e( 'Sets the space between the edge of the image and the watermark.', 'envira-watermarking' ); ?></p>
                        </td>
                    </tr>
                    <tr id="envira-watermarking-watermark-existing-images-box">
                        <th scope="row">
                            <label><?php _e( 'Apply to Existing Images?', 'envira-watermarking' ); ?></label>
                        </th>
                        <td>
                            <input <?php if ( !$image_id ) { ?>disabled="disabled"<?php } ?> type="submit" name="envira-watermarking-watermark-existing-images" value="<?php _e( 'Apply', 'envira-watermarking' ); ?>" class="button" />
                            <p class="description"><?php _e( 'Use this option to apply your watermark to any images uploaded to this Gallery before the Watermark was defined. Any images that already have a watermark will not be changed. You must have already selected an image and updated the gallery to apply.', 'envira-watermarking' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php

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
	function gallery_settings_save( $settings, $post_id ) {

	    $settings['config']['watermarking'] = ( isset( $_POST['_envira_gallery']['watermarking'] ) ? 1 : 0 );
        $settings['config']['watermarking_image_id'] = absint( $_POST['_envira_gallery']['watermarking_image_id'] );
        $settings['config']['watermarking_position'] = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_envira_gallery']['watermarking_position'] );
        $settings['config']['watermarking_margin'] = absint( $_POST['_envira_gallery']['watermarking_margin'] );
        
        // If Apply button was clicked, we need to apply the watermark to all images in the gallery that don't have it
        if ( ! isset( $_POST['envira-watermarking-watermark-existing-images'] ) ) {
            return $settings;
        }

	    // Check we have some gallery images
        if ( ! is_array( $settings['gallery'] ) ) {
            return $settings;
        }

        // Get AJAX instance
        $instance           = Envira_Watermarking_Ajax::get_instance(); 
        $instance_meta      = Envira_Gallery_Metaboxes::get_instance();
        $instance_common    = Envira_Gallery_Common::get_instance();

        // Iterate through gallery images
        foreach ( $settings['gallery'] as $image_id => $image ) {
            // add_watermark() will skip the image if we've already added a watermark to it
            $instance->add_watermark( $image_id, $post_id );
            // recreate the thumbnails, so they have the watermarks as well
            $file = get_attached_file( $image_id );
            wp_generate_attachment_metadata( $image_id, $file ); 
            // echo $post_id; echo '<br>'; echo $image_id; echo '<br>';
            $args = array(
                'position' => 'c',
                'width'    => $instance_meta->get_config( 'crop_width', $instance_meta->get_config_default( 'crop_width' ) ),
                'height'   => $instance_meta->get_config( 'crop_height', $instance_meta->get_config_default( 'crop_height' ) ),
                'quality'  => 100,
                'retina'   => false
            );
            $image = wp_get_attachment_image_src( $image_id, 'full' );    
            $force_overwrite = true;  
            // Generate the new cropped gallery image.
            $cropped_image = $instance_common->resize_image( $image[0], $args['width'], $args['height'], false, $args['position'], $args['quality'], $args['retina'], null, $force_overwrite );

        }

        // If the lightbox thumbnails option is checked, crop images accordingly.
        if ( isset( $settings['config']['thumbnails'] ) && $settings['config']['thumbnails'] ) {
            $args = array(
                'position' => 'c',
                'width'    => $instance_meta->get_config( 'thumbnails_width', $instance_meta->get_config_default( 'thumbnails_width' ) ),
                'height'   => $instance_meta->get_config( 'thumbnails_height', $instance_meta->get_config_default( 'thumbnails_height' ) ),
                'quality'  => 100,
                'retina'   => false
            );
            $args = apply_filters( 'envira_gallery_crop_image_args', $args );
            $instance_meta->crop_thumbnails( $args, $post_id, true ); // true forces thumbnails to be overridden
        }

        // Return settings
        return $settings;
	
	}
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Watermarking_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Watermarking_Metaboxes ) ) {
            self::$instance = new Envira_Watermarking_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_watermarking_metaboxes = Envira_Watermarking_Metaboxes::get_instance();