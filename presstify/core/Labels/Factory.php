<?php
namespace tiFy\Core\Labels;

class Factory
{
	/* = ARGUMENTS = */
	protected	$Labels		= array();
	
	protected	$Plural;
	
	protected	$Singular;
	
	protected	$Gender		= false;

	/* = CONSTRUCTEURS = */
	public function __construct( $labels = array() )
	{
		// Arguments par défaut
		$this->Plural 		= __( 'éléments', 'tify' );
		$this->Singular 	= __( 'élément', 'tify' );
		
		// Traitement des intitulés
		$this->Labels = $this->Parse( $labels );
	}

	/* = CONTROLEURS = */
	/** == == **/
	private function Parse( $labels = array() )
	{
		// Définition des arguments
		foreach( array( 'plural', 'singular', 'gender' ) as $attr ) :
			if ( isset( $labels[$attr] ) ) :
				$var = ucfirst( $attr );
				$this->{$var} = $labels[$attr];
			endif;
		endforeach;
		
		// Traitement des arguments par défaut
		$defaults = array(
			'name'               			=> $this->ucFirst( $this->Plural ),
			'singular_name'      			=> $this->Singular,
			'menu_name'          			=> _x( $this->ucFirst( $this->Plural ), 'admin menu', 'tify' ),
			'name_admin_bar'     			=> _x( $this->Singular, 'add new on admin bar', 'tify' ),
			'add_new'            			=> ! $this->Gender ? __( sprintf( 'Ajouter un %s', $this->Singular ), 'tify' ) : __( sprintf( 'Ajouter une %s', $this->Singular ), 'tify' ),
			'add_new_item'       			=> ! $this->Gender ? __( sprintf( 'Ajouter un %s', $this->Singular ), 'tify' ) : __( sprintf( 'Ajouter une %s', $this->Singular ), 'tify' ),
			'new_item'           			=> ! $this->Gender ? __( sprintf( 'Créer un %s', $this->Singular ), 'tify' ) : __( sprintf( 'Créer une %s', $this->Singular ), 'tify' ),
			'edit_item'          			=> $this->default_edit_item(),
			'view_item'          			=> ! $this->Gender ? __( sprintf( 'Voir ce %s', $this->Singular ), 'tify' ) : __( sprintf( 'Voir cette %s', $this->Singular ), 'tify' ),
			'all_items'          			=> ! $this->Gender ? __( sprintf( 'Tous les %s', $this->Plural ), 'tify' ) : __( sprintf( 'Toutes les %s', $this->Plural ), 'tify' ),
			'search_items'       			=> ! $this->Gender ? __( sprintf( 'Rechercher un %s', $this->Singular ), 'tify' ) : __( sprintf( 'Rechercher une %s', $this->Singular ), 'tify' ),
			'parent_item_colon'  			=> ! $this->Gender ? __( sprintf( '%s parent', ucfirst( $this->Singular ) ), 'tify' ) : __( sprintf( '%s parente', ucfirst( $this->Singular ) ), 'tify' ),
			'not_found'          			=> ! $this->Gender ? __( sprintf( 'Aucun %s trouvé', $this->Singular ), 'tify' ) : __( sprintf( 'Aucune %s trouvée', $this->Singular ), 'tify' ),
			'not_found_in_trash' 			=> ! $this->Gender ? __( sprintf( 'Aucun %s dans la corbeille', $this->Singular ), 'tify' ) : __( sprintf( 'Aucune %s dans la corbeille', $this->Singular ), 'tify' ),
			'update_item'					=> ! $this->Gender ? __( sprintf( 'Mettre à jour ce %s', $this->Singular ), 'tify' ) : __( sprintf( 'Mettre à jour cette %s', $this->Singular ), 'tify' ),
			'new_item_name'					=> ! $this->Gender ? __( sprintf( 'Créer un %s', $this->Singular ), 'tify' ) : __( sprintf( 'Créer une %s', $this->Singular ), 'tify' ),
			'popular_items'					=> ! $this->Gender ? __( sprintf( '%s populaires', ucfirst( $this->Plural ) ), 'tify' ) : __( sprintf( '%s populaires', ucfirst( $this->Plural ) ), 'tify' ),					
			'separate_items_with_commas'	=> ! $this->Gender ? __( sprintf( 'Séparer les %s par une virgule', $this->Plural ), 'tify' ) : __( sprintf( 'Séparer les %s par une virgule', $this->Plural ), 'tify' ),	
			'add_or_remove_items'			=> ! $this->Gender ? __( sprintf( 'Ajouter ou supprimer des %s', $this->Plural ), 'tify' ) : __( sprintf( 'Ajouter ou supprimer des %s', $this->Plural ), 'tify' ),	
			'choose_from_most_used'			=> ! $this->Gender ? __( sprintf( 'Choisir parmi les %s les plus utilisés', $this->Plural ), 'tify' ) : __( sprintf( 'Choisir parmi les %s les plus utilisées', $this->Plural ), 'tify' ),
			'datas_item'					=> $this->default_datas_item(),
			'import_items'					=>  __( sprintf( 'Importer des %s', $this->Plural ), 'tify' ),
			'export_items'					=>  __( sprintf( 'Export des %s', $this->Plural ), 'tify' )	
		);
		
		return wp_parse_args( $labels, $defaults );
	}
	
	/* = PARAMETRAGE = */
	/** == == **/
	public function default_edit_item()
	{
		return sprintf( __( 'Éditer %s %s', 'tify' ), $this->getDeterminant( $this->Singular, $this->Gender ), $this->Singular );
	}
	
	/** == == **/
	public function default_datas_item()
	{
		if( $this->isFirstVowel( $this->Singular ) ) :
			$determinant = __( 'de l\'', 'tify' );
		elseif( $this->Gender ) :
			$determinant = __( 'de la', 'tify' );
		else :
			$determinant = __( 'du', 'tify' );
		endif;
		
		return sprintf( __( 'Données %s %s', 'tify' ), $determinant, $this->Singular );
	}
	
	/* = CONTRÔLEUR = */
	/** == == **/
	public function Set( $label, $value = '' )
	{
		$this->Labels[$label] = $value;
	}
	
	/** == == **/
	public function Get( $label = null, $default = '' )
	{
		if( ! $label ) :
			return $this->Labels;
		elseif( isset( $this->Labels[$label] ) ) :
			
			return $this->Labels[$label];
		endif;
		
		return $default;
	}
	
	/** == Mettre en majuscule la première lettre même s'il elle contient un accent == **/
	private function ucFirst( $s )
	{
		return mb_strtoupper( mb_substr( $s, 0, 1 ) ) . mb_substr( $s, 1 );
	}
	
	/** == Vérifie si la première lettre est une voyelle == **/
	private function isFirstVowel( $s )
	{
		$first = strtolower( mb_substr( remove_accents( $s ), 0, 1 ) );
		
		return( in_array( $first, array( 'a', 'e', 'i', 'o', 'u', 'y' ) ) ); 
	}
	
	/** == Récupération du déterminant == **/ 
	private function getDeterminant( $string, $gender )
	{
		if( $this->isFirstVowel( $string ) ) :
			return __( "l'", 'tify' );
		else :
			return $gender ? __( "la", 'tify' ) : __( "le", 'tify' );
		endif;
	}
}