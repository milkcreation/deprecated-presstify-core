<?php
/*
Plugin Name: SEO
Plugin URI: http://presstify.com/theme-manager/addons/seo
Description: Gestionnaire de référencement de site
Version: 1.150701
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/* = HELPER = */
/** == Récupération des données opengraph déclarée == **/
function tify_seo_opengraph_meta( $property = null ){
	global $tify_opengraph_meta;
	
	if( ! $property )
		return $tify_opengraph_meta;
	elseif( isset( $tify_opengraph_meta[$property] ) )
		return $tify_opengraph_meta[$property];
}

/* = CONTROLEURS = */
/** == Point d'entrée == **/
class tiFy_SEO{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri,
			
			// Configuration
			$options,
			$post_types = array(),
			
			$ua_code,
			$sep			 = "&nbsp;|&nbsp;",
			$append_blogname = true,
			$append_sitedesc = true;
		
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );

		require_once( $this->dir .'/Taboox/GoogleAnalytics.php' );	
		require_once( $this->dir .'/Taboox/MetaTag.php' );
		require_once( $this->dir .'/Taboox/OpenGraph.php' );
		
		// Actions et Filtres Wordpress
		add_action( 'after_setup_theme', array( $this, 'wp_after_setup_theme' ) );
		add_action( 'init', array( $this, 'wp_init' ), 99 );
		add_filter( 'language_attributes', array( $this, 'wp_language_attributes' ) );
		add_action( 'wp_head', array( $this, 'wp_head_first' ), 1 );		
		add_action( 'wp_head', array( $this, 'wp_head_opengraph' ), 5 );		
		add_action( 'wp_head', array( $this, 'wp_head_last' ), 99 );
		add_filter( 'wp_title', array( $this, 'wp_title' ), 10, 3 );
				
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );	
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );	
		
		// Actions et Filtres tiFy_seo
		add_action( 'tify_seo_wp_head', array( $this, 'meta_description' ), 10 );
		add_filter( 'tify_seo_title_is_page_on_front', array( $this, '_title_for_singular' ), 10, 3 );		
		add_filter( 'tify_seo_title_is_single', array( $this, '_title_for_singular' ), 10, 3 );
		add_filter( 'tify_seo_title_is_page', array( $this, '_title_for_singular' ), 10, 3 );		
		add_filter( 'tify_seo_title_is_page_for_posts', array( $this, '_title_for_archive' ), 10, 3 );
		add_filter( 'tify_seo_desc_is_page_on_front', array( $this, '_desc_for_singular' ), 10, 3 );
		add_filter( 'tify_seo_desc_is_single', array( $this, '_desc_for_singular' ), 10, 3 );
		add_filter( 'tify_seo_desc_is_page', array( $this, '_desc_for_singular' ), 10, 3 );			
	}
	
	/* = CONFIGURATION = */
	/** == Définition des options == **/
	function set_options(){
		$this->options = apply_filters( 'tify_seo_options', array( 'taboox_auto' => true ) );
	}
	/** == Définition des types de post == **/
	function set_post_types(){
		$defaults = array(
			'taboox_auto'	=> true,				// Déclaration automatique de la boîte de sasie
		);
		$post_types = apply_filters( 'tify_seo_post_types', array_diff( get_post_types(), array( 'attachment', 'revision', 'nav_menu_item' ) ) );
		
		foreach( $post_types as $k => $v )
			if( is_string( $v ) )
				$this->post_types[$v] = $defaults;
			elseif( is_array( $v ) )
				$this->post_types[$k] = wp_parse_args( $v, $defaults );
	}
	
	/* = CONTROLEURS = */
	/** == Récupération des types de posts == **/
	function get_post_types(){
		// Bypass
		if( ! is_array( $this->post_types ) )
			return array();
		
		return array_keys( $this->post_types );		
	}
	
	/** == Vérifie si le type de post est valide  == **/
	function is_post_type( $post_type ){
		return in_array( $post_type, $this->get_post_types() );
	}
	
	/** == Récupération d'une option de type de post  == **/
	function get_post_type_option( $post_type, $option ){
		// Bypass
		if( ! $this->is_post_type( $post_type ) )
			return;
		
		if( isset( $this->post_types[$post_type][$option] ) )
			return $this->post_types[$post_type][$option];
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation du thème == **/
	function wp_after_setup_theme(){
		$this->ua_code	= get_option( 'tify_google_analytics_ua_code', false );
		
		$this->set_options();		
	}
	
	/** == Initialisation globale == **/
	function wp_init(){
		$this->set_post_types();
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
		remove_action( 'wp_head', 'wp_dlmp_l10n_style' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );	
	}
	
	/** == == **/
	function wp_language_attributes( $output ){
		if( is_admin() )
			return $output;
		return $output . ' xmlns:og="http://opengraphprotocol.org/schema/"';
	}
		
	/** == == **/
	function wp_head_first(){	
		do_action( 'tify_seo_wp_head' );
	}
	
	/** == Balises Meta de l'Opengraph == **/
	function wp_head_opengraph( $output ){
		$tify_opengraph = get_option( 'tify_opengraph' );

		if( ! isset( $tify_opengraph['active'] ) || ( $tify_opengraph['active'] !== 'on' ) )
			return $output;
		
		global $tify_opengraph_meta;
		
		$meta = array();
		
		if( is_front_page() ) :
			$page_on_front = false;
			if( $page_on_front =  get_option( 'page_on_front' ) )
				$post = get_post( $page_on_front );
				
			$meta['title'] 			= ( $page_on_front ) ? get_the_title( $post->ID ) : get_bloginfo('name');
			$meta['site_name']		= get_bloginfo('name') .' | '. get_bloginfo('description');
			$meta['url']			= home_url();
			$meta['description']	= ( $page_on_front && $post->post_excerpt ) ? apply_filters( 'get_the_excerpt', $post->post_excerpt ) : get_bloginfo('description');
				
			if( $image = tify_custom_attachment_image( $tify_opengraph['default_image'], array( 1200, 1200, false ) ) )
				$meta['image'] = esc_attr( $image['url'] .'/'. $image['file'] );
			elseif( $image = tify_custom_attachment_image( $tify_opengraph['default_image'], array( 600, 600, false ) ) )
				$meta['image'] = esc_attr( $image['url'] .'/'. $image['file'] );
			
			$meta['type'] 			= 'website';
			
		elseif( is_singular() ) :
			$meta['title'] 			= get_the_title();
			$meta['site_name']		= get_bloginfo('name');
			$meta['url']			= get_permalink();
			$meta['description']	= esc_attr( strip_tags( get_the_excerpt() ) );
			
			if( $image = tify_custom_attachment_image( get_post_thumbnail_id( get_the_ID() ), array( 1200, 1200, false ) ) ) :
				$meta['image'] = esc_attr( $image['url'] .'/'. $image['file'] );
			elseif( $image = tify_custom_attachment_image( get_post_thumbnail_id( get_the_ID() ), array( 600, 600, false ) ) ) :
				$meta['image'] = esc_attr( $image['url'] .'/'. $image['file'] );
			elseif( $image = tify_custom_attachment_image( $tify_opengraph['default_image'], array( 1200, 1200, true ) ) ) :
				$meta['image'] = esc_attr( $image['url'] .'/'. $image['file'] );
			elseif( $image = tify_custom_attachment_image( $tify_opengraph['default_image'], array( 600, 600, true ) ) ) :
				$meta['image'] = esc_attr( $image['url'] .'/'. $image['file'] );
			endif;
			
			$meta['type'] 			= 'article';
		endif;
		
		// Court-circuitage des metas		
		$meta = apply_filters( 'tify_seo_opengraph_meta', $meta );
		$tify_opengraph_meta = $meta;
		
		// Conversion des metas pour l'affichage
		foreach( $meta as $property => $content )
			$output .= "<meta property=\"og:{$property}\" content=\"{$content}\"/>";		
		
		echo apply_filters( 'tify_seo_opengraph_meta_output', $output, $meta );
	}

	/** == == **/
	function wp_head_last(){		
		// Bypass
		if( ! $this->ua_code )
			return;
	?><script type="text/javascript">/* <![CDATA[ */(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '<?php echo $this->ua_code;?>', 'auto');ga('send', 'pageview');/* ]]> */</script><?php
	}
	
	/** == Balise Titre des pages du site == **/
	function wp_title( $title, $sep, $seplocation ){
		if ( is_feed() )
			return $title;
		
		// PAGE 404
		if ( is_404() ) :
			$title = apply_filters( 'tify_seo_title_is_404', __( 'Erreur 404 - Impossible de trouver la page', 'tify' ), $sep, $seplocation );
		// RECHERCHE
		elseif ( is_search() ) : 
			$title = apply_filters( 'tify_seo_title_is_search', sprintf( '%1$s %2$s', __( 'Recherche de' , 'tify' ), get_search_query() ), $sep, $seplocation );
		// TAXONOMIES
		elseif ( is_tax() ):
			$tax = get_queried_object();			
			$title = apply_filters( 'tify_seo_title_is_tax',  sprintf( '%1$s %2$s %3$s', get_taxonomy( $tax->taxonomy )->label, $sep, $tax->name ), $sep, $seplocation );
		// FRONT PAGE
		elseif ( is_front_page() ) :
			if( $page_for_posts = get_option( 'page_on_front' ) ) :
				$_title = esc_html( wp_strip_all_tags( get_the_title( $page_for_posts ) ) );
				$title = apply_filters( 'tify_seo_title_is_page_on_front', $_title, $sep, $seplocation );
				$this->append_blogname = ( $_title === $title ) ? $this->append_blogname : false;
				$this->append_sitedesc = ( $_title === $title ) ? $this->append_sitedesc : false;
			else :
				if( is_paged() ) :
					global $wp_query;
					$title = apply_filters( 'tify_seo_title_is_front_paged', sprintf( __( 'Actualités page %1$s sur %2$s', 'tify' ), ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ), $wp_query->max_num_pages ) , $sep, $seplocation );
				else :
					$title = apply_filters( 'tify_seo_title_is_front_page', __( 'Actualités', 'tify' ), $sep, $seplocation );
				endif;
			endif;
		// HOME PAGE	
		elseif ( is_home() ) :			
			if( $page_for_posts = get_option( 'page_for_posts' ) ) :
				$title = apply_filters( 'tify_seo_title_is_page_for_posts', esc_html( wp_strip_all_tags( get_the_title( $page_for_posts ) ) ), $sep, $seplocation );
			else :			
				if( is_paged() ) :
					global $wp_query;
					$title = apply_filters( 'tify_seo_title_is_home_paged', sprintf( __( 'Actualités page %1$s sur %2$s', 'tify'),( get_query_var('paged') ? get_query_var('paged') : 1 ), $wp_query->max_num_pages ) , $sep, $seplocation );
				else :
					$title = apply_filters( 'tify_seo_title_is_home', __( 'Actualités', 'tify' ), $sep, $seplocation );
				endif;
			endif;
		// ATTACHMENT
		elseif ( is_attachment() ) :
			$title = apply_filters( 'tify_seo_title_is_attachment', esc_html( wp_strip_all_tags( get_the_title( ) ) ), $sep, $seplocation );
		// SINGLE
		elseif ( is_single() ) :
			$_title = esc_html( wp_strip_all_tags( get_the_title() ) );		
			$title = apply_filters( 'tify_seo_title_is_single', $_title, $sep, $seplocation );	
			$this->append_blogname = ( $_title === $title ) ? $this->append_blogname : false;
			$this->append_sitedesc = ( $_title === $title ) ? $this->append_sitedesc : false;
		// PAGE
		elseif ( is_page() ) :
			$_title = esc_html( wp_strip_all_tags( get_the_title() ) );	
			$title = apply_filters( 'tify_seo_title_is_page', $_title, $sep, $seplocation );
			$this->append_blogname = ( $_title === $title ) ? $this->append_blogname : false;
			$this->append_sitedesc = ( $_title === $title ) ? $this->append_sitedesc : false;		
		// CATEGORY
		elseif( is_category() ) :
			if( $category = get_category( get_query_var('cat'), false ) ):
				$title = apply_filters( 'tify_seo_title_is_category', $category->name, $sep, $seplocation );
			endif;		
		// TAG
		elseif ( is_tag() ):
			$title = apply_filters( 'tify_seo_title_is_tag', sprintf( __( 'Mot clef : %1$s', 'tify' ), get_query_var('tag') ), $sep, $seplocation );
		// AUTHOR
		elseif ( is_author() ):
		// DATE
		elseif ( is_date() ) :
			if ( is_day() ) :
				$title = apply_filters( 'tify_seo_title_is_day', sprintf( __( 'Archive quotidienne : %1$s', 'tify' ), get_the_date() ), $sep, $seplocation );
			elseif ( is_month() ) :  
				$title = apply_filters( 'tify_seo_title_is_month', sprintf( __( 'Archive mensuelle : %1$s', 'tify' ), get_the_date( 'F Y' ) ), $sep, $seplocation );
			elseif ( is_year() ) :
				$title = apply_filters( 'tify_seo_title_is_year', sprintf( __( 'Archive annuelle : %1$s', 'tify' ), get_the_date( 'Y' )  ), $sep, $seplocation );
			endif;
		// ARCHIVES
		elseif ( is_archive() )	:		
			if( is_post_type_archive() ) :
				$title = apply_filters( 'tify_seo_title_post_type_archive', post_type_archive_title( '', false ), $sep, $seplocation );
			else:
				$title = __( 'Archives', 'tify' ); 
			endif;		
		//** TODO **/
		elseif ( is_comments_popup() ) :
		elseif ( is_paged() ) :
		else :
		endif;		

		return $title . ( $this->append_blogname ? $this->sep . get_bloginfo( 'name' ) : '' ) . ( $this->append_sitedesc ? $this->sep . get_bloginfo( 'description', 'display' ) : '' );
	}
	
	/* = ACTIONS ET FILTRES PressTiFy = */
	/** == Déclaration des taboox == **/
	function tify_taboox_register_node(){
		foreach( (array) $this->post_types as $post_type => $args )
			if( $args['taboox_auto'] )
				tify_taboox_register_node( 
					$post_type, 
					array( 
						'id' 		=> 'tify_seo_postmetatag',
						'title' 	=> __( 'Référencement', 'tify' ), 
						'cb' 		=> 'tiFy_SEO_PostMetaTag_Taboox' 
					) 
				);
	}
	
	public function tify_options_register_node(){	
		if( $this->options['taboox_auto'] ) :
			tify_options_register_node(
				array(
					'id' 		=> 'tify_seo_options',
					'title' 	=> __( 'Référencement', 'tify' ),
				)
			);
			tify_options_register_node(
				array(
					'parent'	=> 'tify_seo_options',
					'id' 		=> 'tify_seo_opengraph',
					'title' 	=> __( 'Metadonnées de l\'Opengraph', 'tify' ),
					'cb' 		=> 'tiFy_SEO_OpenGraph_Taboox',
					'order'		=> 1
				)
			);
			tify_options_register_node(
				array(
					'parent'	=> 'tify_seo_options',
					'id' 		=> 'tify_seo_google-analytics',
					'title' 	=> __( 'Google Analytics', 'tify' ),
					'cb' 		=> 'tiFy_SEO_GoogleAnalytics_Taboox',
					'order'		=> 2
				)
			);
		endif;	
	}
	
	/** == Déclaration des taboox == **/
	function tify_taboox_register_form(){		
		tify_taboox_register_form( 'tiFy_SEO_OpenGraph_Taboox' );
		tify_taboox_register_form( 'tiFy_SEO_GoogleAnalytics_Taboox' );
		tify_taboox_register_form( 'tiFy_SEO_PostMetaTag_Taboox', $this );
	}
	
	/* = ACTIONS ET FILTRES TiFy_SEO = */
	/** == Balise meta description des pages du site == **/
	function meta_description(){
		$desc = '';
		// PAGE 404
		if ( is_404() ) :
			$desc = apply_filters( 'tify_seo_desc_is_404', __( 'Erreur 404 - Impossible de trouver la page', 'tify' ) );
		// RECHERCHE
		elseif ( is_search() ) : 
			$desc = apply_filters( 'tify_seo_desc_is_search', sprintf( '%1$s %2$s', __( 'Recherche de' , 'tify' ), get_search_query() ) );
		// TAXONOMIES
		elseif ( is_tax() ):
			$tax = get_queried_object();			
			$desc = apply_filters( 'tify_seo_desc_is_tax',  sprintf( '%1$s %2$s', get_taxonomy( $tax->taxonomy )->label, $tax->name ) );
		// FRONT PAGE
		elseif ( is_front_page() ) :
			if( $page_for_posts = get_option( 'page_on_front' ) ) :
				$desc = apply_filters( 'tify_seo_desc_is_page_on_front', $this->get_singular_desc( $page_for_posts ) );
			else :
				if( is_paged() ) :
					global $wp_query;
					$desc = apply_filters( 'tify_seo_desc_is_front_paged', sprintf( __( 'Actualités page %1$s sur %2$s', 'tify' ), ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ), $wp_query->max_num_pages )  );
				else :
					$desc = apply_filters( 'tify_seo_desc_is_front_page', __( 'Actualités', 'tify' ) );
				endif;
			endif;
		// HOME PAGE	
		elseif ( is_home() ) :			
			if( $page_for_posts = get_option( 'page_for_posts' ) ) :
				$desc = apply_filters( 'tify_seo_desc_is_page_for_posts', $this->get_singular_desc( $page_for_posts ) );
			else :			
				if( is_paged() ) :
					global $wp_query;
					$desc = apply_filters( 'tify_seo_desc_is_home_paged', sprintf( __( 'Actualités page %1$s sur %2$s', 'tify'),( get_query_var('paged') ? get_query_var('paged') : 1 ), $wp_query->max_num_pages )  );
				else :
					$desc = apply_filters( 'tify_seo_desc_is_home', __( 'Actualités', 'tify' ) );
				endif;
			endif;
		// ATTACHMENT
		elseif ( is_attachment() ) :
			$desc = apply_filters( 'tify_seo_desc_is_attachment', esc_html( wp_strip_all_tags( get_the_title( ) ) ) );
		// SINGLE
		elseif ( is_single() ) :		
			$desc = apply_filters( 'tify_seo_desc_is_single',  $this->get_singular_desc( get_the_ID() ) );		
		// PAGE
		elseif ( is_page() ) :
			$desc = apply_filters( 'tify_seo_desc_is_page', $this->get_singular_desc( get_the_ID() ) );		
		// CATEGORY
		elseif( is_category() ) :
			if( $category = get_category( get_query_var('cat'), false ) ):
				$desc = apply_filters( 'tify_seo_desc_is_category', $category->name );
			endif;		
		// TAG
		elseif ( is_tag() ):
			$desc = apply_filters( 'tify_seo_desc_is_tag', sprintf( __( 'Mot clef : %1$s', 'tify' ), get_query_var('tag') ) );
		// AUTHOR
		elseif ( is_author() ):
		// DATE
		elseif ( is_date() ) :
			if ( is_day() ) :
				$desc = apply_filters( 'tify_seo_desc_is_day', sprintf( __( 'Archive quotidienne : %1$s', 'tify' ), get_the_date() ) );
			elseif ( is_month() ) :  
				$desc = apply_filters( 'tify_seo_desc_is_month', sprintf( __( 'Archive mensuelle : %1$s', 'tify' ), get_the_date( 'F Y' ) ) );
			elseif ( is_year() ) :
				$desc = apply_filters( 'tify_seo_desc_is_year', sprintf( __( 'Archive annuelle : %1$s', 'tify' ), get_the_date( 'Y' )  ) );
			endif;
		// ARCHIVES
		elseif ( is_archive() )	:		
			if( is_post_type_archive() ) :
				$desc = apply_filters( 'tify_seo_desc_post_type_archive', post_type_archive_title( '', false ) );
			else:
				$desc = __( 'Archives', 'tify' ); 
			endif;		
		//** TODO **/
		elseif ( is_comments_popup() ) :
		elseif ( is_paged() ) :
		else :
		endif;
		
		echo "<meta name=\"description\" content=\"". esc_attr( strip_tags( stripslashes( $desc ) ) ) ."\"/>"; 
	}
	
	function get_singular_desc( $post_id ){
		// Bypass
		if( ! $post = get_post( $post_id ) )
			return;
		/// Description
		$desc = get_bloginfo( 'name' ) .'&nbsp;|&nbsp;'. get_bloginfo( 'description' );
		if( $post->post_excerpt )
			$desc = tify_excerpt( strip_tags( $post->post_excerpt ), array( 'max' => 156 ) );
		elseif( $post->post_content )
			$desc = tify_excerpt( strip_tags( $post->post_content ), array( 'max' => 156 ) );
		
		return esc_html( wp_strip_all_tags( $desc ) );
	}
	
	/** == == **/
	function _title_for_singular( $title ){
		global $post;
		
		if( ( $seo_meta = get_post_meta( $post->ID, '_seo_meta', true ) ) && ! empty( $seo_meta['title'] ) )
			return $seo_meta['title'];
			
		return $title;		
	}
	/** == == **/
	function _title_for_archive( $title ){
		global $post;

		if( is_home() && ( $page_for_posts = get_option( 'page_for_posts' ) ) && ( $seo_meta = get_post_meta( $page_for_posts, '_seo_meta', true ) ) && ! empty( $seo_meta['title'] ) )
			return $seo_meta['title'];
		
		return $title;		
	}
	/** == == **/
	function _desc_for_singular( $desc ){
		global $post;
		
		if( ( $seo_meta = get_post_meta( $post->ID, '_seo_meta', true ) ) && ! empty( $seo_meta['desc'] ) )
			return $seo_meta['desc'];
			
		return $desc;		
	}
	/** == == **/
	function _desc_for_archive( $desc ){
		global $post;

		if( is_home() && ( $page_for_posts = get_option( 'page_for_posts' ) ) && ( $seo_meta = get_post_meta( $page_for_posts, '_seo_meta', true ) ) && ! empty( $seo_meta['desc'] ) )
			return $seo_meta['desc'];
		
		return $desc;		
	}
	
	/**
	 * @TODO SCHEMA.ORG
	 */
	/**
	 * Langage Attribute de la balise HTML pour Schema.org
	 * @TODO
	 */
	function schema_language_attributes( $output ){
		if( is_admin() )
			return $output;
		return $output . ' itemscope itemtype="http://schema.org/Article"';
	}
	
	/**
	 * Balises Meta de Schema.org
	 * @TODO
	 */
	function schema_wp_head(){
		if ( ! is_singular() && ! is_front_page() )
			return;
	
		if( is_front_page() ) :
		$page_on_front = false;
		if( $page_on_front =  get_option( 'page_on_front' ) )
			$post = get_post( $page_on_front );
			
		if( $page_on_front )
			echo '<meta itemprop="name" content="' . get_the_title( $post->ID ) .'"/>';
		else
			echo '<meta itemprop="name" content="' . get_bloginfo('name') .'"/>';
			
		if( $page_on_front && $post->post_excerpt )
			echo '<meta itemprop="description" content="'. apply_filters( 'get_the_excerpt', $post->post_excerpt ) .'" />';
		else
			echo '<meta itemprop="description" content="'. get_bloginfo('description') .'" />';
	
		else :
		echo '<meta itemprop="name" content="' . get_the_title() . '"/>';
	
		echo '<meta itemprop="description" content="' .strip_tags( get_the_excerpt() ) . '" />';
		if( $src = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' ) ) :
		echo '<meta itemprop="image" content="' . esc_attr( $src[0] ) . '"/>';
		elseif(  $src = $this->get_image_src( $this->options['default_image'] ) ) :
		echo '<meta itemprop="image" content="' . esc_attr( $src ) . '"/>';
		endif;
		endif;
	}
}
new tiFy_SEO;