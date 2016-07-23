<?php
namespace tiFy\Components\Video;

class Modal
{
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