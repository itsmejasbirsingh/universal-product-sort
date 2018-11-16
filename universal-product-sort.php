<?php
/*
    Plugin Name: Universal Product Sort
    Plugin URI: http://wordpress.com/
    Description: Universal Product Sort
    Author: Mindfire solutions
    Author URI: http://mindfiresolutions.com
    Version: 0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once('functions.php');
require_once('class-settings.php');
require_once('filters-listing.php');
require_once('filter-create.php');
require_once('filter-settings.php');
require_once('includes.php');