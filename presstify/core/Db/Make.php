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

		add_action( 'init', array( $this, 'admin_init' ), 99 );
	}
	
	/* = DECLENCHEURS = */
	/** == Installation == **/
	final public function admin_init()
	{
		
		$name 			= $this->Db->Name;
		$primary_key 	= $this->Db->Primary;
		
		// Bypass
		if( $current_version = get_option( 'tify_db_'. $name, 0 ) ) 
			return;
		
		// Création des tables		
		/// Encodage
		$charset_collate = $this->Db->sql()->get_charset_collate();
		
		/// Création de la table principale.
		$create_ddl = "CREATE TABLE {$name} ( ";
		$_create_ddl = array();
		foreach( (array) $this->Db->ColNames as $col_name )
			$_create_ddl[] = $this->create_dll( $col_name );		
		$create_ddl .= implode( ', ', $_create_ddl );
		$create_ddl .= ", PRIMARY KEY ( {$primary_key} )";
		$create_ddl .= $this->create_dll_keys();		
		$create_ddl .= " ) $charset_collate;";
		
		$this->maybe_create_table( $name, $create_ddl );
		
		/// Création de la table des metadonnées
		if( $this->Db->MetaType ) :
			$table_name = $this->Db->meta()->getTableName();
			$join_col	= $this->Db->meta()->getJoinCol();

			$create_ddl  = "CREATE TABLE {$table_name} ( ";
			$create_ddl .= "meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT, ";
  			$create_ddl .= "{$join_col} bigint(20) unsigned NOT NULL DEFAULT '0', ";
  			$create_ddl .= "meta_key varchar(255) DEFAULT NULL, ";
			$create_ddl .= "meta_value longtext";
			$create_ddl .= ", PRIMARY KEY ( meta_id )";
			$create_ddl .= ", KEY {$join_col} ( {$join_col} )";
			$create_ddl .= ", KEY meta_key ( meta_key )";
			$create_ddl .= " ) $charset_collate;";

			$this->maybe_create_table( $table_name, $create_ddl );			
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
	
	/* = HELPERS = */
	/**
	 * Create database table, if it doesn't already exist.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name Database table name.
	 * @param string $create_ddl Create database table SQL.
	 * @return bool False on error, true if already exists or success.
	 */
	function maybe_create_table($table_name, $create_ddl) 
	{
		foreach ($this->Db->sql()->get_col("SHOW TABLES",0) as $table ) {
			if ($table == $table_name) {
				return true;
			}
		}
		// Didn't find it, so try to create it.
		$this->Db->sql()->query($create_ddl);
	
		// We cannot directly tell that whether this succeeded!
		foreach ($this->Db->sql()->get_col("SHOW TABLES",0) as $table ) {
			if ($table == $table_name) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Add column to database table, if column doesn't already exist in table.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name Database table name
	 * @param string $column_name Table column name
	 * @param string $create_ddl SQL to add column to table.
	 * @return bool False on failure. True, if already exists or was successful.
	 */
	function maybe_add_column($table_name, $column_name, $create_ddl) 
	{
		foreach ($this->Db->sql()->get_col("DESC $table_name",0) as $column ) {
	
			if ($column == $column_name) {
				return true;
			}
		}
	
		// Didn't find it, so try to create it.
		$this->Db->sql()->query($create_ddl);
	
		// We cannot directly tell that whether this succeeded!
		foreach ($this->Db->sql()->get_col("DESC $table_name",0) as $column ) {
			if ($column == $column_name) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Drop column from database table, if it exists.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name Table name
	 * @param string $column_name Column name
	 * @param string $drop_ddl SQL statement to drop column.
	 * @return bool False on failure, true on success or doesn't exist.
	 */
	function maybe_drop_column($table_name, $column_name, $drop_ddl) 
	{
		foreach ($this->Db->sql()->get_col("DESC $table_name",0) as $column ) {
			if ($column == $column_name) {
	
				// Found it, so try to drop it.
				$this->Db->sql()->query($drop_ddl);
	
				// We cannot directly tell that whether this succeeded!
				foreach ($this->Db->sql()->get_col("DESC $table_name",0) as $column ) {
					if ($column == $column_name) {
						return false;
					}
				}
			}
		}
		// Else didn't find it.
		return true;
	}
	
	/**
	 * Check column matches criteria.
	 *
	 * Uses the SQL DESC for retrieving the table info for the column. It will help
	 * understand the parameters, if you do more research on what column information
	 * is returned by the SQL statement. Pass in null to skip checking that
	 * criteria.
	 *
	 * Column names returned from DESC table are case sensitive and are listed:
	 *      Field
	 *      Type
	 *      Null
	 *      Key
	 *      Default
	 *      Extra
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $table_name Table name
	 * @param string $col_name   Column name
	 * @param string $col_type   Column type
	 * @param bool   $is_null    Optional. Check is null.
	 * @param mixed  $key        Optional. Key info.
	 * @param mixed  $default    Optional. Default value.
	 * @param mixed  $extra      Optional. Extra value.
	 * @return bool True, if matches. False, if not matching.
	 */
	function check_column($table_name, $col_name, $col_type, $is_null = null, $key = null, $default = null, $extra = null) 
	{
		$diffs = 0;
		$results = $this->Db->sql()->get_results("DESC $table_name");
	
		foreach ($results as $row ) {
	
			if ($row->Field == $col_name) {
	
				// Got our column, check the params.
				if (($col_type != null) && ($row->Type != $col_type)) {
					++$diffs;
				}
				if (($is_null != null) && ($row->Null != $is_null)) {
					++$diffs;
				}
				if (($key != null) && ($row->Key  != $key)) {
					++$diffs;
				}
				if (($default != null) && ($row->Default != $default)) {
					++$diffs;
				}
				if (($extra != null) && ($row->Extra != $extra)) {
					++$diffs;
				}
				if ($diffs > 0) {
					return false;
				}
				return true;
			} // end if found our column
		}
		return false;
	}
}
