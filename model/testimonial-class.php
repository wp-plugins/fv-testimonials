<?php
/**
 * File with testimonial class.
 *
 * @author Foliovision <programming@foliovision.com>
 * @package Foliopress
 * @subpackage Testimonials
 */

/**
 * Testimonial class. This class handles all things related to fv_testimonials table as well as 
 * displaying testimonials.
 */
class FPTclass {

   /// Block with properties of this class that are connected to table columns       
   public $id = 0;
   public $strTitle = '';
   public $strSlug = '';
   public $strExcerpt = '';
   public $strText = '';
   public $iCategory = 0;
   public $strStatus = '';
   public $strFeatured = '';
   public $strLightbox = '';
   public $dateInsert = null;
   public $strLastModified = '';
   public $dateLastModified = null;
   public $iOrder = 0;

   /**
    * Contains name of the table in database with WordPress prefix. It is loaded in constructor.
    * @access public
    * @var string
    */
   public $strTable = '';

   const REPLACE_EXCERPT = '$excerpt$';
   const REPLACE_THUMBNAIL = '$thumbnail$';


   private function CheckDate(){
      /// TODO: Code CheckDate function
      return true;
   }


   /**
    * Loads data into {@link FPTclass::$strTable}.
    *
    * @access public
    */
   public function __construct(){
      global $wpdb;
      $this->strTable = $wpdb->prefix . 'fpt_testimonials';
   }

   /**
    * Loads data into object properties.
    *
    * @access public
    * @param object $objRow Object returned from database query containig one line of data, where
    *    columns are listed as properties
    */
   public function LoadData( $objRow ){
      $this->id = $objRow->id;
      $this->strTitle = $objRow->title;
      $this->strSlug = $objRow->slug;
      $this->strExcerpt = $objRow->excerpt;
      $this->strText = $objRow->text;
      $this->iCategory = $objRow->category;
      $this->strStatus = $objRow->status;
      $this->strFeatured = $objRow->featured;
      $this->strLightbox = $objRow->lightbox;
      $this->dateInsert = $objRow->date;
      $this->strLastModified = $objRow->last_modified;
      $this->dateLastModified = $objRow->last_modified_date;
      $this->iOrder = $objRow->order;
   }

   /**
    * Returns URL to page where this testimonial will be displayed.
    *
    * @access public
    * @global FPTMain Stores options relevant to this plugin
    * @return string URL to page with this testimonial
    */
   public function GetURL(){
      global $objFPTMain;

      if( !$objFPTMain->strUrl || !$this->strSlug ) return '';

      $strUrl = get_option( 'home' );
      if( '/' != $strUrl[ strlen( $strUrl ) - 1 ] ) $strUrl .= '/';
      $strUrl .= $objFPTMain->strUrl;

      /// TODO: Make category link for testimonial as well
      /*if( $this->iCategory ){
         $objCategory = FPTCategory::GetCategory( $this->iCategory );
         $strUrl .= '/' . $objCategory->strSlug;
      }*/

      $strUrl .= '#' . $this->strSlug;

      return $strUrl;
   }

   public function ShowFull( $objImage = null, $bEcho = true ){
      global $objFPTMain;

      try{
         if( !$objImage ) $objImage = FPTImage::GetImage( $this->id, $objFPTMain->strShowFull );
      }catch( Exception $ex ){}

      if( !$bEcho ) ob_start();
      include( FP_TESTIMONAL_ROOT . 'view/user/show-testimonial.php' );
      if( !$bEcho ){
         $strText = ob_get_contents();
         ob_end_clean();
         return $strText;
      }
   }

   /**
    * Outputs testimonial. It uses view/user/show-testimonial-excerpt.php template
    *
    * @access public
    */
   public function Show( $objImage = null, $bEcho = true ){
      global $objFPTMain;

      try{
         if( !$objImage ) $objImage = FPTImage::GetImage( $this->id, $objFPTMain->strShow );
      }catch( Exception $ex ){}

      $strText = $this->strText;
      if( $this->strExcerpt ){
         $strText = $this->strExcerpt;
         $strText .= '<div class="clsMore"><a href="'.$this->GetURL().'">More ...</a></div>';
      }elseif( $iPos = strpos( $strText, '<!--more-->' ) ){
         $strText = substr( $strText, 0, $iPos );
         $strText .= '<div class="clsMore"><a href="'.$this->GetURL().'">More ...</a></div>';
      }

      if( !$bEcho ) ob_start();
      include( FP_TESTIMONAL_ROOT . 'view/user/show-testimonial-excerpt.php' );
      if( !$bEcho ){
         $strText = ob_get_contents();
         ob_end_clean();
         return $strText;
      }
   }

   /**
    * Outputs short testimonial. This function uses template view/admin/list-item-testimonial.php
    *
    * @access public
    */
   public function ShowListItem( $objThumb = null ){
      try{
         if( !$objThumb ) $objThumb = FPTImage::GetImage( $this->id, 'thumbs' );
      }catch( Exception $ex ){}

      include( FP_TESTIMONAL_ROOT . 'view/admin/list-item-testimonial.php' );
   }

   /**
    * Inserts new testimonial into database. It uses values that are stored in properties of this
    * object. It is ordered to the bottom of the testimonials.
    *
    * @access public
    * @global WPDB Wordpress database object
    * @global UserData Wordpress userdata object
    * @return int|bool Value that is returned by $wpdb->query function when INSERT SQL is putted inside
    */
   /// foliovision
   public function Insert(){
      global $wpdb, $userdata;
      get_currentuserinfo();

      $strTitle = $wpdb->escape( $this->strTitle );
      $strSlug = $wpdb->escape( $this->strSlug );
      $strExcerpt = $wpdb->escape( $this->strExcerpt );
      $strText = $wpdb->escape( $this->strText );
      $strUser = $wpdb->escape( $userdata->user_login );
      $iCategory = intval( $this->iCategory );
      $iOrder = intval( $this->iOrder );

      if( !$iOrder ){
         $strSelect = "SELECT MAX(`order`) AS `order` FROM `{$this->strTable}`";
         $objRow = $wpdb->get_row( $strSelect );
         if( !$objRow->order ) $iOrder = 1;
         else $iOrder = $objRow->order + 1;
      }

      $strInsert = "INSERT INTO {$this->strTable}(`title`,`slug`,`excerpt`,`text`,`category`,`date`,`last_modified`,`last_modified_date`,`order`) VALUE ";
      $strInsert .= "('$strTitle','$strSlug','$strExcerpt','$strText',$iCategory,NOW(),'$strUser',NOW(),$iOrder)";

      $result = $wpdb->query( $strInsert );
      if( !$result ) throw new Exception( 'Error occured during insertion of testimonial into database !' );
      $this->id = $wpdb->insert_id;

      return $id;
   }

   /**
    * Updates `title`,`last_modified`,`last_modified_date`,`category`,`status`,`featured`,`slug`
    * according to values stored in properties of object calling this function.
    *
    * @access public
    * @global WPDB Wordpress database object
    * @global UserData Wordpress userdata object
    * @return int|bool Value that is returned by $wpdb->query function when INSERT SQL is putted inside
    */
   public function Update( $aFields ){
      global $wpdb, $userdata;
      get_currentuserinfo();

      $aUpdate = array();
      foreach( $aFields as $strKey => $mixValue ){
         if( 'id' == $strKey ) continue;
         elseif( 'status' == $strKey && !in_array( $mixValue, array( 'wait', 'approved', 'deleted' ) ) ) $mixValue = 'wait';
         elseif( 'featured' == $strKey && !in_array( $mixValue, array( 'yes', 'no', 'old' ) ) ) $mixValue = 'no';
         elseif( 'lightbox' == $strKey && !in_array( $mixValue, array( 'yes', 'no' ) ) ) $mixValue = 'no';

         if( is_int( $mixValue ) || 'NOW()' === $mixValue ) $aUpdate[] = "`$strKey`=$mixValue";
         elseif( is_string( $mixValue ) ) $aUpdate[] = "`$strKey`='".$wpdb->escape( $mixValue )."'";
      }
      $aUpdate[] = "`last_modified`='".$wpdb->escape( $userdata->user_login )."'";

      $strFields = implode( ', ', $aUpdate );
      $id = (isset( $aFields['id'] )) ? intval( $aFields['id'] ) : intval( $this->id );
      $strUpdate = "UPDATE {$this->strTable} SET $strFields WHERE `id`=$id";

      $mixResult = $wpdb->query( $strUpdate );
      return $mixResult;
   }

   /**
    * Returns {@link FPTclass himself} according to $id inserted.
    *
    * @access public
    * @static
    * @global WPDB Wordpress database object
    * @param int $id ID of testimonial to load
    * @return FPTclass|bool Loaded FPTclass if everything is in order or false otherwise
    */
   public static function GetTestimonial( $id ){
      global $wpdb;

      $objTestimonial = new FPTclass();
      $strQuery = "SELECT * FROM {$objTestimonial->strTable} WHERE `id`=$id";
      $objRow = $wpdb->get_row( $strQuery );
      if( !$objRow ) return false;
      $objTestimonial->LoadData( $objRow );

      return $objTestimonial;
   }

   public static function GetTestimonials( $iCategory = 0 ){
      global $wpdb;

      $objTestimonial = new FPTclass();
      $strSelect = "SELECT * FROM `{$objTestimonial->strTable}` ";
      if( $iCategory ) $strSelect .= "WHERE `category`=$iCategory ";

      $aRows = $wpdb->get_results( $strSelect );
      $aResults = array();

      foreach( $aRows as $objRow ){
         $objTestimonial->LoadData( $objRow );

         $aResults[ $objTestimonial->id ] = $objTestimonial;
         unset( $objTestimonial );

         $objTestimonial = new FPTclass();
      }

      unset( $aRows );

      return $aResults;
   }

   private static function CreateQueryVariable( $strKey, $mixValue ){
      global $wpdb;
      $strQuery = '';

      if( is_string( $mixValue ) ) $mixValue = $wpdb->escape( $mixValue );

      if( is_string( $mixValue ) && false !== strpos( $strValue, '%' ) ) $strQuery = "t.`$strKey` LIKE '$mixValue'";
      elseif( is_string( $mixValue ) ) $strQuery = "t.`$strKey`='$mixValue'";
      elseif( is_int( $mixValue ) ) $strQuery = "t.`$strKey`=$mixValue";

      return $strQuery;
   }

   private static function ParseWhere( $aWhere ){
      global $wpdb;
      $aParts = array();

      foreach( $aWhere as $strKey => $mixValue ){
         if( is_array( $mixValue ) && isset( $mixValue['value'] ) && isset( $mixValue['operation'] ) ){

            if( in_array( $mixValue['operation'], array( 'IN', 'NOT IN' ) ) && is_array( $mixValue['value'] ) ){
               for( $i = count( $mixValue['value'] ) - 1;  $i >= 0;  $i-- )
                  if( is_string( $mixValue['value'][$i] ) ) $mixValue['value'][$i] = "'".$wpdb->escape( $mixValue['value'][$i] )."'";

               $strParse = '('.implode( ',', $mixValue['value'] ).')';
               $aParts[] = "t.`$strKey` {$mixValue['operation']} $strParse";
            }else $aParts[] = FPTclass::CreateQueryVariable( $strKey, $mixValue['value'] );

         }else $aParts[] = FPTclass::CreateQueryVariable( $strKey, $mixValue );
      }

      return implode( ' AND ', $aParts );
   }
   
   private static function ParseStructuredWhere( $aWhere ){
      $strWhere = '';
      
      if( isset( $aWhere[1] ) && isset( $aWhere[2] ) && isset( $aWhere['operator'] ) )
         $strWhere = '( '.FPTclass::ParseStructuredWhere( $aWhere[1] ).' ) '.$aWhere['operator'].' ( '.FPTclass::ParseStructuredWhere( $aWhere[2] ).' )';
      else
         $strWhere = FPTclass::ParseWhere( $aWhere );

      return $strWhere;
   }

   public static function GetTestimonialsImages( $strSize, $aOptions = null, $iCategory = 0, $bApproved = true ){
      global $wpdb;

      $objTestimonial = new FPTclass();
      $objImage = new FPTImage();

      $strColumns = 't.`id` AS `id`, t.`title` AS `title`, t.`slug` AS `slug`, t.`excerpt` AS `excerpt`, t.`text` AS `text`, t.`category` AS `category`, ';
      $strColumns .= 't.`status` AS `status`, t.`featured` AS `featured`, t.`lightbox` AS `lightbox`, t.`date` AS `date`, t.`last_modified` AS `last_modified`, ';
      $strColumns .= 't.`last_modified_date` AS `last_modified_date`, t.`order` AS `order`, i.`id` AS `id_image`, i.`testimonial` AS `testimonial`, ';
      $strColumns .= 'i.`path` AS `path`, i.`width` AS `width`, i.`height` AS `height`, i.`type` AS `type`';

      $strSelect = "SELECT $strColumns FROM `{$objTestimonial->strTable}` t LEFT OUTER JOIN `{$objImage->strTable}` i ON (t.`id`=i.`testimonial` OR i.`id` IS NULL) ";
      if( $aOptions && is_array( $aOptions ) ){

         $strSelect .= "WHERE (i.`type`='$strSize' OR i.`type` IS NULL) AND ";

         if( isset( $aOptions['where'] ) ) $strSelect .= FPTclass::ParseWhere( $aOptions['where'] );
         elseif( isset( $aOptions['structured-where'] ) ) $strSelect .= FPTclass::ParseStructuredWhere( $aOptions['structured-where'] );
         
         if( isset( $aOptions['order'] ) ){
            $iCount = count( $aOptions['order'] );
            $strSelect .= " ORDER BY ";

            $aOrder = array();
            foreach( $aOptions['order'] as $strKey => $strOrder ) $aOrder[] = " t.`$strKey` {$strOrder}";
            $strSelect .= implode( ',', $aOrder );
         }

         if( isset( $aOptions['limit'] ) ) $strSelect .= " LIMIT {$aOptions['limit']}";

      }else{

         $strSelect .= "WHERE (i.`type`='$strSize' OR i.`type` IS NULL) ";
         if( $bApproved ) $strSelect .= "AND t.`status`='approved' ";
         if( $iCategory ) $strSelect .= "AND t.`category`=$iCategory ";
         $strSelect .= "ORDER BY t.`featured` ASC, t.`order` ASC";

      }

      $aResults = array();

      if( is_array( $aOptions ) && $aOptions && $aOptions['random'] ){
         $iRows = $wpdb->query( $strSelect );
         $iRandom = rand( 0, $iRows - 1 );
         $objRow = $wpdb->last_result[$iRandom];
         if( !$objRow ) return array();
         $aRows = array( $objRow );
      }else $aRows = $wpdb->get_results( $strSelect );

      foreach( $aRows as $objRow ){
         $objTestimonial->LoadData( $objRow );
         $objImage->LoadData( $objRow, true );

         $aResults[] = array( 'testimonial' => $objTestimonial, 'image' => $objImage );
         unset( $objTestimonial, $objImage );

         $objTestimonial = new FPTclass();
         $objImage = new FPTImage();
      }

      unset( $aRows );

      return $aResults;
   }

   /**
    * Removes testimonial from database. This is not updating status to deleted, this function is removing the testimonial from database for good.
    *
    * @access public
    * @static
    * @param int $id ID of testimonial to delete
    */
   public static function RemoveTestimonial( $id ){
      global $wpdb;

      $objTest = new FPTclass();
      $strSelect = "DELETE FROM `{$objTest->strTable}` WHERE `id`=$id";
      $wpdb->query( $strSelect );

      unset( $objTest );
   }


}

?>