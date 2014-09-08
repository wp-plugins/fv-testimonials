<?php

//require_once( 'fv-testimonials-functions.php');
require_once( FVTESTIMONIALS_ROOT . '/model/image-class.php' );


add_action( 'admin_init', 'fv_testimonials_init');
add_action( 'wp_enqueue_scripts', 'fv_testimonials_scripts_method');
add_action( 'post_edit_form_tag', 'post_edit_form_tag');
add_action( 'admin_menu', 'fvt_testimonials_custom_submenu_page');
add_action( 'add_meta_boxes', 'fv_testimonials_custom_editing_metabox');
add_filter( 'manage_edit-testimonial_columns', 'fvtpro_edit_testimonial_columns' ) ;
add_action( 'manage_testimonial_posts_custom_column', 'fvtpro_manage_testimonial_columns', 10, 2 );
add_filter( 'post_updated_messages', 'fv_testimonials_updated_messages');
add_action( 'save_post', 'fv_testimonials_save_testimonial' );



function fv_testimonials_init(){
   add_action( 'wp_ajax_fv_testimonials_ajax_save_order', 'fv_testimonials_ajax_save_order' );
   add_action( 'wp_ajax_fv_testimonials_ajax_delete_image', 'fv_testimonials_ajax_delete_image' );
}

function fv_testimonials_scripts_method(){
   $myStyleUrl = plugins_url('style.css', __FILE__); // Respects SSL, Style.css is relative to the current file
   $myStyleFile = WP_PLUGIN_DIR . '/fv-testimonials/view/admin.css';
   if ( file_exists($myStyleFile) ) {
//      wp_register_style('myStyleSheets', $myStyleFile);
//      wp_enqueue_style( 'myStyleSheets');
   }
}

function post_edit_form_tag() {
   echo ' enctype="multipart/form-data"';
}

function fvt_testimonials_custom_submenu_page() {
   add_submenu_page( 'edit.php?post_type=testimonial', 'Options', 'Options', 'manage_options', 'fv-testimonial-options2', 'fvt_testimonials_submenu_page_callback_options2' ); 
   add_submenu_page( 'edit.php?post_type=testimonial', 'Order', 'Order', 'manage_categories', 'fv-testimonial-by-category', 'fvt_testimonials_submenu_page_callback_cats' );
}
function fvt_testimonials_submenu_page_callback_options2() {
   global $objFVTMain;
	require( FVTESTIMONIALS_ROOT . 'view/admin/options.php' );
}

 function fvt_testimonials_submenu_page_callback_cats() {
      global $objFVTMain;
      $aCategories = get_terms('testimonial_category', array('hide_empty'=>0));
   	require( FVTESTIMONIALS_ROOT . 'view/admin/by-category.php' );
   }
   
function fv_testimonials_custom_editing_metabox() {
   add_meta_box('fv_testimonials_custom_editing', 'Options', 'fv_testimonials_custom_editing_metabox_display', 'testimonial','normal', 'high');
}

function fv_testimonials_custom_editing_metabox_display() {
    global $post, $post_ID;
    global $objFVTMain;    
    $aImages = get_post_meta($post_ID, '_fvt_images',true);
    $strFeatured = get_post_meta($post_ID, '_fvt_featured', true);
    wp_nonce_field( 'fv-testimonials-formsubmit', 'fv-testimonials-formsubmit' ); 
    echo "<p><label><input type='checkbox' name='featured_testimonial' id='featured_testimonial'";
    if ( $strFeatured == '1' ) echo ' checked ';
    echo "> Make this testimonial featured</label></p>";
    $upload_dir = wp_upload_dir();
   
   if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strPath = str_replace($_SERVER['DOCUMENT_ROOT'],'',$upload_dir['basedir']).'/testimonials';
   else $strPath =  $objFVTMain->strImageRoot;
	
			
    echo '<table>';
    if ($aImages && $aImages[1])
       echo '<tr><td>Main image:</td><td><input type="file" name="fileImage" id="fileImage" /></td><td>Image Title: <input type="text" name="fileTitle" id="fileTitle" value="'.$aImages[1]['original']['name'].'"/></td><td>';
    else echo '<tr><td>Main image:</td><td><input type="file" name="fileImage" id="fileImage" /></td><td>Image Title: <input type="text" name="fileTitle" id="fileTitle" value=""/></td><td>';
    if ( $aImages && $aImages[1] ) echo '<span id="fvt-image-1">Image present: <img src="'.$strPath.$aImages[1]['small']['path'].'" style="max-width: 50px; max-height:50px;"/> <input type="button" value="Delete" class="fpt-del-button" onclick="FVTDeleteImage( '.$post_ID.', 1 )" /></span>';
    echo'</td></tr>';      
    if ($aImages && $aImages[2])
      echo '<tr><td>Second Image:</td><td><input type="file" name="fileImage2" id="fileImage2" /></td><td>Image Title: <input type="text" name="fileTitle2" id="fileTitle2" value="'.$aImages[2]['original']['name'].'" /></td><td>';
    else echo '<tr><td>Second Image:</td><td><input type="file" name="fileImage2" id="fileImage2" /></td><td>Image Title: <input type="text" name="fileTitle2" id="fileTitle2" value="" /></td><td>';
    if ( $aImages && $aImages[2] ) echo '<span id="fvt-image-2">Image present: <img src="'.$strPath.$aImages[2]['small']['path'].'" style="max-width: 50px; max-height:50px;"/><input type="button" value="Delete" class="fpt-del-button" onclick="FVTDeleteImage( '.$post_ID.', 2 )" /></span>';
    echo'</td></tr>';      
    echo '</table>';      
}

function fvtpro_edit_testimonial_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'testimonial_image' => __('Image'),
		'testimonial_id' => __('ID'),
		'title' => __( 'Title' ),
		'testimonial_category' => __( 'Categories' ),
		'testimonial_tag' => __( 'Tags' ),
		'testimonial_featured' => __( 'Featured' ),
		'date' => __( 'Date' )
	);

	return $columns;
}

function fvtpro_manage_testimonial_columns( $column, $post_id ) {
   global $objFVTMain;   
	global $post;
	switch( $column ) {
		case 'testimonial_category' :
				$tags = get_the_terms( $post_id, 'testimonial_category' );
			if ( !empty( $tags ) ) {
				$out = array();
				foreach ( $tags as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'testimonial_category' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'testimonial_category', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else _e( 'Uncategorized' );
			break;

		case 'testimonial_tag' :
			$tags = get_the_terms( $post_id, 'testimonial_tag' );
			if ( !empty( $tags ) ) {
				$out = array();
				foreach ( $tags as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'testimonial_tag' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'testimonial_tag', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else _e( 'No Tags' );
			break;
    case 'testimonial_image' :
	      $aImages = get_post_meta($post_id, '_fvt_images',true);
	      $upload_dir = wp_upload_dir();
         if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strPath = str_replace($_SERVER['DOCUMENT_ROOT'],'',$upload_dir['basedir']).'/testimonials';
         else $strPath =  $objFVTMain->strImageRoot;
			if ( !empty( $aImages[1] ) ) {
				$out = "<img src='".$strPath.$aImages[1]['thumbs']['path']."' style='max-width:50px; max-height:50px;' />";
			}
			else if ( !empty( $aImages[2] ) ) {
				$out = "<img src='".$strPath.$aImages[2]['thumbs']['path']."' style='max-width:50px; max-height:50px;' />";
			}
			else $out="";
			echo $out;
			break;
    case 'testimonial_featured' :
	      $strFeatured = get_post_meta($post_id, '_fvt_featured',true);
			if ( $strFeatured == '1' ) {
				$out = "Featured";
			}
			else {
				$out = "";
			}
			echo $out;
			break;
      case 'testimonial_id' :
	      echo $post_id;
			break;
		default :
			break;
	}
}

// to style the backend custom post type columns, they all had the same width
function custom_backend_columns() {
	   echo '<style type="text/css">
		   th#testimonial_id{width: 2em;}
		   th#testimonial_image{width: 50px;}
		   th#testimonial_featured{width: 5em;}
		   td.testimonial_image{text-align: center;}
		 </style>';
}

add_action('admin_head', 'custom_backend_columns');

function fv_testimonials_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['testimonial'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Testimonial updated. <a href="%s">View testimonial.</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Testimonial updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Testimonial restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Testimonial published. <a href="%s">View testimonial.</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Testimonial saved.'),
    8 => sprintf( __('Testimonial submitted. <a target="_blank" href="%s">Preview testimonial.</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview testimonial.</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Testimonial draft updated. <a target="_blank" href="%s">Preview testimonial</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}
function fv_testimonials_save_testimonial($post_id) {
   global $post;
   global $objFVTMain;
   $upload_dir = wp_upload_dir(); 
   if ($_POST['featured_testimonial']=='on') update_post_meta($post_id, '_fvt_featured', 1);
   else update_post_meta($post_id, '_fvt_featured', 0);

   if (!$post->post_title)
      $strPostSlug = $post->ID;
   else
      $strPostSlug = sanitize_title($post->post_title);

   $strPostSlug = preg_replace( '~[^a-zA-Z-_0-9]~', '', $strPostSlug );

   if ($_FILES['fileImage']['name']){
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true))
        if( !@is_dir( $upload_dir['basedir'].'/testimonials' ) ){ $objFVTMain->CreateImageFolders();}
      if ($_POST['fileTitle']) $strTitle = $_POST['fileTitle']; else $strTitle = '';
      $iNumber = 1;
      $strExtension = pathinfo( $_FILES['fileImage']['name'] );
      $strExtension = strtolower( $strExtension['extension'] );
      $strSavePath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.'/original/';
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strSavePath = $upload_dir['basedir'].'/testimonials/original/';
      $strPath = $strPostSlug."-".$iNumber.".".$strExtension;//$_FILES['fileImage']['name'];

      if( !move_uploaded_file( $_FILES['fileImage']['tmp_name'], $strSavePath.$strPath ) ) 
         throw new Exception( "Error while moving uploaded file to it's new location: '".$strSavePath.$strPath."'" );

      $objOriginal = new FPTImage2();
      $objOriginal->strPath = '/original/'.$strPath;
      $objOriginal->strRealPath = $strSavePath . $strPath;
      $aPathInfo = pathinfo( $objOriginal->strRealPath );
      $objOriginal->strName = substr( $aPathInfo['basename'], 0, strlen( $aPathInfo['basename'] ) - strlen( $aPathInfo['extension'] ) - 1 );
      $objOriginal->strOrigPath = $strSavePath . $strPath;
      $objOriginal->strExtension = $aPathInfo['extension'];
      $objOriginal->aImageInfo = getimagesize( $objOriginal->strRealPath );
      $objOriginal->strType = substr( $objOriginal->aImageInfo['mime'], 6 ); /**/

      if( !$objOriginal->CheckExtension( $strExtension ) ){
         $strExtension = $objOriginal->strType;
         if( 'jpeg' == strtolower( $strExtension ) ) $strExtension = 'jpg';
         $strSavePath .= $strPath;
         $strNewPath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.'/original/';
         if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strNewPath = $upload_dir['basedir'].'/testimonials/original/';
         $strNewName = $strPostSlug."-".$iNumber;//$_FILES['fileImage']['name'];//($strCustomName) ? $strCustomName : $objOriginal->PrepareTitle( $strTitle );
         //if( $iNumber != 1 ) $strNewName .=  '-' . $iNumber;
         $strNewName .= '.' . $strExtension;
         if( !rename( $strSavePath, $strNewPath.$strNewName ) ) throw new Exception( 'Error while renaming file with bad extension !' );

         $objOriginal->strPath = '/original/'.$strNewName;
         $objOriginal->strRealPath = $strNewPath . $strNewName;
         $aPathInfo = pathinfo( $objOriginal->strRealPath );
         $objOriginal->strName = substr( $aPathInfo['basename'], 0, strlen( $aPathInfo['basename'] ) - strlen( $aPathInfo['extension'] ) - 1 );
         $objOriginal->strOrigPath =  $strNewPath . $strNewName;
         $objOriginal->strExtension = $aPathInfo['extension'];
   
         $objOriginal->aImageInfo = getimagesize( $objOriginal->strRealPath );
         $objOriginal->strType = substr( $objOriginal->aImageInfo['mime'], 6 ); /**/
      }

      $objOriginal->iTestimonial = intval( $post_id );//$idTestimonial
      $objOriginal->strSize = 'original';
      $objOriginal->iWidth = intval( $objOriginal->aImageInfo[0] );
      $objOriginal->iHeight = intval( $objOriginal->aImageInfo[1] );
      $objOriginal->iNumber = intval( $iNumber );
      $objOriginal->strTitle = $strTitle;

      $objOriginal->aImages = $objOriginal->SaveNewEntry( $post_id );
      
      $aSizes = array('small'=>intval( get_option( 'FPT_width_small' ) ),'medium'=>intval( get_option( 'FPT_width_medium' ) ),'large'=>intval( get_option( 'FPT_width_large' ) ), 'thumbs'=>50);
      if (!$aSizes['small'])$aSizes['small'] = 150;
      if (!$aSizes['medium'])$aSizes['medium'] = 300;
      if (!$aSizes['large'])$aSizes['large'] = 1024;
      
      foreach( $aSizes as $strSize => $iWidth ){
         $objImage = $objOriginal->CreateResizedImage( $strSize, $iWidth, $iNumber, $post_id, $objFVTMain->iJPGQuality );
         unset( $objImage );
      }
      
      unset( $objOriginal );
   }  else{
      $aImages = get_post_meta($post_id, '_fvt_images', true);
      if ($_POST['fileTitle'] && $aImages && $aImages[1])
      if ($aImages[1]['original']['name'] != $_POST['fileTitle']){
         $aImages[1]['original']['name'] = $_POST['fileTitle'];
         update_post_meta( $post_id, '_fvt_images', $aImages );
      }
   } 
  
    if ($_FILES['fileImage2']['name']){
      $iNumber = 2;
      $strExtension = pathinfo( $_FILES['fileImage2']['name'] );
      $strExtension = strtolower( $strExtension['extension'] );
      $strSavePath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.'/original/';
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strSavePath = $upload_dir['basedir'].'/testimonials/original/';
      $strPath = $strPostSlug."-".$iNumber.".".$strExtension;//$_FILES['fileImage2']['name'];
      if ($_POST['fileTitle2']) $strTitle = $_POST['fileTitle2']; else $strTitle = '';
      if( !move_uploaded_file( $_FILES['fileImage2']['tmp_name'], $strSavePath.$strPath ) ) 
         throw new Exception( "Error while moving uploaded file to it's new location: '".$strSavePath.$strPath."'" );         
      $objOriginal = new FPTImage2();
      $objOriginal->strPath = '/original/'.$strPath;
      $objOriginal->strRealPath = $strSavePath . $strPath;
      $aPathInfo = pathinfo( $objOriginal->strRealPath );
      $objOriginal->strName = substr( $aPathInfo['basename'], 0, strlen( $aPathInfo['basename'] ) - strlen( $aPathInfo['extension'] ) - 1 );
      $objOriginal->strOrigPath = $strSavePath . $strPath;
      $objOriginal->strExtension = $aPathInfo['extension'];
      $objOriginal->aImageInfo = getimagesize( $objOriginal->strRealPath );
      $objOriginal->strType = substr( $objOriginal->aImageInfo['mime'], 6 ); /**/

      if( !$objOriginal->CheckExtension( $strExtension ) ){
         $strExtension = $objOriginal->strType;
         if( 'jpeg' == strtolower( $strExtension ) ) $strExtension = 'jpg';
         $strSavePath .= $strPath;
         $strNewPath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.'/original/';
         if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strNewPath = $upload_dir['basedir'].'/testimonials/original/';
         $strNewName = $strPostSlug."-".$iNumber;//$_FILES['fileImage2']['name'];//($strCustomName) ? $strCustomName : $objOriginal->PrepareTitle( $strTitle );
         //if( $iNumber != 1 ) $strNewName .=  '-' . $iNumber;
         $strNewName .= '.' . $strExtension;
         if( !rename( $strSavePath, $strNewPath.$strNewName ) ) throw new Exception( 'Error while renaming file with bad extension !' );

         $objOriginal->strPath = '/original/'.$strNewName;
         $objOriginal->strRealPath = $strNewPath . $strNewName;
         $aPathInfo = pathinfo( $objOriginal->strRealPath );
         $objOriginal->strName = substr( $aPathInfo['basename'], 0, strlen( $aPathInfo['basename'] ) - strlen( $aPathInfo['extension'] ) - 1 );
         $objOriginal->strOrigPath =  $strNewPath . $strNewName;
         $objOriginal->strExtension = $aPathInfo['extension'];
   
         $objOriginal->aImageInfo = getimagesize( $objOriginal->strRealPath );
         $objOriginal->strType = substr( $objOriginal->aImageInfo['mime'], 6 ); /**/
      }

      $objOriginal->iTestimonial = intval( $post_id );//$idTestimonial
      $objOriginal->strSize = 'original';
      $objOriginal->iWidth = intval( $objOriginal->aImageInfo[0] );
      $objOriginal->iHeight = intval( $objOriginal->aImageInfo[1] );
      $objOriginal->iNumber = intval( $iNumber );
      $objOriginal->strTitle = $strTitle;

      $objOriginal->aImages = $objOriginal->SaveNewEntry($post_id);

      $aSizes = array('small'=>intval( get_option( 'FPT_width_small' ) ),'medium'=>intval( get_option( 'FPT_width_medium' ) ),'large'=>intval( get_option( 'FPT_width_large' ) ), 'thumbs'=>50);
      if (!$aSizes['small'])$aSizes['small'] = 150;
      if (!$aSizes['medium'])$aSizes['medium'] = 300;
      if (!$aSizes['large'])$aSizes['large'] = 1024;
      foreach( $aSizes as $strSize => $iWidth ){
         $objImage = $objOriginal->CreateResizedImage( $strSize, $iWidth, $iNumber, $post_id, $objFVTMain->iJPGQuality );
         unset( $objImage );
      }
      unset( $objOriginal );
   }else{
      $aImages = get_post_meta($post_id, '_fvt_images', true);
      if ($_POST['fileTitle2'] && $aImages && $aImages[2])
      if ($aImages[2]['original']['name'] != $_POST['fileTitle2']){
         $aImages[2]['original']['name'] = $_POST['fileTitle2'];
         update_post_meta( $post_id, '_fvt_images', $aImages );
      }
   } 
}
function fv_testimonials_ajax_save_order(){
   $aOrder = get_option( '_fvt_order' );
   if ( ($_POST['id']) || ('0'===$_POST['id']) ){
      $id = intval($_POST['id']);
      $aOrder[$id] = $_POST['order'];
      update_option('_fvt_order', $aOrder);
  }
  die();   
}
function fv_testimonials_ajax_delete_image(){
   global $objFVTMain;
   $upload_dir = wp_upload_dir();
   if ($_POST['post_id']){
      $aImages = get_post_meta($_POST['post_id'], '_fvt_images', true);
      if ($_POST['image_id'] && $aImages){
         foreach ($aImages[$_POST['image_id']] as $im){
            if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) unlink ( $upload_dir['basedir'].'/testimonials/' . $im['path']);
            else unlink ( $_SERVER['DOCUMENT_ROOT'] . $objFVTMain->strImageRoot . $im['path']);

         }
 //           echo $_SERVER['DOCUMENT_ROOT'] . $objFVTMain->strImageRoot . $im['path'];
         unset($aImages[$_POST['image_id']]);
         update_post_meta( $_POST['post_id'], '_fvt_images', $aImages );
         echo 'Deleted';
         die();
      }
   }
}

?>