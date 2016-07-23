<?php
class tiFy_Membership_Template{
	public	// Contrôleurs
			$master;
			
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Membership $master ){
		$this->master = $master;
	}
	
	function the_content( $content ){
		$view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'home'; 
		
		// Reset Content
		$content  = "";
		
		switch( $view ) :
			default :
			case 'home' :
				if( ! is_user_logged_in() ) :
					$content .= $this->login_form();
					$content .= $this->lostpassword_button() ."&nbsp;". $this->subscribe_button();
				else :
					$content .= $this->account_button() ."&nbsp;". $this->logout_button();
				endif;
					
				return apply_filters( 'tify_membership_view_default', $content );	
				break;
			case 'subscribe' :			
				if( is_user_logged_in() )
					$content .= __( 'Vous êtes déjà connecté', 'tify' );
				else
					$content .= $this->subscribe_form();
							
				return apply_filters( 'tify_membership_view_subscribe', $content );
				break;
			case 'account' :
				if( ! is_user_logged_in() )
					$content .= __( 'Cet espace est réservé aux utilisateurs connectés', 'tify' );
				elseif( ! $this->master->capabilities->has_account() )
					$content .= __( 'Cet espace est réservé aux utilisateurs possédant un compte accès pro.', 'tify' );
				else
					$content .= $this->subscribe_form();
								
				return apply_filters( 'tify_membership_view_subscribe', $content );
				break;
		endswitch;
		 
		return $content;		
	}

	/** == Formulaire d'authentification == **/
	function login_form( $args = array() ){		
		$defaults = array(
			'redirect' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		// Force le retour plutôt que l'affichage	
		$args['echo'] = false;
		
		$output  = "";
		$output .= wp_login_form( $args );
		
		return apply_filters( 'tify_membership_login_form', $output );
	}
	
	/** == Bouton de récupération de mot de passe oublié == **/
	function lostpassword_button( $args = array() ){
		$defaults = array(
			'redirect' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$output  = "";
		$output .= "<a href=\"". wp_lostpassword_url( $args['redirect'] ) ."\" title=\"". __( 'Récupération de mot de passe oublié', 'tify' ) ."\">". __( 'Mot de passe oublié', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_lostpassword_button', $output );
	}
	
	/** == Bouton de déconnection == **/
	function logout_button( $args = array() ){
		$defaults = array(
			'redirect' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$output  = "";
		$output .= "<a href=\"". wp_logout_url( $args['redirect'] ) ."\" title=\"". __( 'Déconnection du forum', 'tify' ) ."\">". __( 'Se déconnecter', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_logout_button', $output );
	}
	
	/** == Formulaire d'inscription == **/
	function subscribe_form(){
		return  $this->master->tify_forms->display( $this->master->form_id );
	}
	
	/** == Bouton d'inscription == **/
	function subscribe_button( $args = array() ){
		$defaults = array(
			'url' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$subscribe_link = esc_url( add_query_arg( array( 'view' => 'subscribe' ), $args['url'] ) );
		
		$output  = "";
		$output .= "<a href=\"". $subscribe_link ."\" title=\"". __( 'Inscription à l\'accès pro.', 'tify' ) ."\">". __( 'S\'inscrire', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_subscribe_button', $output );
	}
	
	/** == Bouton d'inscription == **/
	function account_button( $args = array() ){
		$defaults = array(
			'url' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$account_link = esc_url( add_query_arg( array( 'view' => 'account' ), $args['url'] ) );
		
		$output  = "";
		$output .= "<a href=\"". $account_link ."\" title=\"". __( 'Modification des paramètres du compte', 'tify' ) ."\">". __( 'Modifier mes paramètres', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_account_button', $output );
	}
}


/** == Liste déroulante des campagne == **/
function tify_membership_role_dropdown( $args = array() ){
	global $tify_membership;
	
	$defaults = array(
		'show_option_all' 	=> '', 
		'show_option_none' 	=> '',
		'echo' 				=> 1,
		'selected' 			=> 0,
		'name' 				=> 'role', 
		'id' 				=> '',
		'class' 			=> 'tify_membership_role_dropdown', 
		'tab_index' 		=> 0,
		'hide_if_empty' 	=> false, 
		'option_none_value' => -1
	);

	$r = wp_parse_args( $args, $defaults );
	$option_none_value = $r['option_none_value'];

	$tab_index = $r['tab_index'];

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";
	$_roles = array();
	$roles = array_keys( $tify_membership->roles );	
	foreach( $roles as $role )
		$_roles[]= (object) array( 'ID' => $role );

	$name = esc_attr( $r['name'] );
	$class = esc_attr( $r['class'] );
	$id = $r['id'] ? esc_attr( $r['id'] ) : $name;

	if ( ! $r['hide_if_empty'] || ! empty( $_roles ) )
		$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
	else
		$output = '';
	
	if ( empty( $_roles ) && ! $r['hide_if_empty'] && ! empty( $r['show_option_none'] ) ) 
		$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>{$r['show_option_none']}</option>\n";


	if ( ! empty( $_roles ) ) :
		if ( $r['show_option_all'] ) 
			$output .= "\t<option value='0' ". ( ( '0' === strval( $r['selected'] ) ) ? " selected='selected'" : '' ) .">{$r['show_option_all']}</option>\n";

		if ( $r['show_option_none'] )
			$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' ". selected( $option_none_value, $r['selected'], false ) .">{$r['show_option_none']}</option>\n";
		$walker = new Walker_Membership_RoleDropdown;
		$output .= call_user_func_array( array( &$walker, 'walk' ), array( $_roles, -1, $r ) );
	endif;

	if ( ! $r['hide_if_empty'] || ! empty( $_roles ) )
		$output .= "</select>\n";

	if ( $r['echo'] )
		echo $output;

	return $output;
}

class Walker_Membership_RoleDropdown extends Walker {
	public $db_fields = array ( 'id' => 'ID', 'parent' => '' );

	public function start_el( &$output, $role, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_roles;
		
		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $role->ID ) . "\"";
		if ( $role->ID === $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= translate_user_role( $wp_roles->role_names[$role->ID] );
		$output .= "</option>\n";
	}
}