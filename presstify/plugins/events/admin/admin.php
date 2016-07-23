<?php
class tiFy_EventsAdmin{
	/* = ARGUMENTS = */
	public	// contrôleur
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Events $master ){
		$this->master = $master;
		
		// Actions et Filtres Presstify
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) ); 
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );		
	}	
		
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == == **/
	public function tify_taboox_register_node(){		
		foreach( (array) $this->master->post_types as $post_type => $args )
			if( $args['taboox_auto'] )
				tify_taboox_register_node( 
					$post_type, 
					array( 
						'id' 		=> 'tify_events',
						'title' 	=> __( 'Dates', 'tify' ), 
						'cb' 		=> 'tiFy_Events_Taboox' 
					) 
				);
	}
	/** == Déclaration de la taboox de saisie des dates == **/
	public function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_Events_Taboox', $this->master );
	}	
}

/* = TABOOXES = */
/** == Taboox de saisie des dates == **/
class tiFy_Events_Taboox extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public	// contrôleur
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Events $master ){
		parent::__construct();
		$this->master = $master;
					
		// Actions et Filtres Wordpress
		add_action( 'post_edit_form_tag', array( $this, 'wp_post_edit_form_tag' ) );			
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	public function wp_post_edit_form_tag( $post ){
		// Bypass
		if( ! in_array( $post->post_type, $this->master->get_post_types() ) )
			return;
		echo " autocomplete=\"off\" ";
	}
	
	public function admin_init(){
		add_action( 'wp_ajax_tify_events_display_preview', array( $this, 'wp_ajax_display_preview' ) );	
	}
	
	/** == == **/
	public function wp_ajax_display_preview(){
		$event_id 		= $_POST['event_id'];
		$start_datetime	= ! empty( $_POST['start_datetime'] ) ? $_POST['start_datetime'] : null;
		$end_datetime	= ! empty( $_POST['end_datetime'] ) ? $_POST['end_datetime'] : null;	
		
		echo $this->preview( $event_id, $start_datetime, $end_datetime );
		exit;
	}
	
	
	public function current_screen( $screen ){
		tify_control_enqueue( 'touch_time' );
		tify_control_enqueue( 'dynamic_inputs' );
		wp_enqueue_style( 'tify_events', $this->master->uri .'admin/css/admin.css', array(), 150610 );
		wp_enqueue_script( 'tify_events', $this->master->uri .'admin/js/admin-default.js', array( 'jquery', 'moment' ), 150610, true );
		wp_localize_script( 
			'tify_events', 
			'tify_events', 
			array( 
				'date_range_error' => __( 'La date de début est supérieur à la date de fin', 'tify' )
			)
		);	
	}

	public function form( $post ){
		$values = $this->master->db->get_items( array( 'post_id' => $post->ID, 'order' => 'ASC', 'orderby' => 'start_datetime' ), ARRAY_A );
		
		// Default
		$values 		= $this->parse_values( $values );
		$sample_html 	= $this->sample_default();	
	
		$args = array( 
			'sample_html' 				=> $sample_html, 
			'name' 						=> 'tify_event', 
			'values' 					=> $values
		);
		
		$args['default'] = array(
			'event_start_date' 		=> date( 'Y-m-d', current_time( 'timestamp' ) ),
			'event_start_time' 		=> date( 'H:i:00', current_time( 'timestamp' ) ),
			'event_end_time' 		=> date( 'H:i:00', current_time( 'timestamp' ) ),
			'event_end_date' 		=> date( 'Y-m-d', current_time( 'timestamp' ) ),
			'event_post_id' 		=> $post->ID, 
			'event_id' 				=> 0 
		);
	?>
		<div class="tify_events-taboox">
			<?php tify_control_dynamic_inputs( $args ); ?>
		</div>
	<?php
	}
	
	/** == == **/
	function sample_default(){
		$sample_html  = "";
		$sample_html .= "<div class=\"col\">";					
		$sample_html .= "\t<table>\n";
		$sample_html .= "\t\t<tbody>\n";
		$sample_html .= "\t\t\t<tr>\n";
		$sample_html .= "\t\t\t\t<th scope=\"row\"><label>". __( 'Du', 'tify' ) ."</label></th>\n";
		$sample_html .= "\t\t\t\t<td>";
		$sample_html .= tify_control_touch_time( 
							array( 
								'name' 				=> '%%name%%[%%index%%][event_start_date]', 
								'value' 			=> '%%value%%[event_start_date]',
								'container_id' 		=> 'tify_event_start_date-wrapper-%%index%%',
								'container_class' 	=> 'tify_event_start_date-wrapper',
								'id' 				=> 'tify_event_start_date-%%index%%',
								'type'				=> 'date', 
								'echo' 				=> 0,
								'debug'				=> false
							) 
						);
		$sample_html .= "\t\t\t\t</td>\n";
		$sample_html .= "\t\t\t</tr>\n";
		$sample_html .= "\t\t</tbody>\n";
		$sample_html .= "\t</table>\n";
		
		$sample_html .= "\t<table>\n";
		$sample_html .= "\t\t<tbody>\n";
		$sample_html .= "\t\t\t<tr>\n";
		$sample_html .= "\t\t\t\t<td class=\"label from\"><label>". __( 'De', 'tify' ) ."</label></td>\n";
		$sample_html .= "\t\t\t\t<td class=\"hour\">";
		$sample_html .= tify_control_touch_time( 
							array( 
								'name' 				=> '%%name%%[%%index%%][event_start_time]', 
								'value' 			=> '%%value%%[event_start_time]',
								'container_id' 		=> 'tify_event_start_time-wrapper-%%index%%',
								'container_class' 	=> 'tify_event_start_time-wrapper',
								'id' 				=> 'tify_event_start_time-%%index%%',
								'type'				=> 'time', 
								'echo' 				=> 0,
								'debug'				=> false
							) 
						);
		$sample_html .= "\t\t\t\t</td>\n";
		$sample_html .= "\t\t\t\t<td class=\"label to\"><label>". __( 'A', 'tify' ) ."</label></th>\n";
		$sample_html .= "\t\t\t\t<td class=\"hour\">";
		$sample_html .= tify_control_touch_time( 
							array( 
								'name' 				=> '%%name%%[%%index%%][event_end_time]', 
								'value' 			=> '%%value%%[event_end_time]',
								'container_id' 		=> 'tify_event_end_time-wrapper-%%index%%',
								'container_class' 	=> 'tify_event_end_time-wrapper',
								'id' 				=> 'tify_event_end_time-%%index%%',
								'type'				=> 'time', 
								'echo' 				=> 0,
								'debug'				=> false
							) 
						);
		$sample_html .= "\t\t\t\t</td>\n";
		$sample_html .= "\t\t\t</tr>\n";
		$sample_html .= "\t\t</tbody>\n";
		$sample_html .= "\t</table>\n";
		
		$sample_html .= "\t<table>\n";
		$sample_html .= "\t\t<tbody>\n";
		$sample_html .= "\t\t\t<tr>\n";
		$sample_html .= "\t\t\t\t<th scope=\"row\"><label>";
		$sample_html .= "\t\t\t\t\t". __( 'Jusqu\'au', 'tify' ) ."\n";
		$sample_html .= "\t\t\t\t</label></th>\n";
		$sample_html .= "\t\t\t\t<td>";		
		$sample_html .= "\t\t\t\t\t". 
						tify_control_touch_time( 
							array( 
								'name' 				=> '%%name%%[%%index%%][event_end_date]', 
								'value' 			=> '%%value%%[event_end_date]',
								'container_id' 		=> 'tify_event_end_date-wrapper-%%index%%',
								'container_class' 	=> 'tify_event_end_date-wrapper',
								'id' 				=> 'tify_event_end_date-%%index%%',
								'type'				=> 'date',
								'echo' 				=> 0,
								'debug'				=> false
							) 
						) .
						"\n";
		$sample_html .= "\t\t\t\t</td>\n";
		$sample_html .= "\t\t\t</tr>\n";
		$sample_html .= "\t\t</tbody>\n";
		$sample_html .= "\t</table>\n";
		$sample_html .= "</div>\n";
		$sample_html .= "<div class=\"col preview\">\n".
						"\t<strong>". __( 'Prévisualisation :', 'tify' ) ."</strong>\n".
						"\t<textarea readonly=\"readonly\" autocomplete=\"off\"></textarea>\n".
						"</div>\n";
		$sample_html .= "<input type=\"hidden\" class=\"tify_event_id\" name=\"%%name%%[%%index%%][event_id]\" value=\"%%value%%[event_id]\">\n";
		$sample_html .= "<input type=\"hidden\" class=\"tify_event_post_id\"  name=\"%%name%%[%%index%%][event_post_id]\" value=\"%%value%%[event_post_id]\">\n";
		
		$sample_html .= apply_filters( 'tify_events_custom_fields', '' );
		
		return $sample_html;
	}
		
	/** == Sauvegarde des posts == **/
	public function save_post( $post_id, $post ){
		// Suppression des dates
		if( $exists = $this->master->db->get_items_ids( array( 'post_id' => $post_id ) ) ) :
			$save = array();
			if( isset( $_POST['tify_event'] ) )
				foreach( $_POST['tify_event'] as $event )
					array_push( $save, $event['event_id'] );
				
			foreach( $exists as $id )
				if( empty( $save ) )
					$this->master->db->delete_item( $id );
				elseif( ! in_array( $id, $save ) )
					$this->master->db->delete_item( $id );
		endif;
		// Enregistrement des dates
		if( ! empty( $_POST['tify_event'] ) ) :
			$datas = $this->parse_datas( $_POST['tify_event'] );	
			
			foreach( $datas as $id => $e ) :								
				$start = new DateTime( $e['event_start_datetime'] ); $end = new DateTime( $e['event_end_datetime'] );

				if( $start > $end ) :
					$e['event_end_datetime'] = $start->format( 'Y-m-d' ) .' '. $end->format( 'H:i:s' );
					$end = new DateTime( $e['event_end_datetime'] );
				endif;
				if( $start->format( 'Hi' ) > $end->format( 'Hi' ) ) :
					$end->add( new DateInterval( 'P1D' ) );
					$e['event_end_datetime'] = $end->format( 'Y-m-d H:i:s' );
				endif;

				$this->master->db->insert_item( $e );
			endforeach;
		endif;
	}
	
	/** == Traitement des données == **/
	public function parse_values( $values ){
		foreach( (array) $values as $k => $value ) :
			$s = new DateTime( $value['event_start_datetime'] );
			if( $s->format('Y') < 0 )
				$values[$k]['event_start_date'] = date( 'Y-m-d', current_time( 'timestamp' ) );
			else	
				$values[$k]['event_start_date'] = $s->format( 'Y-m-d' );
			$values[$k]['event_start_time'] = $s->format( 'H:i:s' );			
			unset( $values[$k]['event_start_datetime'] );
			
			$e = new DateTime( $value['event_end_datetime'] );
			if( $e->format( 'Hi' ) < $s->format( 'Hi' ) )
					$e->sub( new DateInterval( 'P1D' ) );			
			$values[$k]['event_end_date'] = $e->format( 'Y-m-d' );
			$values[$k]['event_end_time'] = $e->format( 'H:i:s' ); 
			unset( $values[$k]['event_end_datetime'] );
			
			if( $metas = $this->master->db->get_item_metas( $value['event_id'] ) ) 
				foreach( (array) $metas as $meta_key => $meta_value )
					$values[$k][$meta_key] = current( $meta_value );
		endforeach;
			
		return $values;	
	}
	
	private function preview( $event_id = 0, $start_datetime = null, $end_datetime = null ){
		$output = "";
		if( $e = $this->master->db->get_item_by_id( $event_id ) ) :			
			$start_datetime	= ! empty( $_POST['start_datetime'] ) ? $_POST['start_datetime'] : $e->event_start_datetime;
			$end_datetime	= ! empty( $_POST['end_datetime'] ) ? $_POST['end_datetime'] : $e->event_end_datetime;
			$post_type		= get_post_type( $e->event_post_id );
			$by_day_limit 	= $this->master->get_post_type_option( $post_type, 'by_day_limit' );
					
			if( $split_date_ranges = $this->master->split_date_range( $start_datetime, $end_datetime, $by_day_limit ) )
				foreach( $split_date_ranges as $split_date_range )
					$output .= $this->master->split_date_range_display( $split_date_range, $event_id, $e->event_post_id, false ) ."\n";
		endif;		
		
		return $output;
	}
	
	/** == Traitement des données == **/
	public function parse_datas( $datas ){
		foreach( $datas as $k => &$data ) :
			if( isset( $data['event_start_date'] ) && isset( $data['event_start_time'] ) ) :
				$data['event_start_datetime'] = $data['event_start_date'] ." ". $data['event_start_time']; 
				unset( $datas[$k]['event_start_date'] ); unset( $datas[$k]['event_start_time'] );
			endif;
			if( isset( $data['event_end_date'] ) && isset( $data['event_end_time'] ) ) :
				$data['event_end_datetime'] = $data['event_end_date'] ." ". $data['event_end_time'];
				unset( $datas[$k]['event_end_date'] ); unset( $datas[$k]['event_end_time'] ); 
			endif;
			if( empty( $data['event_start_datetime'] ) || empty( $data['event_end_datetime'] ) ) :
				unset( $datas[$k] ); continue;
			endif;			
		endforeach;
			
		return $datas;	
	}
}