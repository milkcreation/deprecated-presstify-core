<?php
namespace tiFy\Statics;

class Media
{
    /**
     * Import de fichier dans la médiathèque
     * @param string $filename chemin relatif ou absolu, ou url vers le fichier 
     * @param array $args
     * 
     */
    public static function import( $filename, $args = array() )
    {
        $defaults = array(
            // Nom du fichier
            'name'                  => '',            
            // Nettoyage du nom de fichier
            'sanitize_name'         => true,            
            // Sous-répertoire du repertoire d'upload pour le stockage du fichier importé (recommander: laisser vide -> répertoire par défaut de Wordpress) 
            'upload_subdir'         => '',
            // Ecrase le fichier
            'override_id'           => 0,
            // Limite de taille du fichier
            'max_size'              => -1,            
            // Contenu d'accroche du fichier
            'post_parent'           => 0,            
            // Mime-Type du fichier (recommander: laisser vide)
            'post_mime_type'        => '',            
            // Url d'accès au fichier (recommander: laisser vide)
            'guid'                  => '',            
            // Intitulé de la page du fichier
            'post_title'            => '',            
            // Contenu de la page du fichier
            'post_content'          => '',            
            // Extrait de la page du fichier
            'post_excerpt'          => ''            
        );
        $args = wp_parse_args( $args, $defaults );
        
        // Définition du nom du fichier
        if( ! $args['name'] ) :
            $args['name'] = basename( $filename );
        endif;        
        if( $args['sanitize_name'] ) :
            $args['name'] = sanitize_file_name( $args['name'] );
        endif;        
        $name = rawurlencode( $args['name'] );
        
        // Définition du chemin
        $path = dirname( $filename );
        /// Chemin absolu local
        if( preg_match( '/'. preg_quote( ABSPATH, '/' ) .'/', $path ) ) :
            $path = site_url() .'/'. preg_replace( '/'. preg_quote( ABSPATH, '/' ) .'/', '', $path );
        // Url locale
        elseif( preg_match( '/'. preg_quote( site_url(), '/' ) .'/', $path ) ) :
        // Url distante
        elseif( preg_match( '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/',$path ) ) :
        // Chemin relatif
        else :
            $path = site_url() .'/'. ltrim( $path, '/' );
        endif; 
        
        $filename = $path . '/' . $name;   
        
        // Récupération de la réponse du serveur
        if ( ! $response = wp_remote_get( $filename ) ) :
            return new \WP_Error( 
                'tiFyStaticsMediasImport_NoResponse', 
                __( 'Le fichier n\'est pas disponible.', 'tify' ) 
            );
        endif;
        
        // Traitement des attributs de la réponse
        $code = wp_remote_retrieve_response_code( $response );
        $message = wp_remote_retrieve_response_message( $response );
        if( $code != '200' ) :
            return new \WP_Error( 
                'tiFyStaticsMediasImport_ErrorCode', 
                sprintf( 
                    __( 'Le serveur distant a retourné l\'erreur suivante : %1$d %2$s', 'tify' ), 
                    esc_html( $message ), 
                    $code 
                ) 
            );
        endif;
        
        // Définition du répertoire d'upload
        if( $args['upload_subdir'] ) :
            $subdir = trim( $args['upload_subdir'], '/' );
            
            $upload_dir = function() use( $subdir ) {
                return array(
            		'path'    => WP_CONTENT_DIR . '/uploads/' . $subdir,
            		'url'     => WP_CONTENT_URL . '/uploads/' . $subdir,
            		'subdir'  => '/'. $subdir,
            		'basedir' => WP_CONTENT_DIR . '/uploads',
            		'baseurl' => WP_CONTENT_URL . '/uploads',
            		'error'   => false,
            	);    
            };
            
            add_filter( 'upload_dir', $upload_dir );
        endif;    
        
        // Définition de l'ecrasement du fichier existant
        if( $args['override_id'] ) :
            add_filter( 
                'wp_unique_filename',
                function( $filename2, $ext, $dir, $unique_filename_callback ) use ( $name ){
                    return $name;
                },
                10,
                4
            );
        endif;
        
        // Traitement du fichier
        $body = wp_remote_retrieve_body( $response );            
        $upload = wp_upload_bits( $args['name'], 0, $body );        
        if( ! empty( $upload['error'] ) ) :
            return new \WP_Error( 'tiFyStaticsMediasImport_UploadBits', $upload['error'] );
        endif;
                
        // Définition de la taille du fichier
        $filesize = filesize( $upload['file'] );
        
        // Vérifie si le fichier n'est pas vide
        if ( 0 == $filesize ) :
            @unlink( $upload['file'] );
            return new \WP_Error( 
                'tiFyStaticsMediasImport_EmptyFile', 
                __( 'Le fichier téléchargé est vide', 'tify' ) 
            );
        endif;
        
        // Vérifie si la taille du fichier n'excède pas la limite
        if ( ( $args['max_size'] > 0 ) && ( $filesize > $args['max_size'] ) ) :
            @unlink( $upload['file'] );
            return new \WP_Error( 
                'tiFyStaticsMediasImport_MaxSizeAttempt', 
                sprintf(
                    __( 'Le taille du fichier dépasse la limite fixée à %s', 'tify' ), 
                    size_format( $args['max_size'] ) 
                ) 
            );
        endif;

        // Traitement des arguments du fichier attaché
        $attachment_attrs =   array(
                'ID'                => $args['override_id'],
                'post_mime_type'    => $args['post_mime_type'] ? $args['post_mime_type'] : $upload['type'],
                'guid'              => $args['guid'] ? $args['guid'] : $upload['url'],
                'post_parent'       => $args['post_parent'],
                'post_title'        => $args['post_title'] ? $args['post_title'] : sanitize_title( $args['name'] ),
                'post_content'      => $args['post_content'],
                'post_excerpt'      => $args['post_excerpt']
        );
        $attachment_id = wp_insert_attachment( $attachment_attrs, $upload['file'] );
        
        if ( ! is_wp_error( $attachment_id ) ) :
            wp_update_attachment_metadata( 
                $attachment_id, 
                wp_generate_attachment_metadata( 
                    $attachment_id, 
                    $upload['file'] 
                ) 
            );
        endif;
        
        return $attachment_id;
    }
    
    /**
     * Récupération de la source base64 d'un fichier média
     * @param $filename chemin absolu | url vers le fichier 
     * @todo Permettre de soumettre un chemins relatif 
     * @return string
     */
	public static function base64Src( $filename )
	{
		if( \tiFy\Lib\Checker::isUrl( $filename ) ) :
            $ext = pathinfo( parse_url( $filename, PHP_URL_PATH ), PATHINFO_EXTENSION );
		else :
            $ext = pathinfo( basename( $filename ), PATHINFO_EXTENSION );
		endif;

		if( ! in_array( $ext, array( 'svg', 'png', 'jpg', 'jpeg' ) ) )
			return;
		
		switch( $ext ) :
			case 'svg' : 
				$data = 'image/svg+xml';
				break;
			default :
				$data = 'image/'. $ext;
				break;
		endswitch;		

		if( ! $content = \tiFy\Lib\File::getContents( $filename ) )
			return;
			
		return "data:{$data};base64,". base64_encode( $content );
	}
}