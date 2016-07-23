<?php
class tiFy_AdminView_EditForm{
	/* = ARGUMENTS = */
	public	// OPTIONS
			$query,
			$db,
			$uri,
			
			// CONFIGURATION
			/// Url de la page
			$base_url,
			/// Clef primaire
			$primary_key,
			/// Habilitations
			$cap 					= 'edit_posts',
			// Arguments et valeur par défaut d'un élément
			$item_defaults			= array(),
			
			// PARAMETRES
			/// Arguments de l'élément courant
			$item;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $query ){
		$this->query 	= $query;
		$this->db		= $query->db;		
		$this->uri 		= plugin_dir_url( __FILE__ );
		
		// Configuration
		$this->primary_key = $this->db->primary_key;
	}
	
	/* = TRAITEMENT DES ARGUMENTS DE REQUETE = */
	/** == Déclenchement à l'affichage de l'écran courant == **/
	final public function _wp_current_screen( $current_screen ){
		$this->process_bulk_actions();
		
		// Mise en file des scripts
		wp_enqueue_style( 'tify_admin_view-edit_form', $this->uri .'/edit_form.css', array(), 151211 );		
		
		if( method_exists( $this, 'current_screen' ) )
			call_user_func( array( $this, 'current_screen' ), $current_screen );
	}
	
	/** == Récupération de l'élément à traité == **/
	public function current_item() {
		if ( ! empty( $_REQUEST[$this->primary_key] ) )
			return (int) $_REQUEST[$this->primary_key];

		return 0;
	}
	
	/** == Récupération de l'action courante == **/
	public function current_action() {		
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];
		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}
	
	/** == Récupération de la notification courante == **/
	public function current_notification(){
		if( ! empty( $_REQUEST['message'] ) && isset( $this->notifications[$_REQUEST['message']] ) )
			return array( wp_parse_args( $this->notifications[$_REQUEST['message']], array( 'message' => '', 'type' => 'error', 'dismissible' => false ) ) );		
	}
	
	/* = PARAMETRAGE = */	
	/** == Préparation de l'object à éditer == **/
	public function prepare_item(){
		if( $query_item = $this->query->query( array( $this->primary_key => $this->current_item() ) ) )
			return $this->item = current( $query_item );
	}
	
	/** == Liste des champs == **/
	public function get_fields() {
		$f = array();
		foreach( (array) $this->query->db->get_col_names() as $name )
			$f[$name] = $name;
		
		return $f;
	}
	
	/* = TRAITEMENT DES DONNEES  = */
	/** == Éxecution des actions == **/
	protected function process_bulk_actions(){		
		// Vérification des habilitations
		if( ! current_user_can( $this->cap ) )
			wp_die( __( 'Vous n\'êtes pas autorisé à modifier ce contenu.', 'tify' ) );

		// Traitement de l'élément courant
		if( ! $item_id = $this->current_item() )
			$item_id = $this->get_default_item_to_edit();

		// Vérification
		if( ! $item_id )
			wp_die( __( 'ERREUR SYSTEME : Impossible de créer un nouvel élément', 'tify' ) );
		elseif( ! $this->db->get_item_by_id( $item_id ) )
			wp_die( __( 'Vous tentez de modifier un contenu qui n’existe pas. Peut-être a-t-il été supprimé ?!', 'tify' ) );

		// Traitement des actions
		if( ! $this->current_item() ) :
			wp_safe_redirect( add_query_arg( $this->primary_key, $item_id ) );
			exit;
		elseif( method_exists( $this, 'process_bulk_action_'. $this->current_action() ) ) :
			call_user_func( array( $this, 'process_bulk_action_'. $this->current_action() ) );
		elseif( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $_REQUEST['_wp_http_referer'] ) );
			exit;
		endif;	
	}
	
	/** == Création d'un élément par défaut == **/
	protected function get_default_item_to_edit(){
		return $this->db->insert_item( wp_parse_args( $this->item_defaults, array( $this->primary_key => 0 ) ) );
	}
	
	/** == Éxecution de l'action - mise à jour == **/
	protected function process_bulk_action_update(){		
		check_admin_referer( $this->db->table . $this->current_action() . $this->current_item() );
				
		$data = $this->parse_postdata( $_POST );
		
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( array( 'action' => 'edit', $this->item_request => $this->current_item() ), $sendback );
		if( is_wp_error( $data ) ) :
			$sendback = add_query_arg( array( 'message' => $data->get_error_code() ), $sendback );	
		else :		 
			$this->db->insert_item( $data );			
			$sendback = add_query_arg( array( 'message' => 'updated' ), $sendback );			
		endif;
	
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - mise à la corbeille == **/
	protected function process_bulk_action_trash(){
		check_admin_referer( $this->db->table . $this->current_action() . $this->current_item() );
			
		// Traitement de l'élément				
		/// Conservation du statut original
		if( $this->db->has_meta && ( $original_status = $this->db->get_item_var_by_id( $this->item_id, 'status' ) ) )
			$this->db->update_item_meta( $this->item_id, '_trash_meta_status', $original_status );					
		/// Modification du statut
		$this->db->update_item( $this->item_id, array( 'status' => 'trash' ) );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'trashed', $sendback );
											
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Traitement des données de requete == **/
	protected function parse_postdata( $postdata ){
		return $postdata;
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de donnée de la table primaire == **/
	public function get_field( $field ){
		return $this->query->get_field( $field );
	}
	
	/** == Récupération d'une valeur de metadonnée == **/
	public function get_meta( $meta_key ){
		return $this->query->get_meta( $meta_key );
	}
	
	/* = AFFICHAGE */
	/** == Affichage des messages de notifications == **/
	public function notifications(){
		$output = "";
		if ( $notifications = $this->current_notification() )
			foreach( $notifications as $i => $n )
				$output .= "<div id=\"{$n['type']}-{$i}\" class=\"notice notice-{$n['type']}". ( $n['dismissible'] ? ' is-dismissible' : '' ) ."\"><p>{$n['message']}</p></div>";
			
		echo $output;
	}
	
	/** == Champs cachés == **/
	public function hidden_fields(){
	}
	
	/** == Affichage de l'interface de saisie == **/
	public function display(){
	?>
		<div style="margin-right:300px; margin-top:20px;">
			<div style="float:left; width: 100%;">
				<?php $this->form();?>				
			</div>
			<div style="margin-right:-300px; width: 280px; float:right;">
				<?php $this->submitdiv();?>
			</div>
		</div>
	<?php
	}
	
	/** == Formulaire de saisie == **/
	public function form(){
		return $this->display_rows();
	}
		
	/** == Affichage des champs de saisie sous forme de table == **/
	public function display_rows(){
	?>
		<table>
			<tbody>
			<?php 
				foreach( (array) $this->get_fields() as $field_name => $title ) :
					$this->display_row( $field_name, $title );
				endforeach;
			?>
			</tbody>
		</table>
	<?php	 
	}
	
	/** == Affichage d'une ligne de saisie == **/
	public function display_row( $field_name, $title ){
	?>
		<tr>
			<th><?php echo $title;?></th>
			<?php
			if ( method_exists( $this, 'field_' . $field_name ) ) : 
				echo "<td>";
				echo call_user_func( array( $this, 'field_' . $field_name ), $this->item );
				echo "</td>";
			else :
				echo "<td>";
				echo $this->field_default( $this->item, $field_name );
				echo "</td>";
			endif;
			?>
		</tr>
	<?php	
	}
	
	/** == Affichage des champs de saisie par défaut == **/
	public function field_default( $item, $field_name ){
		// Bypass
		if( ! isset( $item->{$field_name} ) )
			return;
		if( $field_name === $this->primary_key )
			return "#{$item->{$field_name}}";		
			
		$col_type = strtoupper( $this->query->db->get_col_attr( $field_name, 'type' ) );
        
        switch( $col_type ) :
            default:				
				return "<input type=\"text\" name=\"{$field_name}\" value=\"{$item->{$field_name}}\"/>";
				break;
			case 'DATETIME' :
				return "<input type=\"datetime\" name=\"{$field_name}\" value=\"{$item->{$field_name}}\"/>";
				break;
			case 'BIGINT' :	
			case 'INT' :
				return "<input type=\"number\" name=\"{$field_name}\" value=\"{$item->{$field_name}}\"/>";
				break;	
			case 'LONGTEXT' :
				return "<textarea name=\"{$field_name}\"/>{$item->{$field_name}}</textarea>";
				break;	
		endswitch;		
	}
	
	/** == Affichage de la boîte de soumission du formulaire == **/
	public function submitdiv(){
	?>
		<div id="submitdiv" class="tify_submitdiv">
			<?php wp_nonce_field( $this->db->table .'update'. $this->item->{$this->primary_key} ); ?>
			<input type="hidden" id="hiddenaction" name="action" value="update" />
			<input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id();?>" />
			<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url( wp_get_referer() ); ?>" />		
			<input type="hidden" id="<?php echo $this->primary_key;?>" name="<?php echo $this->primary_key;?>" value="<?php echo $this->item->{$this->primary_key};?>" />
			<h3 class="hndle">
				<span><?php _e( 'Enregistrer', 'tify' );?></span>
			</h3>
			<div class="inside">
				<div class="minor_actions">
					<?php $this->minor_actions();?>
				</div>	
				<div class="major_actions">
					<?php $this->major_actions();?>
				</div>	
			</div>
		</div>			
	<?php
	}
	
	/** == Affichage des actions secondaire de la boîte de soumission du formulaire == **/
	public function minor_actions(){}
	
	/** == Affichage des actions principale de la boîte de soumission du formulaire == **/
	public function major_actions(){
	?><div class="updating"><?php submit_button( __( 'Enregistrer', 'tify' ), 'primary', 'submit', false );?></div><?php
	}
}