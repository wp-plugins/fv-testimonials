<div class="clsTestimonial">
   <h2><a href="<?php echo $this->GetURL(); ?>"><?php echo $this->strTitle; ?></a></h2>
	
	<?php if( $objImage && $objImage->strPath ) : ?>
      <h5 class="left">
         <a href="<?php echo $objImage->GetURI( 'large' ); ?>" rel="lightbox"><?php $objImage->Show(); ?></a><br />
         <?php echo $this->strTitle; ?>
      </h5>
   <?php endif; ?>

   <div class="clsFPTContent">
      <?php echo $strText; ?>
   </div>
   <?php echo date( 'jS F Y', strtotime( $this->dateInsert ) ); ?>
   
   <div style="clear: both"></div>
</div>