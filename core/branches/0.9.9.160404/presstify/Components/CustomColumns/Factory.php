<?php
namespace tiFy\Components\CustomColumns;

use \tiFy\Environment\App;

abstract class Factory extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'admin_init'
	);
	
	// Configuration
	protected	$PostTypes;
	protected	$Position;
	protected	$Sortable;
	protected	$Title;
	protected	$Column;
		
	/* = CONSTRUCTEUR = */
	function __construct( $args = array() )
	{
		parent::__construct();
		
		$defaults = array(
			'post_type' 	=> array(),
			'position'		=> 0,
			'sortable'		=> false,
			'title'			=> '',
			'column'		=> ''
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
				
		$this->PostTypes 	= ( is_string( $post_type ) ) ? array_map( 'trim', explode( ',', $post_type ) ) : ( is_array( $post_type ) ? $post_type : array() );
		$defaultColumn 		= preg_replace( '/'. preg_quote( 'tiFy\\Components\\CustomColumns\\Column\\', '\\') .'/', '', get_class( $this ) );
		
		foreach( (array) $this->PostTypes as $pt ) :
			$this->Position[$pt] 	= ( isset( $position[$pt] ) ) ? (int) $position[$pt] : ( is_numeric( $position ) ? $position : 0 ); 
			$this->Sortable[$pt] 	= ( isset( $sortable[$pt] ) ) ? (int) $sortable[$pt] : ( is_bool( $sortable ) ? $sortable : false );
			$this->Title[$pt] 		= ( isset( $title[$pt] ) ) ? (string) $title[$pt] : ( is_string( $title ) ? $title : '' );
			$this->Column[$pt] 		= ( isset( $column[$pt] ) ) ? (string) $column[$pt] : ( ! empty( $column ) ? (string) $column : $defaultColumn );	
		endforeach;
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	function admin_init()
	{
		foreach( (array) $this->PostTypes as $post_type ) :
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'Header' ) );			
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, '_Content' ), 10, 2 );
			if( ! empty( $this->Sortable[$post_type] ) )
				add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'Sortable' ) );
		endforeach;
	}	
	
	/** == Déclaration de la colonne == **/
	final public function Header( $columns )
	{
		$post_type = get_current_screen()->post_type;

		if( ! empty( $this->Position[$post_type] ) ) :
			$newcolumns = array(); $n = 0;
			foreach( $columns as $key => $column ) :
				if( $n === (int) $this->Position[$post_type] ) 
					$newcolumns[$this->Column[$post_type]] = $this->Title[$post_type];
				$newcolumns[$key] = $column;
				$n++;				
			endforeach;
			$columns = $newcolumns;
		else :
			$columns[$this->Column[$post_type]] = $this->Title[$post_type];
		endif;

		return $columns;
	}
	
	/** == == **/
	final public function _Content( $column, $post_id )
	{
		// Bypass
		if( $column != $this->Column[get_post_type( $post_id )] )
			return $column;
		
		call_user_func( array( $this, 'Content' ), $column, $post_id );
	}
		
	/** == Affichage des données de la colonne == **/
	public function Content( $column, $post_id )
	{		
		echo __( 'Pas de données', 'tify' );
	}	
}