<?php
namespace tiFy\Components\HookArchive;

use tiFy\Components\Breadcrumb\Breadcrumb;

final class PostType extends Factory
{
	/* = CONTRUCTEUR = */
	public function __construct( $args = array() )
	{
		parent::__construct( $args );
		
		add_action( 'registered_post_type', array( $this, 'registered_post_type' ), 10, 2 );
		add_action( 'edit_form_top', array( $this, 'edit_form_top' ), 10 );
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );
		add_filter( 'post_type_archive_link', array( $this, 'post_type_archive_link' ), 99, 2 );
		
		add_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ), 10, 2 );
		add_filter( 'tify_breadcrumb_is_archive', array( $this, 'tify_breadcrumb_is_archive' ) );
		add_filter( 'tify_seo_title_is_post_type_archive', array( $this, 'tify_seo_title_is_post_type_archive' ) );
		add_filter( 'tify_seo_desc_is_post_type_archive', array( $this, 'tify_seo_desc_is_post_type_archive' ) );
	}
	
	/* = ACTIONS = */
	/** == Déclaration du type de post == **/
	final public function registered_post_type( $post_type, $args )
	{
		if( $this->Archive !== $post_type )
			return;

		// Modification des régles de réécriture
		global $wp_rewrite;						
		
		foreach( (array) $this->GetHooks() as $hook ) :
			$archive_slug = (string) $this->GetArchiveSlug( $hook['id'] );
			
			// Affichage de la page de flux
			add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type={$post_type}&tify_hook_id={$hook['id']}", 'top' );
			add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type={$post_type}&tify_hook_id={$hook['id']}" . '&paged=$matches[1]', 'top' );	
			
			// Affichage du contenu seul
			if( $this->Options['rewrite'] && $hook['permalink'] ) :
				if ( $args->hierarchical )
					add_rewrite_tag( "%$post_type%", '(.+?)', $args->query_var ? "{$args->query_var}=" : "post_type=$post_type&pagename=" );
				else
					add_rewrite_tag( "%$post_type%", '([^/]+)', $args->query_var ? "{$args->query_var}=" : "post_type=$post_type&name=" );		
				add_permastruct( $post_type, "{$archive_slug}/%$post_type%" );
			endif;
				
			// Empêche l'execution multiple de l'action	
			remove_action( 'registered_post_type', array( $this, 'registered_post_type' ) );	
		endforeach;
	}
	
	/** == Affichage d'un message d'avertissement lors de l'édition du contenu d'accroche == **/
	final public function edit_form_top( $post )
	{
		// Vérification de correspondance
		$is_hook = false; 
		foreach( (array) $this->GetHooks() as $hook ) :
			if( get_post_type( $post ) !== $hook['post_type'] ) :
				continue;			
			elseif( (int) $post->ID !== (int) $hook['id'] ) :
				continue;
			else :
				$is_hook = true;
				break;
			endif;
		endforeach;

		// Bypass
		if( ! $is_hook )
			return;
			
		$label = get_post_type_object( $this->Archive )->label;	
		
		echo 	"<div class=\"notice notice-info inline\">\n".
					"\t<p>". sprintf( __( 'Vous éditez actuellement la page d\'affichage des "%s"', 'tify' ), $label ) . "</p>\n".
				"</div>";	
	}
	
	/** == == **/
	final public function display_post_states( $post_states, $post )
	{
		 // Vérification de correspondance
		$is_hook = false; 
		foreach( (array) $this->GetHooks() as $hook ) :
			if( get_post_type( $post ) !== $hook['post_type'] ) :
				continue;			
			elseif( (int) $post->ID !== (int) $hook['id'] ) :
				continue;
			else :
				$is_hook = true;
				break;
			endif;
		endforeach;

		// Bypass
		if( ! $is_hook )
			return $post_states;
			
		$label = get_post_type_object( $this->Archive )->label;
		
		$post_states[ 'hookarchive_for_'. get_post_type( $post ) ] = sprintf( __( 'Page des %s', 'tify' ), strtolower( $label ) );
		
		return $post_states;
	}
	
	/** == == **/
	final public function post_type_archive_link( $link, $post_type )
	{	
		if( $post_type !== $this->Archive )
			return $link;
		/*if( ! $this->Options['rewrite'] )
			return $link; */
		
		$hook_id = 0;	
		foreach( (array) $this->GetHooks() as $hook ) :	
			/*if( ! $hook['permalink'] )
				continue;*/
			$hook_id 			= $hook['id'];
			$hook_post_type 	= $hook['post_type'];
			break;
		endforeach;
		
		if( empty( $hook_id ) || empty( $hook_post_type ) )
			return $link;
		
		$archive_slug = (string) $this->GetArchiveSlug( $hook_id );	
			
		return site_url( $archive_slug );
	}
		
	/* = FIL D'ARIANE = */
	/* = Page de contenu seul == */	
	final public function tify_breadcrumb_is_single( $output )
	{		
		if( ! $this->Options['rewrite'] ) :
		elseif( ( get_post_type() !== $this->Archive ) ) :
		else :
			foreach( $this->GetHooks() as $hook ) :				
				if( ! $permalink = $hook['permalink'] )
					continue;
				if( ! $hook_id = $hook['id'] ) 
					continue;
				break;
			endforeach;

			if( ! empty( $hook_id ) && ( $post = get_post( $hook_id ) ) ) :
			    $Template = $this->appGetContainer('tiFy\Components\Breadcrumb\Template');
						
				$Template::resetParts();
			
				$ancestors = array();
				if( $post->post_parent && $post->ancestors ) :
					$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
					foreach( $parents as $parent ) :
						$ancestors[] = array( 'url' => get_permalink( $parent ), 'name' => $Template::titleRender( $parent ), 'title' => $Template::titleRender( $parent ) );
					endforeach;
				endif;	
				$ancestors[] = array( 'url' => get_post_type_archive_link( get_post_type() ), 'name' => $Template::titleRender( $hook_id ), 'title' => $Template::titleRender( $hook_id ) );
								
				$_ancestors = "";
				foreach( $ancestors as $a ) :				
					$_ancestors .= $Template::partRender( $a );
					$Template::addPart( $a );
				endforeach;
				
				$part = array( 'name' => $Template::titleRender( get_the_ID() ) );
				$Template::addPart( $part );
								
				$output = $_ancestors . $Template::currentRender( $part );
			endif;
		endif;
		
		// Empêche l'execution multiple du filtre
		remove_filter( 'tify_breadcrumb_is_single', __METHOD__ );
		
		return $output;
	}
	
	/** == Page de flux == **/	
	final public function tify_breadcrumb_is_archive( $output )
	{
		if( 
			( get_query_var( 'post_type' ) !== $this->Archive ) ||
			( ! $hook_id = get_query_var( 'tify_hook_id' ) ) ||
			( ! $post = get_post( $hook_id ) )
		) :
		else:		
			$ancestors = "";
			if( $post->post_parent && $post->ancestors ) :
				$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
				foreach( $parents as $parent )
					$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s" class="tiFyBreadcrumb-ItemLink">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
			endif;	
			
			$Template = $this->appGetContainer('tiFy\Components\Breadcrumb\Template');
			$part = array( 'name' => esc_html( wp_strip_all_tags( get_the_title( $hook_id ) ) ) );
			
			$output = $ancestors . $Template::currentRender( $part );
		endif;
		
		// Empêche l'execution multiple du filtre
		remove_filter( 'tify_breadcrumb_is_archive', __METHOD__ );
		
		return $output;
	}
	
	/** == Titre de référencement == **/
	final public function tify_seo_title_is_post_type_archive( $title )
	{
		if( 
			( get_query_var( 'post_type' ) !== $this->Archive ) ||
			( ! $hook_id = get_query_var( 'tify_hook_id' ) ) ||
			( ! $post = get_post( $hook_id ) )
		) :
		elseif( ( $seo_meta = get_post_meta( $post->ID, '_tify_seo_meta', true ) ) && ! empty( $seo_meta['title'] ) ) :
			$title = $seo_meta['title'];
		endif;
		
		return $title;
	}
	
	/** == Description de référencement == **/
	final public function tify_seo_desc_is_post_type_archive( $desc )
	{
		if( 
			( get_query_var( 'post_type' ) !== $this->Archive ) ||
			( ! $hook_id = get_query_var( 'tify_hook_id' ) ) ||
			( ! $post = get_post( $hook_id ) )
		) :
		elseif( ( $seo_meta = get_post_meta( $post->ID, '_tify_seo_meta', true ) ) && ! empty( $seo_meta['desc'] ) ) :
			$desc = $seo_meta['desc'];
		endif;
		
		return $desc;
	}
}	