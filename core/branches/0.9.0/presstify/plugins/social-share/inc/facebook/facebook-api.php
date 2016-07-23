<?php
class tiFy_SocialShare_Facebook{
	/* = ARGUMENTS = */
	public	// Configuration
			$options,
			$defaults = array(
				'appId' 	=> '',
				'uri' 		=> ''
			),
			// Référence
			$master;
		
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_SocialShare $master ){
		$this->master = $master;
		
		// Actions et Filtres Wordpress		
		add_action( 'after_setup_theme', array( $this, 'wp_after_setup_theme' ) );
		add_action( 'wp_ajax_tify_fb_post2feed_callback', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_fb_post2feed_callback', array( $this, 'wp_ajax' ) );
	}
	
	/* = CONFIGURATION = */
	/** == Définition des options == **/
	function set_options(){
		$options = get_option( 'tify_social_share' );
		$options = wp_parse_args( $options, array( 'fb' => $this->defaults ) );
		$this->options = $options['fb'];	
	}	
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation du thème == **/
	function wp_after_setup_theme(){
		if( ! $this->master->is_active( 'facebook' ) )
			return;
		
		// Initialisation du contrôleur de SDK Facebook
		tify_require_lib( 'facebook_sdk' );
		
		// Définition des options
		$this->set_options();		
				
		// Action et Filtres Wordpress		
		add_action( 'init', array( $this, 'wp_init' ) );		
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );	
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
	}
	
	/** == Initialisation globale == **/
	function wp_init(){
		// Instanciation du SDK
		tify_facebook_sdk_set_option( 'javascript_sdk', true );
		
		// Initialisation des scripts
		wp_register_script( 'tify_social_share_facebook', $this->master->uri.'/inc/facebook/facebook-api.js', array( 'jquery' ), '20150108', true );	
	}
	
	/** == Mise en file des scripts == **/
	function wp_enqueue_scripts(){
		wp_enqueue_script( 'tify_social_share_facebook' );
	}

	/** == == **/
	function wp_ajax(){
		$output = apply_filters( 'tify_fb_post2feed_callback_handle', '', $_POST['response'], $_POST['attrs'] );		
		
		wp_die( $output );
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration d'un section de boîte à onglets == **/
	function tify_options_register_node(){
		tify_options_register_node(	
			array(
				'parent' 	=> 'tify_social_share',
				'id' 		=> 'tify_social_share-facebook',				
				'title' 	=> "<i class=\"fa fa-facebook-official\"></i> ". __( 'Facebook', 'tify' ),
				'cb' 		=> 'tiFy_SocialShare_Facebook_Taboox'					
			)
		);
	}
	
	/** == Déclaration des taboox == **/
	function tify_taboox_register_form(){		
		tify_taboox_register_form( 'tiFy_SocialShare_Facebook_Taboox', $this );
	}
}

/* = TABOOX = */
/** == == **/
class tiFy_SocialShare_Facebook_Taboox extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$data_name = 'tify_social_share', 
			$data_key = 'tify_social_share', 

			// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_SocialShare_Facebook $master ){
		parent::__construct();	
		$this->master = $master;
		$this->defaults['fb'] = $this->master->defaults; 			
	}
	
	/** == Formulaire de saisie == **/
	function form(){
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<?php _e( 'Identifiant de l\'API Facebook', 'tify' );?>*<br>
						<em style="font-size:11px; color:#999;"><?php _e( 'Requis', 'tify' );?></em>	
					</th>
					<td>
						<input type="text" name="<?php echo $this->data_name;?>[fb][appId]" value="<?php echo $this->data_value['fb']['appId'];?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Url de la page Facebook', 'tify' );?><br>
					</th>
					<td>
						<input type="text" name="<?php echo $this->data_name;?>[fb][uri]" value="<?php echo $this->data_value['fb']['uri'];?>" size="80" placeholder="<?php _e( 'https://www.facebook.com/[nom de la page]','tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}

/* = GENERAL TEMPLATE = */
/** == Bouton de partage Facebook == **/
function tify_fb_api_share_button( $args = array() ){
	global $tify_social_share;
	
	$defaults = array(
		'class'				=> '',
		'text'				=> __( 'Partager sur Facebook', 'tify' ),
		'uri'				=> is_singular() ? get_permalink( get_the_ID() ) : home_url(),
		'image'				=> ( is_singular() && ( $attachment_id = get_post_thumbnail_id( get_the_ID() ) ) ) ? wp_get_attachment_url( $attachment_id ) : '', 
		'callback_attrs'	=> array(),
		'title'				=> ( is_singular() ) ? get_the_title( get_the_ID() ) : get_bloginfo( 'name' ),
		'desc'				=> ( is_singular() ) ? get_the_excerpt() : get_bloginfo( 'description' ),
		'echo'				=> true	
	);	
	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'tify_fb_api_share_button_args', $args, $defaults );
	extract( $args );

	if( $image_src = tiFy_Utils::get_context_img_src( $image, 1200, 630, true ) ) :
	elseif( $image_src = tiFy_Utils::get_context_img_src( $image, 600, 315, true ) ) :
	elseif( $image_src = tiFy_Utils::get_context_img_src( $image, 200, 200, true ) ) :
	endif;
		
	$output = 	"<a href=\"". esc_url( $uri )."\"".
				" class=\"{$class}\"".
				" data-action=\"tify-fb-api_share_button\"".
				" data-url=\"{$uri}\"".
				" data-title=\"". esc_attr( $title ). "\"".
				" data-desc=\"". esc_attr( $desc ) ."\"".
				" data-image=\"". esc_attr( $image_src ) ."\"".
				" data-callback_attrs=\"". ( htmlentities( json_encode( $callback_attrs ) ) ) ."\"".
				">{$text}</a>";
	
	if( $echo )
		echo $output;
	else
		return $output;
}

/** == Lien vers la page Facebook == **/
function tify_fb_api_page_link( $args = array() ){
	global $tify_social_share;
	
	if( empty( $tify_social_share->fb->options[ 'uri' ] ) )
		return;
	
	$defaults = array(
			'class'		=> '',
			'title'		=> '',
			'attrs'		=> array(),
			'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"". $tify_social_share->fb->options[ 'uri' ] ."\" class=\"$class\"";
	if( ! isset( $attrs['title'] ) )
		$output .= " title=\"". sprintf( __( 'Vers la page Facebook du site %s', 'tify' ), get_bloginfo( 'name' ) ) ."\"";
	if( ! isset( $attrs['target'] ) )
		$output .= " target=\"_blank\"";
	foreach( (array) $attrs as $key => $value ) 
		$output .= " {$key}=\"{$value}\"";
	$output .= ">$title</a>";

	if( $echo )
		echo $output;
	else
		return $output;
}