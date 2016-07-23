<?php
namespace tiFy\Entity;

class Bootstrap
{
	/* = ARGUMENTS = */
	public $master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy $master )
	{
		// Instanciation de la classe de référence	
		$this->master = $master;
		// Actions et Filtres Wordpress
		add_action( 'after_setup_theme', array( $this, '_wp_after_setup_theme' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	final public function _wp_after_setup_theme()
	{
		$schemas = $this->master->params['schema'];
		foreach( $schemas as $entity_id => $opts )
			$this->{$entity_id} = new Entity( $entity_id, $opts );
	}
}