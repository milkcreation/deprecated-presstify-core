<?php
class tiFy_Forms_Addon_RecordExport{
	/* = ARGUMENTS = */
	public	// Configuration
			$expiration,
			$default_options,
			
			// Contrôleur
			$main;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forms_Addon_Record $main ){
		$this->main = $main;
		
		// Options par défaut de l'export
		$this->default_options = array(
			'type' 		=> 'csv',
			'from' 		=> 0,
			'limit' 	=> -1,
			'show_num' 	=> 'y',
			'show_date' => 'y',	
			'fields' 	=> array()
		);
		
		// Expiration des fichiers d'export		
		$this->expiration = is_numeric( $this->main->master->dirs->locations['export']['cleaning'] ) ? $this->main->master->dirs->locations['export']['cleaning'] : 60*60;

		// Actions et Filtre Wordpress
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_tify_forms_records_count', array( $this, 'wp_ajax_count' ) );		
		add_action( 'wp_ajax_tify_forms_records_export', array( $this, 'wp_ajax_export' ) );
		add_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 10, 2 );
		add_action( 'tify_upload_register', array( $this, 'tify_upload_register' ) );	
	}
	
	/**
	 * Déclaration des scripts
	 */
	function wp_admin_enqueue_scripts( $hookname ){
		// Bypass
		if( $hookname != $this->main->hookname )
			return;

		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.10.4/themes/redmond/jquery-ui.css' );
		wp_enqueue_script( 'tify_forms_export-scripts', $this->main->uri .'export.js', array( 'jquery', 'jquery-ui-progressbar' ), 20140523, true );
		wp_localize_script( 'tify_forms_export-scripts', 
			'tify_forms_export', 
			array( 
				'progressLoading' => __( 'Chargement ...', 'tify' ), 
				'progressComplete' => __('Traitement terminé', 'tify' ) 
			)
		);
	}

	/**
	 * Compte de resultat via Ajax
	 */
	function wp_ajax_count(){
		// Bypass	
		if( 
			! isset( $_REQUEST['attrs']['form_id'] ) 
			|| !( $form_id = (int) $_REQUEST['attrs']['form_id'] )
			|| ! check_ajax_referer( 'tify_forms_export-'.$form_id, 'ajax_nonce' )
		)
			return;
			
		// Traitement des options par défaut	
		$options = wp_parse_args( $_REQUEST['options'], $this->default_options );
	
		$args = array(					
			'parent'=> $form_id,
			'limit' => ! empty( $options['limit'] )? $options['limit'] : -1,		
			'order' => 'ASC',
			'orderby' => 'ID'
		);
		echo json_encode( $this->main->db_count_items( $args ) );
		exit;
	}	
	
	/**
	 * Export via Ajax
	 */
	function wp_ajax_export(){
		// Bypass
		if( ! $this->main->master->forms->set_current( (int) $_REQUEST['form_id'] ) )
			return;
		
		$args = array(					
			'status'	=> 'publish',
			'parent'	=> $_REQUEST['form_id'],
			'per_page'	=> $_REQUEST['per_page'],
			'paged'		=> $_REQUEST['paged'],		
			'order' 	=> 'ASC',
			'orderby' 	=> 'ID'
		);
		
		// Récupération des enregistrements
		if( ! $records = $this->main->db_get_items( $args ) ) 
			return; 
			
		// Récupération des champs de metadonnées
		foreach( (array) $this->main->master->fields->get_list() as $slug => $field )
			if( in_array( $slug, $_REQUEST['fields'] ) )
		 		$fields[$field['slug']] = $field['label'];
		
		$filename = $this->main->master->dirs->dirname( 'export' ) .'/'. $_REQUEST['filename'];
		
		// Création des entête de colonnes
		if( ! $_REQUEST['offset'] ) :
			$fp = fopen( $filename, 'w' );
			
			if( $_REQUEST['options']['show_num'] == 'y' )
				$rows[0][] = 'Num.';
			if( $_REQUEST['options']['show_date'] == 'y' )
				$rows[0][] = 'Date';	
			foreach( $fields as $slug => $label )
		 		$rows[0][] = $fields[$slug] = $label;
		else :	
			$fp = fopen( $filename, 'a' );	
		endif;	
		
		// Création des lignes de données
		foreach( $records as $r ):
			$n = ++$_REQUEST['offset'];
			if( $_REQUEST['offset'] > $_REQUEST['total'] )
				break;
			if( $_REQUEST['options']['show_num'] == 'y' )
				$rows[$n][] = $n;
			if( $_REQUEST['options']['show_date'] == 'y' )
				$rows[$n][] = $r->record_date;
			foreach( $fields as $slug => $label )
				$rows[$n][] = $this->main->db_get_meta_item( $r->ID, $slug );
		endforeach;
	
		// Ecriture du fichiers csv
		foreach( $rows as $row )     
			fputcsv( $fp, $row, ';', '"' );
				
		fclose($fp);
		
		set_transient( 'tify_forms_record_export_allowed_file_upload', basename( $filename ), $this->expiration );
		exit;
	}

	/** == Autorisation de téléchargement du type de fichier == **/
	final public function upload_mimes( $mime_types, $user )
	{
		$mime_types['csv'] =  'text/csv';
		
		return $mime_types;
	}
	
	/** == Autorisation de téléchargement de fichier == **/
	final public function tify_upload_register()
	{
		if( ! $transient = get_transient( 'tify_forms_record_export_allowed_file_upload' ) )
			return;
		if( ! isset( $_REQUEST['_wpnonce'] ) )
			return;
		$filename = basename( tify_upload_get( 'url' ) );
		
		if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], "tify_forms_record_export-". $filename ) )
			return;

		if( $filename === $transient )
			tify_upload_register( $this->main->master->dirs->uri( 'export' ) .'/'. $filename );
	}
	
	
	/**
	 * Rendu de l'interface d'export
	 */
	function render(){
		$forms = $this->main->master->addons->get_forms_active( 'record' );
		$form_id = isset( $_REQUEST['form_id'] ) ? (int) $_REQUEST['form_id'] : ( ( ( count( $forms ) == 1 ) ) ? $forms[0] : 0 );
		
		if( $form_id )
			$this->main->master->forms->set_current( $form_id );
		else 
			return;
		
		$filename = sanitize_file_name( uniqid() ."-". $form_id ."-tify_forms_record_export_". date( "Y-m-d_H-i" ) ) . '.csv';
		?>
		<style text="text/css">
		.ui-progressbar {
			position: relative;
		}
		.progress-label {
			position: absolute;
			left: 50%;
			top: 4px;
			font-weight: bold;
			text-shadow: 1px 1px 0 #fff;
		}
		</style>

		<form id="tify_forms-export_form" method="post" action="">
			<?php wp_nonce_field( 'tify_forms_export-'.$form_id );?>	
			
			<input type="hidden" class="export-attrs" name="page" value="<?php echo @$_REQUEST['page'];?>" />				
			<input type="hidden" class="export-attrs" name="form_id" value="<?php echo $form_id;?>"/>
			<input type="hidden" class="export-attrs" name="filename" value="<?php echo $filename;?>" />
			<input type="hidden" class="export-options" name="from" value="<?php echo $this->default_options['from'];?>" />
			<input type="hidden" class="export-options" name="limit" value="<?php echo $this->default_options['limit'];?>" />
			
			<h3><?php _e( 'Options d\'export', 'tify');?></h3>
			<?php /*
			<h4><?php _e( 'Choix des champs à afficher', 'tify');?></h4>
			<div>				
				<label >
					<span style="width:180px; display:inline-block;"><?php _e( 'A partir de l\'enregistrement', 'tify');?></span>&nbsp;
					<input type="text" class="export-options" name="from" value="<?php echo $this->default_options['from'];?>" size="4" />
				</label>
				<br />
				<label>
					<span style="width:180px; display:inline-block;"><?php _e( 'Nombre d\'enregistrements', 'tify');?></span>&nbsp;
					<input type="text" class="export-options" name="limit" value="<?php echo $this->default_options['limit'];?>" size="4" />
				</label>&nbsp;
				<em><?php _e( 'laisser vide pour extraire tous les enregistrements', 'tify');?></em>
			</div> */ ?>
			
			<h4><?php _e( 'Numéroter les lignes', 'tify' );?></h4>
			<div>
				<label>
					<input type="radio" class="export-options" name="show_num" value="y" <?php checked( $this->default_options['show_num']=='y' );?>>&nbsp;
					<?php _e( 'Oui', 'tify' );?>
				</label>&nbsp;&nbsp;&nbsp;
				<label>
					<input type="radio" class="export-options" name="show_num" value="n" <?php checked( $this->default_options['show_num']=='n' );?>>&nbsp;
					<?php _e( 'Non', 'tify' );?>
				</label>&nbsp;&nbsp;&nbsp;
			</div>
			
			<h4><?php _e( 'Afficher la date d\'enregistrement', 'tify' );?></h4>
			<div>
				<label>
					<input type="radio" class="export-options" name="show_date" value="y" <?php checked( $this->default_options['show_date']=='y' );?>>&nbsp;
					<?php _e( 'Oui', 'tify' );?>
				</label>&nbsp;&nbsp;&nbsp;
				<label>
					<input type="radio" class="export-options" name="show_date" value="n" <?php checked( $this->default_options['show_date']=='n' );?>>&nbsp;
					<?php _e( 'Non', 'tify' );?>
				</label>&nbsp;&nbsp;&nbsp;
			</div>			
			
			<h4><?php _e( 'Choix des champs à exporter', 'tify' );?></h4>			
			<div>
			<?php foreach( (array) $this->main->master->fields->get_list() as $slug => $field ) : ?>
				<?php if( ! $this->main->master->addons->get_field_option( 'save', 'record', $field ) ) continue; ?>
				<?php if( ! $this->main->master->addons->get_field_option( 'export', 'record', $field ) ) continue; ?>
				<label>
					<input type="checkbox" class="export-fields" name="fields[<?php echo $slug;?>]" value="<?php echo $slug;?>" checked="checked">&nbsp;
					<?php echo $field['label'];?>
				</label>&nbsp;&nbsp;&nbsp;			
			<?php endforeach;?>
			</div>
			
			<div id="progressbar" style="margin-top:30px; display:none;"><div class="progress-label"><?php _e( 'Chargement ...', 'tify' );?></div></div>
			
			<hr style="margin:10px 0 20px; background-color: #EEE; border:none; height:1px;" />
			<p>
				<input type="submit" class="button-primary" value="exporter" />
				<a href="<?php echo tify_upload_url( $this->main->master->dirs->uri( 'export' ) .'/'. $filename, array( '_wpnonce' => wp_create_nonce( "tify_forms_record_export-" . $filename ) ), admin_url() );?>" id="download-csv" class="button-secondary" style="margin-left:10px; display:none;">
					<?php _e( 'Télecharger', 'tify' );?>
				</a>
			</p>
		</form>
		<?php       
	}		
}