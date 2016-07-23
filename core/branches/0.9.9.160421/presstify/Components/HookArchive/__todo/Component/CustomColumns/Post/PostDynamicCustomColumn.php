<?php
namespace tiFy\Components\HookForArchive\Component;

class PostDynamicCustomColumn
{
	/* = ARGUMENTS = */
	public 	$hook_post_type,
	$hook_args;

	/* = CONSTRUCTEUR = */
	public function __construct( $hook_post_type, $hook_args )
	{
		// Déclaration des arguments
		$this->hook_post_type = $hook_post_type;
		$this->hook_args = $hook_args;

		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'admin == **/
	function wp_admin_init()
	{
		add_filter( "manage_edit-{$this->hook_post_type}_columns", array( $this, 'columns' ) );
		add_action( "manage_{$this->hook_post_type}_posts_custom_column", array( $this, 'custom_column' ), 10, 2 );
	}

	/**
	 * Entête et position de la colonne
	 */
	function columns( $columns )
	{
		$newcolumns = array(); $n = 0;
		foreach( $columns as $key => $column ) :
		if( $n == 3 )
			$newcolumns['tify_hook_for_archive'] = __( 'Page d\'affichage', 'tify' );
			$newcolumns[$key] = $column;
			$n++;
			endforeach;
			$columns = $newcolumns;

			return $columns;
	}

	/**
	 * Affichage des données de la colonne
	 */
	function custom_column( $column, $post_id )
	{
		if( $column !== 'tify_hook_for_archive' )
			return $column;
			$post_type = $this->hook_post_type;
			$hook_post_type = $this->hook_args['hook_post_type'];

			if( $refs = get_post_meta( $post_id, '_'. $hook_post_type .'_for_'. $post_type ) )
				echo implode( ', ', array_map( 'get_the_title', $refs ) );
	}
}