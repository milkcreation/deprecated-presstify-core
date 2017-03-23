<?php
namespace tiFy\Components\PDFViewer;

class PDFViewer extends \tiFy\Environment\Component
{
    /* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts'
	);
	
	// Instances
	static $Instance		= 0;
	
	/**
	 * @todo Ecrire les helpers dans Components.php du dossier helpers
	 */
	public function __construct()
	{
	    parent::__construct();
	    require_once( 'Helpers.php' );
	}
	
    /* = DECLENCHEURS = */
	/** == Inititalisation globale == **/
	final public function init()
	{				
		// Déclaration des scripts
		$worker_src = self::getUrl() .'/pdf.worker.js';
		wp_register_script( 'pdf-js', self::getUrl() .'/pdf.js', array(), '1.6.210', true );
		wp_register_style( 'tiFyComponentsPDFViewer', self::getUrl() .'/PDFViewer.css', array( 'dashicons' ), '170321' );
		wp_register_script( 'tiFyComponentsPDFViewer', self::getUrl() .'/PDFViewer.js', array( 'jquery', 'pdf-js' ), '170321', true );
		wp_localize_script( 'tiFyComponentsPDFViewer', 'tiFyComponentsPDFViewer', array( 'workerSrc' => $worker_src ) );
	}
	
    /** ==  Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		// Bypass
		if( ! self::getConfig( 'enqueue_scripts' ) )
			return;
		
		wp_enqueue_style( 'tiFyComponentsPDFViewer' );
		wp_enqueue_script( 'tiFyComponentsPDFViewer' );
	}
	
	/* = AFFICHAGE = */
	public static function display( $pdf_url = null, $args = array(), $echo = true )
	{
	    // Bypass
	    if( ! $pdf_url )
	        return;
	    // Incrémentation de l'intance
		self::$Instance++;
	    $defaults = array(
	        'id'           => 'tiFyPDFViewer-'.self::$Instance,
	        'class'        => '',
	        'scale'        => 1, // Echelle personnalisée
	        'width'        => null, // Se base sur l'échelle du PDF
	        'full_width'   => false, // Prend toute la largeur du conteneur
	        'navigation'   => true
	    );
	    $args = wp_parse_args( $args, $defaults );
	    extract( $args );
	    
	    $output = "<div class=\"tiFyPDFViewer {$class}\" 
	                   id=\"{$id}\" 
	                   data-navigation=\"" . (int) $navigation . "\"
	                   data-file_url=\"{$pdf_url}\" 
	                   data-scale=\"{$scale}\" 
	                   data-width=\"{$width}\" 
	                   data-full_width=\"" . (int) $full_width . "\">\n";
	    $output .= "\t<div class=\"tiFyPDFViewer-inner\">\n";
	    if( $navigation ) :
	       $output .= "\t\t<button type=\"button\" class=\"tiFyPDFViewer-nav tiFyPDFViewer-nav--prev\">".__( 'Précédent', 'tify' )."</button>\n";
	       $output .= "\t\t<span class=\"tiFyPDFViewer-page\"><span class=\"tiFyPDFViewer-pageNum\"></span><span class=\"tiFyPDFViewer-pageCount\"></span></span>\n";
	       $output .= "\t\t<button type=\"button\" class=\"tiFyPDFViewer-nav tiFyPDFViewer-nav--next\">".__( 'Suivant', 'tify' )."</button>\n";
	    endif;
	    $output .= "\t\t<canvas class=\"tiFyPDFViewer-canvas\"></canvas>\n";
	    $output .= "\t</div>\n";
	    $output .= "</div>";
	    
	    if( $echo )
	        echo $output;
	    else 
	        return $output;
	}
}