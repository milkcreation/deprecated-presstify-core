<?php
class tiFy_WebAgencyCRM_MainTemplate{
	/* = ARGUMENTS = */
	private	// Références
				$master;

	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Déclaration des Références
		$this->master = $master;	
	}
	
	/* = CONTROLEUR = */
	/** == Liste déroulante des clients == **/
	public function customer_dropdown( $args = array() ){
		global $wpdb;
		
		static $instance = 0;
		$instance ++;
				
		$defaults = array(
			'show_option_all' 	=> '', 
			'show_option_none' 	=> '',
			'option_none_value' => -1,			
			'orderby' 			=> 'user_registered', 
			'order' 			=> 'DESC',			
			'echo' 				=> 1,
			'selected' 			=> 0,
			'name' 				=> 'customer_id', 
			'id' 				=> 'tify_wacrm_customer_dropdown-'. $instance,
			'class' 			=> 'tify_wacrm_customer_dropdown', 
			'tab_index' 		=> 0			
		);
	
		$r = wp_parse_args( $args, $defaults );
		$option_none_value = $r['option_none_value'];
	
		$tab_index = $r['tab_index'];
	
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		
		// Requête de récupération
		$query_args = array();
		$query_args['orderby'] 		= $r['orderby'];
		$query_args['order'] 		= $r['order'];
		
		$query_args['count_total']	= true;
		$query_args['fields'] 		= 'all_with_meta';
		$query_args['meta_query']	= array(
			array(
		    	'key' => $wpdb->get_blog_prefix( get_current_blog_id() ) . 'capabilities',
		    	'value' => '"(' . implode('|', array_map( 'preg_quote', array_keys( $this->master->roles ) ) ) . ')"',
		    	'compare' => 'REGEXP'
			)
		);
		$wp_user_query = new WP_User_Query( $query_args );
        $items = $wp_user_query->get_results();
		
		$name = esc_attr( $r['name'] );
		$class = esc_attr( $r['class'] );
		$id = $r['id'] ? esc_attr( $r['id'] ) : $name;
	
		if ( ! empty( $items ) )
			$output = "<select name=\"{$name}\" id=\"{$id}\" class=\"{$class}\" {$tab_index_attribute}>\n";
		else
			$output = '';
		
		if ( empty( $items ) && ! empty( $r['show_option_none'] ) ) 
			$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>{$r['show_option_none']}</option>\n";
	
	
		if ( ! empty( $items ) ) :
			if ( $r['show_option_all'] ) 
				$output .= "\t<option value='0' ". ( ( '0' === strval( $r['selected'] ) ) ? " selected='selected'" : '' ) .">{$r['show_option_all']}</option>\n";
	
			if ( $r['show_option_none'] )
				$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' ". selected( $option_none_value, $r['selected'], false ) .">{$r['show_option_none']}</option>\n";
			$walker = new tiFy_WebAgencyCRM_Walker_DropdownCustomer;
			$output .= call_user_func_array( array( &$walker, 'walk' ), array( $items, -1, $r ) );
		endif;
	
		if ( ! empty( $items ) )
			$output .= "</select>\n";
	
		if ( $r['echo'] )
			echo $output;
	
		return $output;
	}
}

class tiFy_WebAgencyCRM_Walker_DropdownCustomer extends Walker {
	public $db_fields = array ( 'id' => 'ID', 'parent' => '' );

	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $item->{$this->db_fields['id']} ) . "\"";
		if ( $item->{$this->db_fields['id']} == $args['selected'] )
			$output .= " selected=\"selected\"";
		$output .= ">";			
		$output .= wp_unslash( $item->nickname );
		$output .= "</option>\n";
	}
}