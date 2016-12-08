<?php
namespace tiFy\Core\Forms;

use tiFy\Environment\Core;

class Forms extends Core
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify',
		'init',
		'admin_init',
		'wp'
	);		
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'after_setup_tify' 	=> 11,
		'init'				=> 1,
		'wp'				=> 0		
	);
	
	// Liste des Formulaires déclarés
	private static $Registered			= array();
	
	// Formulaire courant 
	private static $Current				= null;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{		
		parent::__construct();
		
		// Initialisation des classe de contrôle
		new Addons;
		new Buttons;
		new FieldTypes;
	}

	/* = DECLENCHEURS = */
	/** == Initialisation l'interface d'administration == **/
	final public function after_setup_tify()
	{		
		// Boutons
		/// Instanciation	
		Buttons::init();
		/// Déclaration des boutons personnalisés
		do_action( 'tify_form_register_button' );
		
		// Types de champs
		/// Instanciation
		FieldTypes::init();
		/// Déclaration des types de champs personnalisés
		do_action( 'tify_form_register_type' );
		
		// Addons
		// Instanciation
		Addons::init();
		// Déclaration des addons personnalisés
		do_action( 'tify_form_register_addon' );
	}
	
	/** == Déclaration des formulaires == **/
	final public function init()
	{
		if( is_admin() )
			$this->registration();
	}
	
	/** == Déclaration des formulaires pour les requêtes ajax== **/
	final public function admin_init()
	{
		if( defined( 'DOING_AJAX' ) )
			$this->registration();
	}
	
	/** == Chargement de Wordpress complet == **/
	final public function wp()
	{
		if( ! is_admin() )
			$this->registration();
		
		foreach( self::getList() as $form ) :
			self::setCurrent( $form );
			$form->handle()->proceed();
			self::resetCurrent();
		endforeach;
	}
		
	/* = PARAMETRAGE = */	
	/** == Déclaration des formulaires == **/
	private function registration()
	{
		// Déclaration des formulaires
		/// Depuis la configuration statique
		foreach( (array) self::getConfig() as $id => $attrs ) :
			$this->register( $id, $attrs );
		endforeach;
			
		/// Depuis la déclaration dynamique	
		do_action( 'tify_form_register' );		
	}	
	
	/** == Déclaration d'un formulaire == **/
	public static function register( $id, $attrs = array() )
	{
		$attrs['ID'] = $id;
		$form = self::$Registered[$id] = new Form\Form( $id, $attrs );
	
		return $form->getID();
	}
	
	/* = CONTROLEURS = */
	/** == Récupération d'un formulaire déclaré == **/
	public static function has( $id )
	{
		return isset( self::$Registered[ $id ] );
	}
	
	/** == Récupération d'un formulaire déclaré == **/
	public static function get( $id )
	{
		if( self::has( $id ) )
			return self::$Registered[ $id ];
	}
	
	/** == Récupération de la liste des formulaires == **/
	public static function getList()
	{
		return self::$Registered;	
	}	
	
	/** == Définition du formulaire courant == **/
	public static function setCurrent( $form = null )
	{
		if( ! is_object( $form ) )
			$form = self::get( $form );
				
		if( ! $form instanceof \tiFy\Core\Forms\Form\Form )
			return;
			
		self::$Current = $form;
		self::$Current->onSetCurrent();
		
		return self::$Current;
	}
	
	/** == Définition du formulaire courant == **/
	public static function getCurrent()
	{
		return self::$Current;
	}
	
	/** == Définition du formulaire courant == **/
	public static function resetCurrent()
	{
		if( self::$Current )
			self::$Current->onResetCurrent();
		
		self::$Current = null;
	}
	
	/** == Affichage du formulaire == **/
	public static function display( $form_id = null, $echo = false )
	{

		// Bypass
		if( ! $form = self::setCurrent( $form_id ) )
			return;

		// Traitement des options du formulaire	
		$output  = "";
		$output .= "\n<div id=\"tiFyForm-{$form_id}\" class=\"tiFyForm\">";
		$output .= $form->display( false );
		$output .= "\n</div>";
		
		if( $echo )
			echo $output;
		
		return $output;
	}
}