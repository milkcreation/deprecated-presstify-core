<?php
/**
 * Usage :
	// Déclaration d'une relation type de post d'accroche <> archive
	add_action( 'tify_hook_for_archive_register', '[function]' );
	function [function](){
		tify_h4a_register( 
			array( 
				'[custom_post_type]',			// [hook_post_type] = page
 				// OU
 			 	'[custom_post_type]' => '[hook_post_type]', //
 				// OU
 			 	'[custom_post_type]' => array(
					'hook_post_type'	=> '[hook_post_type]',
					'hook_id'			=> 0,			// @todo Définition du hook_id
					'entity_type'		=> 'post',		// post (defaut) | @todo taxonomy	
					'display'			=> 'static', 	// static (defaut) | dynamic
					'taboox_auto'		=> true,
					'custom_column'		=> true
				)
			) 
		);
	}
 	// Résultat équivalent à page_for_post
*/
namespace tiFy\Components\HookForArchive;	

use tiFy\Environment\Component;

/** @Autoload */
final class HookForArchive extends Component
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'current_screen',
		'admin_bar_menu',
		'wp_nav_menu_objects',
		'wp_title',
		'post_type_archive_title',
		
		'tify_options_register_node'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'admin_bar_menu'		=> 80,
		'wp_title'				=> 10,
	);	
	// Nombre d'arguments autorisé lors de l'appel des actions
	protected $CallActionsArgsMap		= array(
		'wp_nav_menu_objects' 		=> 2,
		'wp_title'					=> 3,
		'post_type_archive_title'	=> 2
	);
	
	/** == CONFIGURATION == **/
	public static	$hooks 		= array();
	public static	$hook_ids	= array();
			
	// Mode Debug
	private $debug = false;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		if( is_array( self::getConfig() ) )
			self::register( self::getConfig() );
		
		do_action( 'tify_hook_for_archive_register' );
	
		// Réécriture d'url
		add_rewrite_tag( '%tify_hook_id%', '([0-9]{1,})' );
		
		// Contrôleurs		
		new PostDynamic( $this );
		new PostStatic;
				
		if( $this->debug )
			add_action( 'init', array( $this, 'debug' ) );	
	}
	
	/* = CONFIGURATION = */
	/** == Déclaration == **/
	final public static function register( $hooks ){
		$defaults = array(
			'hook_post_type'			=> 'page',
			'hook_id'					=> 0,
			'entity_type'				=> 'post',		// post (defaut) | taxonomy	
			'display'					=> 'static', 	// static (defaut) | dynamic | dynamic-multi
			'taboox_auto'				=> true,
			'custom_column'				=> true,
			'hook_post_type_supports'	=> array()
		);
		
		foreach( $hooks as $k => $v ) :
			if( is_string( $k ) ) :		
				if( is_string( $v ) ) :
					self::$hooks[$k] = wp_parse_args( array( 'hook_post_type' => $v  ), $defaults );
				elseif( is_array( $v ) ) :
					self::$hooks[$k] = wp_parse_args( $v, $defaults );
				endif;
			elseif( is_numeric( $k ) ) :
				self::$hooks[$v] = $defaults;
			endif;
		endforeach;
		
		foreach( self::$hooks as $k => &$v ) :			
			if( ! $v['hook_id'] ) :
				$v['hook_id'] = (int) get_option( $v['hook_post_type'] .'_for_'. $k, 0 );
				self::$hook_ids[$v['hook_id']] = $k;
			endif;
		endforeach;
	}
	
	/* = ACTIONS WORDPRESS = */
	/** == Affichage de l'écran courant == **/
	final public function current_screen( $current_screen )
	{
		foreach( (array) self::$hooks as $archive_post_type => $args ) :
			// Bypass
			if( $current_screen->id !== $args['hook_post_type'] ) :
				continue;
			endif;
			$label = get_post_type_object( $archive_post_type )->label;
				
			if ( isset( $_GET['post'] ) ) :
				$post_id = (int) $_GET['post'];
			elseif ( isset( $_POST['post_ID'] ) ) :
				$post_id = (int) $_POST['post_ID'];
			else :
				$post_id = 0;
			endif;	
			
			// Bypass
			if( ! $post_id  ) :
				continue;
			endif;
			if( $post_id !== $args['hook_id'] ) :
				continue;
			endif;
								
			$admin_notice = function () use( $label ) {
				echo 	"<div class=\"notice notice-warning inline\">\n".
						"\t<p>Vous êtes en train de modifier la page qui affiche vos \"{$label}\".</p>\n".
						"</div>";
			};
			add_action( 'edit_form_after_title', $admin_notice, 99 );
				
			if( ! empty( $args['hook_post_type_supports'] ) ) :
				global $_wp_post_type_features;
				foreach( $_wp_post_type_features[$args['hook_post_type']] as $feature => $features ) :
					if( isset( $args['hook_post_type_supports'][$feature] ) ) :
						if( $args['hook_post_type_supports'][$feature] ) :
							add_post_type_support( $args['hook_post_type'], $feature );
						else :
							remove_post_type_support( $args['hook_post_type'], $feature );
						endif;
					endif;
				endforeach;
			endif;
		endforeach;
	}
	
	/** == Barre d'administration == */
	final public function admin_bar_menu( $wp_admin_bar )
	{
		// Bypass
		if( is_admin() )
			return;
		if( ! is_post_type_archive() )
			return;
		if( ! $hook_id = get_query_var( 'tify_hook_id' ) )
			return;
	
		$post_type_object = get_post_type_object( get_post_type( $hook_id ) );
	
		// Ajout d'un lien de configuration du Diaporama
		$wp_admin_bar->add_node(
			array(
				'id' 	=> 'edit',
				'title' => $post_type_object->labels->edit_item,
				'href' 	=> get_edit_post_link( $hook_id )
			)
		);
	}
	
	/** == Modification du menu de navigation == **/
	final public function wp_nav_menu_objects( $sorted_menu_items, $args )
	{
		if( ! $hook_id = get_query_var( 'tify_hook_id' ) )
			return $sorted_menu_items;
	
		foreach( $sorted_menu_items as &$item ) :
			if( $item->object_id == $hook_id ) :
				$item->classes[] = 'current-menu-item';
				$item->classes[] = 'current_page_item';
			elseif( ! empty( $this->hooks[get_post_type( $hook_id )]['hook_id'] ) && ( $item->object_id == $this->hooks[get_post_type( $hook_id )]['hook_id'] ) ) :
				$item->classes[] = 'current-menu-ancestor';
				$item->classes[] = 'current-menu-parent';
				$item->classes[] = 'current_page_parent';
				$item->classes[] = 'current_page_ancestor';
			endif;
		endforeach;
	
		return $sorted_menu_items;
	}
	
	/** == Titre de réferencement == **/
	final public function wp_title( $title, $sep, $seplocation )
	{
		if( is_post_type_archive() && ( $hook_id = get_query_var( 'tify_hook_id' ) ) ) :
			$title = get_the_title( $hook_id ) . $title;
		endif;
				
		return $title;
	}
	
	/** == Titre de la page des archives == **/
	final public function post_type_archive_title( $archive_title, $archive_post_type )
	{
		//Bypass
		if( $hook_id = get_query_var( 'tify_hook_id' ) ) :
			$archive_title =  get_the_title( $hook_id );
		endif;
	
		return $archive_title;
	}
	
	/** == Initialisation de l'interface d'administration == **/
	final public function tify_options_register_node()
	{
		// Bypass
		if( empty( self::$hooks ) )
			return;
		
		tify_options_register_node(
			array(
				'id' 		=> 'tify_hookforarchive',
				'title' 	=> __( 'Affichage des archives', 'tify' ),
				'cb' 		=> 'tiFy\Components\HookForArchive\Taboox\Admin\Option\PostTypeForArchive'
			)
		);		
	}	
		
	/* = CONTROLEURS = */
	final public function getHooks()
	{
		return self::$hooks;
	}
	
	/** == Deboguage == **/
	final public function debug()
	{
		global $wp_rewrite;

		//var_dump( $wp_rewrite );
		var_dump( get_option( 'rewrite_rules') );
		exit;
	}	
}