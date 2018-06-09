<?php
/**
 * Order class.
 *
 * @since 1.0.0
 *
 * @package Envira_Proofing
 * @author  Envira Team
 */
class Envira_Proofing_Order {

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
		
        add_action( 'init', array( $this, 'migrate' ) );
        
    }

    /**
    * Iterates through galleries, updating the meta structure for 1.0.4+
    *
    * @since 1.0.4
    */
    public function migrate() {

        // Bail if the 1.0.4 migration process completed
        $migrated = get_option( 'envira_proofing_104' );
        if ( $migrated ) {
            return;
        }

        // Get galleries
        $galleries = Envira_Gallery::get_instance()->get_galleries();
        if ( ! $galleries || count( $galleries ) == 0 ) {
            return;
        }

        // Iterate through galleries
        foreach ( $galleries as $gallery ) {
            // Check for 1.0.3- post meta
            $images = get_post_meta( $gallery['id'], '_envira_proofing_order', true );
            if ( empty( $images ) ) {
                continue;
            }
            
            // Move order data
            $order = array(
                'images'    => get_post_meta( $gallery['id'], '_envira_proofing_order', true ),
                'email'     => false,
                'notes'     => get_post_meta( $gallery['id'], '_envira_proofing_order_notes', true ),
                'submitted' => get_post_meta( $gallery['id'], '_envira_proofing_order_submitted', true ),
            );

            // Save order
            $this->save_order( $gallery['id'], $order );

            // Delete meta
            //delete_post_meta( $gallery['id'], '_envira_proofing_order' );
            //delete_post_meta( $gallery['id'], '_envira_proofing_order_notes' );
            //delete_post_meta( $gallery['id'], '_envira_proofing_order_submitted' );
        }

        // Mark the migration as completed
        update_option( 'envira_proofing_104', true );

    }

    /**
     * Retrieves an order from the given Gallery ID
     *
     * @since 1.0
     *
     * @param int $gallery_id Gallery ID
     */
    public function get_orders( $gallery_id ) {

        // Get meta
        $orders = get_post_meta( $gallery_id, '_envira_proofing_order', true );
        
        // Filter
        $orders = apply_filters( 'envira_proofing_order_get_orders', $orders, $gallery_id );

        return $orders; 

    }

    /**
     * Attempts to find an order for the given email address.
     *
     * If one doesn't exist, creates a new, blank order comprising of email + optional name
     *
     * @param   int     $gallery_id     Gallery ID
     * @param   array   $data           Gallery Config
     * @param   string  $email          Email Address
     * @param   string  $name           Name
     * @return  array                   Order
     */
    public function get_or_create_order( $gallery_id, $data, $email, $name = '', $notes = '' ) {

        // Get orders
        $orders = $this->get_orders( $gallery_id );

        // Get order
        if ( isset( $orders[ $email ] ) ) {
            // Existing order - update name + notes
            $orders[ $email ]['name'] = $name;
            $orders[ $email ]['notes'] = $notes;
        } else {
            // No order - create
            $orders[ $email ] = array(
                'name'      => $name,
                'email'     => $email,
                'images'    => array(),
                'submitted' => false,
                'notes'     => $notes,
            );
        }
        
        // Save
        update_post_meta( $gallery_id, '_envira_proofing_order', $orders );

        // Return order
        return $orders[ $email ];

    }

    /**
     * Retrieves an order from the given Gallery ID
     *
     * @since 1.0
     *
     * @param   int     $gallery_id     Gallery ID
     * @param   array   $data           Gallery Config
     * @param   string  $email          Email Address
     * @return  array                   Order
     */
    public function get_order( $gallery_id, $data, $email ) {

        // Get orders
        $orders = $this->get_orders( $gallery_id );

        // Get order
        if ( isset( $orders[ $email ] ) ) {
            return $orders[ $email ];
        }

        // Something went wrong!
        return false;

    }

    /**
     * Saves the given order and order notes against the given Gallery ID
     *
     * @since 1.0
     *
     * @param int       $gallery_id Gallery ID
     * @param array     $order      Order
     * @param string    $email      Email Address (optional)
     */
    public function save_order( $gallery_id, $order ) {

        // Get orders
        $orders = $this->get_orders( $gallery_id );

        // If no orders have been stored before, setup the array now
        if ( ! is_array( $orders ) ) {
            $orders = array();
        }

        // Store the order
        $orders[ $order['email'] ] = $order;

        // Save
        update_post_meta( $gallery_id, '_envira_proofing_order', $orders );

        // If order is submitted, we may need to send an email notification
        if ( ! isset( $order['submitted'] ) || ! $order['submitted'] ) {
            return;
        }

        // Get gallery
        $data = Envira_Gallery::get_instance()->get_gallery( $gallery_id );
        if ( ! $data ) {
            return;
        }

        // Get email address for notifications
        $instance = Envira_Gallery_Shortcode::get_instance();
        $notification_email = $instance->get_config( 'proofing_email', $data );
        if ( empty( $notification_email ) ) {
            return;
        }
        if ( ! filter_var( $notification_email, FILTER_VALIDATE_EMAIL ) ) {
            return;
        }

        // Get email subject and message, parsing any tags in them with real data
        $email_subject = $this->parse_tags( $instance->get_config( 'proofing_email_subject', $data ), $gallery_id, $data, $order );
        $email_message = $this->parse_tags( $instance->get_config( 'proofing_email_message', $data ), $gallery_id, $data, $order );

        add_filter( 'wp_mail_content_type', array( $this, 'wpdocs_set_html_mail_content_type' ) );

        // Send an email
        wp_mail( $notification_email, $email_subject, $email_message );

        remove_filter( 'wp_mail_content_type', array( $this, 'wpdocs_set_html_mail_content_type' ) );

    }

    /**
     * Set format of email to HTML
     * 
     * @since 1.0.8
     *
     */
    public function wpdocs_set_html_mail_content_type() {
        return 'text/html';
    }

    /**
     * Parses the following tags in an email subject or message, replacing them with real data:
     * {title}: Gallery Title
     * {url}: Gallery Edit URL (specifically, the Proofing tab)
     * {email}: Customer's Email Address
     * 
     * @TODO Output the order details in some nice HTML!
     *
     * @since 1.0.8
     *
     * @param   string  $string         The string to parse
     * @param   int     $gallery_id     Gallery ID
     * @param   array   $data           Gallery Config
     * @return  string                  Parsed string
     */
    private function parse_tags( $string, $gallery_id, $data, $order ) {

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        // Replace tags with real content
        $string = str_replace( '{title}', $instance->get_config( 'title', $data ), $string );
        $string = str_replace( '{url}', get_admin_url() . '/post.php?post=' . $gallery_id . '&action=edit#!envira-tab-proofing', $string );
        $string = str_replace( '{order_email}', $order['email'], $string );

        return $string;

    }

    /**
     * Saves the given notes against the given Gallery ID
     *
     * @since 1.0
     *
     * @param int       $gallery_id Gallery ID
     * @param array     $order      Order
     * @param string    $notes      Notes
     */
    public function save_order_notes( $gallery_id, $order, $notes ) {

        // Get orders
        $orders = $this->get_orders( $gallery_id );

        // If no orders have been stored before, setup the array now
        if ( ! is_array( $orders ) ) {
            return false;
        }

        // Update notes
        $order['notes'] = $notes;

        // Update orders
        $orders[ $order['email'] ] = $order;

        // Save
        update_post_meta( $gallery_id, '_envira_proofing_order', $orders );

    }

    /**
     * Marks the given Gallery ID as not submitted
     *
     * @since 1.0
     *
     * @param int       $gallery_id     Gallery ID
     * @param string    $email          Email
     */
    public function unlock_order( $gallery_id, $email ) {

        // Get orders
        $orders = $this->get_orders( $gallery_id );

        // Change order submitted flag
        $orders[ $email ]['submitted'] = false;

        // Save
        update_post_meta( $gallery_id, '_envira_proofing_order', $orders );

    }

    /**
     * Deletes order data for the given Gallery ID
     *
     * @since 1.0
     *
     * @param int       $gallery_id     Gallery ID
     * @param string    $email          Email
     */
    public function delete_order( $gallery_id, $email ) {
    
        // Get orders
        $orders = $this->get_orders( $gallery_id );

        // Unset order
        unset( $orders[ $email ] );

        // Save
        update_post_meta( $gallery_id, '_envira_proofing_order', $orders );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Proofing_Order object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Proofing_Order ) ) {
            self::$instance = new Envira_Proofing_Order();
        }

        return self::$instance;

    }

}

// Load the order class.
$envira_proofing_order = Envira_Proofing_Order::get_instance();