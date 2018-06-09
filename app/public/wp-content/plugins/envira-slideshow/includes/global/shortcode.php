<?php
/**
 * Shortcode class.
 *
 * @since 1.0.8
 *
 * @package Envira_Slideshow
 * @author  Envira Team
 */
class Envira_Slideshow_Shortcode {

    /**
     * Holds the class object.
     *
     * @since 1.0.8
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.8
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

	public $gallery_shortcode;
    /**
     * Primary class constructor.
     *
     * @since 1.0.8
     */
    public function __construct() {
		if ( !class_exists( 'Envira_Gallery_Shortcode' ) ){
			return;
		}

        $this->base = Envira_Slideshow::get_instance();
        $this->gallery_shortcode = Envira_Gallery_Shortcode::get_instance();

        // Register JS
        wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-slideshow-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );

        // Gallery
        add_action( 'envira_gallery_before_output', array( $this, 'gallery_output_css_js' ) );
        add_action( 'envira_link_before_output', array( $this, 'gallery_output_css_js' ) );

       // add_action( 'envira_gallery_api_envirabox_config', array( $this, 'gallery_output' ) );
        add_filter( 'envira_gallery_toolbar_after_prev', array( $this, 'gallery_toolbar_button' ), 10, 2 );
		// add_action( 'envira_gallery_api_after_show', array( $this, 'after_show' ) );

        // Album
        add_action( 'envira_albums_before_output', array( $this, 'albums_output_css_js' ) );

        add_action( 'envira_albums_api_config', array( $this, 'album_output' ) );
        add_filter( 'envira_albums_toolbar_after_prev', array( $this, 'album_toolbar_button' ), 10, 2 );

        add_filter( 'envirabox_actions', array( $this, 'envirabox_actions' ), 90, 2 );
        add_filter( 'envira_always_show_title', array( $this, 'envira_always_show_title' ), 10, 2 );

    }


    /**
     * Enqueue CSS and JS if the Download Button is enabled
     *
     * @since 1.0.0
     *
     * @param array $data Gallery Data
     */
    public function gallery_output_css_js( $data ) {

        if ( ! $this->gallery_shortcode->get_config( 'slideshow', $data ) ) {
            return;
        }

        // Enqueue CSS + JS
        wp_enqueue_script( $this->base->plugin_slug . '-script' );

    }

    /**
    * Enqueue CSS and JS for Albums if Download Button is enabled
    *
    * @since 1.0.0
    *
    * @param array $data Album Data
    */
    public function albums_output_css_js( $data ) {

        if ( ! $this->gallery_shortcode->get_config( 'slideshow', $data ) ) {
            return;
        }

        // Enqueue CSS + JS
        wp_enqueue_script( $this->base->plugin_slug . '-script' );

    }


    public function on_play_start( $data ) {

        $playing_value = 'false';

        if ( $this->gallery_shortcode->get_config( 'slideshow', $data ) && $this->gallery_shortcode->get_config( 'autoplay', $data ) ) { $playing_value = 'true'; } ?>

        var envira_playing = '<?php echo $playing_value; ?>';

    <?php }

    /**
     * Outputs the slideshow settings for a gallery.
     *
     * @since 1.0.8
     *
     * @param array $data Data for the Envira gallery.
     * @return null       Return early if the slideshow is not enabled.
     */
    public function gallery_output( $data ) {

        if ( ! $this->gallery_shortcode->get_config( 'slideshow', $data ) ) {
            return;
        }

        // Output the slideshow init code.
        echo 'autoPlay:' . $this->gallery_shortcode->get_config( 'autoplay', $data ) . ',';
        // Note Filter
        echo 'playSpeed:' . apply_filters( 'envira_slideshow_gallery_speed', $this->gallery_shortcode->get_config( 'ss_speed', $data ), $data ) . ',';

    }

    /**
     * Outputs the slideshow settings for an album.
     *
     * @since 1.0.8
     *
     * @param array $data Data for the Envira Album.
     * @return null       Return early if the slideshow is not enabled.
     */
    public function album_output( $data ) {

        $instance = Envira_Albums_Shortcode::get_instance();

        if ( ! $instance->get_config( 'slideshow', $data ) ) {
            return;
        }

        // Output the slideshow init code.
        echo 'autoPlay:' . $instance->get_config( 'autoplay', $data ) . ',';
        // Note Filter
        echo 'playSpeed:' . apply_filters( 'envira_slideshow_album_speed', $instance->get_config( 'ss_speed', $data ), $data ) . ',';

    }

    /**
     * Outputs the slideshow button in the gallery toolbar.
     *
     * @since 1.0.8
     *
     * @param string $template  The template HTML for the gallery toolbar.
     * @param array $data       Data for the Envira gallery.
     * @return string $template Amended template HTML for the gallery toolbar.
     */
    public function gallery_toolbar_button( $template, $data ) {

        if ( ! $this->gallery_shortcode->get_config( 'slideshow', $data ) ) {
            return $template;
        }

        // Create the slideshow button.
        $button = '<li><div class="envirabox-slideshow-button"><a data-envirabox-play href="#" class="envirabox-button--play" title="' . __( 'Start Slideshow', 'envira-slideshow' ) . '"></a></div></li>';

        // Return with the button appended to the template.
        return $template . $button;

    }

    public function envirabox_actions( $template, $data ) {

        // Check if Download Button output is enabled
        if ( ! envira_get_config( 'slideshow', $data ) ) {
            return $template;
        }

        // Build Button
        $button = '<div class="envirabox-slideshow-button"><a data-envirabox-play href="#" class="envirabox-button--play" title="' . __( 'Start Slideshow', 'envira-slideshow' ) . '"></a></div>';

        return $template . $button;
    }

    public function envira_always_show_title( $show, $data ) {

        if ( ! $this->gallery_shortcode->get_config( 'slideshow', $data ) || ( ! in_array( $this->gallery_shortcode->get_config( 'lightbox_theme', $data ), array( 'base_dark', 'base_light' ) ) ) ) {
            return $show;
        }

        return true;
    }

	public function after_show( $data ){
		if( ! $this->gallery_shortcode->get_config( 'slideshow_hover', $data ) ){
			return;
		} ?>

		$('.envirabox-inner img.envirabox-image, .envirabox-inner iframe').on( {
			mouseenter: function() {
                if ( $('.btnPlay').hasClass('btnPlayOn') ) {
                    /* it was on, so remember that */
                    envira_playing = true;
                } else {
                    envira_playing = false;
                }
				$.envirabox.play( false );
			},
			mouseleave: function() {
                /* was envira playing when you entered the area? if so, restore */
                if ( envira_playing == true ) {
                    $.envirabox.play( true );
                }

			}
		});


		<?php
	}
    /**
     * Outputs the slideshow button in the album toolbar.
     *
     * @since 1.0.4
     *
     * @param string $template  The template HTML for the album toolbar.
     * @param array $data       Data for the Envira album.
     * @return string $template Amended template HTML for the album toolbar.
     */
    public function album_toolbar_button( $template, $data ) {

        if ( ! Envira_Albums_Shortcode::get_instance()->get_config( 'slideshow', $data ) ) {
            return $template;
        }

        // Create the slideshow button.
        $button = '<li><a data-envirabox-play class="btnPlay" title="' . __( 'Start Slideshow', 'envira-slideshow' ) . '" href="javascript:;"></a></li>';

        // Return with the button appended to the template.
        return $template . $button;

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.8
     *
     * @return object The Envira_Slideshow_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Slideshow_Shortcode ) ) {
            self::$instance = new Envira_Slideshow_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_slideshow_shortcode = Envira_Slideshow_Shortcode::get_instance();