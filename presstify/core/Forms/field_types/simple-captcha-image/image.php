<?php
require_once(  '../../../../../../../wp-load.php' );

if( ! isset( $_SESSION ) )
	@ session_start();
$_SESSION['security_number'] = rand( 10000, 99999 );

function imageCreateFromAny( $filepath ) {
    $type = exif_imagetype( $filepath );
    $allowedTypes = array(
        1,  // [] gif
        2,  // [] jpg
        3,  // [] png
        6   // [] bmp
    );
    if ( ! in_array( $type, $allowedTypes ) )
        return false;
	
    switch ($type) :
        case 1 :
            $im = imageCreateFromGif($filepath);
        break;
        case 2 :
            $im = imageCreateFromJpeg($filepath);
        break;
        case 3 :
            $im = imageCreateFromPng($filepath);
        break;
        case 6 :
            $im = imageCreateFromBmp($filepath);
        break;
	endswitch;
	   
    return $im; 
}

ob_clean();
ob_start();

// Configuration
$src 		= apply_filters( "tify_forms_sci_background_image", "texture.jpg" );
$txt_color 	= apply_filters( "tify_forms_sci_text_color", array( 180, 180, 180 ) );
// Traitement
$img 		= imageCreateFromAny( $src );
$image_text = empty( $_SESSION['security_number'] ) ? 'error' : $_SESSION['security_number'];
$text_color = imagecolorallocate( $img, $txt_color[0], $txt_color[1], $txt_color[2] );

// Alternative imagettftext (Serveur MacOSX)
if( function_exists('imagettftext') ) :
	$text 		= imagettftext( $img, 16, rand(-10,10), rand(10,30), rand(25,35), $text_color, "fonts/courbd.ttf", $image_text );
else :
	$font 		= imageloadfont( "./fonts/DaveThin_8x16_BE.gdf" );
	$text 		= imagestring ( $img, $font, rand(10,30), rand(25,35), $image_text, $text_color );
endif;

header( "Content-type:image/jpeg" );
header( "Content-Disposition:inline ; filename=".basename( $src.$image_text ) );	
imagejpeg($img);
imagedestroy($img);