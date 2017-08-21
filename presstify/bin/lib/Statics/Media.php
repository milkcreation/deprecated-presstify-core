<?php
namespace tiFy\Statics;

class Media
{
    /**
     * Import de fichier dans la médiathèque
     * @param string $filename chemin relatif ou absolu, ou url du fichier 
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
        $name = $args['name'];
        
        // Définition du chemin d'accès au fichier source
        $is_url = false;
        /// Chemin absolu local
        if( preg_match( '/'. preg_quote( ABSPATH, '/' ) .'/', $filename ) ) :            
        /// Url locale
        elseif( preg_match( '/'. preg_quote( site_url(), '/' ) .'/', $filename ) ) :
            $filename = ABSPATH . preg_replace( '/'. preg_quote( site_url(), '/' ) .'/', '', $filename );
        /// Url distante
        elseif( preg_match( '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $filename ) ) :
            $is_url = true;
        /// Chemin relatif
        elseif( file_exists( ABSPATH .'/'. ltrim( $filename, '/' ) ) ) :
            $filename = ABSPATH .'/'. ltrim( $filename, '/' );
        endif; 
        
        // Récupération du contenu du fichier source
        if( $is_url ) :
            $filename = rawurlencode( $filename );
            // Récupération de la réponse du serveur
            if ( ! $response = wp_remote_get( $filename ) ) :
                return new \WP_Error( 
                    'tiFyStaticsMediasImport_NoResponse', 
                    __( 'Le fichier n\'est pas disponible.', 'tify' ) 
                );
            endif;            
            // Traitement des attributs de la réponse
            $code = \wp_remote_retrieve_response_code( $response );
            $message = \wp_remote_retrieve_response_message( $response );
    
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
            $content = \wp_remote_retrieve_body( $response );  
        elseif( file_exists( $filename ) ) :              
            $content = file_get_contents( $filename );
        else :
            return new \WP_Error(
                'tiFyStaticsMediasImport_FileNotExist', 
                __( 'Impossible de récupérer le fichier source', 'tify' )
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
        $upload = wp_upload_bits( $name, 0, $content );        
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
                'post_title'        => $args['post_title'] ? $args['post_title'] : sanitize_title( $name ),
                'post_content'      => $args['post_content'],
                'post_excerpt'      => $args['post_excerpt']
        );
        $attachment_id = wp_insert_attachment( $attachment_attrs, $upload['file'] );
        
        if ( ! is_wp_error( $attachment_id ) ) :
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        
            \wp_update_attachment_metadata( 
                $attachment_id, 
                \wp_generate_attachment_metadata( 
                    $attachment_id, 
                    $upload['file'] 
                ) 
            );
        endif;
        
        return $attachment_id;
    }
    
    /**
     * Mise à jour des médias liés à un post 
     * @param int $post_id identifiant du post d'accroche
     * @param array | string filenames chemins relatifs ou absolus, ou url des fichiers à attacher
     * @param array $args attributs globaux de mise à jour des fichiers attachés
     */
    public static function updateAttachments( $post_id, $filenames = array(), $args = array() )
    {
        $defaults = array(
            // Sous-répertoire d'upload
            'upload_subdir'                         => '',
            // Traite uniquement les fichiers attachés du sous-repertoire d'upload
            'attachments_in_subdir_only'            => true
        );
        $args = wp_parse_args( $args, $defaults );
        
        $args['post_type'] = 'attachment';
        $args['post_status'] = 'inherit';
        $args['post_parent'] = $post_id;
        $args['fields'] = 'ids';
        
        // Récupération la liste des fichiers attachés au contenu existants
        $query_args = array_diff_key( $args, array_flip( array( 'upload_subdir', 'attachments_in_subdir_only' ) ) );
        $exists = array();
        
        $attachment_query = new \WP_Query;
        if( $attachments = $attachment_query->query( $query_args ) ) :            
            foreach( $attachments as $attachment_id ) :                
                $filename = get_attached_file( $attachment_id, true );
                // Filtrage des fichiers hors du sous-repertoire d'upload
                if( $args['upload_subdir'] && $args['attachments_in_subdir_only'] ) :
                    preg_match( '/^'.  preg_quote( WP_CONTENT_DIR . '/uploads', '/' ) .'\/(.*)\/'. basename( $filename ).'/', $filename, $match );
                    if( ! isset( $match[1] ) || ( $match[1] !== trim( $args['upload_subdir'], '/' ) ) )
                        continue;
                endif;
                $exists[basename( $filename )] = array( 
                    'attachment_id' => (int) $attachment_id, 
                    'filemtime'     => ( $filemtime = get_post_meta( $attachment_id, '_filemtime', true ) ) ? (int) $filemtime : 0                   
                );
            endforeach;
        endif;               
                
        // Traitement des fichiers à attacher
        $attachment_ids = array();
        $import_args = array_intersect_key( $args, array_flip( array( 'upload_subdir' ) ) );
        
        foreach( (array) $filenames as $filename ) :
            // Nom du fichier
            $name = basename( $filename );
            // Date de modification du fichier
            $filemtime = filemtime( $filename );
            
            // Importe le fichier s'il n'existe pas ou si celui-ci a été modifié 
            if( ! isset( $exists[$name] ) || ( $exists[$name]['filemtime'] !== $filemtime )  ) :
                $attachment_ids[] = $attachment_id = self::import( 
                    $filename,
                    wp_parse_args(
                        $import_args, 
                        array(
                            'post_parent'       => $post_id,
                            'override_id'       => ( isset( $exists[$name] ) ) ? $exists[$name]['attachment_id'] : 0
                        )
                    )
                );
            // Le fichier attaché existe déjà et n'a pas été modifié    
            else :
                $attachment_ids[] = $attachment_id = $exists[$name]['attachment_id'];
            endif;
            
            // Le fichier attaché existant traité est exclu de la liste
            if( isset( $exists[$name] ) ) :
                unset( $exists[$name] );
            endif;
            
            if( ! is_wp_error( $attachment_id ) ) :
                update_post_meta( $attachment_id, '_filemtime', $filemtime );
            endif;
        endforeach;
        
        // Suppression des fichiers attachés non traités
        foreach( $exists as $exist ) :
            wp_delete_post( $exist['attachment_id'] );
        endforeach;
        
        return $attachment_ids;
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