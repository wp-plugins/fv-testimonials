<div class="wrap"><div id="icon-options-general" class="icon32"></div>
   <h2>Testimonials options</h2>
   <?php if( $objFVTMain->strMessage ) : ?>
   <div class="wrap"><h3>Message</h3><p><?php echo $objFVTMain->strMessage; ?></p></div>
<?php endif; ?>

<?php if (!class_exists('FV_Testimonials_PRO_Base')):?>
<div style="float:right; width: 200px; border:1px solid #eee; padding:20px;">
Buy PRO version now!
</div>
<?php endif;?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
      <table>
         <tr>
            <td class="clsTableLeft">Path to post or page where all testimonials are inserted: </td>
            <td><input class="clsBig" type="text" name="tboxTestimonialPage" value="<?php echo $objFVTMain->strUrl; ?>" /></td>
         </tr>
         <?php  // this is not for wpmu 
         if ( (!defined('WP_ALLOW_MULTISITE') || constant ('WP_ALLOW_MULTISITE') === false)) : ?>
         <tr>
            <td class="clsTableLeft">Image root folder: </td>
            <td><input class="clsBig" type="text" name="tboxImageRoot" value="<?php echo $objFVTMain->strImageRoot; ?>" /></td>
         </tr>
         <?php endif; ?>
         <tr>
            <td class="clsTableLeft">Large image width: </td>
            <td><input class="clsSmall" type="text" name="tboxLarge" value="<?php echo $objFVTMain->iWidthLarge; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Medium image width: </td>
            <td><input class="clsSmall" type="text" name="tboxMedium" value="<?php echo $objFVTMain->iWidthMedium; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Small image width: </td>
            <td><input class="clsSmall" type="text" name="tboxSmall" value="<?php echo $objFVTMain->iWidthSmall; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">JPG Quality: </td>
            <td><input class="clsSmall" type="text" name="tboxJPG" value="<?php echo $objFVTMain->iJPGQuality; ?>" /></td>
         </tr>
         <tr>
            <td class="clsTableLeft">Output default CSS: </td>
            <td><input type="checkbox" name="chkCSS" value="yes"<?php if( $objFVTMain->bOutputCSS ) echo ' checked="checked"'; ?> /></td>
         </tr>
      </table>
      <div class="cmdButton"><input type="submit" class="button-primary" name="cmdSaveBasic" value="Save" /></div>
      <div style="clear: both;"></div>
      <h2>Maintanance Checks</h2>
      <div class="fpt-maintanance">
         <input type="submit" name="chmod-images" value="Go" class="button" /> Make Images managable through FTP
      </div>
      <div class="fpt-maintanance">
         <input type="submit" name="recheck-images" value="Go" class="button" /> Recheck database with existing images
      </div>
      <div style="clear: both;"></div>
         <?php  // this is not for wpmu, multisite did not work before at all
         if ( (!defined('WP_ALLOW_MULTISITE') || constant ('WP_ALLOW_MULTISITE') === false)) : ?>
      <h2>Shortcode Conversion</h2>
      <div class="fpt-maintenance">
         <!--p><span id="all-converting"><a href="javascript:void(0);" onclick="FVTConvertTestCats()"  class="button">Convert All Testimonials</a></span></p-->
         <!--span id="cat-converting"><a href="javascript:void(0);" onclick="FVTConvertCats()"  class="button">Convert Categories</a></span>
         <span id="testimonials-converting"><a href="javascript:void(0);" onclick="FVTConvertTestimonials()"  class="button">Convert Testimonials & images</a></span-->
         <p>Following action is not revertible! Be sure to <strong>backup the database before starting the conversion!</strong></p>
         <!--p><span id="convert_standard"><a href="javascript:void(0);" onclick="FVTConvertShortcodesStandard()"  class="button">Convert Shortcodes in Posts, Pages & template files</a></span></p-->
         <!--p><a href="javascript:void(0);" onclick="FVTConvertShortcodesPosts()"  class="button">Convert Shortcodes in posts & pages</a><span id="found_post_ids"></span></p>
         <p><a href="javascript:void(0);" onclick="FVTFindShortcodesTheme()"  class="button">Find Shortcodes in template files</a><span id="found_theme"></span></p-->
         <p><a href="javascript:void(0);" onclick="FVTConvertShortcodesDB()"  class="button">Convert Shortcodes elsewhere in DB</a><span id="found_db"></span> Searches for shortcodes in widgets, sniplets, and other plugins containing some content</p>
         <p><strong>Custom coding.</strong> If you have any custom written functions or hacks, you would have to take care of thee yourself. If you think your tweak might be interesting to others, please submit your idea into our support forum.</p>
      </div>
      <?php endif; ?>
</form>
</div>
