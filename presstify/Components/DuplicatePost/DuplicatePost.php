<?php
namespace tiFy\Components\DuplicatePost;

use \tiFy\Environment\Component;

final class DuplicatePost extends Component
{
	/* = ARGUMENTS = */
	// FILTRES
	/// Liste des actions à déclencher
	protected $CallFilters				= array(
		'page_row_actions',
		'post_row_actions'
	);
	
	// Fonctions de rappel des filtres
	protected $CallFiltersFunctionsMap	= array(
		'page_row_actions'	=> 'row_actions',
		'post_row_actions'	=> 'row_actions'	
	);
	
	// Ordres de priorité d'exécution des filtres
	protected $CallFiltersPriorityMap	= array(
		'page_row_actions' => 99,	
		'post_row_actions' => 99
	);
	
	// Nombre d'arguments autorisés
	protected $CallFiltersArgsMap		= array(
		'page_row_actions' => 2,	
		'post_row_actions' => 2 // $actions, $post
	);
	
	// Configuration
	/// Type de post
	private $PostType	=	array();
	
	/// Configuration
	private $Attrs		= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		if( $post_types = self::getConfig( 'post_type' ) ) :
		
			$this->PostType = array_keys( $post_types );
		
			foreach( $post_types as $pt => $attrs )
				$Attrs[$pt] = wp_parse_args(
					$attrs,
					array(
						'multisite'	=> false	
					)
				);
		endif;
	}
	
	/* = DECLENCHEURS = */
	public function row_actions( $actions, $post )
	{
		if( ! in_array( $post->post_type, $this->PostType ) )
			return $actions;
		
		$actions['tiFyDuplicate'] = "<a href=\"". wp_nonce_url( self::Url( $post->ID ), 'tify_duplicate:'. $post->ID ) ."\" title=\"". __( 'Dupliquer le contenu', 'tify' ) ."\">". __( 'Dupliquer', 'tify' ) ."</a>";
		
		return $actions;
	}
}