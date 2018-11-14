<?php
function ups_include_scripts() {
    wp_enqueue_style('main-css',plugins_url( '/css/main.css', __FILE__ ));
    wp_enqueue_script( 'main-js', plugins_url( '/js/main.js', __FILE__ ));
}

add_action('wp_enqueue_scripts','ups_include_scripts');