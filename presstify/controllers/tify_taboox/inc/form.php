<?php
/* = CLASSE DES ZONES DE SAISIE = */
class tiFy_Taboox{
	/* = ARGUMENTS = */
	protected 	// Configuration
				/// Chemins
				$dir,						// Chemins absolu vers le repertoire
				$uri,						// Url absolue vers le repertoire				
				$allowed_env,				// @todo Environnement autorisé ( option | post | taxonomy | user )
				$inst_max;					// @todo Nombre d'instance maximum
	
	public		// PARAMETRES
				/// Environnement
				$screen,					// Objet écran
				$page,						
				$env,								
				$args,						// 
				$inst 			= 0,  		// Instance courante	
				
				/// Données 				 
				$data_name,					
				$data_key,
				$data_single	= true,
				$data_value,
				$defaults;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $opts = array() ){		
		$this->_get_path();
		
		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, '_wp_admin_init' ) );
		add_action( 'current_screen', array( $this, '_wp_current_screen' ) );
	}
	
	/* = METHODES PUBLIQUES = */
	/** == Initialisation global == **/
	public function admin_init(){}
	
	/** == Initialisation de la vue courante == **/
	public function current_screen( $current_screen ){}
	
	/** == Récupération de la valeur == **/
	//public function get_value(){}
	
	/** == Formulaire de saisie == **/
	//public function form(){}
			
	/** == Action lancée lors de la sauvegarde des options == **/
	public function sanitize_option( $value, $option ){
		return $value;
	}
	
	/** == Action lancée lors de la sauvegarde d'une option == 
	public function sanitize_option_$option( $value, $option ){
		return $value;
	}**/
	
	/** == Action lancée lors de la sauvegarde des posts == **/
	public function save_post( $post_id, $post ){
		return $post_id;
	}
			
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'administration == */
	public function _wp_admin_init(){	
		switch( $this->env ) :
			case 'option' :
				// Déclaration des options
				if( $this->data_key )			
					register_setting( $this->page, $this->data_key );
				
				// Nettoyage des options
				if( $this->data_key )
					add_filter( 'sanitize_option'. $this->data_key, array( $this, '_wp_sanitize_option' ), 10, 2 );
				break;
			case 'post' :				
				// Sauvegarde des post
				add_action( 'save_post', array( $this, '_wp_save_post' ), 10, 2 );
				break;
			case 'taxonomy' :
				break;
			case 'user' :
				break;
		endswitch;
		
		$this->admin_init();			
	}
	
	/** == Initialisation de l'ecran courant == */
	function _wp_current_screen( $current_screen ){
		if( $this->screen && ( $current_screen->id === $this->screen->id ) )
			call_user_func( array( $this, 'current_screen' ), $current_screen );
	}
	
	/** == == **/
	public function _wp_sanitize_option( $value, $option ){
		if( is_callable( array( $this, 'sanitize_option_'. $option ) ) )
			call_user_func( array( $this, 'sanitize_option_'. $option ), $value, $option );
		
		call_user_func_array(	array( $this, 'sanitize_option' ), array( $value, $option ) );
	}
	
	/** == == **/
	public function _wp_save_post( $post_id, $post ){
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	      return;
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.	
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
	      return;
		
		//Bypass
		if( ! isset( $_POST['post_type'] ) )
			return;
			
		// Contrôle des permissions
	  	if ( 'page' == $_POST['post_type'] )
	    	if ( ! current_user_can( 'edit_page', $post_id ) )
	        	return;
	  	else
	    	if ( ! current_user_can( 'edit_post', $post_id ) )
	        	return;
				
		if( ( ! $post = get_post( $post_id ) ) || ( ! $post = get_post( $post ) ) )
			return;	
		
		call_user_func_array(	array( $this, 'save_post' ), array( $post_id, $post ) );
	}	
	
	/* = METHODES PRIVÉES = */
	/** == Récupération des chemins == **/
	private function _get_path(){
		$reflection = new ReflectionClass( $this );
		$this->dir 	= dirname( $reflection->getFileName() );
		$this->uri 	= site_url( '/'. tify_get_relative_path( $this->dir ) );
	}
	
	/** == Traitement et retour du formulaire de saisie == **/
	public final function _form(){
		// Définition de l'instance courante	
		$this->inst++;
		
		// Récupération des arguments
		$get_args = func_get_args();
	
		// Récupération des données
		if( ! $this->data_value && $this->data_key )
			$this->data_value = call_user_func_array( array( $this, '_get_value' ), $get_args );		
		
		call_user_func_array( array( $this, 'form' ), $get_args );			
	}
	
	/** == Récupération automatique de la valeur == **/
	private final function _get_value( ){
		if( $this->data_value )
			return $this->data_value;
		
		switch( $this->env ) :
			case 'option' :
				if( is_callable( array( $this, 'get_value' ) ) )
					return call_user_func( array( $this, 'get_value' ) );
				else
					return get_option( $this->data_key, $this->defaults );
				break;
			case 'post' :
				// Traitement des arguments de la fonction			
				$post = func_get_arg(0);
				
				if( is_callable( array( $this, 'get_value' ) ) )
					return call_user_func( array( $this, 'get_value' ), $post );
				else			
					return get_post_meta( $post->ID, $this->data_key, $this->data_single );
				break;
			case 'taxonomy' :
				// Traitement des arguments de la fonction
				$term = func_get_arg(0);
				$taxonomy = func_get_arg(1);
				
				if( is_callable( array( $this, 'get_value' ) ) )
					return call_user_func( array( $this, 'get_value' ), $term, $taxonomy );
				else
					return get_term_meta( $term->term_id, $this->data_key, $this->data_single );
				break;
			case 'user' :
				break;
		endswitch;
	}
}