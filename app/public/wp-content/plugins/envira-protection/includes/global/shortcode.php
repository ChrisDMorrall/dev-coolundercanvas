<?php
/**
 * Shortcode class.
 *
 * @since 1.0.9
 *
 * @package Envira_Protection
 * @author  Envira Team
 */
class Envira_Protection_Shortcode {

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
     * Holds the base class object.
     *
     * @since 1.0.9
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.9
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Protection::get_instance();

        // Register CSS
        wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/envira-protection.css', $this->base->file ));

        // Insert Loading Scripts
        add_action( 'envira_gallery_before_output', array( $this, 'enable_gallery_protection' ) );
        add_action( 'envira_link_before_output', array( $this, 'enable_gallery_protection' ) );
        add_filter( 'envira_gallery_output_image_attr', array( $this, 'enable_drag_protection' ), 10, 5 );

        add_action( 'envira_albums_before_output',  array( $this, 'enable_albums_protection' ) );
    //    add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css' ) );

    }

    /**
    * Enqueue CSS and JS if Protection is enabled
    *
    * @since 1.0.0
    *
    * @param array $data Gallery Data
    */
    public function gallery_output_css( $data ) {

        // Check if Social Sharing Buttons output is enabled
        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'protection', $data ) && ! Envira_Albums_Shortcode::get_instance()->get_config( 'protection', $data ) ) {
            return;
        }

        // Enqueue CSS + JS

    }

    /**
     * Initializes drag protection for Galleries
     *
     * @since 1.0.9
     *
     * @param array $data Data for the Envira gallery
     * @return null       Return early if protection is not enabled.
     */
    public function enable_drag_protection( $attribute, $id, $item, $data, $i ) {

        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'protection', $data ) ) {
            return;
        }

        $attribute .= ' onmousedown="event.preventDefault()" ';

        return $attribute;

    }

    /**
     * Initializes image protection for Galleries
     *
     * @since 1.0.9
     *
     * @param array $data Data for the Envira gallery
     * @return null       Return early if protection is not enabled.
     */
    public function enable_gallery_protection( $data ) {

        if ( ! Envira_Gallery_Shortcode::get_instance()->get_config( 'protection', $data ) ) {
            return;
        }
        wp_enqueue_style( $this->base->plugin_slug . '-style' );

        $this->load_script();

    }

    /**
     * Initializes image protection for Albums
     *
     * @since 1.0.9
     *
     * @param array $data Album Configuration
     * @return null       Return early if protection is not enabled.
     */
    public function enable_albums_protection( $data ) {

        if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'protection', $data ) ) {
            return;
        }

        wp_enqueue_style( $this->base->plugin_slug . '-style' );

        $this->load_script();

    }

    /**
    * Enqueue the script to protect images
    *
    * @since 1.0.9
    */
    function load_script() {

        // wp_enqueue_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-protection-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-protection-min.js', $this->base->file ), array( 'jquery' ), time(), true );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.9
     *
     * @return object The Envira_Protection_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_protection_shortcode = Envira_Protection_Shortcode::get_instance();