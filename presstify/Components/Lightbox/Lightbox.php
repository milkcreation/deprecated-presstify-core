<?php
namespace tiFy\Components\Lightbox;

use \tiFy\Environment\Component;

final class Lightbox extends Component
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'wp_enqueue_scripts'
	);
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
	}
	
	/* = DECLENCHEURS = */
	public function wp_enqueue_scripts()
	{
		//wp_enqueue_script( 'tiFyComponentsLightbox', self::getUrl() .'/Lightbox.js', array( 'jquery', 'imagelightbox' ), '160902' );
	}
}