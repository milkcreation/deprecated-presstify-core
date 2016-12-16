<?php
namespace tiFy\Core\Db;

class Meta
{
	/* = ARGUMENTS =*/
	protected	$Db;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Factory $Db )
	{
		$this->Db = $Db;
	}
	
	/* = CONTROLEUR = */
	/** == Nom de la table préfixé == **/
	public function getTableName()
	{
		return $this->_get_meta_table( $this->Db->MetaType );
	}
	
	/** == Nom de la colonne de clé primaire == **/
	public function getPrimary()
	{
		return 'user' == $this->Db->MetaType ? 'umeta_id' : 'meta_id';
	}
	
	/** == Nom de la colonne de colonne de jointure == **/
	public function getJoinCol()
	{
		return sanitize_key($this->Db->MetaType . '_id');
	}
	
	/** == Récupération de la valeur de la metadonnée d'un élément == 
 	 * @param int    $id  		 	ID de l'item
 	 * @param string $meta_key 		Optionel. Index de la métadonnée. Retournera, s'il n'est pas spécifié
 	 * 		                    	toutes les metadonnées relative à l'objet.
 	 * @param bool   $single    	Optionel, default is true.
 	 *                         		Si true, retourne uniquement la première valeur pour l'index meta_key spécifié.
	 *                          	Ce paramètres n'a pas d'impact lorsqu'aucun index meta_key n'est spécifié. 
	 **/
	final public function get( $object_id, $meta_key = '', $single = true )
	{
		if ( ! $this->Db->MetaType || ! is_numeric( $object_id ) ) {
			return false;
		}
	
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
	
		$check = apply_filters( "get_{$this->Db->MetaType}_metadata", null, $object_id, $meta_key, $single );
		if ( null !== $check ) {
			if ( $single && is_array( $check ) )
				return $check[0];
			else
				return $check;
		}
	
		$meta_cache = wp_cache_get($object_id, $this->Db->MetaType . '_meta');
	
		if ( !$meta_cache ) {
			$meta_cache = $this->update_meta_cache( array( $object_id ) );
			$meta_cache = $meta_cache[$object_id];
		}
	
		if ( ! $meta_key ) {
			return $meta_cache;
		}
	
		if ( isset($meta_cache[$meta_key]) ) {
			if ( $single )
				return maybe_unserialize( $meta_cache[$meta_key][0] );
			else
				return array_map('maybe_unserialize', $meta_cache[$meta_key]);
		}
	
		if ($single)
			return '';
		else
			return array();
	}
	
	/** == Récupération de toutes les metadonnés d'un élément == 
	 * @param int    $id  		 	ID de l'item
	 **/
	final public function all( $object_id )
	{
		return $this->get( $object_id );
	}
	
	/** == Récupération d'une metadonné selon sa meta_id == **/
	final public function get_by_mid( $meta_id )
	{	
		if ( ! $this->Db->MetaType || ! is_numeric( $meta_id ) ) {
			return false;
		}
	
		$meta_id = absint( $meta_id );
		if ( ! $meta_id ) {
			return false;
		}
	
		$table = $this->_get_meta_table( $this->Db->MetaType );
		if ( ! $table ) {
			return false;
		}
	
		$id_column = ( 'user' == $this->Db->MetaType ) ? 'umeta_id' : 'meta_id';
	
		$meta = $this->Db->sql()->get_row( $this->Db->sql()->prepare( "SELECT * FROM $table WHERE $id_column = %d", $meta_id ) );
	
		if ( empty( $meta ) )
			return false;
	
		if ( isset( $meta->meta_value ) )
			$meta->meta_value = maybe_unserialize( $meta->meta_value );
	
		return $meta;
	}
		
	/** == Ajout d'une metadonnée d'un élément == 
 	 * @param int    $id  		 	ID de l'item
 	 * @param string $meta_key   	Index de la métadonnée.
 	 * @param mixed  $meta_value 	Valeur de la métadonnée. Les données non scalaires seront serialisées.
 	 * @param bool   $unique     	Optionnel, true par défaut.
	 **/
	final function add( $object_id, $meta_key, $meta_value, $unique = true )
	{
		if ( ! $this->Db->MetaType || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}
	
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
	
		$table = $this->_get_meta_table( $this->Db->MetaType );
		if ( ! $table ) {
			return false;
		}
	
		$column = sanitize_key($this->Db->MetaType . '_id');
	
		// expected_slashed ($meta_key)
		$meta_key = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);
		$meta_value = sanitize_meta( $meta_key, $meta_value, $this->Db->MetaType );
	
		$check = apply_filters( "add_{$this->Db->MetaType}_metadata", null, $object_id, $meta_key, $meta_value, $unique );
		if ( null !== $check )
			return $check;
	
		if ( $unique && $this->Db->sql()->get_var( $this->Db->sql()->prepare(
			"SELECT COUNT(*) FROM $table WHERE meta_key = %s AND $column = %d",
			$meta_key, $object_id ) ) )
			return false;
	
		$_meta_value = $meta_value;
		$meta_value = maybe_serialize( $meta_value );
	
		do_action( "add_{$this->Db->MetaType}_meta", $object_id, $meta_key, $_meta_value );
	
		$result = $this->Db->sql()->insert( $table, array(
			$column => $object_id,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value
		) );
	
		if ( ! $result )
			return false;
	
		$mid = (int) $this->Db->sql()->insert_id;
	
		wp_cache_delete($object_id, $this->Db->MetaType . '_meta');
	
		do_action( "added_{$this->Db->MetaType}_meta", $mid, $object_id, $meta_key, $_meta_value );
	
		return $mid;
	}
	
	/** == Mise à jour de la metadonné d'un élément == **/
	final function update( $object_id, $meta_key, $meta_value, $prev_value = '' )
	{
		if ( ! $this->Db->MetaType || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}
	
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
	
		$table = $this->_get_meta_table( $this->Db->MetaType );
		if ( ! $table ) {
			return false;
		}
	
		$column = sanitize_key($this->Db->MetaType . '_id');
		$id_column = 'user' == $this->Db->MetaType ? 'umeta_id' : 'meta_id';
	
		// expected_slashed ($meta_key)
		$raw_meta_key = $meta_key;
		$meta_key = wp_unslash($meta_key);
		$passed_value = $meta_value;
		$meta_value = wp_unslash($meta_value);
		$meta_value = sanitize_meta( $meta_key, $meta_value, $this->Db->MetaType );
	
		$check = apply_filters( "update_{$this->Db->MetaType}_metadata", null, $object_id, $meta_key, $meta_value, $prev_value );
		if ( null !== $check )
			return (bool) $check;
	
		// Compare existing value to new value if no prev value given and the key exists only once.
		if ( empty($prev_value) ) {
			$old_value = $this->get( $object_id, $meta_key, false );
			if ( count($old_value) == 1 ) {
				if ( $old_value[0] === $meta_value )
					return false;
			}
		}
	
		$meta_ids = $this->Db->sql()->get_col( $this->Db->sql()->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id ) );
		if ( empty( $meta_ids ) ) {
			return $this->add( $object_id, $raw_meta_key, $passed_value );
		}
	
		$_meta_value = $meta_value;
		$meta_value = maybe_serialize( $meta_value );
	
		$data  = compact( 'meta_value' );
		$where = array( $column => $object_id, 'meta_key' => $meta_key );
	
		if ( !empty( $prev_value ) ) {
			$prev_value = maybe_serialize($prev_value);
			$where['meta_value'] = $prev_value;
		}
	
		foreach ( $meta_ids as $meta_id ) {
			do_action( "update_{$this->Db->MetaType}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
	
			if ( 'post' == $this->Db->MetaType ) {
				do_action( 'update_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
			}
		}
	
		$result = $this->Db->sql()->update( $table, $data, $where );
		if ( ! $result )
			return false;
	
		wp_cache_delete($object_id, $this->Db->MetaType . '_meta');
	
		foreach ( $meta_ids as $meta_id ) {
			do_action( "updated_{$this->Db->MetaType}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
	
			if ( 'post' == $this->Db->MetaType ) {
				do_action( 'updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value );
			}
		}
	
		return true;
	}
	
	/** == Récupération de la metadonné d'un élément == **/
	final function delete( $object_id, $meta_key, $meta_value = '', $delete_all = false )
	{
		if ( ! $this->Db->MetaType || ! $meta_key || ! is_numeric( $object_id ) && ! $delete_all ) {
			return false;
		}
	
		$object_id = absint( $object_id );
		if ( ! $object_id && ! $delete_all ) {
			return false;
		}
	
		$table = $this->_get_meta_table( $this->Db->MetaType );
		if ( ! $table ) {
			return false;
		}
	
		$type_column = sanitize_key($this->Db->MetaType . '_id');
		$id_column = 'user' == $this->Db->MetaType ? 'umeta_id' : 'meta_id';
		// expected_slashed ($meta_key)
		$meta_key = wp_unslash($meta_key);
		$meta_value = wp_unslash($meta_value);
	
		$check = apply_filters( "delete_{$this->Db->MetaType}_metadata", null, $object_id, $meta_key, $meta_value, $delete_all );
		if ( null !== $check )
			return (bool) $check;
	
		$_meta_value = $meta_value;
		$meta_value = maybe_serialize( $meta_value );
	
		$query = $this->Db->sql()->prepare( "SELECT $id_column FROM $table WHERE meta_key = %s", $meta_key );
	
		if ( !$delete_all )
			$query .= $this->Db->sql()->prepare(" AND $type_column = %d", $object_id );
	
		if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value )
			$query .= $this->Db->sql()->prepare(" AND meta_value = %s", $meta_value );
	
		$meta_ids = $this->Db->sql()->get_col( $query );
		if ( !count( $meta_ids ) )
			return false;
	
		if ( $delete_all ) {
			$value_clause = '';
			if ( '' !== $meta_value && null !== $meta_value && false !== $meta_value ) {
				$value_clause = $this->Db->sql()->prepare( " AND meta_value = %s", $meta_value );
			}
	
			$object_ids = $this->Db->sql()->get_col( $this->Db->sql()->prepare( "SELECT $type_column FROM $table WHERE meta_key = %s $value_clause", $meta_key ) );
		}
	
		do_action( "delete_{$this->Db->MetaType}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );
	
		// Old-style action.
		if ( 'post' == $this->Db->MetaType ) {
			do_action( 'delete_postmeta', $meta_ids );
		}
	
		$query = "DELETE FROM $table WHERE $id_column IN( " . implode( ',', $meta_ids ) . " )";
	
		$count = $this->Db->sql()->query($query);
	
		if ( !$count )
			return false;
	
		if ( $delete_all ) {
			foreach ( (array) $object_ids as $o_id ) {
				wp_cache_delete($o_id, $this->Db->MetaType . '_meta');
			}
		} else {
			wp_cache_delete($object_id, $this->Db->MetaType . '_meta');
		}
	
		do_action( "deleted_{$this->Db->MetaType}_meta", $meta_ids, $object_id, $meta_key, $_meta_value );
	
		// Old-style action.
		if ( 'post' == $this->Db->MetaType ) {
			do_action( 'deleted_postmeta', $meta_ids );
		}
	
		return true;
	}
	
	/** == Suppression de toutes les métadonnées d'un élément == **/
	final function delete_all( $id )
	{
		$table = $this->_get_meta_table( $this->Db->MetaType );
		if ( ! $table )
			return false;
		$column = sanitize_key( $this->Db->MetaType . '_id' );		
		
		$this->Db->sql()->delete( $table, array( $column => $id ), '%d' );
	}
	
	/* = HELPERS = */
	/** == == **/
	private function _get_meta_table($type) 
	{	
		$table_name = $type . 'meta';
	
		if ( empty( $this->Db->sql()->$table_name ) )
			return false;
	
		return $this->Db->sql()->$table_name;
	}
	
	/** == == **/
	private function update_meta_cache( $object_ids ) 
	{
		if ( ! $this->Db->MetaType || ! $object_ids ) {
			return false;
		}
	
		$table = $this->_get_meta_table( $this->Db->MetaType );
		if ( ! $table ) {
			return false;
		}
	
		$column = sanitize_key($this->Db->MetaType . '_id');
	
		if ( !is_array($object_ids) ) {
			$object_ids = preg_replace('|[^0-9,]|', '', $object_ids);
			$object_ids = explode(',', $object_ids);
		}
	
		$object_ids = array_map('intval', $object_ids);
	
		$cache_key = $this->Db->MetaType . '_meta';
		$ids = array();
		$cache = array();
		foreach ( $object_ids as $id ) {
			$cached_object = wp_cache_get( $id, $cache_key );
			if ( false === $cached_object )
				$ids[] = $id;
			else
				$cache[$id] = $cached_object;
		}
	
		if ( empty( $ids ) )
			return $cache;
	
		// Get meta info
		$id_list = join( ',', $ids );
		$id_column = 'user' == $this->Db->MetaType ? 'umeta_id' : 'meta_id';
		$meta_list = $this->Db->sql()->get_results( "SELECT $column, meta_key, meta_value FROM $table WHERE $column IN ($id_list) ORDER BY $id_column ASC", ARRAY_A );
	
		if ( !empty($meta_list) ) {
			foreach ( $meta_list as $metarow) {
				$mpid = intval($metarow[$column]);
				$mkey = $metarow['meta_key'];
				$mval = $metarow['meta_value'];
	
				// Force subkeys to be array type:
				if ( !isset($cache[$mpid]) || !is_array($cache[$mpid]) )
					$cache[$mpid] = array();
				if ( !isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey]) )
					$cache[$mpid][$mkey] = array();
	
				// Add a value to the current pid/key:
				$cache[$mpid][$mkey][] = $mval;
			}
		}
	
		foreach ( $ids as $id ) {
			if ( ! isset($cache[$id]) )
				$cache[$id] = array();
			wp_cache_add( $id, $cache[$id], $cache_key );
		}
	
		return $cache;
	}
}
