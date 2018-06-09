<?php
/**
 * AJAX class.
 *
 * @since 1.0.0
 *
 * @package Envira_Watermarking
 * @author  Envira Team
 */
class Envira_Watermarking_Ajax {

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

        add_action( 'envira_gallery_ajax_load_image', array( $this, 'add_watermark' ), 10, 2 );

    }

    /**
     * Overlay a watermark, if specified, to the uploaded image
     *
     * @param int $attachment_id	Attachment ID
     * @param int $gallery_id 		Gallery ID
     *
     * @since 1.0.0
     */ 
    public function add_watermark( $attachment_id, $gallery_id ) {

    	// Get instance
    	$instance = Envira_Gallery_Shortcode::get_instance();

    	// Get gallery
    	$data = get_post_meta( $gallery_id, '_eg_gallery_data', true );

    	// Check if watermarking is enabled
    	if ( ! $instance->get_config( 'watermarking', $data ) ) {
    		return;
    	}
        $watermarking_image_id = $instance->get_config( 'watermarking_image_id', $data );
		if ( empty( $watermarking_image_id ) ) {
    		return;
    	}

    	// Check if this attachment has already been watermarked
    	// If so, skip it
    	$already_watermarked = get_post_meta( $attachment_id, '_envira_watermarking_applied', true );
    	if ( $already_watermarked ) {
    		return;
    	}

        // Watermark image using GD or Imagick
        if ( $this->has_gd_extension() ) {
            $file = $this->watermark_gd( $attachment_id, $gallery_id, $data );

            $fullsizepath = get_attached_file( $attachment_id );
            wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $fullsizepath ) );

        } else if ( $this->has_imagick_extension() ) {
            $this->watermark_imagick( $attachment_id, $gallery_id, $data );

            $fullsizepath = get_attached_file( $attachment_id );
            wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $fullsizepath ) );
            
        } else {
            return;
        }

        

    }

    /**
     * Flag to determine if the GD library has been compiled.
     *
     * @since 1.0.0
     *
     * @return bool True if has proper extension, false otherwise.
     */
    public function has_gd_extension() {

        return extension_loaded( 'gd' ) && function_exists( 'gd_info' );

    }

    /**
     * Flag to determine if the Imagick library has been compiled.
     *
     * @since 1.0.0
     *
     * @return bool True if has proper extension, false otherwise.
     */
    public function has_imagick_extension() {

        return extension_loaded( 'imagick' );

    }

    /**
    * Watermark image using GD
    *
    * @since 1.0
    */
    public function watermark_gd( $attachment_id, $gallery_id, $data ) {

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        // Get image and watermark image
        $image_path = get_attached_file( $attachment_id );
        $watermark_path = get_attached_file( $instance->get_config( 'watermarking_image_id', $data ) );

        // Make thumbnails and other intermediate sizes.
        // $_wp_additional_image_sizes = wp_get_additional_image_sizes();  

        // Get images
        $image = @imagecreatefromstring( file_get_contents( $image_path ) );
        $watermark = @imagecreatefromstring( file_get_contents( $watermark_path ) );

        // Get widths and heights for the image and watermark
        $image_width = imagesx( $image );
        $image_height = imagesy( $image );
        $watermark_width = imagesx( $watermark );
        $watermark_height = imagesy( $watermark );

        // Get metadata (MIME type) for the image and watermark
        $image_meta = getimagesize( $image_path );
        $watermark_meta = getimagesize( $watermark_path );

        // If the watermark exceeds the width or height of the image, scale the watermark down
        $scale_factor = 0.5;
        if ( $watermark_width > $image_width || $watermark_height > $image_height ) {
            // Calculate new watermark size
            $new_watermark_width = $watermark_width * $scale_factor;
            $new_watermark_height = $watermark_height * $scale_factor;

            // Create resized watermark image
            $watermark = imagecreatetruecolor( $new_watermark_width, $new_watermark_height );
            imagecolortransparent($watermark, imagecolorallocatealpha($watermark, 0, 0, 0, 127));
            imagealphablending( $watermark, false );
            imagesavealpha( $watermark, true );
            imagecopyresampled( $watermark, @imagecreatefromstring( file_get_contents( $watermark_path ) ), 0, 0, 0, 0, $new_watermark_width, $new_watermark_height, $watermark_width, $watermark_height);

            // From here on out, the "new" values are the actual width/height values to consider
            $watermark_width = $new_watermark_width;
            $watermark_height = $new_watermark_height;
        }

        // Enable imagealphablending for correct PNG rendering
        imagealphablending( $image, true );
        imagealphablending( $watermark, true );

        // Calculate position of watermark based on settings
        $watermark_position = $instance->get_config( 'watermarking_position', $data );
        $watermark_margin = $instance->get_config( 'watermarking_margin', $data );
        $position = array(
            'x' => 0,
            'y' => 0,
        );
        switch ( $watermark_position ) {
            case 'top-left':
                $position = array(
                    'x' => ( 0 + $watermark_margin ),
                    'y' => ( 0 + $watermark_margin ),
                );
                break;
            case 'top-right':
                $position = array(
                    'x' => ( ( $image_width - $watermark_width ) - $watermark_margin ),
                    'y' => ( 0 + $watermark_margin ),
                );
                break;
            case 'center':
                $position = array(
                    'x' => ( ( $image_width - $watermark_width ) / 2 ),
                    'y' => ( ( $image_height - $watermark_height ) / 2 ),
                );
                break;
            case 'bottom-left':
                $position = array(
                    'x' => ( 0 + $watermark_margin ),
                    'y' => ( ( $image_height - $watermark_height ) - $watermark_margin ),
                );
                break;
            case 'bottom-right':
                $position = array(
                    'x' => ( ( $image_width - $watermark_width ) - $watermark_margin ),
                    'y' => ( ( $image_height - $watermark_height ) - $watermark_margin ),
                );
                break;
            default:
                // Allow devs to run their own calculations here
                $position = apply_filters( 'envira_watermarking_add_watermark_position', $position, $attachment_id, $gallery_id, $data );
                break;
        }

        // Copy the entire $watermark image onto a matching sized portion of the $image
        imagecopy( $image, $watermark, $position['x'], $position['y'], 0, 0, $watermark_width, $watermark_height );
        
        // Get the MIME type of the original image, so we know which image function to call when saving
        switch ( $image_meta['mime'] ) {
            /**
            * JPEG
            */
            case 'image/jpeg':
            case 'image/jpg':
                // Save image as JPEG
                imagejpeg( $image, $image_path );
                break;

            /**
            * PNG
            */
            case 'image/png':
                // Save image as PNG
                imagepng( $image, $image_path );
                break;

            /**
            * GIF
            */
            case 'image/gif':
                // Save image as GIF
                imagegif( $image, $image_path );
                break;  
        }
        
        // Free up resources
        imagedestroy( $image );
        imagedestroy( $watermark );

        // Mark attachment as watermarked, so we don't do this again
        update_post_meta( $attachment_id, '_envira_watermarking_applied', true );

        return $image;

    }

    /**
    * Watermark image using Imagick
    *
    * @since 1.0
    */
    public function watermark_imagick( $attachment_id, $gallery_id, $data ) {

        // Get instance
        $instance = Envira_Gallery_Shortcode::get_instance();

        // Get image and watermark image
        $image_path = get_attached_file( $attachment_id );
        $watermark_path = get_attached_file( $instance->get_config( 'watermarking_image_id', $data ) );

        // Get images
        $image = new Imagick( $image_path );
        $watermark = new Imagick( $watermark_path );

        // Get widths and heights for the image and watermark
        $image_size = $image->getImageGeometry();
        $image_width = $image_size['width'];
        $image_height = $image_size['height'];

        $watermark_size = $image->getImageGeometry();
        $watermark_width = $watermark_size['width'];
        $watermark_height = $watermark_size['height'];

        // Get metadata (MIME type) for the image and watermark
        $image_meta = $image->getFormat();
        $watermark_meta = $watermark->getFormat();;

        // If the watermark exceeds the width or height of the image, scale the watermark down
        $scale_factor = 0.5;
        if ( $watermark_width > $image_width || $watermark_height > $image_height ) {
            // Calculate new watermark size
            $new_watermark_width = $watermark_width * $scale_factor;
            $new_watermark_height = $watermark_height * $scale_factor;

            // Create resized watermark image
            $watermark->scaleImage( $new_watermark_width, $new_watermark_height );
        }

        // Calculate position of watermark based on settings
        $watermark_position = $instance->get_config( 'watermarking_position', $data );
        $watermark_margin = $instance->get_config( 'watermarking_margin', $data );
        $position = array(
            'x' => 0,
            'y' => 0,
        );
        switch ( $watermark_position ) {
            case 'top-left':
                $position = array(
                    'x' => ( 0 + $watermark_margin ),
                    'y' => ( 0 + $watermark_margin ),
                );
                break;
            case 'top-right':
                $position = array(
                    'x' => ( ( $image_width - $watermark_width ) - $watermark_margin ),
                    'y' => ( 0 + $watermark_margin ),
                );
                break;
            case 'center':
                $position = array(
                    'x' => ( ( $image_width - $watermark_width ) / 2 ),
                    'y' => ( ( $image_height - $watermark_height ) / 2 ),
                );
                break;
            case 'bottom-left':
                $position = array(
                    'x' => ( 0 + $watermark_margin ),
                    'y' => ( ( $image_height - $watermark_height ) - $watermark_margin ),
                );
                break;
            case 'bottom-right':
                $position = array(
                    'x' => ( ( $image_width - $watermark_width ) - $watermark_margin ),
                    'y' => ( ( $image_height - $watermark_height ) - $watermark_margin ),
                );
                break;
            default:
                // Allow devs to run their own calculations here
                $position = apply_filters( 'envira_watermarking_add_watermark_position', $position, $attachment_id, $gallery_id, $data );
                break;
        }

        // Copy the entire $watermark image onto a matching sized portion of the $image
        $image->compositeImage( $watermark, Imagick::COMPOSITE_MATHEMATICS, $position['x'], $position['y'] );

        // Save
        $image->writeImage( $image_path );
        
        // Free up resources
        unset( $image );
        unset( $watermark );

        // Mark attachment as watermarked, so we don't do this again
        update_post_meta( $attachment_id, '_envira_watermarking_applied', true );

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The Envira_Watermarking_Ajax object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Watermarking_Ajax ) ) {
            self::$instance = new Envira_Watermarking_Ajax();
        }

        return self::$instance;

    }

}

// Load the AJAX class.
$envira_watermarking_ajax = Envira_Watermarking_Ajax::get_instance();
