<?php

/**
 * Checks if image has some colors with semi-alpha value.
 *
 * Checks alpha value of color. If this value is not 0 (oblique) or 127(transparent) it returns
 * true upon finding the first of such colors, or it goes through all pixels in image and returns
 * false. Order of going through pixels is by columns from 0.th column to last column. <b>This 
 * method works only with TrueColor images.</b>
 *
 * @param pointer &$img Pointer to image resource
 * @return bool True if TrueColor image is complex (e.g. contains semi-alpha color), false otherwise
 */
function FV_IsComplex( &$img, $aImageInfo ){
	if( !imageistruecolor( $img ) ) return false;
	
	for( $i=0; $i<$aImageInfo[0]; $i++ ){
		for( $j=0; $j<$aImageInfo[1]; $j++ ){
			$iColor = imagecolorat( $img, $i, $j );
			$iAlpha = ($iColor & 0x7F000000) >> 24;
			if( 0 != $iAlpha && 127 != $iAlpha ) return true;
		}
	}
	
	return false;
}

/**
 * Counts number of unique colors and gets position of first transparent color.
 *
 * Counts number of unique colors used in this image. If $bTransparency is set to 'true' then
 * this function also tries to get first transparent color (alpha = 127) and record it position.
 * Order of going through pixels is by columns from 0.th to last. <b>This method works only
 * with TrueColor images.</b>
 *
 * @param pointer &$img Pointer to image resource
 * @param integer $iWidth Width of area in which to search and count
 * @param integer $iHeight Height of area in which to search and count
 * @param boolean $bTransparency If true, then method also tries to get position of first transparency color.
 * @return array|boolean False if inserted image is not TrueColor, or is damaged in another way. Array keys:
 * - colors : Number of unique colors used in image found
 * - transparent : Array with 'x' and 'y' as keys determining position of pixel with fully transparent color	 	 
 */
function FV_CountUniqueColors( &$img, $iWidth, $iHeight, $bTransparency = false ){
	if( !imageistruecolor( $img ) ) return false;
	
	$aColors = array();
	$aTrans = array();
	$aTrans['position'] = '';
	
	for( $i=0; $i<$iWidth; $i++ ){
		for( $j=0; $j<$iHeight; $j++ ){
			$iColor = imagecolorat( $img, $i, $j );
			if( isset( $aColors[$iColor] ) ) $aColors[$iColor] += 1;
			else $aColors[$iColor] = 1;
			
			if( $bTransparency ){
				$iAlpha = ($iColor & 0x7F000000) >> 24;
				if( 127 == $iAlpha ){
					$aTrans['position'] = array( 'x' => $i, 'y' => $j );
					$bTransparency = false;
				}
			}
		}
	}
	
	return array( 'colors' => count( $aColors ), 'transparent' => $aTrans['position'] );
}

/**
 * Atempts to converts TrueColor image to Indexed image
 *
 * The conversion is done by GD function imagetruecolortopalette. In indexed image maximum of 255
 * colors will be used. If Second parameter ($aColorInfo) is suplied, function will atempt
 * to preserve transparency.
 *
 * @param pointer &$imgSource Pointer to image resource
 * @param array $aColorInfo Array that contains information about image {@link kfmImage::CountUniqueColors returned by CountUniqueColors}
 */
function FV_TrueColorToIndexed( &$imgSource, $aColorInfo = NULL ){
	imagetruecolortopalette( $imgSource, true, 255 );
	
	if( is_array( $aColorInfo ) && isset( $aColorInfo['transparent'] ) && $aColorInfo['transparent'] ){
		$aPosition = $aColorInfo['transparent'];
		$iColorIndex = imagecolorat( $imgSource, $aPosition['x'], $aPosition['y'] );
		imagecolortransparent( $imgSource, $iColorIndex );
	}
}

?>