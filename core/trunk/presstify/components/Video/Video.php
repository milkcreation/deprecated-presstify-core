<?php
namespace tiFy\Components\Video;

/** @Autoload */
class Video
{
	/* = ARGUMENTS = */
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Actions et Filtres Wordpress
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_filter( 'embed_oembed_html', array( $this, 'wp_embed_oembed_html' ), 10, 4 );		
		add_filter( 'wp_video_extensions', array( $this, 'wp_video_extensions' ) );		
		add_filter( 'oembed_result', array( $this, 'wp_oembed_result' ), 10, 3 );
		
		add_action( 'wp_ajax_tify_video', array( $this, 'wp_ajax_action' ) );
		add_action( 'wp_ajax_nopriv_tify_video', array( $this, 'wp_ajax_action' ) );	
	}	
	
	/* = PARAMETRAGE = */
	/** == Attributs par défaut == **/
	public function default_attr()
	{
		return array(
			'src'      	=> '',
			'poster'   	=> '',
			'loop'     	=> '',
			'autoplay' 	=> '',
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
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Responsivité des vidéos intégrées == **/
	function wp_head()
	{
	?><style type="text/css">.tify_video-embedded{position:relative;padding-bottom:56.25%;padding-top:30px;height:0;} .tify_video-embedded object,.tify_video-embedded iframe,.tify_video-embedded video,.tify_video-embedded embed{max-width:100%;position:absolute;top:0;left:0;width:100%;height:100%;}</style><?php
	}
		
	/** == Encapsulation des vidéo intégrées == **/
	function wp_embed_oembed_html( $html, $url, $attr, $post_ID )
	{
	    $return = '<div class="tify_video-embedded">'. $html .'</div>';
	    return $return;
	}
	
	/** == == **/
	function wp_video_extensions( $ext )
	{
		array_push( $ext, 'mov' );		
		return $ext;
	}
	
	/** == == **/
	function wp_oembed_result( $html, $url, $args )
	{		
		if ( preg_match( '/^\<iframe.*src\=\"https\:\/\/www.youtube.com\/embed\/(.*)\?feature\=oembed.*\>\<\/iframe>$/', $html, $video_id ) === FALSE )
			return $html;
		if( empty( $video_id[1] ) )
			return $html;
			
		if( ! empty( $args['autoplay'] ) )
			$html = preg_replace( '/\?feature\=oembed/', '?feature=oembed&autoplay=1', $html );
		if( ! empty( $args['loop'] ) )
			$html = preg_replace( '/\?feature\=oembed/', '?feature=oembed&loop=1&playlist='. $video_id[1], $html );
		if( isset( $args['rel'] ) )
			$html = preg_replace( '/\?feature\=oembed/', '?feature=oembed&rel='. $args['rel'], $html );

    	return $html;
	}
	
	/** == == */
	function wp_ajax_action()
	{
		if( empty( $_REQUEST['attr']['src'] ) )
			die(0);
		
		wp_die( self::embed( $_REQUEST['attr'] ) );
		exit;
	}
	
	/* = CONTROLEUR * =/
	/** == == **/
	public static function embed( $attr )
	{
		$src = preg_replace( '/'. preg_quote( site_url(), '/' ) .'/', '', $attr['src'] );
		
		$output = "";		
		if( $output = wp_oembed_get( $src, $attr ) ) :		
			$output = "<div class=\"tify_video-container tify_video-embedded\">". $output ."</div>";
		else :			
			$_attr = '';
			foreach( $attr as $k => $v )
				$_attr .= " {$k}=\"{$v}\"";
			$output = "<div class=\"tify_video-container tify_video-shortcode\">". do_shortcode( "[video$_attr]" ) ."</div>";
		endif;
		
		return $output;
	}	
}