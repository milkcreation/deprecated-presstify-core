<?php
namespace tiFy\Core\Front;

use tiFy\Environment\App;
use tiFy\Core\Front\Front;
use tiFy\Core\Db\Db;
use tiFy\Core\Labels\Labels;

final class Factory extends App
{
	/* = ARGUMENTS = */
	// DECLENCHEURS
	/// Liste des actions à déclencher
	protected $CallActions					= array(
		'init',
		'template_redirect',
		'wp_enqueue_scripts'		
	);
	
	private static $Models					= array(
		'AjaxListTable'	
	);
	
	// PARAMETRES GENERAUX
	/// Identifiant
	private		$TemplateID					= null;

	/// Attributs du template
	private 	$Attrs						= array();
	
	/// Classe de rappel du template
	private		$TemplateCb					= null;

	/// Classe de rappel de la base de donnée
	private		$DbCb						= null;

	/// Class de rappel des intitulés
	private		$LabelCb					= null;	
	
	/* = CONSTRUCTEUR = */
	public function __construct( $id, $attrs = array() )
	{
		parent::__construct();
		
		// Définition de l'identifiant
		$this->TemplateID = $id;
		
		// Initialiasation des attributs
		$this->Attrs = $attrs;	
	}
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function init()
	{			
		// Instanciation de la classe
		$className = $this->getAttr( 'cb' );
		$this->TemplateCb 			= new $className;
		
		// Création des methodes dynamiques
		$factory = $this;
		$this->TemplateCb->template = function() use( $factory ){ return $factory; };
		$this->TemplateCb->db 		= function() use( $factory ){ return $factory->db(); };
		$this->TemplateCb->label 	= function( $label = '' ) use( $factory ){ return $factory->getLabel( func_get_arg(0) ); };		
		
		// Déclenchement de l'action dans la classes du template
		if( method_exists( $this->TemplateCb, '_init' ) ) :
			call_user_func( array( $this->TemplateCb, '_init' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'init' ) ) :
			call_user_func( array( $this->TemplateCb, 'init' ) );
		endif;
	}
	
	/** == Affichage du template == **/
	final public function template_redirect()
	{		
		// Bypass
		if( ! preg_match( '/^\/'. preg_quote( $this->getAttr( 'route', $this->getID() ), '/' ) .'\/?$/', Front::getRoute() ) )
			return;
					
		// Déclenchement de l'action dans la classes du template			
		if( method_exists( $this->TemplateCb, '_current_screen' ) ) :
			call_user_func( array( $this->TemplateCb, '_current_screen' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'current_screen' ) ) :
			call_user_func( array( $this->TemplateCb, 'current_screen' ) );
		endif;	
			
		$this->render();	
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{						
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->TemplateCb, '_wp_enqueue_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, '_wp_enqueue_scripts' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'wp_enqueue_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, 'wp_enqueue_scripts' ) );
		endif;
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération de l'identifiant == **/
	final public function getID()
	{
		return $this->TemplateID;
	}
	
	/** == Récupération de l'identifiant == **/
	final public function getAttr( $attr, $default = '' )
	{
		if( isset( $this->Attrs[$attr] ) )
			return $this->Attrs[$attr];
		
		return $default;
	}
		
	/** == Récupération des intitulées == **/
	final public function getLabel( $label = '' )
	{
		if( ! is_null( $this->LabelCb ) )
			return $this->LabelCb->Get( $label );
		
		if( $this->LabelCb = Labels::Get( $this->getAttr( 'label', $this->getID() ) ) ) :	
		else :
			$this->LabelCb = Labels::Register( $this->getID() );
		endif;

		return $this->LabelCb->Get( $label );
	}
	
	/** == Récupération de la base de données == **/
	final public function db()
	{
		if( ! is_null( $this->DbCb ) )
			return $this->DbCb;
			
		if( $this->DbCb = Db::Get( $this->getAttr( 'db', $this->getID() ) ) ) :		
		else :
			$this->DbClass = Db::Get( 'posts' );
		endif;

		return 	$this->DbCb;
	}
		
	/* = AFFICHAGE = */
	/** == Page de l'interface d'administration == **/
	final public function render()
	{
		return $this->TemplateCb->render();	
	}
}