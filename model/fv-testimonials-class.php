<?php

require_once( FVTESTIMONIALS_ROOT . '/model/image-class.php' );

class FV_Testimonials
{
   public $strUrl = '';
   public $iWidthLarge = 1024;
   public $iWidthMedium = 500;
   public $iWidthSmall = 150;
   public $iWidthThumbs = 50;
   public $strImageRoot = '';
   public $iJPGQuality = 90;
   public $bOutputCSS = true;
   public $bUseTexy = false;
   public $aTemplates = array();
   public $aImages = array();
   public $aCategories = array();
   public $aOrder = array();

   public $strDatabaseVersion = '';

   public $aSizes = array();
   public $aAllSizes = array( 'original', 'large', 'medium', 'small', 'thumbs' );
   public $strShowFull = 'medium';
   public $strShow = 'small';

   public $fptCategory = null;

   public $strMessage = '';

   const OPTION_URL = 'FPT_url';
   const OPTION_LARGE = 'FPT_width_large';
   const OPTION_MEDIUM = 'FPT_width_medium';
   const OPTION_SMALL = 'FPT_width_small';
   const OPTION_IMAGES = 'FPT_images_root';
   const OPTION_JPG = 'FPT_jpg_quality';
   const OPTION_CSS = 'FPT_output_css';
   const OPTION_TEXY = 'FPT_use_texy';
   const OPTION_TEMPLATES = 'FPT_templates';
   const OPTION_DATABASE = 'FPT_database';

	/**
	 * Constructor.
	 */
	function FV_Testimonials()
	{
      $this->strUrl = strval( get_option( self::OPTION_URL ) );
      $this->iWidthLarge = intval( get_option( self::OPTION_LARGE ) );
      $this->iWidthMedium = intval( get_option( self::OPTION_MEDIUM ) );
      $this->iWidthSmall = intval( get_option( self::OPTION_SMALL ) );
      $this->strImageRoot = strval( get_option( self::OPTION_IMAGES ) );
//      if (!$this->strImageRoot) $this->strImageRoot =
      $this->iJPGQuality = intval( get_option( self::OPTION_JPG ) );
      $this->bOutputCSS = ('no' == strval( get_option( self::OPTION_CSS ) )) ? false : true;
//      $this->bUseTexy = ('yes' == strval( get_option( self::OPTION_TEXY ) )) ? true : false;
      $this->aTemplates =  get_option( self::OPTION_TEMPLATES ) ;
      if (is_serialized( $this->aTemplates )) $this->aTemplates = unserialize($this->aTemplates);
      if( !is_array( $this->aTemplates ) ) $this->aTemplates = array();
      
      $args = array(); $args = array( 'taxonomy'=>'testimonial_category' );
      $this->aCategories = get_terms( 'testimonial_category' );
      $this->aOrder = get_option( '_fvt_order' );

      $this->strDatabaseVersion = 0;//strval( get_option( self::OPTION_DATABASE ) );

      $this->CheckOptions();      
      $this->aSizes['large'] = $this->iWidthLarge;
      $this->aSizes['medium'] = $this->iWidthMedium;
      $this->aSizes['small'] = $this->iWidthSmall;
      $this->aSizes['thumbs'] = $this->iWidthThumbs;
      
	}
	
	public function CheckOptions(){
      if( !$this->iWidthLarge ) $this->iWidthLarge = 1024;
      if( !$this->iWidthMedium ) $this->iWidthMedium = 300;
      if( !$this->iWidthSmall ) $this->iWidthSmall = 150;
      if( !$this->iWidthThumbs ) $this->iWidthThumbs = 50;

      if( !$this->strImageRoot ) $this->strImageRoot = '/';
      if( 0 >= $this->iJPGQuality || 100 < $this->iJPGQuality ) $this->iJPGQuality = 90;
   }
   
   public function show_testimionials_all(){
      global $post;
      $old_post = $post;
      
      $args = array('post_type' => 'testimonial');
      $posts_array = get_posts( $args );
      $output = '';
       $upload_dir = wp_upload_dir();
         if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strImagePath = str_replace($_SERVER['DOCUMENT_ROOT'],'',$upload_dir['basedir']).'/testimonials';
         else $strImagePath =  $this->strImageRoot;
      
      foreach( $posts_array as $post ) :	setup_postdata($post); 
        $aImages = get_post_meta($post->ID, '_fvt_main_image',true);
	     $output .= '<div class="clsTestimonial">
                      <a name="'.$post->slug.'"><h2>'.get_the_title().'</h2></a>';
        if ($aImages) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages['medium']['path'].'" /><br />'.get_the_title().'</h5>';
        $output .='<div class="clsFPTContent">'.get_the_content().'</div>
                   </div>';
      endforeach; 
      $post  = $old_post;
      setup_postdata($post);
      return $output;
   }
                                  //$category,       $limit,      $template,    $featured, $   image,            $include,      $exclude,      $offset,      $show,      $length
   public function show_testimonials($category = '', $iLimit = 0, $template = 0,$image = 'medium', $include = '', $exclude = '', $offset = 0, $show = '', $length=''){
      global $post;
      $old_post = $post;
	        
      $strOutput = '';
      $aExclude = explode(',', $exclude );
      foreach($aExclude as $i=>$e) $aExclude[$i] = (int)$e;
      $aInclude = explode(',', $include );
      foreach($aInclude as $i=>$e) $aInclude[$i] = (int)$e;
      if($include && !$aInclude) $aInclude[] = (int)$include;
      
     
      $args = array('post_type' => 'testimonial');
      if ((!empty( $category )) || ($show == 'all') || ($show == 'featured')){
         if ( $category ){ 
            $aCategories = explode(',',$category);
            $aCatSlugs = array();
            foreach($aCategories as $catID){ 
               $cat = get_term_by('slug', $catID, 'testimonial_category' ); 
               if ( !$cat ) $cat = get_term_by('name', $catID, 'testimonial_category' );
               if ( !$cat ) $cat = get_term_by('id', (int)$catID, 'testimonial_category' );
               if ( $cat ) $aCatSlugs[] = $cat->slug;
            }
            if ( $aCatSlugs ) $args['tax_query'] = array(array('taxonomy' => 'testimonial_category','field' => 'slug','terms' => $aCatSlugs));
         }
         $aCustomOrder = array();
         if ( ($show == 'all')||($show == 'featured') || !empty($include) || ( count($aCategories) > 1 ) ) $aCustomOrder = $this->aOrder[0];
         else $aCustomOrder = $this->aOrder[$aCategories[0]];
         if (!$aCustomOrder) $aCustomOrder = $this->aOrder[0];
         $args['post_status'] = 'publish';
         if ($aExclude)               
            $args['post__not_in'] = $aExclude;
         if ($show == 'featured'){   
            $args['meta_key'] = '_fvt_featured'; 
            $args['meta_value'] = '1'; 
         }
         $args['posts_per_page'] = -1;
         //        if ($args['customorder']) add_filter( 'posts_orderby', 'fvt_filter_orderby',10,2);  // this doesn't work very well, especially if we have draft testimonial
        $post_query = new WP_Query($args);
         //      remove_filter( 'posts_orderby',  'fvt_filter_orderby' );
        
        $upload_dir = wp_upload_dir();
        if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) {
          $strImagePath = str_replace($upload_dir['subdir'],'',$upload_dir['url']).'/testimonials';
        }
        else $strImagePath =  $this->strImageRoot;
      
        $aOutputs = array();
        if( $post_query->have_posts() ) {
        	//prepare taxamony for all testimonies
        	if ($template) {
        		$testim_object_ids = array();
				foreach ($post_query->posts as $value) {
					if (($value->post_status == 'publish')&&($value->post_type == 'testimonial')) {
						$testim_object_ids[] = $value->ID;
					}
				}
        		$testimonyTaxamony = wp_get_object_terms($testim_object_ids,'testimonial_category',array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all_with_object_id'));
			} 
           while ($post_query->have_posts()) : $post_query->the_post();
             if (($post->post_status == 'publish')&&($post->post_type == 'testimonial')) { 
                $output = '';
                $iPid = get_the_ID(); 
                $aImages = get_post_meta($iPid, '_fvt_images',true);
                $slug = basename(get_permalink());
                if ($template) 	$output = $this->ParseTemplate(stripslashes($this->aTemplates[$template]['content']), $aImages, $image, $testimonyTaxamony) . $strOutput;
				else{
          	      $output .= '<div class="clsTestimonial"><h2><a name="'.$slug.'">'.get_the_title().'</a></h2>';
          	      $strImageTitle = get_the_title();
   

          	      if ($aImages[1] && $aImages[1]['original']['name']) $strImageTitle = $aImages[1]['original']['name'];
          	      else if ($aImages[2] && $aImages[2]['original']['name']) $strImageTitle = $aImages[2]['original']['name'];
                  if ($aImages[1]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[1][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                  else if ($aImages[2]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[2][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                  $output .= '<div class="clsFPTContent">';
                  if ('excerpt' == $length) $output .= get_the_excerpt();
                  else $output .= get_the_content();
                  $output .= '</div><div style="clear: both"></div>
                             </div>';
               }
               $iIndex = false;
               if (!$aCustomOrder) $aCustomOrder = array();
               if (!empty($aCustomOrder)) $iIndex = array_search($iPid, $aCustomOrder);
               if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder) ) $iIndex = max(max(array_keys($aCustomOrder)),(count($aOutputs)>0? max(array_keys($aOutputs)):0))+1;
               if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder) && !empty($aCustomOrder) ) $iIndex = max(max(array_keys($aCustomOrder)),(count($aOutputs)>0? max(array_keys($aOutputs)):0))+1;
               else if (($iIndex  === false) && !empty($aOutputs)) $iIndex = max(array_keys($aOutputs))+1;
               else if ($iIndex  === false) $iIndex = 0;

               $aOutputs[$iIndex] = $output;
             }
           endwhile;
        }
      }
	
      if ($aInclude){ 
         $post_query = new WP_Query( array( 'post_type' => 'testimonial', 'post__in' => $aInclude ) );
         
         if( $post_query->have_posts() ) {
         	//prepare taxamony for all testimonies
        	if ($template) {
        		$testim_object_ids = array();
				foreach ($post_query->posts as $value) {
					if (($value->post_status == 'publish')&&($value->post_type == 'testimonial')) {
						$testim_object_ids[] = $value->ID;
					}
				}
        		$testimonyTaxamony = wp_get_object_terms($testim_object_ids,'testimonial_category',array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all_with_object_id'));
			} 
            while ($post_query->have_posts()) : $post_query->the_post(); 
              $output = '';
              $iPid = get_the_ID();
              
              $aImages = get_post_meta($iPid, '_fvt_images',true);
              $slug = basename(get_permalink());
              if ($template) $output .= $this->ParseTemplate(stripslashes($this->aTemplates[$template]['content']), $aImages, $image, $testimonyTaxamony);
              else{
         	     $output .= '<div class="clsTestimonial">
                               <h2><a name="'.$slug.'">'.get_the_title().'</a></h2>';
          	      $strImageTitle = get_the_title();
          	      if ($aImages[1] && $aImages[1]['original']['name']) $strImageTitle = $aImages[1]['original']['name'];
          	      else if ($aImages[2] && $aImages[2]['original']['name']) $strImageTitle = $aImages[2]['original']['name'];
                 if ($aImages[1]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[1][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                 else if ($aImages[2]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[2][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                 $output .= '<div class="clsFPTContent">';
                 if ('excerpt' == $length) $output .= get_the_excerpt();
                 else $output .= apply_filters('the_content',get_the_content());
                 $output .= '</div><div style="clear: both"></div>
                            </div>';
                
              }
               $iIndex = false;

               if( !$aCustomOrder )
                  $aCustomOrder = array();

               if( !empty( $aCustomOrder ) )
                  $iIndex = array_search($iPid, $aCustomOrder);

//                if( ( $iIndex  === false ) && is_array( $aOutputs ) && is_array( $aCustomOrder ) ) {
//                   $iIndex = max( max( array_keys( $aCustomOrder ) ), max( array_keys( $aOutputs ) ) ) + 1;
//                }
/// kajo quickfix 20130903 because of errors
/// ( the disgusting code that follows was already here, I just improved the conditions )

               if( ( $iIndex  === false ) && is_array( $aOutputs ) && is_array( $aCustomOrder ) && !empty( $aCustomOrder ) && !empty( $aOutputs ) ) {
                  $iIndex = max( max( array_keys( $aCustomOrder ) ), max( array_keys( $aOutputs ) ) ) + 1;
               } elseif( ( $iIndex  === false ) && is_array( $aCustomOrder ) && !empty( $aCustomOrder ) && ( !is_array( $aOutputs ) || empty( $aOutputs ) ) ) {
                  $iIndex = max( array_keys( $aCustomOrder ) ) + 1;
               } elseif( ( $iIndex  === false ) && ( !is_array( $aCustomOrder ) || empty( $aCustomOrder ) ) && is_array( $aOutputs )  && !empty( $aOutputs ) )  {
                  $iIndex = max( array_keys( $aOutputs ) ) + 1;
               } elseif( $iIndex  === false ) {
                  $iIndex = 0;
               }

               $aOutputs[$iIndex] = $output;

            endwhile;
         }         
      }
      if ( $aOutputs ) ksort($aOutputs); // reorder testimonials according to the custom order!
      if ( $iLimit && ($iLimit > 0) ) $iCount = $iLimit;
      if ( $aOutputs ) 
      foreach( $aOutputs as $out ){ 
         if ( ($iLimit == 0) || ($iCount > 0) ) $strOutput .= $out;
         else {}//continue;
         if ( $iLimit > 0) $iCount--;
      }
      $post  = $old_post;
      setup_postdata($post);

      return $strOutput;
   }/**/
   
   
      public function GetTestimonials($category = '', $iLimit = 0, $template = 0,$image = 'medium', $include = '', $exclude = '', $offset = 0, $show = '', $length='', $echo = true){
      global $post;
      $old_post = $post;
      $objTestimonials = new FV_Testimonials();
      $strOutput = '';
      $aExclude = explode(',', $exclude );
      foreach($aExclude as $i=>$e) $aExclude[$i] = (int)$e;
      $aInclude = explode(',', $include );
      foreach($aInclude as $i=>$e) $aInclude[$i] = (int)$e;
      if($include && !$aInclude) $aInclude[] = (int)$include;
      
     
      $args = array('post_type' => 'testimonial');
      if ((!empty( $category )) || ($show == 'all') || ($show == 'featured')){
         if ( $category ){ 
            $aCategories = explode(',',$category);
            $aCatSlugs = array();
            foreach($aCategories as $catID){ 
               $cat = get_term_by('slug', $catID, 'testimonial_category' ); 
               if ( !$cat ) $cat = get_term_by('name', $catID, 'testimonial_category' );
               if ( !$cat ) $cat = get_term_by('id', (int)$catID, 'testimonial_category' );
               if ( $cat ) $aCatSlugs[] = $cat->slug;
            }
            if ( $aCatSlugs ) $args['tax_query'] = array(array('taxonomy' => 'testimonial_category','field' => 'slug','terms' => $aCatSlugs));
         }
         $aCustomOrder = array();
         if ( ($show == 'all')||($show == 'featured') || !empty($include) || ( count($aCategories) > 1 ) ) $aCustomOrder = $objTestimonials->aOrder[0];
         else $aCustomOrder = $objTestimonials->aOrder[$aCategories[0]];
         if (!$aCustomOrder) $aCustomOrder = $objTestimonials->aOrder[0];
         $args['post_status'] = 'publish';
         if ($aExclude)               
            $args['post__not_in'] = $aExclude;
         if ($show == 'featured'){   
            $args['meta_key'] = '_fvt_featured'; 
            $args['meta_value'] = '1'; 
         }
         $args['posts_per_page'] = -1;
         //        if ($args['customorder']) add_filter( 'posts_orderby', 'fvt_filter_orderby',10,2);  // this doesn't work very well, especially if we have draft testimonial
        $post_query = new WP_Query($args);
         //      remove_filter( 'posts_orderby',  'fvt_filter_orderby' );
        
        $upload_dir = wp_upload_dir();
        if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) {
          $strImagePath = str_replace($upload_dir['subdir'],'',$upload_dir['url']).'/testimonials';
        }
        else $strImagePath =  $objTestimonials->strImageRoot;
      
        $aOutputs = array();
        if( $post_query->have_posts() ) {
           //prepare taxamony for all testimonies
        	if ($template) {
        		$testim_object_ids = array();
				foreach ($post_query->posts as $value) {
					if (($value->post_status == 'publish')&&($value->post_type == 'testimonial')) {
						$testim_object_ids[] = $value->ID;
					}
				}
        		$testimonyTaxamony = wp_get_object_terms($testim_object_ids,'testimonial_category',array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all_with_object_id'));
			} 
           while ($post_query->have_posts()) : $post_query->the_post();
             if (($post->post_status == 'publish')&&($post->post_type == 'testimonial')) { 
                $output = '';
                $iPid = get_the_ID(); 
                $aImages = get_post_meta($iPid, '_fvt_images',true);
                $slug = basename(get_permalink());
                if ($template) $output = $objTestimonials->ParseTemplate(stripslashes($objTestimonials->aTemplates[$template]['content']), $aImages, $image, $testimonyTaxamony) . $strOutput;
                else{
          	      $output .= '<div class="clsTestimonial"><h2><a name="'.$slug.'">'.get_the_title().'</a></h2>';
          	      $strImageTitle = get_the_title();
   

          	      if ($aImages[1] && $aImages[1]['original']['name']) $strImageTitle = $aImages[1]['original']['name'];
          	      else if ($aImages[2] && $aImages[2]['original']['name']) $strImageTitle = $aImages[2]['original']['name'];
                  if ($aImages[1]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[1][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                  else if ($aImages[2]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[2][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                  $output .= '<div class="clsFPTContent">';
                  if ('excerpt' == $length) $output .= get_the_excerpt();
                  else $output .= get_the_content();
                  $output .= '</div><div style="clear: both"></div>
                             </div>';
               }
               $iIndex = false;
               if (!$aCustomOrder) $aCustomOrder = array();
               if (!empty($aCustomOrder)) $iIndex = array_search($iPid, $aCustomOrder);
               if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
               if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder) && !empty($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
               else if (($iIndex  === false) && !empty($aOutputs)) $iIndex = max(array_keys($aOutputs))+1;
               else if ($iIndex  === false) $iIndex = 0;
                /*
               if (!empty($aCustomOrder)) $iIndex = array_search($iPid, $aCustomOrder);
               if (!$aCustomOrder) $aCustomOrder = array();
               if (($iIndex !== 0) && (!$iIndex) && is_array($aOutputs)&& is_array($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
*/
               $aOutputs[$iIndex] = $output;
             }
           endwhile;
        }
      }
      if ($aInclude){ 
         $post_query = new WP_Query( array( 'post_type' => 'testimonial', 'post__in' => $aInclude ) );
         if( $post_query->have_posts() ) {
         	//prepare taxamony for all testimonies
        	if ($template) {
        		$testim_object_ids = array();
				foreach ($post_query->posts as $value) {
					if (($value->post_status == 'publish')&&($value->post_type == 'testimonial')) {
						$testim_object_ids[] = $value->ID;
					}
				}
        		$testimonyTaxamony = wp_get_object_terms($testim_object_ids,'testimonial_category',array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all_with_object_id'));
			} 
            while ($post_query->have_posts()) : $post_query->the_post(); 
              $output = '';
              $iPid = get_the_ID();
              
              $aImages = get_post_meta($iPid, '_fvt_images',true);
              $slug = basename(get_permalink());
              if ($template) $output .= $this->ParseTemplate(stripslashes($objTestimonials->aTemplates[$template]['content']), $aImages, $image, $testimonyTaxamony);
              else{
         	     $output .= '<div class="clsTestimonial">
                               <h2><a name="'.$slug.'">'.get_the_title().'</a></h2>';
          	      $strImageTitle = get_the_title();
          	      if ($aImages[1] && $aImages[1]['original']['name']) $strImageTitle = $aImages[1]['original']['name'];
          	      else if ($aImages[2] && $aImages[2]['original']['name']) $strImageTitle = $aImages[2]['original']['name'];
                 if ($aImages[1]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[1][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                 else if ($aImages[2]) $output .= '<h5 class="left"><img src="'.$strImagePath.$aImages[2][$image]['path'].'" /><br />'.$strImageTitle.'</h5>';
                 $output .= '<div class="clsFPTContent">';
                 if ('excerpt' == $length) $output .= get_the_excerpt();
                 else $output .= apply_filters('the_content',get_the_content());
                 $output .= '</div><div style="clear: both"></div>
                            </div>';
                
              }
               $iIndex = false;
               if (!$aCustomOrder) $aCustomOrder = array();
               if (!empty($aCustomOrder)) $iIndex = array_search($iPid, $aCustomOrder);
               if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
               if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder) && !empty($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
               else if (($iIndex  === false) && !empty($aOutputs)) $iIndex = max(array_keys($aOutputs))+1;
               else if ($iIndex  === false) $iIndex = 0;
               $aOutputs[$iIndex] = $output;
            endwhile;
         }         
      }
      if ( $aOutputs ) ksort($aOutputs); // reorder testimonials according to the custom order!
      if ( $iLimit && ($iLimit > 0) ) $iCount = $iLimit;
      if ( $aOutputs ) 
      foreach( $aOutputs as $out ){ 
         if ( ($iLimit == 0) || ($iCount > 0) ) $strOutput .= $out;
         else {}//continue;
         if ( $iLimit > 0) $iCount--;
      }
      $post  = $old_post;
      setup_postdata($post);
      
      if ($echo === false) return $aOutputs;
      return $strOutput;
   }/**/
   
   private function ParseTemplate( $strTemplate, $aImages, $strSize = 'original' , $testimonyTaxamony = null){
      global $post;

      $strTitle = get_the_title();
      $strContent = apply_filters('the_content',get_the_content());
      $strExcerpt = get_the_excerpt();
      $tags = get_the_terms( $post->ID, 'testimonial_tag' );
      $strTags = '';
      if ($tags) foreach($tags as $tag) $strTags .= $tag->name . ', '; 
      $aImages = get_post_meta($post->ID, '_fvt_images',true);
      
      $upload_dir = wp_upload_dir();
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) {
        $strImagePath = str_replace($upload_dir['subdir'],'',$upload_dir['url']).'/testimonials';
      }
      else $strImagePath =  $this->strImageRoot;

      if( $aImages && count( $aImages ) ){
         $strTemplate = preg_replace( '/\[no-images\][\s\S]*\[end-no-images\]/imU', '', $strTemplate );
      }else{
         $strTemplate = preg_replace( '/\[image\][\s\S]*\[end-image\]/imU', '', $strTemplate );
         $strTemplate = preg_replace( '/\[image\][\s\S]*\[\/image\]/imU', '', $strTemplate );
      }
      
      $strTemplate = preg_replace( '/\[title\]/i', $strTitle, $strTemplate );
      $strTemplate = preg_replace( '/\[title\s\,\]/i', implode( '<br />', explode( ',', $strTitle ) ), $strTemplate );
      $strTemplate = preg_replace( '/\[excerpt\]/i', $strExcerpt, $strTemplate );

      $strTemplate = preg_replace( '/\[content\]/i', $strContent, $strTemplate );
      $strTemplate = preg_replace( '/\[slug\]/i', $post->post_name, $strTemplate );//post_name
      $strTemplate = preg_replace( '/\[tags\]/i', $strTags, $strTemplate );
      $strTemplate = preg_replace( '/\[link\]/i', $this->strUrl.'#'.$post->post_name, $strTemplate );

      if( 'yes' == $this->strFeatured ){
         $strTemplate = preg_replace( '/\[not-featured\][\s\S]*\[end-not-featured\]/imU', '', $strTemplate );
         $strTemplate = preg_replace( '/\[featured\]/i', '', $strTemplate );
         $strTemplate = preg_replace( '/\[end-featured\]/i', '', $strTemplate );
      }else{
         $strTemplate = preg_replace( '/\[featured\][\s\S]*\[end-featured\]/imU', '', $strTemplate );
         $strTemplate = preg_replace( '/\[not-featured\]/i', '', $strTemplate );
         $strTemplate = preg_replace( '/\[end-not-featured\]/i', '', $strTemplate );
      }

      if( 'yes' == $this->strLightbox ){
         $strTemplate = preg_replace( '/\[no-lightbox\][\s\S]*\[end-no-lightbox\]/imU', '', $strTemplate );
         $strTemplate = preg_replace( '/\[lightbox\]/i', '', $strTemplate );
         $strTemplate = preg_replace( '/\[end-lightbox\]/i', '', $strTemplate );
      }else{
         $strTemplate = preg_replace( '/\[lightbox\][\s\S]*\[end-lightbox\]/imU', '', $strTemplate );
         $strTemplate = preg_replace( '/\[no-lightbox\]/i', '', $strTemplate );
         $strTemplate = preg_replace( '/\[end-no-lightbox\]/i', '', $strTemplate );
      }


      if (preg_match('/\[category\-slug\]/',$strTemplate, $matches)){
         //$t_categories = wp_get_post_terms($post->ID,'testimonial_category');
         foreach ($testimonyTaxamony as $key => $testimonyTaxamonySingle) {
            if($post->ID == $testimonyTaxamonySingle->object_id) {
               $t_categories[] = $testimonyTaxamonySingle;
               break;
            }
         }

         if (isset($t_categories[0]->slug)) $strTemplate = preg_replace( '/\[category\-slug\]/i', $t_categories[0]->slug, $strTemplate );
         else $strTemplate = preg_replace( '/\[category\-slug\]/i', '', $strTemplate );/**/
      }
      if (preg_match('/\[custom\-field\s*([\S]*?)\]/',$strTemplate, $matches)){
         $strCustomField = get_post_meta($post->ID, $matches[1],true);
         if ($strCustomField) $strTemplate = preg_replace( '/\[custom\-field\s*'.$matches[1].'\]/i', $strCustomField, $strTemplate );
         else $strTemplate = preg_replace( '/\[custom\-field\s*'.$matches[1].'\]/i', '', $strTemplate );
      }
/*
      if( $this->iCategory ){
         try{
            $objCat = FPTCategory::GetCategory( $this->iCategory );
            $strTemplate = preg_replace( '/\[category\]/i', $objCat->strName, $strTemplate );
         }catch( Exception $ex ){}
      }*/

      if( $aImages && count( $aImages ) ){

         // $strTemplate = preg_replace( '/\[no-images\](.*\n*\f*\r*)*\[end-no-images\]/imU', '', $strTemplate );

         $aMatches = array();
         preg_match_all( '/\[(\d+-)?image\](.*)\[end-(?:\d+-)?image\]/imsU', $strTemplate, $aMatches, PREG_SET_ORDER );
         if (!$aMatches)
            preg_match_all( '/\[(\d+-)?image\](.*)\[\/(\d+-)?image\]/imsU', $strTemplate, $aMatches, PREG_SET_ORDER );
         if (!$aMatches)
            preg_match_all( '/\[image-(\d+)?\](.*)\[\/image(?:-\d+)?\]/imsU', $strTemplate, $aMatches, PREG_SET_ORDER );

         foreach( $aMatches as $aTextImage ){
            $i = intval( $aTextImage[1] );
            if (!$i) $i = 1;
            if( !isset( $aImages[$i] ) ) continue;

            $strText = $aTextImage[2];

            $aSubMatches = array();
         
            if( preg_match( '/\[image\-link(?:\s+)(.+)\]/iU', $strText, $aSubMatches ) ){
               ///$strReplace = '';//$aImages[$i]->GetURI( $aSubMatches[1] );
               $strReplace = $strImagePath.$aImages[$i][$aSubMatches[1]]['path'];           
               $strText = preg_replace( '/\[image\-link(?:\s+)(.+)\]/iU', $strReplace, $strText );
            }

            $strText = preg_replace( '/\[image\-path\]/i', $strImagePath.$aImages[$i][$strSize]['path'], $strText );
            if ($aImages[$i]['original']['name']) $strImageName = $aImages[$i]['original']['name'];
            else $strImageName = $strTitle;
            $strText = preg_replace( '/\[image\-name\]/i', $strImageName, $strText );
            $strText = preg_replace( '/\[image\-height\]/i', $aImages[$i][$strSize]['height'], $strText );
            $strText = preg_replace( '/\[image\-width\]/i', $aImages[$i][$strSize]['width'], $strText );

            if( 1 == $i ){
               $strTemplate = preg_replace( '~\[image\](.*)\[end-image\]~imsU', $strText, $strTemplate, 1 );
               $strTemplate = preg_replace( '~\[image\](.*)\[/image\]~imsU', $strText, $strTemplate, 1 );
            }else{
               $strTemplate = preg_replace( '/\['.$i.'-image\](.*)\[end-'.$i.'-image\]/imsU', $strText, $strTemplate, 1 );
               $strTemplate = preg_replace( '/\[image-'.$i.'\](.*)\[\/image-'.$i.'\]/imsU', $strText, $strTemplate, 1 );
            }
         }

      }
      
      // clean up everything that rest there even after previus replacement (there's that continue)
      {
         $strTemplate = preg_replace( '/\[(\d+-)?image\](.*)\[end-(\d+-)?image\]/imsU', '', $strTemplate );
         //$strTemplate = preg_replace( '/\[image(-\d+)?\](.*\n*\f*\r*)*\[\/image(-\d+)?\]/imU', '', $strTemplate );
         $strTemplate = preg_replace( '/\[no-images\]/i', '', $strTemplate );
         $strTemplate = preg_replace( '/\[end-no-images\]/i', '', $strTemplate );
      }

      return $strTemplate;
   }

   public function FVT_SaveAndLoadData( $objWP ){

      try{
         //if( isset( $_POST['uploadTestimonial'] ) && $_POST['tboxTitle'] && $_POST['txtText'] ) $this->UploadTestimonial();

         if( is_admin() && false !== strpos( $_SERVER['REQUEST_URI'], 'edit.php?post_type=testimonial&page=fv-testimonial-options2' ) ){
            if( isset( $_POST['cmdSaveBasic'] ) || isset( $_POST['chmod-images'] ) || isset( $_POST['recheck-images'] ) || isset( $_POST['restore-order'] ) ) $this->SaveOptions();
            if( isset( $_POST['convert-categories'] )) $this->SaveOptions();

/*            if( isset( $_GET['ajax'] ) ) $this->HandleAjax();
            if( isset( $_POST['addTestimonial'] ) ) $this->UploadTestimonial( false );
            if( isset( $_POST['order'] ) && 'no' != $_POST['order'] ) $this->SaveOrder();
            if( isset( $_POST['featured'] ) && 'no' != $_POST['featured'] ) $this->SaveFeatured();
            if( isset( $_POST['delete'] ) && 'no' != $_POST['delete'] ) $this->RemoveTestimonial();
            if( isset( $_POST['cmdSaveTestimonial'] ) ) $this->SaveTestimonial();
            if( isset( $_POST['cmdSubmitCategory'] ) ) $this->InsertCategory();
            if( isset( $_POST['edit-category'] ) ) $this->UpdateCategory();
            if( isset( $_POST['delete_cat'] ) && 'no' != $_POST['delete_cat'] ) $this->DeleteCategory();
            if( isset( $_POST['add-template'] ) ) $this->AddTemplate();
            if( isset( $_POST['edit-template'] ) ) $this->UpdateTemplate();
            if( isset( $_POST['delete_temp'] ) && 'no' != $_POST['delete_temp'] ) $this->DeleteTemplate();*/
         }

      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
      }
   }
   
    private function SaveOptions(){
      global $wpdb;

      try{
         if( isset( $_POST['chmod-images'] ) ){
            $this->ChmodFiles( $_SERVER['DOCUMENT_ROOT'] . $this->strImageRoot, 'jpg|jpeg|png|bmp|gif|tif|tiff' );
            $this->strMessage .= '<p>Images prepared for FTP management !</p>';
            return;
         }
         if( isset( $_POST['recheck-images'] ) ){
            $aReport = FPTImage2::RecheckImagesExistence();
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
      }catch( Exception $ex ){
         $this->strMessage .= '<p>'.$ex->getMessage().'</p>';
         return;
      }

      
      $this->strUrl = preg_replace( '/\/\//', '/', preg_replace( '/\/$/', '', strval( $_POST['tboxTestimonialPage'] ) ) );
      if( '/' != $this->strUrl[0] ) $this->strUrl = '/' . $this->strUrl;

      if ($_POST['tboxImageRoot']){
         $this->strImageRoot = preg_replace( '/\/\//', '/', preg_replace( '/\/$/', '', strval( $_POST['tboxImageRoot'] ) ) );
         if( '/' != $this->strImageRoot[0] ) $this->strImageRoot = '/' . $this->strImageRoot;
      }

      $this->iWidthLarge = intval( $_POST['tboxLarge'] );
      $this->iWidthMedium = intval( $_POST['tboxMedium'] );
      $this->iWidthSmall = intval( $_POST['tboxSmall'] );
      $this->iJPGQuality = intval( $_POST['tboxJPG'] );
      $this->bOutputCSS = (isset( $_POST['chkCSS'] )) ? true : false;

      $this->CheckOptions();
      if( $this->CheckFolders() ) $this->UpdateOption( self::OPTION_IMAGES, $this->strImageRoot );
      else $this->strImageRoot = get_option( self::OPTION_IMAGES );
   
      if( get_option( self::OPTION_LARGE ) != $this->iWidthLarge )
         $this->strMessage .= '<p>LARGE Images recreated to new width:</p>'.FPTImage2::RecreateToNewWidth( 'large', $this->iWidthLarge );
      if( get_option( self::OPTION_MEDIUM ) != $this->iWidthMedium )
         $this->strMessage .= '<p>MEDIUM Images recreated to new width:</p>'.FPTImage2::RecreateToNewWidth( 'medium', $this->iWidthMedium );
      if( get_option( self::OPTION_SMALL ) != $this->iWidthSmall )
         $this->strMessage .= '<p>SMALL Images recreated to new width:</p>'.FPTImage2::RecreateToNewWidth( 'small', $this->iWidthSmall );

      $this->UpdateOption( self::OPTION_URL, $this->strUrl );
      $this->UpdateOption( self::OPTION_LARGE, $this->iWidthLarge );
      $this->UpdateOption( self::OPTION_MEDIUM, $this->iWidthMedium );
      $this->UpdateOption( self::OPTION_SMALL, $this->iWidthSmall );
      $this->UpdateOption( self::OPTION_JPG, $this->iJPGQuality );
      $this->UpdateOption( self::OPTION_CSS, ($this->bOutputCSS) ? 'yes' : 'no' );

      $this->strMessage .= '<p>Options updated !</p>';
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
        // var_dump($strOldPath);var_dump($strNewPath);
        // var_dump($this->strImageRoot);die();
         try{
            foreach( $this->aAllSizes as $strDir )
               if( !$this->CopyImages( $strOldPath.$strDir, $strNewPath.$strDir ) )
                  throw new Exception( 'Unable to copy images from \''.$strOldPath.$strDir.'\' to \''.$strNewPath.$strDir.'\' !' );

            $strClean = $strOldPath;
         }catch( Exception $ex ){
            $strClean = $strNewPath;
            $this->strMessage = '<p>'.$ex->getMessage().'</p>';
         }

         //foreach( $aFolders as $strDir ) $this->DeleteFolder( $strClean.$strDir );
      }

      return true;
   }
   public function CreateImageFolders(){
      $upload_dir = wp_upload_dir();
      if (defined('WP_ALLOW_MULTISITE') &&  (constant ('WP_ALLOW_MULTISITE') === true)) $strRoot = $upload_dir['basedir'].'/testimonials';
      else $strRoot = $_SERVER['DOCUMENT_ROOT'] . $this->strImageRoot;
      if( !@is_dir( $strRoot ) && !@mkdir( $strRoot ) ) return false;
      $strRoot .= '/';

      foreach( $this->aAllSizes as $strSize ){
         @chmod( $strRoot.$strSize, octdec( '0777' ) );
         if( !@is_dir( $strRoot.$strSize ) && !@mkdir( $strRoot.$strSize ) ) return false;
      }

      return true;
   }
   private function UpdateOption( $strKey, $strValue ){
      if( false === get_option( $strKey ) ) add_option( $strKey, $strValue );
      else update_option( $strKey, $strValue );
   }
   
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
   


} // end fv_MyPlugins class

