<?php
function ups_include_scripts() {
    wp_enqueue_style('ups-main-css',plugins_url( '/css/main.css', __FILE__ ),array(),'0.'.time(),'all');
    wp_enqueue_script( 'ups-jquery-js', plugins_url( '/js/jquery.js', __FILE__ ),array('jquery'),'3.3.1','all');

    wp_enqueue_style('ups-jquery-ui-css',plugins_url( '/css/jquery-ui.css', __FILE__ ),array(),'0.1','all');
    wp_enqueue_script( 'ups-jquery-ui-js', plugins_url( '/js/jquery-ui.js', __FILE__ ),array('jquery'),'3.3.1','all');


    wp_enqueue_script( 'ups-main-js', plugins_url( '/js/main.js', __FILE__ ),array(),'0.'.time(),'all');

    wp_localize_script( 'ups-main-js', 'ajax_path',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

add_action('admin_enqueue_scripts','ups_include_scripts');