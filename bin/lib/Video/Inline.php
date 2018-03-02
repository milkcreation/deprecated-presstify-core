<?php
namespace tiFy\Lib\Video;

class Inline extends \tiFy\App\Factory
{
    /**
     * ATTRIBUTS
     */
    /**
     * Attributs par défaut de la vidéo
     * @var array
     */
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
	
	/**
	 * Attributs par défaut du lien de déclenchement
	 * @var array
	 */
	private static $defaultToggleAttrs	= array(
		'id' 				=> '',
		'class'				=> '',
		'href'				=> '',
		'text'				=> '',
		'title'				=> '',
		'attrs'				=> array(),
		'video'				=> array()
	);
	
	/**
	 * Instance de classe
	 * @var int
	 */
	protected static $Instance = 0;
	
	/**
	 * MÉTHODES
	 */
	/**
	 * Traitement des attributs du lien de déclenchement
	 * @param array $args
	 * @return string|array
	 */
	private static function parseToggleAttrs( $args = array() )
	{	
		$args = wp_parse_args( $args, self::$defaultToggleAttrs );
		
		if( empty( $args['id'] ) )
			$args['id'] = "tiFyVideo-inlineToggle--". uniqid();
		
		if( empty( $args['href'] ) )
			$args['href'] = "#". $args['id'];
		
		return $args;
	}
	
	/**
	 * Affichage du lien de déclenchement
	 * @param string $target cible d'affichage de la vidéo
	 * @param array $args
	 * @param string $echo
	 * @return string
	 */
	public static function toggle( $target = null, $args = array(), $echo = true )
	{
	    $output = "";
	    if( is_null( $target ) ) :
	       $target = "tiFyVideo-inlineViewer--".uniqid();
	       $output .= "<div id=\"{$target}\"></div>";
	       $target = '#'.$target;
	    elseif( preg_match( '/^[^#.]([\w.-]+)/', $target ) ):
	       $target = '#'.$target;
	    endif;
	    
	    $args = self::parseToggleAttrs( $args );
	    
	    $video = htmlentities( json_encode( wp_parse_args( $args['video'], self::$defaultVideoAttrs ) ) );
	    
	    $output .= "<a href=\"{$args['href']}\"";
		$output .= " id=\"{$args['id']}\" class=\"tiFyVideo-inlineToggle". ( $args['class'] ? ' '. $args['class'] : '' ) ."\"";
		
		if( $args['title'] )
			$output .= " title=\"{$args['title']}\"";
		
		foreach( (array) $args['attrs'] as $i => $j )
			$output .= " {$i}=\"{$j}\"";
		
		$output .= " data-target=\"{$target}\"";
		$output .= " data-video=\"{$video}\"";
		$output .= ">";
		$output .= $args['text'];
		$output .= "</a>";		
		
		// Chargement des scripts
		if( ! self::$Instance++ ) :
			$url = self::tFyAppUrl( get_class() ). '/Inline.js';
			add_action( 
				'wp_footer', 
				function() use ($url){
				?><script type="text/javascript" src="<?php echo $url;?>"></script><?php
				},
				1
			);
		endif;
		
	    if( $echo )
			echo $output;
		
		return $output;
	}
	
	/**
	 * Affichage d'une vidéo en ligne
	 * @param array $attr Attributs de la vidéo
	 * @param string $echo Affiche ou retourne
	 * @return string|\tiFy\Lib\Video\false
	 */
	public static function display( $attr, $echo = true )
	{
	    $output = Video::getEmbed( $attr );
	    
	    if( $echo )
	        echo $output;
	    return $output;
	}
}