<?php
namespace tiFy\Entity\Db;

class Meta{
	/* = ARGUMENTS =*/
	protected	$Db;
	
	public	// PARAMETRES
			/// Nom de la table en base de données
			$name	= null,
			/// Nom de la colonne de relation
			$rel	= null;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Db $Db )
	{
		$this->Db = $Db;
	}
	
	/* = CONTROLEUR = */
	/** == Nom de la table en base de données == **/
	final public function table(){
		if( $this->name )
			return $this->name;
		return $this->name = _get_meta_table( $this->Db->MetaType );
	}
	
	/** == Nom de la colonne de relation == **/
	final public function rel(){
		if( $this->rel )
			return $this->rel;
		return $this->rel = $this->Db->MetaType .'_id';
	}
	
	/** == Récupération de la valeur de la metadonnée d'un élément == 
 	 * @param int    $id  		 	ID de l'item
 	 * @param string $meta_key 		Optionel. Index de la métadonnée. Retournera, s'il n'est pas spécifié
 	 * 		                    	toutes les metadonnées relative à l'objet.
 	 * @param bool   $single    	Optionel, default is true.
 	 *                         		Si true, retourne uniquement la première valeur pour l'index meta_key spécifié.
	 *                          	Ce paramètres n'a pas d'impact lorsqu'aucun index meta_key n'est spécifié. 
	 **/
	final public function get( $id, $meta_key = '', $single = true ){
		return get_metadata( $this->Db->MetaType, $id, $meta_key, $single );
	}
	
	/** == Récupération d'une metadonné selon sa meta_id == **/
	final public function get_by_mid( $meta_id ){
		return get_metadata_by_mid( $this->Db->MetaType, $meta_id );
	}
	
	/** == Récupération de toutes les metadonnés d'un élément == 
	 * @param int    $id  		 	ID de l'item
	 **/
	final function get_all( $id ){
		return $this->get_item_meta( $id );
	}
	
	/** == Ajout d'une metadonnée d'un élément == 
 	 * @param int    $id  		 	ID de l'item
 	 * @param string $meta_key   	Index de la métadonnée.
 	 * @param mixed  $meta_value 	Valeur de la métadonnée. Les données non scalaires seront serialisées.
 	 * @param bool   $unique     	Optionnel, true par défaut.
	 **/
	final function add( $id, $meta_key, $meta_value, $unique = true ){
		return add_metadata( $this->Db->MetaType, $id, $meta_key, $meta_value, $unique );
	}
	
	/** == Mise à jour de la metadonné d'un élément == **/
	final function update( $id, $meta_key, $meta_value, $prev_value = '' ){
		return update_metadata( $this->Db->MetaType, $id, $meta_key, $meta_value, $prev_value );
	}
	
	/** == Récupération de la metadonné d'un élément == **/
	final function delete( $id, $key, $value = '' ){
		return delete_metadata( $this->Db->MetaType, $id, $key, $value );
	}
	
	/** == Suppression de toutes les métadonnées d'un élément == **/
	final function delete_all( $id ){
		global $wpdb;
		
		return $wpdb->delete( $this->name, array( $this->rel => $id ), '%d' );
	}
}
