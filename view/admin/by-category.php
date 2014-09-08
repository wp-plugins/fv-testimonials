<div class="wrap">
   <h2>Ordering testimonials</h2>
<style type="text/css">
  
	.connectedSortable li{ margin: 0 5px 5px 5px; padding: 5px; font-size: 1em; width: 450px; background-color:#efefef; }
	/* Vertical Tabs
----------------------------------*/
/*.ui-tabs-vertical { width: 55em; }*/ 
.ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
.ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
.ui-tabs-vertical .ui-tabs-nav li a { display:block; }
.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-selected { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; border-right-width: 1px; }
.ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: left;}
	</style>

	<script>
	jQuery(function() {
	   
		jQuery( "#sortable-0<?php if ($aCategories) foreach ($aCategories  as $tax_term) echo ", #sortable-".$tax_term->slug; ?>" ).sortable().disableSelection();

		var $tabs = jQuery( "#tabs" ).tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
		jQuery("#tabs li").removeClass('ui-corner-top').addClass('ui-corner-left');

		var $tab_items = jQuery( "ul:first li", $tabs ).droppable({
			accept: ".connectedSortable li",
			hoverClass: "ui-state-hover",
			drop: function( event, ui ) {
				var $item = jQuery( this );
				var $list = jQuery( $item.find( "a" ).attr( "href" ) )
					.find( ".connectedSortable" );

				ui.draggable.hide( "slow", function() {
					$tabs.tabs( "select", $tab_items.index( $item ) );
					jQuery( this ).appendTo( $list ).show( "slow" );
				});
			}
		});
	});
	</script>
	
	<div id="tabs">
   <?php
   
   $iCategoryID = 0;
    $aOrder = get_option( '_fvt_order' ,array());
      foreach ($aOrder as $icatid => $order)
         $strOrder[$icatid] = implode(',',$order);
      
      echo "<ul>";
      echo "<li><a href='#tabs-0'>All</a></li>";
      if (is_array($aCategories)) foreach ($aCategories  as $tax_term) {
         if($tax_term->parent == 0){
            echo "<li ".$style."><a href='#tabs-".$tax_term->slug."'>".$tax_term->name."</a></li>";
            foreach ($aCategories  as $tax_t){
               if ($tax_t->parent == $tax_term->term_id) echo "<li style='margin-left: 2em; width:10em;'><a href='#tabs-".$tax_t->slug."'>".$tax_t->name."</a></li>";
            }
         }
      }
      echo "</ul>";
      
      echo "<div id='tabs-0'>";
      $args=array(
                  'post_type' => 'testimonial',
                  'post_status' => array('publish','draft'),
                  'posts_per_page' => -1,
                  'caller_get_posts'=> 1,
                  'order'=>'ASC'
                  );
      
      $my_query = null;
      $my_query = new WP_Query($args);
      
      echo "<ul id='sortable-0' class='connectedSortable ui-helper-reset'>";
      if( $my_query->have_posts() ) {
         $aOutputs = array(); $strOutput = '';
         $aCustomOrder = $aOrder[0];
         while ($my_query->have_posts()) : $my_query->the_post(); 
            $output = '';
            $id = get_the_ID();
            $output .='<li class="cat-0" id="'.$id.'"><a href="./post.php?post='.$id.'&action=edit">'. get_the_title() .'</a></li>'; 

            $iIndex = false;
            if (!$aCustomOrder) $aCustomOrder = array();
            if (!empty($aCustomOrder)) $iIndex = array_search($id, $aCustomOrder);
            if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
            if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder) && !empty($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
            else if (($iIndex  === false) && !empty($aOutputs)) $iIndex = max(array_keys($aOutputs))+1;
            else if ($iIndex  === false) $iIndex = 0;

            $aOutputs[$iIndex] = $output;
         endwhile;
         if($aOutputs) ksort($aOutputs);
         if($aOutputs)
         foreach( $aOutputs as $out ){ 
            $strOutput .= $out;
         }
         echo $strOutput;
      }
      wp_reset_query();
      echo "</ul>";
      if (empty($strOutput)) echo '<p>No testimonials in this category</p>';
      else echo "<span onclick=\"FVTSaveOrder('0', 0)\" id='save-0' class='button-primary' style='float:right; margin: 10px'>Save Order</span>";
      echo "</div>";
            
      if (is_array($aCategories)) {
         foreach ($aCategories  as $tax_term) {
            echo "<div id='tabs-".$tax_term->slug."'>";
            $args=array(
                        'post_type' => 'testimonial',
                        'testimonial_category' => $tax_term->slug,
                        'post_status' => array('publish','draft'),
                        'posts_per_page' => -1,
                        'caller_get_posts'=> 1,
                        'order'=>'ASC' 
                        );
            $my_query = null;
            $my_query = new WP_Query($args);

            echo "<ul id='sortable-".$tax_term->slug."' class='connectedSortable ui-helper-reset' >";
            $aOutputs = array(); $strOutput = '';
            if( $my_query->have_posts() ) {
               $aCustomOrder = $aOrder[$tax_term->term_id];
               if (!$aCustomOrder) $aCustomOrder = $aOrder[0];
               while ($my_query->have_posts()) : $my_query->the_post(); 
                  $output = '';
                  $id = get_the_ID();
                  $output .='<li class="cat-'. $tax_term->slug .'" id="'. $id .'"><a href="./post.php?post='.$id.'&action=edit">'. get_the_title().'</a></li>';
                 
                 $iIndex = false;
                  if (!$aCustomOrder) $aCustomOrder = array();
                  if (!empty($aCustomOrder)) $iIndex = array_search($id, $aCustomOrder);
                  if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
                  if (($iIndex  === false) && is_array($aOutputs)&& is_array($aCustomOrder) && !empty($aCustomOrder)) $iIndex = max(max(array_keys($aCustomOrder)),max(array_keys($aOutputs)))+1;
                  else if (($iIndex  === false) && !empty($aOutputs)) $iIndex = max(array_keys($aOutputs))+1;
                  else if ($iIndex  === false) $iIndex = 0;
                  $aOutputs[$iIndex] = $output;
               endwhile;
               if($aOutputs) ksort($aOutputs); // reorder testimonials according to the custom order!
               if($aOutputs)
               foreach( $aOutputs as $out ){ 
                  $strOutput .= $out;
               }
               echo $strOutput;
            }
            echo "</ul>";
            wp_reset_query();
            if (empty($strOutput)) echo '<p>No testimonials in this category</p>';
            else{ 
               echo "<span class='button-primary' style='float:right; margin: 10px' onclick=\"FVTSaveOrder('".$tax_term->slug."', ".$tax_term->term_id.")\" id='save-".$tax_term->slug."'>Save Order</span>";
            }
            echo "</div>";
         }
      }
      
    
      
   ?>
</div>
