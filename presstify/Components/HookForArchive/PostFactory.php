<?php
namespace tiFy\Components\HookForArchive;

use \tiFy\Environment\App;

class PostFactory extends App
{
	/* = ARGUMENTS = */
	public $hooks = array();

	/* = CONTROLEUR = */
	/** == Récupération des types d'archives == **/
	function get_hooks()
	{
		return $this->hooks;
	}

	/** == Récupération des types d'archives == **/
	function get_archive_post_types()
	{
		return array_keys( $this->hooks );
	}

	/** == Récupération du type de post d'accroche selon un type de post d'archive == **/
	function get_hook_post_type( $archive_post_type )
	{
		if( isset( $this->hooks[ $archive_post_type ]['hook_post_type'] ) )
			return $this->hooks[ $archive_post_type ]['hook_post_type'];
	}

	/** == Récupération de l'ID du post d'accroche selon un type de post d'archive == **/
	function get_hook_id( $archive_post_type )
	{
		if( isset( $this->hooks[ $archive_post_type ]['hook_id'] ) )
			return $this->hooks[ $archive_post_type ]['hook_id'];
	}

	/** == Vérifie si un post est un post d'accroche == **/
	function is_hook( $post_id = null )
	{
		if( ! $post_id && is_singular( ) )
			$post_id = get_the_ID();
			if( ! $post_id )
				return false;
				if( ! $post = get_post( $post_id ) )
					return false;

					foreach( $this->hooks as $archive_post_type => $args )
						if( $this->get_hook_id( $archive_post_type ) == $post->ID )
							return true;
	}

	/** == Vérifie si un post est une lié à un post d'accroche == **/
	function is_archive_post( $post_id = null )
	{
		return ( $this->get_archive_post_hook_id( $post_id ) ) ? true : false;
	}

	/** == Récupération de l'ID du post d'accroche pour un post d'archive == **/
	function get_archive_post_hook_id( $post_id = null )
	{
		if( ! $post_id && is_singular( ) )
			$post_id = get_the_ID();
			if( ! $post_id )
				return false;

				if( $post = get_post( $post_id ) )
					return $this->get_hook_id( $post->post_type );
	}

	/** == Récupération de l'ID du post d'accroche pour un post d'archive == **/
	function get_archive_post_hook_child_id( $post_id = null, $single = true )
	{
		if( ! $post_id && is_singular( ) )
			$post_id = get_the_ID();
			if( ! $post_id )
				return false;
				if( ! $post = get_post( $post_id ) )
					return;

					$archive_post_type = $post->post_type;
					$hook_post_type = $this->get_hook_post_type( $archive_post_type );

					if( ! $ids =  get_post_meta( $post->ID, '_'. $hook_post_type .'_for_'. $archive_post_type ) )
						return;

						if( $single )
							return current( $ids );
							else
								return $ids;
	}
		
	/** == Récupération de la structure des permaliens == **/
	function get_permalink_structure( $archive_post_type, $hook_post_type, $hook_id )
	{
		$permalink_structure = array();

		// Bypass
		if( ! $hook_id )
			return;

			// Récupération des parents du post d'accroche
			if( $ancestors = get_ancestors( $hook_id, $hook_post_type ) ) :
			$ancestors = array_reverse( $ancestors );
			foreach( $ancestors as $post_id ) :
			$post = get_post( $post_id );
			$permalink_structure[] = array( 'permalink' => get_permalink( $post->ID ), 'name' => $post->post_name, 'title' => $post->post_title );
			endforeach;
			endif;

			// Interrruption
			if( ! $post = get_post( $hook_id ) )
				return;

				// Recursivité des sous éléments
				if( ( $parent_hook_post_type = $this->get_hook_post_type( $post->post_type ) ) && ( $parent_hook_id = $this->get_hook_id( $post->post_type ) ) )
					$permalink_structure = array_merge( $this->get_permalink_structure( $post->post_type, $parent_hook_post_type, $parent_hook_id ), $permalink_structure );

					$permalink_structure[] = array( 'permalink' => get_permalink( $post->ID ), 'name' => $post->post_name, 'title' => $post->post_title );

					return $permalink_structure;
	}

	/** == Chemin vers les archives == **/
	function get_archive_slug( $archive_post_type, $hook_post_type, $hook_id )
	{
		if( ! $permalink_structure = $this->get_permalink_structure( $archive_post_type, $hook_post_type, $hook_id ) )
			return;

			$archive_slug = "";
			foreach( $permalink_structure as $permalink )
				$archive_slug .= $permalink['name']. '/';

				$archive_slug = untrailingslashit( $archive_slug );

				return $archive_slug;
	}
}