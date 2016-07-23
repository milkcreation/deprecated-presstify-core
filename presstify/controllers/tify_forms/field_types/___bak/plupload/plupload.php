<?php
/**
 * 
 */	
class tify_forms_field_plupload{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$path,
			$uri,
			
			$mkcf;
		
	/* = CONSTRUCTEUR = */
	function __construct( MKCF $mkcf ){
		// MKCF	
		$this->mkcf 	= $mkcf;

		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Définition du type de champ
		$this->set_type();		
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_tify_forms_async_plupload_'. $this->mkcf->id, array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_forms_async_plupload_'. $this->mkcf->id, array( $this, 'wp_ajax' ) );
		
		// Callbacks
		$this->mkcf->callbacks->field_type_set( 'form_set_options', 'plupload', array( $this, 'cb_form_set_options' ) );
		$this->mkcf->callbacks->field_type_set( 'field_type_output_display', 'plupload', array( $this, 'cb_field_type_output_display' ) );
		$this->mkcf->callbacks->field_type_set( 'handle_before_redirect', 'plupload', array( $this, 'cb_handle_before_redirect' ) );
	}

	/**
	 * Déclaration du type de champ
	 */
	function set_type(){			
		$this->mkcf->fields->set_type(
			array(
				'slug'			=> 'plupload',
				'label'			=> __( 'Ajax File upload', 'mktzr_forms' ),
				'section' 		=> 'input-fields',
				'order' 		=> 1,
				'supports'		=> array( 'label', 'placeholder', 'integrity-check', 'request' ),
				'options'		=> array(
					// @see http://www.plupload.com/docs/Options
					'plupload' => array(
						'runtimes'				=> 'html5,flash,silverlight,html4',
						'container'				=> 'tify-forms-plupload-upload-ui',
						'browse_button'			=> 'tify-forms-plupload-browse-button',			
						'drop_element'			=> 'tify-forms-drag-drop-area',
						'file_data_name'		=> 'file',
						'multi_selection'		=> true,
						'url'					=> add_query_arg( array( 'action' => 'tify_forms_async_plupload_'. $this->mkcf->id ), admin_url( 'admin-ajax.php' ) ),
						'flash_swf_url'			=> includes_url( 'js/plupload/plupload.flash.swf' ),
						'silverlight_xap_url'	=> includes_url( 'js/plupload/plupload.silverlight.xap' ),
						'filters' 				=> array( )
					)
				)				
			)
		);
	}

	/**
	 * ACTIONS ET FILTRES WORDPRESS
	 */
	/**
	 * Initialisation globale
	 */
	function wp_init(){
		// Déclaration des scripts
		wp_register_style( 'tify-forms-plupload', $this->uri .'plupload.css', array( ), '20150225' );
		wp_register_script( 'tify-forms-plupload-handlers', $this->uri .'handlers.js', array( 'jquery', 'wp-plupload' ), '20150224', true );
	}
	
	/**
	 * Mise en file des scripts
	 */
	function wp_enqueue_scripts(){
		wp_enqueue_style( 'tify-forms-plupload' );
		wp_enqueue_script( 'tify-forms-plupload-handlers' );
	}

	/**
	 * Mise en file des scripts
	 */
	function wp_admin_enqueue_scripts(){	
		wp_enqueue_style( 'tify-forms-plupload' );
		wp_enqueue_script( 'tify-forms-plupload-handlers' );
	}

	/**
	 * Traitement de l'upload de fichier
	 */
	function wp_ajax(){
		$file = $_FILES['file'];
		
		// Déplacement du fichier dans le répertoire temporaire		
		$filename 		= $file['tmp_name'];		
		$destination 	= $this->mkcf->dirs->dirname( 'temp' ) ."/". basename( $file['tmp_name'] );						
		if( ! move_uploaded_file( $filename, $destination ) )
			wp_die( sprintf( __( '<h1>ERREUR SYSTEME</h1><p>Impossible de déplacer le fichier du champs "%s" dans le repertoire de stockage temporaire.</p>', 'mktzr_forms' ), $file['name'] ) );
		
		$file['tmp_name'] = $destination;
		
		// Définition du type
		$file_type = $this->get_file_type( $file );
		// Sortie HTML
		$output = "";		
		/// Nom du fichier
		$output .= "<div class=\"original_filename\">". $file['name'] ."</div>";		
		/// Prévisualisation
		$preview = $this->get_file_preview( $file );
		$output .= "<div class=\"small-preview\">". $preview ."</div>";		
		/// Champs de formulaire
		$field = $this->mkcf->fields->get_by_slug( $_REQUEST['field_slug'], $_REQUEST['form_id'] );
		$output .= $this->get_file_input( $file, $field );	
		
		echo json_encode( array( 'file' => $file, 'preview' => ( $file_type['type'] == 'image' ? $preview  : '' ), 'output' => $output  ) );
		
		exit;
	}
	
	/**
	 * MKCF CALLBACKS
	 */	
	/**
	 * Définition des options de formulaire
	 */
	function cb_form_set_options( &$options, $mkcf ){
		$options['enctype'] = true;
	}	
	
	/**
	 * Affichage du champ
	 */
	function cb_field_type_output_display( &$output, $field, $mkcf ){
		// Bypass
		if( $field['type'] != 'plupload' )
			return;			
		
		$items = ""; $n = 0; $full_preview = false;
		if( ! empty( $field['value'] ) && is_array( $field['value'] ) ) :
			foreach( $field['value'] as $fvalue ) :
				if( ! $file = unserialize( @ base64_decode( $fvalue ) ) )
					continue;
				$preview = $this->get_file_preview( $file );
				if( ! $n++ )
					$full_preview = $preview;
				$items .= '<li class="file-item uploaded"><div class="original_filename">'. $file['name'] .'</div><div class="small-preview">'. $preview .'</div>'. $this->get_file_input( $file, $field ) .'</li>';
			endforeach;
		endif;
				
		$output .=	 '<div id="tify-forms-plupload-upload-ui" class="tify-forms-plupload-upload-ui">'
						.'<div class="tify-forms-plupload-error"></div>'
						.'<div id="tify-forms-drag-drop-area" class="drag-drop-area">'
							.'<div class="drag-drop-inside">'
								.'<div class="preview">'. $full_preview .'</div>'
								.'<p>'. __( 'Déposez vos fichiers ici', 'tify' ).'</p>'
							.'</div>'
						.'</div>'
						.'<div class="browse-button"><input id="tify-forms-plupload-browse-button" type="button" value="'. __( 'Selectionnez un fichier', 'tify' ) .'" class="button"/></div>';
		$output .=		'<ul class="file-items">'. $items .'</ul>';
		$output .=	'</div>';
		
		$field = $this->parse_plupload_options( $field );
		$plupload_init = $field['options']['plupload'];

				
		if( is_admin() )
			add_action( 'admin_footer', create_function( '', 'echo "<script type=\"text/javascript\">var tifyFormsUploaderInit ='. addslashes( wp_json_encode( $plupload_init ) ) .';</script>";' ), 9 );	
		else
			add_action( 'wp_footer', create_function( '', 'echo "<script type=\"text/javascript\">var tifyFormsUploaderInit ='. addslashes( wp_json_encode( $plupload_init ) ) .';</script>";' ), 9 );
	}

	/**
	 * 
	 */
	function cb_handle_before_redirect( &$parsed_request, $original_request, $mkcf ){
		// Déplacement des fichiers du répertoire de stokage temporaire vers le repertoire de stockage définitif
		foreach( $parsed_request['fields'] as $slug => &$field ) :
			// Bypass
			if( $field['type'] != 'plupload' ) 
				continue;
						
			foreach( (array) $field['value'] as $k => $value ) :
				// Bypass
				if( ! $file = unserialize( @ base64_decode( $value ) ) ) 
					continue;
					
				$filename 		= $file['tmp_name'];			
				$destination 	= $this->mkcf->dirs->dirname( 'upload' ) ."/". wp_unique_filename( $this->mkcf->dirs->dirname( 'upload' ), $file['name'] );	
								
				if( ! @ copy( $filename, $destination ) )
					wp_die( sprintf( __( '<h1>ERREUR SYSTEME</h1><p>Impossible de déplacer le fichier du champ "%s".</p>', 'tify' ), $field['label'] ) );
					
				@ unlink( $file['tmp_name'] );
				$file['tmp_name']		= $destination;
				$file['name'] 			= basename( $destination );
				$field['value'][$k]		= @ base64_encode( serialize( $file ) );
			endforeach;
		endforeach;
	}

	/**
	 * VUE
	 */
	/**
	 * 
	 */
	function get_file_preview( $file ){
		$file_type = $this->get_file_type( $file );
		
		if( $file_type['type'] === 'image' ) :
			$path =  preg_replace( '/'. preg_quote( ABSPATH, '/' ) .'/', '', $file['tmp_name'] );	
			return "<img src=\"". get_site_url( null, $path ) ."\" />";
		else :
			return "<i class=\"plupload-ico plupload-ico-". ( $file_type['type'] ? $file_type['type'] : 'default' ) ." plupload-ico-". $file_type['ext'] ."\"></i>";
		endif;
	}
	
	/**
	 * 
	 */
	function get_file_input( $file, $field ){
		$output = "";
		$field_class = rtrim( trim( sprintf( $field['field_class'], "field field-{$field['form_id']} field-{$field['slug']}") ) );
		$output .= "<input type=\"hidden\" ";		
		$output .= " name=\"". $this->mkcf->fields->get_name( $field ) ."[]\" id=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$field_class."\"";			
		$output .= " value=\"". @ base64_encode( serialize( $file ) ) ."\"";
		$output .= "/>";
		
		return $output;
	}
	
	/**
	 * CONTROLEURS
	 */
	/**
	 * 
	 */
	function parse_plupload_options( $field ){
		if( empty( $field['options']['plupload']['filters']['mime_types'] ) ) :
			// Types de fichiers autorisés
			foreach( get_allowed_mime_types() as $exts => $title )
				$allowed_mime_types[] = array( 'title' => $title, 'extensions' => preg_replace( '/\|/', ',', $exts ) );
				
			$field['options']['plupload']['filters']['mime_types'] = $allowed_mime_types;
		endif;
		
		if( ! isset( $field['options']['plupload']['filters']['max_file_size'] ) )
			$field['options']['plupload']['filters']['max_file_size'] = wp_max_upload_size() . 'b';
		
		if( empty( $field['options']['plupload']['multipart_params'] ) )
			$field['options']['plupload']['multipart_params'] = array( 'form_id' => $field['form_id'], 'field_slug' => $field['slug'] );
		
		return $field;
	}
	
	/**
	 * Definition du type d'un fichier
	 */
	function get_file_type( $file ){
		$ext = substr( strrchr( $file['name'], "."), 1 );
		return array( 'type' => wp_ext2type( $ext ), 'ext' => $ext );
	}
}