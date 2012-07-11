         <li id="fpt_<?php echo $this->id; ?>" class="<?php echo $this->strStatus; ?>">
            <table cellpadding="0" border="0" cellspacing="4" class="testimonial-table">
               <tr>
                  <td width="70"><?php if( $objThumb ) $objThumb->Show();   else echo '&nbsp;'; ?></td>
                  <td width="40"><?php echo $this->id; ?></td>
                  <td class="testimonial-title"><span onclick="FPTEditTestimonial(<?php echo $this->id; ?>)"><?php echo $this->strTitle; ?></span></td>
                  <td width="60">
                     <?php if( 'approved' == $this->strStatus ) : ?>
                        Approved
                     <?php else : ?>
                        <span onclick="FPTApprove(<?php echo $this->id; ?>)">Approve</span>
                     <?php endif; ?>
                  </td>
                  <td width="40"><input type="checkbox" name="chkFeatured_<?php echo $this->id; ?>" id="chkFeatured_<?php echo $this->id; ?>" value="<?php echo $this->id; ?>" <?php if( 'yes' == $this->strFeatured ) echo 'checked="checked" '; ?>/></td>
                  <td width="80"><?php echo $this->dateInsert; ?></td>
                  <td width="40"><img src="<?php echo FPTMain::GetUrl(); ?>view/images/delete.gif" onclick="<?php echo ('deleted' == $this->strStatus) ? 'FPTRemove' : 'FPTDelete'; ?>(<?php echo $this->id; ?>)" class="testimonial-delete" width="16" height="16" alt="Delete" /></td>               </tr>
            </table>
         </li>
