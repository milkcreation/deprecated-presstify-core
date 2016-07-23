<?php
namespace tiFy\Lib;

class QrCode
{
	/* = ARGUMENTS = */
	//
	private static $Renderer 		= null;
	
	//
	private static $OutputMimeType	= null;
	
	// 
	private static $Render 			= null; 
	
	
	
	/* = CONTROLEUR = */
	/** == == **/
	public static function generate( $content, $format = 'png', $size = array( 256, 256, 4 ) )
	{
		if( ! self::$Renderer ) :
			switch( $format ) :
				default :
				case 'png' :
					self::$Renderer = new \BaconQrCode\Renderer\Image\Png;
					self::$OutputMimeType = 'image/png';
					break;
				case 'svg' :
					self::$Renderer = new \BaconQrCode\Renderer\Image\Svg;
					self::$OutputMimeType = 'image/svg+xml';
					break;
				case 'eps' :
					self::$Renderer = new \BaconQrCode\Renderer\Image\Eps;
					self::$OutputMimeType = null;
					break;
			endswitch;
					
		endif;	
		
		if( ! isset( $size[0] ) )
			$width = 256;
		if( ! isset( $size[1] ) )
			$height = 256;
		if( ! isset( $size[2] ) )
			$margin = 4;
		self::$Renderer->setHeight( $size[0] );
		self::$Renderer->setWidth( $size[1] );
		self::$Renderer->setMargin( $size[2] );
		
		$writer = new \BaconQrCode\Writer( self::$Renderer );	
		
		return self::$Render = $writer->writeString( $content );
		
	}	
	
	/** == == **/
	public static function output( $content, $format = 'png', $size = array( 256, 256, 4 ) )
	{		
		self::generate( $content, $format, $size );
	
		if( self::$OutputMimeType && self::$Render ) :
			return "<img src=\"data:". self::$OutputMimeType .";base64,".  @ base64_encode( self::$Render ) ."\" />";
		endif;
	}
	
	/** == @todo == **/
	public static function save( $content, $format = 'png', $size = array( 256, 256, 4 ) )
	{
		self::generate( $content, $format, $size );
		
		if( self::$OutputMimeType && self::$Render ) :
			file_put_contents( '/qrcode.png', self::$Render );
		endif;
	}
	
}