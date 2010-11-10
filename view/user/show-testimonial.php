<div class="clsTestimonial">
	<a name="<?php echo $this->strSlug; ?>"><h2><?php echo $this->strTitle; ?></h2></a>
		
	<?php if( $objImage && $objImage->strPath ) : ?>
      <h5 class="left">
         <?php if( 'yes' == $this->strLightbox ) : ?><a href="<?php echo $objImage->GetURI( 'large' ); ?>" rel="lightbox"><?php endif; ?>
            <?php $objImage->Show(); ?>
         <?php if( 'yes' == $this->strLightbox ) : ?></a><?php endif; ?>
         <br />
         <?php echo $this->strTitle; ?>
      </h5>
   <?php endif; ?>

   <div class="clsFPTContent">
      <?php echo $this->strText; ?>
   </div>
   
   <div style="clear: both"></div>
</div>