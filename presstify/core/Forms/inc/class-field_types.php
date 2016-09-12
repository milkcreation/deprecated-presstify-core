<?php
Class tiFy_Forms_FieldTypes{
	/* = ARGUMENTS = */
	public	// Configuration
			$sections,
			$types,
			
			// Paramètres
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
	}
		
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){
		$this->set_sections();
		$this->set_types();
	}
		
	/** == Définition des sections == **/
	private function set_sections( $sections = array() ){
	 	$defaults = array(
			'text' 			=> __( 'Chaines de caractères', 'tify' ),
			'input-fields' 	=> __( 'Champs de saisie', 'tify' ), 
			'selection' 	=> __( 'Listes de selection', 'tify' ),
			'misc' 			=> __( 'Eléments riches', 'tify' )
		);
		$this->sections = wp_parse_args( $sections, $defaults );
	}
	 
	/** == Définition des types de champs == **/
	private function set_types(){
		$defaults = array(
			array(
				'slug'			=> 'html',
				'label' 		=> __( 'HTML', 'tify' ),
				'section' 		=> 'text',
				'order' 		=> 1,
				'supports'		=> array( )				 
			),
			array(
				'slug'			=> 'string',
				'label' 		=> __( 'Chaîne de caractères', 'tify' ),
				'section' 		=> 'text',
				'order' 		=> 1,
				'supports'		=> array( )				 
			),
			array(
				'slug'			=> 'input',
				'label'			=> __( 'Champ texte', 'tify' ),
				'section' 		=> 'input-fields',
				'order' 		=> 1,
				'supports'		=> array( 'label', 'placeholder', 'integrity-check', 'request' )
			),
			array(
				'slug'			=> 'textarea',
				'label'			=> __( 'Textarea', 'tify' ),
				'section' 		=> 'input-fields',
				'order' 		=> 2,
				'supports'		=> array( 'label','placeholder', 'integrity-check', 'request' )
			),
			array(
				'slug'			=> 'password',
				'label'			=> __( 'Mot de passe', 'tify' ),
				'section' 		=> 'input-fields',
				'order' 		=> 3,
				'supports'		=> array( 'label', 'placeholder', 'integrity-check', 'request' )
			),
			array(
				'slug'			=> 'hidden',
				'label' 		=> __( 'Champ caché', 'tify' ),
				'section' 		=> 'input-fields',
				'order' 		=> 4,
				'supports'		=> array( 'request' )
			),
			array(
				'slug'			=> 'radio',
				'label' 		=> __( 'Bouton radio', 'tify' ),
				'section' 		=> 'selection',
				'order' 		=> 1,
				'supports'		=> array( 'label', 'choices', 'integrity-check', 'request' )
			),
			array(
				'slug'			=> 'checkbox',
				'label' 		=> __( 'Case à cocher', 'tify' ),
				'section' 		=> 'selection',
				'order' 		=> 2,
				'supports'		=> array( 'label', 'choices', 'multiselect', 'integrity-check', 'request' )
			),
			array(
				'slug'			=> 'dropdown',
				'label' 		=> __( 'Liste déroulante', 'tify' ),
				'section' 		=> 'selection',
				'order' 		=> 3,
				'supports'		=> array( 'label', 'choices', 'integrity-check', 'request' )
			),
			array(
				'slug'			=> 'select-multiple',
				'label' 		=> __( 'Liste déroulante à choix multiples', 'tify' ),
				'section' 		=> 'selection',
				'order' 		=> 4,
				'supports'		=> array( 'label', 'choices', 'multiselect', 'integrity-check', 'request' )
			)						
		);
				
		return $this->types = $defaults;
	}

	/* = PARAMETRAGE = */
	/** == Initialisation == **/
	public function init( ){
		$this->_get();		
	}
	
	/** == Déclaration d'un type de champ == **/
	public function register( $fieldtype_id, $callback, $filename = null ){
		if( array_keys( $this->master->registred_addon, $fieldtype_id ) )
			return;
		if( $filename && file_exists( $filename ) )
			require_once $filename;		
		if ( ! class_exists( $callback ) )
			return;
		
		$this->addons[$fieldtype_id] 				= array();
		$this->addons[$fieldtype_id]['callback'] 	= $callback;

    	new $callback( $this->master );		
	}
	
	/** == Récupération de la liste des types de champ depuis le répertoire de dépôt == **/
	private function _get( ){
		$fieldtype_root = $this->master->dir .'/field_types/';		
		$fieldtypes_dir = @ opendir( $fieldtype_root );

		$fieldtype_files = array();
		if ( $fieldtypes_dir ) :
			while ( ( $file = readdir( $fieldtypes_dir ) ) !== false ) :
				if ( substr( $file, 0, 1 ) == '.' )
					continue;				
				if ( is_dir( $fieldtype_root .'/'. $file ) ) : 
					$fieldtypes_subdir = @ opendir( $fieldtype_root .'/'. $file );
					if ( $fieldtypes_subdir ) :
						while ( ( $subfile = readdir( $fieldtypes_subdir ) ) !== false ) :
							if ( substr( $subfile, 0, 1 ) == '.' )
								continue;
							if ( substr( $subfile, -4 ) == '.php' )
								$fieldtype_files[] = "$file/$subfile";
						endwhile;
						closedir( $fieldtypes_subdir );
					endif;
				else :
					if ( substr($file, -4) == '.php' )
						$fieldtype_files[] = $file;
				endif;
			endwhile;
			closedir( $fieldtypes_dir );
		endif;

		if ( empty( $fieldtype_files ) )
			return $this->master->registred_field_type;
	
		foreach ( $fieldtype_files as $fieldtype_file ) :
			if ( ! is_readable( "$fieldtype_root/$fieldtype_file" ) )
				continue;
	
			$fieldtype_data = $this->get_data( "$fieldtype_root/$fieldtype_file" );
			
			if ( empty ( $fieldtype_data['ID'] ) )
				continue;
			if ( empty ( $fieldtype_data['Name'] ) )
				continue;
			if ( empty ( $fieldtype_data['Callback'] ) )
				continue;			
			
			$this->master->registred_field_type[$fieldtype_data['ID']] = $fieldtype_data['Callback'];
			$this->register( $fieldtype_data['ID'], $fieldtype_data['Callback'], "$fieldtype_root/$fieldtype_file" );
		endforeach;
		
		return $this->master->registred_field_type;
	}
	
	/** == Récupération des données d'un type de champ == **/
	function get_data( $fieldtype_file, $markup = true, $translate = true ) {
		$default_headers = array(
			'Name' 			=> 'FieldType Name',
			'ID' 			=> 'FieldType ID',
			'Callback' 		=> 'Callback',
			'Version' 		=> 'Version',
			'Description' 	=> 'Description',
			'Author' 		=> 'Author',
			'AuthorURI' 	=> 'Author URI',
			'TextDomain' 	=> 'Text Domain',
			'DomainPath' 	=> 'Domain Path'
		);
	
		$fieldtype_data = get_file_data( $fieldtype_file, $default_headers );
	
		$fieldtype_data['Title']      = $fieldtype_data['Name'];
		$fieldtype_data['AuthorName'] = $fieldtype_data['Author'];
	
		return $fieldtype_data;
	}

	/** == == */
	public function set_type( $type = array() ){
		array_push( $this->types, $type );
	}
	
	/** == == **/
	public function has_type( $type, $form = null ){
		if( ! $fields = $this->master->fields->get_list( $form ) )
			return false;
		foreach( $fields as $f )
			if( $f['type'] == $type )
				return true;
		return false;
	}

	/** == == **/
	public function get_forms_has_type( $type ){
		$forms = array();
		if( ! $this->master->forms->get_list() )
			return;
		foreach( $this->master->forms->get_list() as $form )
			if( $this->has_type( $type, $form ) )
				$forms[] = $form['ID'];
		
		return $forms;
	}
	
	/** == Récupération des données d'un type == **/
	public function get_type_datas( $slug = '' ){
		foreach(  $this->types as  $type )
			if( $type['slug'] == $slug )
				return $type;
	}
	
	/** == Récupération de l'intitulé d'un type == **/ 
	private function get_type_label( $slug ){
		if( $type = $this->get_type_datas( $slug ) )
			return $type['label'];
	}
	
	/** == Vérification de support d'un type == **/
	public function type_supports( $attr = '', $type ){
		if( $type = $this->get_type_datas( $type ) )
			return in_array( $attr, $type['supports'] );
	}
	
	/** == Récupération des types d'une section == **/
	private function types_by_section( $section ){
	 	$_types = array();
	 	foreach( $this->types as $type )
			if( $section === $type['section'] )
				$_types[] = $type;
		return $_types;	
	}
}

Class tiFy_Forms_FieldType{
	/* = ARGUMENTS = */
	public	// Configuration
			$ID,							// Identifiant du type de champ
			$attrs 		= array(),			// Attributs du type de champ
	 		$callbacks	= array(),			// Fonction de callback
	 				
		 	// Paramètres
			
			// Contrôleurs
			$master;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Définition de l'ID
		$this->_setID();
		
		// Initialisation des attributs
		$this->_set_attrs();
		
		// Initialisation des fonction de callback
		$this->_callbacks();				
    }
	
	/* = CONFIGURATION = */
	/** == Définition de l'ID == **/
	private function _setID(){
		if( $this->ID )
			return;
		$classname = get_class( $this );

		if( ! $this->ID = array_search( $classname, $this->master->registred_field_type ) )
			$this->ID = $classname;
	}
	
	/** == Définition des attributs == **/
	private function _set_attrs(){
		$this->master->field_types->set_type( $this->attrs );
	}
		
	/** == Déclaration des callbacks == **/
	private function _callbacks(){
		foreach( (array) $this->callbacks as $hookname => $args ) :
			if( is_callable( $args ) ) :
				$this->master->callbacks->field_type_set( $hookname, $this->ID, $args );
			elseif( isset( $args['function'] ) &&  is_callable( $args['function'] ) ) :
				$args = wp_parse_args( $args, array( 'order' => 10 ) );
				$this->master->callbacks->field_type_set( $hookname, $this->ID, $args['function'], $args['order'] );
			endif;
		endforeach;
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération des options de formulaire == **/
	function get_form_option( $option ){
		return $this->master->addons->get_form_option( $option, $this->ID );
	}
}