<?php
namespace tiFy\Components\ContactForm;

final class ContactForm extends \tiFy\Environment\Component
{
	/* = ARGUMENTS = */
	// Actions à déclencher	
	protected $CallActions			= array(
		'tify_form_register',
		'the_content',
		'tify_options_register_node'
	);
	// Configuration
	/// Options par défaut
	private static $Defaults;
	
	/// Liste des formulaires enregistrés
	private static $Forms = array();	
	
	/* = DECLENCHEURS = */
	/** == Déclaration de formulaire == **/
	final public function tify_form_register()
	{
		// Récupération de la configuration par défaut
		self::$Defaults = \tiFy\Core\Params::parseAndEval( self::getDirname() .'/config/defaults.yml' );
		
		do_action( 'tify_contact_form_register' );
				
		// Enregistrement des formulaires passés en arguments
		foreach( (array) self::getConfig() as $id => $args ) :
			self::register( $id, $args );
		endforeach;

		// Enregistrement du formulaire par défaut (si aucun autre formulaire n'a été déclaré)
		if( empty( self::$Forms ) ) :
			$id = 'tiFyContactForm_Default';
			self::register( $id );
		endif;
		
		// Déclaration des formulaire
		foreach( (array) self::$Forms as $id => $args ) :		
			\tify_form_register( $id, $args['form'] );
		endforeach;
	}
	
	/** == Déclaration de la zone d'édition des options == **/
	final public function tify_options_register_node()
	{	
		foreach( (array) self::$Forms as $id => $args ) :
			if( $args['admin'] ) :			
				\tify_options_register_node(
					array(
						'id' 		=> $id,
						'title' 	=> $args['title'],
						'cb'		=> '\tiFy\Components\ContactForm\Taboox\Option\MailOptions\Admin\MailOptions',
						'args'		=> array( 'id' => $id, 'admin' => $args['admin']  )
					)
				);
			endif;
		endforeach;
	}	
		
	/** == == **/
	final public static function the_content( $content )
	{		
		// Bypass
		if( ! in_the_loop() )
			return $content;		
		if( ! $id = self::getHookPageID() )
			return $content;
			
		// Masque le contenu et le formulaire sur la page d'accroche	
		if( ! self::$Forms[$id]['content'] )
			return '';
		// Affiche seulement le contenu du formulaire sur la page d'accroche, le formulaire pourra être appelé manuellement
		elseif( self::$Forms[$id]['content'] === 'only' )
			return $content;
				
		return self::Display( $id, $content, false );
	}
				
	/* = CONTROLEUR = */
	/* = Déclaration d'un formulaire de contact  = */
	final public static function register( $id, $args = array() )
	{
		if( isset( self::$Forms[$id] ) )
			return;
		
		$id = ( is_numeric( $id ) ) ? 'tify_contact_form-'. $id : $id;
			
		self::$Forms[$id] = self::parseArgs( $id, $args );
	}
		
	/* = Traitement des arguments de configuration = */
	private static function parseArgs( $id, $args = array() )
	{					
		// Traitement des arguments généraux
		foreach( array( 'title', 'admin', 'hookpage', 'content' ) as $attr ) :
			if( ! isset( $args[$attr] ) )
				$args[$attr] = self::$Defaults[$attr];
		endforeach;		
		
		if( $args['hookpage'] && ( $hookpage = (int) get_option( 'page_for_'. $id, 0 ) ) )
			$args['hookpage'] = $hookpage;
		
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
				$args['form'][$attr] = self::$Defaults['form'][$attr];
			endif;
		endforeach;
		
		// Traitement de l'addon Mailer
		$mailer_defaults = array(
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
			),
			'admin'			=> false
		);		
		
		if( isset( $args['form']['add-ons']['mailer'] ) && $args['form']['add-ons']['mailer'] === false ) :
			unset( $args['form']['add-ons']['mailer'] );
		elseif( isset( $args['form']['add-ons']['mailer'] ) && $args['form']['add-ons']['mailer'] === true ) :
			$args['form']['add-ons']['mailer'] = $mailer_defaults;
		elseif( isset( $args['form']['add-ons']['mailer'] ) ) :
			$args['form']['add-ons']['mailer'] = wp_parse_args( $args['form']['add-ons']['mailer'], $mailer_defaults );
		else :
			$args['form']['add-ons']['mailer'] = $mailer_defaults;
		endif;
		
		return $args;
	}
	
	/** == Récupération de l'ID de la page d'accroche == **/
	protected static function getHookPageID( $post_id = null )
	{
		if( is_null( $post_id ) && \is_singular() ) :
			$post_id = get_the_ID();
		endif;
		
		if( ! $post_id ) :
			return false;
		endif;
		
		foreach( (array) self::$Forms as $id => $args ) :
			if( $args['hookpage'] === (int) $post_id ) :
				return $id;
			endif;
		endforeach;			
	
		return false;
	}
	
	/** == Récupération de la page d'accroche == **/
	public static function hookPage( $id = null )
	{
		if( ! $id )
			$id = key( self::$Forms );
		
		return self::$Forms[$id]['hookpage'];
	}
	
	/** == == **/
	public static function isPage( $id = null ){
		// Bypass
		if( ! is_singular() )
			return false;		
		if( ! $post_id = (int) get_the_ID() )
			return false;
			
		if( ! $id )
			$id = key( self::$Forms );

		return $post_id === (int) self::$Forms[$id]['hookpage'];
	}
	
	/* = AFFICHAGE = */
	/** == Dans le contenu de la page == **/
	public static function display( $id = null, $content, $echo = true )
	{
		if( ! $id )
			$id = key( self::$Forms );
		
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

    	return $output;
	}
}