<?php
namespace tiFy\Components\CustomFields\PostType\Permalink;

use tiFy\Environment\App;

class Permalink extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'current_screen',
		'wp_loaded'
	); 
	
	// Type de post
	private $PostType 		= null;
	
	// Liste de choix de liens
	private static $Links	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct( $post_type, $args = array() )
	{
		parent::__construct();
		
		$this->PostType = $post_type;
		
		if( isset( $args['links'] ) ) :
			foreach( $args['links'] as $key => $attrs ) :
				self::Register( $key, $attrs );
			endforeach;
		endif;	
	}
	
	/* = CONTRÔLEUR = */
	/** == Déclaration des liens == **/
	public static function Register( $key, $attrs )
	{
		if( is_array( $attrs ) ) :
			if( ! isset( $attrs['url'] ) || ! isset( $attrs['title'] ) )
				return;
			$url 	= (string) $attrs['url'];
			$title 	= (string) $attrs['title'];
		elseif( is_numeric( $key ) ) :
			$url 	= (string) $attrs;
			$title 	= (string) $attrs;
		elseif( is_string( $key ) ) :
			$url 	= $key;
			$title 	= (string) $attrs;
		endif;
		
		if( ! preg_match( '/^http/', $url ) )
			$url = site_url() . $url;
				
		return static::$Links[$url] = $title;		
	}
	
	/* = ACTIONS = */
	/** == Chargement de Wordpress == **/
	final public function wp_loaded()
	{
		do_action_ref_array( 'tify_permalink_register', array( $this ) );
		do_action_ref_array( 'tify_permalink_register_post_type_'.$this->PostType, array( $this ) );
		
		if( $this->PostType == 'page' ) :
			//apply_filters( 'page_link', $link, $post->ID, $sample );
			add_filter( 'page_link', array( $this, 'permalink' ), 10, 3 );
		elseif( $this->PostType == 'attachment' ) :
			//apply_filters( 'attachment_link', $link, $post->ID );
			add_filter( 'attachment_link', array( $this, 'permalink' ), 10, 2 );
		elseif ( in_array( $this->PostType, get_post_types( array('_builtin' => false) ) ) ) :
			//apply_filters( 'post_type_link', $post_link, $post, $leavename, $sample );
			add_filter( 'post_type_link', array( $this, 'permalink' ), 10, 4 );
		else :	
			//apply_filters( 'post_link', $permalink, $post, $leavename );
			add_filter( 'post_link', array( $this, 'permalink' ), 10, 3 );
		endif;	
	}
	
	/** == Chargement de l'écran courant == **/
	final public function current_screen( $current_screen )
	{
		if( $current_screen->id !== $this->PostType )
			return;
		
		// Mise en file des scripts
		tify_control_enqueue( 'dropdown' );	
		wp_enqueue_style( 'tiFy_CustomFields_PostType_Permalink', $this->Url .'/Permalink.css', array(), '160526' );
		wp_enqueue_script( 'tiFy_CustomFields_PostType_Permalink', $this->Url .'/Permalink.js', array( 'jquery' ), '160526' );
		
		tify_meta_post_register( $current_screen->id, '_permalink', true, 'esc_attr' );
		
		add_action( 'edit_form_top', array( $this, 'edit_form_top' ), 10 );
		add_filter( 'get_sample_permalink_html', array( $this, 'get_sample_permalink_html' ), 10, 5 );
	}
	
	/** == Affichage d'un message d'avertissement lorsque le lien est personalisé == **/
	final public function edit_form_top( $post )
	{
		if( ! get_post_meta( $post->ID, '_permalink', true ) )
			return;
		
		echo 	"<div class=\"notice notice-info inline\">\n".
					"\t<p>". __( 'Le permalien qui mène à ce contenu fait référence à un lien personnalisé.', 'siadep' ) ."</p>\n".
				"</div>";	
	}
	
	/** == Modification de l'interface d'édition des permaliens == **/
	final public function get_sample_permalink_html( $output, $post_id, $new_title, $new_slug, $post )
	{
		if( ! static::$Links )
			return $output;
		
		$_permalink = get_post_meta( $post_id, '_permalink', true );

		$output .= "<span id=\"tiFy_CustomFields_PostType_Permalink\">\n";
		$output .= "<input id=\"tiFy_CustomFields_PostType_Permalink-checkbox\" type=\"checkbox\" ". checked( ! empty( $_permalink ), true, false ) ." autocomplete=\"off\"". ( ! empty( $_permalink ) ? " disabled=\"disabled\"" : "")."/>";
		$output .= "\t<label for=\"tiFy_CustomFields_PostType_Permalink-checkbox\">\n";		
		$output .= __( 'Lien personnalisé : ', 'tify' );
		$output .= "\t</label>\n";
		
		/*$output .= "<select id=\"tiFy_CustomFields_PostType_Permalink-dropdown\" name=\"tify_meta_post[_permalink]\" style=\"". ( ! empty( $_permalink )? 'display:inherit;' : 'display:none;' ) ."\" autocomplete=\"off\">";
		$output .= "<option value=\"\" ". selected( empty( $_permalink ), true, false ) .">". __( 'Aucun', 'tify' ) ."</option>";
		foreach( static::$Links as $url => $title ) :
			$output .= "<option value=\"{$url}\" ". selected( html_entity_decode( $_permalink ) == $url, true, false ) .">{$title}</option>";
		endforeach;
		$output .= "</select>";*/
		$output .= tify_control_dropdown(
			array(
				'id'				=> 'tiFy_CustomFields_PostType_Permalink-dropdown',
				'name'				=> 'tify_meta_post[_permalink]',
				'selected'			=> html_entity_decode( $_permalink ), 	
				'choices'			=> static::$Links,
				'option_none_value' => '',
				'attrs'				=> array(
					'style'		=> ! empty( $_permalink )? 'display:inline-block;' : 'display:none;'
				),
				'picker'			=> array(
					'id'		=> 'tiFy_CustomFields_PostType_Permalink-dropdown-picker',
				),
				'echo'				=> false
			)
		);
		$output .= "</span>\n";
		//$output .= "</div>\n";
			
		return $output;
	}
	
	/** ==  == **/
	final public function permalink( $permalink, $post )
	{
		if( $_permalink = get_post_meta( $post, '_permalink', true ) )
			$permalink = $_permalink;
		
		return $permalink;
	}
	
	
}