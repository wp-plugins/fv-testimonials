<?php if( $this->strMessage ) : ?>
   <div class="wrap"><h3>Message</h3><p><?php echo $this->strMessage; ?></p></div>
<?php endif; ?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
   <div class="wrap">
      <h2>Testimonial Basic Options</h2>
      <table style="width: 100%;">
         <tr>
            <td class="clsTableLeft">Path to post or page where all testimonials are inserted: </td>
            <td><input class="clsBig" type="text" name="tboxTestimonialPage" value="<?php echo $this->strUrl; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Image root folder: </td>
            <td><input class="clsBig" type="text" name="tboxImageRoot" value="<?php echo $this->strImageRoot; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Large image width: </td>
            <td><input class="clsSmall" type="text" name="tboxLarge" value="<?php echo $this->iWidthLarge; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Medium image width: </td>
            <td><input class="clsSmall" type="text" name="tboxMedium" value="<?php echo $this->iWidthMedium; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Small image width: </td>
            <td><input class="clsSmall" type="text" name="tboxSmall" value="<?php echo $this->iWidthSmall; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">JPG Quality: </td>
            <td><input class="clsSmall" type="text" name="tboxJPG" value="<?php echo $this->iJPGQuality; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Output default CSS: </td>
            <td><input type="checkbox" name="chkCSS" value="yes"<?php if( $this->bOutputCSS ) echo ' checked="checked"'; ?> /></td>
         </tr>
      </table>
      <div class="cmdButton"><input type="submit" class="button-primary" name="cmdSaveBasic" value="Save" /></div>
      <div style="clear: both;"></div>
   </div>
   <div class="wrap">
      <h2>Maintanance Checks</h2>
      <div class="fpt-maintanance">
         <input type="submit" name="chmod-images" value="Go" class="button" /> Make Images managable through FTP
      </div>
      <div class="fpt-maintanance">
         <input type="submit" name="recheck-images" value="Go" class="button" /> Recheck database with existing images
      </div>
      <div class="fpt-maintanance">
         <input type="submit" name="restore-order" value="Go" class="button" /> Restore order (if reordering of testimonials doesn't work, this may help. 
         Be carreful since this destroys current order of testimonials)
      </div>
   </div>
</form>