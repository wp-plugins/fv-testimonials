<?php
/*
Plugin Name: FV Testimonials
Plugin URI: http://www.foliovision.com
Description: Testimonial management system
Version: 0.9.4 Basic
Author: Foliovision s r.o.
Author URI: http://www.foliovision.com
*/ 

DEFINE( 'FP_TESTIMONAL_ROOT', dirname( __FILE__ ) . '/' );

require( FP_TESTIMONAL_ROOT . 'model/main-class.php' );

if( is_admin() ){
	add_action( 'admin_menu', array( &$objFPTMain, 'AddManagement' ) );
	add_action( 'admin_head', array( &$objFPTMain, 'OutputAdminHead' ) );
	register_activation_hook( __FILE__, array( &$objFPTMain, 'PluginActivate' ) );
}

add_filter( 'the_content', array( &$objFPTMain, 'OutputToContent' ) );
add_action( 'plugins_loaded', array( &$objFPTMain, 'SaveAndLoadData' ) );
if( $objFPTMain->bOutputCSS ) add_action( 'wp_head', array( &$objFPTMain, 'OutputUserCSS' ) );

?>