<?php



class FPTImage {



   public $id = 0;

   public $iTestimonial = 0;

   public $strPath = '';

   public $iWidth = 0;

   public $iHeight = 0;

   public $strSize = '';



   public $strName = '';

   public $strExtension = '';

   public $strType = '';

   public $aImageInfo = array();



   public $strTable = '';



   public function __construct(){

      global $wpdb;

      $this->strTable = $wpdb->prefix . 'fpt_images';

   }







   private function UpdateResizedImage( $idTestimonial, $strPath, $iWidth, $iHeight ){

      global $wpdb;



      $strPath = $wpdb->escape( $strPath );

      $strSelect = "SELECT * FROM `{$this->strTable}` WHERE `path`='$strPath' AND `testimonial`=$idTestimonial";



      $objRow = $wpdb->get_row( $strSelect );

      if( !$objRow ) throw new Exception( "Recreating non-existing image '$strPath' !" );

      $objResized = new FPTImage();

      $objResized->LoadData( $objRow );



      $objResized->iWidth = $iWidth;

      $objResized->iHeight = $iHeight;

      if( !$objResized->Update() ) throw new Exception( "Unable to update changed file '$strPath' !" );



      return $objResized;

   }



   private function SaveResizedImage( $strPath, $iWidth, $iHeight, $strSize ){

      $objResizedImage = new FPTImage();



      $objResizedImage->iTestimonial = $this->iTestimonial;

      $objResizedImage->strPath = $strPath;

      $objResizedImage->iWidth = $iWidth;

      $objResizedImage->iHeight = $iHeight;

      $objResizedImage->strSize = $strSize;

      if( !$objResizedImage->SaveNewEntry() ) throw new Exception( 'Unable to save new image into database !' );



      $objResizedImage->LoadSpecialData();



      return $objResizedImage;

   }





   public function LoadData( $objRow, $bSpecialID = false ){

      if( $bSpecialID ) $this->id = $objRow->id_image;

      else $this->id = $objRow->id;



      $this->iTestimonial = $objRow->testimonial;

      $this->strPath = $objRow->path;

      $this->iWidth = $objRow->width;

      $this->iHeight = $objRow->height;

      $this->strSize = $objRow->type;



      $this->LoadSpecialData();

   }



   public function LoadSpecialData(){

      global $objFPTMain;



      $strRealPath = $_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.$this->strPath;
      $strSource = str_replace('//','/',$strSource);
      
      $aPathInfo = pathinfo( $strRealPath );

      $this->strName = substr( $aPathInfo['basename'], 0, strlen( $aPathInfo['basename'] ) - strlen( $aPathInfo['extension'] ) - 1 );

      $this->strExtension = $aPathInfo['extension'];



      if( !is_file( $strRealPath ) ) return;

      $this->aImageInfo = getimagesize( $strRealPath );

      $this->strType = substr( $this->aImageInfo['mime'], 6 );

   }



   public function CreateResizedImagePNG( $strSource, $strPath, $iWidth, $iHeight, $bTransform = true, $iColorsAtMax = 5000 ){

      $imgSource = imagecreatefrompng( $strSource );

      $imgDest = imagecreatetruecolor( $iWidth, $iHeight );



      imagealphablending( $imgDest, false );

      imagecopyresampled( $imgDest, $imgSource, 0, 0, 0, 0, $iWidth, $iHeight, $this->aImageInfo[0], $this->aImageInfo[1] );

      imagesavealpha( $imgDest, true );



      if( $bTransform ){

         if( !function_exists( 'FV_CountUniqueColors' ) ) require( FP_TESTIMONAL_ROOT . 'include/image-non-class.php' );

         $aColors = FV_CountUniqueColors( $imgDest, $iWidth, $iHeight, true );



         if( $aColors && $iColorsAtMax > $aColors['colors'] ){

            $bComplex = FV_IsComplex( $imgSource, $this->aImageInfo );

            if( !$bComplex ) FV_TrueColorToIndexed( $imgDest, $aColors );

         }

      }



      global $objFPTMain;

      imagepng( $imgDest, str_replace('//','/',$_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.$strPath), 9 );

      imagedestroy( $imgSource );

      imagedestroy( $imgDest );

   }



   public function CreateResizedImageGIF( $strSource, $strPath, $iWidth, $iHeight ){

      $imgSource = imagecreatefromgif( $strSource );

      $imgDest = imagecreatetruecolor( $iWidth, $iHeight );



      imagealphablending( $imgDest, false );

      imagecopyresampled( $imgDest, $imgSource, 0, 0, 0, 0, $iWidth, $iHeight, $this->aImageInfo[0], $this->aImageInfo[1] );

      imagesavealpha( $imgDest, true );



      global $objFPTMain;

      imagegif( $imgDest, str_replace('//','/',$_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.$strPath), 9 );

      imagedestroy( $imgSource );

      imagedestroy( $imgDest );

   }



   public function CreateResizedImageJPG( $strSource, $strPath, $iWidth, $iHeight, $iJPGQuality ){

      $imgSource = imagecreatefromjpeg( $strSource );

      $imgDest = imagecreatetruecolor( $iWidth, $iHeight );



      imagealphablending( $imgDest, false );

      imagecopyresampled( $imgDest, $imgSource, 0, 0, 0, 0, $iWidth, $iHeight, $this->aImageInfo[0], $this->aImageInfo[1] );

      imagesavealpha( $imgDest, true );



      global $objFPTMain;

      imagejpeg( $imgDest, str_replace('//','/',$_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.$strPath), $iJPGQuality );

      imagedestroy( $imgDest );

      imagedestroy( $imgSource );

   }



   public function CreateResizedImage( $strSize, $iDestWidth, $idTestimonial = 0, $iJPGQuality = 80 ){

      $iJPGQuality = intval( $iJPGQuality );

      if( $iJPGQuality < 1 || $iJPGQuality > 100 ) $iJPGQuality = 80;



      if( $iDestWidth > $this->aImageInfo[0] ) $iDestWidth = $this->aImageInfo[0];

      $ratio = $iDestWidth / $this->aImageInfo[0];
      $iWidth = intval( $this->aImageInfo[0] * $ratio );

      $iHeight = intval( $this->aImageInfo[1] * $ratio );

      $strFunctionLoad = 'imagecreatefrom' . $this->strType;

      $strFunctionSave = 'image' . $this->strType;

      if( !function_exists( $strFunctionLoad ) || !function_exists( $strFunctionSave ) ) throw new Exception( "Cannot handle type '{$this->strType}'" );



      global $objFPTMain;

      $strSource = $_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.$this->strPath;
      $strSource = str_replace('//','/',$strSource);
      $strPath = '/' . $strSize . '/' . $this->strName . '.' . $this->strExtension;



      if( 'png' == $this->strType ) $this->CreateResizedImagePNG( $strSource, $strPath, $iWidth, $iHeight );

      if( 'gif' == $this->strType ) $this->CreateResizedImageGIF( $strSource, $strPath, $iWidth, $iHeight );

      if( 'jpeg' == $this->strType || 'jpg' == $this->strType ) $this->CreateResizedImageJPG( $strSource, $strPath, $iWidth, $iHeight, $iJPGQuality );



      @chmod( str_replace('//','/',$_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.$strPath), octdec( '0'.'777' ) );

      if( $idTestimonial ) return $this->UpdateResizedImage( $idTestimonial, $strPath, $iWidth, $iHeight ); 

      else return $this->SaveResizedImage( $strPath, $iWidth, $iHeight, $strSize );

   }



   public function Update(){

      global $wpdb;



      $id = intval( $this->id );

      $idTestimonial = intval( $this->iTestimonial );

      $iWidth = intval( $this->iWidth );

      $iHeight = intval( $this->iHeight );

      $strPath = $wpdb->escape( $this->strPath );

      $strSize = $this->strSize;



      $strUpdate = "UPDATE `{$this->strTable}` SET `testimonial`=$idTestimonial, `path`='$strPath', `width`=$iWidth, `height`=$iHeight, `type`='$strSize' ";

      $strUpdate .= "WHERE `id`=$id";

      if( false === $wpdb->query( $strUpdate ) ) return false;



      return true;

   }



   public function SaveNewEntry(){

      global $wpdb;



      $idTestimonial = intval( $this->iTestimonial );

      $iWidth = intval( $this->iWidth );

      $iHeight = intval( $this->iHeight );

      $strPath = $wpdb->escape( $this->strPath );

      $strSize = $this->strSize;



      $strInsert = "INSERT INTO {$this->strTable} (`testimonial`,`path`,`width`,`height`,`type`) VALUE ($idTestimonial,'$strPath',$iWidth,$iHeight,'$strSize')";

      if( !$wpdb->query( $strInsert ) ) return false;



      $this->id = $wpdb->insert_id;

      return true;

   }



   public function CheckExtension( $strExtension ){

      if( 'jpeg' == strtolower( $this->strType ) && 'jpg' == strtolower( $strExtension ) ) return true;

      if( 0 == strcasecmp( $this->strType, $strExtension ) ) return true;



      return false;

   }



   public function Show( $bEcho = true ){

      if( !$this->strPath ) return '';



      global $objFPTMain;

      if( !$bEcho ) ob_start();



      $strPath = $objFPTMain->strImageRoot . $this->strPath;

      $strPath = str_replace('//','/',$strPath);

      $objImage = $this;

      include( FP_TESTIMONAL_ROOT . 'view/user/image.php' );



      if( !$bEcho ){ 

         $strImage = ob_get_contents();

         ob_end_clean();

         return $strImage;

      }

   }



   public function GetURI( $strSize = '' ){

      global $objFPTMain;



      if( !$strSize || $strSize == $this->strSize ) $strPath = $objFPTMain->strImageRoot.$this->strPath;

      else $strPath = $objFPTMain->strImageRoot . str_replace( '/'.$this->strSize.'/', '/'.$strSize.'/', $this->strPath );

      $strPath = str_replace('//','/',$strPath);

      return ($this->strPath) ? $strPath : false;

   }





   /**

    * Removes images assigned to Testimonial from database and from disk.

    *

    * @access public

    * @static

    * @param int $iTestimonial ID of testimonial

    */

   public static function RemoveImagesToTestimonial( $iTestimonial ){

      global $wpdb, $objFPTMain;



      $objImage = new FPTImage();

      $strSelect = "SELECT `path` FROM `{$objImage->strTable}` WHERE `testimonial`=$iTestimonial";

      $aPaths = $wpdb->get_results( $strSelect );

      if( !$aPaths ) return true;



      $strBasePath = $_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot;
      $strBasePath = str_replace('//','/',$strBasePath);
      
      foreach( $aPaths as $objPath ) @unlink( $strBasePath.$objPath->path );



      $strDelete = "DELETE FROM `{$objImage->strTable}` WHERE `testimonial`=$iTestimonial";

      if( !$wpdb->query( $strDelete ) ) return false;

      return true;

   }



   public static function RecheckImagesExistence(){

      global $objFPTMain;

      $aImages = FPTImage::GetImages();



      $strPath = $_SERVER['DOCUMENT_ROOT'] . $objFPTMain->strImageRoot;
      $strPath = str_replace('//','/',$strPath);

      $aReport = array( 'recreated' => array(), 'missing' => array() );

      foreach( $aImages as $objImage ){



         if( !file_exists( $strPath . $objImage->strPath ) ){

            $objOriginal = FPTImage::GetImage( $objImage->iTestimonial, 'original', $aImages );



            try{

               if( file_exists( $strPath . $objOriginal->strPath ) ){

                  $objOriginal->CreateResizedImage( $objImage->strSize, $objFPTMain->aSizes[$objImage->strSize], $objImage->iTestimonial, $objFPTMain->iJPGQuality );

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



   public static function RecreateToNewWidth( $strSize, $iWidth, $bReturnInfo = true ){

      global $wpdb;



      set_time_limit( 1000 );

      $objOriginal = new FPTImage();

      $strSelect = "SELECT * FROM `{$objOriginal->strTable}` WHERE `type`='original'";



      if( $bReturnInfo ) $strInfo = '<table class="recreateInfo">';



      $aRows = $wpdb->get_results( $strSelect );



      foreach( $aRows as $objRow ){

         $objOriginal->LoadData( $objRow );



         $objImage = $objOriginal->CreateResizedImage( $strSize, $iWidth, $objOriginal->iTestimonial, $objFPTMain->iJPGQuality );

         if( $bReturnInfo ) $strInfo .= '<tr><td>'.$objImage->id.'</td><td>'.$objImage->strPath.'</td><td>'.$objImage->iWidth.'</td><td>'.$objImage->iHeight.'</td></tr>';



         unset( $objOriginal, $objImage );

         $objOriginal = new FPTImage();

      }



      if( $bReturnInfo ) $strInfo .= '</table>';



      return $strInfo;

   }



   /**

    * Fetches all the images from database, loads them into FPTImage classes and returns the array with them

    *

    * @access private

    * @return array     Array of FPTImage classes loaded from DB

    */

   public static function GetImages(){

      global $wpdb;



      $objImage = new FPTImage();

      $aResults = $wpdb->get_results( "SELECT * FROM `{$objImage->strTable}` ORDER BY `testimonial`" );

      if( !is_array( $aResults ) ) throw new Exception( "Error while fetching testimonial images from database !" );



      $aImages = array();

      foreach( $aResults as $objRow ){

         $objImage = new FPTImage();

         $objImage->LoadData( $objRow );

         $aImages[] = $objImage;

      }



      return $aImages;

   }



   public static function GetImage( $id, $strSize = '', &$aImages = array() ){

      if( is_array( $aImages ) && 0 < count( $aImages ) ){

         foreach( $aImages as $objImage ){

            if( $id == $objImage->iTestimonial ){

               if( !$strSize ) return $objImage;

               elseif( $strSize == $objImage->strSize ) return $objImage;

            }

         }

      }



      global $wpdb;



      $objImage = new FPTImage();

      $strSelect = "SELECT * FROM `{$objImage->strTable}` WHERE ";

      if( $strSize ) $strSelect .= "`testimonial`=$id AND `type`='$strSize'";

      else $strSelect .= "`id`=$id";



      $objRow = $wpdb->get_row( $strSelect );

      if( !$objRow ) throw new Exception( "There is no image with ID: '$id' !" );

      $objImage->LoadData( $objRow );



      return $objImage;

   }



   public static function UploadPhoto( $idTestimonial, $aUploadedFile, $strCustomName, $strTitle, $bChange = false ){

      global $objFPTMain;



      if( $bChange ) FPTImage::RemoveImagesToTestimonial( $idTestimonial );



      $objOriginal = new FPTImage();



      $strExtension = pathinfo( $aUploadedFile['name'] );

      $strExtension = strtolower( $strExtension['extension'] );



      $strSavePath = $_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.'/original/';
      $strSavePath = str_replace('//','/',$strSavePath);

      $strPath = ($strCustomName) ? $strCustomName : FPTMain::CreateSEOSlug( $strTitle );

      $strPath .= '.' . $strExtension;



      if( !move_uploaded_file( $aUploadedFile['tmp_name'], $strSavePath.$strPath ) ) throw new Exception( "Error while moving uploaded file to it's new location: '".$strSavePath.$strPath."'" );



      $objOriginal->strPath = '/original/'.$strPath;

      $objOriginal->LoadSpecialData();



      if( !$objOriginal->CheckExtension( $strExtension ) ){

         $strExtension = $objOriginal->strType;

         if( 'jpeg' == strtolower( $strExtension ) ) $strExtension = 'jpg';

         $strSavePath .= $strPath;

         $strNewPath = $_SERVER['DOCUMENT_ROOT'].$objFPTMain->strImageRoot.'/original/';
         $strNewPath = str_replace('//','/',$strNewPath);

         $strNewName = ($strCustomName) ? $strCustomName : $objOriginal->PrepareTitle( $strTitle );

         $strNewName .= '.' . $strExtension;

         if( !rename( $strSavePath, $strNewPath.$strNewName ) ) throw new Exception( 'Error while renaming file with bad extension !' );



         $objOriginal->strPath = '/original/'.$strNewName;

         $objOriginal->LoadSpecialData();

      }



      $objOriginal->iTestimonial = intval( $idTestimonial );

      $objOriginal->strSize = 'original';

      $objOriginal->iWidth = intval( $objOriginal->aImageInfo[0] );

      $objOriginal->iHeight = intval( $objOriginal->aImageInfo[1] );



      $objOriginal->SaveNewEntry();

      $aImagesIDs = array();
      foreach( $objFPTMain->aSizes as $strSize => $iWidth ){

         $objImage = $objOriginal->CreateResizedImage( $strSize, $iWidth, 0, $objFPTMain->iJPGQuality );

         $aImagesIDs[$strSize] = $objImage->id;

         unset( $objImage );

      }

      unset( $objOriginal );



      return $aImagesIDs;

   }



}



?>