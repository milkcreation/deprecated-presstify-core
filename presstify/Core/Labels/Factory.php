<?php
namespace tiFy\Core\Labels;

class Factory
{
	/* = ARGUMENTS = */
	protected	$Labels		= array();

	/* = CONSTRUCTEURS = */
	public function __construct( $labels = array() )
	{
		$this->Labels = $this->Parse( $labels );
	}

	/* = CONTROLEURS = */
	/** == == **/
	private function Parse( $labels = array() )
	{
		// Traitement des arguments généraux
		$plural 	= __( 'éléments', 'tify' );
		$singular 	= __( 'élément', 'tify' );
		$gender 	= false;
		
		foreach( array( 'plural', 'singular', 'gender' ) as $attr ) :
			if ( isset( $labels[$attr] ) ) :
				${$attr} = $labels[$attr];
			endif;
		endforeach;
		
		$defaults = array(
			'name'               			=> ucfirst( $plural ),
			'singular_name'      			=> $singular,
			'menu_name'          			=> _x( ucfirst( $plural ), 'admin menu', 'tify' ),
			'name_admin_bar'     			=> _x( $singular, 'add new on admin bar', 'tify' ),
			'add_new'            			=> ! $gender ? __( sprintf( 'Ajouter un %s', $singular ), 'tify' ) : __( sprintf( 'Ajouter une %s', $singular ), 'tify' ),
			'add_new_item'       			=> ! $gender ? __( sprintf( 'Ajouter un nouvel %s', $singular ), 'tify' ) : __( sprintf( 'Ajouter une nouvelle %s', $singular ), 'tify' ),
			'new_item'           			=> ! $gender ? __( sprintf( 'Nouvel %s', $singular ), 'tify' ) : __( sprintf( 'Nouvelle %s', $singular ), 'tify' ),
			'edit_item'          			=> ! $gender ? __( sprintf( 'Éditer cet %s', $singular ), 'tify' ) : __( sprintf( 'Éditer cette %s', $singular ), 'tify' ),
			'view_item'          			=> ! $gender ? __( sprintf( 'Voir cet %s', $singular ), 'tify' ) : __( sprintf( 'Voir cette %s', $singular ), 'tify' ),
			'all_items'          			=> ! $gender ? __( sprintf( 'Tous les %s', $plural ), 'tify' ) : __( sprintf( 'Toutes les %s', $plural ), 'tify' ),
			'search_items'       			=> ! $gender ? __( sprintf( 'Rechercher un %s', $singular ), 'tify' ) : __( sprintf( 'Rechercher une %s', $singular ), 'tify' ),
			'parent_item_colon'  			=> ! $gender ? __( sprintf( '%s parent', ucfirst( $singular ) ), 'tify' ) : __( sprintf( '%s parente', ucfirst( $singular ) ), 'tify' ),
			'not_found'          			=> ! $gender ? __( sprintf( 'Aucun %s trouvé', $singular ), 'tify' ) : __( sprintf( 'Aucune %s trouvée', $singular ), 'tify' ),
			'not_found_in_trash' 			=> ! $gender ? __( sprintf( 'Aucun %s dans la corbeille', $singular ), 'tify' ) : __( sprintf( 'Aucune %s dans la corbeille', $singular ), 'tify' ),
			'update_item'					=> ! $gender ? __( sprintf( 'Mettre à jour cet %s', $singular ), 'tify' ) : __( sprintf( 'Mettre à jour cette %s', $singular ), 'tify' ),
			'new_item_name'					=> ! $gender ? __( sprintf( 'Nouvel %s', $singular ), 'tify' ) : __( sprintf( 'Nouvelle %s', $singular ), 'tify' ),
			'popular_items'					=> ! $gender ? __( sprintf( '%s populaires', ucfirst( $plural ) ), 'tify' ) : __( sprintf( '%s populaires', ucfirst( $plural ) ), 'tify' ),					
			'separate_items_with_commas'	=> ! $gender ? __( sprintf( 'Séparer les %s par une virgule', $plural ), 'tify' ) : __( sprintf( 'Séparer les %s par une virgule', $plural ), 'tify' ),	
			'add_or_remove_items'			=> ! $gender ? __( sprintf( 'Ajouter ou supprimer des %s', $plural ), 'tify' ) : __( sprintf( 'Ajouter ou supprimer des %s', $plural ), 'tify' ),	
			'choose_from_most_used'			=> ! $gender ? __( sprintf( 'Choisir parmi les %s les plus utilisés', $plural ), 'tify' ) : __( sprintf( 'Choisir parmi les %s les plus utilisées', $plural ), 'tify' ),
			'datas_item'					=> ! $gender ? __( sprintf( 'Données de cet %s', $singular ), 'tify' ) : __( sprintf( 'Données de cette %s', $singular ), 'tify' ),
			'import_items'					=>  __( sprintf( 'Importer des %s', $plural ), 'tify' ),
			'export_items'					=>  __( sprintf( 'Export des %s', $plural ), 'tify' )	
		);
		
		return wp_parse_args( $labels, $defaults );
	}
	
	/** == == **/
	public function Set( $label, $value = '' )
	{
		$this->Labels[$label] = $value;
	}
	
	/** == == **/
	public function Get( $label = '' )
	{
		if( ! $label )
			return $this->Labels;
		elseif( ! isset( $this->Labels[$label] ) )
			return $this->Labels['name'];
		else
			return $this->Labels[$label];
	}
}