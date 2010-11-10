var g_help = "";
var g_help2 = "";

function FPTStartSorting(){
   jQuery( "#FPTList" ).sortable({ items: 'li' });

   var cmdSort = jQuery( "#cmdSort" );
   cmdSort.val( "Save Order" );
   cmdSort.click( FPTPostSortOrder );
}

function FPTPostSortOrder( event ){
   g_help = "";
   jQuery( "#FPTList li" ).each( function( i, liItem ){
      g_help += liItem.id.substr( 4 ) + ",";
   });
   g_help = g_help.substr( 0, g_help.length - 1 );

   jQuery( "#order" ).val( g_help ) ;
   jQuery( "#formTestimonials" ).submit();
}

function FPTSaveFeatured(){
   g_help = "";
   g_help2 = "";

   jQuery( "#FPTList li" ).each( function( i, liItem ){
      var strID = liItem.id.substr( 4 );
      if( jQuery( "#chkFeatured_" + strID + ":checked" ).attr( "checked" ) ) g_help += strID + ",";
      else g_help2 += strID + ",";
   });

   if( 0 < g_help.length ) g_help = g_help.substr( 0, g_help.length - 1 );
   else g_help = "yes";
   if( 0 < g_help2.length ) g_help2 = g_help2.substr( 0, g_help2.length - 1 );
   else g_help2 = "yes";

   jQuery( "#featured" ).val( g_help );
   jQuery( "#nonfeatured" ).val( g_help2 );
   jQuery( "#formTestimonials" ).submit();
}

function FPTEditTestimonial( id ){
   jQuery.get( fpt_base, { ajax: "EditTestimonial", fpt_id: id }, function( strData, strStatus ){
      jQuery( "#fpt_" + id ).html( strData );
   });
}

function FPTChangeImage(){
   jQuery.get( fpt_base, { ajax: "ChangeImage", fpt_id: 0 }, function( strData, strStatus ){
      jQuery( "#divChangeImage" ).html( strData );
   });
}

function FPTApprove( id ){
   jQuery.get( fpt_base, { ajax: "ApproveTestimonial", fpt_id: id }, function( strData, strStatus ){
      var strText = strData.replace( /<li .*?>/, "" ).replace( /<\/li>/, "" );
      var liItem = jQuery( "#fpt_" + id );
      liItem.removeClass();
      liItem.addClass( "approved" );
      liItem.html( strText );
   });
}

function FPTRemoveImage( id ){
   if( window.confirm( "Do you really want to delete all images associated with this testimonial ?" ) ){
      jQuery.get( fpt_base, { ajax: "RemoveImages", fpt_id: id }, function( strData, strStatus ){
         jQuery( "#fpt_" + id ).html( strData );
      });
   }
}

function FPTDelete( id ){
   jQuery.get( fpt_base, { ajax: "DeleteTestimonial", fpt_id: id }, function( strData, strStatus ){
      var strText = strData.replace( /<li .*?>/, "" ).replace( /<\/li>/, "" );
      var liItem = jQuery( "#fpt_" + id );
      liItem.removeClass();
      liItem.addClass( "deleted" );
      liItem.html( strText );
   });
}

function FPTRemove( id ){
   if( window.confirm( "Do you really want to delete this testimonial from database ?" ) ){
      jQuery( "#delete" ).val( id ); //id.toString();
      jQuery( "#formTestimonials" ).submit();
   }
}


function FPTShowAdvanced(){
   jQuery( ".fpt-hidden" ).toggle( 800 );
}