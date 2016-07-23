<?php
namespace tiFy\Core\Db;

use tiFy\Core\Db\Handle;
use tiFy\Core\Db\Meta;
use tiFy\Core\Db\Parse;
use tiFy\Core\Db\Query;
use tiFy\Core\Db\Select;

class Factory
{
	/* = ARGUMENTS =*/
	// Identifiant unique de la table
	public $ID 					= '';
	
	// Nom de la table en base
	public $Name				= '';
	
	// Préfixe des intitulés de colonne
	public $ColPrefix 			= '';
	
	// Liste des noms de colonne de la table
	public $ColNames			= array();
	
	// Cartographie des noms de colonnes
	public $ColMap				= array();
	
	// Nom de la clé primaire
	public $Primary				= null;
	
	// Liste des clés d'index
	public $IndexKeys			= array();
	
	// Nom des colonnes ouvertes à la recherche de termes
	public $SearchColNames		= array();
	
	// Nom de la table de metadonnée
	public $MetaType			= null;
	
	// 
	public $PrivateQueryVars 	= array( 
		'include', 	/** @todo deprecated alias item__in **/
		'item__in', 
		'exclude', 	/** @todo deprecated alias item__not_in **/ 
		'item__not_in', 
		'search', 	/** @todo deprecated alias s **/ 
		's',
		'per_page', 
		'paged', 
		'order', 
		'orderby', 
		'item_meta',
		'limit'
	);
	
	// Classe de rappel
	public $Handle, $Meta, $Parse, $Query, $Select;
					
	// Liste des attributs accessibles
	public $GetAttrs				= array( 'ID', 'Name', 'ColPrefix', 'ColNames', 'Primary', 'IndexKeys', 'SearchColNames', 'MetaType' );
		
	/* = CONSTRUCTEUR = */	
	public function __construct( $id, $attrs = array() )
	{
		// Définition de l'identifiant de la table
		$this->ID = $id;
		
		// Définition des attributs de la classe		
		$defaults = array(
			'name'			=> '',	/** @todo Cas où la table n'a pas pour nom de l'ID **/
			'primary'		=> '',	/** @todo Cas où la colonne de clé primaire n'est pas la première colonne **/
			'col_prefix'	=> '',
			'columns'		=> array(),
			'keys'			=> array(),
			'search'		=> array(),
			'meta'			=> null
		);	
		$attrs = wp_parse_args( $attrs, $defaults );			
		extract( $attrs, EXTR_SKIP );	
		
		/// Définition du préfixe par défaut des noms de colonnes 
		$this->setColPrefix( $col_prefix );
		
		/// Traitement des attributs de colonnes
		foreach( (array) $columns as $col_name => $attrs ) :
			$this->setColAttrs( $col_name, $attrs );
		endforeach;
		
		/// Définition de la clé primaire
		$this->setPrimary();
		
		/// Définition des clés d'index
		$this->setIndexKeys( $keys );
		
		/// Définition des colonnes ouvertes à la recherche de termes
		$this->setSearchColNames( $search );

		/// Définition du nom de la table en base de données
		$this->setName();
		
		/// Définition de nom de la table de metadonnées en base
		$this->setMeta( $meta );
	}
		
	/* = DÉFINITION DE DONNÉES = */
	/** == Définition du prefixe des colonnes == **/
	private function setColPrefix( $col_prefix = '' )
	{
		return $this->ColPrefix = $col_prefix;
	}
	
	/** == Traitement des arguments de colonne == **/
	private function setColAttrs( $col_name, $attrs = array() )
	{
		$defaults = array(
			'prefix'		=> true
		);
		$attrs = wp_parse_args( $attrs, $defaults );
		
		$_col_name = $attrs['prefix'] ? $this->ColPrefix . $col_name : $col_name;
		array_push( $this->ColNames, $_col_name );
		$this->ColMap[$col_name] = $_col_name;
		
		$col = "col_{$_col_name}";		
		return $this->{$col} = $attrs;
	}
	
	/** == Définition de la clé primaire == **/
	private function setPrimary()
	{			
		// Bypass	
		if( empty( $this->ColNames ) )
			return;

		reset( $this->ColNames );
		return $this->Primary = $this->ColNames[0];		
	}
		
	/** == Définition des clés d'index == **/
	private function setIndexKeys( $keys = array() )
	{
		$this->IndexKeys = $keys;
	} 
	
	/** == Définition des colonnes ouvertes à la recherche de termes == **/
	private function setSearchColNames( $search_columns  = array() )
	{
		foreach( (array) $search_columns as $col_name )
			if( isset( $this->ColMap[$col_name] ) )
				array_push( $this->SearchColNames, $this->ColMap[$col_name] );
	} 
	
	/** == Définition du nom de la table en base de données == **/
	private function setName()
	{
		global $wpdb;

		if( ! in_array( $this->ID, $wpdb->tables ) ) :	
			array_push( $wpdb->tables, $this->ID );				
			$wpdb->set_prefix( $wpdb->base_prefix );
		endif;
		
		return $this->Name = $wpdb->{$this->ID};
	}
	
	/** == Définition du nom de la table en base de données == **/
	private function setMeta( $meta_type = null )
	{
		if( ! $meta_type )
			return;
			
		global $wpdb;
		
		if( is_bool( $meta_type ) )
			$meta_type = $this->ID;
		
		$table = $meta_type .'meta';
		
		if( ! in_array( $table, $wpdb->tables ) ) :	
			array_push( $wpdb->tables, $table );				
			$wpdb->set_prefix( $wpdb->base_prefix );
		endif;
		
		return $this->MetaType = $meta_type;
	}
	
	/* = RECUPERATION DE DONNÉES = */
	/** == Récupération d'un attribut de colonne selon son nom == **/
	final public function getColAttrs( $col_name )
	{
		if( ! $col_name = $this->isCol( $col_name ) )
			return null;
		$col = "col_{$col_name}";
		if( isset( $this->{$col} ) );
			return $this->{$col}; 
	}	
	
	/** == Récupération d'un attribut de colonne selon son nom == **/
	final public function getColAttr( $col_name, $attr )
	{
		if( ( $column_attrs = $this->getColAttrs( $col_name ) ) && isset( $column_attrs[$attr] ) );
			return $column_attrs[$attr]; 
	}	
	
	/* = VERIFICATION DE DONNÉES = */
	/** == Vérification de l'existance d'une colonne == **/
	final public function isCol( $col_name )
	{
		if( $this->isPrivateQueryVar( $col_name ) )
			return false;
		elseif( in_array( $col_name, $this->ColNames ) )
			return $col_name;
		elseif( in_array( $this->ColPrefix . $col_name, $this->ColNames ) )
			return $this->ColPrefix . $col_name;
		
		return false;
	}
	
	/** == Vérifie si une variable de requête est une variable reservée au système == **/
	final public function isPrivateQueryVar( $var )
	{
		return in_array( $var, $this->PrivateQueryVars );
	}
	
	/** == Vérifie de l'existance d'une table de metadonnée en relation avec la table == **/
	final public function hasMeta()
	{
		return $this->MetaType ? true : false;
	}
		
	/** == Vérifie l'existance de colonnes ouvertes à la recherche == **/
	final public function hasSearch()
	{
		return ! empty( $this->SearchColNames );
	}	
	
	/* = FONCTIONS DE RAPPELS = */	
	/** == Traitement des éléments en base == **/
	public function handle()
	{
		return new Handle( $this );
	}
	
	/** == Gestion des éléments de la base de metadonnées == **/
	public function meta()
	{
		return new Meta( $this );
	}
	
	/** == Traitement des arguments de requête == **/
	public function parse()
	{
		return new Parse( $this );
	}	
	
	/** == == **/
	public function query( $query = '' )
	{
		return new Query( $this, $query );
	}
	
	/** == Récupération d'élément de la base de données == **/
	public function select()
	{
		return new Select( $this );
	}
}
