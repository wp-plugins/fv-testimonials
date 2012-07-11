<?php if( $this->strMessage ) : ?>
   <div class="wrap"><h3>Message</h3><p><?php echo $this->strMessage; ?></p></div>
<?php endif; ?>
<div class="wrap">
   <h2>Add New Testimonial</h2>
   <form action="<?php echo $strUrl; ?>" method="post" enctype="multipart/form-data">
      <table border="0" cellspacing="1" cellpadding="3" class="fpt-table">
         <tbody>
            <tr>
               <td align="right" width="150">*Name: </td>
               <td align="left"><input type="text" name="name" value="" /></td>
            </tr>
            <!--<tr>
               <td align="right" width="150">Slug: </td>
               <td align="left"><input type="text" name="slug" value="" /></td>
            </tr>
            <tr>
               <td align="right" width="150">Excerpt: </td>
               <td align="left"><textarea name="text" rows="5"></textarea></td>
            </tr>-->
            <tr>
               <td align="right" width="150">*Text: </td>
               <td align="left">
                  <textarea name="text" rows="10"></textarea>
               </td>
            </tr>
            <tr>
               <td align="right" width="150">Image: </td>
               <td align="left"><input type="file" name="fileImage" /></td>
            </tr>
         </tbody>
      </table>
      <div style="text-align: center; padding-top: 20px;"><input style="width: 100px;" type="submit" name="addTestimonial" value="Add New" class="button-primary" /></div>
   </form>
</div>