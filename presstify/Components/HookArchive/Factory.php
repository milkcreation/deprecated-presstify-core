<?php
namespace tiFy\Components\HookArchive;

use \tiFy\Environment\App;

class Factory extends App
{	
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	
	/** == CONFIGURATION == **/
	/**
	 * Type d'accroche (requis)
	 * (string) post (par défaut) | taxonomy
	 */	
	protected 		$Type					= '';
	
	/**
	 * Identifiant de l'archive à accrocher 
	 * Type de post natif ou personnalisé | Type de taxonomy native ou personnalisée 
	 * (string) ex: post (post_type) | page (post_type) | category (taxonomy) | tag (taxonomy) | ...
	 */
	protected 		$Archive				= '';
	
	/**
	 * Attributs des contenus d'accroche 
	 * (int)
	 */
	private			$Hooks					= 0;
	
	/**
	 * Liste des chemins d'affichage des archives
	 */
	private 		$ArchiveSlugs			= array();
	
	/**
	 * Paramètres de configuration globaux (hérité par les contenus d'accroche)
	 */
	protected		$Options				= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct( $args = array() )
	{
		parent::__construct();

		// Définition des arguments de configuration
		$this->Type 			= (string) 	$args['type'];
		$this->Archive 			= (string) 	$args['archive'];
		$this->Options 			= (array) 	$this->ParseOptions( $args['options'] );
		$this->Hooks 			= (array) 	$this->ParseHooks( $args['hooks'] );
		
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );
	}
					
	/* = CONTROLEURS = */
	/** == Traitement des options globales == **/
	private function ParseOptions( $options )
	{
		$defaults = array(
			'edit'			=> true,	
			'post_type'		=> 'page',
			'duplicate'		=> false,
			'rewrite'		=> false,
			'permalink'		=> false
		);
		return wp_parse_args( $options, $defaults );
	}
	
	/** == Traitement de contenus d'accroche == **/
	private function ParseHooks( $hooks )
	{
		$defaults = array(
			'id'		=> 0,
			'post_type'	=> (string) $this->Options['post_type'],
			'edit'		=> (bool)  $this->Options['edit'],	
			'permalink'	=> $this->Options['permalink']
		);
		if( $this->Type === 'taxonomy' )
			$defaults['term'] = 0;
		
		if( $_hooks = get_option( "tify_hook_". $this->Type ."_". $this->Archive, false ) )
			$hooks = $_hooks;	
		
		foreach( $hooks as &$hook ) :			
			$hook = wp_parse_args( $hook, $defaults );
		endforeach;

		return $hooks;
	}
	
	/** == Récupération de la liste des contenu d'accroche == **/
	public function GetHooks()
	{
		return $this->Hooks;
	}
	
	/** == Récupération des options == **/
	public function GetOption( $index = null )
	{
		if( ! $index )
			return $this->Options;
		if( isset( $this->Options[$index] ) )
		return $this->Options[$index];
	}
		
	/** == Chemin vers les archives == **/
	protected function GetArchiveSlug( $hook_id, $hook_post_type )
	{
		if( ! empty( $this->ArchiveSlugs[$hook_post_type][$hook_id] ) )
			return $this->ArchiveSlugs[$hook_post_type][$hook_id];
		
		$archive_slug = "";
		
		// Bypass
		if( ! $hook_id )
			return $archive_slug;
		if( ! $hook = get_post( $hook_id ) )
			return $archive_slug;
			
		$permalink_structure = array();

		// Récupération des parents du contenu d'accroche
		if( $ancestors = get_ancestors( $hook_id, $hook_post_type ) ) :
			$ancestors = array_reverse( $ancestors );
		
			foreach( (array) $ancestors as $ancestor_id ) :
				$ancestor = get_post( $ancestor_id);
				$permalink_structure[] = array( 
					'permalink'		=> get_permalink( $ancestor->ID ), 
					'name'			=> $ancestor->post_name, 
					'title'			=> $ancestor->post_title
				);
			endforeach;
		endif;
		
		/** @todo Recursion des hook (hook2hook) **/
		/*// Recursivité des sous éléments
		if( ( $parent_hook_post_type = $this->get_hook_post_type( $post->post_type ) ) && ( $parent_hook_id = $this->get_hook_id( $post->post_type ) ) )
			$permalink_structure = array_merge( $this->get_permalink_structure( $post->post_type, $parent_hook_post_type, $parent_hook_id ), $permalink_structure );*/

		$permalink_structure[] = array( 'permalink' => get_permalink( $hook_id ), 'name' => $hook->post_name, 'title' => $hook->post_title );
				
		foreach( $permalink_structure as $permalink ) :
			$archive_slug .= $permalink['name']. '/';
		endforeach;
		
		return $this->ArchiveSlugs[$hook_post_type][$hook_id] = rtrim( $archive_slug, '/' );
	}
	
	/* = ACTIONS = */
	final public function tify_options_register_node()
	{
		if( ! $this->hasAdmin() )
			return;

		$attrs = array(
			'id' 		=> 'tify_hookarchive-'. $this->Type .'-'. $this->Archive,
			'parent'	=> 'tify_hookarchive',
			'cb'		=> "\\tiFy\\Components\\HookArchive\\Taboox\\Option\\HookSelector\\Admin\\HookSelector",
			'args'		=> array( 'type' => $this->Type, 'archive' => $this->Archive, 'hooks' => $this->Hooks, 'options' => $this->Options )
		);
		
		switch( $this->Type ) :
			case 'post' :
				$attrs['title']	= get_post_type_object( $this->Archive )->label;				
				break;
			case 'taxonomy' :
				$attrs['title']	= get_taxonomy( $this->Archive )->label;
				break;
		endswitch;
			
		tify_options_register_node( $attrs );
	}
	
	/* = METHODES PUBLIC = */
	/** == Récupération de la liste des contenu d'accroche == **/
	public function hasAdmin()
	{
		foreach( $this->Hooks as $hook ) :
			if( $hook['edit'] ) :
				return true;
			endif;
		endforeach;
			
		return false;
	}
}