<?php

function coolUnderCanvas_files() {
  wp_enqueue_script('main-js', get_theme_file_uri('/js/scripts-bundled.js'), NULL, '1.0', true);
  wp_enqueue_style('font-awsesome', '//use.fontawesome.com/releases/v5.0.13/css/all.css');
  wp_enqueue_style('google-fonts', '//fonts.googleapis.com/css?family=Montserrat');
  wp_enqueue_style('coolUnderCanvas_main_styles', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'coolUnderCanvas_files');
