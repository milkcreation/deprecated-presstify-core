<?php
namespace
{
	/* = BREADCRUMB = */
	/** == Affichage du fil d'Ariane == **/
	function tify_breadcrumb( $args = array() )
	{
		return tiFy\Components\Breadcrumb\Breadcrumb::display( $args );
	}
		
	/* = CONTACT FORM = */
	/** == Affichage du formulaire de contact == **/
	function tify_contact_form_display( $id, $content = '', $echo = true )
	{
		return tiFy\Components\ContactForm\ContactForm::Display( $id, $content, $echo );
	}
		
	/* = HOOKFORARCHIVE = */
	/** == Déclaration d'un type de post d'accroche pour des archives == **/
	function tify_h4a_register( $hooks = array() )
	{
		return tiFy\Components\HookForArchive\HookForArchive::register( $hooks );
	}
	
	/** == Récupération du hook_id selon un type d'archive == **/
	function tify_h4a_hook_id_by_archive( $archive_post_type )
	{
		if( isset( tiFy\Components\HookForArchive\HookForArchive::$hooks[$archive_post_type] ) )
			return tiFy\Components\HookForArchive\HookForArchive::$hooks[$archive_post_type]['hook_id'];
	}
	
	/** == Récupération d'un type d'archive selon son hook_id == **/
	function tify_h4a_archive_by_hook_id( $hook_id )
	{
		if( isset( tiFy\Components\HookForArchive\HookForArchive::$hook_ids[$hook_id] ) )
			return tiFy\Components\HookForArchive\HookForArchive::$hook_ids[$hook_id];
	}
	
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
	
	/* = PAGINATION * =/
	 /** == Affichage de la pagination == **/
	function tify_pagination( $args = array() )
	{
		return tiFy\Components\Pagination\Pagination::display( $args );
	}
	
	/* = VIDEO = */
	/** == == **/
	function tify_video_embed( $attr, $echo = true ){
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
	function tify_video_toggle( $target, $args = array() ){
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
	function tify_video_modal( $target, $args = array() ){
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