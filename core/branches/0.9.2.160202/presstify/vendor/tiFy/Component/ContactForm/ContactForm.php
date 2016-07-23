<?php
namespace tiFy\Component\ContactForm;

use tiFy\Environment\App;

abstract class ContactForm extends App
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe
	protected $ID;
	
	// Identifiant du formulaire
	protected $FormID;
	
	// HTML affiché avant le formulaire ( note: Ajoutez %s pour afficher le contenu de la page ).
	protected $FormBefore			= '';
	
	// HTML affiché après le formulaire ( note: Ajoutez %s pour afficher le contenu de la page ).
	protected $FormAfter			= '';
	
	// Champs du formulaire
	protected $FormFields			= array();
	
	// Options du formulaire
	protected $FormOptions			= array();
	
	// Boutons du formulaire
	protected $FormButtons			= array();
	
	// Addons du formulaire
	protected $FormAddons			= array();
	
	// Options du formulaire de contact
	protected $Options				= array();
	
	// Actions à déclencher	
	protected $CallActions			= array( 'tify_options_register_node', 'tify_taboox_register_form', 'tify_form_register' );
	
	// Filtres à déclencher
	protected $CallFilters			= array();
	
	// Liste des attributs pouvant être récupéré
	protected $GetAttrs				= array( 'ID', 'Options' );
	
	// Liste des attributs pouvant être défini
	protected $SetAttrs				= array( 'ID', 'FormID', 'FormBefore', 'FormAfter', 'FormFields', 'FormOptions', 'FormButtons', 'FormAddons', 'Options' );
	
	// Instanciation de la classe			
	private static $instance 		= 0;
	
	// Liste des Methodes
	private $Methods				= array();
	
			
	/* = CONSTRUCTEUR = */
	public function __construct( $id = null )
	{		
		// Définition de l'identifiant de la classe	
		$this->ID = ( ! empty( $id ) ) ? $id : ( is_subclass_of( $this, __CLASS__ ) ? get_class( $this ) :  get_class() .'-'. ++self::$instance );
		$this->ID = preg_replace( '/\\\/', '_', $this->ID );
	
		// Configuration
		$this->FormID 			= $this->setFormID();
		$this->FormBefore 		= $this->setFormBefore();
		$this->FormAfter 		= $this->setFormAfter();
		$this->FormFields 		= $this->setFormFields();
		$this->FormOptions		= $this->setFormOptions();
		$this->FormButtons		= $this->setFormButtons();
		$this->FormAddons		= $this->setFormAddons();
		$this->Options			= $this->setOptions();		
					
		// Actions et Filtres Wordpress
		if( $this->Options['hookpage'] && $this->Options['auto'] )
			array_push( $this->CallFilters, 'the_content' );
		
		parent::__construct();
	}
	
	/* = DÉFINITION DE DONNÉES = */
	/** == Définition de l'ID du formulaire == **/
	public function setFormID()
	{
		 return $this->ID;
	}
	
	/** == Définition du HTML affiché avant le formulaire == **/
	public function setFormBefore()
	{
		 return $this->FormBefore;
	}
	
	/** == Définition du HTML affiché après le formulaire == **/
	public function setFormAfter()
	{
		 return $this->FormAfter;
	}
	
	/** == Définition des champs de formulaire == **/
	public function setFormFields()
	{
		return array(
			array(
				'slug'			=> 'lastname',
				'label' 		=> __( 'Nom', 'tify' ),
				'placeholder'	=> __( 'Renseignez votre nom', 'tify' ),
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'record' => array(
						'column' => true
					)
				)
			),
			array(
				'slug'			=> 'firstname',
				'label' 		=> __( 'Prénom', 'tify' ),
				'placeholder'	=> __( 'Renseignez votre prénom', 'tify' ),
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'record' => array(
						'column' => true
					)
				)
			),
			array(
				'slug'			=> 'email',
				'label' 		=> __( 'Adresse mail', 'tify' ),
				'placeholder'	=> __( 'Indiquez votre adresse email', 'tify' ),
				'integrity_cb'	=> 'is_email',
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'record' => array(
						'column' => true
					)
				)
			),
			array(
				'slug'			=> 'subject',
				'label' 		=> __( 'Sujet du message', 'tify' ),
				'placeholder'	=> __( 'Sujet de votre message', 'tify' ),
				'type' 			=> 'input',
				'required'		=> true,
				'add-ons'		=> array(
					'cookie_transport' 	=> array( 'ignore' => true ),					
					'record' 			=> array(
						'column' => true
					)
				)
			),	
			array(
				'slug'			=> 'message',
				'label' 		=> __( 'Message', 'tify' ),
				'placeholder'	=> __( 'Votre message', 'tify' ),
				'type' 			=> 'textarea',
				'required'		=> true,
				'add-ons'		=> array(
					'cookie_transport' 	=> array( 'ignore' => true ),
					'record' 			=> array(
						'column' => true
					)
				)						
			),	
			array(
				'slug'			=> 'captcha',
				'label' 		=> __( 'Code de sécurité', 'tify' ),
				'placeholder'	=> __( 'Code de sécurité', 'tify' ),
				'type' 			=> 'simple-captcha-image',					
			)
		);
	}

	/** == Définition des options du formulaire == **/
	public function setFormOptions()
	{
		return array();
	}
	
	/** == Définition des boutons du formulaire == **/
	public function setFormButtons()
	{
		return array();
	}

	/** == Définition des addons du formulaire == **/
	private function setFormAddons()
	{
		$addons = array();	
		$addonClasses =  $this->getPrefixedMethods( 'setFormAddon_' ); 
		foreach( (array) $addonClasses as $Class ) :
			$name = strtolower( preg_replace( '/^setFormAddon_/', '', $Class ) );
			if( $options = call_user_func( array( $this, $Class ) ) )
				$addons[$name] = $options;
			else
				array_push( $addons, $name );
		endforeach;
		
		return $addons;
	}
	
	/** == Définition de l'addon d'enregistrement en base du formulaire == **/
	public function setFormAddon_Record()
	{
		
	}
	
	/** == Définition de l'addon d'envoi du formulaire par email == **/
	public function setFormAddon_Mailer()
	{
		return array(					
			'debug' => false,
			'confirmation' => array(
				'send' 		=> ( get_option( $this->ID .'-confirmation', 'on' ) === 'on' ) ? true : false,
				'from' 		=> get_option( $this->ID .'-sender' ),
				'to' 		=> array( array( 'email' => '%%email%%', 'name' => '%%firstname%% %%lastname%%' ) ),			
				'subject' 	=> __( get_bloginfo( 'blogname' ).' | Votre message a bien été réceptionné', 'tify' )
			),
			'notification' => array(
				'send' 		=> ( get_option( $this->ID .'-notification', 'off' ) === 'on' ) ? true : false,
				'from' 		=> array( 'name' => get_bloginfo( 'blogname' ), 'email' => get_option( 'admin_email' ) ),			
				'to' 		=> get_option( $this->ID .'-recipients' ),
				'subject' 	=> __( get_bloginfo( 'blogname' ).' | Vous avez reçu une nouvelle demande de contact', 'tify' )
			)
		);
	}
	
	/** == Définition des options du  == **/
	public function setOptions()
	{
		return array
		(
			// Liaison du formulaire à une page
			'hookpage'	=> true,
			// Affichage automatique du formulaire dans le contenu de la page
			'auto'		=> true
		);
	}
	
	
	/* = RÉCUPERATION DE DONNÉES = */
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Affichage du formulaire de contact == **/
	public function the_content( $content )
	{			
		if( ! $this->isPageFor() )
			return $content;
		if( ! in_the_loop() )
			return $content;
		
		return $this->the_contentDisplay( $content, false );
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == OPTIONS == **/
	/*** === Déclaration de l'entrée de gestion des options de formulaire === ***/
	protected function tify_options_register_node()
	{
		\tify_options_register_node(
			array(
				'id' 		=> 'tify_options_node-'. $this->ID,
				'title' 	=> __( 'Formulaire de contact', 'tify' ),
				'cb'		=> 'tiFy\Component\ContactForm\Taboox\Option\ContactForm',
			)
		);
	}
		
	/*** === Déclaration de la boîte de saisie des options === ***/
	protected function tify_taboox_register_form()
	{
		\tify_taboox_register_form( 'tiFy\Component\ContactForm\Taboox\Option\ContactForm', $this );
	}
	
	/** == FORMULAIRES == **/
	/** == Déclaration du formulaire de contact == **/
	protected function tify_form_register()
	{
		\tify_form_register(		
			array(
				'ID' 				=> $this->FormID,
				'title' 			=> __( 'Formulaire de contact', 'tify' ),
				'prefix' 			=> 'tify_contact_form',
				'container_class'	=> 'tify_form_container tify_contact_form',
				'fields' 			=> apply_filters( 'tify_contact_form_fields', $this->FormFields ),
				'options' 			=> apply_filters( 'tify_contact_form_options', $this->FormOptions ),
				'buttons'			=> apply_filters( 'tify_contact_form_buttons', $this->FormButtons ),
				'add-ons' 			=> apply_filters( 'tify_contact_form_addons', $this->FormAddons ) 
			)
		);
	}
	
	/* = CONTROLEUR = */
	/** == == **/
	protected function isPageFor( $post_id = null )
	{			
		if( is_null( $post_id ) && \is_singular() )
			$post_id = get_the_ID();

		if( ! $post_id )
			return false;
				
		return( (int) get_option( 'page_for_'. $this->ID, 0 ) === (int) $post_id );
	}
	
	/** == == **/
	private function getPrefixedMethods( $prefix )
	{
		if( ! $this->Methods )
			$this->Methods = get_class_methods( $this );
		
		return preg_grep( '/^'. $prefix .'(\w)*/', $this->Methods );		
	}
	
	/* = AFFICHAGE = */
	/** == Dans le contenu de la page == **/
	final public function the_contentDisplay( $content, $echo = true )
	{
		$output  = "";
		$output .= sprintf( apply_filters( 'tify_contact_form_before', $this->FormBefore ), $content );
		$output .= \tify_form_display( $this->FormID, false );
		$output .= sprintf( apply_filters( 'tify_contact_form_after', $this->FormAfter ), $content );
		
		if( $echo )
			echo $output;
		else
			return $output;
	}	
}