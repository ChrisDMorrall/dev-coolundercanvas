<?php
/**
 * AJAX class.
 *
 * @since 1.0.0
 *
 * @package Envira_Proofing
 * @author  Envira Team
 */
class Envira_Proofing_Ajax {

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

        add_action( 'wp_ajax_envira_proofing_clear_order', array( $this, 'clear_order' ) );
        add_action( 'wp_ajax_envira_proofing_unlock_order', array( $this, 'unlock_order' ) );

    }
    
    /**
	* Clears order details for the given gallery ID
	*
	* @since 1.0.0
	*/
    public function clear_order() {

        // Run a security check first.
        check_ajax_referer( 'envira-proofing-clear-order', 'nonce' );

        // Get vars
        $post_id = absint( $_POST['post_id'] );
        $email = sanitize_text_field( $_POST['email'] );
        
        // Delete order
        $result = Envira_Proofing_Order::get_instance()->delete_order( $post_id, $email );

        // Done
        echo json_encode( $result );
        die();
		  
    }

    /**
    * Unlocks the order for the given gallery ID
    *
    * @since 1.0.0
    */
    public function unlock_order() {

        // Run a security check first.
        check_ajax_referer( 'envira-proofing-clear-order', 'nonce' );

        // Get vars
        $post_id = absint( $_POST['post_id'] );
        $email = sanitize_text_field( $_POST['email'] );
        
        // Delete order
        $result = Envira_Proofing_Order::get_instance()->unlock_order( $post_id, $email );

        // Done
        echo json_encode( $result );
        die();
          
    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Proofing_Ajax object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Proofing_Ajax ) ) {
            self::$instance = new Envira_Proofing_Ajax();
        }

        return self::$instance;

    }

}

// Load the AJAX class.
$envira_proofing_ajax = Envira_Proofing_Ajax::get_instance();