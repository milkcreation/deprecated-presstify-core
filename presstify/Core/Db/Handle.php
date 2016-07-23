<?php
namespace tiFy\Core\Db;

class Handle{
	/* = ARGUMENTS =*/
	protected	$Db;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Factory $Db )
	{
		$this->Db = $Db;
	}
	
	/** == Création d'un élément == **/
	final public function record( $data = array() )
	{
		$primary_key = $this->Db->Primary;	
			
		if( ! empty( $data[$primary_key] ) && $this->Db->select()->count( array( $primary_key => $data[$primary_key] ) ) )
			return $this->update( $data[$primary_key], $data );			
		else
			return $this->create( $data );
	}
	
	/** == Création d'un nouvel élément == **/
	final public function create( $data = array() )
	{
		global $wpdb;
		
		// Extraction des metadonnées	
		if( isset( $data['item_meta'] ) ) :
			$metas = $data['item_meta'];
			unset( $data['item_meta'] );
		else :
			$metas = false;
		endif;				 
		
		// Formatage des données	
		$data = $this->Db->parse()->validate( $data );			
		$data = array_map( 'maybe_serialize', $data );
		
		// Enregistrement de l'élément en base de données
		$wpdb->insert( $this->Db->Name, $data );		
		$id = $wpdb->insert_id;
		
		// Enregistrement des metadonnées de l'élément en base
		if( is_array( $metas ) && $this->Db->hasMeta() )
			foreach( (array) $metas as $meta_key => $meta_value )
				$this->Db->meta()->update( $id, $meta_key, $meta_value );			

		return $id;
	}	
	
	/** == Mise à jour d'un élément == **/
	final public function update( $id, $data = array() )
	{
		global $wpdb;
		
		// Extraction des metadonnées	
		if( isset( $data['item_meta'] ) ) :
			$metas = $data['item_meta'];
			unset( $data['item_meta'] );
		else :
			$metas = false;
		endif;				 
		
		// Formatage des données	
		$data = $this->Db->parse()->validate( $data );			
		$data = array_map( 'maybe_serialize', $data );
		
		$wpdb->update( $this->Db->Name, $data, array( $this->Db->Primary => $id ) );
		
		// Enregistrement des metadonnées de l'élément en base
		if( is_array( $metas ) && $this->Db->hasMeta() )
			foreach( (array) $metas as $meta_key => $meta_value )
				$this->Db->meta()->update( $id, $meta_key, $meta_value );
		
		return $id;
	}
		
	/** == Suppression d'un élément son id == **/
	public function delete_by_id( $id )
	{
		global $wpdb;
		
		return $wpdb->delete( $this->Db->Name, array( $this->Db->Primary => $id ), '%d' );
	}
	
	/** == Valeur de la prochaine clé primaire == **/
	public function next()
	{
		global $wpdb;
		
		if( $last_insert_id = $wpdb->query( "SELECT LAST_INSERT_ID() FROM {$this->wpdb_table}" ) )
			return ++$last_insert_id;
	}
}