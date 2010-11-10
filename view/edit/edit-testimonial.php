<input type="hidden" name="idTestimonial" value="<?php echo $objTestimonial->id; ?>" />

<table cellspacing="8" border="0" cellpadding="4" class="edit-testimonial">
   <tr>
      <td width="80"><strong>Title: </strong></td>
      <td><input type="text" name="tboxTitle" value="<?php echo $objTestimonial->strTitle; ?>" /></td>
   </tr>
   <tr>
      <td width="80"><strong>Text: </strong></td>
      <td><textarea name="txtText" cols="10" rows="5"><?php echo htmlspecialchars( $objTestimonial->strText ); ?></textarea></td>
   </tr>
</table>
<table cellspacing="8" border="0" cellpadding="4" class="edit-testimonial">
   <tr>
      <td width="50%">
         <input type="checkbox" name="chkFeatured" value="yes" <?php if( 'yes' == $objTestimonial->strFeatured ) echo 'checked="checked" '; ?>/>
         <strong>Featured</strong>
      </td>
      <td width="50%">
         <input type="checkbox" name="chkLightbox" value="yes" <?php if( 'yes' == $objTestimonial->strLightbox ) echo 'checked="checked" '; ?>/>
         <strong>Display larger image in lightbox</strong>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <?php if( $objImage ) : ?>
            <a href="<?php echo $objImage->GetURI(); ?>" target="_blank">Original image</a> is present.
         	<div class="inline" id="divChangeImage">
         		<input type="button" class="button" value="Change" onclick="FPTChangeImage();"> | 
         		<input type="button" class="button" value="Remove" onclick="FPTRemoveImage(<?php echo $objTestimonial->id; ?>);">
         	</div>
         <?php else : ?>
            No Original image present on this testimonial. Insert: <input type="file" name="fileImage" id="fileImage" />
         <?php endif; ?>
      </td>
   </tr>
</table>
<div style="text-align: center; width: 100%; margin-bottom: 10px;">
   <input name="advanced-options" type="button" class="button" value="Advanced Options" onclick="FPTShowAdvanced();" />
</div>

<div class="fpt-hidden">
   <table cellspacing="8" border="0" cellpadding="4" class="edit-testimonial">
      <tr>
         <td width="100"><strong>Status: </strong></td>
         <td>
            <input type="radio" name="optStatus[]" value="wait" <?php if( 'wait' == $objTestimonial->strStatus ) echo 'checked="checked" '; ?>/> Waiting for approval<br />
            <input type="radio" name="optStatus[]" value="approved" <?php if( 'approved' == $objTestimonial->strStatus ) echo 'checked="checked" '; ?>/> Approved<br />
            <input type="radio" name="optStatus[]" value="deleted" <?php if( 'deleted' == $objTestimonial->strStatus ) echo 'checked="checked" '; ?>/> Deleted<br />
         </td>
      </tr>
      <tr>
         <td width="100"><strong>Slug: </strong></td>
         <td><input type="text" name="tboxSlug" value="<?php echo $objTestimonial->strSlug; ?>" /></td>
      </tr>
      <tr>
         <td width="100"><strong>Insert Date: </strong></td>
         <td><input type="text" name="tboxDate" value="<?php echo $objTestimonial->dateInsert; ?>" /></td>
      </tr>
      <tr>
         <td width="100"><strong>Excerpt: </strong></td>
         <td><textarea name="txtExcerpt" cols="10" rows="3"><?php echo htmlspecialchars( $objTestimonial->strExcerpt ); ?></textarea></td>
      </tr>
   </table>
</div>

<div style="text-align: center; width: 100%;">
   <input type="submit" class="button" name="cmdSaveTestimonial" value="Save" /> |
   <input type="submit" class="button" name="cmdCancel" value="Cancel" />
</div>
