<?php
/**
 * Common class.
 *
 * @since 1.0.0
 *
 * @package Envira_Proofing
 * @author  Envira Team
 */
class Envira_Proofing_Common {

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

        // Lightbox
        $defaults['proofing_lightbox']                  = 0;
    
        // Proofing
        $defaults['proofing']                           = 0;
        $defaults['proofing_multiple_enabled']          = 0;
        $defaults['proofing_edit']                      = 0;
        $defaults['proofing_delete']                    = 0;
        $defaults['proofing_name_enabled']              = 0;
        $defaults['proofing_hide_gallery']              = 1;
        $defaults['proofing_multiple_label']            = __( 'Please enter your email address to begin the selection process.', 'envira-proofing' );
        $defaults['proofing_quantity_enabled']          = 0;
        $defaults['proofing_size_enabled']              = 0;
        $defaults['proofing_sizes']                     = array();
        $defaults['proofing_add_to_order_label']        = __( 'Add to Order', 'envira-proofing' );
        $defaults['proofing_save_button_label']         = __( 'Save', 'envira-proofing' );
        $defaults['proofing_edit_button_label']         = __( 'Edit Order', 'envira-proofing' );
        $defaults['proofing_delete_button_label']       = __( 'Delete Order', 'envira-proofing' );
        $defaults['proofing_notes_placeholder_text']    = __( 'Order Notes (Optional)', 'envira-proofing' );
        $defaults['proofing_save_notes_button_label']   = __( 'Save Order Notes', 'envira-proofing' );
        $defaults['proofing_submit_button_label']       = __( 'Submit', 'envira-proofing' );
        $defaults['proofing_submitted_message']         = __( 'Thanks for submitting your order. We will be in touch.', 'envira-proofing' );
        $defaults['proofing_email']                     = '';
        $defaults['proofing_email_box_position']        = '';
        $defaults['proofing_email_subject']             = __( 'Envira Proofing: New Order for Gallery: {title}', 'envira-proofing' );
        $defaults['proofing_email_message']             = __( 'A new order has been submitted by {order_email} for Gallery {title}', 'envira-proofing' ) . "\n\n" . __( 'View the order at {url}', 'envira-proofing' );
        
        // Return
        return $defaults;
    
    }

    /**
     * Helper method for retrieving email box positions.
     *
     * @since 1.0.0
     *
     * @return array Array of column data.
     */
    public function get_envira_email_box_positions() {

        $orientations = array(
            array(
                'value' => '',
                'name'  => __( 'Above', 'envira-proofing' ),
            ),
            array(
                'value' => 'below',
                'name'  => __( 'Below', 'envira-proofing' ),
            ),
        );

        return apply_filters( 'envira_proofing_email_box_positions', $orientations );

    }

    /**
     * Helper method for retrieving size orientations.
     *
     * @since 1.0.0
     *
     * @return array Array of column data.
     */
    public function get_size_orientations() {

        $orientations = array(
            array(
                'value' => '',
                'name'  => __( 'Landscape and Portrait', 'envira-proofing' ),
            ),
            array(
                'value' => 'landscape',
                'name'  => __( 'Landscape', 'envira-proofing' ),
            ),
            array(
                'value' => 'portrait',
                'name'  => __( 'Portrait', 'envira-proofing' ),
            ),
        );

        return apply_filters( 'envira_proofing_size_orientations', $orientations );

    }

    /**
     * Helper function to retrieve a Setting
     *
     * @since 1.0.0
     *
     * @param string $key Setting
     * @return array Settings
     */
    public function get_setting( $key ) {
        
        // Get settings
        $settings = $this->get_settings();

        // Check setting exists
        if ( ! is_array( $settings ) ) {
            return false;
        }
        if ( ! array_key_exists( $key, $settings ) ) {
            return false;
        }

        $setting = apply_filters( 'envira_proofing_setting', $settings[ $key ] );
        return $setting;

    }

    /**
     * Helper function to retrieve Settings
     *
     * @since 1.0.0
     *
     * @return array Settings
     */
    public function get_settings() {
        
        $settings = get_option( 'envira-proofing' );
        $settings = apply_filters( 'envira_proofing_settings', $settings );
        return $settings;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Proofing_Common object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Proofing_Common ) ) {
            self::$instance = new Envira_Proofing_Common();
        }

        return self::$instance;

    }

}

// Load the common class.
$envira_proofing_common = Envira_Proofing_Common::get_instance();