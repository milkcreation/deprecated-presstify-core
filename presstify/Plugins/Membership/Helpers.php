<?php
namespace{
	function tify_membership_has_account( $user_id = 0 )
	{
		return tiFy\Plugins\Membership\Capabilities::has_account( $user_id );
	}
	
	function tify_membership_status( $user_id = 0 )
	{
		return tiFy\Plugins\Membership\Capabilities::get_status( $user_id );
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
	
	class Walker_Membership_RoleDropdown extends Walker 
	{
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
}