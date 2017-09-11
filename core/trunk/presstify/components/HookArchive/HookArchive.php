<?php
namespace tiFy\Components\HookArchive;	

use tiFy\Environment\Component;

final class HookArchive extends Component
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des actions à déclencher
	protected $tFyAppActions			= array(
		'init',
		'current_screen',
		'admin_bar_menu',
		'tify_options_register_node'
	);
	
	// Ordres de priorité d'exécution des actions
	protected $tFyAppActionsPriority	= array(
		'admin_bar_menu'		=> 99,
	);

	
	/** == FILTRES == **/
	// Liste des Filtres à déclencher
	protected $CallFilters				= array(
		'wp_title_parts',
		'post_type_archive_title',
		'single_term_title'
	);	
	// Nombre d'arguments autorisés
	protected $CallFiltersPriorityMap	= array(
			
	);
	// Nombre d'arguments autorisés
	protected $CallFiltersArgsMap		= array(
		'post_type_archive_title'	=> 2
	);
			
	// Liste des Hooks déclarés
	public static	$Hooks 				= array();
	
	// Liste des types d'objets autorisés
	private static $AllowedObject		= array( 'post_type', 'taxonomy' );
	
	// Interface d'administration des contenu d'accroche active
	private $Admin 						= false;
	
	// Activation du mode déboguage
	private $Debug 						= false;
	
	/* = CONSTRUCTEUR = */
	public function __construct( )
	{
		parent::__construct();

		foreach( (array) self::tFyAppConfig() as $obj => $hooks ) :
			foreach( $hooks as $archive => $args ) :
				$args['obj'] 			= $obj;
				$args['archive']		= $archive;
				self::Register( $args );
			endforeach;
		endforeach;

		do_action( 'tify_hookarchive_register' );
		
		foreach( self::$AllowedObject as $obj ) :
			if( isset( self::$Hooks[$obj] ) ) :
				foreach( (array) self::$Hooks[$obj] as $archive => $Class ) :
					if( $Class->hasAdmin() ) :
						$this->Admin = true;
						break 2;
					endif;
				endforeach;
			endif;
		endforeach;
	}
			
	/* = CONFIGURATION = */
	/** == Déclaration == **/
	final public static function register( $args = array() )
	{
	    $defaults = array(
			// (requis) Type d'objet d'accroche : post_type (par défaut) | taxonomy
			'obj'							=> 'post_type',
			// (requis) Identifiant de l'archive à accrocher (post_type_name | taxonomy_name )
			'archive'						=> '',
			// (optionel) Paramètres de configuration globaux hérités par les contenus d'accroche
			'options'	=> array(
				/// Permet de rendre les tous les contenus d'accroche administrable
				'edit'						=> true,
				/// Type de post global des contenus d'accroche
				'post_type'					=> 'page',
				/// Réécriture global des permaliens des contenus de l'archive
				'permalink'					=> false,
				// 
				/// Autoriser les doublons
				'duplicate'					=> false,
				/// Autoriser la réécriture des permaliens
				'rewrite'					=> false				
			),
			/// (optionel) Définition des contenus d'accroche 
			'hooks'						=>	array(
				array(
                    /*
                    // Identifiant du post (post_id) du contenu d'accroche (~tify_hook_id)
                    'id'          => 0,
                    
                    // Type de post du contenu d'accroche 
                    'post_type'   => 'page',     
                               
                    // Réécriture du permalien du contenu d'accroche bool | array
                    // Pour l'object_type taxonomie : [ 'page', 'post', 'product' ]
                    'permalink'   => false,
                    
                    // Modifiable
                    'edit'        => true,
                    
                    // Identifiant du terme (term_id) - Uniquement si l'objet type est une taxonomie 
                    'term'          => 0
                    */
				)	
			)			
		);
		$args = wp_parse_args( $args, $defaults );
		
		// Traitement des arguments
		if( empty( $args['archive'] ) )
			return;
				
		// Instanciation de l'objet	
		switch( $args['obj'] ) :
			case 'post_type' :
				self::$Hooks[$args['obj']][$args['archive']] 	= new PostType( $args );
				break;
			case 'taxonomy' :
				self::$Hooks[$args['obj']][$args['archive']]	= new Taxonomy( $args );
				break;
		endswitch;
	}
	
	/* = CONTROLEURS = */
	/** == Deboguage == **/
	private function Debug()
	{
		global $wp_rewrite;
		//flush_rewrite_rules();
		//var_dump( $wp_rewrite );
		var_dump( get_option( 'rewrite_rules') );
		exit;
	}
	
	/* = ACTIONS ET FILTRES = */
	/** == Initialisation de Wordpress == **/
	final public function init()
	{
		// Variable de requête personnalisée
		add_rewrite_tag( '%tify_hook_id%', '([0-9]{1,})' );
		
		// Déclenchement du mode déboguage
		if( $this->Debug )
			return $this->Debug();
	}
	
	/** == Initialisation de l'interface d'administration == **/
	final public function current_screen( $current_screen )
	{	    
	    if( get_current_screen()->id !== 'settings_page_tify_options' )
			return;
		//if( $this->Admin && isset( $_GET['settings-updated'] ) ) :
		flush_rewrite_rules();
		//endif;
	}
			
	/** == Barre d'administration == */
	final public function admin_bar_menu( $wp_admin_bar )
	{
		// Bypass
		if( is_admin() )
			return;
		if( ! is_post_type_archive() && ! is_tax() )
			return;
		if( ! $hook_id = get_query_var( 'tify_hook_id' ) )
			return;
		
		if( is_post_type_archive() ) :
			$post_type_object = get_post_type_object( get_post_type( $hook_id ) );
		
			// Ajout d'un lien de configuration du Diaporama
			$wp_admin_bar->add_node(
				array(
					'id' 	=> 'edit',
					'title' => $post_type_object->labels->edit_item,
					'href' 	=> get_edit_post_link( $hook_id )
				)
			);
		elseif( is_tax() ) :
			$post_type_object = get_post_type_object( get_post_type( $hook_id ) );
		
			// Ajout d'un lien de configuration du Diaporama
			$wp_admin_bar->add_node(
				array(
					'id' 	=> 'edit',
					'title' => $post_type_object->labels->edit_item,
					'href' 	=> get_edit_post_link( $hook_id )
				)
			);
		endif;
	}
	
	/** == Modification des éléments constituants la balise title == **/
	final public function wp_title_parts( $wp_title_parts )
	{
		if( ( is_post_type_archive() || is_tax() ) && ( $hook_id = get_query_var( 'tify_hook_id' ) ) ) :
			$wp_title_parts[0] = get_the_title( $hook_id );
			if( is_tax() )
				unset( $wp_title_parts[1] );
		endif;

		return $wp_title_parts;
	}
	
	/** == Titre de la page des archives de post == **/
	final public function post_type_archive_title( $archive_title, $post_type )
	{
		if( $hook_id = get_query_var( 'tify_hook_id' ) ) :
			$archive_title =  get_the_title( $hook_id );
		endif;
	
		return $archive_title;
	}
	
	/** == Titre de la page des archives de term de taxonomy == **/
	final public function single_term_title( $term_title )
	{
		if( $hook_id = get_query_var( 'tify_hook_id' ) ) :
			$term_title =  get_the_title( $hook_id );
		endif;
	
		return $term_title;
	}
	
	/** == Initialisation de l'interface d'administration == **/
	final public function tify_options_register_node()
	{
		// Bypass
		if( ! $this->Admin )
			return;

		tify_options_register_node(
			array(
				'id' 		=> 'tify_hookarchive',
				'title' 	=> __( 'Affichage des archives', 'tify' )
			)
		);		
	}
	
	/* = @TODO = */
	/** == Modification du menu de navigation == **/
	final public function wp_nav_menu_objects( $sorted_menu_items, $args )
	{
		if( ! $hook_id = get_query_var( 'tify_hook_id' ) )
			return $sorted_menu_items;
	
		foreach( $sorted_menu_items as &$item ) :
			if( $item->object_id == $hook_id ) :
				$item->classes[] = 'current-menu-item';
				$item->classes[] = 'current_page_item';
			elseif( ! empty( $this->hooks[get_post_type( $hook_id )]['hook_id'] ) && ( $item->object_id == $this->hooks[get_post_type( $hook_id )]['hook_id'] ) ) :
				$item->classes[] = 'current-menu-ancestor';
				$item->classes[] = 'current-menu-parent';
				$item->classes[] = 'current_page_parent';
				$item->classes[] = 'current_page_ancestor';
			endif;
		endforeach;
	
		return $sorted_menu_items;
	}
	
	/** == == **/
	public static function getRegistered( $object_type = null, $type = null )
	{
		if( ! empty( $object_type ) ) :
			if( ! isset( self::$Hooks[$object_type] ) ) :
				return false;
			elseif(  ! empty( $type ) ) :
				if( ! isset( self::$Hooks[$object_type][$type] ) ) :
					return false;
				else :
					return self::$Hooks[$object_type][$type];
				endif;
			else :
				return self::$Hooks[$object_type];
			endif;		
		else:
			return self::$Hooks;
		endif;
				
		return false;
	}

	/** == == **/
	public static function getHooks( $object_type, $type )
	{
		if( ! $registered = self::getRegistered( $object_type, $type ) )
			return false;
			
		return $registered->GetHooks();	
	}
	
	// Récupére le contenu d'accroche d'un post
	public static function GetPostHook( $post = null, $permalink = true )
	{
		// Bypass
		if( ! self::$Hooks )
			return;
		if( ! $post && ! is_singular() )
			return;
		if( ! $post = get_post( $post ) )
			return;
		$post_id	= $post->ID;
		$post_type  = $post->post_type;	
			
		$hooks = array();
		foreach( self::$AllowedObject as $obj ) :
			switch( $obj ) :
				case 'post_type' :
					if( $_hooks = self::GetPostTypeHooks( $post_type, $permalink, 'post_type' ) ) :
						$hooks += $_hooks;
					endif;
					break;
				case 'taxonomy' :
					foreach( self::$Hooks['taxonomy'] as $archive => $Class ) :	
						if( $permalink && ! $Class->GetOption( 'rewrite' ) )
							continue;
						if( ! is_object_in_taxonomy( $post_type, $archive ) )
							continue;
						$terms = wp_get_post_terms( $post_id, $archive, array( 'fields' => 'ids' ) );
						if( is_wp_error( $terms ) )
							continue;
						$_hooks = $Class->GetHooks();
						foreach( $_hooks as $hook ) :
							if( $permalink && ! $hook['permalink'] )
								continue;
							if( ! $hook['id'] )
								continue;							
							if( is_array( $hook['permalink'] ) && ! in_array( $post_type, $hook['permalink'] ) )	
								continue;
							if( ! in_array( $hook['term'], $terms ) )
								continue;
							if( $hook_obj = get_post( $hook['id'] ) )
								array_push( $hooks, $hook_obj );
						endforeach;
					endforeach;
					break;
			endswitch;			
		endforeach;

		if( $hooks && $permalink )
			return current( $hooks );
		
		return $hooks;
	}
	
	// Récupére les contenus d'accroches d'un type
	public static function GetPostTypeHooks( $post_type, $permalink = true, $object = null )
	{
		// Bypass
		if( ! self::$Hooks )
			return;
	
		if( ! $object )
			$object = self::$AllowedObject;
		if( is_string( $object ) )
			$object = array_map( 'trim', explode( ',', $object ) );
		
		$hooks = array();
		foreach( $object as $obj ) :
			if( ! isset( self::$Hooks[$obj] ) )
				continue;
			foreach( (array) self::$Hooks[$obj] as $archive => $Class ) :	
				if( $permalink && ! $Class->GetOption( 'rewrite' ) )
					continue;
				elseif( ! $permalink && $Class->GetOption( 'rewrite' ) )
					continue;
				
				switch( $obj ) :
					case 'post_type' :						
							if( $archive !== $post_type )
								continue 2;
							
							$_hook = current( $Class->GetHooks() );

							if( $permalink && ! $_hook['permalink'] )
								continue 2;
							if( ! $_hook['id'] )
								continue 2;
							if( $hook_obj = get_post( $_hook['id'] ) )
								array_push( $hooks, $hook_obj );
						break;
					case 'taxonomy' :	
							if( ! is_object_in_taxonomy( $post_type, $archive ) )
								continue 2;
								
							$_hooks = $Class->GetHooks();
							foreach( $_hooks as $_hook ) :								
								if( $permalink && ! $_hook['permalink'] )
									continue;
								if( ! $_hook['id'] )
									continue;
								if( is_array( $_hook['permalink'] ) && ! in_array( $post_type, $_hook['permalink'] ) )	
									continue;
								if( $hook_obj = get_post( $_hook['id'] ) ) :
									array_push( $hooks, $hook_obj );
								endif;
							endforeach;
						break;
				endswitch;
				
			endforeach;
		endforeach;
		
		return $hooks;
	}
		
	/** == == **/
	public static function getPermalinkStructure( $type, $object_type = 'post' /* post | taxonomy */ )
	{
		$object_type = ( $object_type === 'post' ) ? 'post_type' : 'taxonomy';
		
		if( ! isset( self::$Hooks[$object_type][$type] ) )
				return;
		
		$hook_id = 0;
		$factory = self::$Hooks[$object_type][$type];	
		foreach( (array) $factory->getHooks() as $attrs ) :
			if( ! $attrs['permalink'] )	
				continue;
			$hook_id = (int) $attrs['id'];
			break;
		endforeach;
		
		if( ! $hook_id )
			return;
		
		return self::$Hooks[$object_type][$type]->getArchiveSlug( $hook_id );
	}
}