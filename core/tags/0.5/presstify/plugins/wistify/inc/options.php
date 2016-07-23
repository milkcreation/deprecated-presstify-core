<?php
class tiFy_Wistify_Options_Main{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Declaration de la classe de référence principale
		$this->master = $master;
	}
	
	/* = CONTRÔLEURS = */
	/** == Options par défaut == **/
	public function get_defaults( $option = null ){
		$defaults = array(
			'wistify_global_options'		=> array( 
				'api_key' 			=> '',
				'api_test_key'		=> ''	
			),	
			'wistify_contact_information'	=> array( 
				'contact_name' 	=> '',
				'contact_email'	=> get_option( 'admin_email' ),
				'reply_to'		=> '',
				'company_name'	=> get_bloginfo( 'name' ),
				'website'		=> get_bloginfo( 'url' ),
				'address'		=> '',
				'phone'			=> ''			
			),
			'wistify_subscribe_form'	=> array(
				'title'			=> __( 'Inscription à la newsletter', 'tify' ),
				'label'			=> __( 'Email', 'tify' ),
				'placeholder'	=> __( 'Renseignez votre email', 'tify' ),
				'button'		=> __( 'Inscription', 'tify' ),
				'list_id'		=> 0,
				'success'		=> __( 'Un email de validation vous a été adressé, cliquez sur le lien de confirmation pour valider votre inscription', 'tify' )
			)
		);
		
		if( ! $option )
			return $defaults;
		elseif( isset( $defaults[$option] ) )
			return $defaults[$option];
	}

	/** == Récupération des options == **/
	public function get( $option, $sub = null ){
		if( ! $defaults = $this->get_defaults( $option ) )
			return;
		
		$option = wp_parse_args( get_option( $option, $defaults ) );
		if( ! $sub )
			return $option;
		elseif( isset( $option[$sub] ) )
			return $option[$sub];
	}	
}