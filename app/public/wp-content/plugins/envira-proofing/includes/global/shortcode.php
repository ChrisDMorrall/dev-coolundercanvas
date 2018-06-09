<?php
/**
 * Shortcode class.
 *
 * @since 1.0.0
 *
 * @package Envira_Proofing
 * @author  Envira Team
 */
class Envira_Proofing_Shortcode {

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
     * Holds the order object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $order;

    /**
     * Holds success and error messages when saving/submitting orders
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $messages = array();

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Envira_Proofing::get_instance();

        // Register CSS
        wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/envira-proofing.css', $this->base->file ), array(), $this->base->version );

        // Register JS
        wp_register_script( $this->base->plugin_slug . '-cookie', plugins_url( 'assets/js/envira-js-cookie.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );
        wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/min/envira-proofing-min.js', $this->base->file ), array( 'jquery' ), $this->base->version, true );

        // Gallery
        add_filter( 'envira_gallery_pre_data', array( $this, 'define_image_orientation' ) );
        add_action( 'init', array( $this, 'maybe_save_submit_order' ) );
        add_action( 'envira_gallery_before_output', array( $this, 'output_css_js' ) );
        add_action( 'envira_link_before_output', array( $this, 'output_css_js' ) );

        add_filter( 'envira_gallery_output_before_container', array( $this, 'prepend_gallery' ), 10, 2 );
        add_filter( 'envira_gallery_output_after_link', array( $this, 'output_checkbox' ), 10, 5 );
        add_filter( 'envira_gallery_output_after_container', array( $this, 'append_gallery' ), 10, 2 );
        add_filter( 'envira_gallery_temp_output_after_container', array( $this, 'prepend_email_box' ), 10, 2 );
        add_filter( 'envira_gallery_output_extra_css', array( $this, 'extra_css' ), 10, 2 );

        // 1.7.0
        add_filter( 'envira_gallery_get_transient_markup', array( $this, 'disable_transient' ), 10, 2 );

    }

    public function disable_transient( $cached, $data ) {
        if ( isset($data['config']['proofing']) && $data['config']['proofing'] === 1 ) {
            return false;
        } else {
            return $cached;
        }
    }

    public function extra_css ( $css, $data ) {
        if ( empty( $this->order ) ) {
            $css .= ' envira-proofing-no-order ';
        } else {
            $css .= ' envira-proofing-yes-order ';
        }
        return $css;
        exit;
    }

    /**
    * Iterates through each image in the gallery, defining its orientation
    *
    * @since 1.0.2
    *
    * @param array  $data Gallery Data
    * @return array $data Gallery Data
    */
    public function define_image_orientation( $data ) {

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return $data;
        }

        // Check images exist
        if ( ! isset( $data['gallery'] ) || count( $data['gallery'] ) == 0 ) {
            return $data;
        }

        foreach ( $data['gallery'] as $index => $image ) {
            // Get image size and assume it's neither landscape or portrait
            $data['gallery'][ $index ]['landscape'] = false;
            $data['gallery'][ $index ]['portrait'] = false;
            $size = @getimagesize( $image['src'] );

            // Check we were able to get a size
            if ( ! is_array( $size ) ) {
                continue;
            }

            // If width > height = landscape
            if ( $size[0] > $size[1] ) {
                $data['gallery'][ $index ]['landscape'] = true;
            } else {
                $data['gallery'][ $index ]['portrait'] = true;
            }
        }

        return $data;

    }

    /**
     * Checks for the existence of Proofing $_POST data, and performs one of the following actions:
     * - save the order
     * - submit the order
     * - edit (unlock) the order
     * - delete the order
     *
     * @since 1.0
     */
    public function maybe_save_submit_order() {

        // Don't run on the admin side
        if ( is_admin() ) {
            return;
        }

        // Check if a gallery ID was specified
        if ( ! isset( $_POST['envira_gallery_id'] ) ) {
            return;
        }

        // Get gallery
        $gallery_id = absint( $_POST['envira_gallery_id'] );
        $data = Envira_Gallery::get_instance()->get_gallery( $gallery_id );

        // If no gallery found, bail
        if ( ! $data ) {
            return;
        }

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return false;
        }

        // Check if a nonce exists
        if ( ! isset( $_POST['envira_proofing_nonce'] ) ) {
            return;
        }

        // Check nonce is valid
        if ( ! wp_verify_nonce( $_POST['envira_proofing_nonce'], 'envira_proofing_nonce' ) ) {
            return;
        }


        // If multiple orders are enabled and an email address is submitted, store this as a cookie and either
        // create a new order or fetch an existing order
        $name   = ( isset( $_POST['envira_proofing_name'] ) ? $_POST['envira_proofing_name'] : '' );
        $email  = 'default';
        $notes  = ( isset( $_POST['envira_proofing_notes'] ) ? $_POST['envira_proofing_notes'] : '' );
        if ( $this->get_config( 'proofing_multiple_enabled', $data ) ) {
            if ( isset( $_POST['envira_proofing_email'] ) ) {
                // Sanitize the input
                $email = sanitize_text_field( $_POST['envira_proofing_email'] );

                // Set a cookie so we don't ask the user to complete this form again
                setcookie( 'envira_proofing_email', $email, time() + 2592000 );
            } else {
                // Try to get the email from the cookie
                $email = $_COOKIE['envira_proofing_email'];
            }
        }

        // Get order (creates an order stub if one doesn't exist)
        $instance = Envira_Proofing_Order::get_instance();
        $order = $instance->get_or_create_order( $gallery_id, $data, $email, $name, $notes );

        // If the existing order has already been submitted, don't do anything else!
        if ( isset( $order['submitted'] ) && $order['submitted'] ) {
            // If the gallery allows the user to edit their order, and the user submitted an edit (i.e. a request to unlock the order), do so now
            if ( $this->get_config( 'proofing_edit', $data ) && ( $_POST['envira_gallery_id'] == $gallery_id ) && isset( $_POST['envira_proofing_unlock'] ) ) {
                $instance->unlock_order( $gallery_id, $email );
                return;
            }

            // If the gallery allows the user to delete their order, and the user submitted a delete request, do so now
            if ( $this->get_config( 'proofing_delete', $data ) && ( $_POST['envira_gallery_id'] == $gallery_id ) && isset( $_POST['envira_proofing_delete'] ) ) {
                // Delete order
                $instance->delete_order( $gallery_id, $email );

                // Clear some cookies
                setcookie( 'envira_proofing_email', $email, time() - 3600 );
                return;
            }

            // Otherwise, just return
            return;
        }

        // Get order again
        $order = $instance->get_order( $gallery_id, $data, $email, $name, $notes );

        // Get some settings
        $quantity_enabled = $this->get_config( 'proofing_quantity_enabled', $data );
        $sizes_enabled = $this->get_config( 'proofing_size_enabled', $data );
        $sizes = $this->get_config( 'proofing_sizes', $data );

        // clear the order before adding to it
        $order['images'] = false;

        // The above method is no longer valid because we've switched to hidden fields that get injected with the image information...
        // ...you can't rely on $_POST['envira_proofing'] because that is a form submit of a possible ONE page in a pagination scenario

        $selected_images = ( isset($_POST['envira_proofing_selected_images']) ? json_decode( stripslashes( $_POST['envira_proofing_selected_images'] ) ) : false );
        $selected_images_ids = ( isset($_POST['envira_proofing_selected_images_ids']) ? json_decode( stripslashes( $_POST['envira_proofing_selected_images_ids'] ) ) : false );
        $selected_images_fields = ( isset($_POST['envira_proofing_selected_images_fields']) ? json_decode( stripslashes( $_POST['envira_proofing_selected_images_fields'] ) ) : false );

        if ( isset( $selected_images ) && isset( $selected_images_ids ) && is_array( $selected_images_ids ) ) {

            foreach ( $selected_images_ids as $image_id ) {

                $image_id = intval( $image_id );
                if ( !$image_id ) {
                    continue;
                }

                $order['images'][$image_id] = array();

                if ( !empty( $selected_images_fields ) ) {
                    foreach ( $selected_images_fields as $field_object ) {
                        if ( key((array)$field_object) == $image_id ) {
                            // ok, check for size AND quantity
                            if ( isset( $field_object->$image_id->size ) && isset( $field_object->$image_id->quantity ) ) {
                                $order['images'][$image_id][$field_object->$image_id->size] = intval( $field_object->$image_id->quantity );
                            } else if ( isset( $field_object->$image_id->quantity ) ) { // ok, check for just quantity
                                $order['images'][$image_id]['default'] = intval( $field_object->$image_id->quantity );
                            } else { // No quantity, no size - just select image(s)
                                $order['images'][$image_id]['default'] = 1;
                            }
                        }
                    }
                }

            }

        }

        // Check if the user is submitting the order or just saving it
        if ( isset( $_POST['envira_proofing_submit'] ) ) {
            $order['submitted'] = true;
        }

        // Save the order
        $instance->save_order( $data['id'], $order );

    }

    /**
    * Enqueue CSS and JS if Proofing is enabled
    *
    * @since 1.0.0
    *
    * @param array $data Gallery Data
    */
    public function output_css_js( $data ) {

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return false;
        }

        // Enqueue CSS + JS
        wp_enqueue_style( $this->base->plugin_slug . '-style' );
        wp_enqueue_script( $this->base->plugin_slug . '-cookie' );
        wp_enqueue_script( $this->base->plugin_slug . '-script' );

    }

    /**
    * Prepends a form tag to a gallery, when Proofing is enabled
    * Outputs any messages set in the class' $message object
    *
    * @since 1.0.0
    *
    * @param string $html HTML
    * @return string HTML
    */
    public function prepend_gallery( $html, $data ) {

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return $html;
        }

        // Get email address
        if ( ! $this->get_config( 'proofing_multiple_enabled', $data ) ) {
            $email = 'default';
        } else {
            if ( isset( $_POST['envira_proofing_email'] ) ) {
                // Get from input
                $email = sanitize_text_field( $_POST['envira_proofing_email'] );
            } elseif ( isset( $_COOKIE['envira_proofing_email'] ) ) {
                // Get from cookie
                $email = $_COOKIE['envira_proofing_email'];
            } else {
                // We don't have an email address
                $email = '';
            }
        }

        // If no email address, bail
        if ( empty( $email ) ) {
            return $html;
        }

        // If here, we know the customer's email address
        // Get order and store in class variable for later use
        $this->order = Envira_Proofing_Order::get_instance()->get_order( $data['id'], $data, $email );

        $prepend_html = '';

        // If order has been submitted, display a message confirming this
        if ( isset( $this->order['submitted'] ) && $this->order['submitted'] ) {
            $this->messages[] = array(
                'type'      => 'success',
                'message'   => $this->get_config( 'proofing_submitted_message', $data ),
            );
        }

        // Maybe output success/error messages, if any have been set
        if ( ! empty( $this->messages ) ) {

            $prepend_html .= '<div id="envira-proofing-messages">';

            foreach ( $this->messages as $message ) {
                $prepend_html .= '<div class="envira-proofing-message-' . $message['type'] . '">' . $message['message'] . '</div>';
            }

            $prepend_html .= '</div>';

        }

        // Open form
        $prepend_html .= '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post" class="envira-proofing-form" data-envira-id="' . $data['id'] . '">';

        // Return
        return $prepend_html . $html;

    }



    /**
     * Appends a checkbox and other fields (if applicable) to the end of each image
     *
     * @since 1.0.0
     *
     * @param string $image Image HTML
     * @return string Amended Image HTML
     */
    public function output_checkbox( $output, $id, $item, $data, $i ) {

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return $output;
        }

        // If multiple orders are enabled, and there's no order or email address, don't append checkboxes just yet
        if ( $this->get_config( 'proofing_multiple_enabled', $data ) ) {
            if ( empty( $this->order ) || empty( $this->order['email'] ) ) {
                return $output;
            }
        }

        // If the order has already been submitted, disable the form field inputs
        if ( isset( $this->order['submitted'] ) && $this->order['submitted'] ) {
            $disabled = ' disabled="disabled"';
        } else {
            $disabled = '';
        }

        // Append checkbox
        $padding = absint( $this->get_config( 'gutter', $data ) );
        $output .= '<input type="checkbox" id="envira_proofing_images_' . $id . '" name="envira_proofing[images][' . $id . ']" value="1" style="top:' . $padding . 'px;left:' . $padding . 'px" class="envira-proofing-select-image"' . ( isset( $this->order['images'][ $id ] ) ? ' checked' : '' ) . $disabled . ' />
        <label for="envira_proofing_images_' . $id . '" class="envira-proofing-select-image"></label>';

        // Append quantity / size options
        $quantity_enabled = $this->get_config( 'proofing_quantity_enabled', $data );
        $sizes_enabled = $this->get_config( 'proofing_size_enabled', $data );
        $sizes = $this->get_config( 'proofing_sizes', $data );

        $output .= '<div class="envira-proofing-fields">';

        if ( $quantity_enabled && $sizes_enabled && is_array( $sizes ) && count( $sizes ) > 0 ) {
            // Output sizes with quantity beside each
            foreach ( $sizes as $size ) {
                // If the size has an orientation defined, only show this size if the image matches the given orientation
                $orientation = ( isset( $size['orientation'] ) ? $size['orientation'] : '' );
                if ( empty( $orientation ) || ( $orientation == 'landscape' && $item['landscape'] ) || ( $orientation == 'portrait' && $item['portrait'] ) ) {
                    // No orientation defined for this size, or orientation is defined and matches the image
                    $output .= '<div class="envira-proofing-field">
                        <label for="envira_proofing_quantity_' . $id . '_' . $size['slug'] . '">' . esc_attr( $size['name'] ) . '</label>
                        <input type="number" name="envira_proofing[quantities][' . $id . '][' . $size['name'] . ']" id="envira_proofing_quantity_' . $id . '_' . $size['slug'] . '" min="0" value="' . ( isset( $this->order['images'][ $id ][ $size['name'] ] ) ? $this->order['images'][ $id ][ $size['name'] ] : 0 ) . '" class="envira-proofing-number"' . $disabled . ' />
                    </div>';
                }
            }
        } elseif ( $quantity_enabled && ! $sizes_enabled ) {
            // Output quantity field only
            $output .= '<div class="envira-proofing-field">
                <label for="envira_proofing_quantity_' . $id . '">' . __( 'Quantity', 'envira-proofing' ) . '</label>
                <input type="number" name="envira_proofing[quantities][' . $id . ']" id="envira_proofing_quantity_' . $id . '" min="0" value="' . esc_attr( ( isset( $this->order[ $id ]['default'] ) ? $this->order['images'][ $id ]['default'] : 1 ) ) . '" class="envira-proofing-number"' . $disabled . ' />
            </div>';

        } elseif ( ! $quantity_enabled && $sizes_enabled && is_array( $sizes ) && count( $sizes ) > 0 ) {
            // Output sizes only
            foreach ( $sizes as $size ) {
                // If the size has an orientation defined, only show this size if the image matches the given orientation
                $orientation = ( isset( $size['orientation'] ) ? $size['orientation'] : '' );
                if ( empty( $orientation ) || ( $orientation == 'landscape' && $item['landscape'] ) || ( $orientation == 'portrait' && $item['portrait'] ) ) {
                    // No orientation defined for this size, or orientation is defined and matches the image
                    $output .= '<div class="envira-proofing-field">
                        <label for="envira_proofing_quantity_' . $id . '_' . $size['slug'] . '">' . esc_attr( $size['name'] ) . '</label>
                        <input type="checkbox" name="envira_proofing[quantities][' . $id . '][' . $size['name'] . ']" id="envira_proofing_quantity_' . $id . '_' . $size['slug'] . '" value="1" class="envira-proofing-checkbox" ' . ( isset( $this->order['images'][ $id ][ $size['name'] ] ) ? ' checked' : '' ) . $disabled . ' />
                    </div>';
                }
            }
        }

        // Close .envira-proofing-fields
        $output .= '</div>';

        return $output;

    }

    /**
    * Appends a closing form tag to a gallery, when Proofing is enabled
    *
    * @since 1.0.0
    *
    * @param string $html HTML
    * @return string HTML
    */
    public function append_gallery( $html, $data ) {

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return $html;
        }

        // Did the user choose to have this below the gallery images?
        if ( $this->get_config( 'proofing_email_box_position', $data ) != 'below' ) {
            return $html;
        }

        // If multiple orders are enabled and no order object is set, we need to ask the user for their email
        if ( $this->get_config( 'proofing_multiple_enabled', $data ) ) {
            if ( empty( $this->order ) ) {
                // If the gallery is set to hidden until the email address is entered, clear the existing HTML
                // (i.e. the Gallery Images output)
                if ( $this->get_config( 'proofing_hide_gallery', $data ) ) {
                    $html = '';
                }

                // Open form
                $html .= '<div><form action="' . esc_url ( $_SERVER['REQUEST_URI'] ) . '" method="post" class="envira-proofing-form" data-envira-id="' . $data['id'] . '">';
                $html .= '<p>' . $this->get_config( 'proofing_multiple_label', $data ) . '</p>';

                // If name enabled, display field now
                if ( $this->get_config( 'proofing_name_enabled', $data ) ) {
                    $html .= '<input type="text" name="envira_proofing_name" value="" placeholder="' . __( 'Name', 'envira-proofing' ) . '">';
                }

                $html .= '<input type="email" name="envira_proofing_email" value="" placeholder="' . __( 'Email Address', 'envira-proofing' ) . '">';
                $html .= '<input type="submit" name="envira_proofing_save" value="' . esc_attr( $this->get_config( 'proofing_save_button_label', $data ) ) . '" />';
                $html .= '<input type="hidden" name="envira_gallery_id" value="' . $data['id'] . '" />
                        ' . wp_nonce_field( 'envira_proofing_nonce', 'envira_proofing_nonce', true, false ) . '
                        </form>';

                // Return, as we don't want to output anything else
                return $html;
            }

            // If here, multiple orders are supported and the user's submitted their email
            $html .= '<input type="hidden" name="envira_proofing_name" value="' . esc_attr( ( ( isset( $this->order['name'] ) && $this->order['name'] !== false ) ? $this->order['name'] : '' ) ) . '" />';
            $html .= '<input type="hidden" name="envira_proofing_email" value="' . esc_attr( ( ( isset( $this->order['email'] ) && $this->order['email'] !== false ) ? $this->order['email'] : '' ) ) . '" />';
        }

        // Add notes field
        $html .= '<textarea name="envira_proofing_notes" placeholder="' . esc_attr( $this->get_config( 'proofing_notes_placeholder_text', $data ) ) . '">' . ( isset( $this->order['notes'] ) ? $this->order['notes'] : '' ) . '</textarea>';

        // Add nonce field
        $html .= wp_nonce_field( 'envira_proofing_add_to_cart', 'envira_proofing_add_to_cart', true, false );

        // Add selected image id field
        $html .= '<input type="hidden" id="envira_proofing_selected_images" name="envira_proofing_selected_images" value="">';
        $html .= '<input type="hidden" id="envira_proofing_selected_images_ids" name="envira_proofing_selected_images_ids" value="">';
        $html .= '<input type="hidden" id="envira_proofing_selected_images_fields" name="envira_proofing_selected_images_fields" value="">';

        // Show buttons based on whether the order has been submitted or not
        if ( ! isset( $this->order['submitted'] ) || ! $this->order['submitted'] ) {
            // Save + Submit Buttons
            $html .= '<input type="submit" name="envira_proofing_save" value="' . esc_attr( $this->get_config( 'proofing_save_button_label', $data ) ) . '" />';
            $html .= '<input type="submit" name="envira_proofing_submit" value="' . esc_attr( $this->get_config( 'proofing_submit_button_label', $data ) ) . '" />';
        } else {
            // Show button as "Save Notes"
            $html .= '<input type="submit" name="envira_proofing_save" value="' . esc_attr( $this->get_config( 'proofing_save_notes_button_label', $data ) ) . '" />';

            // If the option to edit or delete the order are enabled, output those options too
            if ( $this->get_config( 'proofing_edit', $data ) ) {
                $html .= '<input type="submit" name="envira_proofing_unlock" value="' . esc_attr( $this->get_config( 'proofing_edit_button_label', $data ) ) . '" />';
            }
            if ( $this->get_config( 'proofing_delete', $data ) ) {
                $html .= '<input type="submit" name="envira_proofing_delete" value="' . esc_attr( $this->get_config( 'proofing_delete_button_label', $data ) ) . '" />';
            }
        }

        $html .= '<input type="hidden" name="envira_gallery_id" value="' . $data['id'] . '" />
            ' . wp_nonce_field( 'envira_proofing_nonce', 'envira_proofing_nonce', true, false ) . '
        </form>';

        // Summary Box
        $html .= '<div class="envira-proofing-summary-box" data-envira-id="' . $data['id'] . '">
            <div class="envira-proofing-summary-box-inner">
                <div class="images"><div class="images-inner"></div></div>';

        // Hide buttons if order submitted
        if ( ! isset( $this->order['submitted'] ) || ! $this->order['submitted'] ) {
            $html .= '<div class="buttons">
                    <button name="envira_proofing_save">' . esc_attr( $this->get_config( 'proofing_save_button_label', $data ) ) . '</button>
                    <button name="envira_proofing_submit">' . esc_attr( $this->get_config( 'proofing_submit_button_label', $data ) ) . '</button>
                </div>';
        }

        $html .= '
            </div>
        </div>';

        return $html;

    }

    /**
    * Prepends an email box to a gallery, when Proofing is enabled
    *
    * @since 1.0.0
    *
    * @param string $html HTML
    * @return string HTML
    */
    public function prepend_email_box( $html, $data ) {

        // Check if Proofing is enabled
        if ( ! $this->get_config( 'proofing', $data ) ) {
            return $html;
        }

        // Did the user choose to have this below the gallery images?
        if ( $this->get_config( 'proofing_email_box_position', $data ) === 'below' ) {
            return $html;
        }

        $temp_html = '';

        // If multiple orders are enabled and no order object is set, we need to ask the user for their email
        if ( $this->get_config( 'proofing_multiple_enabled', $data ) ) {
            if ( empty( $this->order ) ) {
                // If the gallery is set to hidden until the email address is entered, clear the existing HTML
                // (i.e. the Gallery Images output)

                // if ( $this->get_config( 'proofing_hide_gallery', $data ) ) {
                //     $temp_html = '';
                // }

                // Open form
                $temp_html .= '<div><form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post" class="envira-proofing-form" data-envira-id="' . $data['id'] . '">';
                $temp_html .= '<p>' . $this->get_config( 'proofing_multiple_label', $data ) . '</p>';

                // If name enabled, display field now
                if ( $this->get_config( 'proofing_name_enabled', $data ) ) {
                    $temp_html .= '<input type="text" name="envira_proofing_name" value="" placeholder="' . __( 'Name', 'envira-proofing' ) . '">';
                }

                $temp_html .= '<input type="email" name="envira_proofing_email" value="" placeholder="' . __( 'Email Address', 'envira-proofing' ) . '">';
                $temp_html .= '<input type="submit" name="envira_proofing_save" value="' . esc_attr( $this->get_config( 'proofing_save_button_label', $data ) ) . '" />';
                $temp_html .= '<input type="hidden" name="envira_gallery_id" value="' . $data['id'] . '" />
                        ' . wp_nonce_field( 'envira_proofing_nonce', 'envira_proofing_nonce', true, false ) . '
                        </form>';

                if ( $this->get_config( 'proofing_hide_gallery', $data ) ) {
                    // Return, as we don't want to output anything else
                    return $temp_html;
                } else {
                    return $temp_html . $html;
                }
            }

            // If here, multiple orders are supported and the user's submitted their email
            $temp_html .= '<input type="hidden" name="envira_proofing_name" value="' . esc_attr( ( ( isset( $this->order['name'] ) && $this->order['name'] !== false ) ? $this->order['name'] : '' ) ) . '" />';
            $temp_html .= '<input type="hidden" name="envira_proofing_email" value="' . esc_attr( ( ( isset( $this->order['email'] ) && $this->order['email'] !== false ) ? $this->order['email'] : '' ) ) . '" />';

        }

        // Add notes field
        $temp_html .= '<textarea name="envira_proofing_notes" placeholder="' . esc_attr( $this->get_config( 'proofing_notes_placeholder_text', $data ) ) . '">' . ( isset( $this->order['notes'] ) ? $this->order['notes'] : '' ) . '</textarea>';

        // Add nonce field
        $temp_html .= wp_nonce_field( 'envira_proofing_add_to_cart', 'envira_proofing_add_to_cart', true, false );

        // Add selected image id field
        $temp_html .= '<input type="hidden" id="envira_proofing_selected_images" name="envira_proofing_selected_images" value="">';
        $temp_html .= '<input type="hidden" id="envira_proofing_selected_images_ids" name="envira_proofing_selected_images_ids" value="">';
        $temp_html .= '<input type="hidden" id="envira_proofing_selected_images_fields" name="envira_proofing_selected_images_fields" value="">';

        // Show buttons based on whether the order has been submitted or not
        if ( ! isset( $this->order['submitted'] ) || ! $this->order['submitted'] ) {
            // Save + Submit Buttons
            $temp_html .= '<input type="submit" name="envira_proofing_save" value="' . esc_attr( $this->get_config( 'proofing_save_button_label', $data ) ) . '" />';
            $temp_html .= '<input type="submit" name="envira_proofing_submit" value="' . esc_attr( $this->get_config( 'proofing_submit_button_label', $data ) ) . '" />';
        } else {
            // Show button as "Save Notes"
            $temp_html .= '<input type="submit" name="envira_proofing_save" value="' . esc_attr( $this->get_config( 'proofing_save_notes_button_label', $data ) ) . '" />';

            // If the option to edit or delete the order are enabled, output those options too
            if ( $this->get_config( 'proofing_edit', $data ) ) {
                $temp_html .= '<input type="submit" name="envira_proofing_unlock" value="' . esc_attr( $this->get_config( 'proofing_edit_button_label', $data ) ) . '" />';
            }
            if ( $this->get_config( 'proofing_delete', $data ) ) {
                $temp_html .= '<input type="submit" name="envira_proofing_delete" value="' . esc_attr( $this->get_config( 'proofing_delete_button_label', $data ) ) . '" />';
            }
        }

        $temp_html .= '<input type="hidden" name="envira_gallery_id" value="' . $data['id'] . '" />
            ' . wp_nonce_field( 'envira_proofing_nonce', 'envira_proofing_nonce', true, false ) . '
        </form>';

        // Summary Box
        $temp_html .= '<div class="envira-proofing-summary-box" data-envira-id="' . $data['id'] . '">
            <div class="envira-proofing-summary-box-inner">
                <div class="images"><div class="images-inner"></div></div>';

        // Hide buttons if order submitted
        if ( ! isset( $this->order['submitted'] ) || ! $this->order['submitted'] ) {
            $temp_html .= '<div class="buttons">
                    <button name="envira_proofing_save">' . esc_attr( $this->get_config( 'proofing_save_button_label', $data ) ) . '</button>
                    <button name="envira_proofing_submit">' . esc_attr( $this->get_config( 'proofing_submit_button_label', $data ) ) . '</button>
                </div>';
        }

        $temp_html .= '
            </div>
        </div>';

        return $temp_html . $html;

    }


    /**
     * Helper method for retrieving gallery config values.
     *
     * @since 1.0.0
     *
     * @param string $key The config key to retrieve.
     * @param array $data The gallery data to use for retrieval.
     * @return string     Key value on success, default if not set.
     */
    public function get_config( $key, $data ) {

        $instance = Envira_Gallery_Shortcode::get_instance();
        return $instance->get_config( $key, $data );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Proofing_Shortcode object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Proofing_Shortcode ) ) {
            self::$instance = new Envira_Proofing_Shortcode();
        }

        return self::$instance;

    }

}

// Load the shortcode class.
$envira_proofing_shortcode = Envira_Proofing_Shortcode::get_instance();