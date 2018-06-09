<?php
/**
 * Taxonomy class.
 *
 * @since 1.0.5
 *
 * @package Envira_Tags
 * @author  Envira Team
 */
class Envira_Tags_Taxonomy {

    /**
     * Holds the class object.
     *
     * @since 1.0.5
     *
     * @var object
     */
    public static $instance;

    /**
     * Path to the file.
     *
     * @since 1.0.5
     *
     * @var string
     */
    public $file = __FILE__;

    /**
     * Holds the base class object.
     *
     * @since 1.0.5
     *
     * @var object
     */
    public $base;

    /**
     * Primary class constructor.
     *
     * @since 1.0.5
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Gallery::get_instance();
        
        // Build the labels for the taxonomy
        $labels = array(
            'name'                       => apply_filters('envira_whitelabel', false ) ? __( apply_filters('envira_whitelabel_name_singular', false ) . ' Tags' ) : __( 'Envira Tags', 'envira-tags' ),
            'singular_name'              => apply_filters('envira_whitelabel', false ) ? __( apply_filters('envira_whitelabel_name_singular', false ) . ' Tags' ) : __( 'Envira Tag', 'envira-tags' ),
            'search_items'               => __( 'Search Tags', 'envira-tags' ),
            'popular_items'              => __( 'Popular Tags', 'envira-tags' ),
            'all_items'                  => __( 'All Tags', 'envira-tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Tag', 'envira-tags' ),
            'update_item'                => __( 'Update Tag', 'envira-tags' ),
            'add_new_item'               => __( 'Add New Tag', 'envira-tags' ),
            'new_item_name'              => __( 'New Tag Name', 'envira-tags' ),
            'separate_items_with_commas' => __( 'Separate tags with commas', 'envira-tags' ),
            'add_or_remove_items'        => __( 'Add or remove tags', 'envira-tags' ),
            'choose_from_most_used'      => __( 'Choose from the most used tags', 'envira-tags' ),
            'not_found'                  => __( 'No tags found.', 'envira-tags' ),
            'menu_name'                  => apply_filters('envira_whitelabel', false ) ? __( apply_filters('envira_whitelabel_name_singular', false ) . ' Tags' ) : __( 'Envira Tags', 'envira-tags' ),
        );
		$labels = apply_filters( 'envira_tags_taxonomy_labels', $labels );
	
		// Build the taxonomy arguments
        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'query_var'             => true,
            'show_in_nav_menus'     => false,
            'show_tagcloud'         => false,
            'rewrite'               => array( 'slug' => 'envira-tag' ),
        );
		$args = apply_filters( 'envira_tags_taxonomy_args', $args );
	
		// Register the taxonomy with WordPress.
		register_taxonomy( 'envira-tag', 'attachment', $args );

        // Build the labels for the taxonomy
        $labels = array(
            'name'                       => apply_filters('envira_whitelabel', false ) ? __( apply_filters('envira_whitelabel_name_singular', false ) .  ' Categories' ) : __( 'Envira Categories', 'envira-tags' ),
            'singular_name'              => apply_filters('envira_whitelabel', false ) ? __( apply_filters('envira_whitelabel_name_singular', false ) . ' Category' ) : __( 'Envira Category', 'envira-tags' ),
            'search_items'               => __( 'Search Categories', 'envira-tags' ),
            'popular_items'              => __( 'Popular Categories', 'envira-tags' ),
            'all_items'                  => __( 'All Categories', 'envira-tags' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Category', 'envira-tags' ),
            'update_item'                => __( 'Update Category', 'envira-tags' ),
            'add_new_item'               => __( 'Add New Category', 'envira-tags' ),
            'new_item_name'              => __( 'New Category Name', 'envira-tags' ),
            'separate_items_with_commas' => __( 'Separate categories with commas', 'envira-tags' ),
            'add_or_remove_items'        => __( 'Add or remove categories', 'envira-tags' ),
            'choose_from_most_used'      => __( 'Choose from the most used categories', 'envira-tags' ),
            'not_found'                  => __( 'No categories found.', 'envira-tags' ),
            'menu_name'                  => __( 'Categories', 'envira-tags' ),
        );
        $labels = apply_filters( 'envira_tags_category_taxonomy_labels', $labels );
    
        // Build the taxonomy arguments
        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'query_var'             => true,
            'show_in_nav_menus'     => false,
            'show_tagcloud'         => false,
            'rewrite'               => array( 'slug' => 'envira-category' ),
        );
        $args = apply_filters( 'envira_tags_category_taxonomy_args', $args );
    
        // Register the category taxonomy with WordPress.
        register_taxonomy( 'envira-category', array( 'envira', 'envira_album' ), $args );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.5
     *
     * @return object The Envira_Tags_Taxonomy object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Tags_Taxonomy ) ) {
            self::$instance = new Envira_Tags_Taxonomy();
        }

        return self::$instance;

    }

}

// Load the taxonomy class.
$envira_tags_taxonomy = Envira_Tags_Taxonomy::get_instance();