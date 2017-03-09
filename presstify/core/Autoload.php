<?php
namespace tiFy\Core;

class Autoload extends \tiFy\Environment\Core
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify'
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		/* = Après l'instanciation des plugins et des sets = */	
		'after_setup_tify' => 1	
	);
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Chargement des contrôleurs non configurable
		new AjaxActions;
		new Control\Control;
		new Capabilities;		
		new Meta\Meta;		
		new Params;
		new ScriptLoader\ScriptLoader;		
	}
	
	
	/* = DECLENCHEUR = */
	/** == Au chargement de tiFy == **/
	final public function after_setup_tify()
	{
		// Chargement des contrôleurs configurable
		new	CustomType\CustomType;
		new Db\Db;
		new Templates\Templates;
		new Options\Options;		
		new Taboox\Taboox;
		new Upload\Upload;
		/// Formulaires
		new Forms\Forms;
		new Security\Security;
	}
}