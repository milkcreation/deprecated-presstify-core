<?php
namespace tiFy\Components\PdfViewer;

use tiFy\Lib\Modal\Modal as tiFyModal;

class PdfViewer extends \tiFy\Environment\Component
{
	/**
	 * Liste des Actions à déclencher
	 * @var array
	 */
	protected $tFyAppActions				= array(
		'init',
		'wp_enqueue_scripts',
	    'wp_ajax_tify_components_pdfviewer_modal',
		'wp_ajax_nopriv_tify_components_pdfviewer_modal'
	);
	/**
     * Cartographie des méthodes de rappel des actions
     * @var array
     */
    protected $tFyAppActionsMethods    = array(
        'wp_ajax_tify_components_pdfviewer_modal'           => 'wp_ajax',
		'wp_ajax_nopriv_tify_components_pdfviewer_modal'    => 'wp_ajax'
    );
	
	/**
	 * Attributs par défaut de la visionneuse
	 * @var array
	 */
	private static $defaultPdfArgs = array(
	    'id'           => '',
        'class'        => '',
        'scale'        => 1, // Echelle personnalisée
        'width'        => null, // Se base sur l'échelle du PDF
        'full_width'   => false, // Prend toute la largeur du conteneur
        'navigation'   => true,
        'download'     => true,
        'filename'     => ''
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
		'pdf'				=> array(),
	    'modal'             => array()
	);
	
	/**
	 * Attributs par défaut de la modal
	 * @var array
	 */
	private static $defaultModalAttrs = array(
	    'id'				=> '',
        'target'			=> '',
	    'class'             => 'tiFyPdfViewer-modal',
        'options'			=> array(
        	'backdrop' 			=> true,
        	'keyboard'			=> true,
        	'show'				=> false
        ),
        'dialog'			=> array(
        	'size'				=> null,
        	'title'				=> '',
        	'header_button'		=> true
        )    
	);
	
	/**
	 * Instances
	 * @var integer
	 */
	static $Instance		= 0;
		
	/**
	 * DECLENCHEURS
	 */
	/**
	 * Inititalisation globale
	 */
	final public function init()
	{				
		/**
		 * Déclaration des scripts
		 * @since 1.0.327 Chargement des scripts en CDN + conservation locale
		 */
		$worker_src = '//cdnjs.cloudflare.com/ajax/libs/pdf.js/1.7.225/pdf.worker.min.js';
		wp_register_script( 'pdf-js', '//cdnjs.cloudflare.com/ajax/libs/pdf.js/1.7.225/pdf.min.js', array(), '1.7.225', true );
		wp_register_style( 'tiFyComponentsPdfViewer', self::tFyAppUrl() .'/PdfViewer.css', array( 'dashicons' ), '170321' );
		wp_register_script( 'tiFyComponentsPdfViewer', self::tFyAppUrl() .'/PdfViewer.js', array( 'jquery', 'jquery-ui-widget', 'pdf-js' ), '170321', true );
		wp_localize_script( 'tiFyComponentsPdfViewer', 'tiFyComponentsPdfViewer', array( 'workerSrc' => $worker_src ) );
		/**
		 * Modal
		 */
		wp_register_style( 'tiFyComponentsPdfViewerModal', self::tFyAppUrl() .'/Modal.css', array( 'tiFyComponentsPdfViewer' ), '170615' );
		wp_register_script( 'tiFyComponentsPdfViewerModal', self::tFyAppUrl() .'/Modal.js', array( 'tiFyComponentsPdfViewer' ), '170615', true );
	}
	
	/**
	 * Mise en file des scripts
	 */
	final public function wp_enqueue_scripts()
	{
		// Bypass
		if( ! self::tFyAppConfig( 'enqueue_scripts' ) )
			return;
		
		if( self::tFyAppConfig( 'modal' ) ) :
		    wp_enqueue_style( 'tiFyComponentsPdfViewerModal' );
		    wp_enqueue_script( 'tiFyComponentsPdfViewerModal' );
		else :
		    wp_enqueue_style( 'tiFyComponentsPdfViewer' );
		    wp_enqueue_script( 'tiFyComponentsPdfViewer' );
		endif;
	}
	
	/**
	 * Action AJAX de récupération de la visionneuse
	 */
	public function wp_ajax()
	{
	    if( $output = self::display( $_POST['file_url'], $_POST['pdf'], false ) ) :
	       wp_send_json_success( $output );
	    else :
	       wp_send_json_error();
	    endif;
	}
	
	/**
	 * MÉTHODES
	 */
	/**
	 * Traitement des attributs de la visionneuse
	 * @since 1.0.327
	 * @param array $args Attributs de la visionneuse
	 * @param $footer_buttons Affichage des organes de navigation dans le footer de la modal
	 * @return string|array
	 */
	private static function parsePdfArgs( $args = array(), $footer_buttons = false )
	{
	    $args = wp_parse_args( $args, self::$defaultPdfArgs );
	    
	    if( empty( $args['id'] ) ) :
	       $args['id'] = 'tiFyPdfViewer--'.self::$Instance;
	    endif;
	    if( empty( $args['filename'] ) ) :
	       $args['filename'] = __( 'Fichier', 'tify' );
	    endif;
	    if( $footer_buttons ) :
	       $args['navigation'] = false;
	       $args['download'] = false;
	    endif;
	    
	    return $args;
	}
	
    /**
	 * Traitement des attributs du lien de déclenchement
	 * @since 1.0.327
	 * @param array $args Attributs du lien de déclenchement
	 * @return string|array
	 */
	private static function parseToggleAttrs( $args = array() )
	{	
		$args = wp_parse_args( $args, self::$defaultToggleAttrs );
		
		if( empty( $args['id'] ) ) :
			$args['id'] = 'tiFyPdfViewer-modalToggle--'.self::$Instance;
		endif;
		
		if( empty( $args['href'] ) ) :
			$args['href'] = '#tiFyPdfViewer-modal--'.self::$Instance;
		endif;
		
		if( empty( $args['attrs']['data-target'] ) ) :
		    $args['attrs']['data-target'] = '#tiFyPdfViewer-modal--'.self::$Instance;
		endif;
		
		return $args;
	}
	
	/**
	 * Traitement des attributs de la modal
	 * @since 1.0.327 
	 * @param string $pdf_url Url du document Pdf
	 * @param array $args Attributs de la modal
	 * @param $footer_buttons Affichage des organes de navigation dans le footer de la modal
	 */
	private static function parseModalAttrs( $pdf_url = null, $args = array(), $footer_buttons = false )
	{
	    $args = wp_parse_args( $args, self::$defaultModalAttrs );
	    
	    if( empty( $args['id'] ) ) :
	       $args['id'] = 'tiFyPdfViewer-modal--'.self::$Instance;
	    endif;
	    if( empty( $args['target'] ) ) :
	       $args['target'] = 'tiFyPdfViewer-modal--'.self::$Instance;
	    endif;
	    if( empty( $args['dialog']['title'] ) ) :
	       if( ! is_null( $pdf_url ) ) :
	           $args['dialog']['title'] = wp_basename( $pdf_url );
	       else :
	           $args['dialog']['title'] = __( 'Fichier', 'tify' );
	       endif;
	    endif;
	    if( $footer_buttons ) :
	       $args['class'] .= ' tiFyPdfViewer-modal--footerButtons';
	       $args['dialog']['footer'] = empty( $args['dialog']['footer'] ) ? '' : $args['dialog']['footer'];
	       $args['dialog']['footer'] .= "<div class=\"tiFyPdfViewer-modalFooterButtons\">\n";
	       $args['dialog']['footer'] .= self::getPdfNavButton( 'download' );
	       $args['dialog']['footer'] .= self::getPdfNavButton( 'prev' );
	       $args['dialog']['footer'] .= self::getPdfNavButton( 'next' );
	       $args['dialog']['footer'] .= "</div>";
	    endif;
	    
	    return $args;
	}
    
	/**
	 * Récupération d'un organe de navigation (Suivant, Précédent, Téléchargement)
	 * @since 1.0.327 
	 * @param string $type Type de l'organe
	 * @return string|NULL
	 */
	protected static function getPdfNavButton( $type = '' )
	{
	    switch( $type ) :
    	    case 'prev' :
	            return "<button type=\"button\" class=\"tiFyPdfViewer-nav tiFyPdfViewer-nav--prev\">".__( 'Précédent', 'tify' )."</button>";
    	        break;
	        case 'next' :
	            return "<button type=\"button\" class=\"tiFyPdfViewer-nav tiFyPdfViewer-nav--next\">".__( 'Suivant', 'tify' )."</button>";
    	        break;
	        case 'download' :
	            return "<button type=\"button\" class=\"tiFyPdfViewer-download\">".__( 'Télécharger', 'tify' )."</button>";
    	        break;
    	    default :
    	        return null;
    	        break;
	    endswitch;
	}
	
	/**
	 * Affichage brut de la visionneuse Pdf
	 * @param string $pdf_url Url du document Pdf
	 * @param array $args Tableau associatif contenant les paramètres de la visionneuse
	 * @param string $echo Affichage ou retour de fonction
	 * @return void|string
	 */
	public static function display( $pdf_url = null, $args = array(), $echo = true )
	{
	    // Bypass
	    if( ! $pdf_url )
	        return;
	    // Incrémentation de l'intance
		self::$Instance++;
		
	    $args = self::parsePdfArgs( $args );
	    extract( $args );
	    
	    $output = "<div class=\"tiFyPdfViewer {$class}\" 
	                   id=\"{$id}\" 
	                   data-navigation=\"" . (int) $navigation . "\"
	                   data-file_url=\"{$pdf_url}\" 
	                   data-scale=\"{$scale}\" 
	                   data-width=\"{$width}\" 
	                   data-full_width=\"" . (int) $full_width . "\"
                       data-filename=\"{$filename}\">\n";
	    $output .= "\t<div class=\"tiFyPdfViewer-inner\">\n";
	    if( json_decode( $navigation ) ) :
	       $output .= self::getPdfNavButton( 'prev' );
	       $output .= "\t\t<span class=\"tiFyPdfViewer-page\"><span class=\"tiFyPdfViewer-pageNum\"></span><span class=\"tiFyPdfViewer-pageCount\"></span></span>\n";
	       $output .= self::getPdfNavButton( 'next' );
	    else :
	       $output .= "\t\t<span class=\"tiFyPdfViewer-page\"><span class=\"tiFyPdfViewer-pageNum\"></span><span class=\"tiFyPdfViewer-pageCount\"></span></span>\n";
	    endif;
	    if( json_decode( $download ) ) :
	       $output .= self::getPdfNavButton( 'download' );
	    endif;
	    $output .= "\t\t<canvas class=\"tiFyPdfViewer-canvas\"></canvas>\n";
	    $output .= "\t</div>\n";
	    $output .= "</div>";
	    
	    if( $echo )
	        echo $output;
	    else 
	        return $output;
	}
	
	/**
	 * Affichage d'un déclencheur permettant l'affichage de la visionneuse dans une modal
	 * @since 1.0.327
	 * @see tiFy\Lib\Modal\Modal::$defaultModalAttrs Attributs par défaut de la modal
	 * @param string $pdf_url Url du document Pdf
	 * @param array $args Tableau associatif contenant les paramètres du déclencheur et de la visionneuse
	 * @param bool $footer_buttons Affichage des organes de navigation dans le footer de la modal
	 * @param string $echo Affichage ou retour de fonction
	 * @return void|string
	 */
    public static function modalToggle( $pdf_url = null, $args = array(), $footer_buttons = true, $echo = true )
    {
        // Bypass
	    if( ! $pdf_url )
	        return;
        // Incrémentation de l'intance
		self::$Instance++;
		
		$args = self::parseToggleAttrs( $args );
		extract( $args );
		
		$output = "<a href=\"{$href}\"";
		$output .= " id=\"{$id}\" class=\"tiFyPdfViewer-modalToggle". ( $class ? ' '. $class : '' ) ."\"";
		
		if( $args['title'] )
			$output .= " title=\"{$title}\"";
		
		foreach( (array) $attrs as $i => $j )
			$output .= " {$i}=\"{$j}\"";
		
		$output .= " data-file_url=\"{$pdf_url}\"";
		$output .= " data-pdf=\"".htmlentities( json_encode( self::parsePdfArgs( $pdf, $footer_buttons ) ) )."\"";
		$output .= " data-footer_buttons=\"{$footer_buttons}\"";
		$output .= ">";
		$output .= $text;
		$output .= "</a>";
		
		$output .= tiFyModal::display( self::parseModalAttrs( $pdf_url, $modal, $footer_buttons ) );
        
        if( $echo )
	        echo $output;
	    else 
	        return $output;
    }
}