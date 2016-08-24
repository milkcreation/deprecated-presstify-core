<?php
namespace tiFy\Components\CustomColumns;

use tiFy\Environment\App;

abstract class Factory extends App
{
	/* = ARGUMENTS = */
	// Configuration
	private			$Config			= null;	
	
	private static 	$Instance 		= 0;
		
	/* = CONSTRUCTEUR = */
	public function __construct( $args )
	{
		if( empty( $args['env'] ) || empty( $args['type'] ) )
			return;
		
		self::$Instance++;
		
		// Définition de la configuration
		$defaults = array(
			'sortable'	=> false,
			'position'	=> 0,	
			'title'		=> '',
			'column'	=> 'tiFyColumn-'. self::$Instance
		);
		
		if( is_callable( array( $this, 'getDefaults' ) ) )
			$defaults = wp_parse_args( (array) call_user_func( array( $this, 'getDefaults' ) ), $defaults );
				
		$this->Config = wp_parse_args( $args, $defaults );
		
		// Initialisation de la vue courante
		add_filter( "manage_edit-{$args['type']}_columns", array( $this, '_Header' ) );
	
		switch( $args['env'] ) :
			case 'post_type' :
				add_action( "manage_{$args['type']}_posts_custom_column", array( $this, '_Content' ), 10, 2 );
				break;
			case 'taxonomy' :
				add_filter( "manage_{$args['type']}_custom_column", array( $this, '_Content' ), 10, 3 );
				break;
		endswitch;
	}
	
	/* = RÉCUPERATION DE LA CONFIGURATION = */
	final public function getConfig( $index = null )
	{
		if( ! $index ) :
			return $this->Config;
		elseif( isset( $this->Config[$index] ) ) :
			return $this->Config[$index];
		endif;
	}
	
		
	/** == Déclaration de la colonne == **/
	final public function _Header( $columns )
	{	
		if( ! empty( $this->Config['position'] ) ) :
			$newcolumns = array(); $n = 0;
			foreach( $columns as $key => $column ) :
				if( $n === (int) $this->Config['position'] ) 
					$newcolumns[$this->Config['column']] = $this->Config['title'];
				$newcolumns[$key] = $column;
				$n++;				
			endforeach;
			$columns = $newcolumns;
		else :
			$columns[$this->Config['column']] = $this->Config['title'];
		endif;

		return $columns;
	}
	
	/** == == **/
	final public function _Content()
	{
		switch( $this->Config['env'] ) :
			case 'post_type':
				$column_name	= func_get_arg( 0 );
				// Bypass
				if( $column_name !== $this->Config['column'] )
					return;
			break;
			case 'taxonomy':
				$output			= func_get_arg( 0 );
				$column_name	= func_get_arg( 1 );
				// Bypass
				if( $column_name !== $this->Config['column'] )
					return $output;
			break;
		endswitch;
				
		call_user_func_array( array( $this, 'content' ), func_get_args() );
	}
		
	/* = MÉTHODES PUBLIQUES = */
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array();	
	}
			
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	/* = CHARGEMENT DE LA PAGE COURANTE = */
	public function current_screen( $current_screen )
	{
		
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		
	}
	
	/** == Affichage des données de la colonne == **/
	/*public function content()
	{		
		echo __( 'Pas de données à afficher', 'tify' );
	}*/	
}