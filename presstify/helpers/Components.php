<?php
namespace
{
	/* = GLOBAL = */
	/** == Déclaration dynamique d'un composant == **/
	function tify_component_register( $component )
	{
		add_action( "tify_component_register", function() use ( $component ){
			return tiFy\Components\Autoload::Register( $component );
		});
	}
		
	/* = ARCHIVE FILTERS = */
	/** == Test d'existance de filtres pour la vue courante == **/
	function tify_archive_filters_has()
	{
		return tiFy\Components\ArchiveFilters\ArchiveFilters::Has();
	}
	
	/** == Affichage des filtres == **/
	function tify_archive_filters_display( $echo = true )
	{
		return tiFy\Components\ArchiveFilters\ArchiveFilters::Display( $echo );
	}
	/*** === Déclaration d'un filtre === ***/
	function tify_archive_filters_register( $obj_type = 'post', $obj = 'post_type', $args = array() )
	{
		$action = function( $class ) use( $obj_type, $obj, $args ) {
			$class::Register( $obj_type, $obj, $args );
		};

		add_action( "tify_archive_filters_register", $action );
	}
	
	/* = BREADCRUMB = */
	/** == Affichage du fil d'Ariane == **/
	function tify_breadcrumb( $args = array(), $echo = true )
	{
		return tiFy\Components\Breadcrumb\Breadcrumb::display( $args, $echo );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = CONTACT FORM = */
	/** == Déclaration d'un formulaire de contact == **/
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
	/* = CUSTOM COLUMN = */
	/** == Déclaration d'une colonne personnalisée == **/
	function tify_custom_columns_register( $cb, $args = array(), $env, $type )
	{
		return tiFy\Components\CustomColumns\CustomColumns::Register( $cb, $args, $env, $type );
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
	function tify_permalink_register( $key, $attrs = array(), $obj_type = null, $obj = 'post_type' )
	{
		$action = function( $class ) use( $key, $attrs ) {
			$class::Register( $key, $attrs );
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
	
	function tify_hookarchive_get_permalink_structure( $type )
	{
		return tiFy\Components\HookArchive\HookArchive::getPermalinkStructure( $type );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = LOGIN = */
	/** == Affichage d'un élément de template == **/
	function tify_login_init( $id, $config = array() )
	{
		return new tiFy\Components\Login\Factory( $id, $config );
	}
	
	/** == Affichage d'un élément de template == **/
	function tify_login_display( $id, $args = array(), $echo = true )
	{
		return tiFy\Components\Login\Login::display( $id, 'display', $args, $echo );
	}
	
	/** == Affichage du formulaire d'authentification == **/
	function tify_login_form( $id, $args = array() )
	{
		return tiFy\Components\Login\Login::display( $id, 'form', $args );
	}
	
	/** == Affichage des erreurs de traitement de formulaire == **/
	function tify_login_errors( $id, $args = array() )
	{
		return tiFy\Components\Login\Login::display( $id, 'errors', $args );
	}
	
	/** == Affichage des erreurs de traitement de formulaire == **/
	function tify_login_logout_link( $id, $args = array() )
	{
		return tiFy\Components\Login\Login::display( $id, 'logout_link', $args );
	}
		
	// --------------------------------------------------------------------------------------------------------------------------
	/* = PAGINATION * =/
	/** == Affichage de la pagination == **/
	function tify_pagination( $args = array(), $echo = true )
	{
		return tiFy\Components\Pagination\Pagination::display( $args, $echo );
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
}