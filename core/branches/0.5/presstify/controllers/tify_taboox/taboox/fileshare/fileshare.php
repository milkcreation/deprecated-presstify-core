<?php
/**
 * Metaboxe de saisie
 */ 
function mkpbx_fileshare_render( $post, $args = array() ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' 		=> 'fileshare',
			'filetype' 	=> '', // video || application/pdf || video/flv, video/mp4,
			'max' 		=> -1
		)
	);
	extract( $args );
	
	$metadatas = has_meta($post->ID);

	foreach ( $metadatas as $key => $value )
		if ( $metadatas[ $key ][ 'meta_key' ] != '_'.$name )
			unset( $metadatas[ $key ] );
		else
			$metadatas[ $key ]['meta_value'] = maybe_unserialize( $metadatas[ $key ]['meta_value'] );
		
	$orderly = ( $_orderly =  get_post_meta( $post->ID, '_'.$name.'_order', true ) ) ? $_orderly : array();

	foreach ( (array) $metadatas as $key => $params ) :
		$metadatas[$key]['order'] = array_search( $params['meta_value'], $orderly );
		$meta_value_order[$key] = $metadatas[$key]['order'];
	endforeach;

	@array_multisort( $meta_value_order, $metadatas );
?>
	<div id="fileshare-postbox">
		<input type="hidden" name="mkpbx_postbox[fileshare][names][]" value="<?php echo esc_attr( $name );?>" />
		<ul id="fileshare-<?php echo sanitize_title($name);?>-list" class="fileshare-list">
		<?php foreach( $metadatas as $n => $metadata ) : ?>
			<li>
				<span class="icon"><?php echo  wp_get_attachment_image( $metadata['meta_value'],  array(46,60), true );?></span>
				<span class="title"><?php echo get_the_title( $metadata['meta_value'] );?></span>
				<span class="mime"><?php echo get_post_mime_type( $metadata['meta_value'] );?></span>							
				<a href="#" class="remove tify_button_remove"></a>
				<input type="hidden" name="mkpbx_postbox[multi][<?php echo $name;?>][<?php echo $metadata['meta_id'];?>]" value="<?php echo esc_attr( $metadata['meta_value'] );?>" />
				<input type="hidden" class="order" name="mkpbx_postbox[single][<?php echo $name.'_order';?>][]" value="<?php echo esc_attr( $metadata['meta_value'] );?>" />						
			</li>
		<?php endforeach;?>		
		</ul>
		<a href="#" class="add-fileshare button-secondary" 
			<?php if( $filetype ) echo "data-type=\"{$filetype}\"";?> 
			data-item_name="<?php echo $name;?>" 
			data-target="#fileshare-<?php echo sanitize_title($name);?>-list"
			data-max="<?php echo $max;?>"
			data-uploader_title="<?php _e( 'Sélectionner les fichiers à associer', 'tify' );?>"
		>
			<div class="dashicons dashicons-media-text" style="vertical-align:middle;"></div>&nbsp;
			<?php echo _n( __(  'Ajouter le fichier', 'tify' ), __(  'Ajouter des fichiers', 'tify' ), ( ( $max === 1 ) ? 1 : 2 ), 'tify'  );?>
		</a>
	</div>			
<?php	
}

/**
 * Sauvegarde
 */
function mkpbx_fileshare_sanitize_multi_metadata( $metas, $post ){
	if( ! isset( $_REQUEST['mkpbx_postbox']['fileshare']['names'] ) )
		return $metas;

	$exists = has_meta( $post->ID );
	
	foreach( $_REQUEST['mkpbx_postbox']['fileshare']['names'] as $name ) :
		foreach( $exists as $exist ) :
			if( $exist['meta_key'] != '_'.$name )
				continue;
			if( ! isset( $metas[$name][$exist['meta_id']] ) )
				delete_metadata_by_mid( 'post', $exist['meta_id'] );
		endforeach;
	endforeach;
	
	return $metas;	
}
add_filter( 'mkpbx_sanitize_multi_metadata', 'mkpbx_fileshare_sanitize_multi_metadata', 10, 2 );

/**
 * Vérification de l'existance de métadonnées
 */ 
function mkpbx_fileshare_has_file( $post_id = null, $args = array() ){
	global $post;
	
	if( $post_id )
		$post = get_post( $post_id );
	else
		$post = get_post( $post );	
	// Bypass
	if( !$post )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'fileshare',
			'filetype' => '', // video || application/pdf || video/flv, video/mp4,
			'max' => -1
		)
	);
	extract( $args );
	
	$files = get_post_meta($post->ID, '_'.$name );
		
	if( ! is_array( $files) )
		return false;

	return $files;
}

/**
 * Récupération des fichiers partagés
 */
function mkpbx_fileshare_get_files( $post_id = null, $args = array() ){
	global $post;

	if( $post_id )
		$post = get_post( $post_id );
	else
		$post = get_post( $post );	
	
	// Bypass
	if( !$post )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'fileshare',
			'filetype' => '', // video || application/pdf || video/flv, video/mp4,
			'max' => -1
		)
	);
	extract( $args );
		
	if( ! $attachment_ids = get_post_meta( $post->ID, '_'.$name ) )
		return;
	
	// Trie des fichiers
	if( $attachment_order = get_post_meta( $post->ID, '_'.$name.'_order', true ) ) :
		$_attachment_ids = array(); $_attachment_order = array();
		foreach ( (array) $attachment_ids as $key => $attachment_id ) :
			$_attachment_ids[$key] = $attachment_id;
			$_attachment_order[$key] = array_search( $attachment_id, $attachment_order );
		endforeach;
		@array_multisort( $_attachment_order, $_attachment_ids );
	else : 
		$_attachment_ids = $attachment_ids;
	endif;
	
	return $_attachment_ids;
}

/**
 * Récupération des fichiers en partage
 */
function get_the_fileshare( $args = array() ){
	// Bypass
	global $post;
	if( ! $post )
		return $return;
	
	$defaults = array(
		'echo' => true,
		'name' => 'fileshare',
		'filetype' => '', // video || application/pdf || video/flv, video/mp4,
		'max' => -1
	);		
	$args = wp_parse_args( $args, $defaults);	
	extract( $args );	
			
	$upload_dir = wp_upload_dir();
	$upload_path = $upload_dir['path'];
	$upload_url = $upload_dir['url']; 
	
	$output  = ""; 
	$output .= "<ul>";
	foreach( (array) mkpbx_fileshare_get_files( $post->ID, $args ) as $file_id ) :
		$fileurl = wp_get_attachment_url( $file_id );
		$filename = $upload_path.'/'.wp_basename( $fileurl );
		$ext = preg_replace( '/^.+?\.([^.]+)$/', '$1', $fileurl );
		$filesize = 0;
		if( file_exists( $filename ) )
			$filesize = round( filesize( $filename ), 2);
		
		$thumb_url = false;	
		if ( ( $attachment_id = intval( $file_id ) ) && $thumb_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail', false ) )
			$thumb_url = $thumb_url[0];
		
		$output .= "\n<li class=\"mkpbx_fileshare mkpbx_fileshare-".$name."\">";
		$output .= "\n\t<a href=\"" . add_query_arg( array( 'file_upload_media' => $file_id, 'post_id' => $post->ID, 'fileshare_name' => $name ), site_url() ) . "\" class=\"fileshare_link clearfix\"  title=\"" . __( 'Télécharger le fichier', 'tify' ) ."\">";
		
		// Icone
		if( $thumb_url )
			$output .= "\n\t\t<img src=\"$thumb_url\" class=\"mimetype_ico\" />"; 
		else
			$output .= "\n\t\t<i class=\"mkpbx_fileshare_ico mkpbx_fileshare_ico-" . $ext ."\"></i>"; 
		
		// Titre du fichier		
		$output .= "\n\t\t<span class=\"fileshare_title\">". get_the_title( $file_id ) ."</span>";
		
		// Nom du fichier
		$output .= "\n\t\t<span class=\"fileshare_basename\">" . wp_basename( $fileurl ) . "</span>";
		
		// Poids du fichiers				
		if(  $filesize )
			$output .= "\n\t\t<span class=\"fileshare_size\">". mkpbx_fileshare_format_bytes( $filesize ) . "\n\t\t</span>";
		
		$output .= "\n\t\t<span class=\"download-label\">".__( 'Télécharger', 'tify'  )."</span>";
		
		$output .= "\n\t</a>";
		$output .= "\n</li>";
		
		$output = apply_filters( 'mk_file_share_display_element', $output, $file_id );
		
	 endforeach;
	$output .= "</ul>"; 
	
	if( $echo)
		echo $output;
	else
		return $output; 
}

/**
 * Autorisation de forçage de téléchargement du fichier
 */
function mkpbx_fileshare_allow_file_upload( $return, $filename ){
	// Bypass
	if( !isset( $_REQUEST['post_id'] ) )
		return $return;
	if( !isset( $_REQUEST['fileshare_name'] ) )
		return $return;	
	if( ! $attachment_ids = get_post_meta( $_REQUEST['post_id'], '_'.$_REQUEST['fileshare_name'] ) )
		return $return;
	
	$attachment_path = array();
	foreach( $attachment_ids as $attachment_id )
		$attachment_path[] = get_attached_file( $attachment_id );	
	
	return in_array( $filename, $attachment_path );
}
add_filter( 'mk_force_file_upload_allowed', 'mkpbx_fileshare_allow_file_upload', 10, 2 );

/**
 * Convertion du poids des fichiers
 */
function mkpbx_fileshare_format_bytes($a_bytes){
    if ($a_bytes < 1024) {
        return $a_bytes .' B';
    } elseif ($a_bytes < 1048576) {
        return round($a_bytes / 1024, 2) .' Ko';
    } elseif ($a_bytes < 1073741824) {
        return round($a_bytes / 1048576, 2) . ' Mo';
    } elseif ($a_bytes < 1099511627776) {
        return round($a_bytes / 1073741824, 2) . ' Go';
    } elseif ($a_bytes < 1125899906842624) {
        return round($a_bytes / 1099511627776, 2) .' To';
    } elseif ($a_bytes < 1152921504606846976) {
        return round($a_bytes / 1125899906842624, 2) .' Po';
    } elseif ($a_bytes < 1180591620717411303424) {
        return round($a_bytes / 1152921504606846976, 2) .' Eo';
    } elseif ($a_bytes < 1208925819614629174706176) {
        return round($a_bytes / 1180591620717411303424, 2) .' Zo';
    } else {
        return round($a_bytes / 1208925819614629174706176, 2) .' Yo';
    }
}