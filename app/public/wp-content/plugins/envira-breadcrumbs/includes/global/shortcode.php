<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Breadcrumbs
 * @author  Envira Team
 */

class Envira_Breadcrumbs_Shortcode {

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
	public $album_shortcode = null;
    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        if ( class_exists( 'Envira_Albums' ) ) {

    	    $this->base = Envira_Albums::get_instance();

        }

        if ( class_exists( 'Envira_Albums_Shortcode' ) ){

			$this->album_shortcode = Envira_Albums_Shortcode::get_instance();

		}

        add_filter( 'envira_albums_output_before_container',    array( $this, 'output_album_breadcrumbs' ), 10, 2 );
        add_filter( 'envira_gallery_output_before_container',   array( $this, 'output_gallery_breadcrumbs' ), 10, 2 );
        add_filter( 'envira_gallery_output',                    array( $this, 'output_gallery_breadcrumbs' ), 10, 2 );
        add_filter( 'wpseo_breadcrumb_links',                   array( &$this, 'change_wpseo_breadcrumb_links' ) );

    }

    /**
     * Outputs Breadcrumb navigation on an Album, if the Album has this functionality enabled
     *
     * @since 1.0.0
     *
     * @param string     $html          Album HTML
     * @param array      $album_data    Album Data
     * @return string                   Album HTML
     */
    public function output_album_breadcrumbs( $html, $album_data ) {


        if ( ! $this->album_shortcode->get_config( 'breadcrumbs_enabled', $album_data ) ) {
            return $html;
        }

        // Check we're on a standalone Album (we can't display breadcrumbs for embedded Albums, as we can never
        // determine the referring Album when clicking a Gallery).
        // Check we're viewing a single gallery
        if ( ! is_singular( 'envira_album' ) ) {
            $html .= $this->breadcrumb_html( $album_data['id'], '', $this->album_shortcode->get_config( 'breadcrumbs_separator', $album_data ) );
            return $html;
        }

        // Prepend breadcrumbs to HTML
        $html .= $this->breadcrumb_html( $album_data['id'], '', $this->album_shortcode->get_config( 'breadcrumbs_separator', $album_data ) );

        // Return
        return $html;

    }

    /**
    * Outputs Breadcrumb navigation on a Gallery, if the user navigated from an Album and that Album
    * has this functionality enabled
    *
    * @since 1.0.0
    *
    * @param string     $html           Gallery HTML
    * @param array      $gallery_data   Gallery Data
    * @return string                    Gallery HTML
    */
    public function output_gallery_breadcrumbs( $html, $gallery_data ) {

        // Check we got to this Gallery from an Album
        if ( ! $this->referred_from_album() ) {
            return $html;
        }

        $album_slug = $this->get_album_slug_from_referrer_url();

        if ( empty( $album_slug ) ) {
            return $html;
        }

        // Get Album
        $album_data = envira_get_album_by_slug( $album_slug );
		
		//Bail if no data
		if ( ! $album_data ){
			return $html;
		}
        
        // Check that Album has Breadcrumb functionality enabled
        if ( ! $this->album_shortcode->get_config( 'breadcrumbs_enabled', $album_data ) ) {
            return $html;
        }

        // Make sure this is unqueued because sometimes it won't be, such as with password protected pages
        wp_enqueue_style( ENVIRA_SLUG . '-style' );

        // Prepend breadcrumbs to HTML
        $html .= $this->breadcrumb_html( $album_data['id'], $gallery_data['id'], $this->album_shortcode->get_config( 'breadcrumbs_separator', $album_data ) );

        // Return
        return $html;

    }

    /**
    * Determines whether we were referred to this gallery from an album
    *
    * @since 1.0
    *
    * @return bool Referred to Gallery from Album
    */
    private function referred_from_album() {

        // Check we're viewing a single gallery
        if ( ! is_singular( 'envira' ) ) {
            return false;
        }

        // Check if the user was referred from an Album
        if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
            return false;
        }

        // If first part of referrer URL matches the Envira Album slug, the visitor clicked on a gallery from an album
        $referer_url = str_replace( get_bloginfo( 'url' ), '', $_SERVER['HTTP_REFERER'] );
        $referer_url_parts = array_values ( array_filter( explode( '/', $referer_url ) ) );
        if ( ! is_array( $referer_url_parts ) || count ( $referer_url_parts ) < 2 ) {
            return false;
        }

        return true;

    }

    /**
    * Returns the Album Slug from the referrer
    *
    * @since 1.0
    *
    * @return string Album Slug
    */
    private function get_album_slug_from_referrer_url() {

        $referer_url = str_replace( get_bloginfo( 'url' ), '', $_SERVER['HTTP_REFERER'] );
        $referer_url_parts = array_values ( array_filter( explode( '/', $referer_url ) ) );
        $album_slug = $referer_url_parts[ count( $referer_url_parts ) - 1 ];

        return $album_slug;

    }

    /**
    * Returns HTML markup for breadcrumb navigation
    *
    * @since 1.0
    *
    * @param array  $album_id       Album ID
    * @param array  $gallery_id     Gallery ID
    * @return string                HTML
    */
    private function breadcrumb_html( $album_id, $gallery_id = '', $separator = '', $album_post_id = false ) {

        global $post;

        // Start HTML
        $html = '<div class="envira-breadcrumbs">
            <span xmlns:v="http://rdf.data-vocabulary.org/#">';

        // Breadcrumbs
        $breadcrumbs = array();

        // Home
        $breadcrumbs[] = array(
            'title' => get_bloginfo( 'name' ),
            'url'   => get_bloginfo( 'url' ),
        );

        // Album
        if ( $album_post_id ) {
            // display the POST the album is in, not the album directly... if there is $album_post
            $breadcrumbs[] = array(
                'title' => get_the_title( $album_post_id ),
                'url'   => get_permalink( $album_post_id ),
                'id'    => $album_post_id
            );
        } else {

            if ( $post && $post->ID != $album_id && $post->ID != $gallery_id ){
                $breadcrumbs[] = array(
                    'title' => get_the_title( $post->ID ),
                    'url'   => get_permalink( $post->ID ),
                    'id'    => $post->ID
                );
            }
            $breadcrumbs[] = array(
                'title' => get_the_title( $album_id ),
                'url'   => get_permalink( $album_id ),
                'id'   => $album_id
            );
        }

        // Gallery
        if ( ! empty( $gallery_id ) ) {
            $breadcrumbs[] = array(
                'title' => get_the_title( $gallery_id ),
                'url'   => get_permalink( $gallery_id ),
            );
        }       

        // Iterate through breadcrumbs
        foreach ( $breadcrumbs as $index => $breadcrumb ) {
            // Create HTML based on whether this is the last breadcrumb or not
            if ( $index == ( count( $breadcrumbs ) - 1 ) ) {
                // Last
                $html .= ' <span class="breadcrumb_last">' . $breadcrumb['title'] . '</span>';
            } else {
                // Any other
                $html .= '<span typeof="v:Breadcrumb">
                    <a href="' . $breadcrumb['url'] . '" rel="v:url" property="v:title">' . $breadcrumb['title'] . '</a>
                </span>' . $separator;
            }
        }

        // End HTML
        $html .= '</span>
        </div>';

        // Return
        return $html;

    }

    /**
    * When Yoast SEO / WordPress SEO Plugin outputs its breadcrumbs, if we
    * are viewing a Gallery embedded with an Album, add the Album to the breadcrumbs
    * - Remove the deepest / child term, so we're just left with the top level taxonomy term
    * - Add the Advice Centre Page as a breadcrumb before the top level taxonomy term
    *
    * This changes Home > Gallery to
    * Home > Album > Gallery
    *
    * @since 1.0
    *
    * @param array $crumbs  Breadcrumbs
    * @return array         Breadcrumbs
    */
    public function change_wpseo_breadcrumb_links( $crumbs ) {

        // Check we got to this Gallery from an Album
        if ( ! $this->referred_from_album() ) {
            return $crumbs;
        }

        // Get Album Slug
        $album_slug = $this->get_album_slug_from_referrer_url();
        if ( empty( $album_slug ) ) {
            return $crumbs;
        }

        // Get Album
        $album_data = $this->base->get_album_by_slug( $album_slug );

        // Check that Album has Breadcrumb functionality enabled
        if ( ! $this->album_shortcode->get_config( 'breadcrumbs_enabled_yoast', $album_data ) ) {
            return $crumbs;
        }

        // Setup container for new breadcrumbs and add the Home Page to it
        $new_crumbs = array( 0 => $crumbs[0] );

        // Move Gallery to last elemend
        $new_crumbs[2] = $crumbs[1];

        // Inject Album to 1st element
        $new_crumbs[1] = array(
            'id' => $album_data['id'],
        );

        // Sort array
        ksort( $new_crumbs );

        // Return
        return $new_crumbs;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Breadcrumbs_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Breadcrumbs_Shortcode ) ) {
            self::$instance = new Envira_Breadcrumbs_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_breadcrumbs_shortcode = Envira_Breadcrumbs_Shortcode::get_instance();

function wpse172275_get_all_attributes( $tag, $text )
{
    preg_match_all( '/' . get_shortcode_regex() . '/s', $text, $matches );
    $out = array();
    if( isset( $matches[2] ) )
    {
        foreach( (array) $matches[2] as $key => $value )
        {
            if( $tag === $value )
                $out[] = shortcode_parse_atts( $matches[3][$key] );
        }
    }
    return $out;
}