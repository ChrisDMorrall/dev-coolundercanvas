<?php
/**
 * Plugin Name: Envira Gallery - Gallery Themes Addon
 * Plugin URI:  http://enviragallery.com
 * Description: Enables custom themes for the grid display of Envira galleries.
 * Author:      Envira Gallery Team
 * Author URI:  http://enviragallery.com
 * Version:     1.4.1
 * Text Domain: envira-gallery-themes
 * Domain Path: languages
 *
 * Envira Gallery is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Envira Gallery is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Envira Gallery. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Envira\Utils\Updater;

if ( ! class_exists( 'Envira_Gallery_Themes' ) ) :


	class Envira_Gallery_Themes{

		public static $_instance = null;

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct(){

			add_action( 'envira_gallery_updater'                  , array( $this, 'updater' ) );
			add_filter( 'envira_gallery_gallery_themes'           , array( $this, 'register_gallery_themes' ) );
			add_filter( 'envira_gallery_lightbox_themes'          , array( $this, 'register_envirabox_themes' ) );

			add_action( 'envira_gallery_metabox_scripts'          , array( $this, 'themes_metabox_scripts' ) );

			/* Albums */
			load_plugin_textdomain( 'envira-gallery-themes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		}

		/**
		 * setup_constants function.
		 *
		 * @access public
		 * @return void
		 */
		public function setup_constants(){

			// Define necessary addon constants.
			define( 'ENVIRA_GALLERY_THEMES_PLUGIN_NAME', 'Envira Gallery - Gallery Themes Addon' );
			define( 'ENVIRA_GALLERY_THEMES_PLUGIN_VERSION', '1.4.1' );

			define( 'ENVIRA_GALLERY_THEMES_PLUGIN_SLUG', 'envira-gallery-themes' );

		}

		/**
		 * updater function.
		 *
		 * @access public
		 * @param mixed $key
		 * @return void
		 */
		public function updater( $key ){

			$args = array(
				'plugin_name' => ENVIRA_GALLERY_THEMES_PLUGIN_NAME,
				'plugin_slug' => ENVIRA_GALLERY_THEMES_PLUGIN_SLUG,
				'plugin_path' => plugin_basename( __FILE__ ),
				'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . ENVIRA_GALLERY_THEMES_PLUGIN_SLUG,
				'remote_url'  => 'https://enviragallery.com/',
				'version'     => ENVIRA_GALLERY_THEMES_PLUGIN_VERSION,
				'key'         => $key
			);

			$updater = new \Envira\Utils\Updater( $args );

		}
		public function themes_metabox_scripts() {
		    // Conditional Fields
		    wp_register_script( ENVIRA_GALLERY_THEMES_PLUGIN_SLUG . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', __FILE__ ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), ENVIRA_GALLERY_THEMES_PLUGIN_VERSION, true );
		    wp_enqueue_script( ENVIRA_GALLERY_THEMES_PLUGIN_SLUG . '-conditional-fields-script' );
		}
		/**
		 * register_themes function.
		 *
		 * @access public
		 * @return void
		 */
		public function register_envirabox_themes( $themes ){

		    // Add custom themes here.
		    $themes[] = array(
		        'value' => 'base_light',
		        'name'  => __( 'Base (Light)', 'envira-gallery-themes' ),
		        'file'  => __FILE__,
				'config' => array(
					'arrows'          => 'true',
					'margins'         => array( 220, 0 ), //top/bottom, left/right
					'gutter'          => '100',
					'thumbs_position' => 'bottom',
					'base_template'   => 'envirabox_default_template',
				),
		    );

		    $themes[] = array(
		        'value' => 'captioned',
		        'name'  => __( 'Captioned', 'envira-gallery-themes' ),
		        'file'  => __FILE__,
				'config' => array(
					'arrows'          => 'true',
					'margins'         => array( 220, 0 ),  //top/bottom, left/right
					'gutter'          => '50',
					'base_template'   => 'envirabox_legecy_template',
				),
		    );

		    $themes[] = array(
		        'value' => 'polaroid',
		        'name'  => __( 'Polaroid', 'envira-gallery-themes' ),
		        'file'  => __FILE__,
				'config' => array(
					'arrows'          => 'true',
					'margins'         => array( 220, 0 ),  //top/bottom, left/right
					'gutter'          => '50',
					'base_template'   => 'envirabox_legecy_template',
				),
		    );

		    $themes[] = array(
		        'value' => 'showcase',
		        'name'  => __( 'Showcase', 'envira-gallery-themes' ),
		        'file'  => __FILE__,
				'config' => array(
					'arrows'          => 'true',
					'margins'         => array( 220, 0 ),  //top/bottom, left/right
					'gutter'          => '50',
					'base_template'   => 'envirabox_legecy_template',
				),
		    );

		    $themes[] = array(
		        'value' => 'sleek',
		        'name'  => __( 'Sleek', 'envira-gallery-themes' ),
		        'file'  => __FILE__,
				'config' => array(
					'arrows'          => 'true',
					'margins'         => array( 220, 0 ),  //top/bottom, left/right
					'gutter'          => '50',
					'base_template'   => 'envirabox_legecy_template',
				),
		    );

		    $themes[] = array(
		        'value' => 'subtle',
		        'name'  => __( 'Subtle', 'envira-gallery-themes' ),
		        'file'  => __FILE__,
				'config' => array(
					'arrows'          => 'true',
					'margins'         => array( 220, 0 ),  //top/bottom, left/right
					'gutter'          => '50',
					'base_template'   => 'envirabox_legecy_template',
				),
		    );

		    return $themes;
		}

		/**
		 * register_gallery_themes function.
		 *
		 * @access public
		 * @param mixed $themes
		 * @return void
		 */
		public function register_gallery_themes( $themes ){
		    // Add custom themes here.
		    $themes[] = array(
		        'value' => 'captioned',
		        'name'  => __( 'Captioned', 'envira-gallery-themes' ),
		        'file'  => __FILE__
		    );

		    $themes[] = array(
		        'value' => 'polaroid',
		        'name'  => __( 'Polaroid', 'envira-gallery-themes' ),
		        'file'  => __FILE__
		    );

		    $themes[] = array(
		        'value' => 'showcase',
		        'name'  => __( 'Showcase', 'envira-gallery-themes' ),
		        'file'  => __FILE__
		    );

		    $themes[] = array(
		        'value' => 'sleek',
		        'name'  => __( 'Sleek', 'envira-gallery-themes' ),
		        'file'  => __FILE__
		    );

		    $themes[] = array(
		        'value' => 'subtle',
		        'name'  => __( 'Subtle', 'envira-gallery-themes' ),
		        'file'  => __FILE__
		    );

		    return $themes;

		}

		/**
		 * load_global function.
		 *
		 * @access public
		 * @return void
		 */
		public function require_global(){

		}

		/**
		 * autoload function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public static function autoload(){

		}

        /**
         * Returns the singleton instance of the class.
         *
         * @since 1.6.0
         *
         * @return object The Envira_Albums object.
         */
        public static function get_instance() {

            if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof Envira_Gallery_Themes ) ) {
                self::$_instance = new self();
                self::$_instance->setup_constants();
                self::$_instance->require_global();
            }

            return self::$_instance;

        }



	}

	add_action( 'envira_gallery_init', 'envira_gallery_themes_plugins_loaded' );

	/**
	 * Ensures the full Envira Gallery plugin is active before proceeding.
	 *
	 * @since 1.0.0
	 *
	 * @return null Return early if Envira Gallery is not active.
	 */
	function envira_gallery_themes_plugins_loaded() {

		return Envira_Gallery_Themes::get_instance();

	}

endif;