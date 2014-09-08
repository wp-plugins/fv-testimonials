<form enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="testimonialInput">

<table summary="" border="0" cellspacing="1" cellpadding="3" class="testimonil-table">
<colgroup span="1" width="100" />

	   <tr><td>Name: </td><td><input type="text" id="testimonial_title" name="testimonial_title" value="<?php echo $strTitle; ?>" /></td>

	   <tr><td>Text: </td><td><textarea name="testimonial_content" id="testimonial_content"><?php echo htmlspecialchars( $strText ); ?></textarea></td>

	   <tr><td>Image: </td><td><input type="file" name="testimonial_user_image" id="testimonial_user_image" /></td>

	   <tr><td>&nbsp;</td><td><input type="submit" name="uploadTestimonial" value="Send" /></td>
		
</table>
</form>