<?php

namespace Envira\Albums\Frontend;

use Envira\Albums\Frontend\Shortcode;
use Envira\Albums\Frontend\Posttype;
use Envira\Albums\Frontend\Standalone;
use Envira\Albums\Widget;
use Envira\Albums\Utils\Ajax;

class Frontend_Container{

    public function __construct(){

        $posttype   = new Posttype();
        $shortcode  = new Shortcode();
        $standalone = new Standalone();
		$ajax		= new Ajax();
		
        // Load the plugin widget.
        add_action( 'widgets_init', array( $this, 'widget' ) );

    }

    /**
     * Registers the Envira Gallery widget.
     *
     * @since 1.7.0
     */
    public function widget() {

        register_widget( 'Envira\Albums\Widgets\Widget' );

    }

}