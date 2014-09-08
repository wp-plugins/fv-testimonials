<?php
/*
Plugin Name: FV Testimonials
Plugin URI: http://foliovision.com
Description: Management system for testimonials
Version: 1.12.1
Author: Foliovision
Author URI: http://foliovision.com
*/

  DEFINE( 'FVTESTIMONIALS_ROOT', dirname( __FILE__ ) . '/' );
  
  require( FVTESTIMONIALS_ROOT . 'model/fv-testimonials-class.php' );
  require_once( FVTESTIMONIALS_ROOT . 'controller/shortcodes.php' );
  require_once( FVTESTIMONIALS_ROOT . 'controller/backend.php' );
  require_once( FVTESTIMONIALS_ROOT . 'controller/conversions.php' );

  // register_activation_hook( __FILE__, 'fv_testimonials_activate' );
  add_action('admin_init', 'fv_testimonials_activate');

  function GetUrlTestimonials(){
      $strUrl = substr( FVTESTIMONIALS_ROOT, strlen( realpath( ABSPATH ) ) );
      if( DIRECTORY_SEPARATOR != '/' ) $strUrl = str_replace( DIRECTORY_SEPARATOR, '/', $strUrl );

      $strUrl = get_bloginfo( 'wpurl' ) . '/' . ltrim( $strUrl, '/' );

      // Do an SSL check - only works on Apache
      global $is_IIS;
      if( isset( $_SERVER['HTTPS'] ) && !empty( $_SERVER['HTTPS'] ) && !$is_IIS ) $strUrl = str_replace( 'http://', 'https://', $strUrl );

      return $strUrl;
  }

  $objFVTMain = new FV_Testimonials();
  
  add_action( 'plugins_loaded', array( &$objFVTMain, 'FVT_SaveAndLoadData' ) );
  

  wp_register_style('FVTestimonialsStyleSheets2', GetUrlTestimonials() .'view/user.css');
  //wp_enqueue_style( 'FVTestimonialsStyleSheets2');

  if(is_admin()) {
     wp_register_script( 'FVTestimonials', GetUrlTestimonials() .'js/fv-testimonials.js' );
     wp_enqueue_script( 'FVTestimonials' );
//      wp_register_style('FVTestimonialsStyleSheets', GetUrlTestimonials() .'view/admin.css');
//      wp_enqueue_style( 'FVTestimonialsStyleSheets');
      wp_register_style('FVTestimonialsStyleSheets3', GetUrlTestimonials() .'view/jquery-ui-tabs.css');
      wp_enqueue_style( 'FVTestimonialsStyleSheets3');
      
      wp_enqueue_script( 'jquery' );
      wp_enqueue_script( 'jquery-ui-core' );   
      wp_enqueue_script( 'jquery-ui-sortable' );
      wp_enqueue_script( 'jquery-ui-draggable' );
      wp_enqueue_script( 'jquery-ui-droppable' );
      wp_enqueue_script( 'jquery-ui-tabs' );
   }
   
   add_action('init', 'fvt_custom_init',2);
   add_action('admin_init', 'fvt_custom_init',1);
   function fvt_custom_init() 
   {
       $labels = array(
       'name' => _x('Testimonials', 'post type general name'),
       'singular_name' => _x('Testimonial', 'post type singular name'),
       'add_new' => _x('Add New', 'events'),
       'add_new_item' => __('Add Testimonial'),
       'edit_item' => __('Edit Testimonial'),
       'new_item' => __('New Testimonial'),
       'all_items' => __('All Testimonials'),
       'view_item' => __('View Testimonials'),
       'search_items' => __('Search Testimonials'),
       'not_found' =>  __('No testimonials found'),
       'not_found_in_trash' => __('No testimonials found in Trash'), 
       'parent_item_colon' => '',
       'menu_name' => 'Testimonials'
     );
     $args = array(
       'labels' => $labels,
       'public' => true,
       'publicly_queryable' => true,
       'show_ui' => true, 
       'show_in_menu' => true, 
       'query_var' => true,
       'rewrite' => true,
       'show_ui' => true,
       'capability_type' => 'post',
       'has_archive' => true, 
       'hierarchical' => false,
       'menu_position' => null,
       'supports' => array('title','editor','author','thumbnail','excerpt','custom-fields','sticky'),
       'taxonomies' => array('testimonial_category','testimonial_tag')//, 'post_tag'
     ); 
     register_post_type('testimonial',$args);
   }
   
   function fvt_filter_orderby( $title,$strOrder ) {
   	$orderby = " FIELD(`ID`, ".$strOrder->query_vars["customorder"].") ASC, `ID`";
   	return $orderby;
   }
   function version(){
      return '1.12';
   }
   
   function fv_testimonials_activate(){
      $strInstall = get_option( 'FPT_database', true );
      $strVersion = version();
      
      if ( ( floatval($strInstall) >=  1.0 ) ) return;
      //if ( defined('WP_ALLOW_MULTISITE') || constant ('WP_ALLOW_MULTISITE') === true ) return; // not for multisite, previous versions were not working there
      if( 0 == strcmp( $strInstall, $strVersion ) ) return;  // db version is the same
      if( !$strInstall ) return; // nothing has been installed before, there's nothing to convert
      // do the conversion here:
      if ( true ){
         delete_option('_fvt_converted_categories');
         delete_option('_fvt_converted_testimonials');
         delete_option('_fvt_order');
         $convertcategories = fv_testimonials_ajax_convert_cats();
         if (!$convertcategories){
            $plugin = plugin_basename( __FILE__ ); 
            deactivate_plugins( $plugin );
            wp_die( __('Categories failed to convert. Please make sure you have FV Testimonials PRO version at least 0.1 uploaded and activated before you activate this plugin.') );
         }
         $converttestimonials = fv_testimonials_ajax_convert_testimonials();
         if (!$convertcategories || !$converttestimonials) wp_die( __('The conversion of testimonials failed!') );
         update_option( 'FPT_database', $strVersion );
       $aTemplates = get_option( 'FPT_templates', true );  // get rid of that stupid double serialization!
         if (is_serialized( $aTemplates )) $aTemplates = unserialize($aTemplates);
         if (is_serialized( $aTemplates )) $aTemplates = unserialize($aTemplates);
         update_option( 'FPT_templates', $aTemplates );
        
         // moreover clean the database here
         //global $wpdb;
         //$wpdb->query("DROP TABLE wp_fpt_category,wp_fpt_images,wp_fpt_testimonials");
      

              
         
         $posts_converted = fv_testimonials_ajax_convert_shortcodes_posts();
         $theme_converted = fv_testimonials_ajax_convert_shortcodes_theme();
         //$db_converted    = fv_testimonials_ajax_convert_shortcodes_db();
         
         if ( !$posts_converted || !$theme_converted)  wp_die( __('The conversion of shortcodes for FV Testimonials failed. Please rewrite your shortcodes manually, check the Testimonials Options Page for detailed description.') );
      }
     
   }


?>