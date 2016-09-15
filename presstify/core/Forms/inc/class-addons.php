<?php
Class tiFy_Forms_Addons{
	/* = ARGUMENTS = */
	public	// Configuration
			$default_form_options 	= array(),
			$default_field_options	= array(),
			
			// Paramètres			
			$addons = array(),
			$inherit,
			$active,			
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forms $master ){
		// Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
    }
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){}
	
	/** == Définition des options d'addons par défaut des formulaires == 
	 * @param $addon_id
	 * @param $options
	 **/
	public function set_default_form_options( $addon_id, $options ){
		$this->default_form_options[$addon_id] = $options;
	}
	
	/** == Définition des options d'addons par défaut des champs de formulaire ==
	 * @param $addon_id
	 * @param $options 
	 **/
	public function set_default_field_options( $addon_id, $options ){
		$this->default_field_options[$addon_id] = $options;
	}	
	
	/* = PARAMETRAGE = */
	/** == Initialisation == **/
	public function init( ){
		$this->_get();		
	} 
	
	/** == Déclaration d'un addon == **/
	public function register( $addon_id, $callback, $filename = null, $args = array() )
	{
		if( array_keys( $this->master->registred_addon, $addon_id ) )
			return;
	
		if( $filename && file_exists( $filename ) )
			require_once $filename;	
			
		if ( ! class_exists( $callback ) )
			return;
		
		$this->master->registred_addon[$addon_id] = $callback;
		
		$this->addons[$addon_id] 				= array();
		$this->addons[$addon_id]['callback'] 	= $callback;
		$this->addons[$addon_id]['args'] 		= $args;

    	new $callback( $this->master, $args );		
	}
	
	/** == Récupération de la liste des addons depuis le répertoire de dépôt == **/
	private function _get( ){
		$addon_root = $this->master->dir .'/addons/';		
		$addons_dir = @ opendir( $addon_root );

		$addon_files = array();
		if ( $addons_dir ) :
			while ( ( $file = readdir( $addons_dir ) ) !== false ) :
				if ( substr( $file, 0, 1 ) == '.' )
					continue;				
				if ( is_dir( $addon_root .'/'. $file ) ) : 
					$addons_subdir = @ opendir( $addon_root .'/'. $file );
					if ( $addons_subdir ) :
						while ( ( $subfile = readdir( $addons_subdir ) ) !== false ) :
							if ( substr( $subfile, 0, 1 ) == '.' )
								continue;
							if ( substr( $subfile, -4 ) == '.php' )
								$addon_files[] = "$file/$subfile";
						endwhile;
						closedir( $addons_subdir );
					endif;
				else :
					if ( substr($file, -4) == '.php' )
						$addon_files[] = $file;
				endif;
			endwhile;
			closedir( $addons_dir );
		endif;

		if ( empty( $addon_files ) )
			return $this->master->registred_addon;
	
		foreach ( $addon_files as $addon_file ) :
			if ( ! is_readable( "$addon_root/$addon_file" ) )
				continue;
	
			$addon_data = $this->get_data( "$addon_root/$addon_file" );
			
			if ( empty ( $addon_data['ID'] ) )
				continue;
			if ( empty ( $addon_data['Name'] ) )
				continue;
			if ( empty ( $addon_data['Callback'] ) )
				continue;		
			
			$this->register( $addon_data['ID'], $addon_data['Callback'], "$addon_root/$addon_file" );
		endforeach;
		
		return $this->master->registred_addon;
	}
	
	/** == Récupération des données d'un addon == **/
	function get_data( $addon_file, $markup = true, $translate = true ) {
		$default_headers = array(
			'Name' 			=> 'Addon Name',
			'ID' 			=> 'Addon ID',
			'Callback' 		=> 'Callback',
			'Version' 		=> 'Version',
			'Description' 	=> 'Description',
			'Author' 		=> 'Author',
			'AuthorURI' 	=> 'Author URI',
			'TextDomain' 	=> 'Text Domain',
			'DomainPath' 	=> 'Domain Path'
		);
	
		$addon_data = get_file_data( $addon_file, $default_headers );
	
		$addon_data['Title']      = $addon_data['Name'];
		$addon_data['AuthorName'] = $addon_data['Author'];
	
		return $addon_data;
	}		
	
	/**
	 * Permet de récupérer les options par défaut d'un add-on
	 * 
	 * @param string $addon (requis) Intitulé de l'add-on
	 */
	public function get_options( $addon ){
		if( isset( $this->addons[$addon]['options'] ) )
			return $this->addons[$addon]['options'];
	}
	
	/**
	 * Initialisation des addons de formulaire
	 */
	public function set_form(){
		$this->set_form_options( $this->master->forms->current['add-ons'] );
		// Initialisation des formulaire actifs selon leurs addons 
		foreach( $this->master->forms->current['add-ons'] as $key => $addon ) 
			$this->active[$key][] = $this->master->forms->current['ID'];
	}	
	
	/**
	 * Retourne les formulaires actifs pour un add-on
	 * 
	 * @param string $addon nom de l'add-on
	 * @return array Tableau indexés des IDs de formulaires actifs pour l'add-on requis
	 */
	public function get_forms_active( $addon ){
		if( isset( $this->active[$addon] ) )
			return $this->active[$addon];					
	}
	
	/**
	 * Retourne les formulaires actifs pour un add-on
	 * 
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Tableau indexés des IDs de formulaires actifs pour l'add-on requis
	 */
	public function get_active_for_form( $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return false;
		
		$active = array();
		foreach( (array) $_form['add-ons'] as $key => $addon )
			if( is_int( $key ) )
				$active[] = $addon;
			elseif( is_string( $key) )
				$active[] = $key;

		return $active;			
	}
	
	/**
	 * Verifie si un formulaire est actif pour un add-on
	 * 	
	 * @param string $addon nom de l'add-on
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant  
	 * 
	 * @return boolean Vrai si l'add-on est actif pour le formulaire requis
	 */
	public function is_form_active( $addon, $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return false;
		
		// Récupération de la liste des formulaire actif pour cet add-on
		if( ( $forms = $this->get_forms_active( $addon ) ) && in_array( $_form['ID'], $forms ) )
			return true;
		
		return false;					
	}
		
	/**
	 * Récupération de toutes les options d'un add-on pour un formulaire.
	 * 
	 * @param string $addon nom de l'add-on
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Tableau dimensionné des options de l'add-on pour le formulaire requis
	 */
	public function get_form_options( $addon, $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return false;

		if( isset( $_form['add-ons'][$addon] ) )
			return $_form['add-ons'][$addon];		
	}
	
	/** == Définition des addons d'un formulaire ==  
	 * @param array $addons Tableau indexé des add-ons
	 * @see $this->set_addon pour connaître la syntaxe des attributs par défaut d'un add-on 
	 **/
	private function set_form_options( &$addons = array() ){		
		foreach( $addons as $key => &$addon ) :	
			if( is_string( $addon ) ) :
				unset( $addons[$key] );
				$addons[$addon] = $this->master->functions->parse_options( array(), array( 'active' => true ) );
			else :
				$addon = $this->master->functions->parse_options( $addon, array( 'active' => true ) );
			endif;
			
		endforeach;
		foreach( $addons as $key => &$addon ) 
			if( isset( $this->default_form_options[$key] ) )				
				$addon = $this->master->functions->parse_options( $addons[$key], $this->default_form_options[$key] );
		
		$this->master->callbacks->call( 'addon_set_form_options', array( &$addons, $this->master ) );
	}
	
	/**
	 * Récupération d'une option d'un add-on pour un formulaire.
	 * 
	 * @param string $option nom de l'option
	 * @param string $addon nom de l'add-on	 
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Tableau dimensionné des options de l'add-on pour le formulaire requis
	 */
	public function get_form_option( $option, $addon, $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return false;

		if( isset( $_form['add-ons'][$addon][$option] ) )
			return $_form['add-ons'][$addon][$option];		
	}	

	/**
	 * Récupération des options d'un add-on pour un champ de formulaire.
	 * 
	 * @param array $field (requis) Tableau dimensionné du champ
	 * @param string $addon (requis) Nom de l'add-on	
	 * 
	 * @return array Tableau dimensionné des options de l'add-on pour le champs de formulaire requis
	 */
	public function get_field_options( $field, $addon ){
		if( isset( $field['add-ons'][$addon] ) )
			return $field['add-ons'][$addon];	
	}
	
	/**
	 * Mise à jour des options de champs d'un add-on
	 * @param string $addon (requis) Intitulé de l'add-on
	 **/
	public function set_field_options( &$addons_field_options ){
		// Bypass
		if( ! $addons = $this->get_active_for_form( ) )
			return;
		foreach( $addons as $addon ) :
			if( ! isset( $addons_field_options[$addon] ) )	
				$addons_field_options[$addon] = array();
			if( isset( $this->default_field_options[$addon] ) )	
				$addons_field_options[$addon] = $this->master->functions->parse_options( $addons_field_options[$addon], $this->default_field_options[$addon] );
		endforeach;				
		$this->master->callbacks->call( 'addon_set_field_options', array( &$addons_field_options , $this->master ) );
		
		return $addons_field_options;
	}
	
	/**
	 * Récupération d'une option d'un add-on pour un champ de formulaire.
	 * 
	 * @param string $option (requis) nom de l'option
	 * @param string $addon (requis) nom de l'add-on	 
	 * @param array $field (requis) Tableau dimensionné du champ
	 * 
	 * @return array Tableau dimensionné des options de l'add-on pour le formulaire requis
	 */
	public function get_field_option( $option, $addon, $field ){
		if( isset( $field['add-ons'][$addon][$option] ) )
			return $field['add-ons'][$addon][$option];		
	}
}

Class tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	public	// Configuration
			$ID,						// Identifiant de l'addon
	 		$default_form_options,		// Options de formulaire par défaut 
	 		$default_field_options,		// Option de champ par défaut
	 		$callbacks,					// Fonction de callback
	 				
		 	// Paramètres
			$form_options,
			$field_options,
			
			// Contrôleurs
			$master;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Définition de l'ID
		$this->_setID();
		
		// Initialisation de la configuration
		$this->_config();
		
		// Initialisation de la configuration
		$this->_callbacks();				
    }
	
	/* = CONFIGURATION = */
	/** == Définition de l'ID == **/
	private function _setID(){
		if( $this->ID )
			return;
		$classname = get_class( $this );

		if( ! $this->ID = array_search( $classname, $this->master->registred_addon ) )
			$this->ID = $classname;
	}
	
	/** == Initialisation == **/
	private function _config(){		
		// Définition des options par défaut
		$this->master->addons->set_default_form_options( $this->ID, $this->default_form_options );
		$this->master->addons->set_default_field_options( $this->ID, $this->default_field_options );		
	}
	
	/** == Déclaration des callbacks == **/
	private function _callbacks(){
		foreach( (array) $this->callbacks as $hookname => $args ) :
			if( is_callable( $args ) ) :
				$this->master->callbacks->addons_set( $hookname, $this->ID, $args );
			elseif( isset( $args['function'] ) &&  is_callable( $args['function'] ) ) :
				$args = wp_parse_args( $args, array( 'order' => 10 ) );
				$this->master->callbacks->addons_set( $hookname, $this->ID, $args['function'], $args['order'] );
			endif;
		endforeach;
	}
	
	/* = CONTROLEURS = */
	/** == == **/
	function get_form_option( $option ){
		return $this->master->addons->get_form_option( $option, $this->ID );
	}
}
