<?php
namespace tiFy\Components\ContactForm;

use tiFy\Environment\Component;

/** @Autoload */
class ContactForm extends Component
{
	/* = ARGUMENTS = */
	// Actions à déclencher	
	protected $CallActions			= array(			
		'the_content',
		'tify_options_register_node',
		'tify_form_register'
	);
	// Configuration
	/// Options par défaut
	private $Defaults;
	/// Liste des formulaires enregistrés
	static $Forms = array();	
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Récupération de la configuration par défaut
		$this->Defaults = \tiFy\Core\Params::parseAndEval( $this->Dirname .'/config/defaults.yml' );

		// Déclaration des formulaires
		if( empty( self::getConfig() ) ) :
			$id = 'tify_contact_form-0';
			self::$Forms[$id] = $this->parseArgs( $id );
		else :
			foreach( self::getConfig() as $i => $args ) :
				$id = ( is_numeric( $i ) ) ? 'tify_contact_form-'. $i : $i;
				self::$Forms[$id] = $this->parseArgs( $id, $args );
			endforeach;
		endif;
	}
	
	/* = Traitement des arguments de configuration = */
	private function parseArgs( $id, $args = array() )
	{			
		// Traitement des arguments généraux
		foreach( array( 'title', 'tify_options', 'hookpage', 'content'   ) as $attr ) :
			if( ! isset( $args[$attr] ) )
				$args[$attr] = $this->Defaults[$attr];
		endforeach;		
		
		// Traitement des arguments de formulaire
		// ID du formulaire
		if( ! isset( $args['form']['ID'] ) )
			$args['form']['ID'] = $id;
		// Titre du formulaire
		if( ! isset( $args['form']['title'] ) )
			$args['form']['title'] = $args['title'];
		// Préfixe du formulaire
		if( ! isset( $args['form']['prefix'] ) )
			$args['form']['prefix'] = $id;
		foreach( array( 'container_class', 'before', 'after', 'fields', 'options', 'buttons', 'add-ons'  ) as $attr ) :
			if( ! isset( $args['form'][$attr] ) ) :
				$args['form'][$attr] = $this->Defaults['form'][$attr];
			endif;
		endforeach;
		
		// Traitement de l'addon Mailer
		$mailer_defaults = array(
			'debug' => false,
			'confirmation' => array(
				'send' 		=> ( get_option( $id .'-confirmation', 'on' ) === 'on' ) ? true : false,
				'from' 		=> get_option( $id .'-sender' ),
				'to' 		=> array( array( 'email' => '%%email%%', 'name' => '%%firstname%% %%lastname%%' ) ),
				'subject' 	=> __( get_bloginfo( 'blogname' ).' | Votre message a bien été réceptionné', 'tify' )
			),
			'notification' => array(
				'send' 		=> ( get_option( $id .'-notification', 'off' ) === 'on' ) ? true : false,
				'from' 		=> array( 'name' => get_bloginfo( 'blogname' ), 'email' => get_option( 'admin_email' ) ),
				'to' 		=> get_option( $id .'-recipients' ),
				'subject' 	=> __( get_bloginfo( 'blogname' ).' | Vous avez reçu une nouvelle demande de contact', 'tify' )
			)
		);		
		if( $args['form']['add-ons']['mailer'] === false ) :
			unset( $args['form']['add-ons']['mailer'] );
		elseif( $args['form']['add-ons']['mailer'] === true ) :
			$args['form']['add-ons']['mailer'] = $mailer_defaults;
		else :
			$args['form']['add-ons']['mailer'] = wp_parse_args( $args['form']['add-ons']['mailer'], $mailer_defaults );
		endif;
		
		return $args;
	}
	
	/* = = */
	final public function the_content( $content )
	{
		// Bypass
		if( ! in_the_loop() )
			return $content;		
		if( ! $id = $this->getHookPageID() )
			return $content;
		
		// Masque le contenu et le formulaire sur la page d'accroche	
		if( ! self::$Forms[$id]['content'] )
			return '';
		// Affiche seulement le contenu du formulaire sur la page d'accroche, le formulaire pourra être appelé manuellement
		elseif( self::$Forms[$id]['content'] === 'only' )
			return $content;
		
		return self::Display( $id, $content, false );
	}
		
	/* = = */
	final public function tify_options_register_node()
	{
		foreach( (array) self::$Forms as $id => $args ) :
			if( $args['tify_options'] ) :
				\tify_options_register_node(
					array(
						'id' 		=> $id,
						'title' 	=> $args['title'],
						'cb'		=> "\\tiFy\\Components\\ContactForm\\Taboox\\Option\MailOptions\\Admin\\MailOptions",
						'args'		=> array( 'id' => $id )
					)
				);
			endif;
		endforeach;
	}
		
	/* = = */
	final public function tify_form_register()
	{
		foreach( (array) self::$Forms as $id => $args ) :
			\tify_form_register( $args['form'] );
		endforeach;
	}	
	
	/* = CONTROLEUR = */
	/** == == **/
	protected function getHookPageID( $post_id = null )
	{
		if( is_null( $post_id ) && \is_singular() ) :
			$post_id = get_the_ID();
		endif;
		
		if( ! $post_id ) :
			return false;
		endif;
		
		foreach( (array) self::$Forms as $id => $args ) :
			if( $args['hookpage'] && ( $hookpage = (int) get_option( 'page_for_'. $id, 0 ) ) && ( $hookpage === (int) $post_id ) ) :
				return $id;
			endif;
		endforeach;			
	
		return false;
	}
	
	
	/* = AFFICHAGE = */
	/** == Dans le contenu de la page == **/
	public static function Display( $id, $content, $echo = true )
	{
		$output  = "";
		if( self::$Forms[$id]['content'] === 'before' ) :
			$output .= $content;
		endif;
		$output .= \tify_form_display( self::$Forms[$id]['form']['ID'], false );
		if( self::$Forms[$id]['content'] === 'after' ) :
			$output .= $content;
		endif;
	
		if( $echo )
			echo $output;
		else
			return $output;
	}
}