<?php
/**
 * Metaboxe de saisie
 */ 
function mkpbx_custom_fields_render( $post, $args = array() ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'mkpbx_postbox'
		)
	);
	extract( $args );

	$metadatas = has_meta($post->ID);
	foreach ( $metadatas as $key => $value )
		if ( $metadatas[ $key ][ 'meta_key' ] != '_mkcustom_fields' )
			unset( $metadatas[ $key ] );
		else
			$metadatas[ $key ]['meta_value'] = maybe_unserialize($metadatas[ $key ]['meta_value']);
?>	
	<div id="custom_fields-postbox">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('Champs personnalisés', 'mktzr_postbox' );?>							
						</label>
					</th>
					<td>
						<?php foreach( $metadatas as $metadata ) :?>
						<div>
							<input type="text" name="<?php echo $name;?>[multi][mkcustom_fields][<?php echo $metadata['meta_id'];?>][key]" value="<?php echo esc_attr( $metadata['meta_value']['key'] );?>" />
							<textarea name="<?php echo $name;?>[multi][mkcustom_fields][<?php echo $metadata['meta_id'];?>][value]" style='vertical-align:top;'><?php echo esc_textarea( $metadata['meta_value']['value'] );?></textarea>
							<a href="#" class="dashicons dashicons-no"></a>
						</div>
						<?php endforeach;?>
						<div class="sample">
							<?php $index = uniqid();?>
							<input type="text" name="<?php echo $name;?>[multi][mkcustom_fields][<?php echo $index;?>][key]" />
							<textarea name="<?php echo $name;?>[multi][mkcustom_fields][<?php echo $index;?>][value]" style='vertical-align:top;'></textarea>
							<a href="#" class="dashicons dashicons-no"></a>
						</div>
						<a href="#" id="add-custom_field" class="button-secondary" data-name="<?php echo $name;?>"><?php _e('Ajouter', 'mktzr_postbox');?></a>		
					</td>
				</tr>							
			</tbody>
		</table>			
	</div>				
<?php	
}

/**
 * Nettoyage des metadonnées avant enregistrement
 */
function mkpbx_custom_fields_sanitize_multi_metadata( $metas ){
	if( isset($metas['mkcustom_fields'] ) )
		foreach( $metas['mkcustom_fields'] as $key => $value )
			if( empty( $value['key']) )
				unset(  $metas['mkcustom_fields'][$key]);
			else
				 $metas['mkcustom_fields'][$key]['value'] = stripslashes_deep($value ['value']);	
		
	return $metas;
}
add_filter( 'mkpbx_sanitize_multi_metadata', 'mkpbx_custom_fields_sanitize_multi_metadata' );