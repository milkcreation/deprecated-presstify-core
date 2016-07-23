<?php
namespace tiFy\Components\HookArchive;

final class PostType extends Factory
{
	/* = CONTRUCTEUR = */
	public function __construct( $args = array() )
	{
		parent::__construct( $args );
		
		add_action( 'registered_post_type', array( $this, 'registered_post_type' ), 10, 2 );
		add_action( 'edit_form_top', array( $this, 'edit_form_top' ), 10 );
		add_filter( 'post_type_archive_link', array( $this, 'post_type_archive_link' ), 99, 2 );
		
		add_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ) );
		add_filter( 'tify_breadcrumb_is_archive', array( $this, 'tify_breadcrumb_is_archive' ) );
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
			$archive_slug = (string) $this->GetArchiveSlug( $hook['id'], $hook['post_type'] );
			
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
	final public function post_type_archive_link( $link, $post_type )
	{	
		if( $post_type !== $this->Archive )
			return $link;
		if( ! $this->Options['rewrite'] )
			return $link; 
		
		$hook_id = 0;	
		foreach( (array) $this->GetHooks() as $hook ) :	
			if( ! $hook['permalink'] )
				continue;
			$hook_id 			= $hook['id'];
			$hook_post_type 	= $hook['post_type'];
			break;
		endforeach;
		
		if( empty( $hook_id ) || empty( $hook_post_type ) )
			return $post_link;
		
		$archive_slug = (string) $this->GetArchiveSlug( $hook_id, $hook_post_type );	
			
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
				$ancestors = "";
				if( $post->post_parent && $post->ancestors ) :
					$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
					foreach( $parents as $parent )
						$ancestors .= sprintf('<li><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
				endif;	
				
				$post_type_archive_link = sprintf( '<li><a href="%1$s">%2$s</a></li>', get_post_type_archive_link( get_post_type() ), get_the_title( $hook_id ) );
				$output = $ancestors . $post_type_archive_link . '<li class="active">'. esc_html( wp_strip_all_tags( get_the_title() ) ) .'</li>';
			endif;
		endif;
		
		// Empêche l'execution multiple du filtre
		remove_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ) );
		
		return  $output;
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
					$ancestors .= sprintf( '<li><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
			endif;	
			
			$output = $ancestors . '<li class="active">'. esc_html( wp_strip_all_tags( get_the_title( $hook_id ) ) ) .'</li>';
		endif;
		
		// Empêche l'execution multiple du filtre
		remove_filter( 'tify_breadcrumb_is_archive', array( $this, 'tify_breadcrumb_is_archive' ) );
		
		return $output;
	}
}	