<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Watermarking
 * @author  Envira Team
 */
class Envira_Watermarking_Common {

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

        add_filter( 'envira_gallery_defaults', array( $this, 'defaults' ), 10, 2 );
		
    }

    /**
     * Adds the default settings for this addon.
     *
     * @since 1.0.0
     *
     * @param array $defaults  Array of default config values.
     * @param int $post_id     The current post ID.
     * @return array $defaults Amended array of default config values.
     */
    function defaults( $defaults, $post_id ) {
    
        // Add default settings to main defaults array
        $defaults['watermarking']           = 0;
        $defaults['watermarking_image_id']  = '';
        $defaults['watermarking_position']  = 'bottom-right';
        $defaults['watermarking_margin']    = 10;

        // Return
        return $defaults;
    
    }

    /**
     * Helper method for retrieving positions.
     *
     * @since 1.0.0
     *
     * @return array Array of positions.
     */
    public function get_positions() {

        $positions = array(
            array(
                'value' => 'top-left',
                'name'  => __( 'Top Left', 'envira-watermarking' ),
            ),
            array(
                'value' => 'top-right',
                'name'  => __( 'Top Right', 'envira-watermarking' ),
            ),
            array(
                'value' => 'center',
                'name'  => __( 'Center', 'envira-watermarking' ),
            ),
            array(
                'value' => 'bottom-left',
                'name'  => __( 'Bottom Left', 'envira-watermarking' ),
            ),
            array(
                'value' => 'bottom-right',
                'name'  => __( 'Bottom Right', 'envira-watermarking' ),
            ),
        );

        return apply_filters( 'envira_watermarking_positions', $positions );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Watermarking_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Watermarking_Common ) ) {
            self::$instance = new Envira_Watermarking_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_watermarking_common = Envira_Watermarking_Common::get_instance();