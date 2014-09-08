<?php

class FPTImage2 {

   public $iTestimonial = 0;
   public $aImages = array();


   public $iNumber = 1;
   public $strName = '';
   public $strTitle = '';
   public $strExtension = '';
   public $strType = '';
   public $strRealPath;
   public $strOrigPath;
   public $strSize;
   public $aImageInfo = array();

   public $strTable = '';


   public function __construct(){
      global $wpdb;
      //$this->strTable = $wpdb->prefix . 'fpt_images';
   }



/// --------------------------------------------------------------------------------------------------------------------
/// Loading of object
/// --------------------------------------------------------------------------------------------------------------------

   /// done
   public function LoadData( $iTestimonialID, $iNumber, $aImages, $strSize, $bSpecialID = false ){
      global $objFVTMain;
      
   /*   if( $bSpecialID ) $this->id = $objRow->id_image;
      else $this->id = $iTestimonialID;
*/
      $upload_dir = wp_upload_dir();
      $this->iTestimonial = $iTestimonialID;
      $this->aImages = $aImages;
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $this->strRealPath = $upload_dir['basedir'].'/testimonials' . $aImages[$strSize]['path'];
      else $this->strRealPath = $_SERVER['DOCUMENT_ROOT'] . $objFVTMain->strImageRoot . $aImages[$strSize]['path'];
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $this->strOrigPath = $upload_dir['basedir'].'/testimonials' . $aImages['original']['path'];
      else $this->strOrigPath = $_SERVER['DOCUMENT_ROOT'] . $objFVTMain->strImageRoot . $aImages['original']['path'];
      $this->strPath = $aImages[$strSize]['path'];
      $this->iWidth = $aImages[$strSize]['width'];
      $this->iHeight = $aImages[$strSize]['height'];
      $this->strTitle = $aImages[$strSize]['name'];
      $this->strSize = $strSize;
/*     $this->strSize = $objRow->type;
*/    $this->iNumber = intval( $iNumber );
      
      $this->LoadSpecialData();
   }

   /// done
   public function LoadSpecialData(){
      global $objFVTMain;
      $upload_dir = wp_upload_dir();
      $aPathInfo = pathinfo( $this->strRealPath );
      $this->strName = substr( $aPathInfo['basename'], 0, strlen( $aPathInfo['basename'] ) - strlen( $aPathInfo['extension'] ) - 1 );
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $this->strOrigPath = $upload_dir['basedir'].'/testimonials' . $this->aImages['original']['path'];
      else $this->strOrigPath = $_SERVER['DOCUMENT_ROOT'] . $objFVTMain->strImageRoot . $this->aImages['original']['path'];
      $this->strExtension = $aPathInfo['extension'];

      if( !is_file( $this->strRealPath ) ) return;
      $this->aImageInfo = getimagesize( $this->strRealPath );
      $this->strType = substr( $this->aImageInfo['mime'], 6 ); /**/
   }

/// --------------------------------------------------------------------------------------------------------------------
/// Database functions
/// --------------------------------------------------------------------------------------------------------------------

   /// done
   private function UpdateResizedImage($iNumber, $idTestimonial, $strSize, $strPath, $iWidth, $iHeight ){
      
      $this->iTestimonial = $idTestimonial;
      $this->iNumber = $iNumber;
      $this->strSize = $strSize;
      $this->iWidth = $iWidth;
      $this->iHeight = $iHeight;
      $this->strPath = $strPath;
      
      $this->Update($idTestimonial);
      
      return $this;

      /*global $wpdb;
      
      $idTestimonial = intval($idTestimonial);

      $strPath = $wpdb->escape( $strPath );
      $strSelect = "SELECT * FROM `wp_postmeta` WHERE `post_id`=$idTestimonial AND `meta_key`='_fvt_images' LIMIT 1";
      
      $objRow = $wpdb->get_row( $strSelect );
      if( !$objRow ) throw new Exception( "Recreating non-existing image '$strPath' !" );
      $objResized = new FPTImage2();
      $objResized->LoadData( $this->iTestimonial, $this->iNumber, $this->aImages, $this->strSize );

      $objResized->iWidth = $iWidth;
      $objResized->iHeight = $iHeight;
     // var_dump($iWidth);
      
      
      
      if( !$objResized->Update($idTestimonial) ) throw new Exception( "Unable to update changed file '$strPath' !" );
     // var_dump($objResized);die();
      return $objResized;*/
   }

   /// done
   private function SaveResizedImage( $strPath, $iWidth, $iHeight, $strSize, $iNumber, $post_id){
      $objResizedImage = new FPTImage2();
      $objResizedImage->iTestimonial = $this->iTestimonial;
      $objResizedImage->strPath = $strPath;
      $objResizedImage->iWidth = $iWidth;
      $objResizedImage->iHeight = $iHeight;
      $objResizedImage->strSize = $strSize;
      $objResizedImage->iNumber = $iNumber;
      if( !$objResizedImage->SaveNewEntry($post_id) ) throw new Exception( 'Unable to save new image into database !' );

      $objResizedImage->LoadSpecialData();

      return $objResizedImage;
   }



   /// done
   public function Update($idTestimonial = 0){
      global $wpdb;

      $aImages = get_post_meta($this->iTestimonial,'_fvt_images',true);
      $aImages[$this->iNumber][$this->strSize]['width'] = ( $this->iWidth );
      $aImages[$this->iNumber][$this->strSize]['height'] = ( $this->iHeight );
      $aImages[$this->iNumber][$this->strSize]['path'] = $wpdb->escape( $this->strPath );
      $aImages[$this->iNumber][$this->strSize]['name'] = $wpdb->escape( $this->strTitle );

      $this->aImages[$this->strSize]['path'] =  $this->strPath;
      $this->aImages[$this->strSize]['width'] =  $this->iWidth;
      $this->aImages[$this->strSize]['height'] =  $this->iHeight;
      $this->aImages[$this->strSize]['name'] =  $this->strTitle;
      
      update_post_meta((int)$this->iTestimonial, '_fvt_images', $aImages);
      return true;
   }

   /// done
   public function SaveNewEntry($post_id = 0){
      global $wpdb;

      $aImages = get_post_meta($post_id,'_fvt_images',true);
      
      $aImages[$this->iNumber][$this->strSize]['path'] = $wpdb->escape( $this->strPath );
      $aImages[$this->iNumber][$this->strSize]['width'] = intval( $this->iWidth );
      $aImages[$this->iNumber][$this->strSize]['height'] = intval( $this->iHeight );
      $aImages[$this->iNumber][$this->strSize]['name'] = $this->strTitle;
      
      if( !update_post_meta($post_id, '_fvt_images', $aImages) ) return false;

      $this->id = $post_id;//$wpdb->insert_id;
      return $aImages;
   }

   /**
    * Fetches all the images from database, loads them into FPTImage2 classes and returns the array with them
    *
    * @access private
    * @return array     Array of FPTImage2 classes loaded from DB
    */
   /// done
   public static function GetImages( $strSize = false, $idTestimonial = false ){
      global $wpdb;

      $objImage = new FPTImage2();
      $strSelect = "SELECT * FROM `{$objImage->strTable}`";
      if( $strSize || $idTestimonial ){
         $aSelect = array();
         if( $strSize ) $aSelect[] = "`type`='$strSize'";
         if( $idTestimonial ) $aSelect[] = "`testimonial`=$idTestimonial";
         $strSelect .= " WHERE " . implode( ' AND ', $aSelect );
      }
      $strSelect .= ' ORDER BY `testimonial`, `number`';

      $aResults = $wpdb->get_results( $strSelect );
      if( !is_array( $aResults ) ) throw new Exception( "Error while fetching testimonial images from database !" );

      $aImages = array();
      foreach( $aResults as $objRow ){
         $objImage = new FPTImage2();
         $objImage->LoadData( $objRow );
         $aImages[] = $objImage;
      }

      return $aImages;
   }




   /**
    * Removes images assigned to Testimonial from database and from disk.
    *
    * @access public
    * @static
    * @param int $iTestimonial ID of testimonial
    */
   /// done
   public static function RemoveImagesToTestimonial( $iTestimonial, $iNumber = -1, $bMoveHigher = true ){
      global $wpdb, $objFVTMain;

      $objImage = new FPTImage2();

      $strSelect = "SELECT `path` FROM `{$objImage->strTable}` WHERE `testimonial`=$iTestimonial";
      if( $iNumber != -1 ) $strSelect .= " AND `number`=$iNumber";
      $aPaths = $wpdb->get_results( $strSelect );
      if( !$aPaths ) return true;

      $strBasePath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot;
      foreach( $aPaths as $objPath ) @unlink( $strBasePath.$objPath->path );

      $strDelete = "DELETE FROM `{$objImage->strTable}` WHERE `testimonial`=$iTestimonial";
      if( $iNumber != -1 ) $strDelete .= " AND `number`=$iNumber";
      $iDelete = $wpdb->query( $strDelete );

      if( $iDelete ) return true;
      else return false;
   }

   /// done
   public static function GetImage( $id, $iNumber = 1, $strSize = '', &$aImages = array() ){
      if( is_array( $aImages ) && 0 < count( $aImages ) ){
         foreach( $aImages as $objImage ){
            if( $id == $objImage->iTestimonial && $iNumber == $objImage->iNumber ){
               if( !$strSize ) return $objImage;
               elseif( $strSize == $objImage->strSize ) return $objImage;
            }
         }
      }

      global $wpdb;

      $objImage = new FPTImage2();
      $strSelect = "SELECT * FROM `{$objImage->strTable}` WHERE ";
      if( $strSize ) $strSelect .= "`testimonial`=$id AND `type`='$strSize' AND `number`=$iNumber";
      else $strSelect .= "`id`=$id";

      $objRow = $wpdb->get_row( $strSelect );
      if( !$objRow ) throw new Exception( "There is no image with ID: '$id' !" );
      $objImage->LoadData( $objRow );

      return $objImage;
   }

/// --------------------------------------------------------------------------------------------------------------------
/// Image Creation functions
/// --------------------------------------------------------------------------------------------------------------------

   /// done
   public function CreateResizedImagePNG( $strSource, $strPath, $iWidth, $iHeight, $bTransform = true, $iColorsAtMax = 5000 ){
      $upload_dir = wp_upload_dir();
      $imgSource = imagecreatefrompng( $strSource );
      $imgDest = imagecreatetruecolor( $iWidth, $iHeight );
      $iOrigImageInfo = getimagesize( $this->strOrigPath );
      imagealphablending( $imgDest, false );
      imagecopyresampled( $imgDest, $imgSource, 0, 0, 0, 0, $iWidth, $iHeight,  $iOrigImageInfo[0],$iOrigImageInfo[1] );
      imagesavealpha( $imgDest, true );

      if( $bTransform ){
         if( !function_exists( 'FV_CountUniqueColors' ) ) require( FVTESTIMONIALS_ROOT . 'model/image-non-class.php' );
         $aColors = FV_CountUniqueColors( $imgDest, $iWidth, $iHeight, true );

         if( $aColors && $iColorsAtMax > $aColors['colors'] ){
            $bComplex = FV_IsComplex( $imgSource, $this->aImageInfo );
            if( !$bComplex ) FV_TrueColorToIndexed( $imgDest, $aColors );
         }
      }

      global $objFVTMain;
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) imagepng( $imgDest, $upload_dir['basedir'].'/testimonials'.$strPath, 9 );
      else imagepng( $imgDest, $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.$strPath, 9 );

      imagedestroy( $imgSource );
      imagedestroy( $imgDest );
   }

   /// done
   public function CreateResizedImageGIF( $strSource, $strPath, $iWidth, $iHeight ){
      $upload_dir = wp_upload_dir();
      $imgSource = imagecreatefromgif( $strSource );
      $imgDest = imagecreatetruecolor( $iWidth, $iHeight );
      $iOrigImageInfo = getimagesize( $this->strOrigPath );
      imagealphablending( $imgDest, false );
      imagecopyresampled( $imgDest, $imgSource, 0, 0, 0, 0, $iWidth, $iHeight, $iOrigImageInfo[0],$iOrigImageInfo[1] );
      imagesavealpha( $imgDest, true );

      global $objFVTMain;
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) imagegif( $imgDest, $upload_dir['basedir'].'/testimonials'.$strPath, 9 );
      else imagegif( $imgDest, $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.$strPath, 9 );

      imagedestroy( $imgSource );
      imagedestroy( $imgDest );
   }

   /// done
   public function CreateResizedImageJPG( $strSource, $strPath, $iWidth, $iHeight, $iJPGQuality ){
      $upload_dir = wp_upload_dir();
      $imgSource = imagecreatefromjpeg( $strSource );
      $imgDest = imagecreatetruecolor( $iWidth, $iHeight );
      imagealphablending( $imgDest, false );
      $iOrigImageInfo = getimagesize( $this->strOrigPath );
      
      imagecopyresampled( $imgDest, $imgSource, 0, 0, 0, 0, $iWidth, $iHeight, $iOrigImageInfo[0],$iOrigImageInfo[1]);//$this->aImageInfo[0], $this->aImageInfo[1] 
      imagesavealpha( $imgDest, true );
      
      global $objFVTMain;
       if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) imagejpeg( $imgDest, $upload_dir['basedir'].'/testimonials'.$strPath, $iJPGQuality );
       else imagejpeg( $imgDest, $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.$strPath, $iJPGQuality );

      imagedestroy( $imgDest );
      imagedestroy( $imgSource );
   }

/// --------------------------------------------------------------------------------------------------------------------
/// More complex functions
/// --------------------------------------------------------------------------------------------------------------------

   /// done
   public function Show( $bEcho = true ){
      if( !$this->strPath ) return '';

      global $objFVTMain;
      if( !$bEcho ) ob_start();

      $strPath = $objFVTMain->strImageRoot . $this->strPath;
      $objImage = $this;
      include( FP_TESTIMONAL_ROOT . 'view/user/image.php' );

      if( !$bEcho ){ 
         $strImage = ob_get_contents();
         ob_end_clean();
         return $strImage;
      }
   }

   /// done
               //  CreateResizedImage( $strSize, $iNewWidth, $iNumber, $objOriginal->iTestimonial, $objFVTMain->iJPGQuality );
   public function CreateResizedImage( $strSize, $iDestWidth, $iNumber, $idTestimonial = 0, $iJPGQuality = 80){
      if (!$idTestimonial) return false;
      global $objFVTMain;
      $upload_dir = wp_upload_dir();
      $iOrigImageInfo = getimagesize( $this->strOrigPath );
      if ($iOrigImageInfo){
         $iJPGQuality = intval( $iJPGQuality );
         if( $iJPGQuality < 1 || $iJPGQuality > 100 ) $iJPGQuality = 80;
   
         if( $iDestWidth > $iOrigImageInfo[0] ) $iDestWidth = $iOrigImageInfo[0];
         $ratio = $iDestWidth / $iOrigImageInfo[0];
         $iWidth = intval( $iOrigImageInfo[0] * $ratio );
         $iHeight = intval( $iOrigImageInfo[1] * $ratio );
   //      var_dump($iOrigImageInfo);var_dump($this->strOrigPath);
         $strFunctionLoad = 'imagecreatefrom' . $this->strType;
         $strFunctionSave = 'image' . $this->strType;
         if( !function_exists( $strFunctionLoad ) || !function_exists( $strFunctionSave ) ){
            //var_dump( $this );
            throw new Exception( "Cannot handle type '{$this->strType}'" );
         }
         
         $strSource = $this->strOrigPath;
         $strPath = '/' . $strSize . '/' . $this->strName . '.' . $this->strExtension;
   
         if( 'png' == $this->strType ) $this->CreateResizedImagePNG( $strSource, $strPath, $iWidth, $iHeight );
         if( 'gif' == $this->strType ) $this->CreateResizedImageGIF( $strSource, $strPath, $iWidth, $iHeight );
         if( 'jpeg' == $this->strType || 'jpg' == $this->strType ) $this->CreateResizedImageJPG( $strSource, $strPath, $iWidth, $iHeight, $iJPGQuality );
         
         
         if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) @chmod( $upload_dir['basedir'].'/testimonials'.$strPath, octdec( '0'.'777' ) ); 
         else @chmod( $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.$strPath, octdec( '0'.'777' ) );
         
         return $this->UpdateResizedImage($iNumber, $idTestimonial, $strSize, $strPath, $iWidth, $iHeight ); 
      }      
   }
   public static function RecreateToNewWidth( $strSize, $iNewWidth, $bReturnInfo = true ){
      global $wpdb,$objFVTMain;
      $strInfo = '';
      if( $bReturnInfo ) $strInfo = '<table class="recreateInfo">';
      set_time_limit( 1000 );
      $objOriginal = new FPTImage2();
      $strSelect = "SELECT * FROM $wpdb->postmeta WHERE `meta_key`='_fvt_images'";
      $aRows = $wpdb->get_results( $strSelect );

      foreach( $aRows as $objRow ){
         $aAllImages = unserialize($objRow->meta_value);          
         foreach($aAllImages as $iNumber => $aImages){
            
           $objOriginal->LoadData( $objRow->post_id, $iNumber, $aImages, 'original' );
           try{
              $objResized = $objOriginal->CreateResizedImage( $strSize, $iNewWidth, $iNumber, $objOriginal->iTestimonial, $objFVTMain->iJPGQuality );
           }catch( Exception $ex ){
              $strInfo .= '<tr><td>!! '.$objResized->aImages[$strSize]['path'].'</td><td>'.$objResized->aImages[$strSize]['width'].'px &times;</td><td>'.$objResized->aImages[$strSize]['height'].'px</td></tr></table>'.$ex->getMessage();
              throw new Exception( $strInfo );
           }
           if( $bReturnInfo ) $strInfo .= '<tr><td>'.$objResized->aImages[$strSize]['path'].'</td><td>'.$objResized->aImages[$strSize]['width'].'px &times;</td><td>'.$objResized->aImages[$strSize]['height'].'px</td></tr>';
           unset( $objOriginal );
           $objOriginal = new FPTImage2(); 
         }
      }

      if( $bReturnInfo ) $strInfo .= '</table>';

      return $strInfo;
   }
   public function CheckExtension( $strExtension ){
      if( 'jpeg' == strtolower( $this->strType ) && 'jpg' == strtolower( $strExtension ) ) return true;
      if( 0 == strcasecmp( $this->strType, $strExtension ) ) return true;

      return false;
   }

   public function GetURI( $strSize = '' ){
      global $objFVTMain;

      if( !$strSize || $strSize == $this->strSize ) $strPath = $objFVTMain->strImageRoot.$this->strPath;
      else $strPath = $objFVTMain->strImageRoot . str_replace( '/'.$this->strSize.'/', '/'.$strSize.'/', $this->strPath );

      return ($this->strPath) ? $strPath : false;
   }
   

   public static function RecheckImagesExistence(){
      global $objFVTMain;
      $aImages = FPTImage2::GetImages();
      $upload_dir = wp_upload_dir();
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strPath = $upload_dir['basedir'].'/testimonials/';
      else $strPath = $_SERVER['DOCUMENT_ROOT'] . $objFVTMain->strImageRoot;
      $aReport = array( 'recreated' => array(), 'missing' => array() );
      foreach( $aImages as $objImage ){
         if( !file_exists( $strPath . $objImage->strPath ) ){
            $objOriginal = FPTImage2::GetImage( $objImage->iTestimonial, $objImage->iNumber, 'original', $aImages );

            try{
               if( file_exists( $strPath . $objOriginal->strPath ) ){
                  $objOriginal->CreateResizedImage( $objImage->strSize, $objFVTMain->aSizes[$objImage->strSize], $objImage->iNumber, $objImage->iTestimonial, $objFVTMain->iJPGQuality );
                  $aReport['recreated'][$objImage->id] = $objImage->strPath;
               }else 
                  $aReport['missing'][$objImage->id] = $objImage->strPath;

            }catch( Exception $ex ){
               $aReport['missing'][$objImage->id] = $objImage->strPath;
            }
         }
      }
      return $aReport;
   }



   private static function GetNextNumber( $idTestimonial ){
      global $wpdb;

      $objImage = new FPTImage2();
      $iMax = intval( $wpdb->get_var( "SELECT MAX(`number`) FROM `{$objImage->strTable}` WHERE `testimonial`=$idTestimonial" ) ) + 1;
      return $iMax;
   }


   public static function UploadPhoto( $idTestimonial, $aUploadedFile, $strCustomName, $strTitle, $bChange = false ){
      global $objFVTMain;
      
      $iNumber = FPTImage2::GetNextNumber( $idTestimonial );
      if( $bChange ) FPTImage2::RemoveImagesToTestimonial( $idTestimonial, $iNumber, false );

      $objOriginal = new FPTImage2();

      $strExtension = pathinfo( $aUploadedFile['name'] );
      $strExtension = strtolower( $strExtension['extension'] );

      $strSavePath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.'/original/';
      $strPath = ($strCustomName) ? $strCustomName : FPTMain::CreateSEOSlug( $strTitle );
      if( $iNumber != 1 ) $strPath .= '-' . $iNumber;
      $strPath .= '.' . $strExtension;

      if( !move_uploaded_file( $aUploadedFile['tmp_name'], $strSavePath.$strPath ) ) throw new Exception( "Error while moving uploaded file to it's new location: '".$strSavePath.$strPath."'" );

      $objOriginal->strPath = '/original/'.$strPath;
      $objOriginal->LoadSpecialData();

      if( !$objOriginal->CheckExtension( $strExtension ) ){
         $strExtension = $objOriginal->strType;
         if( 'jpeg' == strtolower( $strExtension ) ) $strExtension = 'jpg';
         $strSavePath .= $strPath;
         $strNewPath = $_SERVER['DOCUMENT_ROOT'].$objFVTMain->strImageRoot.'/original/';
         $strNewName = ($strCustomName) ? $strCustomName : $objOriginal->PrepareTitle( $strTitle );
         if( $iNumber != 1 ) $strNewName .=  '-' . $iNumber;
         $strNewName .= '.' . $strExtension;
         if( !rename( $strSavePath, $strNewPath.$strNewName ) ) throw new Exception( 'Error while renaming file with bad extension !' );

         $objOriginal->strPath = '/original/'.$strNewName;
         $objOriginal->LoadSpecialData();
      }

      $objOriginal->iTestimonial = intval( $idTestimonial );
      $objOriginal->strSize = 'original';
      $objOriginal->iWidth = intval( $objOriginal->aImageInfo[0] );
      $objOriginal->iHeight = intval( $objOriginal->aImageInfo[1] );
      $objOriginal->iNumber = intval( $iNumber );

      $objOriginal->SaveNewEntry();

      $aImagesIDs = array();
      foreach( $objFVTMain->aSizes as $strSize => $iWidth ){
         $objImage = $objOriginal->CreateResizedImage( $strSize, $iWidth, $iNumber, 0, $objFVTMain->iJPGQuality );
         $aImagesIDs[$strSize] = $objImage->id;
         unset( $objImage );
      }
      unset( $objOriginal );

      return $aImagesIDs;
   }

}

?>