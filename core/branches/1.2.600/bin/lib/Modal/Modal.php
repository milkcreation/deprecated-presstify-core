<?php
namespace tiFy\Lib\Modal;

class Modal extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	/** == CONFIGURATION == **/
	// Attributs par défaut du lien de déclenchement de la fenêtre modale
	private static $defaultToggleAttrs	= array(
		'target'			    => null,	
		'id' 				=> '',
		'class'				=> '',
		'href'				=> '',
		'text'				=> '',
		'title'				=> '',
		'attrs'				=> array(),
		'modal'				=> array()
	);
	// Attributs par défaut de la fenêtre modale
	private	static $defaultModalAttrs 		= array(
		'target'			=> null,
		'id'				=> '',
		'class'				=> '',
		'attrs'				=> array(),
		
		'options'			=> array(
			'backdrop' 			=> true, // false | 'static'
			'keyboard'			=> true,
			'show'				=> true
		),		
		
		'before'			=> '',
		'after' 			=> '',
			
		'backdrop_button'	=> false,
		'animations'		=> 'fade',	
		
		'dialog'			=> array(
			// Taille de la fenêtre de dialog lg | sm | null (defaut :normal)	
			'size'				=> 'lg',			
			
			'header_button'		=> false,
			'title'				=> '',
			'body'				=> '',
			'footer'			=> ''			
		),
		'in_footer'			=> true
	);
	
	// Liste des modales instanciée dans le dom
	private static $Modals		= array();
	
	// Instance
	protected static $Instance; 
	
	/* = Traitement des attributs du lien de déclenchement = */
	private static function parseToggleAttrs( $args = array() )
	{	
		$args = wp_parse_args( $args, self::$defaultToggleAttrs );
		
		if( empty( $args['target'] ) )
			$args['target'] = uniqid();
		
		if( empty( $args['id'] ) )
			$args['id'] = "tiFyModal-toggle--". $args['target'];
		
		if( empty( $args['href'] ) )
			$args['href'] = "#". $args['id'];
		
		return $args;
	}
	
	/* = Traitement des attributs de la fenêtre modal = */
	private static function parseModalAttrs( $args = array() )
	{	
		$args = wp_parse_args( $args, self::$defaultModalAttrs );
		
		if( empty( $args['target'] ) )
			$args['target'] = uniqid();
		
		if( empty( $args['id'] ) )
			$args['id'] = "tiFyModal-display--". $args['target'];
		
		$args['options'] = wp_parse_args( $args['options'], self::$defaultModalAttrs['options'] );

		if( ! empty( $args['dialog'] ) )
			$args['dialog'] = wp_parse_args( $args['dialog'], self::$defaultModalAttrs['dialog'] );
		
		return $args;
	}
	
	
	/* = Lien de déclenchement d'une modale = */
	public static function toggle( $args = array(), $echo = true )
	{		
	    $args = self::parseToggleAttrs( $args );
		
		$output  = "";
		$output .= "<a href=\"{$args['href']}\"";
		$output .= " id=\"{$args['id']}\" class=\"tiFyModal-toggle". ( $args['class'] ? ' '. $args['class'] : '' ) ."\"";
		
		if( $args['title'] )
			$output .= " title=\"{$args['title']}\"";
		
		foreach( (array) $args['attrs'] as $i => $j )
			$output .= " {$i}=\"{$j}\"";
		
		$output .= " data-toggle=\"tiFyModal\" data-target=\"{$args['target']}\"";
		$output .= ">";
		$output .= $args['text'];
		$output .= "</a>";		
		
		// Chargement de la modal 
		$modal_attrs = isset( $args['modal'] ) ? $args['modal'] : array();

		if( $modal_attrs !== false ) :
			$modal_attrs['target'] = $args['target'];				
			if( ! isset( $modal_attrs['options']['show'] ) ) :
				$modal_attrs['options']['show'] = false;
			endif;
			
			static::display( $modal_attrs );
		endif;
	
		if( $echo )
			echo $output;
		
		return $output;
	}
	
	/* = Affichage de la fenêtre de dialogue = */
	public static function display( $args = array(), $echo = true )
	{
		// Traitement des arguments
		$args = self::parseModalAttrs( $args );
		
		// Bypass
		if( in_array( $args['target'], self::$Modals ) )
			return;
			
		$output  = "";
		$output .= "<div id=\"{$args['id']}\"";
		
		// Classe
		$output .= " class=\"tiFyModal". ( $args['class'] ? ' '. $args['class'] : '' ) ." modal fade\"";
				
		// Options
		foreach( $args['options'] as $name => $value )
			$output .= " data-{$name}=\"{$value}\"";
		
		// Attributs de balise HTML
		foreach( (array) $args['attrs'] as $i => $j )
			$output .= " {$i}=\"{$j}\"";	
			
		// Attributs complémentaires
		$output .=  "tabindex=\"-1\" role=\"dialog\"";
		
		$output .= " data-role=\"tiFyModal\" data-id=\"{$args['target']}\"";
		
		$output .= ">\n";
	
		// Pré-affichage
		$output .= $args['before'];

		// Bouton du fond
		if( $args['backdrop_button'] ) :
			$output .= 	"\t<button type=\"button\" class=\"backdrop-close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $args['backdrop_button'] ) ? "<span aria-hidden=\"true\">&times;</span>" : (string) $args['backdrop_button'] ) .
						"</button>\n";
		endif;


		// Ouverture de la fenêtre de dialog
		$output .= "\t<div class=\"modal-dialog". ( in_array( $args['dialog']['size'], array( 'lg', 'sm' ) ) ? ' modal-'. $args['dialog']['size'] : '' ) ."\" role=\"document\">\n";

		// Ouverture du contenu
		$output .= "\t\t<div class=\"modal-content\">";

		/// Entête
		$header  = "";				
		//// Bouton de fermeture
		if( $args['dialog']['header_button'] ) :
			$header .= "\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">".
						( is_bool( $args['dialog']['header_button'] ) ? "<span aria-hidden=\"true\">&times;</span>" : (array) $args['dialog']['header_button'] ) .
						"</button>\n";			
		endif;	
				
		//// Titre de la modale
		if( $args['dialog']['title'] ) :
			$header .= "\t\t\t\t<h4 class=\"modal-title\">{$args['dialog']['title']}</h4>\n";
		endif;
				
		if( $header ) :
			$output .= "\t\t\t<div class=\"modal-header\">{$header}</div>";
		endif;	
			
		/// Corps de la modale
		$output .= "\t\t\t<div class=\"modal-body\">{$args['dialog']['body']}</div>\n";
			
		// Pied de page
		if( $args['dialog']['footer'] ) :
			$output .= "\t\t\t<div class=\"modal-footer\">{$args['dialog']['footer']}</div>\n";
		endif;
			
		// Fermeture du contenu
		$output .= "\t\t</div>\n";
		
		// Fermeture de la fenêtre de dialog
		$output .= "\t</div>\n";


		// Post-affichage
		$output .= $args['after'];
		$output .= "</div>\n";
		
		if( $args['in_footer'] ) :
			add_action( 
				'wp_footer', 
				function() use( $output ){
					echo $output;
				},
				0
			);
		elseif( $echo ) :
			echo $output;
		endif;
		
		array_push( self::$Modals, $args['target'] );
		
		// Chargement des scripts
		if( ! self::$Instance++ ) :
			$url = self::tFyAppUrl( get_class() ) .'/Modal.min.js';
			add_action( 
				'wp_footer', 
				function() use( $url ){
				?><script type="text/javascript" src="<?php echo $url;?>"></script><script type="text/javascript">/* <![CDATA[ */jQuery(document).ready(function($){$('[data-role="tiFyModal"]').each(function(){$(this).modal();});$(document).on( 'click','[data-toggle="tiFyModal"]',function(e){e.preventDefault();$('[data-id="'+$(this).data('target')+'"]').modal('show');});});/* ]]> */</script><?php
				},
				1
			);
		endif;
	}
}