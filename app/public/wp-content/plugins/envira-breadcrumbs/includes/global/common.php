<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Breadcrumbs
 * @author  Envira Team
 */
class Envira_Breadcrumbs_Common {

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
		
        add_filter( 'envira_albums_defaults', array( $this, 'defaults' ), 10, 2 );

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

        $defaults['breadcrumbs_enabled']        = 0;
        $defaults['breadcrumbs_separator']      = '»';
        $defaults['breadcrumbs_enabled_yoast']  = 0;

        // Return
        return $defaults;
    
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Breadcrumbs_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Breadcrumbs_Common ) ) {
            self::$instance = new Envira_Breadcrumbs_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_breadcrumbs_common = Envira_Breadcrumbs_Common::get_instance();