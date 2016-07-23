<?php
namespace tiFy\Components\HookArchive;

final class Taxonomy extends Factory
{
	/* = ARGUMENTS = */

	
	/* = CONSTRUCTEUR = */
	public function __construct( $args = array() )
	{
		parent::__construct( $args );		
		
		add_action( 'registered_taxonomy', array( $this, 'registered_taxonomy' ), 10, 3 );
		add_action( 'init', array( $this, 'init' ), 9999 );
		add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );
		add_action( 'edit_form_top', array( $this, 'edit_form_top' ), 10 );
		add_filter( 'term_link', array( $this, 'term_link' ), 10, 3 );
		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 4 );
		
		add_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ) );
		add_filter( 'tify_breadcrumb_is_tax', array( $this, 'tify_breadcrumb_is_tax' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
	}
		
	/* = ACTIONS = */
	/** == Déclaration de la taxonomie == **/
	final public function registered_taxonomy( $taxonomy, $object_type, $args )
	{	
		if( $this->Archive !== $taxonomy )
			return;
		
		global $wp_rewrite;
		
		$RegisteredTaxonomy = array();
		
		foreach( (array) $this->GetHooks() as $hook ) :
			if( ! $hook['term'] || ! $hook['id'] )
				continue;
			if( ! $term = get_term( $hook['term'] ) )
				continue;
			
			$RegisteredTaxonomy[$hook['id']][] = $term->slug;	
		endforeach;
		
		foreach( (array) $RegisteredTaxonomy as $hook_id => $slugs ) :
			$archive_slug = (string) $this->GetArchiveSlug( $hook_id );
				$_slugs = join( ',', $slugs );
				add_rewrite_rule( "{$archive_slug}/?$", "index.php?{$taxonomy}={$_slugs}&tify_hook_id={$hook_id}", 'top' );
				add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?{$taxonomy}={$_slugs}&tify_hook_id={$hook_id}" . '&paged=$matches[1]', 'top' );		
		endforeach;
					
		// Empêche l'execution multiple de l'action	
		remove_action( 'registered_taxonomy', array( $this, 'registered_taxonomy' ) );
	}
	
	/** == == **/
	final public function init()
	{
		if( $this->Options['rewrite'] ) :
			global $wp_post_types;

			foreach( (array) $this->Options['permalink'] as $post_type ) :
				if( isset( $wp_post_types[$post_type] ) ) :
					call_user_func( array( $this, 'registered_post_type_for_taxonomy' ), $post_type, $wp_post_types[$post_type] );
				elseif( ! has_action( 'registered_post_type', array( $this, 'registered_post_type_for_taxonomy' ) ) ) :
					add_action( 'registered_post_type', array( $this, 'registered_post_type_for_taxonomy' ), 10, 2 );
				endif;
			endforeach;
		endif;
	}
			
	/** == == **/
	final public function registered_post_type_for_taxonomy( $post_type, $args )
	{
		if( ! is_object_in_taxonomy( $post_type, $this->Archive ) )
			return;

		// Affichage du contenu seul
		foreach( (array) $this->GetHooks() as $hook ) :
			if( ! $hook['term'] || ! $hook['permalink'] || ( ! $term = get_term( $hook['term'] ) ) )
				continue;
			if( is_array( $hook['permalink'] ) && ! in_array( $post_type, $hook['permalink'] ) )	
				continue;
			
			$archive_slug = (string) $this->GetArchiveSlug( $hook['id'] );
			
			if ( $args->hierarchical )
				add_rewrite_rule( $archive_slug ."/(.+?)/?$", "index.php?post_type={$post_type}&tify_hook_id={$hook['id']}" . '&pagename=$matches[1]', 'top' );
			else
				add_rewrite_rule( $archive_slug ."/([^/]+)/?$", "index.php?post_type={$post_type}&tify_hook_id={$hook['id']}" . '&name=$matches[1]', 'top' );
		endforeach;
	}
	
	/** == Nettoyage des metaboxe == **/
	final public function remove_meta_box()
	{
		foreach( (array) $this->GetHooks() as $hook ) :	
			foreach( (array) $hook['permalink'] as $post_type ) :
				remove_meta_box( 'tagsdiv-'. $this->Archive, $post_type, true );
			endforeach;
		endforeach;
	}
	
	/** == Affichage d'un message d'avertissement lors de l'édition du contenu d'accroche == **/
	final public function edit_form_top( $post )
	{
		// Vérification de correspondance
		foreach( (array) $this->GetHooks() as $hook ) :
			if( $post->post_type !== $hook['post_type'] ) :
				continue;
			elseif( (int) $post->ID !== $hook['id'] ) :
				continue;
			elseif( $term = get_term( $hook['term'] ) ) :
				break;
			endif;
		endforeach;
		
		// Bypass
		if( empty( $term ) || is_wp_error( $term ) )
			return;
			
		$label = $term->name;	
		
		echo 	"<div class=\"notice notice-info inline\">\n".
					"\t<p>Vous éditez actuellement la page d'affichage des \"{$label}\".</p>\n".
				"</div>";	
	}
	
	/** == == **/
	final public function term_link( $termlink, $term, $taxonomy )
	{
		if( $taxonomy !== $this->Archive )
			return $termlink;
		
		$hook_id = 0;	
		foreach( (array) $this->GetHooks() as $hook ) :	
			if( ( (int) $hook['term'] !== $term->term_id  ) )
				continue;
			$hook_id 			= $hook['id'];
			$hook_post_type 	= $hook['post_type'];
			break;
		endforeach;
		
		if( empty( $hook_id ) || empty( $hook_post_type ) )
			return $termlink;
		
		$archive_slug = (string) $this->GetArchiveSlug( $hook_id );
		
		return site_url( $archive_slug );
	}
		
	/** == == **/
	final public function post_type_link( $post_link, $post, $leavename, $sample )
	{
		// Bypass
		if( ! $this->Options['rewrite'] )
			return $post_link;
		
		if( ! is_object_in_taxonomy( $post->post_type, $this->Archive ) )
			return $post_link;
		
		$terms = wp_get_post_terms( $post->ID, $this->Archive, array( 'fields' => 'ids' ) );
		if( is_wp_error( $terms ) )
			return $post_link;
		
		$hook_id = 0;	
		foreach( (array) $this->GetHooks() as $hook ) :	
			if( ! in_array( $hook['term'], $terms ) || ! $hook['permalink'] || ( ! $term = get_term( $hook['term'] ) ) )
				continue;
			if( is_array( $hook['permalink'] ) && ! in_array( $post->post_type, $hook['permalink'] ) )	
				continue;
			
			$permalink_term = (int) get_post_meta( $post->ID, '_tify_hookarchive_term_permalink', true );
			if( $permalink_term < 0 )
				continue;
			elseif( ( $permalink_term > 0 ) && ( $permalink_term !== (int) $hook['term'] ) )
				continue;
			
			$hook_id 			= $hook['id'];
			$hook_post_type 	= $hook['post_type'];
			break;
		endforeach;

		if( empty( $hook_id ) || empty( $hook_post_type ) )
			return $post_link;
		
		$archive_slug = (string) $this->GetArchiveSlug( $hook_id );	
			
		return site_url( $archive_slug .'/'. $post->post_name );
	}
	
	/* = FIL D'ARIANE = */
	/* = Page de contenu seul == */	
	final public function tify_breadcrumb_is_single( $output )
	{		
		// Bypass
		if( 
			! $this->Options['rewrite'] ||
			! is_object_in_taxonomy( get_post_type(), $this->Archive ) ||
			( ! $terms = wp_get_post_terms( get_the_ID(), $this->Archive, array( 'fields' => 'ids' ) ) ) ||
			 is_wp_error( $terms ) 
		) :
		else :
			foreach( $this->GetHooks() as $hook ) :				
				if( ! in_array( $hook['term'], $terms ) || ! $hook['permalink'] || ( ! $term = get_term( $hook['term'] ) ) )
					continue;
				
				if( ( $hook_id = $hook['id'] ) && ( $term->term_id === (int) get_post_meta( $hook_id, '_tify_hookarchive_term_permalink', true ) ) )
					break;
				
				continue;				
			endforeach;
						
			if( ! empty( $hook_id ) && ( $post = get_post( $hook_id ) ) ) :			
				$ancestors = "";
				if( $post->post_parent && $post->ancestors ) :
					$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
					foreach( $parents as $parent )
						$ancestors .= sprintf('<li><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
				endif;	
				
				$term_link = sprintf( '<li><a href="%1$s">%2$s</a></li>', get_term_link( $term ), get_the_title( $hook_id ) );
				$output = $ancestors . $term_link . '<li class="active">'. esc_html( wp_strip_all_tags( get_the_title() ) ) .'</li>';
			endif;
		endif;
			
		// Empêche l'execution multiple du filtre
		remove_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ) );
		
		return  $output;
	}
	
	/** == Page de flux == **/	
	final public function tify_breadcrumb_is_tax( $output )
	{
		if( 
			( get_queried_object()->taxonomy !== $this->Archive ) ||
			( ! $hook_id = get_query_var( 'tify_hook_id' ) ) ||
			( ! $post = get_post( $hook_id ) )
		) :
		else:		
			$ancestors = "";
			if( $post->post_parent && $post->ancestors ) :
				$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
				foreach( $parents as $parent )
					$ancestors .= sprintf( '<li><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
			endif;	
			
			$output = $ancestors . '<li class="active">'. esc_html( wp_strip_all_tags( get_the_title( $hook_id ) ) ) .'</li>';
		endif;
		
		// Empêche l'execution multiple du filtre
		remove_filter( 'tify_breadcrumb_is_tax', array( $this, 'tify_breadcrumb_is_archive' ) );
		
		return $output;
	}
	
	/** == Déclaration des sections de boites de saisie des metadonnées de contenu == **/
	final public function tify_taboox_register_node()
	{
		foreach( (array) $this->GetHooks() as $hook ) :	
			foreach( (array) $hook['permalink'] as $post_type ) :
				tify_taboox_register_node( 
					$post_type, 
					array(
						'title'	=> get_taxonomy( $this->Archive )->label,
	    				'cb'	=> "\\tiFy\\Components\\HookArchive\\Taboox\\Post\\TermSelector\\Admin\\TermSelector",
	   					'args'	=> array(	
	   						'taxonomy' 			=> $this->Archive, 
	   						'selector'			=> 'checkbox', 
	   						'show_option_none'	=> false   						
	   					),
						'order'				=> 0
					)
				);
			endforeach;
		endforeach;
	}
}	