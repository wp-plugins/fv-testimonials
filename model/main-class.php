<?php
/**
 * File with Main class for Foliopress Testimonials.
 *
 * @author Foliovision <programming@foliovision.com>
 * @package Foliopress
 * @subpackage Testimonials
 */

require( FP_TESTIMONAL_ROOT . 'model/image-class.php' );
require( FP_TESTIMONAL_ROOT . 'model/testimonial-class.php' );

/**
 * Main class of Foliopress Testimonials plugin. This class handles all the interaction with Wordpress.
 */
class FPTMain {

   public $strUrl = '';
   public $iWidthLarge = 0;
   public $iWidthMedium = 0;
   public $iWidthSmall = 0;
   public $iWidthThumbs = 50;
   public $strImageRoot = '';
   public $iJPGQuality = 90;
   public $bOutputCSS = true;
   public $aTemplates = array();

   public $strDatabaseVersion = '';

   public $aSizes = array();
   public $aAllSizes = array( 'original', 'large', 'medium', 'small', 'thumbs' );
   public $strShowFull = 'medium';
   public $strShow = 'small';

   public $strMessage = '';

   const OPTION_URL = 'FPT_url';
   const OPTION_LARGE = 'FPT_width_large';
   const OPTION_MEDIUM = 'FPT_width_medium';
   const OPTION_SMALL = 'FPT_width_small';
   const OPTION_IMAGES = 'FPT_images_root';
   const OPTION_JPG = 'FPT_jpg_quality';
   const OPTION_CSS = 'FPT_output_css';
   const OPTION_TEMPLATES = 'FPT_templates';

   const OPTION_DATABASE = 'FPT_database';

   public function __construct(){
      $this->strUrl = strval( get_option( self::OPTION_URL ) );
      $this->iWidthLarge = intval( get_option( self::OPTION_LARGE ) );
      $this->iWidthMedium = intval( get_option( self::OPTION_MEDIUM ) );
      $this->iWidthSmall = intval( get_option( self::OPTION_SMALL ) );
      $this->strImageRoot = strval( get_option( self::OPTION_IMAGES ) );
      $this->iJPGQuality = intval( get_option( self::OPTION_JPG ) );
      $this->bOutputCSS = ('no' == strval( get_option( self::OPTION_CSS ) )) ? false : true;
      $this->aTemplates = @unserialize( get_option( self::OPTION_TEMPLATES ) );
      if( !is_array( $this->aTemplates ) ) $this->aTemplates = array();

      $this->strDatabaseVersion = strval( get_option( self::OPTION_DATABASE ) );

      $this->aSizes['large'] = $this->iWidthLarge;
      $this->aSizes['medium'] = $this->iWidthMedium;
      $this->aSizes['small'] = $this->iWidthSmall;
      $this->aSizes['thumbs'] = $this->iWidthThumbs;

      $this->CheckOptions();
   }

   /**
    * Checks if values in public properties are correct, if not, corrects them to default values.
    *
    * @access public
    */
   public function CheckOptions(){
      if( !$this->iWidthLarge ) $this->iWidthLarge = 1024;
      if( !$this->iWidthMedium ) $this->iWidthMedium = 300;
      if( !$this->iWidthSmall ) $this->iWidthSmall = 150;
      if( !$this->iWidthThumbs ) $this->iWidthThumbs = 50;

      if( !$this->strImageRoot ) $this->strImageRoot = '/';
      if( 0 >= $this->iJPGQuality || 100 < $this->iJPGQuality ) $this->iJPGQuality = 90;
   }

   /**
    * Updates options into database using WP functions.
    *
    * @access private
    */
   private function UpdateOption( $strKey, $strValue ){
      if( false === get_option( $strKey ) ) add_option( $strKey, $strValue );
      else update_option( $strKey, $strValue );
   }
   
   public function GetTemplateIndex( $idTemp ){
      for( $i = count( $this->aTemplates );  $i >= 0;  $i-- )
         if( $idTemp == $this->aTemplates[$i]['id'] ) return $i;

      throw new Exception( "Invalid template id !" );
   }

   /**
    * Returns correct URL to this plugin directory.
    *
    * @return string URL to this plugin
    */
   public static function GetUrl(){
      $strUrl = substr( FP_TESTIMONAL_ROOT, strlen( realpath( ABSPATH ) ) );
      if( DIRECTORY_SEPARATOR != '/' ) $strUrl = str_replace( DIRECTORY_SEPARATOR, '/', $strUrl );

      $strUrl = get_bloginfo( 'wpurl' ) . '/' . ltrim( $strUrl, '/' );

      // Do an SSL check - only works on Apache
      global $is_IIS;
      if( isset( $_SERVER['HTTPS'] ) && !$is_IIS ) $strUrl = str_replace( 'http://', 'https://', $strUrl );

      return $strUrl;
   }

   /**
    * Creates folders for different sizes of images created from original testimonial image. 
    *
    * @access private
    * @return bool True if all folders are created, false otherwise
    */
   private function CreateImageFolders(){
      $strRoot = $_SERVER['DOCUMENT_ROOT'] . $this->strImageRoot;
      if( !@is_dir( $strRoot ) && !@mkdir( $strRoot ) ) return false;
      $strRoot .= '/';

      foreach( $this->aAllSizes as $strSize ){
         @chmod( $strRoot.$strSize, octdec( '0777' ) );
         if( !@is_dir( $strRoot.$strSize ) && !@mkdir( $strRoot.$strSize ) ) return false;
      }

      return true;
   }

   /**
    * Changes permissions to files in specified directory. Checks only files that have certain extension. Can be recursive
    *
    * @access private
    * @param string $strRoot         Directory in which to check files
    * @param string $strExtensions   Extensions of files which should be "chmoded" ;-) (it will be a part of regex expresion, examples: "jpg|jpeg|gif|png|bmp"-images, ".*"-all)
    * @param bool $bRecursive        Specifies if the function should be recursively checking the subdirectories
    * @param string $strRights       Linux octal form of rights to change to (e.g. 0777, 0755, ...)
    * @return bool                   True if successful, else an Exceptions is thrown 
    */
   private function ChmodFiles( $strRoot, $strExtensions, $bRecursive = true, $strRights = '0777' ){
      if( !@is_dir( $strRoot ) ) throw new Exception( "Specified folder does not exists: $strRoot !" );
      $aFiles = @scandir( $strRoot );
      if( !is_array( $aFiles ) ) throw new Exception( "Unable do get list of files, check your server settings !" );

      foreach( $aFiles as $strFile ){
         if( @is_file( $strRoot.'/'.$strFile ) && preg_match( '/^[^\.].*\.('.$strExtensions.')$/', $strFile ) ){
            if( ! @chmod( $strRoot.'/'.$strFile, octdec( $strRights ) ) ) 
               throw new Exception( "Unable to change permissions to file: $strRoot/$strFile !" );
         }
         if( @is_dir( $strRoot.'/'.$strFile ) && $bRecursive && preg_match( '/^[^\.]/', $strFile ) ) 
            $this->ChmodFiles( $strRoot.'/'.$strFile, $strExtensions, $bRecursive, $strRights );
      }

      return true;
   }

   /**
    * Deletes folder with all its subfolders and files.
    *
    * @access private
    * @param string $strRoot Full path to directory to delete
    * @return bool True if folder with all its subfolders and files is deleted, false otherwise
    */
   private function DeleteFolder( $strRoot ){
      /// TODO: better protection when deleting
      $aFiles = @scandir( $strRoot );
      if( !is_array( $aFiles ) ) return true;

      $bReturn = true;
      foreach( $aFiles as $strName ){
         if( @is_file( $strRoot.'/'.$strName ) ) $bReturn &= @unlink( $strRoot.'/'.$strName );
         elseif( is_dir( $strRoot.'/'.$strName ) && '.' != substr( $strName, 0, 1 ) ) $bReturn &= $this->DeleteFolder( $strRoot.'/'.$strName );
      }

      $bReturn &= @rmdir( $strRoot );
      return $bReturn;
   }

   private function CopyImages( $strOld, $strNew ){
      $aFiles = @scandir( $strOld );
      if( !is_array( $aFiles ) ) return true;

      foreach( $aFiles as $strName ){
         $strOldFile = $strOld.'/'.$strName;
         $strNewFile = $strNew.'/'.$strName;

         if( @is_file( $strOldFile ) && !@copy( $strOldFile, $strNewFile ) ) return false;
         unset( $strOldFile, $strNewFile );
      }

      return true;
   }

   private function CheckFolders(){
      if( !$this->CreateImageFolders() ){
         $this->strMessage .= '<p>Unable to create directory structure for images !</p>';
         return false;
      }

      $strOldPath = get_option( self::OPTION_IMAGES );
      if( $strOldPath && $strOldPath != $this->strImageRoot ){
         $strOldPath = $_SERVER['DOCUMENT_ROOT'] . $strOldPath . '/';
         $strNewPath = $_SERVER['DOCUMENT_ROOT'] . $this->strImageRoot . '/';

         try{
            foreach( $this->aAllSizes as $strDir )
               if( !$this->CopyImages( $strOldPath.$strDir, $strNewPath.$strDir ) )
                  throw new Exception( 'Unable to copy images from \''.$strOldPath.$strDir.'\' to \''.$strNewPath.$strDir.'\' !' );

            $strClean = $strOldPath;
         }catch( Exception $ex ){
            $strClean = $strNewPath;
            $this->strMessage = '<p>'.$ex->getMessage().'</p>';
         }

         if ($aFolders) foreach( $aFolders as $strDir ) $this->DeleteFolder( $strClean.$strDir );
      }

      return true;
   }



/// ================================================================================================
/// Activation of plugin
/// ================================================================================================

   /**
    * Returns version of database tables that are needed for current version of plugin.
    *
    * @access private
    * @return string Version of database tables needed
    */
   private function Version(){
      return '0.2';
   }

   /**
    * Installs or updates custom database tables used by this plugin. This function requires the 'sql'
    * folder with relevant subdirectories and files present in main plugin directory.
    *
    * @access private
    * @global WPDB
    * @param string $strInstall Version of currently installed database tables 
    */
   private function InstallDatabase( $strInstall = '' ){
      global $wpdb;

      $bTrackErrors = ini_set( 'track_errors', true );

      $strVersion = $this->Version();
      $strFile = FP_TESTIMONAL_ROOT . 'sql/' . $strVersion;
      $strFile .= (!$strInstall) ? '/install.sql' : "/update-$strInstall.sql";

      if( @file_exists( $strFile ) ){
         $strQuery = @file_get_contents( $strFile );
         if( !$strQuery ) throw new Exception( "Cannot read file '$strFile', install the database tables yourself according to this file ;-)" );
         $aQuery = explode( ';', $strQuery );

         $iQuery = count( $aQuery );
         for( $i=0; $i<$iQuery; $i++ ) $aQuery[$i] = str_replace( '%prefix%', $wpdb->prefix, trim( $aQuery[$i] ) );

         for( $i=0; $i<$iQuery; $i++ ) if( $aQuery[$i] ) $wpdb->query( $aQuery[$i] );

      }else{
         if( $php_errormsg ) throw new Exception( "Cannot read file '$strFile', install the database tables yourself according to this file ;-)" );
      }

      ini_set( 'track_errors', $bTrackErrors );
   }

   /**
    * Updates 'options' Wordpress table with option {@link FPTMain::OPTION_DATABASE} and stores there
    * current version of database tables.
    *
    * @access private
    */
   private function UpdateDatabaseVersion( $strInstall ){
      if( false === $strInstall ) add_option( self::OPTION_DATABASE, $this->Version() );
      else update_option( self::OPTION_DATABASE, $this->Version() );
   }

   /**
    * Checks version of custom database tables and updates them if neccessary.
    *
    * @access public
    */
   public function PluginActivate(){
      $strInstall = $this->strDatabaseVersion;
      $strVersion = $this->Version();

      if( 0 == strcmp( $strInstall, $strVersion ) ) return;

      if( !$strInstall ) $this->InstallDatabase();
      elseif( 0 > strcmp( $strInstall, $strVersion ) ) $this->InstallDatabase( $strInstall );

      $this->UpdateDatabaseVersion( $strInstall );
   }




/// ================================================================================================
/// Saving Posted Data
/// ================================================================================================

   /**
    * Handles Ajax requests. Two $_GET variables needs to be set. $_GET['ajax'] with name of function
    * and $_GET['fpt_id'] with ID of a testimonial or 0. Exits right after output from function.
    *
    * @access private
    */
   private function HandleAjax(){
      $strPageUrl = explode( '&', $_SERVER['REQUEST_URI'] );
      $strPageUrl = $strPageUrl[0];

      try{
         $this->$_GET['ajax']( $_GET['fpt_id'], $strPageUrl );
      }catch( Exception $ex ){}
      exit;
   }

   /**
    * Handles submit of new testimonial from public or private page.
    *
    * @access private
    * @param bool $bPublic   Specifies if the insert page was public or private
    * @throws Exception      Many errors. Problems with access rights on server folders, database access or invalid image type.
    */
   private function UploadTestimonial( $bPublic = true ){
      $objTestimonial = new FPTclass();

      if( !$bPublic ){
         $strSlug = stripcslashes( $_POST['slug'] );
         if( !$strSlug ) $strSlug = FPTMain::CreateSEOSlug( $_POST['name'] );
         $objTestimonial->strTitle = stripcslashes( $_POST['name'] );
         $objTestimonial->strExcerpt = stripcslashes( $_POST['excerpt'] );
         $objTestimonial->strText = stripcslashes( $_POST['text'] );
         $objTestimonial->iCategory = intval( $_POST['category'] );
         $objTestimonial->strSlug = $strSlug;
      }else{
         $strSlug = FPTMain::CreateSEOSlug( $_POST['tboxTitle'] );
         $objTestimonial->strTitle = stripcslashes( $_POST['tboxTitle'] );
         $objTestimonial->strText = stripcslashes( $_POST['txtText'] );
         $objTestimonial->strSlug = $strSlug;
      }

      $objTestimonial->Insert();

      /// Image upload
      if( isset( $_FILES['fileImage'] ) && $_FILES['fileImage']['tmp_name'] ){
         $aIDs = FPTImage::UploadPhoto( $objTestimonial->id, $_FILES['fileImage'], $strSlug, $objTestimonial->strTitle );
      }

      $this->strMessage .= '<p>Testimonial uploaded and it\'s waiting for approval !</p>';
   }

   /**
    * Removes Testimonial from database and deletes images from disk.
    *
    * @access public
    */
   private function RemoveTestimonial(){
      try{
         $aIDs = array_values( array_filter( explode( ',', $_POST['delete'] ) ) );

         foreach( $aIDs as $strID ){
            FPTImage::RemoveImagesToTestimonial( (int)$strID );
            FPTclass::RemoveTestimonial( (int)$strID );
         }

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
      }
   }

   /**
    * Saves Testimonial changes made from Testimonials management page after clicking on some testimonial.
    *
    * @access private
    * @throws Exception Many errors. Problems with access rights on server folders, database access or invalid image type.
    */
   private function SaveTestimonial(){
      $objTest = FPTclass::GetTestimonial( intval( $_POST['idTestimonial'] ) );

      $aFields = array();
      $aFields['title'] = stripcslashes( trim( $_POST['tboxTitle'] ) );
      $aFields['slug'] = stripcslashes( trim( $_POST['tboxSlug'] ) );
      $aFields['excerpt'] = stripcslashes( trim( $_POST['txtExcerpt'] ) );
      $aFields['text'] = stripcslashes( trim( $_POST['txtText'] ) );
      $aFields['category'] = intval( $_POST['selCategory'] );
      $aFields['status'] = $_POST['optStatus'][0];
      $aFields['featured'] = isset( $_POST['chkFeatured'] ) ? 'yes' : 'no';
      $aFields['lightbox'] = isset( $_POST['chkLightbox'] ) ? 'yes' : 'no';

      $strDate = '';
      if( preg_match( '/^\d\d\d\d\-\d\d\-\d\d$/', $_POST['tboxDate'] ) ) $strDate = $_POST['tboxDate'];
      if( $strDate ) $aFields['date'] = $strDate;

      /// Image change or upload
      try{
         if( (isset( $_FILES['fileImage'] ) && $_FILES['fileImage']['tmp_name']) || (isset( $_FILES['fileChange'] ) && $_FILES['fileChange']['tmp_name']) ){

            if( (isset( $_FILES['fileChange'] ) && $_FILES['fileChange']['tmp_name']) )
               FPTImage::UploadPhoto( $objTest->id, $_FILES['fileChange'], $objTest->strSlug, $objTest->strTitle, true );
            else
               FPTImage::UploadPhoto( $objTest->id, $_FILES['fileImage'], $objTest->strSlug, $objTest->strTitle );

         }
      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage();
         $bException = true;
      }

      $objTest->Update( $aFields );
      if( isset( $bException ) && $bException ) $this->strMessage .= ' Everything else was updated except from images !</p>';
      $this->strMessage .= '<p>Testimonial updated</p>';
   }

   /**
    * Saves new order of testimonials changed from Testimonials management page.
    *
    * @access private
    */
   private function SaveOrder(){
      $aTest = FPTclass::GetTestimonials();

      $aNewOrder = explode( ',', $_POST['order'] );
      $aOrderNumbers = array();
      foreach( $aNewOrder as $id ) $aOrderNumbers[] = $aTest[$id]->iOrder;

      sort( $aOrderNumbers );
      $iIter = 0;
      $aField = array( 'order' => 999 );
      foreach( $aNewOrder as $id ){
         $aField['order'] = intval( $aOrderNumbers[$iIter] );
         $aTest[ $id ]->Update( $aField );
         $iIter++;
      }

      $this->strMessage .= '<p>New order of testimonials saved !</p>';
   }

   /**
    * Saves testimonials featured setting from Testimonials management page. Only changes are updated in database
    *
    * @access private
    */
   private function SaveFeatured(){
      $aTest = FPTclass::GetTestimonials();
      $aField = array( 'featured' => 'yes' );

      /// Testimonials that are featured
      if( 'yes' != $_POST['featured'] ){
         $aUpdate = explode( ',', $_POST['featured'] );
         foreach( $aUpdate as $id )
            if( isset( $aTest[$id] ) && 'yes' != $aTest[$id] ) $aTest[$id]->Update( $aField );
      }

      /// Testimonials that are not featured
      if( 'yes' != $_POST['not_featured'] ){
         $aField['featured'] = 'no';
         $aUpdate = explode( ',', $_POST['not_featured'] );
         foreach( $aUpdate as $id )
            if( isset( $aTest[$id] ) && 'no' != $aTest[$id] ) $aTest[$id]->Update( $aField );
      }

      $this->strMessage .= '<p>Featured status updated for all testimonials !</p>';
      unset( $aUpdate, $aField, $aTest );
   }

   /**
    * Saves data from Options page in Testimonials management. This also recreates all the images and
    * thumbnails to their new size if the size was changed.
    *
    * @access private
    */
   private function SaveOptions(){
      global $wpdb;

      try{
         if( isset( $_POST['chmod-images'] ) ){
            $this->ChmodFiles( $_SERVER['DOCUMENT_ROOT'] . $this->strImageRoot, 'jpg|jpeg|png|bmp|gif|tif|tiff' );
            $this->strMessage .= '<p>Images prepared for FTP management !</p>';
            return;
         }
   
         if( isset( $_POST['recheck-images'] ) ){
            $aReport = FPTImage::RecheckImagesExistence();
            $this->strMessage = '<p>Recheck complete</p>'."\n";

            if( 0 < count( $aReport['recreated'] ) ){
               $this->strMessage .= "<p>Recreated images:</p>\n<ul>";
               foreach( $aReport['recreated'] as $id => $strPath ) $this->strMessage .= "\t<li>$id: $strPath</li>\n";
               $this->strMessage .= "</ul>";
            }

            if( 0 < count( $aReport['missing'] ) ){
               $this->strMessage .= "<p>Missing images:</p>\n<ul>";
               foreach( $aReport['missing'] as $id => $strPath ) $this->strMessage .= "\t<li>$id: $strPath</li>\n";
               $this->strMessage .= "</ul>";
            }

            return;
         }

         if( isset( $_POST['restore-order'] ) ){
            $wpdb->query( "UPDATE `{$wpdb->prefix}fpt_testimonials` SET `order`=`id`" );
            $this->strMessage .= '<p>Order of testimonials restored !</p>';
            return;
         }
      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
         return;
      }

      $this->strImageRoot = preg_replace( '/\/\//', '/', preg_replace( '/\/$/', '', strval( $_POST['tboxImageRoot'] ) ) );
      if( '/' != $this->strImageRoot[0] ) $this->strImageRoot = '/' . $this->strImageRoot;

      $this->iWidthLarge = intval( $_POST['tboxLarge'] );
      $this->iWidthMedium = intval( $_POST['tboxMedium'] );
      $this->iWidthSmall = intval( $_POST['tboxSmall'] );
      $this->iJPGQuality = intval( $_POST['tboxJPG'] );
      $this->bOutputCSS = (isset( $_POST['chkCSS'] )) ? true : false;

      $this->CheckOptions();

      if( $this->CheckFolders() ) $this->UpdateOption( self::OPTION_IMAGES, $this->strImageRoot );
      else $this->strImageRoot = get_option( self::OPTION_IMAGES );

      if( get_option( self::OPTION_LARGE ) != $this->iWidthLarge )
         $this->strMessage .= '<p>LARGE Images recreated to new width:</p>'.FPTImage::RecreateToNewWidth( 'large', $this->iWidthLarge );
      if( get_option( self::OPTION_MEDIUM ) != $this->iWidthMedium )
         $this->strMessage .= '<p>MEDIUM Images recreated to new width:</p>'.FPTImage::RecreateToNewWidth( 'medium', $this->iWidthMedium );
      if( get_option( self::OPTION_SMALL ) != $this->iWidthSmall )
         $this->strMessage .= '<p>SMALL Images recreated to new width:</p>'.FPTImage::RecreateToNewWidth( 'small', $this->iWidthSmall );

      $this->UpdateOption( self::OPTION_LARGE, $this->iWidthLarge );
      $this->UpdateOption( self::OPTION_MEDIUM, $this->iWidthMedium );
      $this->UpdateOption( self::OPTION_SMALL, $this->iWidthSmall );
      $this->UpdateOption( self::OPTION_JPG, $this->iJPGQuality );
      $this->UpdateOption( self::OPTION_CSS, ($this->bOutputCSS) ? 'yes' : 'no' );

      $this->strMessage .= '<p>Options updated !</p>';
   }

   /**
    * Function hooked to Wordpresses 'plugins_loaded' hook. It handles all save and upload requests
    * as well as Ajax requests for this plugin.
    *
    * @access public
    * @see FPTMain::HandleAjax
    * @see FPTMain::UploadTestimonial
    * @see FPTMain::SaveTestimonial
    * @see FPTMain::SaveOrder
    * @see FPTMain::SaveFeatured
    * @see FPTMain::SaveOptions
    * @param object $objWP WP object
    */
   public function SaveAndLoadData( $objWP ){

      try{
         if( isset( $_POST['uploadTestimonial'] ) && $_POST['tboxTitle'] && $_POST['txtText'] ) $this->UploadTestimonial();

         if( is_admin() && false !== strpos( $_SERVER['REQUEST_URI'], '.php?page=fv-testimonials' ) ){
            if( isset( $_GET['ajax'] ) ) $this->HandleAjax();
            if( isset( $_POST['addTestimonial'] ) ) $this->UploadTestimonial( false );
            if( isset( $_POST['order'] ) && 'no' != $_POST['order'] ) $this->SaveOrder();
            if( isset( $_POST['featured'] ) && 'no' != $_POST['featured'] ) $this->SaveFeatured();
            if( isset( $_POST['delete'] ) && 'no' != $_POST['delete'] ) $this->RemoveTestimonial();
            if( isset( $_POST['cmdSaveTestimonial'] ) ) $this->SaveTestimonial();
            if( isset( $_POST['cmdSaveBasic'] ) || isset( $_POST['chmod-images'] ) || isset( $_POST['recheck-images'] ) || isset( $_POST['restore-order'] ) ) $this->SaveOptions();
         }

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
      }

   }



/// ================================================================================================
/// 
/// ================================================================================================

   public function IDList( $strIDs, $strSeparator = ',', $strType = 'int' ){
      $aIDs = explode( $strSeparator, $strIDs );
      for( $i = count( $aIDs ) - 1;  $i >= 0;  $i-- ){
         if( 'int' == $strType ) $aIDs[$i] = intval( trim( $aIDs[$i] ) );
         else $aIDs[$i] = trim( $aIDs[$i] );
      }

      return $aIDs;
   }

   /**
    * Called from OutputToContent to replace special text: [Testimonials *] with Testimonials.
    * Recognizes this marks (instead of *):
    * - all:    All approved testimonials
    * - F:      All featured testimonials
    * - \d+F:   Featured with specific count
    * - \d+C:   All from specified category
    * - options:
    *    -- -f:                          Featured
    *    -- -c "number":                 How many
    *    -- -i "coma separated ids":     Include these IDs
    *    -- -e "coma separated ids":     Exclude these IDs
    *    -- -img "size"                  Size of image to load
    */
   public function ReplaceAbreviations( $aMatches ){
      if( !isset( $aMatches[1] ) ) return '';
      $aOptions = array();
      $strReturn = '';
      $aSubMatch = array();

      if( preg_match( '/all/', $aMatches[1], $aSubMatch ) ){
         $aTestimonials = FPTclass::GetTestimonialsImages( $this->strShowFull, null );

         foreach( $aTestimonials as $aTest ){
            $strReturn .= $aTest['testimonial']->ShowFull( $aTest['image'], false );
         }
         return $strReturn;
      }

      if( preg_match( '/(\d+)?F/', $aMatches[1], $aSubMatch ) ){
         $aOptions['where'] = array( 'featured' => 'yes', 'status' => 'approved' );
         $aOptions['order'] = array( 'order' => 'ASC' );
         if( $aSubMatch[1] ) $aOptions['limit'] = intval( $aSubMatch[1] );
         $aTestimonials = FPTclass::GetTestimonialsImages( $this->strShowFull, $aOptions );

         foreach( $aTestimonials as $aTest ){
            $strReturn .= $aTest['testimonial']->ShowFull( $aTest['image'], false );
         }
         return $strReturn;
      }

      $aSubMatch = array();
      if( preg_match( '/(\d+)C/', $aMatches[1], $aSubMatch ) ){
         $aOptions['where'] = array( 'category' => intval( $aSubMatch[1] ), 'status' => 'approved' );
         $aOptions['order'] = array( 'featured' => 'ASC', 'order' => 'ASC' );
         $aTestimonials = FPTclass::GetTestimonialsImages( $this->strShowFull, $aOptions );

         foreach( $aTestimonials as $aTest ){
            $strReturn .= $aTest['testimonial']->ShowFull( $aTest['image'], false );
         }
         return $strReturn;
      }

      $aParse = array();
      if( preg_match( '/\-f/', $aMatches[1] ) ) $aParse['featured'] = true;
      if( preg_match( '/\-c\s?(\d+)/', $aMatches[1], $aSubMatch ) ) $aParse['count'] = intval( $aSubMatch[1] );
      if( preg_match( '/\-i\s?([\d\s,]+)/', $aMatches[1], $aSubMatch ) ) $aParse['include'] = $this->IDList( $aSubMatch[1] );
      if( preg_match( '/\-e\s?([\d\s,]+)/', $aMatches[1], $aSubMatch ) ) $aParse['exclude'] = $this->IDList( $aSubMatch[1] );
      if( preg_match( '/\-img\s?([a-zA-Z]+)/', $aMatches[1], $aSubMatch ) ) $aParse['image'] = $aSubMatch[1];

      $aWhere = array();
      $aOptions['order'] = array( 'featured' => 'ASC', 'order' => 'ASC' );
      if( isset( $aParse['featured'] ) ) $aWhere['featured'] = 'yes';
      if( isset( $aParse['count'] ) ) $aOptions['limit'] = intval( $aParse['count'] );
      if( isset( $aParse['include'] ) ){
         if( count( $aWhere ) ) $aWhere = array( 1 => $aWhere, 2 => array( 'id' => array( 'value' => $aParse['include'], 'operation' => 'IN' ) ), 'operator' => 'OR' );
         else $aWhere['id'] = array( 'value' => $aParse['include'], 'operation' => 'IN' );
      }
      if( isset( $aParse['exclude'] ) ){
         if( count( $aWhere ) ) $aWhere = array( 1 => $aWhere, 2 => array( 'id' => array( 'value' => $aParse['exclude'], 'operation' => 'NOT IN' ) ), 'operator' => 'AND' );
         else $aWhere['id'] = array( 'value' => $aParse['exclude'], 'operation' => 'NOT IN' );
      }
      if( count( $aWhere ) ) $aWhere = array( 1 => $aWhere, 2 => array( 'status' => 'approved' ), 'operator' => 'AND' );
      else $aWhere['status'] = 'approved';
      
      if( !isset( $aParse['image'] ) ) $aParse['image'] = $this->strShowFull;

      $aOptions['structured-where'] = array( 1 => $aWhere, 2 => array( 'status' => 'approved' ), 'operator' => 'AND' );
      $aTestimonials = FPTclass::GetTestimonialsImages( $aParse['image'], $aOptions );

      foreach( $aTestimonials as $aTest ){
         $strReturn .= $aTest['testimonial']->ShowFull( $aTest['image'], false );
      }
      return $strReturn;
   }

   public function OutputToContent( $strText ){
      $strText = preg_replace_callback( '/\[Testimonials: (.*?)\]/i', array( &$this, 'ReplaceAbreviations' ), $strText );
      return $strText;
   }

   public function RenderMessage( $bEcho = true ){
      if( !$bEcho ) ob_start();
      
      include( FP_TESTIMONAL_ROOT . 'view/user/message.php' );
      
      if( !$bEcho ){
         $strText = ob_get_contents();
         ob_end_clean();
         return $strText;
      }
   }



   public function OutputUserCSS(){
      echo '<link rel="stylesheet" href="'.$this->GetUrl().'view/css/user.css" type="text/css" media="screen" charset="utf-8" />'."\n";
   }

   /**
    * Function hooked to Wordpresses 'admin_head' hook. It outputs link to administration css file.
    */
   public function OutputAdminHead(){
      echo '<link rel="stylesheet" href="'.$this->GetUrl().'view/css/admin.css" type="text/css" media="screen" charset="utf-8" />'."\n";
      echo '<link rel="stylesheet" href="'.$this->GetUrl().'view/css/jquery-ui-1.7.2.css" type="text/css" media="screen" charset="utf-8" />'."\n";
      echo '<script type="text/javascript">fpt_base = "'.$_SERVER['REQUEST_URI'].'";</script>';
   }

   /**
    * Hooked to management page display to register needed javascripts
    */
   public function ManageScripts(){
   /*   wp_deregister_script( 'jquery' );
      wp_deregister_script( 'jquery-sort' );

      wp_register_script( 'jquery', self::GetUrl().'js/jquery-1.3.2.js' );*/
/*      wp_register_script( 'jquery-sort', self::GetUrl().'js/jquery-ui-1.7.2.js', array( 'jquery' ) );*/
      
      wp_register_script( 'FPTestimonials', self::GetUrl().'js/testimonials.js', array( 'jquery-ui-sortable' ) );
//      wp_register_script( 'FPTestimonials', self::GetUrl().'js/testimonials.js' );

      wp_enqueue_script( 'FPTestimonials' );
   }

   /**
    * Function hooked to Wordpresses 'admin_menu' hook. This loads a function to output Testimonials
    * management.
    */
   public function AddManagement(){
      $mixPage = add_management_page('Foliopress Testimonials', 'Testimonials', 'edit_posts', 'fv-testimonials.php', array( &$this, 'OutputManagement' ) );
      add_action( "admin_print_scripts-$mixPage", array( &$this, 'ManageScripts' ) );
   }

   /**
    * Outputs Testimonials management submenu and then calls relevant function to load content.
    *
    * @see FPTMain::OutputList
    * @see FPTMain::OutputCategories
    * @see FPTMain::OutputOptions
    */
   public function OutputManagement(){
      $strUrl = explode( '&', $_SERVER['REQUEST_URI'] );
      $strUrl = $strUrl[0];
      if( !isset( $_GET['sub'] ) ) $_GET['sub'] = 'list';
      include( FP_TESTIMONAL_ROOT . 'view/admin/submenu.php' );

      if( 'list' == $_GET['sub'] ) $this->OutputList();
      elseif( 'add' == $_GET['sub'] ) $this->OutputAddNew();
      elseif( 'categories' == $_GET['sub'] ) $this->OutputCategories();
      elseif( 'templates' == $_GET['sub'] ) $this->OutputTemplates();
      elseif( 'options' == $_GET['sub'] ) $this->OutputOptions();
   }

   public function OutputList(){
      $iCategory = 0;
      $aTestimonials = FPTclass::GetTestimonialsImages( 'thumbs', null, $iCategory, false );

      include( FP_TESTIMONAL_ROOT . 'view/admin/list.php' );
   }

   public function OutputTemplates(){
      include( FP_TESTIMONAL_ROOT . 'view/admin/templates.php' );
   }

   public function OutputAddNew(){
      $strUrl = explode( '&', $_SERVER['REQUEST_URI'] );
      $strUrl = $strUrl[0] . '&sub=list';

      include( FP_TESTIMONAL_ROOT . 'view/admin/add-new.php' );
   }

   /**
    * Outputs Options subpage on Testimonials management page. It uses file 'view/admin/options.php'
    * as it's template.
    *
    * @access public
    */
   public function OutputOptions(){
      include( FP_TESTIMONAL_ROOT . 'view/admin/options.php' );
   }



   public static function CreateSEOSlug( $strText ){
      $strText = preg_replace( '/[^\w\d\s]/', '', $strText );
      $strText = preg_replace( '/[\s\_]/', '-', $strText );
      $strText = trim( preg_replace( '/(\-)+/', '-', $strText ), '-' );
      $strText = strtolower( $strText );

      return $strText;
   }



/// ================================================================================================
/// Functions to load Ajax data
/// ================================================================================================

   private function EditTestimonial( $id, $strPageUrl ){
      try{
         $objTestimonial = FPTclass::GetTestimonial( $id );

         $objImage = null;
         try{ 
            $objImage = FPTImage::GetImage( $id, 'original' );
         }catch( Exception $ex ){}

         include( FP_TESTIMONAL_ROOT . 'view/edit/edit-testimonial.php' );

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
         $this->RenderMessage();
      }
   }

   private function ChangeImage( $id, $strPageUrl ){
      include( FP_TESTIMONAL_ROOT . 'view/edit/change-image.php' );
   }

   private function ApproveTestimonial( $id, $strPageUrl ){
      try{
         $objTestimonial = FPTclass::GetTestimonial( $id );
         $objTestimonial->Update( array( 'status' => 'approved' ) );
         $objTestimonial->strStatus = 'approved';
         $objTestimonial->ShowListItem();

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
         $this->RenderMessage();
      }
   }

   private function DeleteTestimonial( $id, $strPageUrl ){
      try{
         $objTestimonial = FPTclass::GetTestimonial( $id );
         $objTestimonial->Update( array( 'status' => 'deleted' ) );
         $objTestimonial->strStatus = 'deleted';
         $objTestimonial->ShowListItem();

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
         $this->RenderMessage();
      }
   }

   private function RemoveImages( $id, $strPageUrl ){
      try{
         FPTImage::RemoveImagesToTestimonial( $id );

         $objTestimonial = FPTclass::GetTestimonial( $id );
         $objImage = null;

         include( FP_TESTIMONAL_ROOT . 'view/edit/edit-testimonial.php' );

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
         $this->RenderMessage();
      }
   }



/// ================================================================================================
/// Functions for other plugins and templates
/// ================================================================================================

   public function OutputTestimonials( $aOptions, $bEcho = false ){
      $aTestimonials = FPTclass::GetTestimonialsImages( $this->strShow, $aOptions );
      $strText = '';

      if( is_array( $aTestimonials ) ){
         foreach( $aTestimonials as $aTest ){
            $strText = $aTest['testimonial']->Show( $aTest['image'], $bEcho );
         }
      }

      return $strText;
   }


}

/**
 * Main object for this plugin. In this class there are functions for all the Wordpress hooks and
 * filters. All options there are relevant for this plugin.
 *
 * @global FPTMain $objFPTMain
 */
$objFPTMain = new FPTMain();


?>