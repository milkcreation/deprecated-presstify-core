<?php
namespace tiFy\Core\Forms\Form;

use tiFy\Core\Forms\FieldTypes;

class Field
{
	/* = ARGUMENTS = */
	// Configuration
	/// Instance
	private static $Instance;
	
	/// Attributs par défaut
	private	$DefaultAttrs		= array(			
		// DOM
		/// Identifiant HTML du conteneur
		'container_id' 		=> '',		
		/// Classe HTML du conteneur
		'container_class' 	=> '',
		/// Identifiant HTML de l'intitulé	
		'label_id' 			=> '',
		/// Classe HTML de l'intitulé	
		'label_class' 		=> '',
		/// Identifiant HTML de l'interface de saisie
		'input_id'			=> '',
		/// Classe HTML de l'interface de saisie
		'input_class' 		=> '',
		/// Contenu HTML affiché avant le champs
		'before'			=> '',
		/// Contenu HTML affiché après le champs	
		'after'				=> '',	
			
		// Hiérarchisation 
		'group'				=> 0,			
		'order'				=> 0,
		'page'				=> 1,
		
		// Attributs label
		'label'				=> true,
			
		// Attributs HTML
		'type'				=> 'text',
		'name'				=> '',
		'value'				=> '',
		'placeholder'		=> '',
		'tabindex'			=> 0,
		'readonly'			=> false,				
		'autocomplete'		=> 'on',
		'onpaste'			=> false,
		'pattern'			=> false,
		
		// Valeurs multiples	
		'choices'			=> array(),
		'choice_none' 		=> '',
		'choice_all' 		=> '',	
			
		// Traitement
		/// Le champs est requis
		/// bool | string : Message d'erreur personnalisé | array( 'tagged' => true, 'check' => true, html5 => true, 'error' => 'message d'erreur perso' ); **/
		'required'			=> false, 
		/// Tests d'intégrité	
		/// string | array( 'function' => [function_name], 'args' => array( $arg1, $arg2, ... ), 'error' => 'message d'erreur personnalisé' ) | array( array( 'function' ... ), array( ... ) )
		'integrity_cb' 		=> false, 	
		///
		'transport'			=> true,		
			
		// Addons et options
		'add-ons'			=> array(),
		'options'			=> array()
	);
	
	// Paramétrage
	// Objet formulaire de référence
	private $Form				= null;
	
	// Objet type de champ de référence
	private $Type				= null;
	
	/// Object addons
	private $Addons				= array();
	
	/// Attributs de configuration
	private $Attrs				= array();
		
	/// Options
	private $Options			= array();
			
	/// Attribut de champs requis
	private $Required			= null;
	
	/// Fonctions de rappel de test d'intégrité
	private $IntegrityCBs		= null; 
	
	/// Valeur du champ
	private $Value				= null;
	
	/// Indice du champ
	private $Index				= 0;
		
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\Forms\Form\Form $Form, $attrs = array() )
	{
		// Définition du formulaire de référence
		$this->Form = $Form;
		
		// Définition de l'index
		$this->Index = self::$Instance++;
		
		// Défintion des attributs
		$this->Attrs = Helpers::parseArgs( $attrs, $this->DefaultAttrs );
		
		// Définition des attributs		
		if( empty( $this->Attrs['slug'] ) ) :
			$this->Attrs['slug'] = "field-slug_". $this->Form->getUID() ."-". $this->getIndex();
		endif;
		
		// Définition du type de champ
		$this->_setType();
		
		// Définition des attributs d'addons
		$this->_setAddons();
		
		// Définition des options
		$this->_setOptions();
		
		// Définition de la valeur du champ
		$this->_setValue();
				
		// Court-circuitage des
		$this->Form->call( 'field_set_params', array( &$this ) );
	}
		
	/* = PARAMETRAGE = */
	/** == Définition des attributs == **/
	private function _setAttrs( $field = array() )
	{							
		/*
		 * @todo
		// Incrémentation des liste choix
		if( $field['choices'] && is_array( $field['choices'] ) ) :
			if( isset( $field['choices'][0] ) ) :
				array_unshift( $field['choices'], null );
				unset( $field['choices'][0] ) ;
			endif;
		endif;
		*/
	}
	
	/** == Définition des attributs d'addons == **/
	private function _setAddons()
	{
		$attrs = $this->getAttr( 'add-ons' );
		
		foreach( (array) $this->form()->addons() as $id => $addon ) :
			$this->Addons[$id] = $addon;
			$addon->setField( $this, ( isset( $attrs[$id] ) ? (array) $attrs[$id] : array() ) );			
		endforeach;
	}
	
	/** == Définition des options de champ == **/
	private function _setOptions()
	{		
		$this->type()->initOptions( $this->Attrs[ 'options' ] );
	}
	
	/** == Définition du type de champ == **/
	private function _setType()
	{
		$this->Type = FieldTypes::set( $this->getAttr( 'type' ), $this );
	}
	
	/** == Définition du type de champ == **/
	private function _setValue()
	{
		$value = $this->getAttr( 'value' );

		if( $this->getAttr( 'html' ) ) :
			// @todo DEPRECATED à signifier
			$value = $this->getAttr( 'html' );
		endif;
		
		// Court-circuitage de l'initialisation de la valeur du champ
		$this->Form->call( 'field_set_value', array( &$value, $this ) );
		
		$this->Value = $value;
	}
	
	/* = PARAMETRES = */	
	/** == Réinitialisation de l'instance == **/
	public static function resetInstance()
	{
		self::$Instance = 0;
	}
	
	/** == Récupération de l'indice du champ == **/
	public function getIndex()
	{
		return $this->Index;	
	}
	
	/** == Récupération de la liste des attributs de champ == **/
	public function getAttrs()
	{		
		return $this->Attrs;
	}
		
	/** == Récupération d'un attribut de champ == **/
	public function getAttr( $attr = 'ID' )
	{		
		if( isset( $this->Attrs[$attr] ) )
			return $this->Attrs[$attr];
	}
	
	/** == Définition d'un attribut de champ == **/
	public function setAttr( $attr, $value )
	{		
		return $this->Attrs[$attr] = $value;
	}
	
	/** == Récupération de l'identifiant unique == **/
	public function getSlug()
	{
		return $this->getAttr( 'slug' );
	}
	
	/** == Récupération de l'identifiant unique == **/
	public function getLabel()
	{
		return $this->getAttr( 'label' );
	}
		
	/** == Récupération de la valeur du champ == **/
	public function getValue( $raw = false )
	{				
		$value = $this->Value;

		// Court-circuitage de la valeur du champ
		$this->Form->call( 'field_get_value', array( &$value, $this ) );
				
		// Sécurisation des valeurs
		if( ! $raw )
			$value = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( $value );
		
		return $value;
	}
	
	/** == Définition de la valeur d'un champ == **/
	public function setValue( $value )
	{
		$this->Value = $value;
	}
	
	/** == Récupération de la valeur du champ == **/
	public function getDisplayValue( $raw = false, $glue = ', ' )
	{				
		$value = (array) $this->getValue();
		
		if( $choices = $this->getAttr( 'choices' ) ) :
			foreach( $value as &$v ) :
				if( isset( $choices[$v] ) ) :
					$v = $choices[$v];
				endif;
			endforeach;
		endif;
		
		if( $raw )
			$value = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( $value );
		
		$value = join( ', ', $value );			
			
		return $value;
	}
	
	/** == Récupération de l'indice d'enregistrement du champ de saisie == **/
	public function getName()
	{
		if( ! empty( $this->getAttr( 'name' ) ) ) :
			$name = $this->getAttr( 'name' );
		else :
			$name = $this->getSlug();
		endif;
		
		return $name;
	}
		
	/** == Récupération de l'attribut name du champ de saisie affiché  == **/
	public function getDisplayName()
	{
		return sprintf( '%s[%s]', $this->form()->getUID(), $this->getName() );
	}
	
	/** == Identifiant du type de champ == **/
	public function getType()
	{
		return $this->type()->getID();
	}
			
	/** == Récupération des attributs de champ requis == **/
	public function getRequired( $attr )
	{
		if( ! $this->Required ) :
			$defaults = array(
				// Affichage de l'indicateur de champs requis
				'tagged'		=> false,
				// Vérification d'existance du champs au moment du traitement
				'check'			=> false,
				// Court-circuitage de la soumission via HTML5
				'html5'			=> false,
				// Message d'erreur
				'error'			=> __( 'Le champ "%s" doit être renseigné.', 'tify' )	
			);
			
			$required = $this->getAttr( 'required' );
			if( is_bool( $required ) ) :
				$args = array( 'tagged' => $required, 'check' => $required );
			elseif( is_string( $required ) ) :
				$args = array( 'tagged' => true, 'check' => true, 'error' => $required );
			else :
				$args = (array) $args;
			endif;
			$this->Required = wp_parse_args( $args, $defaults );
		endif;
		
		if( isset( $this->Required[ $attr ] ) )
			return $this->Required[ $attr ];
	}
	
	/** == Vérifie qu'une valeur de champs à bien été saidie == **/
	public function isRequired()
	{
		return $this->getRequired( 'check' );
	}
	
	/** == == **/
	public function getTabIndex()
	{
		if( $tabindex = $this->getAttr( 'tabindex' ) ) :
			return (int) $tabindex;
		else :
			return $this->setAttr( 'tabindex', $this->form()->increasedTabIndex() );
		endif;
	}
	
	/** == Récupération des tests d'intégrités == **/
	public function getIntegrityCallbacks()
	{
	
		if( ! is_null( $this->IntegrityCBs ) )
			return $this->IntegrityCBs;
	
		if( ! $callbacks = $this->getAttr( 'integrity_cb' ) )
			return $this->IntegrityCBs = array();
				
		return $this->IntegrityCBs = $this->_parseIntegrityArgs( $callbacks );
	}
	
	/** == Traitement des attributs de test d'intégrité == **/
	private function _parseIntegrityArgs( $callbacks, $output = array(), $depth = 0 )
	{	
		$defaults = array(
			'function'	=> '__return_true',
			'args'		=> array(),
			'error'		=> __( 'Le format du champ "%s" est invalide', 'tify' )
		);

		//if( $depth < 2 ) :
			if( is_string( $callbacks ) ) :
				$callbacks = array_map( 'trim', explode( ',', $callbacks ) );
				foreach( $callbacks as $cb ) :
					$output[] = wp_parse_args( array( 'function' => $cb ), $defaults );
				endforeach;			
			elseif( is_array( $callbacks ) ) :
				if( is_callable( $callbacks ) ) :				
					$output[] = wp_parse_args( array( 'function' => $callbacks ), $defaults );
				elseif( isset( $callbacks[ 'function'] ) ) :					
					$output[] = wp_parse_args( $callbacks, $defaults );
				else :	
					foreach( (array) $callbacks as $cb ) :
						$output += $this->_parseIntegrityArgs( $cb, $output, ++$depth );
					endforeach;				
				endif;
			endif;
		//endif;
		
		return $output;
	}	
	
	/* = CONTROLEURS = */
	/** == Récupération de l'objet formulaire de référence == **/
	public function form()
	{
		return $this->Form;
	}
	
	/** == Récupération de l'objet type de champ == **/
	public function type()
	{
		return $this->Type;
	}
	
	/** == Récupération de l'objet type de champ == **/
	public function addons()
	{
		return $this->Addons;
	}
		
	/* = ALIAS = */
	/** == Identifiant du formulaire de référence == **/
	public function formID()
	{
		return $this->form()->getID();
	}
			
	/** == Vérification du support pour le type de champ == **/
	public function typeSupport( $support )
	{
		return $this->type()->isSupport( $support );
	}
	
	/** == == **/
	public function getAddonAttr( $id, $attr, $default = '' )
	{
		if( isset( $this->Addons[$id] ) )
			return $this->Addons[$id]->getFieldAttr( $this, $attr, $default );
		
		return $default;		
	}
	
	/** == == **/
	public function getOptions()
	{
		return $this->type()->getOptions();
	}
	
	/** == Récupération d'une option == **/
	public function getOption( $option, $default = '' )
	{
		return $this->type()->getOption( $option, $default );
	}
	
	/** == Définition d'une option == **/
	public function setOption( $option, $value )
	{		
		return $this->type()->setOption( $option, $value );
	}
	
	/* = AFFICHAGE = */	
	/** == Affichage du champ == **/
	public function display()
	{
		// Court-circuitage des propriétés de champs avant son affichage
		//Callbacks::call( 'field_before_display', array( &$this ) );		
		
		// Affichage du champs
		$output  = "";
		
		if( $this->typeSupport( 'wrapper' ) ) :
			$class = array();
			$class[] = 'tiFyForm-FieldContainer';
			$class[] = 'tiFyForm-FieldContainer--'. $this->getType();
			$class[] = 'tiFyForm-FieldContainer--'. $this->getSlug();
			$class[] = $this->getAttr( 'container_class' );			
			if( $this->getAttr( 'required' ) )
				$class[] = 'tiFyForm-FieldContainer--required';
							
			$output .= "<div". ( ( $container_id = $this->getAttr( 'container_id' ) ) ? ' id="'. $container_id .'"': '' ) ." class=\"". join( ' ', $class ) ."\">\n"; 
		endif;
		
		$output .= $this->type()->_display();
		
		
		if( $this->typeSupport( 'wrapper' ) ) :
			$output .= "</div>\n"; 
		endif;
					
		// Fonction de court-circuitage de l'affichage du champ
		//Callbacks::call( 'field_output_display', array( &$output, $this ) );		
						
		return $output;
	}	
}