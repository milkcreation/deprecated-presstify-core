<?php
namespace tiFy\Core\Db;

class Make
{
	/* = ARGUMENTS =*/
	protected	$Db;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Factory $Db )
	{
		$this->Db = $Db;

		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}
	
	/* = ACTIONS = */
	/** == Installation == **/
	final public function admin_init()
	{
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Bypass
		if( $current_version = get_option( 'tify_db_'. $name, 0 ) ) 
			return;
		
		global $wpdb;
		
		require_once( ABSPATH .'wp-admin/install-helper.php' );			
		
		// Création des tables		
		/// Encodage
		$charset_collate = $wpdb->get_charset_collate();
		
		/// Création de la table principale.
		$create_ddl = "CREATE TABLE {$name} ( ";
		$_create_ddl = array();
		foreach( (array) $this->Db->ColNames as $col_name )
			$_create_ddl[] = $this->create_dll( $col_name );		
		$create_ddl .= implode( ', ', $_create_ddl );
		$create_ddl .= ", PRIMARY KEY ( {$primary_key} )";
		$create_ddl .= $this->create_dll_keys();		
		$create_ddl .= " ) $charset_collate;";
		
		maybe_create_table( $name, $create_ddl );
		
		/// Création de la table des metadonnées
		if( $this->Db->MetaType ) :
			$table_name = $this->Db->meta()->table();
			$meta_id	= $this->Db->meta()->rel();
			
			$create_ddl  = "CREATE TABLE {$table_name} ( ";
			$create_ddl .= "meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, ";
  			$create_ddl .= "{$meta_id} bigint(20) unsigned NOT NULL DEFAULT '0', ";
  			$create_ddl .= "meta_key varchar(255) DEFAULT NULL, ";
			$create_ddl .= "meta_value longtext";
			$create_ddl .= ", PRIMARY KEY ( meta_id )";
			$create_ddl .= ", KEY {$meta_id} ( {$meta_id} )";
			$create_ddl .= ", KEY meta_key ( meta_key )";
			$create_ddl .= " ) $charset_collate;";

			maybe_create_table( $table_name, $create_ddl );			
		endif;
		
		update_option( 'tify_db_'. $name, $this->Db->Version );
	}
	
	/** == == **/
	private function create_dll( $col_name )
	{
		$primary_key = $this->Db->Primary;
		$types_allowed = array( 
			// Numériques
			'tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial',
			// Dates
			'date', 'datetime', 'timestamp', 'time', 'year',
			//Textes
			'char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'binary', 'varbinary', 'tinyblob', 'mediumblob', 'blob', 'longblob', 'enum', 'set'
			// 
		);
		$defaults = array(
			'type'				=> false,
			'size'				=> false,
			'unsigned'			=> false,			
			'auto_increment'	=> false,
			'default'			=> false
		);
		$attrs = $this->Db->getColAttrs( $col_name );	
		$attrs = wp_parse_args( $attrs, $defaults );
		extract( $attrs, EXTR_SKIP );
	
		// Formatage du type
		$type = strtolower( $type );
		if( ! in_array( $type, $types_allowed ) )
			return;
		
		$create_ddl  = "";
		$create_ddl .= "{$col_name} {$type}";
		
		if( $size )
			$create_ddl .= "({$size})";
			
		if( $unsigned || ( $col_name === $primary_key ) )	
			$create_ddl .= " UNSIGNED";	
		
		if( $auto_increment || ( $col_name === $primary_key ) )	
			$create_ddl .= " AUTO_INCREMENT";
		
		if( ! is_null( $default ) ) :
			if( is_numeric( $default ) )
				$create_ddl .= " DEFAULT ". $default ." NOT NULL";
			elseif( is_string( $default ) )
				$create_ddl .= " DEFAULT '". $default ."' NOT NULL";
			else		
				$create_ddl .=  " NOT NULL";
		else :
			$create_ddl .=  " DEFAULT NULL";
		endif;	
			
		return $create_ddl;
	}
	
	/** == Création des clefs d'index == **/
	private function create_dll_keys( )
	{
		$create_dll_keys = array();
		foreach( (array) $this->Db->IndexKeys as $key_name => $key_value ) :
			if( is_string( $key_value ) )
				$key_value = array( $key_value );
			$key_value = array_map( array( $this->Db, 'isCol' ), $key_value );
			
			$key_value = implode( ', ', $key_value );
			array_push( $create_dll_keys, "KEY {$key_name} ({$key_value})" );
		endforeach;
		
		if( ! empty( $create_dll_keys ) )
			return ", ". implode( ', ', $create_dll_keys );
	}
}
