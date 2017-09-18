<?php
namespace tiFy\Components\CustomFields\PostType\Permalink;

class Permalink extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'wp_ajax_tiFyComponentsCustomFieldsPostTypePermalink',
		'wp_loaded',
		'current_screen',
		'admin_enqueue_scripts'
	); 
	
	// Type de post
	private $PostType 		= null;
	
	// Liste de choix de liens
	private static $Permalinks	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct( $post_type, $args = array() )
	{
		parent::__construct();
		
		$this->PostType = $post_type;
		
		if( isset( $args['permalinks'] ) ) :
			foreach( $args['permalinks'] as $key => $attrs ) :
				self::Register( $key, $attrs );
			endforeach;
		endif;	
	}
	
	/* = CONTRÔLEUR = */
	/** == Déclaration des liens == **/
	public static function Register( $key, $attrs = array() )
	{
		return static::$Permalinks[sanitize_key($key)] = wp_parse_args( $attrs, array( 'title' => '', 'url' => '' ) );		
	}
	
	/* = DECLENCHEURS = */
	/** == Action Ajax == **/
	final public function wp_ajax_tiFyComponentsCustomFieldsPostTypePermalink()
	{
		$data = array();
		
		if( isset( $_POST['key'] ) && isset( self::$Permalinks[$_POST['key']]['url'] ) ) :
			$data['url'] 		= self::$Permalinks[$_POST['key']]['url'];
			$data['selected']	= 'key:'. $_POST['key'];
		elseif( isset( $_POST['post_id'] ) && ( $permalink = get_permalink( (int) $_POST['post_id'] ) ) ) :
			$data['url'] 			= $permalink;
			$data['selected']		= 'post_id:'. $_POST['post_id'];
		elseif( isset( $_POST['url'] ) ) :
			$data['url'] 		= ( ! preg_match( '/^http/', $_POST['url'] ) ) ? site_url() .'/'. ltrim( $_POST['url'], '/' ) : $_POST['url'];
			$data['selected']	= $_POST['url'];		
		elseif( isset( $_POST['cancel'] ) ) :
			delete_post_meta( $_POST['cancel'], '_permalink' );	
			add_filter( 'get_sample_permalink_html', array( $this, 'get_sample_permalink_html' ), 10, 5 );
			$data['url'] = get_sample_permalink_html( (int) $_POST['cancel'] );	
		endif;
				
		wp_send_json( $data );
	}
		
	/** == Chargement de Wordpress == **/
	final public function wp_loaded()
	{
		do_action_ref_array( 'tify_permalink_register', array( $this ) );
		do_action_ref_array( 'tify_permalink_register_post_type_'. $this->PostType, array( $this ) );

		if( $this->PostType == 'page' ) :
			//apply_filters( 'page_link', $link, $post->ID, $sample );
			add_filter( 'page_link', array( $this, 'permalink' ), 10, 3 );
		elseif( $this->PostType == 'attachment' ) :
			//apply_filters( 'attachment_link', $link, $post->ID );
			add_filter( 'attachment_link', array( $this, 'permalink' ), 10, 2 );
		elseif ( in_array( $this->PostType, get_post_types( array( '_builtin' => false ) ) ) ) :
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
				
		tify_meta_post_register( $current_screen->id, '_permalink', true, 'esc_attr' );
		
		add_action( 'edit_form_top', array( $this, 'edit_form_top' ), 10 );
		add_filter( 'get_sample_permalink_html', array( $this, 'get_sample_permalink_html' ), 10, 5 );
	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function admin_enqueue_scripts()
	{
		// Mise en file des scripts
		tify_control_enqueue( 'dropdown' );	
		tify_control_enqueue( 'findposts' );
		tify_control_enqueue( 'suggest' );
		wp_enqueue_style( 'tiFyComponentsCustomFieldsPostTypePermalink', self::tFyAppUrl() . '/Permalink.css', array(), '160526' );
		wp_enqueue_script( 'tiFyComponentsCustomFieldsPostTypePermalink', self::tFyAppUrl() . '/Permalink.js', array( 'jquery' ), '160526' );
	}
		
	/** == Affichage d'un message d'avertissement lorsque le lien est personalisé == **/
	final public function edit_form_top( $post )
	{
		if( ! get_post_meta( $post->ID, '_permalink', true ) )
			return;
		
		echo 	"<div id=\"tiFyComponentsCustomFieldsPostTypePermalink-notice\" class=\"notice notice-info inline\">\n".
					"\t<p>". __( 'Le permalien qui mène à ce contenu fait référence à un lien personnalisé.', 'siadep' ) ."</p>\n".
				"</div>";	
	}
	
	/** == Modification de l'interface d'édition des permaliens == **/
	final public function get_sample_permalink_html( $output, $post_id, $new_title, $new_slug, $post )
	{
		$_permalink = get_post_meta( $post_id, '_permalink', true );

		$output .= "<section id=\"tiFyComponentsCustomFieldsPostTypePermalink\">\n";
		$output .= "\t<input id=\"tiFyComponentsCustomFieldsPostTypePermalink-active\" type=\"checkbox\" autocomplete=\"off\"/>";
		$output .= "\t<input id=\"tiFyComponentsCustomFieldsPostTypePermalink-selected\" type=\"hidden\" name=\"tify_meta_post[_permalink]\" value=\"{$_permalink}\"/>\n"; 
		$output .= "\t<label for=\"tiFyComponentsCustomFieldsPostTypePermalink-active\">\n";		
		$output .= __( 'Personnalisation du lien', 'tify' );
		$output .= "\t</label>\n";
		$output .= "\t&nbsp;&nbsp;<a href=\"#\" id=\"tiFyComponentsCustomFieldsPostTypePermalink-cancel\" data-post_permalink=\"\" style=\"". ( ! empty( $_permalink ) ? 'display:inline;'  : 'display:none;' ) ."\">". __( 'Annuler', 'tify' ) ."</a>";
		$output .= "\t<div id=\"tiFyComponentsCustomFieldsPostTypePermalink-selectors\">\n";
		
		// Interface des permaliens prédéfini
		if( static::$Permalinks ) :	
			$output .= "\t\t<section>";
			$output .= "\t\t\t<h4>- ". __( 'Choisir parmi une liste de liens prédéfinis :', 'tify' ). "</h4>";
			
			/// Formatage des permaliens prédéfini
			$permalinks = array();
			foreach( (array) static::$Permalinks as $id => $attrs ) :
				$permalinks[$id] = $attrs['title'];
			endforeach;
			$output .= tify_control_dropdown(
				array(
					'id'				=> 'tiFyComponentsCustomFieldsPostTypePermalink-dropdown',
					'selected'			=> $_permalink, 	
					'choices'			=> $permalinks,
					'option_none_value' => '',
					'picker'			=> array(
						'id'				=> 'tiFyComponentsCustomFieldsPostTypePermalink-dropdown-picker',
					),
					'echo'				=> false
				)
			);
			$output .= "\t\t</section>";
		endif;
		
		///
		$output .= "\t\t<section>\n";
		$output .= "\t\t\t<h4>- ". __( 'Recherher parmi les contenus du site :', 'tify' ). "</h4>\n";
		$output .= tify_control_suggest(
			array(
				'id'			=> 'tiFyComponentsCustomFieldsPostTypePermalink-suggest',
				'elements'		=> array( 'title', 'permalink', 'type', 'status', 'id' ),
				'echo' 			=> false
			)
		);
		$output .= "\t\t</section>\n";
		
		/// 
		$output .= "\t\t<section>\n";
		$output .= "\t\t\t<h4>- ". __( 'Saisir un lien personnalisé :', 'tify' ). "</h4>\n";
		$output .= "\t\t\t<div id=\"tiFyComponentsCustomFieldsPostTypePermalink-custom\">\n";
		$output .= "\t\t\t\t<input type=\"text\" value=\"\" placeholder=\"". __( 'Saisir l\'url du site', 'tify' ) ."\"/>\n";
		$output .= "\t\t\t\t<a href=\"#\">". __( 'Valider', 'tify' ) ."</a>";
		$output .= "\t\t\t</div>\n";
		$output .= "\t\t</section>\n";
		
		$output .= "\t</div>\n";
		$output .= "</section>\n";
				
		return $output;
	}
	
	/** ==  == **/
	final public function permalink( $permalink, $post )
	{		
		if( ! $post = get_post( $post ) )
			return $permalink;
		if( ! $_permalink = get_post_meta( $post->ID, '_permalink', true ) )
			return $permalink;		
		
		if( preg_match( '/^key:(.*)/', $_permalink, $match ) && isset( self::$Permalinks[$match[1]]['url'] ) ) :
			$permalink = self::$Permalinks[$match[1]]['url'];
		elseif( preg_match( '/^post_id:(\d*)/', $_permalink, $match ) && ( $permalink_post = get_post( (int) $match[1] ) ) ) :
			if( $permalink_post->ID !== $post->ID ) :
				$permalink = get_permalink( $permalink_post );
			endif;				
		elseif( ! preg_match( '/^http/', $_permalink ) ) :
			$permalink = site_url() .'/'. ltrim( $_permalink, '/' ); 
		else :
			$permalink = $_permalink;
		endif;	
		
		
		
		return $permalink;
	}
	
	
}