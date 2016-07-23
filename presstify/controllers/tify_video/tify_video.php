<?php
/* = HELPER = */
/** == == **/
function tify_video_embed( $attr, $echo = true ){
	global $tify_video;
	
	$output = $tify_video->embed( $attr );
	
	if( $echo )
		echo $output;
	else 
		return $output;
}

/** == Création d'un contrôleur d'affichage d'une modale 
 * prerequis : wp_enqueue_style( 'tify-video' ); wp_enqueue_script( 'tify-video' );
== **/
function tify_video_toggle( $target, $args = array() ){
	$_target = 'tify_video-'. $target;
	
	// Traitement des arguments
	$defaults = array(
		// Arguments du lien
		'id' 			=> 'tify_video_toggle-'. $target,
		'class'			=> '',
		'href'			=> '',		
		'text'			=> '',
		'link_title'	=> '',
		'link_attrs'	=> array(),
		'echo'			=> true,
		
		// Arguments de la modale
		'autoload'		=> true,		// Instanciation automatique de la modal
		'options'		=> array(
			'backdrop' 		=> true, 	// false | 'static'
			'keybord'		=> true,
			'show'			=> false
		),
		'animations'		=> 'fade',
		'attrs'				=> array(),		
		'before' 			=> '',
		'after' 			=> '',
		'title'				=> '',
		'body'				=> '',
		'footer'			=> '',
		'header_button'		=> true,
		'backdrop_button' 	=> false,
		
		// Arguments de la video
		'attr'				=> array()				
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output  = "";
	$output .= "<a href=\"{$href}\"";	
	$output .= " title=\"{$link_title}\"";
	$output .= " id=\"{$id}\" class=\"tify_video-toggle".( $class ? ' '.$class :'') ."\"";
	foreach( $link_attrs as $i => $j )
		$output .= " {$i}=\"{$j}\"";
	$output .= " data-toggle=\"tify_modal\" data-target=\"{$_target}\"";
	$output .= ">";	
	$output .= $text;
	$output .= "</a>";
	
	if( $autoload )
		tify_video_modal( $target, compact( 'options', 'animations', 'attrs', 'before', 'after', 'title', 'body', 'footer', 'header_button', 'backdrop_button', 'attr' ) );
	
	if( $echo )
		echo $output;
	else	
		return $output;
}

/** == == **/
function tify_video_modal( $target, $args = array() ){
	$defaults = array(
		// Arguments de la modale
		'options'		=> array(
			'backdrop' 		=> true, // false | 'static'
			'keybord'		=> true,
			'show'			=> true
		),
		'animations'		=> 'fade',
		'attrs'				=> array(),		
		'before' 			=> '',
		'after' 			=> '',
		'title'				=> '',
		'body'				=> '',
		'footer'			=> '',
		'header_button'		=> true,
		'backdrop_button' 	=> false,
		
		// Arguments de la video
		'attr'				=> array()
	);
	$args = wp_parse_args( $args, $defaults );
	
	$tify_video_modal = new tiFy_VideoModal( $target, $args );
	add_action( 'wp_footer', array( $tify_video_modal, 'wp_footer' ) );
}

/* = tiFy_Video = */
class tiFy_Video{
	/* = ARGUMENTS = */
		
	/* = CONSTRUCTEUR = */
	public function __construct(  ){
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
	public function default_attr(){
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
	function wp_head(){
	?><style type="text/css">.tify_video-embedded{position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;} .tify_video-embedded object,.tify_video-embedded iframe,.tify_video-embedded video,.tify_video-embedded embed{max-width:100%;position:absolute;top:0;left:0;width:100%;height:100%;}</style><?php
	}
		
	/** == Encapsulation des vidéo intégrées == **/
	function wp_embed_oembed_html( $html, $url, $attr, $post_ID ) {
	    $return = '<div class="tify_video-embedded">'. $html .'</div>';
	    return $return;
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
	
	/** == == */
	function wp_ajax_action(){
		if( empty( $_REQUEST['attr']['src'] ) )
			die(0);

		wp_die( $this->embed( $_REQUEST['attr'] ) );
		exit;
	}
	
	/* = CONTROLEUR * =/
	/** == == **/
	function embed( $attr ){
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
		
		return $output;
	}	
}
global $tify_video;
$tify_video = new tiFy_Video;

/** = tiFY_VideoModal = 
 * @see https://css-tricks.com/rundown-of-handling-flexible-media/
 */ 
class tiFy_VideoModal{
	/* = ARGUMENTS = */
	public	// Configuration
			$target,		
			$args = array();		// Conservation des arguments originaux
			
	private	// Paramètres	
			$options 	= array(
				'backdrop' 	=> true, // false | 'static'
				'keybord'	=> true,
				'show'		=> true
			),			
			$attrs		= array(),
			$animations	= 'fade',
			/// Paramètres d'affichage
			$before 			= '',
			$after 				= '',			
			$title				= '',
			$body				= '',
			$footer				= '',
			$header_button		= true,
			$backdrop_button	= false,
			
			// Arguments de la video
			$attr				= array();
		
	/* = CONSTRUCTEUR = */
	public function __construct( $target, $args = array() ){
		$this->target = $target;
		$this->init( $args );		
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation == **/
	public function init( $args = array() ){
		foreach( array( 'options', 'attrs' ) as $arg )
			if( isset( $args[$arg] ) )
				$this->{$arg} = wp_parse_args( $args[$arg], $this->{$arg} );

		foreach( array( 'animations', 'before', 'after', 'title', 'body', 'footer' ) as $arg ) 
			if( ! empty( $args[$arg] ) )
				$this->{$arg} = $args[$arg];
			
		foreach( array( 'header_button', 'backdrop_button' ) as $arg ) 
			if( isset( $args[$arg] ) )
				$this->{$arg} = $args[$arg];
			
		$this->attr = wp_parse_args( $args['attr'], $this->attr );
	}
			
	/** == Pied de page du site == **/
	function wp_footer(){
		$output  = "";
		$output .= "<div id=\"tify_video-{$this->target}\"";
		// Classe
		$output .= " class=\"tify_video modal fade\"";
		// Options
		foreach( $this->options as $option_name => $option_value )
			$output .= " data-{$option_name}=\"{$option_value}\"";
		// Attributs de la video
		$output .= " data-video_attr=\"". ( htmlentities( json_encode( $this->attr ) ) ) ."\"";
		
		
		// Attributs complémentaires
		$output .=  "tabindex=\"-1\" role=\"dialog\">\n";
		
		// Pré-affichage
		$this->before = apply_filters( 'tify_modal_before-'. $this->target, $this->before );
		$this->before = apply_filters( 'tify_modal_before', $this->before );
		$output .= $this->before;	
		
		$this->backdrop_button = apply_filters( 'tify_video_backdrop_button-'. $this->target, $this->backdrop_button );
		$this->backdrop_button = apply_filters( 'tify_video_backdrop_button',$this->backdrop_button );
		if( $this->backdrop_button )		
			$output .= 	"\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $this->backdrop_button ) ? "<span aria-hidden=\"true\">&times;</span>" : $this->backdrop_button ) .
						"</button>\n";
		
		// Ouverture de la modal
		$output .= "\t<div class=\"modal-dialog modal-lg\" role=\"document\">\n";
		
		// Ouverture du Contenu
		$content = "\t\t<div class=\"modal-content\">";		
		
		/// Entête 
		$header  = "";
		$this->header_button = apply_filters( 'tify_video_header_button-'. $this->target, $this->header_button );
		$this->header_button = apply_filters( 'tify_video_header_button', $this->header_button );
		if( $this->header_button )		
			$header .= "\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $this->header_button ) ? "<span aria-hidden=\"true\">&times;</span>" : $this->header_button ) .
						"</button>\n";
		
		//// Titre de la modale
		$this->title = apply_filters( 'tify_video_title-'. $this->target, $this->title );
		$this->title = apply_filters( 'tify_video_title', $this->title );	
		$header .= "\t\t\t\t<h4 class=\"modal-title\">{$this->title}</h4>\n";
		$header = apply_filters( 'tify_video_header-'. $this->target, $header, $this->title );
		$header = apply_filters( 'tify_video_header', $header, $this->title );	
		$content .= "\t\t\t<div class=\"modal-header\">{$header}</div>";
		
		//// Corps de la modale
		$this->body = apply_filters( 'tify_video_body-'. $this->target, $this->body );
		$this->body = apply_filters( 'tify_video_body', $this->body );
		$content .= "\t\t\t<div class=\"modal-body\">{$this->body}</div>\n";
		
		// Pied de page
		$this->footer = apply_filters( 'tify_video_footer-'. $this->target, $this->footer );
		$this->footer = apply_filters( 'tify_video_footer', $this->footer );
		$content .= "\t\t\t<div class=\"modal-footer\">{$this->footer}</div>\n";
		
		// Traitement du contenu
		$content = apply_filters( 'tify_video_content-'. $this->target, $content, $header, $this->body, $this->footer );
		$content = apply_filters( 'tify_video_content', $content, $header, $this->body, $this->footer );
		
		// Fermeture du contenu
		$content = $content. "</div>\n";
		$output .= $content;

		$output .= "\t</div>\n";
		
		// Post-affichage
		$this->after = apply_filters( 'tify_modal_after-'. $this->target, $this->after );
		$this->after = apply_filters( 'tify_modal_after', $this->after );
		$output .= $this->after;
		
		// Spinner
		$output .= "\t<div class=\"modal-spinner sk-spinner sk-spinner-three-bounce\">\n";
		$output .= "\t\t<div class=\"sk-bounce1\"></div>\n";
		$output .= "\t\t<div class=\"sk-bounce2\"></div>\n";
		$output .= "\t\t<div class=\"sk-bounce3\"></div>\n";
		$output .= "\t</div>\n";
		
		$output .= "</div>\n";
				
		echo $output;
	}
}