<?php
/**
 * WP List Table Admin Class.
 *
 * @since 1.3.0
 *
 * @package Envira_Albums
 * @author  Envira Team
 */
namespace Envira\Albums\Admin;

class Table {

    /**
     * Holds the base class object.
     *
     * @since 1.3.0
     *
     * @var object
     */
    public $base;

    /**
     * Holds the metabox class object.
     *
     * @since 1.3.0
     *
     * @var object
     */
    public $metabox;

    /**
     * Primary class constructor.
     *
     * @since 1.3.0
     */
    public function __construct() {

        // Append data to various admin columns.
        add_filter( 'manage_edit-envira_album_columns', array( $this, 'columns' ) );
        add_action( 'manage_envira_album_posts_custom_column', array( $this, 'custom_columns'), 10, 2 );

        add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

    }
	public function pre_get_posts( $query ){
		
		if ( is_admin() && 'edit.php' === $GLOBALS['pagenow'] 
		
			&& $query->is_main_query() 
			&& $query->get( 'post_type' ) === 'envira_album' ) {
		
            $this->stickies = array();
			$this->stickies[] = get_option( 'envira_default_album' );
			$this->stickies[] = get_option( 'envira_dynamic_album' );

			add_filter( 'post_class', array( $this, 'post_class' ), 10, 3 );    
			add_filter( 'option_sticky_posts', array( $this, 'custom_stickies' ) );
			$query->is_home = 1;
			$query->set( 'ignore_sticky_posts', 0 );
	
		}
	
	}

	public function custom_stickies( $data ){
		
		if( count( $this->stickies ) > 0 ) {

			$data = $this->stickies;

		}

		return $data;
	}

	public function post_class( $classes, $class, $id ){

		if( in_array( $id, $this->stickies, true ) ){

            $classes[] = 'is-admin-sticky';
	
		}
	
		return $classes;
	
    }
    
    /**
     * Customize the post columns for the Envira Album post type.
     *
     * @since 1.3.0
     *
     * @param array $columns  The default columns.
     * @return array $columns Amended columns.
     */
    public function columns( $columns ) {

        // Add additional columns we want to display.
        $envira_columns = array(
            'cb'            => '<input type="checkbox" />',
            'title'         => __( 'Title', 'envira-albums' ),
            'shortcode'     => __( 'Shortcode', 'envira-albums' ),
            'galleries'     => __( 'Number of Galleries', 'envira-albums' ),
            'modified'      => __( 'Last Modified', 'envira-albums' ),
            'date'          => __( 'Date', 'envira-albums' )
        );

        // Allow filtering of columns
        $envira_columns = apply_filters( 'envira_albums_table_columns', $envira_columns, $columns );

        // Return merged column set.  This allows plugins to output their columns (e.g. Yoast SEO),
        // and column management plugins, such as Admin Columns, should play nicely.
        return array_merge( $envira_columns, $columns );

    }

    /**
     * Add data to the custom columns added to the Envira Album post type.
     *
     * @since 1.3.0
     *
     * @global object $post  The current post object
     * @param string $column The name of the custom column
     * @param int $post_id   The current post ID
     */
    public function custom_columns( $column, $post_id ) {

        $post_id = absint( $post_id );

        switch ( $column ) {
            /**
            * Shortcode
            */
            case 'shortcode' :
                echo '
                <div class="envira-code">
                    <code id="envira_shortcode_' . $post_id . '">[envira-album id="' . $post_id . '"]</code>
                    <a href="#" title="' . __( 'Copy Shortcode to Clipboard', 'envira-album' ) . '" data-clipboard-target="#envira_shortcode_' . $post_id . '" class="dashicons dashicons-clipboard envira-clipboard">
                        <span>' . __( 'Copy to Clipboard', 'envira-album' ) . '</span>
                    </a>
                </div>';
                break;

            /**
            * Galleries
            */
            case 'galleries':
                $data = get_post_meta( $post_id, '_eg_album_data', true);
                echo ( isset( $data['galleryIDs'] ) ? count( $data['galleryIDs'] ) : 0 );
                break;

            /**
            * Last Modified
            */
            case 'modified' :
                the_modified_date();
                break;
        }

    }

}
