<?php
/*
Plugin Name: Home Slideshow
Plugin URI: http://presstify.com/theme_manager/addons/homeslideshow
Description: Diaporama de la page d'accueil
Version: 1.0.1
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * @see http://tympanus.net/Tutorials/CSS3SlidingImagePanels/index3.html
 */

class tiFy_theme_manager_homeslideshow{
	/* = ARGUMENTS = */
		var $tiFy,
		$editbox,		
		$dir,
		$uri,
		$path,
		$options;
		
	/* = CONSTRUCTEUR = */
	function __construct(){
		global $tiFy;
		
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;
		
		// Action et filtres Wordpress
		add_action( 'init', array( $this,'init' ) );
		add_action( 'admin_menu', array( $this,'admin_menu' ) );
		add_action( 'admin_init', array( $this,'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this,'admin_enqueue_scripts' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ) );
				
		/* Actions ajax */
		add_action( 'wp_ajax_mk_home_slideshow_get_item_html', array( $this, 'wp_ajax' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/* = Définition des options par défaut = */
	function init(){
		$this->options = apply_filters( 'tify_homeslideshow_options', array(
				'admin_menu' => true,
				'max'		 => -1,
			)
		);
	} 
	
	/** == Ajout d'une entrée de menu Wordpress == **/
	function admin_menu(){
		if( $this->options['admin_menu'] )
			$option_page = add_theme_page(
				__( 'Diaporama', 'tify' ),
				__( 'Diaporama de l\'accueil', 'tify' ),
				'manage_options',
				'mk_home_slideshow_options',
				array( $this, 'admin_render' )
			);
	}
	
	/** == Enregistrements des scripts == **/
	function admin_init(){
		// Déclaration des scripts
		wp_register_script( 'tinyMCE', includes_url( 'js/tinymce' ). '/tinymce.min.js', array(), ' 4.1.4', true );
		wp_register_script( 'jQuery-tinyMCE', '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.1.4/jquery.tinymce.min.js', array( 'jquery', 'tinyMCE' ), true );
		wp_register_style( 'home-slideshow-options', $this->uri ."/homeslideshow.css", array(), '130608' );
		wp_register_script( 'home-slideshow-options', $this->uri ."/homeslideshow.js", array( 'jQuery-tinyMCE', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'tinyMCE' ), '130608', true );	
	
		// Section d'option	
		add_settings_section( 'posts', __( 'Diaporama', 'tify' ), '__return_false', 'mk_home_slideshow_options' );		
		// Déclaration des options
		register_setting( 'mk_home_slideshow_options', 'mk_home_slideshow_items' );
	}
	
	/** == Mise en file des scripts == **/
	function admin_enqueue_scripts( $hookname ){
		if( $hookname != 'appearance_page_mk_home_slideshow_options' )
			return;
		
		wp_enqueue_media();
		wp_enqueue_style( 'home-slideshow-options' );
		wp_enqueue_script( 'home-slideshow-options' );
		
		do_action('mk_home_slideshow_options_admin_enqueue_scripts', $hookname );
		
		wp_localize_script( 'home-slideshow-options', 'tify', array(
				'max'	  => $this->options['max'],
				'l10nMax' => __( 'Nombre maximum de vignettes atteint', 'tify' )
			)
		);
	}
	
	/** == Ajout d'un noeud à la barre d'administration Wordpress == **/
	function admin_bar_menu( $wp_admin_bar ){
		// Bypass
		if( is_admin() )
			return;
		if( ! $this->options['admin_menu'] )
			return;
		
		// Ajout d'un lien de configuration du Diaporama
		$wp_admin_bar->add_node(
			array(
				'id' => 'mk_home_slideshow_options',
	    		'title' => __('Configurer le Diaporama', 'mktzr'),
	    		'href' => admin_url('/themes.php?page=mk_home_slideshow_options'),
	   			'parent' => 'site-name'
			)
		);
	}
	
	/** == Chargement AJAX d'une vignette == **/
	function wp_ajax(){				
		echo mk_home_slideshow_item_html( array( 'post_id' => $_POST['post_id'], 'order' =>$_POST['order'] ) ); 
		exit;
	}
	
	/* = INTERFACE = */
	function admin_render(){
	?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'Diaporama de la page d\'accueil', 'mktzr' ); ?></h2>
			<?php settings_errors(); ?>
			
			<form method="post" action="options.php">
				<?php settings_fields( 'mk_home_slideshow_options' );?>
				
				<?php mk_home_slideshow_options_render_panel();?>
				
				<?php do_settings_sections( 'mk_home_slideshow_custom_options' ); ?>
				<?php submit_button(); ?>			
			</form>			
		</div>
	<?php	
	}	
}
new tiFy_theme_manager_homeslideshow;
/**
 * Panneau de rendu des options
 */
function mk_home_slideshow_options_render_panel(){
?>
	<style>
		.ui-autocomplete{
			overflow: auto;
			max-height: 200px;
		}
	</style>
	<div id="slideshow_post-selector">
		<input type="text" id="search-slideshow_post" value="" data-post_type="<?php echo apply_filters( 'mk_home_slideshow_post_type', 'any' );?>" size="70" placeholder="<?php _e('Choisissez un contenu à afficher dans le diaporama', 'mktzr');?>" autocomplete="off" /> 
		<a href="#" id="add-slideshow_post" class="button-primary button-primary-disabled"><?php _e('Ajouter un contenu du site', 'mktzr'); ?></a>&nbsp;
		<?php _e('ou', 'mktzr');?>&nbsp;<a href="#" id="add-custom_link" class="button-secondary"><?php _e('Ajouter un lien personnalisé', 'mktzr'); ?></a>
	</div>
	<div id="slideshow_post-list">
		<div class="overlay"><?php _e('Chargement ...', 'tify'); ?></div>
		<ul id="list-slideshow_post">
		<?php if( $slides = get_option( 'mk_home_slideshow_items' ) ) :
				// Trie selon l'attribut d'ordonnancement
				if( is_array( $slides ) )
					$slides = mk_multisort($slides);
				// Affichage des entrées
				foreach( (array) $slides as $index => $slide ) 
					echo mk_home_slideshow_item_html( $slide, $index );
						
		endif; ?>
		</ul>
	</div>
<?php	
}

/**
 * Affichage de l'interface de saisie d'une vignette d'illustration
 */
function mk_home_slideshow_item_html( $slide, $index = 0 ){
	if( !$index )
		$index = uniqid();

	$_thumb = false; $alert = false; $invalid = false; $image = false;

	$defaults = array( 
		'post_id'=> 0,
		'caption'=> '', 
		'attachment_id'=> 0, 
		'clickable' => '1',
		'url' => ''
		); 
	
	$slide = wp_parse_args( $slide, $defaults );
	// Récupération de l'image d'illustration
	if( $image = wp_get_attachment_image_src( $slide['attachment_id'], 'slide' ) ) :
		$attachment_id = $slide['attachment_id'];
	elseif( ( $attachment_id = get_post_thumbnail_id( $slide['post_id']) ) &&  ( $image = wp_get_attachment_image_src( $attachment_id, 'slide' ) ) ) :
	endif;
		
	if( $attachment_id )
		$_thumb = wp_get_attachment_image($attachment_id);			
		
	$output  = "";
	$output .= "\n<li class=\"slideshow_post ".( $invalid? 'invalid': '' )."\">";
	
	$output .= "\n\t<div class=\"handle\"></div>";
	
	// Image d'illustration
	$output .= "\n\t<div class=\"thumbnail\">";
	$output .= "\n\t\t<a href=\"#\" id=\"slideshow-image-".$index."\" class=\"add-slideshow-image\" data-index=\"{$index}\" data-uploader_title=\"".__( 'Illustration de la vignette du diporama', 'mktzr' )."\">";
	if( $_thumb ) :
		$output .= $_thumb;
		$output .= "\n\t<input type=\"hidden\" name=\"mk_home_slideshow_items[{$index}][attachment_id]\" value=\"".$attachment_id."\" />";
	endif;
	$output .= "\n\t\t</a>";
	$output .= "\n\t</div>";
	
	// Message d'alerte
	if( $alert )
		$output .= "\n\t<strong class=\"alert\">".$alert."</strong>";
	
	$output .= "\n<div class=\"input-fields\">";
	// Titre
	if( $slide['post_id'] )
		$output .= "\n\t<h3 class=\"title\">".get_the_title($slide['post_id'])."</h3>";	
	
	// Vignette cliquable
	$output .= "\n\t<p class=\"clickable\">";
	$output .= __( 'Vignette cliquable', 'mktzr' );
	$output .= "&nbsp;&nbsp;<label><input type=\"radio\" name=\"mk_home_slideshow_items[{$index}][clickable]\" value=\"1\" ".checked( $slide['clickable'], true, false )." /> ".__('Oui', 'mktzr')."</label>&nbsp;";		
	$output .= "<label><input type=\"radio\" name=\"mk_home_slideshow_items[{$index}][clickable]\" value=\"0\" ".checked( ! $slide['clickable'], true, false )." /> ".__('Non', 'mktzr')."</label>";	
	$output .= "\n\t</p>";
	
	// Url personnalisée
	if( !$slide['post_id'] )
		$output .= "\n\t<input type=\"text\" class=\"url\" placeholder=\"".__( 'Saisissez l\'url du site', 'mktzr' )."\" name=\"mk_home_slideshow_items[{$index}][url]\" value=\"{$slide['url']}\" size=\"80\"/>";
	
	// Texte personnalisé de la vignette
	$output .= "\n\t<div id=\"mk_home_slideshow_items[{$index}][caption]\" class=\"caption editable\" >".$slide['caption']."</div>";
	$output .= "\n</div>";
	
	
	// Ordre d'affichage	
	$output .= "\n\t<div class=\"order\"><input type=\"text\" name=\"mk_home_slideshow_items[{$index}][order]\" class=\"order-value\" size=\"2\" value=\"".$slide['order']."\" readonly/></div>";
	
	$output .= "\n\t<input type=\"hidden\" name=\"mk_home_slideshow_items[{$index}][post_id]\" value=\"".$slide['post_id']."\" />";
	
	$output .= "\n\t<a href=\"\" class=\"tify_button_remove remove\" style=\"font-size:0.9em; color:red;\"></a>";
	$output .="\n</li>";
	
	return apply_filters( 'mk_home_slideshow_item_html', $output, $slide, $index );
}

/**
 * Affichage du diaporama
 */
function mk_home_slideshow_display( $args = array( ) ){
	// Bypass	
	if( ! $slides = get_option( 'mk_home_slideshow_items' ) ) 
		return false;
	if( is_array( $slides ) )
		$slides = mk_multisort($slides);	
	
	$defaults = array(
		'image_size' => 'slide', 
		'image_as_bkg' => true,
		'echo' => 1
	);	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
		
	$output  = "";
	$output .= "\n<div id=\"mk-home-slideshow\" class=\"mk-slideshow\">";	
	$output .= "\n\t<div class=\"viewer\">";
	$output .= "\n\t\t<ul class=\"roller\">";
	
	// Vignettes
	foreach( (array) $slides as $slide ) :
		if( empty( $slide['attachment_id'] ) ) continue; 
		$output .= "\n\t\t\t<li>";
		if( isset( $slide['clickable'] ) && $slide['clickable'] ) :
			if( $slide['post_id'] ) :
				$url = get_permalink($slide['post_id']); 
			elseif( $slide['url'] ) :
				$url= $slide['url']; 
			else :
				$url='#mk-home-slideshow';
			endif;	
			$output .= "\n\t\t\t<a href=\"{$url}\">";
		endif;	
		if( $image_as_bkg ) :
			$image =  wp_get_attachment_image_src( $slide['attachment_id'], 'full' );
			$output .= "<div class=\"item-image\" style=\"background-image:url(".$image[0].")\"></div>";			
		else :
			$output .= wp_get_attachment_image( $slide['attachment_id'], $image_size, false, array( 'class' => 'item-image') );
		endif;
		
		if( isset( $slide['clickable'] ) && $slide['clickable'] )
			$output .= "\n\t\t\t\t</a>";
		
		// Légende de la vignette
		if( !empty( $slide['caption'] ) )
			$output .= "<div class=\"caption\">".$slide['caption']."</div>";	
		
		$output .= "\n\t\t\t</li>";
	endforeach;	
	$output .= "\n\t\t</ul>";// Fin des vignettes 

	// Navigation suivant/précédent
	$output .= "\n\t\t<a href=\"#\" class=\"nav prev\">&larr;</a>";
	$output .= "\n\t\t<a href=\"#\" class=\"nav next\">&rarr;</a>";
	
	// Navigation tabulation
	reset($slides);
	$output .= "\n\t\t<ul class=\"tabs\">";
	foreach( (array) $slides as $slide )
		$output .= "\n\t\t<li class=\"tab\"><a href=\"#mk-home-slideshow\">".($slide['order'])."</a></li>";	
	$output .= "\n\t\t</ul>"; 
	
	$output .= "\n\t\t<div class=\"overlay\"></div>";
	$output .= "\n\t\t<div class=\"progressbar\"><span></span></div>";
	
	$output .= "\n\t</div>";	
	$output .= "\n</div>";// Fin du diaporama
	
	$output = apply_filters( 'mk_home_slideshow_display', $output, $slides, $args );
	
	if( $echo )
		echo $output;
	else
		return $output;	
}