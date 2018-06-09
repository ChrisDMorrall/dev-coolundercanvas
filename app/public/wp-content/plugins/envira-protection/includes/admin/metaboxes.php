<?php
/**
 * Metabox class.
 *
 * @since 1.0.9
 *
 * @package Envira_Protection
 * @author  Envira Team
 */
class Envira_Protection_Metaboxes {

    /**
     * Holds the class object.
     *
     * @since 1.0.9
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.9
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Primary class constructor.
     *
     * @since 1.0.9
     */
    public function __construct() {

        // Gallery
        add_action( 'envira_gallery_misc_box', array( $this, 'settings' ) );
        add_filter( 'envira_gallery_save_settings', array( $this, 'save_settings' ), 10, 2 );

        // Albums
        add_action( 'envira_albums_misc_box', array( $this, 'settings' ) );
        add_filter( 'envira_albums_save_settings', array( $this, 'save_settings' ), 10, 2 );
        
    }

    /**
     * Adds addon setting to the Misc tab.
     *
     * @since 1.0.9
     *
     * @param object $post The current post object.
     */
    public function settings( $post ) {

        switch ( $post->post_type ) {
            /**
            * Gallery
            */
            case 'envira':
                $instance = Envira_Gallery_Metaboxes::get_instance();
                $key = '_envira_gallery';
                break;
            
            /**
            * Album
            */
            case 'envira_album':
                $instance = Envira_Albums_Metaboxes::get_instance();
                $key = '_eg_album_data[config]';
                break;
        }
        ?>
        <tr id="envira-config-protection-box">
            <th scope="row">
                <label for="envira-config-protection"><?php _e( 'Enable Image Protection?', 'envira-protection' ); ?></label>
            </th>
            <td>
                <input id="envira-config-protection" type="checkbox" name="<?php echo $key; ?>[protection]" value="<?php echo $instance->get_config( 'protection', $instance->get_config_default( 'protection' ) ); ?>" <?php checked( $instance->get_config( 'protection', $instance->get_config_default( 'protection' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'Enables or disables image protection for gallery images (display and lightbox).', 'envira-protection' ); ?></span>
            </td>
        </tr>
        <?php

    }

    /**
     * Saves the addon setting.
     *
     * @since 1.0.9
     *
     * @param array $settings  Array of settings to be saved.
     * @param int $post_id     The current post ID.
     * @return array $settings Amended array of settings to be saved.
     */
    function save_settings( $settings, $post_id ) {

        // Gallery
        if ( isset( $_POST['_envira_gallery'] ) ) {
            $settings['config']['protection'] = isset( $_POST['_envira_gallery']['protection'] ) ? 1 : 0;
        }

        // Album
        if ( isset( $_POST['_eg_album_data'] ) ) {
            $settings['config']['protection'] = isset( $_POST['_eg_album_data']['config']['protection'] ) ? 1 : 0;
        }
        
        return $settings;

    }    
    
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.9
     *
     * @return object The Envira_Protection_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;

    }

}

// Load the metaboxes class.
$envira_protection_metaboxes = Envira_Protection_Metaboxes::get_instance();