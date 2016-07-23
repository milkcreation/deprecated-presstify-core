<?php
/*
FieldType Name: File Upload
FieldType ID: file
Callback: tiFy_Forms_FieldType_File
Version: 1.150817
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

Class tiFy_Forms_FieldType_File extends tiFy_Forms_FieldType{
	/* = ARGUMENTS = */

	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition du type de champ
		$this->attrs = array(
			'slug'			=> 'file',
			'label'			=> __( 'File upload', 'tify' ),
			'section' 		=> 'input-fields',
			'order' 		=> 1,
			'supports'		=> array( 'label', 'placeholder', 'integrity-check', 'request' ),
			'options'		=> array(
				'ajax_upload'			=> false,
				'allowed_file_types' 	=> false,	// extension séparée par des espaces ex : 'jpg jpeg png'
				'max_filesize'			=> 2, 		// Taille maximale du fichier en MB 
				'conservation'			=> false,	// Active la conservation à la soumission
				'preview'				=> true,	// La conservation doit être active
				'upload_path'			=> false
			)				
		);	
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'form_set_options' 				=> array( $this, 'cb_form_set_options' ),
			'field_type_output_display' 	=> array( $this, 'cb_field_type_output_display' ),
			'handle_get_request' 			=> array( $this, 'cb_handle_get_request' ),
			'handle_check_request' 			=> array( $this, 'cb_handle_check_request' ),			
			'handle_submit_request' 		=> array( 'function' => array( $this, 'cb_handle_submit_request' ), 'order' => 9 )
		);
		
        parent::__construct( $master );	
	}
		
	/* = CALLBACKS = */
	/** == Définition des options de formulaire == **/
	function cb_form_set_options( &$options ){
		$options['enctype'] = true;
	}	
	
	/** == Affichage du champ == **/
	function cb_field_type_output_display( &$output, $field ){
		// Bypass
		if( $field['type'] != 'file' )
			return;			
			
		$output .= "\n\t<input type=\"file\" ";
	
		$field_class = rtrim( trim( sprintf( $field['field_class'], "field field-{$field['form_id']} field-{$field['slug']} tify_forms_fieldtype_file-input") ) );
		$output .= " name=\"". esc_attr( $this->master->fields->get_name( $field ) ) ."\" id=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$field_class."\"";			
		$output .= "/>";
		
		if( $this->master->errors->field_has( $field ) )
			return;
		
		// Transport des données fichier
		$output .= "\n\t<input type=\"hidden\" value=\"". esc_attr( $field['value'] ) ."\" name=\"{$field['form_prefix']}[{$field['form_id']}][{$field['slug']}]\" />";
		
		// Conservation des données		
		/// Bypass
		if( ! $field['options']['conservation'] )
			return;
		
		$file = unserialize( @ base64_decode( $field['value'] ) );			
		/// Affichage du nom de fichier
		$output .= "\n\t<input class=\"tify_forms_fieldtype_file-conservation\" type=\"text\" value=\"". ( ( $file = unserialize( @ base64_decode( $field['value'] ) ) ) ? esc_attr( $file['name'] ) : false ) ."\" placeholder=\"".$field['placeholder']."\" readonly=\"readonly\" autocomplete=\"off\"/>";
		/// Prévisualisation du fichier téléchargé	
		if( ! $field['options']['preview'] || ! $file )
			return;	
		$output .= "\n\t<div class=\"tify_forms_fieldtype_file-preview\"><span style=\"display: inline-block;height: 100%;vertical-align: middle;\"></span><img src=\"". $this->master->dirs->uri( 'temp' ) ."/". basename( $file['tmp_name'] ) ."\" style=\"max-width:100%; height:auto;\"></div>";
	}
	
	/** == Récupération de la valeur du champ == **/
	function cb_handle_get_request( &$request, $field, $_method ){
		// Bypass
		if( $field['type'] != 'file' )
			return;
		
		if( $file = $this->parse_file_request( $field ) ) :
			if( $field['options']['conservation'] ) :
				$filename 		= $file['tmp_name'];		
				$destination 	= wp_normalize_path( $this->master->dirs->dirname( 'temp' ) ."/". basename( $file['tmp_name'] ) );
					// Déplacement du fichier dans le repertoire de stockage temporaire (pour les fichiers autorisés uniquement)
				if( ! file_exists( $destination ) && $this->check_file_type_is_allowed( $file, $field ) ) :											
					if( ! move_uploaded_file( $filename, $destination ) ) :
						wp_die( sprintf( __( '<h1>ERREUR SYSTEME</h1><p>Impossible de déplacer le fichier du champs "%s" dans le repertoire de stockage temporaire.</p>', 'tify' ), $field['label'] ) );
					endif;					
				endif;
				$file['tmp_name'] = $destination;
			endif;

			$request = @ base64_encode( serialize( $file ) );
		elseif( ! $field['options']['conservation'] ) :
			$request = false;
		endif;
	}
	
	/** == Vérification des requêtes == **/
	function cb_handle_check_request( &$errors, &$field ){		
		// Bypass
		if( $field['type'] != 'file' )
			return;
		
		if( ! $file = unserialize( @ base64_decode( $field['value'] ) ) ) 
			return;		
		
		// Retour des erreurs PHP
		if( isset( $file['error'] ) && ( $file['error'] > 0 ) ):
			switch ( $file['error'] ) :
				case 1:
				case 2:
					$errors[] = sprintf( __( 'La taille du fichier téléchargé excède la valeur autorisée pour le champ "%s".', 'tify' ), $field['label'] );
					break;	
				case 3:
					$errors[] = sprintf( __( 'ERREUR SYSTÈME : Le fichier du champs "%s" n\'a été que partiellement téléchargé.', 'tify' ), $field['label'] );
					break;		
				case 4:
					if( $field['required'] )
						$errors[] = sprintf( __( 'Aucun fichier n\'a été téléchargé dans le champs "%s".', 'tify' ), $field['label'] );
					break;
				case 6:
					$errors[] = __( 'ERREUR SYSTÈME : Le dossier temporaire est manquant', 'tify' );
					break;
				case 7:
					$errors[] = __( 'ERREUR SYSTÈME : Échec de l\'écriture du fichier sur le disque.', 'tify' );
					break;
				case 8:
					$errors[] = __( 'ERREUR SYSTÈME : Une extension PHP a arrêté l\'envoi de fichier.', 'tify' );
					break;
			endswitch;
		// Test des droits d'extension de fichier
		elseif( ! $this->check_file_type_is_allowed( $file, $field ) ) :
			$errors[] = sprintf( __( 'Type de fichier non autorisé dans le champ "%s".', 'tify' ), $field['label'] );
			$field['value'] = false;
		elseif( ! $this->check_file_size( $file, $field ) ) :
			$errors[] = sprintf( __( 'La taille maximale du fichier atteint dans le champ "%s".', 'tify' ), $field['label'] );
			$field['value'] = false;	
		endif;
	}
	
	/** == == **/
	function cb_handle_submit_request( &$parsed_request, $original_request ){
		$sanitized = array( );

		// Déplacement des fichiers du répertoire de stokage temporaire vers le repertoire de stockage définitif
		foreach( $parsed_request['fields'] as $slug => &$field ) :
			// Bypass
			if( $field['type'] != 'file' ) 
				continue;				
			if( ! $file = unserialize( @ base64_decode( $field['value'] ) ) ) 
				continue;
			
			// Définition des arguments
			$upload_dir 	= $this->upload_dir( $field );
			$filename 		= wp_normalize_path( $file['tmp_name'] );					
			$destination 	= wp_normalize_path( $upload_dir['path'] ."/". wp_unique_filename( $upload_dir['path'], sanitize_file_name( remove_accents( $file['name'] ) ) ) );
			
			if( ! file_exists( $filename ) )
				return;						
			if( ! @ copy( $filename, $destination ) )
				wp_die( sprintf( __( '<h1>ERREUR SYSTEME</h1><p>Impossible de déplacer le fichier du champ "%s".</p>', 'tify' ), $field['label'] ) );
			
			$sanitized[$slug] 	= $file['tmp_name'];
			$file['name'] 		= basename( $destination );
			$file['upload_dir'] = $upload_dir;
			$parsed_request['values'][$slug] = $field['value'] = @ base64_encode( serialize( $file ) );
		endforeach;
		
		// Nettoyage du dossier temporaire
		foreach( $sanitized as $slug => $tmp_name )
			@ unlink( tmp_name );
	}
	
	/* = CONTROLEURS = */
	/** == Vérifie si le fichier envoyé est un type d'extension autorisé == **/
	function check_file_type_is_allowed( $file, $field ){
		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		
		$allowed_file_types = array();
		if( ! $field['options']['allowed_file_types'] )
			foreach( array_keys( get_allowed_mime_types() ) as $exts )
				foreach( explode( '|', $exts ) as $ext )
					array_push( $allowed_file_types, $ext );
		elseif( is_string( $field['options']['allowed_file_types'] ) )
			$allowed_file_types = explode( ' ', $field['options']['allowed_file_types'] );
		else
			$allowed_file_types = $field['options']['allowed_file_types'];		

		return in_array( $ext, $allowed_file_types );
	}
	
	/** == Vérifie si la taille du fichier envoyé est inférieur à la valeur maximum autorisée == **/
	function check_file_size( $file, $field ){
		if( ! $max_file_size = (float) $field['options']['max_filesize'] )
			return true;
		
		return ! ( ( $file['size']/1048576 ) > $max_file_size );
	}
	
	/** == Traitement de la requête $_FILES relative au champ == **/
	function parse_file_request( $field ){
		$form_id = $field['form_id'];
		$form_prefix =  $this->master->forms->get_prefix( $form_id );

		// Bypass
		if( ! isset( $_FILES[ $form_prefix ] ) )
			return;
			
		foreach( array( 'name', 'type', 'tmp_name', 'error', 'size' ) as $index )
			if( empty( $_FILES[ $form_prefix ][$index][$form_id][$field['slug']] ) )
				continue;
			else
				$_file[$index] = $_FILES[ $form_prefix ][$index][$form_id][$field['slug']];
		
		if( empty( $_file['name'] ) || empty( $_file['type'] ) || empty( $_file['tmp_name'] ) || empty( $_file['size'] ) )
			return;		
		
		return $_file;
	}
	
	
	/** == Récupération du répertoire d'upload d'un champ == 
	 * @return array(
			[path] 		=> C:\path\to\wordpress\wp-content\uploads\2010\05
        	[url] 		=> http://example.com/wp-content/uploads/2010/05
        	[subdir] 	=> /2010/05
        	[basedir] 	=> C:\path\to\wordpress\wp-content\uploads
        	[baseurl] 	=> http://example.com/wp-content/uploads
        	[error] 	=>
	 		
	 		[rel]		=> /wp-content/uploads/2010/05
		)
	 **/
	function upload_dir( $field ){
		$upload_dir = array(
			'path'		=> wp_normalize_path( $this->master->dirs->dirname( 'upload' ) ),
			'url'		=> $this->master->dirs->uri( 'upload' ),
			'subdir'	=> '',
			'basedir'	=> wp_normalize_path( $this->master->dirs->dirname( 'upload' ) ),
			'baseurl'	=> $this->master->dirs->uri( 'upload' ),
			'rel'		=> $this->master->dirs->path( 'upload' ),
			'error'		=> false
		);

		if( $field['options']['upload_path'] ) :
			$upload_path = trailingslashit( trim( $field['options']['upload_path'], '/\\' ) );
			if( wp_mkdir_p( wp_normalize_path( ABSPATH ) . $field['options']['upload_path'] ) ) :
				$upload_dir = array(
					'path'		=> wp_normalize_path( ABSPATH ) . $upload_path,
					'url'		=> site_url() . '/' . $upload_path,
					'subdir'	=> '',
					'basedir'	=> wp_normalize_path( ABSPATH ) . $upload_path,
					'baseurl'	=> site_url() . '/' . $upload_path,
					'rel'		=> '/'. $upload_path,
					'error'		=> false
				);			
			endif;
		endif;
		
		return $upload_dir;
	}
}