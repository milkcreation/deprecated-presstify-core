<?php
namespace tiFy\Core\Forms\FieldTypes\File;

use tiFy\Core\Forms\FieldTypes\Factory;

class File extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID = 'file';
		
	// Support
	public $Supports = array(
		'integrity',
		'label', 
		'placeholder', 
		'request',
		'wrapper'
	);	
	
	// PARAMETRES
	/// Répertoire de stockage des fichiers
	private $UploadDir;	
	
	/* = CONSTRUCTEUR = */				
	public function __construct()
	{		
		// Options par défaut
		$this->Defaults = array(			
			// Liste des extensions autorisées, séparées par une virgule (ex:'jpg,jpeg,png') ou @see get_allowed_mime_types()
			'allowed_file_ext' 		=> '',	
			// Taille maximale du fichier en MB
			'max_file_size'			=> 2, 		 
			// Répertoire de stockage des fichiers	
			'upload_dir'			=> WP_CONTENT_DIR . '/uploads/tify_forms/upload',
				
			/* @todo */
			// Previsualisation
			'preview'				=> true,
			// Active la conservation du soumis en cas d'erreur sur le formulaire	
			'transport'				=> false,
			// Téléchargement via ajax
			'ajax_upload'			=> false
		);
		
		// Définition des fonctions de callback
		$this->Callbacks = array(
			'form_set_params' 					=> array( $this, 'cb_form_set_params' ),
			'handle_parse_query_field_value' 	=> array( $this, 'cb_handle_parse_query_field_value' ),
			'handle_check_field' 				=> array( $this, 'cb_handle_check_field' ),			
			'handle_submit_request' 			=> array( 'function' => array( $this, 'cb_handle_submit_request' ), 'order' => 0 )
		);
	}
	
	/* = CONTROLEURS = */
	/** == Traitement de la requête $_FILES == **/
	private function parseFileRequest()
	{
		// Bypass
		if( empty( $_FILES[ $this->form()->getUID() ] ) )
			return array();
		
		$files = $_FILES[ $this->form()->getUID() ];
		$pieces = array( 'name', 'type', 'tmp_name', 'error', 'size' );
		
		foreach( $pieces as $key ) :
			if( empty( $files[ $key ][ $this->field()->getSlug() ] ) )
				continue;
			${$key} = $files[ $key ][ $this->field()->getSlug() ];		
		endforeach;
		
		return compact( $pieces );
	}
	
	/** == Vérifie si type du fichier est autorisé == **/
	private function isAllowedFileType( $filename )
	{
		// Bypass
		if( empty( $this->getOption( 'allowed_file_ext', false ) ) ) :
			$check = wp_check_filetype( $filename );
			return ( $check['ext'] &&  $check['type'] );
		endif;
		
		// Traitement de la liste des extensions autorisées	
		$exts = $this->getOption( 'allowed_file_ext' );			
		if( is_string( $exts ) )	
			$exts = array_map( 'trim', explode( ',', $exts ) );
				
		foreach ( (array) $exts as $ext ) :
			if ( preg_match( '!\.(' . $ext . ')$!i', $filename, $ext_matches ) ) :
				return true;
			endif;
		endforeach;
		
		return false;
	}
	
	/** == Vérifie si la taille du fichier envoyé est inférieur à la valeur maximum autorisée == **/
	private function isMaxFileSize( $size )
	{
		if( ! $max_file_size = (float) $this->getOption( 'max_file_size' ) )
			return true;
		
		return ! ( ( $size / 1048576 ) > $max_file_size );
	}
	
	/** == Récupération du répertoire de dépôt des fichier== **/
	final public function getUploadDir()
	{
		if( $this->UploadDir )
			return $this->UploadDir;
		
		if( ! $upload_dir = $this->getOption( 'upload_dir' ) ) :
			$uploads = wp_upload_dir();
			$upload_dir = $uploads['basedir'] . $uploads['subdir'];				
		endif;
			
		$upload_dir = \tiFy\Core\Forms\Form\Helpers::parseMergeVars( $upload_dir, $this->form() );
		
		return $this->UploadDir = $upload_dir;
	}
	
	/** == == **/
	final public function getDestinationFilename()
	{
		if( ! $file = unserialize( @base64_decode( $this->field()->getValue() ) ) )
			return;
			
	}
	
	/* = COURT-CIRCUITAGE = */
	/** == Définition des paramètres du formulaire == **/
	public function cb_form_set_params( &$form )
	{
		$form->setAttr( 'enctype', 'multipart/form-data' );
	}	
		
	/** == Récupération de la valeur du champ == **/
	public function cb_handle_parse_query_field_value( &$value, $field, $handle )
	{
		// Bypass
		if( $field !== $this->field() )
			return;
		$file = $this->parseFileRequest();	
		$value = @ base64_encode( serialize( $file ) );
	}
	
	/** == Vérification des requêtes == **/
	public function cb_handle_check_field( &$errors, $field )
	{		
		// Bypass
		if( $field !== $this->field() )
			return;
		
		if( ! $file = unserialize( @base64_decode( $field->getValue() ) ) ) 
			return;		
			
		// Erreurs PHP
		if( ! empty( $file['error'] ) ):
			switch ( $file['error'] ) :
				case 1:
				case 2:
					$errors[] = sprintf( __( 'La taille du fichier téléchargé excède la valeur autorisée pour le champ "%s".', 'tify' ), $field->getLabel() );
					break;	
				case 3:
					$errors[] = sprintf( __( 'ERREUR SYSTÈME : Le fichier du champs "%s" n\'a été que partiellement téléchargé.', 'tify' ), $field->getLabel() );
					break;		
				case 4:
					if( $field->isRequired() )
						$errors[] = sprintf( __( 'Aucun fichier n\'a été téléchargé dans le champs "%s".', 'tify' ), $field->getLabel() );
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
			
		// Extension de fichier autorisée
		elseif( ! $this->isAllowedFileType( $file['name'] ) ) :
			$errors[] = sprintf( __( 'Type de fichier non autorisé, pour le fichier du champ "%s".', 'tify' ), $field->getLabel() );
		// Taille maximum du fichier
		elseif( !  $this->isMaxFileSize( $file['size'] ) ) :
			$errors[] = sprintf( __( 'La taille maximum du fichier est atteinte, pour le fichier du champ "%s".', 'tify' ), $field->getLabel() );
		endif;
	}
	
	/** == Traitement de la requête de soumission de formulaire == **/
	public function cb_handle_submit_request( &$handle )
	{		
		if( ! $file = unserialize( @ base64_decode( $this->field()->getValue() ) ) ) 
			return;
		if( empty( $file['tmp_name'] ) || empty( $file['name'] ) )
			return;			
			
		if( ! $upload_dir = $this->getUploadDir() ) :
			return $handle->addError( sprintf( __( 'ERREUR SYSTEME : Le répertoire de destination est indisponible, impossible d\'enregistrer le fichier du champ %s', 'tify' ), $this->field()->getLabel() ) );			
		endif;
			
		if( ! wp_mkdir_p( $this->getUploadDir() ) ) :
			return $handle->addError( sprintf( __( 'ERREUR SYSTEME : Le répertoire de destination est inaccessible, impossible d\'enregistrer le fichier du champ %s', 'tify' ), $this->field()->getLabel() ) );
		endif;
		
		$source 		= wp_normalize_path( $file['tmp_name'] );	
		$filename 		= wp_unique_filename( $upload_dir, sanitize_file_name( remove_accents( $file['name'] ) ) );
		$destination 	= wp_normalize_path( $upload_dir .'/'. $filename );
		
		if( ! file_exists( $source ) )
			return $handle->addError( sprintf( __( 'ERREUR SYSTEME : Le fichier d\'origine téléchargé est indisponible, impossible de récupérer le fichier du champ %s', 'tify' ), $this->field()->getLabel() ) );
			
		if( ! @ copy( $source, $destination ) )
			return $handle->addError( sprintf( __( 'ERREUR SYSTEME : Impossible de déplacer le fichier du champ %s', 'tify' ), $this->field()->getLabel() ) );				
		
		$file = wp_parse_args( array( 'tmp_name' => '', 'name' => $filename ), $file );	
		
		$this->field()->setValue( @ base64_encode( serialize( $file ) ) );
			
		// Nettoyage du dossier temporaire
		@ unlink( $source );
	}
	
	/* = CONTROLEURS = */
	/** == Affichage du champ == **/
	public function display()
	{
		$output  = "";
		$output .= "\n\t<input type=\"file\" ";
		/// ID HTML
		$output .= " id=\"". $this->getInputID() ."\"";
		/// Classe HTML
		$output .= " class=\"". join( ' ', $this->getInputClasses() ) ."\"";
		/// Name		
		$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."\"";
		/// TabIndex
		$output .= " ". $this->getTabIndex();
		/// Fermeture
		$output .= "/>";
		
		return $output;
	}
}