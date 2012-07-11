<?php if( $this->strMessage ) : ?>
   <div class="wrap"><h3>Message</h3><p><?php echo $this->strMessage; ?></p></div>
<?php endif; ?>
<div class="wrap">
   <h2>Testimonials</h2>
   <div class="testimonial-header">
      <table cellpadding="0" border="0" cellspacing="4" class="testimonial-table">
         <tr>
            <td width="70">Thumbnail</td>
            <td width="40">ID</td>
            <td>Title</td>
            <td width="60">Status</td>
            <td width="40">Featured</td>
            <td width="80">Date</td>
            <td width="40">Delete</td>
         </tr>
      </table>
   </div>
   <form enctype="multipart/form-data" id="formTestimonials" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<input type="hidden" id="order" name="order" value="no" />
		<input type="hidden" id="featured" name="featured" value="no" />
		<input type="hidden" id="nonfeatured" name="not_featured" value="no" />
		<input type="hidden" id="delete" name="delete" value="no" />
		
      <ul id="FPTList" class="clsFPTList">
<?php foreach( $aTestimonials as $aTest ) $aTest['testimonial']->ShowListItem( $aTest['image'] ); ?>
      </ul>
      <p class="clsButtons">
         <input type="button" class="button" id="cmdSort" name="sort" value="Sort" onclick="FPTStartSorting();" />
         <input type="button" class="button" name="cmdFeatured" value="Save Featured" onclick="FPTSaveFeatured();" />
      </p>
   </form>
</div>
