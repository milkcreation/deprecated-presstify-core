<?php
class tiFy_SocialShare_GooglePlus{
	/* = ARGUMENTS = */
	public	// Configuration
			$options,
			$defaults = array(
				'uri' 		=> ''
			),
			// Référence
			$master;
		
	/* = CONSTRUCTEUR = */
	function __construct( $master ){
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'after_setup_theme', array( $this, 'wp_after_setup_theme' ) );
	}
	
	/* = CONFIGURATION = */
	/** == Définition des options == **/
	function set_options(){
		$options = get_option( 'tify_social_share' );
		$options = wp_parse_args( $options, array( 'gplus' => $this->defaults ) );
		$this->options = $options['gplus'];	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation du thème == **/
	function wp_after_setup_theme(){
		if( ! $this->master->is_active( 'googleplus' ) )
			return;
		
		// Définition des options par défaut
		$this->set_options();
		
		// Action et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 99 );
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
	}
	
	/** == Initialisation globale == **/
	function wp_init(){}
	
	/** == Pied de page du site == **/
	function wp_footer(){
		?><script type="text/javascript">/* <![CDATA[ */
		      window.___gcfg = {
		        lang: '<?php echo get_locale();?>'
		      };		
		      (function() {
		        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		        po.src = 'https://apis.google.com/js/plusone.js';
		        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		      })();
	      /* ]]> */</script><?php		
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration d'un section de boîte à onglets == **/
	function tify_options_register_node(){
		tify_options_register_node(	
			array(
				'id' 		=> 'tify_social_share-gplus',
				'parent' 	=> 'tify_social_share',
				'title' 	=> "<i class=\"fa fa-google-plus\"></i> ". __( 'Google Plus', 'tify' ),
				'cb' 		=> 'tiFy_SocialShare_GooglePlus_Taboox'
			)
		);
	}
		
	/** == Déclaration des taboox == **/
	function tify_taboox_register_form(){		
		tify_taboox_register_form( 'tiFy_SocialShare_GooglePlus_Taboox', $this );
	}
}

/* = TABOOX = */
/** == == **/
class tiFy_SocialShare_GooglePlus_Taboox extends tiFy\Taboox\Admin
{
	/* = ARGUMENTS = */
	private	$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_SocialShare_GooglePlus $master )
	{
		parent::__construct();	
		$this->master = $master;		
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		$value = $this->master->options;
	?>
		<table class="form-table">
			<tbody>			
				<tr>
					<th scope="row">
						<?php _e( 'Url de la page Google Plus', 'tify' );?><br>
					</th>
					<td>
						<input type="text" name="tify_social_share[gplus][uri]" value="<?php echo $value['uri'];?>" size="80" placeholder="<?php _e( 'https://plus.google.com/[nom de la page]', 'tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}

/* = GENERAL TEMPLATE = */
/** == Bouton de partage == **/
function tify_gplus_api_share_button( $args = array() ){
	$defaults = array(
		'class'			=> '',
		'button_text'	=> '',
		'uri'			=> is_singular() ? get_the_permalink( get_the_ID() ) : home_url( '/' ),
		'echo'			=> true	
	);	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$output = "<a href=\"https://plus.google.com/share?url=". esc_attr( $uri )."\" class=\"$class\" onclick=\"javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\">$button_text</a>";
	
	if( $echo )
		echo $output;
	else
		return $output;
}

/** == Lien vers la page == **/
function tify_gplus_api_page_link( $args = array() ){
	global $tify_social_share;
	
	if( empty( $tify_social_share->gplus->options[ 'uri' ] ) )
		return;
	
	$defaults = array(
			'class'		=> '',
			'title'		=> '',
			'attrs'		=> array(),
			'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"". $tify_social_share->gplus->options[ 'uri' ] ."\" class=\"$class\"";
	if( ! isset( $attrs['title'] ) )
		$output .= " title=\"". sprintf( __( 'Vers la page Google+ du site %s', 'tify'), get_bloginfo( 'name' ) ) ."\"";
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