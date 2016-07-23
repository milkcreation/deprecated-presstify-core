<?php
/**
 * @ressources https://github.com/PHPOffice/PHPExcel
 */
class tiFy_AdminView_Import{
	/* = ARGUMENTS = */
	public 	// Configuration
			$id,
			$screen,
			$db,					
			$upload_dir,
			
			// Paramètres du fichier d'example
			$sample	= array(
			/*	'name'	=> 'import-exemple.csv',
				'rows'	=> array(
					array( 'sample #1' ),
					array( 'sample #2' ),
					array( 'sample #3' )
				) */
			),
			
			// Paramètres de données
			$filename,									// Fichier d'import
			$header		= 0,							// Permet de spécifié si le fichier contient un en-tête
			$offset 	= 0,							// Elément à partir duquel commencer le traitement
			$per_pass	= 10,							// Nombre d'éléments traités par passe
			$total		= 0,							// Nombre d'éléments total contenu dans le fichier 
			$mime_types	= array( 'csv', 'txt' ),		// Types de fichiers autorisés
			
			// Paramètres d'import
			$import_options = array(),
			
			// Paramètres d'import CSV
			$delimiter 	= ",",
			$enclosure 	= "\"",
			$escape 	= "\\",
						
			// Cartographie des colonnes 				
			$column_map = array(
			/*
				[col_name] =>	array(
			 		'title' 			=> $col_name,					
					'update'			=> false,
					'meta'				=> false,
			 		'integrity_cb'		=> '__return_true',			// Test d'intégrité de la valeur
					'format_value_cb'	=> false					// Formatage de la valeur avant l'injection
			 	)
			 */
			);			
			
	private	//Paramètres
			/// Chemins
			$dir,
			$uri,
			
			// Données de class
			$row_error 	= array(),
			$row_exist	= array(),
			$items 		= array(),
			
			// Contrôleur
			$list_table;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $args = array(), $db = null ){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );		
		
		// Configuration
		/// Environnement
		$this->id = get_class($this);
		$this->screen = convert_to_screen( $args['screen'] );

		/// Base de données
		$this->db = ( ! $db ) ? new tiFy_Contest_DbPosts : $db;
		
		/// Repertoire d'import des fichiers
		$upload_dir = wp_upload_dir();
		$this->upload_dir = $upload_dir['basedir'];
		
		// Actions et Filtres Wordpress
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_tify_adminview_import_download_sample_'. $this->id, array( $this, 'wp_ajax_download_sample' ) );
		add_action( 'wp_ajax_tify_adminview_import_upload_'. $this->id, array( $this, 'wp_ajax_upload' ) );
		add_action( 'wp_ajax_tify_adminview_import_handle_'. $this->id, array( $this, 'wp_ajax_import' ) );
	}
	
	/* = PARAMETRAGE = */
	/** == Traitement des colonnes == **/
	private function _parse_column_map(){
		if( $this->column_map ) :
			foreach( (array) $this->column_map as $col_name => $args ) :
				$args['title'] = ( empty( $args['title'] ) ) ? $col_name : $args['title'];
				$args['title'] = sanitize_title_for_query( $args['title'] );
				$this->column_map[$col_name] = wp_parse_args( $args, array( 'update' => false, 'meta' => false ) );
			endforeach;
		else :
			foreach( $this->db->col_names as $col_name )
				$this->column_map[$col_name] = wp_parse_args(
					array( 
						'title' 		=> $col_name,
						'integrity_cb'	=> '__return_true',
						'single'		=> false,
						'meta'			=> false
					)
				);
		endif;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Mise en file des scripts == **/
	public function wp_admin_enqueue_scripts(){
		tify_progress();
		wp_enqueue_style( 'tify_csv', $this->uri .'/css/tify_admin_view-import.css', array(), 150607 );
		wp_enqueue_script( 'tify_csv', $this->uri .'/js/tify_admin_view-import.js', array( 'jquery', 'tify-fixed_submitdiv' ), 150607 );
	}
	
	/** == Fichier d'exemple a télécharger == **/
	public function wp_ajax_download_sample(){
		if( empty( $this->sample ) )
			wp_die( __( '<h1>Téléchargement du fichier impossible</h1><p>La fonctionnalité n\'est pas active</p>', 'tify' ), __( 'Impossible de télécharger le fichier', 'tify' ), 404 );
		
		@ini_set("auto_detect_line_endings", true);
		
		$rows = array();
		if( ! empty( $this->sample['rows'] ) ) :
			$rows = $this->sample['rows'];	
		else :
			global $wpdb;
			foreach( $wpdb->get_col( "DESC {$this->db->wpdb_table}", 0 ) as $column )
				$rows[0][] = $column;
			foreach( range( 0, 4, 1 ) as $i )
				$rows[$i+1] = $wpdb->get_row( "SELECT * FROM {$this->db->wpdb_table}", ARRAY_A, $i );
		endif;
		reset( $rows );
				
		header( 'Content-Encoding: UTF-8' );
		header( 'Content-Type: application/csv; charset=UTF-8' );
	    header( 'Content-Disposition: attachment; filename="'. ( $this->sample['name'] ? sanitize_file_name( $this->sample['name'] ) : 'export-sample' ) .'.csv";' );
	
	    $f = fopen( 'php://output', 'w' );				
	    foreach ( $rows as $row ) :
	        fputcsv( $f, $row, $this->delimiter, $this->enclosure );
		endforeach;
		exit();
	}
	
	/** == Traitement Ajax de téléchargement du fichier == **/
	public function wp_ajax_upload(){
		// Récupération des variables de requête		
		$file 		= current( $_FILES );	
		$filename 	= sanitize_file_name( basename( $file['name'] ) );
		
		$response = array();
		if( ! @move_uploaded_file( $file['tmp_name'],  $this->upload_dir . "/" . $filename  ) ) :
			$response = array( 
				'success' 	=> false, 
				'data' 		=> sprintf( __( 'Impossible de déplacer le fichier "%s" dans le dossier d\'import', 'tify' ), basename( $file['name'] ) )
			);
		else :
			$this->filename = $this->upload_dir . "/" . $filename;
			$this->header	= $_POST['header']; 		
			$data = array();
			
			$this->table_init();
			$data['table'] = $this->get_table_preview();
						
			ob_start();
			$this->display_form_import_options();
			$data['options'] = ob_get_clean();
			
			$response = array( 'success' => true, 'data' => $data );
		endif;
							
		wp_send_json( $response );
	}
	
	/** == Traitement Ajax d'import des données == **/
	public function wp_ajax_import(){
		$this->filename 		= $_POST['filename'];
		$header 				= (int) $_POST['header'];		
		$offset 				= (int) $_POST['offset'];
		$per_pass 				= (int) $_POST['per_pass'];
		$this->import_options	= $_POST['options'];
				
		$this->_parse_column_map();		
		
		$response 	= array();
		
		$n = $header ? - 1 : 0;		
		foreach( (array) $this->get_file_datas( $offset, $per_pass ) as $row => $datas ) :	
			$result = $this->import_row( $row, $datas );	
			$response[($offset+$n++)] = ( is_wp_error( $result ) ) ? $result->get_error_message() : __( 'Enregistré avec succés', 'tify' ); 
		endforeach;
				
		wp_send_json( $response );
		exit;
	}
	
	/* = TABLE D'APERCU DES DONNEES = */
	/** == Initialisation de la table == **/
	public function table_init(){
		$this->list_table = new tiFy_AdminView_Import_List_Table( $this );
	}
	
	/* = Préparation = */
	private function table_prepare(){		
		$cols = array_keys( $this->column_map );
		$n = 1;
		foreach( (array) $this->get_file_datas( (int) $this->header ) as $row => $datas ) :
			// Préparation de l'item courant
			$this->items[$row] 			= new stdClass();				
			$this->items[$row]->row 	= $n++; 
			$this->items[$row]->cb 		= "<input type=\"checkbox\" />";       	
			
			// Récupération des données du fichier csv						     
	        foreach( $datas as $cell => $value ) :
				$col 	= $cols[$cell];
				
				// Test d'intégrité de la valeur du champs
				$integrity = $this->check_value_integrity( $col, $value );
				if( is_wp_error( $integrity ) )
					$this->row_error[$row][] = $integrity->get_error_message();															
			 	
				// Formatage de la valeur
				$value = $this->format_value( $col, $value );
					
				$this->items[$row]->{$col} = $value;
			endforeach;
			
			// Test d'existance
			$this->row_exist[$row] = ( $this->check_item_exists( $row, $this->items[$row] ) ) ? true : false;					
			
			$tify_adminview_import_result = $this->id. '_tify_adminview_import_result';
			$this->items[$row]->$tify_adminview_import_result = $this->_get_row_results( $row );

			$row++;
		endforeach;
	}

	/** == Affichage de la table == **/
	private function get_table_preview(){
		$this->_parse_column_map();		
		$this->table_prepare();
		
		list( $columns, $hidden ) 	= $this->list_table->get_column_info();
		$this->list_table->items 	= $this->items;
		
		$output  = "";
		ob_start();
		$this->list_table->display();
		$output .= ob_get_clean();
		
		return $output;
	}
	
	/*** === Récupération des actions d'import === ***/
	private function _get_row_results( $row ){
		$output  = "";
		if( isset( $this->row_error[$row] ) ) :
			$output .= "<strong style=\"color:red;text-transform:uppercase\">". __( 'Import impossible', 'tify' ) ."</strong>";
			$output .= "<ol style=\"line-height:1;margin:0;padding:0;margin-left:1em;color:red;\">";
			foreach( $this->row_error[$row] as $error )
				$output .= "<li style=\"line-height:1;margin:0;padding:0;\">{$error}</li>";
			$output .= "</ol>";
		elseif( $this->row_exist[$row] ) :
			$output .= "<em style=\"color:orange;\">".__( 'Mise à jour', 'tify' ) ."</em>";
		else :
			$output .= "<em style=\"color:green;\">".__( 'Création', 'tify' ) ."</em>";
		endif;	
		
		return $output;
	}
	
	/* = IMPORT DES DONNEES EN BASE = */	
	/** == Traitement de l'import de données == **/
	private function import_row( $row, $datas ){
		// Traitement et enregistrement des données de post
		$item = false;
		$cols = array_keys( $this->column_map );
		foreach( $datas as $cell => $value ) :			
			$col 	= $cols[$cell];
			
			// Test d'intégrité de la valeur du champs
			$integrity = $this->check_value_integrity( $col, $value );
			if( is_wp_error( $integrity ) )
				return $integrity;															
		 	
			// Formatage de la valeur
			$value = $this->format_value( $col, $value );
			
			$item = new stdClass;	
			$item->{$col} = $value;
		endforeach;
		
		if( $item_id = $this->check_item_exists( $row, $item ) )
			$item->{$this->db->primary_key} = $item_id;
		
		$item = $this->parse_importdata( $item );
		
		if( $item_id = $this->db->insert_item( (array) $item ) ) :		
			$this->postprocess_importdata( $item_id );
			return $item_id;
		else :
		endif;	
	}

	/** == Traitement des données avant insertion == **/
	public function parse_importdata( $item ){
		return $item;
	}
	
	/** == Post-traitement de l'import de données d'une ligne == **/
	public function postprocess_importdata( $item_id ){}

	/* = TRAITEMENT DES DONNEES = */
	/** == Récupération des données du fichier == **/
	private function get_file_datas( $offset = 0, $passed = -1 ){
		/**
		 * http://stackoverflow.com/questions/32184933/solved-remove-bom-%C3%AF-from-imported-csv-file
		 * http://stackoverflow.com/questions/4348802/how-can-i-output-a-utf-8-csv-in-php-that-excel-will-read-properly
		 */		 
		/*
		// SOLUTION 1
		function removeBomUtf8($s){
		  if(substr($s,0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
		       return substr($s,3);
		   }else{
		       return $s;
		   }
		}
		// SOLUTION 2
		$fileContent = file_get_contents( $this->filename );
		$fileContent = mb_convert_encoding( $fileContent, "UTF-8" );
		$lines = explode("\n", $fileContent);
		*/
		
		// SOLUTION 3
		$lines = file( $this->filename );
				
		$this->total = count( $lines );
		$max = ( $passed > 0 ) ? ( $offset + $passed ) : ( $this->total+1 - $offset );		
		if( $max > $this->total ) $max = $this->total;
		
		$datas = array();
		for( $i = $offset; $i < $max; $i++ ) :
			$s = $lines[$i];
			// Eviter les erreurs de BOM
			$s = ( substr( $s, 0, 3 ) == chr( hexdec( 'EF' ) ) . chr( hexdec( 'BB' ) ) . chr( hexdec( 'BF' ) ) ) ? substr( $s, 3 ) : $s;
			$datas[$i] = str_getcsv( utf8_encode( $s ), $this->delimiter, $this->enclosure, $this->escape );
		endfor;
			
		return $datas;
	}
	
	/** == Contrôle d'intégrité des valeurs == **/
	private function check_value_integrity( $col, $value ){
		if( ! $check_integrity = call_user_func( $this->column_map[$col]['integrity_cb'], $value ) )
			return new WP_Error(  'tify_adminview_import-error', sprintf( __( '%s invalide', 'tify' ), $this->column_map[$col]['title'] ) );
		elseif( is_wp_error( $check_integrity ) )
			return $check_integrity;
		
		return true;
	}
	
	/** == Formatage des valeurs == **/
	private function format_value( $col, $value ){
		if( $this->column_map[$col]['format_cb'] )
			$value = call_user_func( $this->column_map[$col]['format_cb'], $value );
		
		return $value;
	}
	
	/*** === Vérification d'existance === ***/
	private function check_item_exists( $row, $item ){
		$query_args = array();
		foreach( $this->column_map as $col_name => $args )
			if( $args['single'] )
				$query_args[$col_name] = $item->$col_name;
		
		return $this->db->get_item_id( $query_args );
	}
	
	/* = AFFICHAGE = */
	/** == Rendu de l'interface d'administration == **/
	public function admin_render(){
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Importation d\'éléments', 'tify' );?>
				<?php if( ! empty( $this->sample ) ) :?>
					<a id="tify_adminview_import-download_sample" class="add-new-h2" href="<?php echo esc_url( add_query_arg( array( 'action' => 'tify_adminview_import_download_sample_'. $this->id ), admin_url( 'admin-ajax.php') ) );?>"><?php _e( 'Fichier d\'exemple', 'tify' );?></a>
				<?php endif;?>
			</h2>
			<div style="margin-right:300px; margin-top:20px;">
				<div style="float:left; width: 100%;">					
					<div id="tify_adminview_import-table_preview">
						<?php $this->display_table_preview();?>						
					</div>				
				</div>
				<div id="side-sortables" style="margin-right:-300px; width: 280px; float:right;">
					<div id="submitdiv" class="tify_submitdiv">
						<h3 class="hndle">
							<span><?php _e( 'Enregistrer', 'tify' );?></span>
						</h3>
						<div class="inside">
							<div class="minor_actions">
								<?php $this->display_form_upload();?>
							</div>	
							<div class="major_actions">
								<div id="tify_adminview_import-options_form">
									<?php $this->display_form_import_options();?>
								</div>
							</div>	
						</div>
					</div>					
				</div>
			</div>
						
			
		</div>
	<?php
	}
	
	/** == Formulaire de téléchargement de fichier == **/
	public function display_form_upload(){
	?>
		<div style="position:relative;padding:20px 0 10px;">
			<label><?php _e( 'Type de fichier autorisés : ', 'tify' );?></label>
			<p style="line-height:1;"><?php echo implode( ', ', $this->mime_types );?></p>
			<form id="tify_adminview_import-uploadfile" method="post" action="" enctype="multipart/form-data">				
				<input id="tify_adminview_import-id" type="hidden" name="tify_adminview_import-id" value="<?php echo $this->id;?>">
				<p><label><input id="tify_adminview_import-header" name="tify_adminview_import-header" type="checkbox" value="1" <?php checked( $this->header == 1 );?> /><?php _e( 'Le fichier comporte un en-tête' , 'tify' );?></p>
				<input style="font-size:0.9em;" id="tify_adminview_import-uploadfile_button" type="file" name="" autocomplete="off"/>
				<span class="spinner" style="position:absolute; top:0px;right:-10px;"></span>
			</form>	
		</div>
	<?php
	}
	
	/** == Table d'aperçu des données == **/
	public function display_table_preview(){
		if( $this->filename ) echo $this->get_table_preview();
	}

	/** == Affichage des options d'import de formulaire == **/
	public function display_form_import_options(){
		if( ! $this->filename )
			return;
	?>
		<form method="post" action="">
			<input type="hidden" id="tify_adminview_import-hasheader" value="<?php echo (int) $this->header;?>"/>
			<ul style="margin:0;">
				<li>
					<label><?php _e( 'Nom du fichier à traiter', 'tify' );?></label>
					<input type="hidden" id="tify_adminview_import-filename" value="<?php echo $this->filename;?>"/>
					<?php echo basename( $this->filename );?>
				</li>
				<li>
					<label><?php _e( 'Nombre total d\'élément à traiter', 'tify' );?></label>
					<input type="hidden" id="tify_adminview_import-total" value="<?php echo $this->total;?>"/>
					<?php echo $this->total;?>
				</li>
				<li>
					<label><?php _e( 'Démarrer le traitement à partir de l\'élément', 'tify' );?></label>
					<input type="number" id="tify_adminview_import-offset" min="1" max="<?php echo $this->total;?>" value="<?php echo $this->offset+1;?>"/>						
				</li>
				<li>
					<label>
						<?php _e( 'Nombre d\'élément à traiter', 'tify' );?>
						<em style="display:block;font-size:0.9em;color:#AAA;"><?php _e( '(-1 pour tous)', 'tify');?></em>
					</label>
					<input type="number" id="tify_adminview_import-limit" min="-1" max="<?php echo $this->total;?>" value="-1"/>						
				</li>
			</ul>
			
			<?php $this->display_import_options();?>
			<hr/>
			<button type="submit" id="tify_adminview_import-import_button" class="button-primary"><span class="dashicons dashicons-migrate" style="vertical-align:middle"></span> <?php _e( 'Lancer l\'import', 'tify');?></a>
		</form>
	<?php
	}
	
	/*** === Affichage des options d'import == **/
	public function display_import_options(){}
}

if( ! is_admin() )
	return;
tify_require_lib( 'admin_view' );

/* = LISTE = */
class tiFy_AdminView_Import_List_Table extends tiFy_AdminView_List_Table {
	/* = ARGUMENTS = */	
	public 	// Contrôleur
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_AdminView_Import $main ){
		// Définition du controleur principal	
		$this->main = $main;	
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
            	'singular'  => 'tify_csv',
            	'plural'    => 'tify_csvs',
            	'screen'	=> $this->main->screen->id
        	),
        	$main->db        	 
		);
	}
	
	/** == Définition des colonnes == **/
	public function get_columns(){
		$c['row'] = '#';	
		foreach( $this->main->column_map as $col => $args ) :
			$c[$col] = "<b>{$args['title']}</b><em style=\"display:block;font-size:0.8em;line-height:0.9;color:#999;\">". ( ! $args['meta'] ? __( 'Données de la table principale', 'tify' ) : __( 'Metadonnée', 'tify' ) ) ."</em>";
		endforeach;
		$c[ $this->main->id .'_tify_adminview_import_result' ] = "<b>". __( 'Action d\'import', 'tify' ) ."</b>";

		return $c;
	}
	
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->row );
    }
}