<?php
/** 
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Breadcrumbs
 * @author  Envira Team
 */
class Envira_Breadcrumbs_Metaboxes {

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
        $this->base = Envira_Breadcrumbs::get_instance();

        add_action( 'envira_gallery_metabox_scripts', array( $this, 'metabox_scripts' ) );

        // Tab and Metabox
        add_filter( 'envira_albums_tab_nav', array( $this, 'tabs' ) );
        add_action( 'envira_albums_tab_breadcrumbs', array( $this, 'breadcrumbs_box' ) );

        // Save Settings
        add_filter( 'envira_albums_save_settings', array( $this, 'save' ), 10, 2 );

    }

    /**
     * Initializes scripts for the metabox admin.
     *
     * @since 1.0.2
     *
     * @param string $key The user license key.
     */
    public function metabox_scripts() {
        // Conditional Fields
        wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );
    }

    /**
     * Registers the Breadcrumbs tab for Albums
     *
     * @since 1.0
     *
     * @param array     $tabs   Admin Tabs when editing an Album
     * @return array            Admin Tabs
     */
    public function tabs( $tabs ) {

        global $post;

        // get option
        $dynamic_id = get_option( 'envira_dynamic_album' );

        if ( !$dynamic_id || $dynamic_id != $post->ID ) {

            $tabs['breadcrumbs'] = __( 'Breadcrumbs', 'envira-breadcrumbs' );

        } 
       
        return $tabs;

    }

    /**
     * Outputs options for enabling/disabling Breadcrumbs for Albums
     *
     * @since 1.0.0
     *
     * @param WP_Post $post Album Post
     */
    public function breadcrumbs_box( $post ) {

        // Get instance
        $instance = Envira_Albums_Metaboxes::get_instance();
        ?>
        <div id="envira-breadcrumbs">
            <p class="envira-intro">
                <?php _e( 'Breadcrumb Settings', 'envira-breadcrumbs' ); ?>
                <small>
                    <?php _e( 'The settings below adjust the breadcrumb options on Galleries assigned to this Album.', 'envira-breadcrumbs' ); ?>
                    <br />
                    <?php _e( 'Need some help?', 'envira-breadcrumbs' ); ?>
                    <a href="http://enviragallery.com/docs/breadcrumbs-addon/" class="envira-doc" target="_blank">
                        <?php _e( 'Read the Documentation', 'envira-breadcrumbs' ); ?>
                    </a>
                    or
                    <a href="https://www.youtube.com/embed/qEsw50Q0zTw" class="envira-video" target="_blank">
                        <?php _e( 'Watch a Video', 'envira-breadcrumbs' ); ?>
                    </a>
                </small>
            </p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-breadcrumbs-enabled-box">
                        <th scope="row">
                            <label for="envira-breadcrumbs-enabled"><?php _e( 'Enable Envira Breadcrumbs?', 'envira-breadcrumbs' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-breadcrumbs-enabled" type="checkbox" name="_eg_album_data[config][breadcrumbs_enabled]" value="1" <?php checked( $instance->get_config( 'breadcrumbs_enabled', $instance->get_config_default( 'breadcrumbs_enabled' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If enabled, breadcrumb navigation can be displayed by this Addon above this Album and its Galleries.', 'envira-breadcrumbs' ); ?></span>
                        </td>
                    </tr>
                    <tr id="envira-breadcrumbs-separator-box">
                        <th scope="row">
                            <label for="envira-breadcrumbs-separator"><?php _e( 'Breadcrumb Separator', 'envira-breadcrumbs' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-breadcrumbs-separator" type="text" name="_eg_album_data[config][breadcrumbs_separator]" value="<?php echo $instance->get_config( 'breadcrumbs_separator', $instance->get_config_default( 'breadcrumbs_separator' ) ); ?>" />
                            <p class="description">
                                <?php _e( 'The separator to use between breadcrumb items. Examples:', 'envira-breadcrumbs' ); ?>
                                <code>&raquo;, &rsaquo;, &rarr;, /</code>
                            </p>
                        </td>
                    </tr>
                    <tr id="envira-breadcrumbs-enabled-yoast-box">
                        <th scope="row">
                            <label for="envira-breadcrumbs-yoast-enabled"><?php _e( 'Enable Yoast Breadcrumbs?', 'envira-breadcrumbs' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-breadcrumbs-yoast-enabled" type="checkbox" name="_eg_album_data[config][breadcrumbs_enabled_yoast]" value="1" <?php checked( $instance->get_config( 'breadcrumbs_enabled_yoast', $instance->get_config_default( 'breadcrumbs_enabled_yoast' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'If you\'re using the Yoast SEO plugin\'s breadcrumb functionality, enabling this option injects the Album to the breadcrumb list when a Gallery is accessed from an Album.', 'envira-breadcrumbs' ); ?></span>
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
    function save( $settings, $post_id ) {

        $settings['config']['breadcrumbs_enabled']        = isset( $_POST['_eg_album_data']['config']['breadcrumbs_enabled'] ) ? 1 : 0;
        $settings['config']['breadcrumbs_separator']      = sanitize_text_field( esc_attr( $_POST['_eg_album_data']['config']['breadcrumbs_separator'] ) );
        $settings['config']['breadcrumbs_enabled_yoast']  = isset( $_POST['_eg_album_data']['config']['breadcrumbs_enabled_yoast'] ) ? 1 : 0;
        
        return $settings;
    
    }

	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Breadcrumbs_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Breadcrumbs_Metaboxes ) ) {
            self::$instance = new Envira_Breadcrumbs_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_breadcrumbs_metaboxes = Envira_Breadcrumbs_Metaboxes::get_instance();