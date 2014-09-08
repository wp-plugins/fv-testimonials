function FVTMAddImage(nonce) {
//	jQuery( '#amazon-product-'+id ).addClass("amazon-processing"); 
	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",               
			timeout: 3000,
			data: { _ajax_nonce: nonce, file: jQuery( "input[name=fileImage]" ).val(), action: 'fv_testimonials_ajax_action_upload_image'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
					jQuery( '#fileUploaded' ).before( data ).remove(); 
				}
				else {
					// TODO replace DOM element
					jQuery( '#fileUploaded' ).before( data ).remove(); 
				}
			}
		} );
}

function FVTDeleteImage( idPost, idImage ){
   
	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",               
			timeout: 3000,
			data: { post_id: idPost, image_id: idImage, action: 'fv_testimonials_ajax_delete_image'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
				//	jQuery( "#fvt-image-1" + idTemp ).before( data ).remove(); 
				}
				else {
					// TODO replace DOM element
					jQuery( "#fvt-image-" + idImage ).before( data ).remove(); 
				}
			}
		} );
}


function FVTSaveOrder( slug, id ){
	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",               
			timeout: 3000,
			data: { slug: slug, id: id, order: jQuery('#sortable-'+slug).sortable('toArray'), action: 'fv_testimonials_ajax_save_order'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
				//	jQuery( "#save-" + slug ).before( data ).remove(); 
				}
				else {
					// TODO replace DOM element
					//jQuery( "#template_" + idTemp ).before( data ).remove(); 
				}
			}
		} );
}
function FVTConvertTestCats(){
	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",               
			timeout: 3000,
			data: { action: 'fv_testimonials_ajax_convert_content'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
				//	jQuery( "#save-" + slug ).before( data ).remove(); 
				}
				else {
					// TODO replace DOM element
					jQuery( "#all-converting").before( "Testimonials were converted." ).remove(); 
				}
			}
		} );
}

function FVTConvertShortcodesStandard(){
	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",               
			timeout: 3000,
			data: { action: 'fv_testimonials_ajax_convert_shortcodes_standard'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
				//	jQuery( "#save-" + slug ).before( data ).remove(); 
				}
				else {
					// TODO replace DOM element
					jQuery( "#convert_standard" ).before( data ).remove(); 
				}
			}
		} );
}
function FVTConvertShortcodesDB(){
   	jQuery.ajax({	type: "POST",
			url: "../wp-admin/admin-ajax.php",               
			timeout: 3000,
			data: { action: 'fv_testimonials_ajax_convert_shortcodes_db'},
			success: function(data) {
			//console.log(data);
				if( data == '0') {
					//element.innerHTML = 'Error?'; //todo
				//	jQuery( "#save-" + slug ).before( data ).remove(); 
				}
				else {
					// TODO replace DOM element
					jQuery( "#found_db" ).before( data ).remove(); 
				}
			}
		} );

}
