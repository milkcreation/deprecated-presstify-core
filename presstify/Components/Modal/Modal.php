<?php
namespace tiFy\Components\Modal;

class Modal
{
	/* = ARGUMENTS = */
	/** == CONFIGURATION == **/
	// Options du lien de déclenchement d'affichage de la fenêtre modale
	private $toggle		= array(
		'target'			=> null,	
		'id' 				=> '',
		'class'				=> '',
		'href'				=> '',
		'text'				=> '',
		'link_title'		=> '',
		'link_attrs'		=> array(),
		'echo'				=> true
	);
	// Options d'affichage de la fenêtre modale
	private	$modal 		= array(
		'target'			=> null,
		'class'				=> '',
		'options'			=> array(
			'backdrop' 			=> true, // false | 'static'
			'keybord'			=> true,
			'show'				=> true
		),
		'attrs'				=> array(),
		'animations'		=> 'fade',
		'before'			=> '',
		'after' 			=> '',
		'title'				=> '',
		'body'				=> '',
		'footer'			=> '',
		'header_button'		=> true,
		'backdrop_button'	=> false,
		'size'				=> 'lg',
		'echo'				=> true
	);			
		
	/* = CONSTRUCTEUR = */
	public function __construct( $tpl, $args = array() )
	{
		$modal = false; $toggle = false;
		
		if( $tpl === 'modal' ) :
			$modal = $args;
		elseif( isset( $args[ 'modal' ] ) ) :
			$modal = $args[ 'modal' ];
			unset( $args[ 'modal' ] );
			$toggle = $args;
		else :
			$toggle = $args;
		endif;
		
		// Traitement des options
		/// Options du lien de déclenchement
		$this->toggle = ( is_array( $toggle ) ) ? wp_parse_args( $toggle, $this->toggle ) : $toggle;

		/// Options de la fenêtre modale
		if( is_array( $modal ) ) :
			if( isset( $modal['options'] ) ) :
				$defaults = array( 'backdrop' => true, 'keybord' => true, 'show' => $this->toggle ? false : true );
				$modal['options'] =  wp_parse_args( $modal['options'], $defaults );
			endif;
				
			$this->modal = wp_parse_args( $modal, $this->modal );
		else :
			$this->modal = $modal;
		endif;
		
		// Définition de la cible
		$target = uniqid();
		if( $this->toggle && is_null( $this->toggle['target'] ) )
			$this->toggle['target'] = $target;
		if( $this->toggle && is_null( $this->modal['target'] ) )
			$this->modal['target'] = $this->toggle['target'];
	}
	
	/* = AFFICHAGE DU LIEN DE DECLENCHEMENT = */
	final public function ToggleDisplay()
	{		
		extract( $this->toggle );
		
		if( empty( $id ) )
			$id = "tify_modal-toggle-". $target;
		
		$output  = "";
		$output .= "<a href=\"{$href}\"";
		$output .= " title=\"{$link_title}\"";
		$output .= " id=\"{$id}\" class=\"tify_modal-toggle". ( $class ? ' '.$class :'' ) ."\"";
		foreach( $link_attrs as $i => $j )
			$output .= " {$i}=\"{$j}\"";
		$output .= " data-toggle=\"tify_modal\" data-target=\"tify_modal-{$target}\"";
		$output .= ">";
		$output .= $text;
		$output .= "</a>";
	
		if( is_array( $this->modal ) )
			add_action( 'wp_footer', array( $this, 'ModalDisplay' ) );
	
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/* = AFFICHAGE DE LA FENÊTRE MODALE = */
	final public function ModalDisplay()
	{
		extract( $this->modal );
		
		$output  = "";
		$output .= "<div id=\"tify_modal-{$target}\"";
		// Classe
		$output .= " class=\"tify_modal modal fade $class\"";
		// Options
		foreach( $options as $option_name => $option_value )
			$output .= " data-{$option_name}=\"{$option_value}\"";
		// Attributs complémentaires
		$output .=  "tabindex=\"-1\" role=\"dialog\">\n";
	
		// Pré-affichage
		$before = apply_filters( 'tify_modal_before-'. $target, $before );
		$before = apply_filters( 'tify_modal_before', $before );
		$output .= $before;
	
		$backdrop_button = apply_filters( 'tify_modal_backdrop_button-'. $target, $backdrop_button );
		$backdrop_button = apply_filters( 'tify_modal_backdrop_button', $backdrop_button );
		if( $backdrop_button )
			$output .= 	"\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $this->backdrop_button ) ? "<span aria-hidden=\"true\">&times;</span>" : $backdrop_button ) .
						"</button>\n";
	
		// Ouverture de la modal
		$output .= "\t<div class=\"modal-dialog". ( $size === 'lg' ? ' modal-lg' : '' ) ."\" role=\"document\">\n";

		// Ouverture du Contenu
		$content = "\t\t<div class=\"modal-content\">";

		/// Entête
		$header  = "";
		$header_button = apply_filters( 'tify_modal_header_button-'. $target, $header_button );
		$header_button = apply_filters( 'tify_modal_header_button', $header_button );
		if( $header_button )
			$header .= "\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $header_button ) ? "<span aria-hidden=\"true\">&times;</span>" : $header_button ) .
						"</button>\n";

		//// Titre de la modale
		$title = apply_filters( 'tify_modal_title-'. $target, $title );
		$title = apply_filters( 'tify_modal_title', $title );		
		$header .= "\t\t\t\t<h4 class=\"modal-title\">{$title}</h4>\n";
		$header = apply_filters( 'tify_modal_header-'. $target, $header, $title );
		$header = apply_filters( 'tify_modal_header', $header, $title );
		$content .= "\t\t\t<div class=\"modal-header\">{$header}</div>";

		//// Corps de la modale
		$body = apply_filters( 'tify_modal_body-'. $target, $body );
		$body = apply_filters( 'tify_modal_body', $body );
		$content .= "\t\t\t<div class=\"modal-body\">{$body}</div>\n";

		// Pied de page
		$footer = apply_filters( 'tify_modal_footer-'. $target, $footer );
		$footer = apply_filters( 'tify_modal_footer', $footer );
		$content .= "\t\t\t<div class=\"modal-footer\">{$footer}</div>\n";

		// Traitement du contenu
		$content = apply_filters( 'tify_modal_content-'. $target, $content, $header, $body, $footer );
		$content = apply_filters( 'tify_modal_content', $content, $header, $body, $footer );

		// Fermeture du contenu
		$content = $content. "</div>\n";
		$output .= $content;

		$output .= "\t</div>\n";

		// Post-affichage
		$after = apply_filters( 'tify_modal_after-'. $target, $after );
		$after = apply_filters( 'tify_modal_after', $after );
		$output .= $after;

		$output .= "</div>\n";

		if( $echo )
			echo $output;
		else
			return $output;
	}
}