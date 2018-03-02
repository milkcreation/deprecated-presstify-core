<?php
namespace tiFy\Lib\Video;

class Video
{
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_filter( 'embed_oembed_html', array( $this, 'embed_oembed_html' ), 10, 4 );
        add_filter( 'wp_video_extensions', array( $this, 'wp_video_extensions' ) );
		add_filter( 'oembed_fetch_url', array( $this, 'oembed_fetch_url' ), 10, 3 );
		add_filter( 'oembed_result', array( $this, 'oembed_result' ), 10, 3 );
    }
    
	/* = DECLENCHEURS = */
	/** == Responsivité des vidéos intégrées == **/
	final public function wp_head()
	{
	?><style type="text/css">.tify_video-embedded{position:relative;padding-bottom:56.25%;/*padding-top:30px*/;height:0;} .tify_video-embedded object,.tify_video-embedded iframe,.tify_video-embedded video,.tify_video-embedded embed{max-width:100%;position:absolute;top:0;left:0;width:100%;height:100%;}</style><?php
	}
		
	/** == Encapsulation des vidéo intégrées == **/
	final public function embed_oembed_html( $html, $url, $attr, $post_ID )
	{
	    $return = '<div class="tify_video-embedded">'. $html .'</div>';
	    return $return;
	}
	
	/** == == **/
	final public function wp_video_extensions( $ext )
	{
		array_push( $ext, 'mov' );		
		return $ext;
	}
	
	/** == == **/
	final public function oembed_fetch_url( $provider, $url, $args )
	{
		if( ! preg_match( '/^https\:\/\/vimeo.com/', $url ) )
			return $provider;
		
		/** 
		 * @see https://developer.vimeo.com/apis/oembed 
		 **/
		// Lecture automatique	
		if( ! empty( $args['autoplay'] ) )
			$provider = add_query_arg( 'autoplay', 1, $provider );
		// Boucle	
		if( ! empty( $args['loop'] ) )
			$provider = add_query_arg( 'loop', true, $provider );
		
		return $provider;
	}
		
	/** == == **/
	final public function oembed_result( $html, $url, $args )
	{		
		/**
		 * PARAMETRES DES VIDEOS YOUTUBE
		 * @see https://developers.google.com/youtube/player_parameters#Parameters
		 */
		if ( preg_match( '/^\<iframe.*src\=\"https\:\/\/www.youtube.com\/embed\/(.*)\?feature\=oembed.*\>\<\/iframe>$/', $html, $matches ) === FALSE )
			return $html;
		if( empty( $matches[1] ) )
			return $html;
		$video = $matches[1];
			
		if ( preg_match( '/^\<iframe.*src\=\"([^"\']*)\".*\>\<\/iframe>$/', $html, $matches ) === FALSE )
			return $html;
		if( empty( $matches[1] ) )
			return $html;
		
		$ori = $src	= $matches[1];
		
		// Lecture automatique
		if( ! empty( $args['autoplay'] ) )
			$src = add_query_arg( 'autoplay', 1, $src ); 
		// Boucle	
		if( ! empty( $args['loop'] ) )
			$src = add_query_arg( array( 'loop' => 1, 'playlist' => $video ), $src );
		// Vidéo en relation à la fin
		if( isset( $args['rel'] ) )
			$src = add_query_arg( 'rel', (int) $args['rel'], $src );
		
		// Modification de l'iFrame de sortie	
		if( $src !== $ori )	:	
			$html = preg_replace( '/'. preg_quote( $ori, '/' ) .'/', $src, $html );	
		endif;
			
    	return $html;
	}
		
	/**
	 * MÉTHODES
	 */
	/**
	 * Récupération du code d'intégration d'une vidéo
	 * @param array $attr Paramètres de la vidéo
	 * @return string|false Code d'affichage de la vidéo
	 */
	public static function getEmbed( $attr )
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