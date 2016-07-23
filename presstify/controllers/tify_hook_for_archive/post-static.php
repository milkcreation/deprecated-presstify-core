<?php
class tiFy_HookForArchive_PostStatic extends tiFy_HookForArchive_Post{
	/* = ARGUMENTS = */
	public	// Configuration
			$hooks = array( ),
			$removes = array(),
			$rules,
			// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_HookForArchive_Master $master ){
		// Déclaration de la classe de Référence
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'registered_post_type', array( $this, 'wp_registered_post_type' ), 10, 2 );
		add_filter( 'rewrite_rules_array', array( $this, 'wp_rewrite_rules_array' ) );
		add_filter( 'pre_update_option', array( $this, 'wp_pre_update_option' ), 10, 3  );
		
		// Actions et Filtres TiFy
		add_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ) );
		add_filter( 'tify_breadcrumb_is_archive', array( $this, 'tify_breadcrumb_is_archive' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		foreach( $this->master->hooks as $post_type => $args ) 
			if( $args['display'] === 'static' )			
				$this->hooks[$post_type] = $args;
		
	}
	
	/** == Finalisation de l'enregistrement d'un type de post personnalisé ==
	 * @see /wp-includes/post.php - function register_post_type( $post_type, $args = array() )
	**/
	function wp_registered_post_type( $archive_post_type, $args ){
		// Bypass		
		if( ! $hook_post_type = $this->get_hook_post_type( $archive_post_type ) )
			return;			
		if( ! $hook_id = $this->get_hook_id( $archive_post_type ) )
			return;			
		
		$this->add_rewrite_rules( $archive_post_type, $hook_post_type, $hook_id, $args );
	}

	/** == == **/
	function wp_rewrite_rules_array( $rules ){
		if( empty( $this->removes ) )
			return $rules;
		
		$this->rules = $rules;		
		foreach( $this->removes as $archive_post_type => $v )
			$this->remove_rewrite_rules( $archive_post_type, $v['hook_post_type'], $v['hook_id'], $v['args'] );
		
		return $this->rules;
	}

	/** == Sauvegarde == **/
	function wp_pre_update_option( $value, $option, $old_value ){		
		$hook_post_type = false; $archive_post_type = false;
		foreach( $this->get_hooks() as $_archive_post_type => $v ) :	
			if( $option !== $v['hook_post_type'] .'_for_'. $_archive_post_type )
				continue;
			$hook_post_type = $v['hook_post_type']; 
			$archive_post_type = $_archive_post_type;
			break;	
		endforeach;	
	
		//Bypass
		if( ! $hook_post_type || ! $archive_post_type )
			return $value;

		if( ! $args = get_post_type_object( $archive_post_type ) )
			return $value;
			
		$this->add_rewrite_rules( $archive_post_type, $hook_post_type, $value, $args );
		if( $value !== $old_value )
			$this->removes[$archive_post_type] = array(
				'hook_post_type' 	=> $hook_post_type, 
				'hook_id' 			=> $old_value,
				'args'				=> $args			
			);	
				
		flush_rewrite_rules( );
		return $value;		
	}
	
	/* = ACTIONS ET FILTRES TiFY = */
	/** == Fil d'Ariane == **/
	/*** === Page === ***/	
	function tify_breadcrumb_is_single( $output ){		
		if( ! in_array( get_post_type(), $this->get_archive_post_types() ) )
			return $output;	
		
		$archive_post_type = get_post_type();
		if( ! $hook_id = $this->get_hook_id( $archive_post_type ) )
			return $output;
		
		if( ! $post = get_post( $hook_id ) )
			return;
		
		$ancestors = "";
		if( $post->post_parent && $post->ancestors ) :
			$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
			foreach( $parents as $parent )
				$ancestors .= sprintf('<li><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
		endif;	
		
		$post_type_archive_link = sprintf( '<li><a href="%1$s">%2$s</a></li>', get_post_type_archive_link( get_post_type() ), get_the_title( $hook_id ) );
		$output = $ancestors . $post_type_archive_link . '<li class="active">'. esc_html( wp_strip_all_tags( get_the_title() ) ) .'</li>';
			
		return  $output;
	}
	
	/*** === Page de flux === ***/	
	function tify_breadcrumb_is_archive( $output ){
		if( ! in_array( get_query_var( 'post_type' ), $this->get_archive_post_types() ) )
			return $output;
		
		$archive_post_type = get_query_var( 'post_type' );
		
		if( ! $hook_id = $this->get_hook_id( $archive_post_type ) )
			return $output;
		if( ! $post = get_post( $hook_id ) )
			return;
		$ancestors = "";
		if( $post->post_parent && $post->ancestors ) :
			$parents = ( count( $post->ancestors ) > 1 ) ? array_reverse( $post->ancestors ) : $post->ancestors;
			foreach( $parents as $parent )
				$ancestors .= sprintf('<li><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), esc_html( wp_strip_all_tags( get_the_title( $parent ) ) ) );
		endif;	

		return $ancestors . $output;
	}
	
	/* = CONTROLEURS = */
	/** == == **/
	function add_rewrite_rules( $archive_post_type, $hook_post_type, $hook_id, $args ){
		global $wp_post_types, $wp_rewrite;
		
		if( ! $archive_slug = $this->get_archive_slug( $archive_post_type, $hook_post_type, $hook_id ) )
			return;
		
		if( $archive_post_type != 'post' )
			$args->has_archive = true;
		
		$args->rewrite['slug'] = $archive_slug;		
		
		if ( $args->hierarchical )
			add_rewrite_tag( "%$archive_post_type%", '(.+?)', $args->query_var ? "{$args->query_var}=" : "post_type=$archive_post_type&pagename=" );
		else
			add_rewrite_tag( "%$archive_post_type%", '([^/]+)', $args->query_var ? "{$args->query_var}=" : "post_type=$archive_post_type&name=" );
		
		if ( $args->has_archive ) :
			//$archive_slug = $args->has_archive === true ? $args->rewrite['slug'] : $args->has_archive;
			if ( $args->rewrite['with_front'] )
				$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
			else
				$archive_slug = $wp_rewrite->root . $archive_slug;
			
			add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}", 'top' );
			if ( !empty( $args->rewrite['feeds'] ) && $wp_rewrite->feeds ) :
				$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
				add_rewrite_rule( "{$archive_slug}/feed/{$feeds}/?$", "index.php?post_type={$archive_post_type}&tify_hook_id=$hook_id" . '&feed=$matches[1]', 'top' );
				add_rewrite_rule( "{$archive_slug}/{$feeds}/?$", "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}" . '&feed=$matches[1]', 'top' );
			endif;
		endif;	
		
		if ( $args->rewrite['pages'] )
			add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}" . '&paged=$matches[1]', 'top' );	
	
		$permastruct_args = $args->rewrite;
		if( isset( $permastruct_args['feeds'] ) )
			$permastruct_args['feed'] = $permastruct_args['feeds'];
		add_permastruct( $archive_post_type, "{$archive_slug}/%$archive_post_type%", $permastruct_args );	
		
		$wp_post_types[ $archive_post_type ] = $args;
	}
	
	/** == == **/
	function remove_rewrite_rules( $archive_post_type, $hook_post_type, $hook_id, $args ){
		global $wp_rewrite;

		// Bypass
		if( ! $archive_slug = $this->get_archive_slug( $archive_post_type, $hook_post_type, $hook_id ) )
			return;
			
		if( isset( $this->rules["{$archive_slug}/?$"] ) )
			unset($this->rules["{$archive_slug}/?$"] );
		/*if( isset( $this->rules["{$archive_slug}/feed/{$feeds}/?$"] ) )
			unset($this->rules["{$archive_slug}/feed/{$feeds}/?$"] );
		if( isset( $this->rules["{$archive_slug}/{$feeds}/?$"] ) )
			unset($this->rules["{$archive_slug}/{$feeds}/?$"] );*/
		if( isset( $this->rules["{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$"] ) )
			unset($this->rules["{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$"] );		
	}
}	