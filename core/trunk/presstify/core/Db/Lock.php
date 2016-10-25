<?php
namespace tiFy\Core\Db;

class Lock
{
	/* = VERROUILLAGE = */
	/** == Récupération des types de verrou == **/
	private function get_lock_types(){
		if( $this->has_meta && ! empty( $this->locks ) )
			return $this->locks;		
	}
	
	/** == Vérifie si un type de verrou est actif == **/
	public function has_lock_type( $type = null ){
		if( $type )
			return ( $this->get_lock_types() && in_array( $type, $this->get_lock_types() ) );
		elseif( $this->get_lock_types() )
			return true;
	}
	
	/** == Récupération du délai de vérouillage == **/
	public function get_lock_time(){
		return (int) $this->lock_time;
	}
	
	/** == Vérification du verrouillage selon son type == **/
	public function check_lock( $item_id, $type = 'edit', $user_id = 0, $lock_user = 0 ){
		if( ! $this->has_lock_type( $type ) )
			return;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		if ( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		
		if ( ! $lock = $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true ) )
			return false;

		$lock = explode( ':', $lock );
		$time = $lock[0];
		$user = isset( $lock[1] ) ? $lock[1] : $lock_user;

		if ( $time && $time > time() - $this->lock_time && $user != $user_id )
			return $user;
		return false;		
	}
	
	/** == Vérification du verrouillage général d'un élément == **/
	public function check_locks( $item_id ){
		if( ! $this->get_lock_types() )
			return;
		if ( ! $item = $this->get_item_by_id( $item_id ) )
			return false;

		$callback = function($lock){ return "\"_". $lock ."_lock\""; };
		$locks = implode(',', array_map( $callback, $this->get_lock_types() ) );
		$query = "SELECT meta_id FROM {$this->wpdb_metatable} WHERE {$this->table}_id = %d AND meta_key IN ({$locks})";
		
		return $this->Db->sql()->query( $this->Db->sql()->prepare( $query, $item->{$this->primary_key} ) );	
	}
		
	/** == Définition du verrouillage d'un élément pour un utilisateur == **/
	public function set_lock( $item_id, $type = 'edit', $user_id = 0 ){
		if( ! $this->has_lock_type( $type ) )
			return false;
		if( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		
		$now = time();
		$lock = "{$now}:{$user_id}";

		$this->update_item_meta( $item->{$this->primary_key}, "_{$type}_lock", $lock );
	}
	
	/** == Récupération du verrouillage d'un élément selon son type == **/
	public function get_lock( $item_id, $type = 'edit' ){
		if( ! $this->has_lock_type( $type ) )
			return null;
		
		return $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true );
	}
	
	/** == Translation du verrouillage d'un élément pour un utilisateur == **/
	public function translate_lock( $item_id, $type = 'edit', $user_id = 0 ){		
		if( ! $this->has_lock_type( $type ) )
			return false;
		if( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		if ( ! $lock = $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true ) )
			return false;
		
		$active_lock = array_map( 'absint', explode( ':', $lock ) );
		if ( $active_lock[1] === $user_id )
			return false;
		
		$new_lock = time() . ':' . $user_id;
		$this->update_item_meta( $item->{$this->primary_key}, "_{$type}_lock", $new_lock, implode( ':', $active_lock ) );
		
		return true;		
	}
	
	/** == Translation du verrouillage d'un élément pour un utilisateur == **/
	public function delete_lock( $item_id, $type = 'edit', $user_id = 0 ){		
		if( ! $this->has_lock_type( $type ) )
			return false;
		if( ! $item = $this->get_item_by_id( $item_id ) )
			return false;
		if( ! $user_id && ( 0 == ( $user_id = get_current_user_id() ) ) )
			return false;
		if ( ! $lock = $this->get_item_meta( $item->{$this->primary_key}, "_{$type}_lock", true ) )
			return false;
		
		$active_lock = array_map( 'absint', explode( ':', $lock ) );
		if ( $active_lock[1] !== $user_id )
			return false;

		$new_lock = ( time() - $this->lock_time + 5 ) . ':' . $user_id;
		$this->update_item_meta( $item->{$this->primary_key}, "_{$type}_lock", $new_lock, implode( ':', $active_lock ) );
		
		return true;		
	}
}