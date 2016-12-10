<?php
namespace tiFy\Core\Templates\Front;

class Factory extends \tiFy\Core\Templates\Factory
{
	/* = ARGUMENTS = */
	// DECLENCHEURS
	/// Liste des actions à déclencher
	protected $CallActions					= array(
		'init',
		'template_redirect',
		'wp_enqueue_scripts'		
	);
	
	// Contexte d'execution
	protected static $Context				= 'front';
	
	// Liste des modèles prédéfinis
	protected static $Models				= array(
		'AjaxListTable',
		'EditForm',
		'ListTable'
	);	
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function init()
	{			
		// Bypass
		if( ! $this->getAttr( 'cb' ) && ! $this->getAttr( 'model' ) )
			return;
			
		// Instanciation de la classe
		if(  ! $this->getAttr( 'cb' ) ) : 
			$model = $this->getAttr( 'model' );		
			$className = "\\tiFy\\Core\\Templates\\". ucfirst( self::$Context ) ."\\Model\\{$model}\\{$model}";
		else :
			$className = $this->getAttr( 'cb' );
		endif;

		if( ! class_exists( $className ) )
			return;
		$this->TemplateCb = new $className( $this->getAttr( 'args', null ) );

		// Création des methodes dynamiques
		$factory = $this;
		$this->TemplateCb->template = function() use( $factory ){ return $factory; };
		$this->TemplateCb->db = function() use( $factory ){ return $factory->db(); };
		$this->TemplateCb->label = function( $label = '' ) use( $factory ){ if( func_num_args() ) return $factory->getLabel( func_get_arg(0) ); };		
		$this->TemplateCb->getConfig = function( $attr, $default = '' ) use( $factory ){ if( func_num_args() ) return call_user_func_array( array( $factory, 'getAttr' ), func_get_args() ); };	
			
		//
		if( ! $this->getAttr( 'base_url' ) )
			$this->setAttr( 'base_url', \site_url( $this->getAttr( 'route' ) ) );
		
		
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
		if( ! $this->TemplateCb )
			return;
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
		// Bypass
		if( ! $this->TemplateCb )
			return;
		
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->TemplateCb, '_wp_enqueue_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, '_wp_enqueue_scripts' ) );
		endif;
		if( method_exists( $this->TemplateCb, 'wp_enqueue_scripts' ) ) :
			call_user_func( array( $this->TemplateCb, 'wp_enqueue_scripts' ) );
		endif;
	}
}