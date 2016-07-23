<?php
class tiFy_Forum_OptionsMain{
	/* = ARGUMENTS = */
	public	// Paramètres
			$translate;
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Declaration de la classe de référence principale
		$this->master = $master;
		
		// Définition des options à translater
		$this->translate = array( 'require_name_email', 'contrib_registration', 'thread_contribs', 'page_contribs', 'contribs_notify', 'moderation_notify', 'contribs_moderation', 'contribs_whitelist' );
	}
	
	/* = CONTRÔLEURS = */
	/** == Options par défaut == **/
	public function get_defaults( $option = null ){
		$defaults = array(
			'contributor' => array(
				'double_optin'					=> 'on',
				'moderate_account_activation'	=> 'on'
			),
			'global' => array( 
				'require_name_email' 	=> 'off', 
				'contrib_registration' 	=> 'on', 
				'thread_contribs' 		=> 'off', 
				'thread_contribs_depth' => 'on', 
				'page_contribs' 		=> 'on', 
				'contribs_per_page' 	=> 20, 
				'default_contribs_page' => 'newest',
				'contribs_order' 		=> 'desc'  
			), 
			'email' => array( 
				'contribs_notify' 		=> 'off', 
				'moderation_notify' 	=> 'off' 
			),
			'moderation' => array( 
				'contrib_moderation'	=> 'on',
				'contrib_whitelist' 	=> 'off' 
			)
		);
		
		if( ! $option )
			return $defaults;
		elseif( isset( $defaults[$option] ) )
			return $defaults[$option];
	}

	/** == Récupération des options == **/
	public function get( $option, $sub = null, $translate = false ){
		if( ! $defaults = $this->get_defaults( $option ) )
			return;
		
		$option = wp_parse_args( get_option( $option, $defaults ) );
		
		if( ! $sub ) :
			if( $translate )
				foreach( $option as $k => &$v )
					$v = $this->translate( $k, $v );
			return $option;
		elseif( isset( $option[$sub] ) ) :
			return ( $translate ) ? $this->translate( $sub, $option[$sub] ) : $option[$sub];		
		endif;	
	}
	
	/** == Recupération des options de section == **/
	private function translate( $index, $value ){
		if( in_array( $index, $this->translate ) )
			$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		
		return $value;
	}	
}