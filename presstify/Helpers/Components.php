<?php
namespace
{
	/* = ARCHIVE FILTERS = */
	/** == Affichage des filtres == **/
	function tify_archive_filters_display( $id = null, $echo = true )
	{
		return tiFy\Components\ArchiveFilters\ArchiveFilters::Display( $id, $echo );
	}
	
	/* = BREADCRUMB = */
	/** == Affichage du fil d'Ariane == **/
	function tify_breadcrumb( $args = array(), $echo = true )
	{
		return tiFy\Components\Breadcrumb\Breadcrumb::display( $args, $echo );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = CONTACT FORM = */
	function tify_contact_form_register( $id, $args = array() )
	{
		return tiFy\Components\ContactForm\ContactForm::Register( $id, $args );
	}
	
	/** == Affichage du formulaire de contact == **/
	function tify_contact_form_display( $id = null, $content = '', $echo = true )
	{
		return tiFy\Components\ContactForm\ContactForm::Display( $id, $content, $echo );
	}
	
	/** == Affichage du formulaire de contact == **/
	function tify_contact_form_hookpage( $id  = null )
	{
		return tiFy\Components\ContactForm\ContactForm::HookPage( $id );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = CUSTOM FIELDS = */
	/** == SUBTITLE == **/
	/*** === Récupération du sous-titre === ***/
	function get_the_subtitle( $post = null )
	{
		if( ! $post )
			global $post;
		// Bypass	
		if( ! $post = get_post( $post) )
			return;
	
		$subtitle = get_post_meta( $post->ID, '_subtitle', true ) ? get_post_meta( $post->ID, '_subtitle', true ) : '';
		$id = isset( $post->ID ) ? $post->ID : 0;
	
		if ( ! is_admin() ) {
			if ( ! empty( $post->post_password ) ) {
				$protected_title_format = apply_filters( 'protected_subtitle_format', __( 'Protected: %s' ) );
				$subtitle = sprintf( $protected_title_format, $subtitle );
			} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
				$private_title_format = apply_filters( 'private_subtitle_format', __( 'Private: %s' ) );
				$subtitle = sprintf( $private_title_format, $subtitle );
			}
		}
	
		return apply_filters( 'the_subtitle', $subtitle, $id );
	}
	
	/** == Affichage du sous-titre == **/
	function the_subtitle( $before = '', $after = '', $echo = true )
	{
		$subtitle = get_the_subtitle();
	
		if ( strlen($subtitle) == 0 )
			return;
	
		$subtitle = $before . $subtitle . $after;
	
		if ( $echo )
			echo $subtitle;
		else
			return $subtitle;
	}
	
	/** == PERMALINK == **/
	/*** === Déclaration de permalien === ***/
	function tify_permalink_register( $url, $title = '', $obj_type = null, $obj = 'post_type' )
	{
		$action = function( $class ) use( $url, $title ) {
			$class::Register( $url, $title );
		};
		
		if( $obj_type )
			add_action( "tify_permalink_register_{$obj}_{$obj_type}", $action );
		else 
			add_action( "tify_permalink_register", $action );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = HOOKFORARCHIVE = */
	/** == Déclaration d'un type de post d'accroche pour des archives == **/
	function tify_hookarchive_register( $hook )
	{
		return tiFy\Components\HookArchive\HookArchive::Register( $hook );
	}
	
	/** == Récupére le contenu d'accroche d'un post == **/
	function tify_hookarchive_get_post_hook( $post = null )
	{
		return tiFy\Components\HookArchive\HookArchive::GetPostHook( $post, true );
	}
	
	/** == Récupére le contenu d'accroche pour un type de post == **/
	function tify_hookarchive_get_post_type_hooks( $post_type, $permalink = true, $object = null )
	{
		return tiFy\Components\HookArchive\HookArchive::GetPostTypeHooks( $post_type, $permalink, $object );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = LOGIN = */
	/** == Affichage d'un élément de template == **/
	function tify_login_init( $id, $config = array() )
	{
		return new tiFy\Components\Login\Factory( $id, $config );
	}
	/** == Affichage d'un élément de template == **/
	function tify_login_display( $id, $tmpl, $args = array() )
	{
		return tiFy\Components\Login\Login::display( $id, $tmpl, $args );
	}
	
	/** == Affichage du formulaire d'authentification == **/
	function tify_login_form_display( $id, $args = array() )
	{
		return tify_login_display( $id, 'form', $args );
	}
	
	/** == Affichage des erreurs de traitement de formulaire == **/
	function tify_login_errors_display( $id, $args = array() )
	{
		return tify_login_display( $id, 'errors', $args );
	}
	
	/** == Affichage des erreurs de traitement de formulaire == **/
	function tify_login_logout_link_display( $id, $args = array() )
	{
		return tify_login_display( $id, 'logout_link', $args );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = MODAL = */
	/** == Création d'un contrôleur d'affichage d'une modale ==
	 * prerequis : wp_enqueue_script( 'tify-modals' );
	 **/
	function tify_modal_toggle( $args = array() )
	{
		$modal = new tiFy\Components\Modal\Modal( 'toggle', $args );
		return $modal->ToggleDisplay();
	}
	
	/** == Création d'une modale ==
	 * prerequis : wp_enqueue_script( 'tify-modals' );
	 **/
	function tify_modal( $args = array() )
	{
		$modal = new tiFy\Components\Modal\Modal( 'modal', $args );
		return $modal->ModalDisplay();
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = PAGINATION * =/
	/** == Affichage de la pagination == **/
	function tify_pagination( $args = array() )
	{
		return tiFy\Components\Pagination\Pagination::display( $args );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = SEARCH = */
	/** == Récupération du numéro de post (deprecated) == **/
	function tify_search_post_num( $post = null )
	{
		return \tiFy\Components\Search\Search::PostNum( $post );
	}
	
	/** == Intitulé des sections de résultat de recherche == **/
	function tify_search_post_section( $post = null )
	{
		return \tiFy\Components\Search\Search::PostSection( $post );
	}
		
	/** == Intitulé des sections de résultat de recherche == **/
	function tify_search_section_label( $section = null )
	{
		return \tiFy\Components\Search\Search::SectionLabel();
	}
	
	/** == Nombre total de résultats == **/
	function tify_search_section_found_posts( $section = null )
	{
		return \tiFy\Components\Search\Search::SectionFoundPosts( $section );
	}
	
	/** == Nombre de résultat courant == **/
	function tify_search_section_post_count( $section = null  )
	{
		return \tiFy\Components\Search\Search::SectionPostCount( $section );
	}
	
	/** == Lien vers tous les resultats de recherche d'une section == **/
	function tify_search_section_showall_link( $section = null, $args = array() )
	{
		return \tiFy\Components\Search\Search::SectionShowAllLink( $section, $args );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = SIDEBAR = */
	/* = Ajout d'élément au panneau latéral = */
	function tify_sidebar_register( $id = null, $args = array() )
	{
		\tiFy\Components\Sidebar\Sidebar::Register( $id, $args );
	}
	
	/* = Affichage du panneau latéral = */
	function tify_sidebar_display()	
	{	
		\tiFy\Components\Sidebar\Sidebar::Display();
	}	
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = VIDEO = */
	/** == == **/
	function tify_video_embed( $attr, $echo = true )
	{
		global $tify_video;
	
		$output = $tify_video->embed( $attr );
	
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/** == Création d'un contrôleur d'affichage d'une modale
	 * prerequis : wp_enqueue_style( 'tify-video' ); wp_enqueue_script( 'tify-video' );
	 == **/
	function tify_video_toggle( $target, $args = array() )
	{
		$_target = 'tify_video-'. $target;
	
		// Traitement des arguments
		$defaults = array(
				// Arguments du lien
				'id' 			=> 'tify_video_toggle-'. $target,
				'class'			=> '',
				'href'			=> '',
				'text'			=> '',
				'link_title'	=> '',
				'link_attrs'	=> array(),
				'echo'			=> true,
	
				// Arguments de la modale
				'autoload'		=> true,		// Instanciation automatique de la modal
				'options'		=> array(
						'backdrop' 		=> true, 	// false | 'static'
						'keybord'		=> true,
						'show'			=> false
				),
				'animations'		=> 'fade',
				'attrs'				=> array(),
				'before' 			=> '',
				'after' 			=> '',
				'title'				=> '',
				'body'				=> '',
				'footer'			=> '',
				'header_button'		=> true,
				'backdrop_button' 	=> false,
	
				// Arguments de la video
				'attr'				=> array()
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
	
		$output  = "";
		$output .= "<a href=\"{$href}\"";
		$output .= " title=\"{$link_title}\"";
		$output .= " id=\"{$id}\" class=\"tify_video-toggle".( $class ? ' '.$class :'') ."\"";
		foreach( $link_attrs as $i => $j )
			$output .= " {$i}=\"{$j}\"";
		$output .= " data-toggle=\"tify_modal\" data-target=\"{$_target}\"";
		$output .= ">";
		$output .= $text;
		$output .= "</a>";
	
		if( $autoload )
			tify_video_modal( $target, compact( 'options', 'animations', 'attrs', 'before', 'after', 'title', 'body', 'footer', 'header_button', 'backdrop_button', 'attr' ) );
	
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/** == == **/
	function tify_video_modal( $target, $args = array() )
	{
		$defaults = array(
				// Arguments de la modale
				'options'		=> array(
						'backdrop' 		=> true, // false | 'static'
						'keybord'		=> true,
						'show'			=> true
				),
				'animations'		=> 'fade',
				'attrs'				=> array(),
				'before' 			=> '',
				'after' 			=> '',
				'title'				=> '',
				'body'				=> '',
				'footer'			=> '',
				'header_button'		=> true,
				'backdrop_button' 	=> false,
	
				// Arguments de la video
				'attr'				=> array()
		);
		$args = wp_parse_args( $args, $defaults );
	
		$tify_video_modal = new tiFy\Components\Video\Modal( $target, $args );
		add_action( 'wp_footer', array( $tify_video_modal, 'wp_footer' ) );
	}
}