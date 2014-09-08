<?php

function FVTShowTestimonialsTemplate ($id, $template, $edit) {
		global $wpdb;
		
		$out = '';
		$html = '';
  if( $edit == false ) {
//			$aLinks = $wpdb->get_var( "SELECT display_name FROM `$eblex_links`");
			$html = <<<HTML
			
			<li id="template_{$id}">(-t{$id}) <span onclick="FVTEditTemplate( {$id} );">{$template['name']}</span>
            <input type="button" value="[X]" class="fpt-del-button" onclick="FVTDeleteTemplate( {$id} )" />
         </li>
HTML;
		}
		else {
   		$content = stripslashes($template['content']);
         // add all detail here once we know what we have		   
			$html = <<<HTML
			<li id="template_{$id}"><table cellpadding="0" border="0" cellspacing="4">
         <tr><td width="80">Title:</td><td ><strong>{$template['name']}</strong></td><td></td><td></td></tr>
         <tr><td width="80" style="vertical-align:top">Content:</td><td><textarea class="template_content" id="template_content_{$id}" rows="5"  cols="120">{$content}</textarea></td><td></td><td></td></tr>
         <tr><td></td><td colspan="2"><a href="javascript:void(0);" onclick="FVTSaveTemplate('{$id}') ">[Save]</a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="FVASEditProduct('{$id}', false) ">[Cancel]</a></td></tr>
         </table>
         </li>
HTML;
		}

		echo $html;
	}

?>