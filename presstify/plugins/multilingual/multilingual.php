<?php
/*
Plugin Name: Multilingual
Plugin URI: http://presstify.com/multilingual
Description: Gestion de site multilingue basée sur Wordpress multisite
Version: 1.141127
Author: Milkcreation
Author URI: http://milkcreation.fr
Text Domain: tify_multilingual
*/

class tiFy_Multilingual{
	/* = ARGUMENTS = */
	public	// Configuration
			$sites,
			$flags;
	
	/* = CONTRUCTEUR = */
	function __construct(){
		if( ! is_multisite() )
			return;
		
		// Contrôleur
		/// Définition des traduction de locales
		if( ! function_exists( 'wp_get_available_translations' ) )
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$this->languages = get_available_languages();
		$this->translations = wp_get_available_translations();
		$this->translations['en_US'] = array(
			'language' 		=> 'en_US',
			'english_name' 	=> 'English (United States)',
			'native_name'  	=> 'English (United States)',
			'iso'			=> array( 1 => 'en' )
		);
		/// Récupération de la liste des sites
		$this->sites = wp_get_sites( );
		/// Récupération des drapeaux par pays de site
		$this->get_flags( );
		
		// Actions et Filtres Wordpress
		add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ) );
		add_action( 'admin_print_styles', array( $this, 'wp_print_styles' ) );
		add_action( 'admin_bar_menu', array( $this, 'wp_admin_bar_menu' ), 99 );		
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Styles de la barre d'administration == **/
	function wp_print_styles(){
	?><style type="text/css">
		#wp-admin-bar-my-sites .tify_multilingual-flag{
			width:30px;
			height:18px;
			vertical-align:middle;
			margin-right:5px;
		}
	</style><?php
	}
	
	/** == Personnalisation de la barre d'administration ==  
 	 * @see http://fr.wikipedia.org/wiki/ISO_3166-1
 	 * @see http://wpcentral.io/internationalization/
 	 */
	function wp_admin_bar_menu( $wp_admin_bar ){		
		foreach( $this->sites as $site ) :
			$blog_id = $site['blog_id'];
			$locale = ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
			$wp_admin_bar->add_node(
				array( 
					'id' => 'blog-'. $blog_id,
					'title' => ( ( isset( $this->flags[$blog_id] ) )? $this->flags[$blog_id] : '' ) . get_blog_option( $blog_id, 'blogname' ),
					'parent' => 'my-sites-list',
					'href' => get_admin_url( $blog_id )
				)
			);
		endforeach;
	}
	
	/** == CONTRÔLEUR == **/
	/** == Récupération des drapeaux == **/
	function get_flags(){
		global $tiFy;
		
		foreach( $this->sites as $site ) :
			$blog_id = $site['blog_id'];
			$locale = ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
			$filename = dirname( __FILE__ ) .'/flags/'.$locale.'.svg';
			
			if( ! file_exists( $filename ) )
				continue;

			if( $src = tify_svg_img_src( $filename ) )
				$this->flags[ $blog_id ] = "<img src=\"$src\" class=\"flag tify_multilingual-flag tify_multilingual-flag-{$locale}\" />";
		endforeach;
	}
	
	/* = TEMPLATES = */
	/** == Selecteur de langage == **/
	function tpl_switcher( $args = array( ) ){
		static $instance = 0;
		$instance++; 
		
		$defaults = array(
			'id'		=> 'tify_multilingual_switcher-'. $instance,
			'selected' 	=> get_current_blog_id(),
			'display'	=> 'dropdown',
			'separator'	=> '&nbsp;|&nbsp;',
			'label'		=> 'iso', // iso -ex : fr | language - ex: fr_FR | english_name - ex : French (France) | native_name - ex : Français
			'labels'	=> array( ), // Intitulés personnalisés, tableaux indexés par blog_id
			'flag'		=> false,
			'echo'		=> true
		);
		$args = wp_parse_args( $args, $defaults );

		// Création des liens
		$args['links'] = array();
		foreach( $this->sites as $site ) :			
			$blog_id = $site['blog_id'];
			$locale = ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
			if( ! empty( $args['labels'][$blog_id] ) )
				$label = $args['labels'][$blog_id];
			elseif( isset( $this->translations[$locale] ) )
				$label = ( $args['label'] === 'iso' ) ?  $this->translations[$locale]['iso'][1] : $this->translations[$locale][ $args['label'] ];
			else
				$label = $locale;
			
			$args['links'][$blog_id]  = "";			
			$args['links'][$blog_id] .= "<a href=\"". get_site_url( $blog_id ) ."\"". ( $args['selected'] == $blog_id ? ' class="selected"' : '' ).">";
			if( $args['flag'] && isset( $this->flags[$blog_id] ) )
				$args['links'][$blog_id] .=	$this->flags[$blog_id];
			$args['links'][$blog_id] .= $label ."</a>";
		endforeach;

		$output = "";
		if( $args['display'] == 'dropdown' ) :
			$_args = $args;
			$_args['echo'] = false;
			
			$output .= tify_control_dropdown_menu( $_args );
		elseif( $args['display'] == 'inline' ) :
			$output .= "<div id=\"{$args['id']}\" class=\"tify_multilingual_switcher-inline\">". implode( $args['separator'], $args['links'] ) ."</div>";
		elseif( $args['display'] == 'list' ) :
			$output .= 	"<div id=\"{$args['id']}\" class=\"tify_multilingual_switcher-inline\">\n".
						"\t<ul>\n";
			foreach( $args['links'] as $link )
				$output .= "\t\t<li>". $link ."</li>\n";
			$output .= "\t<ul>\n";
			$output .= "</div>\n";
		endif;
		
		if( $args['echo'] )
			echo $output;
		
		return $output;
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY  = */
	/** == Déclaration de taboox == **/
	function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_Multilingual_MenuSwitcher_Taboox', $this );
		tify_taboox_register_form( 'tiFy_Multilingual_AdminLang_Taboox', $this );
	}	
}
global $tify_multilingual;
$tify_multilingual = new tiFy_Multilingual;

/* = TABOOXES = */
/** == Configuration du menu de bascule  de langage == **/
class tiFy_Multilingual_MenuSwitcher_Taboox extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$name = 'tify_multilingual_switcher',
			// Référence
			$master;
	
	/* = CONTRUCTEUR = */
	function __construct( tiFy_Multilingual $master ){
		parent::__construct();	
		$this->master = $master;
	}
	
	/* = FORMULAIRE DE SAISIE = */
	function form(){
	?>
	<table class="form-table">
		<tbody>
		<?php foreach( $this->master->sites as $site ) : 
				$blog_id 	= $site['blog_id']; 
				$locale 	= ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale : 'en_US'; 
				$label	 	= isset( $this->master->translations[$locale] ) ? $this->master->translations[$locale]['native_name'] : __( 'English (United States)');
				$default 	= isset( $this->master->translations[$locale] ) ? $this->master->translations[$locale]['iso'][1] : $locale;
		?>
			<tr>
				<th scope="row">
					<?php echo $label;?>
				</th>
				<td>
					<input type="text" name="<?php echo $this->name;?>[<?php echo $blog_id;?>]" value="<?php echo isset( $this->value[$blog_id] ) ? $this->value[$blog_id] : $default;?>">
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<?php
	}
}

class tiFy_Multilingual_AdminLang_Taboox extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$name = 'tify_multilingual_adminlang',
			// Référence
			$master;
	
	/* = CONTRUCTEUR = */
	function __construct( tiFy_Multilingual $master ){
		parent::__construct();
		$this->master = $master;		
	}
		
	/* = FORMULAIRE DE SAISIE = */
	function form( $args = array() ){
		$languages = get_available_languages(); 
		$translations = wp_get_available_translations();
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Choix de la langue de l\'interface d\'administration', 'tify' );?></th>
				<td>
					<?php
						$locale = get_locale();
						if ( ! in_array( $locale, $languages ) )
							$locale = '';
						wp_dropdown_languages( 
							array(								
								'id'           => $this->name,
								'name'         => $this->name,
								'selected'     => $this->value ? ( is_array( $this->value )? current( array_keys( $this->value ) ) : $this->value ): $locale,
								'languages'    => $languages,
								'translations' => $translations,
								'show_available_translations' => ( ! is_multisite() || is_super_admin() ) && wp_can_install_language_pack(),
							) 
						);
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}
add_action( 'setup_theme', 'tify_multilingual_setup_theme' ); 
function tify_multilingual_setup_theme(){
	if( get_option( 'tify_multilingual_adminlang', 0 ) )		
		add_filter( 'locale', 'tify_multilingual_admin_locale' );
}
function tify_multilingual_admin_locale( $locale = null ){
	if( is_admin() )
		$locale = get_option( 'tify_multilingual_adminlang', 0 );
	return $locale;
}


/* = HELPER = */
/** == Affichage du selecteur de langues == **/
function tify_multilingual_switcher( $args = array( ) ){
	global $tify_multilingual;

	return $tify_multilingual->tpl_switcher( $args );
}