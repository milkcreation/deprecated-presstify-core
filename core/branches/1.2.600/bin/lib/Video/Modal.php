<?php
namespace tiFy\Lib\Video;

class Modal extends \tiFy\Lib\Modal\Modal
{	
	/* = ARGUMENT = */
	private static $defaultVideoAttrs = array(
		// Url de la vidéo
		'src'      	=> '',
		// Couverture de la vidéo	
		'poster'   	=> '',
		'loop'     	=> '',
		'autoplay' 	=> 0,
		'preload'  	=> 'metadata',
		'width'    	=> '100%',
		'height'   	=> '100%',
		/**
		 * Paramètres spécifiques à YouTube
		 * @see https://developers.google.com/youtube/player_parameters
		 * 
		 * 'rel'		=> 1		// Détermine si le lecteur doit afficher des vidéos similaires à la fin de la lecture d'une vidéo. 
		 */	
	);
	
	/* = Lien de déclenchement d'une modale =  */
	public static function toggle( $args = array(), $echo = true )
	{
		if( ! isset( $args['attrs'] ) )
			$args['attrs'] = array();
		$args['attrs']['data-type'] = 'video';
		
		if( ! isset( $args['modal'] ) ) :
			if( ! isset( $args['modal']['attrs'] ) ) :
				$args['modal']['attrs'] = array();
			endif;
		endif;
		
		if( $args['modal'] !== false ) :
			$args['modal']['attrs']['data-type']	= 'video';
			$args['modal']['attrs']['data-video'] 	= htmlentities( json_encode( wp_parse_args( $args['video'], self::$defaultVideoAttrs ) ) );
		endif;
				
		return parent::toggle( $args, $echo );
	}
	
	/* = Affichage de la fenêtre de dialogue = */
	public static function display( $args = array(), $echo = true )
	{
		if( ! self::$Instance ) :
			$url = self::tFyAppUrl( get_class() ). '/Modal.js';
			add_action( 
				'wp_footer', 
				function() use ($url){
				?><script type="text/javascript" src="<?php echo $url;?>"></script><?php
				},
				1
			);
		endif;
		
		$output = parent::display( $args, false );		
		
		if( $echo )
			echo $output;
		
		return $output;
	}
}
