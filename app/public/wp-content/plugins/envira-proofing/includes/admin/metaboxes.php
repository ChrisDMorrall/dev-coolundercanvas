<?php
/**
 * Metabox class.
 *
 * @since 1.0.0
 *
 * @package Envira_Proofing
 * @author  Envira Team
 */
class Envira_Proofing_Metaboxes {

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

    	// Load the base class object.
        $this->base = Envira_Proofing::get_instance();

    	// Scripts and styles
        add_action( 'envira_gallery_metabox_styles', array( $this, 'meta_box_styles' ) );
        add_action( 'envira_gallery_metabox_scripts', array( $this, 'meta_box_scripts' ) );
 
		// Tab and Metabox
        add_filter( 'envira_gallery_tab_nav', array( $this, 'tabs' ) );
        add_action( 'envira_gallery_lightbox_box', array( $this, 'lightbox_box' ) );
        add_action( 'envira_gallery_tab_proofing', array( $this, 'proofing_box' ) );

        // Save Settings
		add_filter( 'envira_gallery_save_settings', array( $this, 'save' ), 10, 2 );

    }

    /**
     * Enqueues CSS for the metabox
	 *
	 * @since 1.0.0
     */
    public function meta_box_styles() {

        wp_enqueue_style( $this->base->plugin_slug . '-metabox-style', plugins_url( 'assets/css/metabox.css', $this->base->file ), array(), $this->base->version );
        
    }

    /**
     * Enqueues JS for the metabox
	 *
	 * @since 1.0.0
     */
    public function meta_box_scripts() {

    	wp_enqueue_script( $this->base->plugin_slug . '-metabox-script', plugins_url( 'assets/js/metabox.js', $this->base->file ), array( 'jquery', 'jquery-ui-sortable' ), $this->base->version, true );
    	wp_localize_script(
            $this->base->plugin_slug . '-metabox-script',
            'envira_proofing_metabox',
            array(
                'delete_size'	=> __( 'Are you sure you want to delete this size?', 'envira-proofing' ),
                'clear_order'	=> __( 'Are you sure you want to clear this order? This action cannot be reversed, and the user will need to re-order again.', 'envira-proofing' ),
                'unlock_order'	=> __( 'Are you sure you want to unlock this order? This action cannot be reversed, and the user will need to re-submit again.', 'envira-proofing' ),
                'nonce'			=> wp_create_nonce( 'envira-proofing-clear-order' ),
            )
        );

        // Conditional Fields
        wp_register_script( $this->base->plugin_slug . '-conditional-fields-script', plugins_url( 'assets/js/min/conditional-fields-min.js', $this->base->file ), array( 'jquery', Envira_Gallery::get_instance()->plugin_slug . '-conditional-fields-script' ), $this->base->version, true );
        wp_enqueue_script( $this->base->plugin_slug . '-conditional-fields-script' );
    }
   
	/**
	 * Adds a tab for this Addon
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Tabs
	 * @return array Tabs
	 */
	public function tabs( $tabs ) {

		$tabs['proofing'] = __( 'Proofing', 'envira-proofing' );
		return $tabs;
		
	}

	/**
	 * Adds addon settings UI to the Lightbox tab
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function lightbox_box( $post ) {

		// Setup instance and meta key
		$instance = Envira_Gallery_Metaboxes::get_instance();
		?>
	    <tr id="envira-config-lightbox-proofing-box">
            <th scope="row">
                <label for="envira-config-lightbox-proofing"><?php _e( 'Display Proofing Options?', 'envira-proofing' ); ?></label>
            </th>
            <td>
                <input id="envira-config-lightbox-proofing" type="checkbox" name="_envira_gallery[proofing_lightbox]" value="<?php echo $instance->get_config( 'proofing_lightbox', $instance->get_config_default( 'proofing_lightbox' ) ); ?>" <?php checked( $instance->get_config( 'proofing_lightbox', $instance->get_config_default( 'proofing_lightbox' ) ), 1 ); ?> />
                <span class="description"><?php _e( 'If enabled, and the Proofing Addon is configured, displays Proofing options for each image in the Lightbox view.', 'envira-proofing' ); ?></span>
            </td>
        </tr>
		<?php

	}

    /**
	 * Adds addon settings UI to the Proofing tab
	 *
	 * @since 1.0.0
	 *
	 * @param object $post The current post object.
	 */
	public function proofing_box( $post ) {
		
		// Setup instance and meta key
		$instance = Envira_Gallery_Metaboxes::get_instance();
		$orders = Envira_Proofing_Order::get_instance()->get_orders( $post->ID );
		$key = '_envira_gallery';

		// Get orientations
		$orientations = Envira_Proofing_Common::get_instance()->get_size_orientations();

		// If the Defaults Addon isn't enabled, show a notice that this can all be preconfigured to save time
		// if the user installs our Defaults Addon.
		if ( ! is_plugin_active( 'envira-defaults/envira-defaults.php' ) ) {
			if ( class_exists( 'Envira_Gallery_Notice_Admin' ) ) {
				// Display notice
				Envira_Gallery_Notice_Admin::get_instance()->display_inline_notice( 
					'proofing_defaults', 
					__( 'Want to preconfigure Proofing options and sizes?', 'envira-proofing' ),
					__( 'Install and activate the <strong>Envira Defaults Addon</strong> to set Proofing options for all future Envira Galleries.', 'envira-proofing' ),
					'success',
					__( 'Click here to manage your Envira Addons', 'envira-proofing' ),
					'edit.php?post_type=envira&amp;page=envira-gallery-settings#!envira-tab-addons',
					true
				);
			}
		}
	    ?>
        <div id="envira-proofing">
            <p class="envira-intro">
            	<?php _e( 'Proofing Gallery Settings', 'envira-proofing' ); ?>
            	<small>
            		<?php _e( 'The settings below adjust the Proofing options for the Gallery.', 'envira-proofing' ); ?>
	        		<br />
	                <?php _e( 'Need some help?', 'envira-proofing' ); ?>
	                <a href="http://enviragallery.com/docs/proofing-addon/" class="envira-doc" target="_blank">
	                    <?php _e( 'Read the Documentation', 'envira-proofing' ); ?>
	                </a>
	                or
	                <a href="https://www.youtube.com/embed/Hg12iisPEdw/?rel=0" class="envira-video" target="_blank">
	                    <?php _e( 'Watch a Video', 'envira-proofing' ); ?>
	                </a>
	            </small>
            </p>
            <table class="form-table">
                <tbody>
			        <tr id="envira-proofing-enabled-box">
			            <th scope="row">
			                <label for="envira-proofing-enabled"><?php _e( 'Enabled?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-enabled" type="checkbox" name="<?php echo $key; ?>[proofing]" value="1" <?php checked( $instance->get_config( 'proofing', $instance->get_config_default( 'proofing' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'Enables or disables the Proofing selection process.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-edit-box">
			            <th scope="row">
			                <label for="envira-proofing-edit"><?php _e( 'Allow User to Edit Submitted Order?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-edit" type="checkbox" name="<?php echo $key; ?>[proofing_edit]" value="1" <?php checked( $instance->get_config( 'proofing_edit', $instance->get_config_default( 'proofing_edit' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'Allow Users to edit a Proofing Order they have previously submitted.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-delete-box">
			            <th scope="row">
			                <label for="envira-proofing-delete"><?php _e( 'Allow User to Delete Submitted Order?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-delete" type="checkbox" name="<?php echo $key; ?>[proofing_delete]" value="1" <?php checked( $instance->get_config( 'proofing_delete', $instance->get_config_default( 'proofing_delete' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'Allow Users to delete a Proofing Order they have previously submitted.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-multiple-enabled-box">
			            <th scope="row">
			                <label for="envira-proofing-multiple-enable"><?php _e( 'Require Email Address', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-multiple-enabled" type="checkbox" name="<?php echo $key; ?>[proofing_multiple_enabled]" value="1" <?php checked( $instance->get_config( 'proofing_multiple_enabled', $instance->get_config_default( 'proofing_multiple_enabled' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'If enabled, each visitor will need to supply their email address to make an order. This is useful if you have more than one person submitting orders and/or if the gallery is public. Orders will also be displayed by each individual email address below.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-email-box-position-box">
			            <th scope="row">
			                <label for="envira-proofing-multiple-enable"><?php _e( 'Display Email Form?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
	                        <select id="envira-config-email-box-position" name="_envira_gallery[proofing_email_box_position]">
	                            <?php foreach ( (array) $this->get_envira_email_box_positions() as $i => $data ) : ?>
	                                <option value="<?php echo $data['value']; ?>"<?php selected( $data['value'], $instance->get_config( 'proofing_email_box_position', $instance->get_config_default( 'proofing_email_box_position' ) ) ); ?>><?php echo $data['name']; ?></option>
	                            <?php endforeach; ?>
	                        </select>
			                <p class="description"><?php _e( 'Display the email form above or below gallery images.', 'envira-proofing' ); ?></p>
			            </td>
			        </tr>
			        <tr id="envira-proofing-name-enabled-box">
			            <th scope="row">
			                <label for="envira-proofing-name-enable"><?php _e( 'Add Name Field?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-name-enabled" type="checkbox" name="<?php echo $key; ?>[proofing_name_enabled]" value="1" <?php checked( $instance->get_config( 'proofing_name_enabled', $instance->get_config_default( 'proofing_name_enabled' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'If enabled, each visitor will be asked for their name as well as their email address.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>

			        <tr id="envira-proofing-hide-until-email">
			            <th scope="row">
			                <label for="envira-proofing-quantity-enabled"><?php _e( 'Hide Gallery until Email Address entered?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-quantity-enabled" type="checkbox" name="<?php echo $key; ?>[proofing_hide_gallery]" value="1" <?php checked( $instance->get_config( 'proofing_hide_gallery', $instance->get_config_default( 'proofing_hide_gallery' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'Check this option if you do not want to display the Gallery until the user has entered their email address.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>

			        <tr id="envira-proofing-quantity-enabled-box">
			            <th scope="row">
			                <label for="envira-proofing-quantity-enabled"><?php _e( 'Enable Quantity Field?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-quantity-enabled" type="checkbox" name="<?php echo $key; ?>[proofing_quantity_enabled]" value="1" <?php checked( $instance->get_config( 'proofing_quantity_enabled', $instance->get_config_default( 'proofing_quantity_enabled' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'Enables or disables the option for the user to specify a quantity to order for each image.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>

			        <tr id="envira-proofing-size-enabled-box">
			            <th scope="row">
			                <label for="envira-proofing-size-enabled"><?php _e( 'Enable Size Options?', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-size-enabled" type="checkbox" name="<?php echo $key; ?>[proofing_size_enabled]" value="1" <?php checked( $instance->get_config( 'proofing_size_enabled', $instance->get_config_default( 'proofing_size_enabled' ) ), 1 ); ?> />
			                <span class="description"><?php _e( 'Enables or disables the option for the user to order different size(s) for each image.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>

			        <!-- Sizes -->
			        <tr id="envira-proofing-sizes-box">
			            <th scope="row">
			                <label for="envira-proofing-sizes"><?php _e( 'Sizes', 'envira-proofing' ); ?></label><br />
			                <small><a href="#" title="<?php _e( 'Add Size', 'envira-proofing' ); ?>" class="add"><?php _e( 'Add Size', 'envira-proofing' ); ?></a></small>
			            </th>
			            <td>
			            	<table id="envira-proofing-sizes">
			            		<tbody>
					            	<?php
					            	// Get available sizes
					            	$sizes = $instance->get_config( 'proofing_sizes', $instance->get_config_default( 'proofing_sizes' ) );
					            	if ( is_array( $sizes ) && count( $sizes ) > 0 ) {
					            		foreach ( $sizes as $size ) {
					            			?>
					            			<tr class="sortable">
							            		<td><input type="text" name="<?php echo $key; ?>[proofing_sizes][name][]" value="<?php echo esc_attr( $size['name'] ); ?>" placeholder="<?php _e( '4x6, 8x10 etc', 'envira-proofing' ); ?>"/></td>
							            		<td>
							            			<select name="<?php echo $key; ?>[proofing_sizes][orientation][]" size="1">
							            				<?php
							                            foreach ( (array) $orientations as $i => $data ) {
							                            	// Not using selected() as we introduced orientation in 1.0.2, so it's an undefined key for existing
							                            	// Proofing Galleries
								                            ?>
						                                    <option value="<?php echo $data['value']; ?>"<?php echo ( ( isset( $size['orientation'] ) && $data['value'] == $size['orientation'] ) ? ' selected' : '' ); ?>><?php echo $data['name']; ?></option>
															<?php
							                            }
						                                ?>
							            			</select>
							            		</td>
							            		<td>
							            			<a href="#" class="move" title="<?php _e( 'Move', 'envira-proofing' ); ?>"></a>
							            			<a href="#" class="dashicons dashicons-trash delete" title="<?php _e( 'Delete', 'envira-proofing' ); ?>"></a>
							            		</td>
							            	</tr>
					            			<?php
					            		}
					            	}
					            	?>

					            	<tr class="hidden">
					            		<td><input type="text" name="<?php echo $key; ?>[proofing_sizes][name][]" value="" /></td>
					            		<td>
					            			<select name="<?php echo $key; ?>[proofing_sizes][orientation][]" size="1">
					            				<?php
					                            foreach ( (array) $orientations as $i => $data ) {
					                            	?>
				                                    <option value="<?php echo $data['value']; ?>"><?php echo $data['name']; ?></option>
													<?php
					                            }
				                                ?>
					            			</select>
					            		</td>
					            		<td>
					            			<a href="#" class="move" title="<?php _e( 'Move', 'envira-proofing' ); ?>"></a>
					            			<a href="#" class="dashicons dashicons-trash delete" title="<?php _e( 'Delete', 'envira-proofing' ); ?>"></a>
					            		</td>
					            	</tr>
					            </tbody>
					        </table>
			            </td>
			        </tr>
			        <tr id="envira-proofing-add-to-order-label-box">
			            <th scope="row">
			                <label for="envira-proofing-add-to-order-label"><?php _e( 'Add to Order Label', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-add-to-order-label" type="text" name="<?php echo $key; ?>[proofing_add_to_order_label]" value="<?php echo esc_attr( $instance->get_config( 'proofing_add_to_order_label', $instance->get_config_default( 'proofing_add_to_order_label' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The text to display on the Add to Order label in the Lightbox view.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
					<tr id="envira-proofing-save-button-label-box">
			            <th scope="row">
			                <label for="envira-proofing-save-button-label"><?php _e( 'Save Button Label', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-save-button-label" type="text" name="<?php echo $key; ?>[proofing_save_button_label]" value="<?php echo esc_attr( $instance->get_config( 'proofing_save_button_label', $instance->get_config_default( 'proofing_save_button_label' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The text to display on the save button, which allows the user to save the order.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
					<tr id="envira-proofing-edit-button-label-box">
			            <th scope="row">
			                <label for="envira-proofing-edit-button-label"><?php _e( 'Edit Order Button Label', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-edit-button-label" type="text" name="<?php echo $key; ?>[proofing_edit_button_label]" value="<?php echo esc_attr( $instance->get_config( 'proofing_edit_button_label', $instance->get_config_default( 'proofing_edit_button_label' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The text to display on the edit button, which allows the user to edit the order.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
					<tr id="envira-proofing-delete-button-label-box">
			            <th scope="row">
			                <label for="envira-proofing-delete-button-label"><?php _e( 'Delete Button Label', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-delete-button-label" type="text" name="<?php echo $key; ?>[proofing_delete_button_label]" value="<?php echo esc_attr( $instance->get_config( 'proofing_delete_button_label', $instance->get_config_default( 'proofing_delete_button_label' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The text to display on the delete button, which allows the user to delete the order.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-submit-button-label-box">
			            <th scope="row">
			                <label for="envira-proofing-submit-button-label"><?php _e( 'Submit Button Label', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-submit-button-label" type="text" name="<?php echo $key; ?>[proofing_submit_button_label]" value="<?php echo esc_attr( $instance->get_config( 'proofing_submit_button_label', $instance->get_config_default( 'proofing_submit_button_label' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The text to display on the submit button, which allows the user to submit/finalise the order.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-save-notes-button-label-box">
			            <th scope="row">
			                <label for="envira-proofing-order-notes-placeholder-text"><?php _e( 'Order Notes Placeholder Text', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-order-notes-placeholder-text" type="text" name="<?php echo $key; ?>[proofing_notes_placeholder_text]" value="<?php echo esc_attr( $instance->get_config( 'proofing_notes_placeholder_text', $instance->get_config_default( 'proofing_notes_placeholder_text' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The placeholder text to display in the Order Notes field.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-save-notes-button-label-box">
			            <th scope="row">
			                <label for="envira-proofing-save-notes-button-label"><?php _e( 'Save Notes Button Label', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-save-notes-button-label" type="text" name="<?php echo $key; ?>[proofing_save_notes_button_label]" value="<?php echo esc_attr( $instance->get_config( 'proofing_save_notes_button_label', $instance->get_config_default( 'proofing_save_notes_button_label' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The text to display on the save button, which is displayed once an order has been submitted.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
					<tr id="envira-proofing-submitted-message-box">
			            <th scope="row">
			                <label for="envira-proofing-submitted-message"><?php _e( 'Submitted Message', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
	                        <?php
	                        $message = $instance->get_config( 'proofing_submitted_message' );
	                        if ( empty( $message ) ) {
		                        $message = $instance->get_config_default( 'proofing_submitted_message' );
		                    }
	                        wp_editor( $message, 'envira-proofing-submitted-message', array(
	                        	'media_buttons' => false,
	                        	'wpautop' 		=> true,
	                        	'tinymce' 		=> true,
	                        	'textarea_name' => $key . '[proofing_submitted_message]',
	                        ) );
	                        ?>
                            <p class="description"><?php _e( 'The text to display once the user has submitted their order', 'envira-proofing' ); ?></p>
			            </td>
			        </tr>
			        <tr id="envira-proofing-email-box">
			            <th scope="row">
			                <label for="envira-proofing-email-label"><?php _e( 'Email Order Notifications To', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-email-label" type="text" name="<?php echo $key; ?>[proofing_email]" value="<?php echo $instance->get_config( 'proofing_email', $instance->get_config_default( 'proofing_email' ) ); ?>" /><br />
			                <span class="description"><?php _e( 'Enter an email address to receive notifications when a new order is submitted. Leave blank if you do not want to receive email notifications.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-email-subject-box">
			            <th scope="row">
			                <label for="envira-proofing-email-subject"><?php _e( 'Email Subject', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
			                <input id="envira-proofing-email-subject" type="text" name="<?php echo $key; ?>[proofing_email_subject]" value="<?php echo esc_attr( $instance->get_config( 'proofing_email_subject', $instance->get_config_default( 'proofing_email_subject' ) ) ); ?>" /><br />
			                <span class="description"><?php _e( 'The email message subject.', 'envira-proofing' ); ?></span>
			            </td>
			        </tr>
			        <tr id="envira-proofing-email-message-box">
			            <th scope="row">
			                <label for="envira-proofing-email-message"><?php _e( 'Email Message', 'envira-proofing' ); ?></label>
			            </th>
			            <td>
	                        <?php
	                        $message = $instance->get_config( 'proofing_email_message' );
	                        if ( empty( $message ) ) {
		                        $message = $instance->get_config_default( 'proofing_email_message' );
		                    }
	                        wp_editor( $message, 'envira-proofing-email-message', array(
	                        	'media_buttons' => false,
	                        	'wpautop' 		=> true,
	                        	'tinymce' 		=> true,
	                        	'textarea_name' => $key . '[proofing_email_message]',
	                        ) );
	                        ?>
                            <p class="description">
                            	<?php _e( 'The email message sent once the user has submitted their order.', 'envira-proofing' ); ?><br />
                            	<?php _e( 'Supported Tags:', 'envira-proofing' ); ?><br />
                            	<strong>{title}: </strong><?php _e( 'The Gallery Title', 'envira-proofing' ); ?><br />
                            	<strong>{url}: </strong><?php _e( 'The Gallery Edit URL', 'envira-proofing' ); ?><br />
                            	<strong>{order_email}:</strong><?php _e( 'The customer\'s email address', 'envira-proofing' ); ?>
                            </p>
			            </td>
			        </tr>
			    </tbody>
			</table>
		</div>

		<!-- Order -->
		<div id="envira-proofing-order">
			<p class="envira-intro">
				<?php _e( 'Orders', 'envira-proofing' ); ?>
				<small>
					<?php _e( 'Orders will display here as they are submitted by your visitors', 'envira-proofing' ); ?>
				</small>
			</p>

			<!-- Options -->
			<?php
			if ( is_array( $orders ) ) {
				foreach ( $orders as $email => $order ) {
					// Skip if the order has no images selected
					if ( ! isset( $order['images'] ) || count( $order['images'] ) == 0 ) {
						continue;
					}
					
					// Display order details
					?>
					<hr />
					<div class="envira-proofing-order">
						<div class="envira-alert envira-clear">
							<div style="float:left;">
								<?php
								if ( ! empty( $order['name'] ) ) {
									echo sprintf( __( 'Name: <strong>%s</strong><br />', 'envira-proofing' ), $order['name'] ); 
								}
								if ( ! empty( $order['email'] ) ) {
									echo sprintf( __( 'Email: <strong>%s</strong><br />', 'envira-proofing' ), $order['email'] ); 
								}
								
								echo sprintf( __( 'Order Status: <strong>%s</strong><br />', 'envira-proofing' ), $order['submitted'] ? __( 'Submitted', 'envira-proofing' ) : __( 'Not Submitted', 'envira-proofing' ) ); 
								
								if ( ! empty( $order['notes'] ) ) {
									echo sprintf( __( 'Order Notes: <strong>%s</strong><br />', 'envira-proofing' ), $order['notes'] ); 
								}
								?>
							</div>
							<?php
							if ( $order['submitted'] ) {
								?>
								<a href="#" class="envira-proofing-unlock-order button" data-email="<?php echo $email; ?>" style="float:right;margin-top:10px;"><?php _e( 'Unlock', 'envira-proofing' ); ?></a>
								<?php
							}
							?>
							<a href="#" class="envira-proofing-clear-order button" data-email="<?php echo $email; ?>" style="float:right;margin-top:10px;margin-right:5px;"><?php _e( 'Delete', 'envira-proofing' ); ?></a>
						</div>

						<table class="form-table">
			                <tbody>
			                	<?php if ( ! empty( $order['images'] ) ): ?>
				                	<?php
				                	foreach ( $order['images'] as $image_id => $image_order ) {
				                		?>
					                	<tr>
								            <th scope="row">
								            	<?php
					                   			$thumbnail = wp_get_attachment_image_src( $image_id, 'thumbnail' );
					                   			?>
					                   			<img src="<?php echo esc_url( $thumbnail[0] ); ?>"  /><br />
					                   			<?php echo basename( $thumbnail[0] ); ?>
											</th>
								            <td>
								            	<?php
								            	foreach ( $image_order as $size => $quantity ) {
								            		?>
								            		<strong><?php echo $size; ?></strong>: <?php echo $quantity; ?><br />
								            		<?php
								            	}
								            	?>
						                   	</td>
								        </tr>
								        <?php
								    }
								    ?>
								<?php endif; ?>
			                </tbody>
			            </table>
		            </div>
		            <?php
				}
			}
			?>
		</div>
        <?php
	
	}

	

    /**
     * Helper method for retrieving lightbox transition effects.
     *
     * @since 1.0.0
     *
     * @return array Array of transition effect data.
     */
    public function get_envira_email_box_positions() {

        $instance = Envira_Proofing_Common::get_instance();
        return $instance->get_envira_email_box_positions();

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
	function save( $settings, $post_id ) {

		// Lightbox
		$settings['config']['proofing_lightbox']				= ( isset( $_POST['_envira_gallery']['proofing_lightbox'] ) ? 1 : 0 );
		
		// Proofing Settings
	    $settings['config']['proofing']							= ( isset( $_POST['_envira_gallery']['proofing'] ) ? 1 : 0 );
		$settings['config']['proofing_edit']					= ( isset( $_POST['_envira_gallery']['proofing_edit'] ) ? 1 : 0 );
		$settings['config']['proofing_delete']					= ( isset( $_POST['_envira_gallery']['proofing_delete'] ) ? 1 : 0 );
		
		// Envira Proofing Integration Settings
	    $settings['config']['proofing_multiple_enabled']		= ( isset( $_POST['_envira_gallery']['proofing_multiple_enabled'] ) ? 1 : 0 );
	    $settings['config']['proofing_name_enabled']			= ( isset( $_POST['_envira_gallery']['proofing_name_enabled'] ) ? 1 : 0 );
	    $settings['config']['proofing_hide_gallery']			= ( isset( $_POST['_envira_gallery']['proofing_hide_gallery'] ) ? 1 : 0 );
	    $settings['config']['proofing_quantity_enabled']		= ( isset( $_POST['_envira_gallery']['proofing_quantity_enabled'] ) ? 1 : 0 );
	    $settings['config']['proofing_size_enabled'] 			= ( isset( $_POST['_envira_gallery']['proofing_size_enabled'] ) ? 1 : 0 );
	    $settings['config']['proofing_add_to_order_label']		= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_add_to_order_label'] ) );
	    $settings['config']['proofing_save_button_label']		= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_save_button_label'] ) );
	    $settings['config']['proofing_edit_button_label']		= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_edit_button_label'] ) );
	    $settings['config']['proofing_delete_button_label']		= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_delete_button_label'] ) );
	    $settings['config']['proofing_notes_placeholder_text']  = sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_notes_placeholder_text'] ) );
	    $settings['config']['proofing_save_notes_button_label']	= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_save_notes_button_label'] ) );
	    $settings['config']['proofing_submit_button_label']		= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_submit_button_label'] ) );
	    $settings['config']['proofing_submitted_message'] 		= trim( $_POST['_envira_gallery']['proofing_submitted_message'] );
	    $settings['config']['proofing_email'] 					= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_email'] ) );
	    if ( isset( $_POST['_envira_gallery']['proofing_email_box_position'] ) ) {
	    	$settings['config']['proofing_email_box_position'] 	= sanitize_text_field( $_POST['_envira_gallery']['proofing_email_box_position'] );	    
	    }
	    $settings['config']['proofing_email_subject'] 			= sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_email_subject'] ) );
	    $settings['config']['proofing_email_message'] 			= trim( $_POST['_envira_gallery']['proofing_email_message'] );
	    
	    // Sizes
	    $sizes = array();
	    if ( isset( $_POST['_envira_gallery']['proofing_sizes'] ) ) {
	    	// Build array of sizes
	    	foreach ( $_POST['_envira_gallery']['proofing_sizes']['name'] as $index => $name ) {
	    		// Skip empty sizes
	    		if ( empty( $_POST['_envira_gallery']['proofing_sizes']['name'][ $index ] ) ) {
	    			continue;
	    		}

	    		// Generate slug
	    		$slug = str_replace( ' ', '_', strtolower( sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_sizes']['name'][ $index ] ) ) ) );

	    		// Add to sizes array
	    		$sizes[ $slug ] = array(
	    			'slug' 			=> $slug,
	    			'name' 			=> sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_sizes']['name'][ $index ] ) ),	
	    			'orientation' 	=> sanitize_text_field( esc_attr( $_POST['_envira_gallery']['proofing_sizes']['orientation'][ $index ] ) ),
	    		);	
	    	}
	    }
	    $settings['config']['proofing_sizes'] = $sizes;

	    return $settings;
	
	}
	
    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Proofing_Metaboxes object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Proofing_Metaboxes ) ) {
            self::$instance = new Envira_Proofing_Metaboxes();
        }

        return self::$instance;

    }

}

// Load the metabox class.
$envira_proofing_metaboxes = Envira_Proofing_Metaboxes::get_instance();