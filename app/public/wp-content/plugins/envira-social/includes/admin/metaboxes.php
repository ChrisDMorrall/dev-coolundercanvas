<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Social
 * @author  Envira Team
 */

use Envira\Admin\Notices;

class Envira_Social_Metaboxes {

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

        $this->base = Envira_Social::get_instance();

        add_action( 'envira_gallery_metabox_scripts', array( $this, 'metabox_scripts' ) );
        add_action( 'envira_albums_metabox_scripts', array( $this, 'metabox_scripts' ) );


        // Notices
        add_action( 'admin_notices', array( $this, 'notice' ) );

		// Envira Gallery
        add_filter( 'envira_gallery_tab_nav', array( $this, 'register_tabs' ) );
        add_action( 'envira_gallery_tab_social', array( $this, 'social_tab' ) );
        add_action( 'envira_gallery_mobile_box', array( $this, 'mobile_screen' ) );
        add_action( 'envira_gallery_mobile_lightbox_box', array( $this, 'mobile_lightbox_screen' ) );
		add_filter( 'envira_gallery_save_settings', array( $this, 'gallery_settings_save' ), 10, 2 );

		// Envira Album
        add_filter( 'envira_albums_tab_nav', array( $this, 'register_tabs' ) );
        add_action( 'envira_albums_tab_social', array( $this, 'social_tab' ) );
        add_action( 'envira_albums_mobile_box', array( $this, 'mobile_screen' ) );
        add_action( 'envira_albums_mobile_lightbox_box', array( $this, 'mobile_lightbox_screen' ) );
		add_filter( 'envira_albums_save_settings', array( $this, 'album_settings_save' ), 10, 2 );
    }

    /**
     * Initializes scripts for the metabox admin.
     *
     * @since 1.0.0
     *
     * @param string $key The user license key.
     */
    public function metabox_scripts() {
        // Conditional Fields
        // wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
        wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/conditional-fields.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );

        wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );
    
        wp_register_style( $this->base->plugin_slug . '-social-admin', plugins_url( 'assets/css/envira-social-admin.css', $this->base->file ) );
        wp_enqueue_style ( $this->base->plugin_slug . '-social-admin' );
    }

    /**
     * Show a notice if the plugin settings haven't been configured
     *
     * These are required to ensure that Facebook and Twitter sharing doesn't throw errors
     *
     * @since 1.0.4
     */
    function notice() {

        // Check if we have required config options
        $common = Envira_Social_Common::get_instance();
        $facebook_app_id = $common->get_setting( 'facebook_app_id' );
        $twitter_username = $common->get_setting( 'twitter_username' );

        if ( empty( $facebook_app_id ) || empty( $twitter_username ) ) {

            $notices = new Notices;

            $message = sprintf( __( '<strong>Envira Gallery:</strong> The Social Addon requires configuration with Facebook and Twitter. Please visit the <a href="%s" title="Settings" target="_blank">Settings</a> screen to complete setup.', 'envira-social' ), 'edit.php?post_type=envira&page=envira-gallery-settings' );

            $notices->display_inline_notice( 'warning-social-fb-tw', false, $message, 'error', $button_text = '', $button_url = '', true, DAY_IN_SECONDS );

        }

    }

    /**
     * Registers tab(s) for this Addon in the Settings screen
     *
     * @since 1.0.0
     *
     * @param   array   $tabs   Tabs
     * @return  array           Tabs
     */
    function register_tabs( $tabs ) {

        $tabs['social'] = __( 'Social', 'envira-social' );
        return $tabs;

    }
    
    /**
     * Adds addon settings UI to the Social tab
     *
     * @since 1.0.0
     *
     * @param object $post The current post object.
     */
    function social_tab( $post ) {
        
        // Get post type so we load the correct metabox instance and define the input field names
        // Input field names vary depending on whether we are editing a Gallery or Album
        $post_type = get_post_type( $post );
        switch ( $post_type ) {
            /**
            * Gallery
            */
            case 'envira':
                $instance = Envira_Gallery_Metaboxes::get_instance();
                $key = '_envira_gallery';
                break;
            
            /**
            * Album
            */
            case 'envira_album':
                $instance = Envira_Albums_Metaboxes::get_instance();
                $key = '_eg_album_data[config]';
                break;
        }

        // Gallery options only apply to Galleries, not Albums
        // EDIT: NO longer, keeping comment here as such for next few releases
        // if ( 'envira' == $post_type ) {
            ?>
            <p class="envira-intro">
                <?php _e( 'Social Gallery Settings', 'envira-social' ); ?>

                    <small>

                        <?php _e( 'The settings below adjust the Social Sharing options for the Gallery output.', 'envira-social' ); ?>

                        <?php if ( apply_filters('envira_whitelabel', false )  ): ?>
                            
                            <?php do_action('envira_social_whitelabel_tab_helptext'); ?>

                        <?php else: ?>

                    
                                    <br />
                <?php _e( 'Need some help?', 'envira-social' ); ?>
                <a href="http://enviragallery.com/docs/social-addon/" class="envira-doc" target="_blank">
                    <?php _e( 'Read the Documentation', 'envira-social' ); ?>
                </a>
                or
                <a href="https://www.youtube.com/embed/FQhR0PA9skQ/?rel=0" class="envira-video" target="_blank">
                    <?php _e( 'Watch a Video', 'envira-social' ); ?>
                </a> 
                        <?php endif; ?>
                    </small>

            </p>
            <table class="form-table">
                <tbody>
                    <tr id="envira-config-social-box">
                        <th scope="row">
                            <label for="envira-config-social"><?php _e( 'Display Social Sharing Buttons?', 'envira-social' ); ?></label>
                        </th>
                        <td>
                            <input id="envira-config-social" type="checkbox" name="<?php echo $key; ?>[social]" value="1" <?php checked( $instance->get_config( 'social', $instance->get_config_default( 'social' ) ), 1 ); ?> />
                            <span class="description"><?php _e( 'Enables or disables displaying social sharing buttons on each image in the gallery view.', 'envira-social' ); ?></span>
                        </td>
                    </tr>

            		<tr id="envira-config-social-networks-box">
                    	<th scope="row">
            		    	<label><?php _e( 'Social Buttons', 'envira-social' ); ?></label>
            		    </th>
            		    <td>
            		        <?php
            		        foreach ( $this->get_networks() as $network => $name ) {
            		        	?>
            		        	<label for="envira-config-social-<?php echo $network; ?>" class="label-for-checkbox">
            		        		<input id="envira-config-social-<?php echo $network; ?>" type="checkbox" name="<?php echo $key; ?>[social_<?php echo $network; ?>]" value="1" <?php checked( $instance->get_config( 'social_' . $network, $instance->get_config_default( 'social_' . $network ) ), 1 ); ?> />
            			        	<?php echo $name; ?>
            			        </label>
            			        <?php	
            		        }
            		        ?>
            	        </td>
                    </tr>
                    <tr id="envira-config-social-position-box">
                        <th scope="row">
                            <label for="envira-config-social-position"><?php _e( 'Social Buttons Position', 'envira-social' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-social-position" name="<?php echo $key; ?>[social_position]">
                                <?php foreach ( (array) $this->get_positions() as $value => $name ) : ?>
                                    <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_position', $instance->get_config_default( 'social_position' ) ) ); ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Where to display the social sharing buttons over the image.', 'envira-social' ); ?></p>
                        </td>
                    </tr>

                    <tr id="envira-config-social-orientation-box">
                        <th scope="row">
                            <label for="envira-config-social-orientation"><?php _e( 'Social Buttons Orientation', 'envira-social' ); ?></label>
                        </th>
                        <td>
                            <select id="envira-config-social-orientation" name="<?php echo $key; ?>[social_orientation]">
                                <?php foreach ( (array) $this->get_orientations() as $value => $name ) : ?>
                                    <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_orientation', $instance->get_config_default( 'social_orientation' ) ) ); ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e( 'Displays the social sharing buttons horizontally or vertically.', 'envira-social' ); ?></p>
                        </td>
                    </tr>



                </tbody>
            </table>
            <?php
        // }

        // Lightbox Options
        ?>
        <p class="envira-intro">
            <?php _e( 'Social Lightbox Settings', 'envira-social' ); ?>

                    <small>

                        <?php _e( 'The settings below adjust the Social Sharing options for the Lightbox output.', 'envira-social' ); ?>

                        <?php if ( apply_filters('envira_whitelabel', false )  ): ?>
                            
                            <?php do_action('envira_social_lightbox_whitelabel_tab_helptext'); ?>

                        <?php else: ?>

                              
                        <?php endif; ?>
                    </small>


        </p>
        <table class="form-table">
            <tbody>
                <tr id="envira-config-social-lightbox-box">
                    <th scope="row">
                        <label for="envira-config-social-lightbox"><?php _e( 'Display Social Sharing Buttons?', 'envira-social' ); ?></label>
                    </th>
                    <td>
                        <input id="envira-config-social-lightbox" type="checkbox" name="<?php echo $key; ?>[social_lightbox]" value="1" <?php checked( $instance->get_config( 'social_lightbox', $instance->get_config_default( 'social_lightbox' ) ), 1 ); ?> />
                        <span class="description"><?php _e( 'Enables or disables displaying social sharing buttons on each image in the Lightbox view.', 'envira-social' ); ?></span>
                    </td>
                </tr>
                <tr id="envira-config-social-lightbox-networks-box">
                    <th scope="row">
                        <label><?php _e( 'Social Networks', 'envira-social' ); ?></label>
                    </th>
                    <td>
                        <?php
                        foreach ( $this->get_networks() as $network => $name ) {
                            ?>
                            <label for="envira-config-social-lightbox-<?php echo $network; ?>" class="label-for-checkbox">
                                <input id="envira-config-social-lightbox-<?php echo $network; ?>" type="checkbox" name="<?php echo $key; ?>[social_lightbox_<?php echo $network; ?>]" value="1" <?php checked( $instance->get_config( 'social_lightbox_' . $network, $instance->get_config_default( 'social_lightbox_' . $network ) ), 1 ); ?> />
                                <?php echo $name; ?>
                            </label>
                            <?php   
                        }
                        ?>
                    </td>
                </tr>

                 
                <tr id="envira-config-social-lightbox-position-box">
                    <th scope="row">
                        <label for="envira-config-social-lightbox-position"><?php _e( 'Social Buttons Position', 'envira-social' ); ?></label>
                    </th>
                    <td>
                        <select id="envira-config-social-lightbox-position" name="<?php echo $key; ?>[social_lightbox_position]">
                            <?php foreach ( (array) $this->get_positions() as $value => $name ) : ?>
                                <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_lightbox_position', $instance->get_config_default( 'social_lightbox_position' ) ) ); ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e( 'Where to display the social sharing buttons over the image.', 'envira-social' ); ?></p>
                    </td>
                </tr>



                <tr id="envira-config-social-lightbox-orientation-box">
                    <th scope="row">
                        <label for="envira-config-social-lightbox-orientation"><?php _e( 'Social Buttons Orientation', 'envira-social' ); ?></label>
                    </th>
                    <td>
                        <select id="envira-config-social-lightbox-orientation" name="<?php echo $key; ?>[social_lightbox_orientation]">
                            <?php foreach ( (array) $this->get_orientations() as $value => $name ) : ?>
                                <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_lightbox_orientation', $instance->get_config_default( 'social_lightbox_orientation' ) ) ); ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e( 'Displays the social sharing buttons horizontally or vertically.', 'envira-social' ); ?></p>
                    </td>
                </tr>

                <tr id="envira-config-social-lightbox-outside-box">
                    <th scope="row">
                        <label for="envira-config-social-outside"><?php _e( 'Display Social Buttons Outside of Image?', 'envira-social' ); ?></label>
                    </th>
                    <td>
                        <input id="envira-config-social-lightbox-outside" type="checkbox" name="<?php echo $key; ?>[social_lightbox_outside]" value="1" <?php checked( $instance->get_config( 'social_lightbox_outside', $instance->get_config_default( 'social_lightbox_outside' ) ), 1 ); ?> />
                        <span class="description"><?php _e( 'If enabled, displays the social sharing buttons outside of the lightbox/image frame.', 'envira-social' ); ?></span>
                    </td>
                </tr>

            </tbody>
        </table>

        <div id="envira-social-advanced-settings">

            <p class="envira-intro">
                <?php _e( 'Advanced Settings', 'envira-social' ); ?>
                <small>
                    <?php _e( 'The settings below apply to social sharing in both lighboxes and galleries.', 'envira-social' ); ?>
                    <br />
                    <?php _e( 'Need some help?', 'envira-social' ); ?>
                    <a href="http://enviragallery.com/docs/social-addon/" class="envira-doc" target="_blank">
                        <?php _e( 'Read the Documentation', 'envira-social' ); ?>
                    </a><?php /*
                    or
                    <a href="https://www.youtube.com/embed/CYpIZgBv-yw/?rel=0" class="envira-video" target="_blank">
                        <?php _e( 'Watch a Video', 'envira-social' ); ?>
                    </a> */ ?>
                </small>
            </p>

            <?php $key = "_general"; ?>

                <table class="form-table facebook-settings">
                    <thead>
                        <td colspan="2" scope="row" style="padding:0;">
                            <h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff;  width: 100%; line-height: 50px;">
                                <img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo $this->base->path; ?>assets/images/admin_facebook.svg" alt="Facebook" />
                                <span style="display:inline-block; height: 100%; vertical-align: middle; color: #000;"><?php _e( 'Facebook Options', 'envira-social' ); ?></span>
                            </h3>
                        </th>
                    </thead>
                    <tbody>
                        <tr id="envira-config-social-networks-facebook-what-to-show">
                            <th scope="row">
                                <label><?php _e( 'What To Share', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <?php
                                foreach ( $this->get_facebook_show_options() as $option_value => $option_name ) {
                                    ?>
                                    <label for="envira-config-social-<?php echo $option_value; ?>" class="label-for-checkbox">
                                        <input id="envira-config-social-<?php echo $option_value; ?>" type="checkbox" name="<?php echo $key; ?>[social_facebook_show_option_<?php echo $option_value; ?>]" value="1" <?php checked( $instance->get_config( 'social_facebook_show_option_' . $option_value, $instance->get_config_default( 'social_facebook_show_option_' . $option_value ) ), 1 ); ?> />
                                        <?php echo $option_name; ?>
                                    </label>
                                    <?php   
                                }
                                ?>
                                <p class="description">
                                    <?php _e( 'Select the information that should be shared with each image.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr id="envira-config-social-networks-facebook-box">
                            <th scope="row">
                                <label for="envira-config-social-networks-facebook"><?php _e( 'Facebook Optional Text', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-social-networks-facebook" type="text" name="<?php echo $key; ?>[social_facebook_text]" value="<?php echo $instance->get_config( 'social_facebook_text', $instance->get_config_default( 'social_facebook_text' ) ); ?>" />
                                <p class="description">
                                    <?php _e( 'Enter an optional message to append to Facebook shares. The image, image URL, title and caption are automatically shared.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-facebook-tags-options">
                            <th scope="row">
                                <label><?php _e( 'Facebook Tag Options', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-config-social-position" name="<?php echo $key; ?>[social_facebook_tag_options]">
                                    <?php foreach ( (array) $this->get_facebook_show_tag_options() as $value => $name ) : ?>
                                        <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_facebook_tag_options', $instance->get_config_default( 'social_facebook_tag_options' ) ) ); ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php _e( 'You can manually set one tag for all gallery images or automatically use tags assigned with the <a href="http://enviragallery.com/addons/tags-addon/" target="_blank">Tags Addon</a>. Note that you are allowed only one Facebook tag when using the Manual option.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-facebook-tags-options-manual">
                            <th scope="row">
                                <label for="envira-config-social-networks-facebook-tags-options-manual"><?php _e( 'Facebook Hashtag', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-social-networks-facebook-tags-options-manual" name="<?php echo $key; ?>[social_facebook_tags_manual]" value="<?php echo $instance->get_config( 'social_facebook_tags_manual', $instance->get_config_default( 'social_facebook_tags_manual' ) ); ?>" />
                                <p class="description">
                                    <?php _e( 'Add one tag, starting with the "#" symbol. <strong>Example:</strong> #Envira.' , 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-facebook-quote">
                            <th scope="row">
                                <label for="envira-config-social-networks-facebook-quote"><?php _e( 'Facebook Quote', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <textarea id="envira-config-social-networks-facebook-quote" name="<?php echo $key; ?>[social_facebook_quote]"><?php echo $instance->get_config( 'social_facebook_quote', $instance->get_config_default( 'social_facebook_quote' ) ); ?></textarea>
                                <p class="description">
                                    <?php _e( 'Add a short text statement to be included with each image shared from this gallery. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information. Field accepts text only, no HTML.' , 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="form-table">
                    <thead>
                        <td colspan="2" scope="row" style="padding:0;">
                            <h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; width: 100%; line-height: 50px;">
                                <img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo $this->base->path; ?>assets/images/admin_pinterest.svg" alt="Pinterest" />
                                <span style="display:inline-block; height: 100%; vertical-align: middle; color: #000"><?php _e( 'Pinterest Options', 'envira-social' ); ?></span>
                            </h3>
                        </th>
                    </thead>
                    <tbody>
                        <tr id="envira-config-social-networks-pinterest-type">
                            <th scope="row">
                                <label for="envira-config-social-networks-pinterest-type"><?php _e( 'Pinterest Sharing Type', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-config-social-networks-pinterest-type" name="<?php echo $key; ?>[social_pinterest_type]">
                                    <?php foreach ( (array) $this->get_pinterest_share_options() as $value => $name ) : ?>
                                        <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_pinterest_type', $instance->get_config_default( 'social_pinterest_type' ) ) ); ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php _e( 'Pin One includes the image and caption of the image being shared. Pin All displays all available images and allows users to select the specific image they wish to share.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-pinterest-rich-row">
                            <th scope="row">
                                <label for="envira-config-social-networks-pinterest-rich"><?php _e( 'Pinterest Rich Pins', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-social-networks-pinterest-rich" type="checkbox" name="<?php echo $key; ?>[social_pinterest_rich]" value="1" <?php checked( $instance->get_config( 'social_pinterest_rich', $instance->get_config_default( 'social_pinterest_rich' ) ), 1 ); ?> />
                                <span class="description"><?php _e( 'Enable Pinterest\'s Rich Pins on the page where this gallery is displayed. Important: Pinterest must pre-approve your site for this option to work. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.', 'envira-social' ); ?></span>
                            </td>
                        </tr>

                    </tbody>
                </table>

                <table class="form-table">
                    <thead>
                        <td colspan="2" scope="row" style="padding:0;">
                            <h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; width: 100%; line-height: 50px;">
                                <img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo $this->base->path; ?>assets/images/admin_twitter.svg" alt="Twitter" />
                                <span style="display:inline-block; height: 100%; vertical-align: middle; color: #000"><?php _e( 'Twitter Options', 'envira-social' ); ?></span>
                            </h3>
                        </th>
                    </thead>
                    <tbody>
                        <tr id="envira-config-social-networks-twitter-box">
                            <th scope="row">
                                <label for="envira-config-social-networks-twitter"><?php _e( 'Twitter Optional Text', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-social-networks-twitter" type="text" name="<?php echo $key; ?>[social_twitter_text]" value="<?php echo $instance->get_config( 'social_twitter_text', $instance->get_config_default( 'social_twitter_text' ) ); ?>" />
                                <p class="description">
                                    <?php _e( 'Enter an optional message to append to Tweets. The image, image URL and caption are automatically shared.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-twitter-summary-card">
                            <th scope="row">
                                <label for="envira-config-social-networks-twitter-summary-card"><?php _e( 'Twitter Summary Card', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <select id="envira-config-social-networks-twitter-summary-card" name="<?php echo $key; ?>[social_twitter_sharing_method]">
                                    <?php foreach ( (array) $this->get_twitter_sharing_methods() as $value => $name ) : ?>
                                        <option value="<?php echo $value; ?>"<?php selected( $value, $instance->get_config( 'social_twitter_sharing_method', $instance->get_config_default( 'social_twitter_sharing_method' ) ) ); ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php _e( 'Twitter summary cards share additional details (such as an image, title, and caption) than a standard text Tweet.', 'envira-social' ); ?><br/>
                                    <?php _e( '<strong>No Summary Card</strong> disables Summary Cards and shares only a text link.', 'envira-social' ); ?><br/>
                                    <?php _e( '<strong>Summary Card + Thumbnail</strong> shares a 120 x 120 pixel image with Title and Caption. <a href="https://dev.twitter.com/cards/types/summary">Learn more</a>.', 'envira-social' ); ?><br/>
                                    <?php _e( '<strong>Summary Card + Large Image</strong> shares a larger image with Title and Caption. <a href="https://dev.twitter.com/cards/types/summary-large-image">Learn more</a>.', 'envira-social' ); ?><br/>
                                    <?php _e( 'The image shared from the gallery is used for the Summary Card image. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-twitter-summary-card-site">
                            <th scope="row">
                                <label for="envira-config-social-networks-twitter-summary-card-site"><?php _e( 'Twitter Summary Card Username', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <input id="envira-config-social-networks-twitter-summary-card-site" type="text" name="<?php echo $key; ?>[social_twitter_summary_card_site]" value="<?php echo $instance->get_config( 'social_twitter_summary_card_site', $instance->get_config_default( 'social_twitter_summary_card_site' ) ); ?>" />
                                <p class="description">
                                    <?php _e( 'The Twitter username to attribute the Summary Card to, starting with the "@" sign. <strong>Example:</strong> @enviragallery', 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                        <tr id="envira-config-social-networks-twitter-summary-card-desc">
                            <th scope="row">
                                <label for="envira-config-social-networks-twitter-summary-card-desc"><?php _e( 'Twitter Summary Card Description', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <textarea id="envira-config-social-networks-twitter-summary-card-desc" name="<?php echo $key; ?>[social_twitter_summary_card_desc]"><?php echo $instance->get_config( 'social_twitter_summary_card_desc', $instance->get_config_default( 'social_twitter_summary_card_desc' ) ); ?></textarea>
                                <p class="description">
                                    <?php _e( 'Twitter requires a description for Summary Cards. Envira will attempt to find and use the image caption, gallery description, or Twitter Optional Text setting (in that order). Optionally, Envira can pass the custom text entered in this field to Twitter instead. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.' , 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>

                    </tbody>
                </table>

                <table class="form-table">
                    <thead>
                        <td colspan="2" scope="row" style="padding:0;">
                            <h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; color: #fff; width: 100%; line-height: 50px;">
                                <img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo $this->base->path; ?>assets/images/admin_google.svg" alt="Google+" />
                                <span style="display:inline-block; height: 100%; vertical-align: middle; color: #000"><?php _e( 'Google+ Options', 'envira-social' ); ?></span>
                            </h3>
                        </th>
                    </thead>
                    <tbody>

                        <tr id="envira-config-social-networks-google-default-desc">
                            <th scope="row">
                                <label for="envira-config-social-networks-google-default-desc"><?php _e( 'Google+ Default Description', 'envira-social' ); ?></label>
                            </th>
                            <td>
                                <textarea id="envira-config-social-networks-google-default-desc" name="<?php echo $key; ?>[social_google_desc]"><?php echo $instance->get_config( 'social_google_desc', $instance->get_config_default( 'social_google_desc' ) ); ?></textarea>
                                <p class="description">
                                    <?php _e( 'Enter the text you would like Google+ to use if no description or caption is found for your gallery or image being shared. <a href="http://enviragallery.com/docs/social-addon/" target="_blank">See our documentation</a> for additional information.
    Field accepts text only, no HTML.' , 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>


                    </tbody>
                </table>

                <table class="form-table">
                    <thead>
                        <td colspan="2" scope="row" style="padding:0;">
                            <h3 class="social-heading" style="font-size: 1.1em; padding: 0; text-indent: 0px; background-color: #fff; color: #fff; width: 100%; line-height: 50px; margin-bottom: 0;">
                                <!-- <img style="display:inline-block; margin: 10px 10px 10px 0; float: left;" width="30" height="30" src="<?php echo $this->base->path; ?>assets/images/admin_google.svg" alt="Email" /> -->
                                <div class="envira-social-settings-email-icon"></div>
                                <span style="display:inline-block; line-height: 32px; height: 100%; vertical-align: top; color: #000"><?php _e( 'Email Options', 'envira-social' ); ?></span>
                            </h3>
                        </th>
                    </thead>
                    <tbody>

                        <tr id="envira-config-social-networks-google-default-desc">
                            <th scope="row">
                                <label for="envira-config-social-networks-google-default-desc"><?php _e( 'Image Size To Share', 'envira-social' ); ?></label>
                            </th>

                            <td>
                                <select id="envira-config-image-size" name="<?php echo $key; ?>[social_email_image_size]">
                                    <?php
                                    foreach ( (array) $this->get_email_image_sizes() as $option_value => $option_name ) {
                                        ?>
                                        <option value="<?php echo $option_value; ?>"<?php selected( $option_value, $instance->get_config( 'social_email_image_size', $instance->get_config_default( 'social_email_image_size' ) ) ); ?>><?php echo $option_name; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="description">
                                    <?php _e( 'Select if you want to share the url of the full sized image or a smaller image via email.' , 'envira-social' ); ?>
                                </p>
                            </td>
                        </tr>


                    </tbody>
                </table>

        </div>

        <?php
	
	}
	
    /**
     * Adds addon settings UI to the Mobile tab
     *
     * @since 1.0.9
     *
     * @param object $post The current post object.
     */
    function mobile_screen( $post ) {
        
        // Get post type so we load the correct metabox instance and define the input field names
        // Input field names vary depending on whether we are editing a Gallery or Album
        $post_type = get_post_type( $post );
        switch ( $post_type ) {
            /**
            * Gallery
            */
            case 'envira':
                $instance = Envira_Gallery_Metaboxes::get_instance();
                $key = '_envira_gallery';
                break;
            
            /**
            * Album
            */
            case 'envira_album':
                $instance = Envira_Albums_Metaboxes::get_instance();
                $key = '_eg_album_data[config]';
                break; 
        }
        ?>
        <tr id="envira-config-social-mobile-box">
            <th scope="row">
                <label for="envira-config-social-mobile"><?php _e( 'Display Social Sharing Buttons On Gallery?', 'envira-social' ); ?></label>
            </th>
            <td>
                <input id="envira-config-social-mobile" type="checkbox" name="<?php echo $key; ?>[mobile_social]" value="1" <?php checked( $instance->get_config( 'mobile_social', $instance->get_config_default( 'mobile_social' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'If enabled, will display social sharing buttons based on the settings in the Social Addon: Gallery settings. If disabled, no social sharing buttons for galleries will be displayed on mobile.', 'envira-social' ); ?></span>
            </td>
        </tr>
        <tr id="envira-config-social-networks-mobile-box">
            <th scope="row">
                <label><?php _e( 'Social Networks', 'envira-social' ); ?></label>
            </th>
            <td>
                <?php
                foreach ( $this->get_networks() as $network => $name ) {
                    ?>
                    <label for="envira-config-mobile-social-<?php echo $network; ?>" class="label-for-checkbox">
                        <input id="envira-config-mobile-social-<?php echo $network; ?>" type="checkbox" name="<?php echo $key; ?>[mobile_social_<?php echo $network; ?>]" value="1" <?php checked( $instance->get_config( 'mobile_social_' . $network, $instance->get_config_default( 'mobile_social_' . $network ) ), 1 ); ?> />
                        <?php echo $name; ?>
                    </label>
                    <?php   
                }
                ?>
            </td>
        </tr>

        <?php

    }
    
    /**
     * Adds addon settings UI to the Mobile tab
     *
     * @since 1.0.9
     *
     * @param object $post The current post object.
     */
    function mobile_lightbox_screen( $post ) {
        
        // Get post type so we load the correct metabox instance and define the input field names
        // Input field names vary depending on whether we are editing a Gallery or Album
        $post_type = get_post_type( $post );
        switch ( $post_type ) {
            /**
            * Gallery
            */
            case 'envira':
                $instance = Envira_Gallery_Metaboxes::get_instance();
                $key = '_envira_gallery';
                break;
            
            /**
            * Album
            */
            case 'envira_album':
                $instance = Envira_Albums_Metaboxes::get_instance();
                $key = '_eg_album_data[config]';
                break; 
        }
        ?>

        <tr id="envira-config-social-lightbox-mobile-box">
            <th scope="row">
                <label for="envira-config-social-mobile"><?php _e( 'Display Social Sharing Buttons In Lightboxes?', 'envira-social' ); ?></label>
            </th>
            <td>
                <input id="envira-config-social-mobile" type="checkbox" name="<?php echo $key; ?>[mobile_social_lightbox]" value="1" <?php checked( $instance->get_config( 'mobile_social_lightbox', $instance->get_config_default( 'mobile_social_lightbox' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'If enabled, will display social sharing buttons based on the settings in the Social Addon: Lightbox settings. If disabled, no social sharing buttons in lightboxes will be displayed on mobile.', 'envira-social' ); ?></span>
            </td>
        </tr>
        <tr id="envira-config-social-networks-lightbox-mobile-box">
            <th scope="row">
                <label><?php _e( 'Social Networks', 'envira-social' ); ?></label>
            </th>
            <td>
                <?php
                foreach ( $this->get_networks() as $network => $name ) {
                    ?>
                    <label for="envira-config-mobile-social-lightbox-<?php echo $network; ?>" class="label-for-checkbox">
                        <input id="envira-config-mobile-social-lightbox-<?php echo $network; ?>" type="checkbox" name="<?php echo $key; ?>[mobile_social_lightbox_<?php echo $network; ?>]" value="1" <?php checked( $instance->get_config( 'mobile_social_lightbox_' . $network, $instance->get_config_default( 'mobile_social_lightbox_' . $network ) ), 1 ); ?> />
                        <?php echo $name; ?>
                    </label>
                    <?php   
                }
                ?>
            </td>
        </tr>

        <?php

    }

	/**
     * Helper method for retrieving social networks.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_networks() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_networks();

    }

	/**
     * Helper method for retrieving positions.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_positions() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_positions();

    }


    /**
     * Helper method for retrieving Twitter sharing methods.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_twitter_sharing_methods() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_twitter_sharing_methods();

    }

    /**
     * Helper method for retrieving Facebook sharing methods.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_facebook_show_options() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_facebook_show_options();

    }

    /**
     * Helper method for retrieving Facebook sharing methods.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_facebook_show_tag_options() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_facebook_show_tag_options();

    }

    /**
     * Helper method for retrieving Pinterest sharing methods.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_pinterest_share_options() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_pinterest_share_options();

    }

    /**
     * Helper method for retrieving Pinterest sharing methods.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_email_image_sizes() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_email_image_sizes();

    }


    /**
     * Helper method for retrieving orientations.
     *
     * @since 1.0.0
     *
     * @return array Array of position data.
     */
    public function get_orientations() {

        $instance = Envira_Social_Common::get_instance();
        return $instance->get_orientations();

    }
	
	/**
	 * Saves the addon's settings for Galleries.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int $pos_tid     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	function gallery_settings_save( $settings, $post_id ) {
		
		// Gallery
	    $settings['config']['social']          			= ( isset( $_POST['_envira_gallery']['social'] ) ? 1 : 0 );
	    foreach ( $this->get_networks() as $network => $name ) {
	    	$settings['config'][ 'social_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'social_' . $network ] ) ? 1 : 0 );
		}

        // The below four options were moved to _general in the settings form.        
        // 
		// $settings['config']['social_facebook_text']     = sanitize_text_field( esc_attr( $_POST['_envira_gallery']['social_facebook_text'] ) );
        // $settings['config']['social_twitter_text']      = sanitize_text_field( esc_attr( $_POST['_envira_gallery']['social_twitter_text'] ) );
        
        $settings['config']['social_position']          = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_envira_gallery']['social_position'] );
        $settings['config']['social_orientation']       = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_envira_gallery']['social_orientation'] );

        // Misc (New)
        
        // $settings['config']['social_link_standalone']   = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_general']['social_link_standalone'] );

        // Twitter (New)
        
        $settings['config']['social_twitter_text']                  = sanitize_text_field( $_POST['_general']['social_twitter_text'] );
        $settings['config']['social_twitter_sharing_method']        = sanitize_text_field( $_POST['_general']['social_twitter_sharing_method'] );
        $settings['config']['social_twitter_summary_card_site']     = sanitize_text_field( $_POST['_general']['social_twitter_summary_card_site'] );
        $settings['config']['social_twitter_summary_card_desc']     = sanitize_text_field( $_POST['_general']['social_twitter_summary_card_desc'] );

        // Google (New)

        $settings['config']['social_google_desc'] = sanitize_text_field( $_POST['_general']['social_google_desc'] );

        // Facebook (New)
        foreach ( $this->get_facebook_show_options() as $value => $name ) {
            $settings['config'][ 'social_facebook_show_option_' . $value ] = ( isset( $_POST['_general'][ 'social_facebook_show_option_' . $value ] ) ? 1 : 0 );
        }
        $settings['config']['social_facebook_text']         = sanitize_text_field( $_POST['_general']['social_facebook_text'] );
        $settings['config']['social_facebook_tag_options']  = sanitize_text_field( $_POST['_general']['social_facebook_tag_options'] );
        $settings['config']['social_facebook_tags_manual']  = sanitize_text_field( $_POST['_general']['social_facebook_tags_manual'] );
        $settings['config']['social_facebook_quote']        = sanitize_text_field( $_POST['_general']['social_facebook_quote'] );

        // Pinterest (new)
        $settings['config']['social_pinterest_type']        = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_general']['social_pinterest_type'] );

        if ( ! empty( $_POST['_general']['social_pinterest_rich'] ) ) {
            $settings['config']['social_pinterest_rich']        = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_general']['social_pinterest_rich'] );
        } else {
            $settings['config']['social_pinterest_rich']        = false;
        }

        // Email
        $settings['config']['social_email_image_size']        = ( isset( $_POST['_general']['social_email_image_size'] ) ? sanitize_text_field( $_POST['_general']['social_email_image_size'] ) : '' ); 

	    // Lightbox
	    $settings['config']['social_lightbox'] 			= ( isset( $_POST['_envira_gallery']['social_lightbox'] ) ? 1 : 0 );
	    foreach ( $this->get_networks() as $network => $name ) {
	    	$settings['config'][ 'social_lightbox_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'social_lightbox_' . $network ] ) ? 1 : 0 );
		}

        // The below four options were moved to _general in the settings form.    
		
        // $settings['config']['social_lightbox_facebook_text'] = sanitize_text_field( esc_attr( $_POST['_envira_gallery']['social_lightbox_facebook_text'] ) );
        // $settings['config']['social_lightbox_twitter_text']  = sanitize_text_field( esc_attr( $_POST['_envira_gallery']['social_lightbox_twitter_text'] ) );
        
        $settings['config']['social_lightbox_position']    = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_envira_gallery']['social_lightbox_position'] );
		$settings['config']['social_lightbox_outside']     = ( isset( $_POST['_envira_gallery']['social_lightbox_outside'] ) ? 1 : 0 );
        $settings['config']['social_lightbox_orientation'] = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_envira_gallery']['social_lightbox_orientation'] );

        // Mobile
        $settings['config']['mobile_social']              = ( isset( $_POST['_envira_gallery']['mobile_social'] ) ? 1 : 0 );
        foreach ( $this->get_networks() as $network => $name ) {
            $settings['config'][ 'mobile_social_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'mobile_social_' . $network ] ) ? 1 : 0 );
        }
        $settings['config']['mobile_social_lightbox']              = ( isset( $_POST['_envira_gallery']['mobile_social_lightbox'] ) ? 1 : 0 );
        foreach ( $this->get_networks() as $network => $name ) {
            $settings['config'][ 'mobile_social_lightbox_' . $network ] = ( isset( $_POST['_envira_gallery'][ 'mobile_social_lightbox_' . $network ] ) ? 1 : 0 );
        }

	    return $settings;
	
	}

	/**
	 * Saves the addon's settings for Albums.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings  Array of settings to be saved.
	 * @param int $pos_tid     The current post ID.
	 * @return array $settings Amended array of settings to be saved.
	 */
	function album_settings_save( $settings, $post_id ) {
		
        $settings['config']['social']                   = ( isset( $_POST['_eg_album_data']['config']['social'] ) ? 1 : 0 );
        foreach ( $this->get_networks() as $network => $name ) {
            $settings['config'][ 'social_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'social_' . $network ] ) ? 1 : 0 );
        }

        $settings['config']['social_position']          = ( isset( $_POST['_eg_album_data']['config']['social_position'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['social_position'] ) : false;
        $settings['config']['social_orientation']       = ( isset( $_POST['_eg_album_data']['config']['social_orientation'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['social_orientation'] ) : false;

	    // Lightbox
	    $settings['config']['social_lightbox'] 			= ( isset( $_POST['_eg_album_data']['config']['social_lightbox'] ) ? 1 : 0 );
	    foreach ( $this->get_networks() as $network => $name ) {
	    	$settings['config'][ 'social_lightbox_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'social_lightbox_' . $network ] ) ? 1 : 0 );
		}
		$settings['config']['social_lightbox_position']    = ( isset( $_POST['_eg_album_data']['config']['social_lightbox_position'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['social_lightbox_position'] ) : false;
		$settings['config']['social_lightbox_outside']     = ( isset( $_POST['_eg_album_data']['config']['social_lightbox_outside'] ) ? 1 : 0 );
        $settings['config']['social_lightbox_orientation'] = ( isset( $_POST['_eg_album_data']['config']['social_lightbox_orientation'] ) ) ? preg_replace( '#[^a-z0-9-_]#', '', $_POST['_eg_album_data']['config']['social_lightbox_orientation'] ) : false;        
        
        if ( isset( $_POST['_general'] ) ) {

            // Twitter (New)

            $settings['config']['social_twitter_text']                  = sanitize_text_field( $_POST['_general']['social_twitter_text'] );
            $settings['config']['social_twitter_sharing_method']        = sanitize_text_field( $_POST['_general']['social_twitter_sharing_method'] );
            $settings['config']['social_twitter_summary_card_site']     = sanitize_text_field( $_POST['_general']['social_twitter_summary_card_site'] );
            $settings['config']['social_twitter_summary_card_desc']     = sanitize_text_field( $_POST['_general']['social_twitter_summary_card_desc'] );

            // Google (New)

            $settings['config']['social_google_desc'] = sanitize_text_field( $_POST['_general']['social_google_desc'] );

            // Facebook (New)
            foreach ( $this->get_facebook_show_options() as $value => $name ) {
                $settings['config'][ 'social_facebook_show_option_' . $value ] = ( isset( $_POST['_general'][ 'social_facebook_show_option_' . $value ] ) ? 1 : 0 );
            }
            $settings['config']['social_facebook_text']         = sanitize_text_field( $_POST['_general']['social_facebook_text'] );
            $settings['config']['social_facebook_tag_options']  = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_general']['social_facebook_tag_options'] );
            $settings['config']['social_facebook_tags_manual']  = sanitize_text_field( $_POST['_general']['social_facebook_tags_manual'] );
            $settings['config']['social_facebook_quote']        = sanitize_text_field( $_POST['_general']['social_facebook_quote'] );

            // Pinterest (new)
            $settings['config']['social_pinterest_type']        = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_general']['social_pinterest_type'] );
            if ( ! empty( $_POST['_general']['social_pinterest_rich'] ) ) {
                $settings['config']['social_pinterest_rich']        = preg_replace( '#[^a-z0-9-_]#', '', $_POST['_general']['social_pinterest_rich'] );
            } else {
                $settings['config']['social_pinterest_rich']        = false;
            }

            // Email
            $settings['config']['social_email_image_size']        = ( isset( $_POST['_general']['social_email_image_size'] ) ? sanitize_text_field( $_POST['_general']['social_email_image_size'] ) : '' ); 

        }

        // Mobile
        $settings['config']['mobile_social']              = ( isset( $_POST['_eg_album_data']['config']['mobile_social'] ) ? 1 : 0 );
        foreach ( $this->get_networks() as $network => $name ) {
            $settings['config'][ 'mobile_social_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'mobile_social_' . $network ] ) ? 1 : 0 );
        }
        $settings['config']['mobile_social_lightbox']              = ( isset( $_POST['_eg_album_data']['config']['mobile_social_lightbox'] ) ? 1 : 0 );
        foreach ( $this->get_networks() as $network => $name ) {
            $settings['config'][ 'mobile_social_lightbox_' . $network ] = ( isset( $_POST['_eg_album_data']['config'][ 'mobile_social_lightbox_' . $network ] ) ? 1 : 0 );
        }

	    return $settings;
	
	}
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Pagination_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Social_Metaboxes ) ) {
            self::$instance = new Envira_Social_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_social_metaboxes = Envira_Social_Metaboxes::get_instance();