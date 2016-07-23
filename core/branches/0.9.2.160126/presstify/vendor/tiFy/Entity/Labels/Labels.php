<?php
namespace tiFy\Entity\Labels;

class Labels
{
	/* = ARGUMENTS = */
	protected	$labels		= array();

	/* = CONSTRUCTEURS = */
	public function __construct( $labels = array() )
	{
		$this->labels = wp_parse_args( $labels, $this->defaults() );
	}

	/* = CONTROLEURS = */
	/** == == **/
	private function defaults()
	{
		return array(
			'name'					=>	__( 'Éléments', 'tify' ),
		    'singular_name'			=>	__( 'Élément', 'tify' ),
		    'menu_name'				=>	__( 'Éléments', 'tify' ),
		    'name_admin_bar'		=>	__( 'Élément', 'tify' ),
		    'all_items'				=>	__( 'Tous les éléments', 'tify' ),
		    'add_new'				=>	__( 'Ajouter un élément', 'tify' ),
		    'edit_item'				=>	__( 'Editer l\'élément', 'tify' ),
		    'new_item'				=>	__( 'Nouvel élément', 'tify' ),
		    'view_item'				=>	__( 'Voir l\'élément', 'tify' ),
		    'search_items'			=>	__( 'Rechercher un élément', 'tify' ),
		    'not_found'				=>	__( 'Aucun élément trouvée', 'tify' ),
		    'not_found_in_trash'	=>	__( 'Aucun élément dans la corbeille', 'tify' )
		);
	}

	/** == == **/
	public function get( $label = '' )
	{
		if( ! $label )
			return $this->labels;
		elseif( ! isset( $this->labels[$label] ) )
			return $this->labels['name'];
		else
			return $this->labels[$label];
	}
}