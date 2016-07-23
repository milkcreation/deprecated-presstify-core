<?php
class tiFy_SocialShare_Viadeo{
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
		$options = wp_parse_args( $options, array( 'viadeo' => $this->defaults ) );
		$this->options = $options['viadeo'];	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation du thème == **/
	function wp_after_setup_theme(){
		if( ! $this->master->is_active( 'viadeo' ) )
			return;	
		
		// Définition des options par défaut
		$this->set_options();
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_options_register_node', array( $this, 'tify_options_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
	}
	
	/** == Initialisation globale == **/
	function wp_init(){}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration d'un section de boîte à onglets == **/
	function tify_options_register_node(){
		tify_options_register_node(	
			array(
				'id' 		=> 'tify_social_share-viadeo',
				'parent' 	=> 'tify_social_share',
				'title' 	=> __( 'Viadeo', 'tify' ),
				'cb' 		=> 'tiFy_SocialShare_Viadeo_Taboox'
			)
		);
	}
	/** == Déclaration des taboox == **/
	function tify_taboox_register_form(){		
		tify_taboox_register_form( 'tiFy_SocialShare_Viadeo_Taboox', $this );
	}
}

/* = TABOOX = */
/** == == **/
class tiFy_SocialShare_Viadeo_Taboox extends tiFy\Taboox\Admin
{
	/* = ARGUMENTS = */
	private	$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_SocialShare_Viadeo $master )
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
						<?php _e( 'Url du profil Viadeo', 'tify' );?><br>
					</th>
					<td>
						<input type="text" name="tify_social_share[viadeo][uri]" value="<?php echo $value['uri'];?>" size="80" placeholder="<?php _e( 'http://www.viadeo.com/profile/[identifiant du profil]', 'tify' );?>" />
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}
}

/* = GENERAL TEMPLATE = */
/** == lien vers la page == **/
function tify_viadeo_api_account_link( $args = array() ){
	global $tify_social_share;
	
	if( empty( $tify_social_share->viadeo->options[ 'uri' ] ) )
		return;
	
	$defaults = array(
			'class'		=> '',
			'title'		=> '',
			'attrs'		=> array(),
			'echo'		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = "<a href=\"". $tify_social_share->viadeo->options[ 'uri' ] ."\" class=\"$class\"";
	if( ! isset( $attrs['title'] ) )
		$output .= " title=\"". sprintf( __( 'Vers le profil Viadeo du site %s', 'tify'), get_bloginfo( 'name' ) ) ."\"";
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