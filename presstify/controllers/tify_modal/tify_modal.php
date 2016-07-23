<?php
/* = HELPER = */
/** == Création d'un contrôleur d'affichage d'une modale 
 * prerequis : wp_enqueue_script( 'tify_modal' );
== **/
function tify_modal_toggle( $target, $args = array() ){
	$_target = 'tify_modal-'. $target;
	
	// Traitement des arguments
	$defaults = array(
		// Arguments du lien
		'id' 			=> 'tify_modal_toggle-'. $target,
		'class'			=> '',
		'href'			=> '',		
		'text'			=> '',
		'link_title'	=> '',
		'link_attrs'	=> array(),
		'echo'			=> true,
		
		// Argument de la modale
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
		'backdrop_button' 	=> false		
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$output  = "";
	$output .= "<a href=\"{$href}\"";	
	$output .= " title=\"{$link_title}\"";
	$output .= " id=\"{$id}\" class=\"tify_modal-toggle".( $class ? ' '.$class :'') ."\"";
	foreach( $link_attrs as $i => $j )
		$output .= " {$i}=\"{$j}\"";
	$output .= " data-toggle=\"tify_modal\" data-target=\"{$_target}\"";
	$output .= ">";	
	$output .= $text;
	$output .= "</a>";
	
	if( $autoload )
		tify_modal( $target, compact( 'options', 'animations', 'attrs', 'before', 'after', 'title', 'body', 'footer', 'header_button', 'backdrop_button' ) );
	
	if( $echo )
		echo $output;
	else	
		return $output;
}

/** == == **/
function tify_modal( $target, $args = array() ){
	$defaults = array(
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
		'backdrop_button' 	=> false
	);
	$args = wp_parse_args( $args, $defaults );
	
	$tify_modal = new tiFy_Modal( $target, $args );
	add_action( 'wp_footer', array( $tify_modal, 'wp_footer' ) );
}

class tiFy_Modal{
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
			$backdrop_button	= false;
		
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
	}	
		
	/** == Pied de page du site == **/
	function wp_footer(){
		$output  = "";
		$output .= "<div id=\"tify_modal-{$this->target}\"";
		// Classe
		$output .= " class=\"tify_modal modal fade\"";
		// Options
		foreach( $this->options as $option_name => $option_value )
			$output .= " data-{$option_name}=\"{$option_value}\"";
		// Attributs complémentaires
		$output .=  "tabindex=\"-1\" role=\"dialog\">\n";
		
		// Pré-affichage
		$this->before = apply_filters( 'tify_modal_before-'. $this->target, $this->before );
		$this->before = apply_filters( 'tify_modal_before', $this->before );
		$output .= $this->before;		
		
		$this->backdrop_button = apply_filters( 'tify_modal_backdrop_button-'. $this->target, $this->backdrop_button );
		$this->backdrop_button = apply_filters( 'tify_modal_backdrop_button',$this->backdrop_button );
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
		$this->header_button = apply_filters( 'tify_modal_header_button-'. $this->target, $this->header_button );
		$this->header_button = apply_filters( 'tify_modal_header_button', $this->header_button );
		if( $this->header_button )		
			$header .= "\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $this->header_button ) ? "<span aria-hidden=\"true\">&times;</span>" : $this->header_button ) .
						"</button>\n";
		
		//// Titre de la modale
		$this->title = apply_filters( 'tify_modal_title-'. $this->target, $this->title );
		$this->title = apply_filters( 'tify_modal_title', $this->title );	
		$header .= "\t\t\t\t<h4 class=\"modal-title\">{$this->title}</h4>\n";
		$header = apply_filters( 'tify_modal_header-'. $this->target, $header, $this->title );
		$header = apply_filters( 'tify_modal_header', $header, $this->title );	
		$content .= "\t\t\t<div class=\"modal-header\">{$header}</div>";
		
		//// Corps de la modale
		$this->body = apply_filters( 'tify_modal_body-'. $this->target, $this->body );
		$this->body = apply_filters( 'tify_modal_body', $this->body );
		$content .= "\t\t\t<div class=\"modal-body\">{$this->body}</div>\n";
		
		// Pied de page
		$this->footer = apply_filters( 'tify_modal_footer-'. $this->target, $this->footer );
		$this->footer = apply_filters( 'tify_modal_footer', $this->footer );
		$content .= "\t\t\t<div class=\"modal-footer\">{$this->footer}</div>\n";
		
		// Traitement du contenu
		$content = apply_filters( 'tify_modal_content-'. $this->target, $content, $header, $this->body, $this->footer );
		$content = apply_filters( 'tify_modal_content', $content, $header, $this->body, $this->footer );
		
		// Fermeture du contenu
		$content = $content. "</div>\n";
		$output .= $content;

		$output .= "\t</div>\n";
		
		// Post-affichage
		$this->after = apply_filters( 'tify_modal_after-'. $this->target, $this->after );
		$this->after = apply_filters( 'tify_modal_after', $this->after );
		$output .= $this->after;	
		
		$output .= "</div>\n";
		
		echo $output;
	}
}