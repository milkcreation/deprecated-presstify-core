<?php
/* = Déclaration d'une boîte à onglets =
--------------------------------------
add_action( 'tify_taboox_register_box', [function_register_box] );  
function function_register_box( ){
	tify_taboox_register_box(
		[hookname],						// (string) @see get_current_screen()->id
 		[env]							// (string) option | post | taxonomy | user
 		array(
 			'title'		=> ''			// (optionnel) Titre de la boîte à onglet,
			'page'		=> ''			// @todo A DOCUMENTER				
 		),
		
 	);
}
*/
function tify_taboox_register_box( $hookname, $env, $args = array() ){
	global $tify_taboox;	
	return $tify_taboox->register_box( $hookname, $env, $args );
}

/* = Déclaration d'un section de boîte à onglets =
------------------------------------------------
add_action( 'tify_taboox_register_node', [function_register_node] );  
function function_register_node( ){
	tify_taboox_register_node(
 		[screen],									// (string) @see get_current_screen()->id
		array( 
	 		'id' 			=> false,				// (string) requis
			'parent'		=> false,				// id de la section de boîte à onglets parente	
			'title' 		=> '',					// (string)	requis
			'order'			=> 99					// Ordre d'affichage de la section
						
			'cb' 			=> __return_null(),		// Fonction de callback
			'args' 			=> array(),				// 

			// @todo
			'capability'	=> 'manage_options',	// Habilitation d'accès à la section
 			'nested'		=> array()				// Callback des interfaces de saisie incluses 			
 		)
 	)
}    
*/
function tify_taboox_register_node( $hookname, $args = array() ){
	global $tify_taboox;		
	return $tify_taboox->register_node( $hookname, $args );
}

/* = Déclaration d'une interface de saisie personnalisée =
--------------------------------------------------------
add_action( 'tify_taboox_register_form', '[function_register_form]' );
function function_register_form( [taboox_RegisterFormClass], $passed_args = array() ){
	tify_taboox_register_form( [taboox_RegisterFormClass], $passed_args );
}
*/
function tify_taboox_register_form( $class, $passed_args = array() ){
	global $tify_taboox;	
	return $tify_taboox->register_form( $class, $passed_args );
}

/*
class taboox_RegisterFormClass extends tiFy_Taboox{
	// (requis) Constructeur
	public function __construct( $passed_args ){	
		parent::__construct(
			// Options
			array(
				'allowed_env'		=> array( 'option', 'post', 'taxonomy', 'user' ),		// Environnements valides
				'dir'				=> dirname( __FILE__ ),									// Répertoire
				'max_inst'  		=> 1													// Nombre d'instance possible
			)
		);
	}
 	
	// (requis) Formulaire de saisie
	public function form(){}

	// (optionel env:post uniquement) Action lancée lors de la sauvegarde des posts
	public function save_post( $post_id, $post ){
		return $post_id;
	}
	
	// (optionel env:option uniquement) Action lancée lors de la sauvegarde des options
	public function sanitize_option( $value, $option ){
		return $value;
	}
	
	// (optionel env:option uniquement) Action lancée lors de la sauvegarde des options pour le [name] déclaré
	public function sanitize_option_[option_name]( $value, $option ){
		return $value;
	}
}
*/

/** == == **/
function tify_taboox_display(){
	global $tify_taboox;
	
	call_user_func_array( array( $tify_taboox->factory, 'box_render' ), func_get_args() );
}
