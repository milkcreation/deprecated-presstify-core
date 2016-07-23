<?php
/**
 * RESSOURCES
 * @see https://css-tricks.com/rundown-of-handling-flexible-media/
 */

/* = HELPERS = */
/** == Mise en file des scripts == **/
function tify_video_enqueue(){
	wp_enqueue_style( 'tify_video_player' );
	wp_enqueue_script( 'tify_video_player' );
}

/** == Attributs par défaut == **/
function tify_video_default_attr(){
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

/** == Lien vers une vidéo == **/
function tify_video_link( $attr = array(), $args = array() ){
	static $instance;
	$instance ++;
	
	$attr = wp_parse_args( $attr, tify_video_default_attr() );
	
	$defaults = array(
		'id' 		=> 'tify_video_link-'. $instance,
		'class'		=> '',
		'href'		=> '',
		'title'		=> '',
		'html'		=> '',
		'attrs'		=> array(),
		'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$output  = "";
	$output .= "<a href=\"". ( $href ? $href : $attr['src'] ) ."\" title=\"{$title}\"";
	$output .= " id=\"{$id}\" class=\"tify_video_link {$class}\" data-tify_video=\"1\"";
	foreach( $attr as $k => $v )
		$output .= " data-{$k}=\"{$v}\"";
	foreach( $attrs as $i => $j )
		$output .= " {$i}=\"{$j}\"";
	$output .= ">";	
	$output .= $html;
	$output .= "</a>";
	
	if( $echo )
		echo $output;
	else	
		return $output;
}

/** == Affichage d'une video == **/
function tify_video_display( $attr, $echo = true ){
	// Bypass
	if( empty( $attr['src'] ) )
		return ;
	
	$attr = wp_parse_args( $attr, tify_video_default_attr() );
	
	$src = preg_replace( '/'. preg_quote( site_url(), '/' ) .'/', '', $attr['src'] );
	
	$output = "";		
	if( $output = wp_oembed_get( $src, $attr ) ) :		
		$output = "<div class=\"tify_video-container tify_video-embedded\">". $output ."</div>";
	else :			
		$_attr = '';
		foreach( $attr as $k => $v )
			$_attr .= "$k='$v' ";
		$output = "<div class=\"tify_video-container tify_video-shortcode\">". do_shortcode( "[video $_attr]" ) ."</div>";
	endif;
	
	if( $echo )
		echo $output;
	else 
		return $output;
}


/**
 * 
 */
class tiFy_Video{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri;
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );

		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_filter( 'embed_oembed_html', array( $this, 'wp_embed_oembed_html' ), null, 4 ) ;
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		
		add_action( 'wp_ajax_tify_video', array( $this, 'wp_ajax_action' ) );
		add_action( 'wp_ajax_nopriv_tify_video', array( $this, 'wp_ajax_action' ) );
		
		add_filter( 'wp_video_extensions', array( $this, 'wp_video_extensions' ) );
		
		add_filter( 'oembed_result', array( $this, 'wp_oembed_result' ), 10, 3 );
	}	
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		wp_register_style( 'tify_video_player', $this->uri. 'tify_video.css', array( 'wp-mediaelement', 'dashicons', 'spinkit-three-bounce' ), '20141222' );
		wp_register_script( 'tify_video_player', $this->uri .'tify_video.js', array( 'jquery', 'froogaloop', 'wp-mediaelement' ), '20141222', true );
	}
	
	/** == Responsivité des vidéos intégrées == **/
	function wp_head(){
	?><style type="text/css">.tify_video-embedded{position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;} .tify_video-embedded object,.tify_video-embedded iframe,.tify_video-embedded video,.tify_video-embedded embed{max-width:100%;position:absolute;top:0;left:0;width:100%;height:100%;}</style><?php
	}
		
	/** == Encapsulation des vidéo intégrées == **/
	function wp_embed_oembed_html( $html, $url, $attr, $post_ID ) {
	    $return = '<div class="tify_video-embedded">'. $html .'</div>';
	    return $return;
	}
		
	/** == Pied de page du site == **/
	function wp_footer(){
	?>
		<div id="tify_video-modal">
			<div id="tify_video-overlay">
				<div id="tify_video-spinner" class="sk-spinner sk-spinner-three-bounce">
					<div class="sk-bounce1"></div>
					<div class="sk-bounce2"></div>
					<div class="sk-bounce3"></div>
				</div>
				<div id="tify_video-wrapper"></div>
			</div>
		</div>
	<?php
	}
	
	/** == == */
	function wp_ajax_action(){
		if( empty( $_REQUEST['attr']['src'] ) )
			die(0);

		tify_video_display( $_REQUEST['attr'] );
		exit;
	}
	
	/** == == **/
	function wp_video_extensions( $ext ){
		array_push( $ext, 'mov' );		
		return $ext;
	}
	
	/** == == **/
	function wp_oembed_result( $html, $url, $args ){		
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
}
new tiFy_Video;