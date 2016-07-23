<?php
/*
Plugin Name: Forum
Plugin URI: http://presstify.com/theme-manager/addons/premium/forum
Description: Gestion de forum
Version: 0.151202
Author: Milkcreation
Author URI: http://profile.milkcreation.fr/jordy
*/

namespace tiFy\Plugins\Forum;

use tiFy\Environment\Plugin;

class Forum extends Plugin
{
	/* = ARGUMENTS = */
	public 	// Configuration	
			$dir, $uri,
			
			// Paramètres
			$is_multi,
			$hook_page_id,
			$roles,
			$menu_slug 	= array(),
			$hookname 	= array(),
					
			// Contrôleurs
			$admin,			
			$contributor,
			$contribution,
			$db,
			$forms,
			$handle,
			$multi,
			$options,
			$rewrite,
			$template,
			$topic;
					
	/* = CONSTRUCTEUR = */
	public function __construct(){
		global $tify_forum;		
		$tify_forum = $this; 
				
		// Configuration
		$this->is_multi 	= false;
		$this->roles 		= array( 
			'tify_forum_contributor' => array(
				'name' 					=> __( 'Contributeur de forum', 'tify' ),
				'capabilities'			=> array(),
				'show_admin_bar_front' 	=> false
			)
		);
		$this->menu_slug = array(
			'parent'		=> 'tify_forum',			
			'topic'			=> $this->is_multi ? 'tify_forum_topic' : 'tify_forum',
			'contribution'	=> 'tify_forum_contribution',			
			'options'		=> 'tify_forum_options',
			'contributor'	=> 'tify_forum_contributor'
		);
		
		// Contrôleurs		
		/// Interface d'administration
		require_once $this->dir .'/inc/admin.php';			
		$this->admin = new tiFy_Forum_AdminMain( $this );		
		/// Contributions
		require_once $this->dir .'/inc/contribution.php';
		$this->contribution = new tiFy_Forum_ContributionMain( $this );			
		/// Contributeurs
		require_once $this->dir .'/inc/contributor.php';	
		$this->contributor = new tiFy_Forum_ContributorMain( $this );		
		/// Base de données
		require_once $this->dir .'/inc/db.php';
		$this->db = new tiFy_Forum_DbMain( $this );				
		/// Formulaires
		require_once $this->dir .'/inc/forms.php';
		$this->forms = new tiFy_Forum_FormsMain( $this );			
		/// Traitement
		require_once $this->dir .'/inc/handle.php';			
		$this->handle = new tiFy_Forum_HandleMain( $this );
		/// Helpers
		require_once $this->dir .'/inc/helpers.php';			
		/// Forum Multiples
		/** @todo **/
		if( $this->is_multi ) :
			require_once $this->dir .'/inc/multi.php';
			$this->multi = new tiFy_Forum_MultiMain( $this );
		endif;		
		/// Options
		require_once $this->dir .'/inc/options.php';	
		$this->options = new tiFy_Forum_OptionsMain( $this );
		/// Réécriture
		require_once $this->dir .'/inc/rewrite.php';
		$this->rewrite = new tiFy_Forum_RewriteMain( $this );
		/// Gabarits
		require_once $this->dir .'/inc/general-template.php';
		$this->template = new tiFy_Forum_TemplateMain( $this );					
		/// Sujets de forum
		require_once $this->dir .'/inc/topic.php';
		$this->topic = new tiFy_Forum_TopicMain( $this );
			
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de la page d'accroche des forums == **/
	final public function hook_page_id(){
		if( ! $this->hook_page_id )
			return $this->hook_page_id = (int) get_option( 'page_for_tify_forum', 0 );
		else
			return $this->hook_page_id;
	}
	
	/** == Url de la page d'accroche des forums == **/
	final public function hook_page_permalink(){
		return esc_url( get_permalink( $this->hook_page_id() ) );
	}
}
New Forum;