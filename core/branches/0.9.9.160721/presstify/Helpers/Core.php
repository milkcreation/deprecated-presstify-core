<?php
namespace
{
	// --------------------------------------------------------------------------------------------------------------------------
	/* = CHEMINS (Deprecated) = */
	/** == Récupération du répertoire de PressTiFy == **/
	function tify_get_directory()
	{
		global $tiFy;

		return $tiFy->get_directory();
	}

	/** == Récupération du répertoire de PressTiFy == **/
	function tify_get_directory_uri()
	{
		global $tiFy;

		return $tiFy->get_directory_uri();
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = ADMIN = */
	/** == Déclaration == **/
	function tify_admin_register( $id, $args = array() )
	{
		return tiFy\Core\Admin\Admin::Register( $id, $args );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = CONTROL = */
	/** == Mise en file des scripts du contrôleur == **/
	function tify_control_enqueue( $scripts )
	{
		if( is_string( $scripts ) )
			$scripts = array( $scripts );
	
		foreach( $scripts as $script ) :
			if( isset( tiFy\Core\Control\Control::$Factories[$script] ) ) :
				tiFy\Core\Control\Control::$Factories[$script]->enqueue_scripts();
			endif;
		endforeach;
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = CUSTOM TYPE = */
	/** == Déclaration d'une taxonomie personnalisée == **/
	function tify_custom_taxonomy_register( $taxonomy, $args )
	{
		tiFy\Core\CustomType\CustomType::RegisterTaxonomy( $taxonomy, $args );
	}
	
	/** == Déclaration d'un type de post personnalisé == **/
	function tify_custom_post_type_register( $post_type, $args )
	{
		tiFy\Core\CustomType\CustomType::RegisterPostType( $post_type, $args );
	}
	
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = DB = */
	/** == Déclaration == **/
	function tify_db_register( $id, $args = array() )
	{
		return tiFy\Core\Db\Db::Register( $id, $args );
	}
	
	/** == Déclaration == **/
	function tify_db_get( $id )
	{
		return tiFy\Core\Db\Db::Get( $id );
	}
	
	/** == Boucle == **/
	/*** === Initialisation === ***/
	function tify_query( $id, $query = null )
	{
		if( $db =  tiFy\Core\Db\Db::Get( $id ) )
			return $db->query( $query );
	}
	
	/*** === Récupération d'un champs == **/
	function  tify_query_field( $name )
	{
		if( $query =  tiFy\Core\Db\Db::$Query )
			return $query->get_field( $name );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = FILE UPLOAD = */
	/** == Déclaration d'un fichier à télécharger == **/
	function tify_upload_register( $file )
	{
		return tiFy\Core\Upload\Upload::Register( $file );
	}
	
	/** == Récupération du fichier à télécharger == **/
	function tify_upload_get( $type = null )
	{
		return tiFy\Core\Upload\Upload::Get( $type );
	}
	
	/** == Url de téléchargement d'un fichier == **/
	function tify_upload_url( $file, $query_vars = array() )
	{
		return tiFy\Core\Upload\Upload::Url( $file, $query_vars );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = FORMS = */
	/** == Affichage d'un formulaire == **/
	function tify_form_display( $form = null, $echo = true )
	{
		if( $echo )
			echo 	do_shortcode( '[formulaire id="'. $form .'"]' );
		else
			return 	do_shortcode( '[formulaire id="'. $form .'"]' );
	}
	
	/** == Déclaration d'un formulaire == **/
	function tify_form_register( $form = array() )
	{
		global $tify_forms;
	
		return $tify_forms->register_form( $form );
	}
	
	/** == Déclaration d'un addon == **/
	function tify_form_register_addon( $id, $callback, $filename = null, $args = array() )
	{
		global $tify_forms;
	
		return $tify_forms->addons->register( $id, $callback, $filename, $args );
	}
	
	/** == Shortcode d'affichage de formulaire == **/
	add_shortcode( 'formulaire', 'tify_form_shortcode' );
	function tify_form_shortcode( $atts = array() )
	{
		global $tify_forms;
	
		extract(
				shortcode_atts(
						array( 'id' => null ),
						$atts
						)
				);
	
		return $tify_forms->display( $id, false );
	}
	
	/** == Définition du formulaire courant == **/
	function tify_form_set_current( $form_id )
	{
		global $tify_forms;
	
		return $tify_forms->forms->set_current( $form_id );
	}
	
	/** == Récupération du formulaire courant == **/
	function tify_form_get_current()
	{
		global $tify_forms;
	
		return $tify_forms->forms->get_current();
	}
	
	// --------------------------------------------------------------------------------------------------------------------------	
	/* = META = */
	/** == POST == **/
	/** == Déclaration d'une metadonnée de post == **/
	function tify_meta_post_register( $post_type, $meta_key, $single = false, $sanitize_callback = '' )
	{
		return tiFy\Core\Meta\Post::Register( $post_type, $meta_key, $single, $sanitize_callback );	
	}
	
	/** == Récupération de métadonnée en mode avancée (gestion de l'ordre) == **/
	function tify_meta_post_get( $post_id, $meta_key )
	{
		return tiFy\Core\Meta\Post::Get( $post_id, $meta_key );	
	}
	
	/** == TERM == **/
	/** == Déclaration d'une metadonnée de post == **/
	function tify_meta_term_register( $taxonomy, $meta_key, $single = false, $sanitize_callback = '' )
	{
		return tiFy\Core\Meta\Term::Register( $taxonomy, $meta_key, $single, $sanitize_callback );	
	}
	
	/** == Récupération de métadonnée en mode avancée (gestion de l'ordre) == **/
	function tify_meta_term_get( $term_id, $meta_key )
	{
		return tiFy\Core\Meta\Term::Get( $term_id, $meta_key );	
	}
	
	/** == USER == **/
	/** == Déclaration d'une metadonnée de post == **/
	function tify_meta_user_register( $meta_key, $single = false, $sanitize_callback = '' )
	{
		return tiFy\Core\Meta\User::Register( $meta_key, $single, $sanitize_callback );	
	}
	
	/** == Récupération de métadonnée en mode avancée (gestion de l'ordre) == **/
	function tify_meta_user_get( $user_id, $meta_key )
	{
		return tiFy\Core\Meta\User::Get( $user_id, $meta_key );	
	}
	
	/** == Déclaration d'une metadonnée de post == **/
	function tify_option_user_register( $meta_key, $single = false, $sanitize_callback = '' )
	{
		return tiFy\Core\Meta\UserOption::Register( $meta_key, $single, $sanitize_callback );	
	}
	
	/** == Récupération de métadonnée en mode avancée (gestion de l'ordre) == **/
	function tify_option_user_get( $user_id, $meta_key )
	{
		return tiFy\Core\Meta\UserOption::Get( $user_id, $meta_key );	
	}
	
	// --------------------------------------------------------------------------------------------------------------------------	
	/* = OPTIONS = */
	/** == Déclaration d'une section de boîte à onglets dans l'interface de gestion des options de PresstiFy  == **/
	function tify_options_register_node( $node = array() )
	{
		return tiFy\Core\Options\Options::registerNode( $node );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = SCRIPT LOADER = */		
	/** == Déclaration / Modification d'un script JavaScript == **/
 	function tify_register_script( $handle, $args = array() )
 	{
	 	return tiFy\Core\ScriptLoader\ScriptLoader::register_script( $handle, $args );
	}
	
	/** == Déclaration / Modification d'un script JavaScript == **/
	function tify_register_style( $handle, $args = array() )
	{
	 	return tiFy\Core\ScriptLoader\ScriptLoader::register_style( $handle, $args );
	}
	 
	/** == Récupération de la source d'un script JS == **/
	function tify_script_get_src( $handle, $context = null )
	{
		return tiFy\Core\ScriptLoader\ScriptLoader::get_src( $handle, 'js', $context );
	}
	 
	/** == Récupération de la source d'une feuille de style CSS == **/
	function tify_style_get_src( $handle, $context = null )
	{
		return tiFy\Core\ScriptLoader\ScriptLoader::get_src( $handle, 'css', $context );
	}
	 
	/** == Récupération de l'attribut d'un script JS == **/
	function tify_script_get_attr( $handle, $attr = 'version' )
	{
		return tiFy\Core\ScriptLoader\ScriptLoader::get_attr( $handle, 'js', $attr );
	}
	 
	/** == Récupération de l'attribut d'une feuille de style CSS == **/
	function tify_style_get_attr( $handle, $attr = 'version' )
	{
		return tiFy\Core\ScriptLoader\ScriptLoader::get_attr( $handle, 'css', $attr );
	}
	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = TABOOX = */	
	/** == Déclaration d'une boîte à onglets ==	**/
	function tify_taboox_register_box( $hookname, $env, $args = array() )
	{
		return tiFy\Core\Taboox\Taboox::registerBox( $hookname, $env, $args );
	}
	
	/** == Déclaration d'une section de boîte à onglets == **/
	function tify_taboox_register_node( $hookname, $args = array() )
	{
		return tiFy\Core\Taboox\Taboox::registerNode( $hookname, $args );
	}
		
	/** == Affichage de la boîte à onglet de l'écran courant == **/
	function tify_taboox_display()
	{
		if( $screen = tiFy\Core\Taboox\Taboox::$Screen )
			return call_user_func_array( array( $screen, 'box_render' ), func_get_args() );
		var_dump( tiFy\Core\Taboox\Taboox::$Screen );
	}
}