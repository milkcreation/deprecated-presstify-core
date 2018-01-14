<?php
/**
 * 
 */
function mkpbx_product_types_thumbnail() {
	add_action ( 'product_types_edit_form_fields', 'mkpbx_product_types_edit_form_fields' );
	add_action ( 'edited_product_types', 'mkpbx_edited_product_types' );
	
	add_filter( "manage_edit-product_types_columns", 'mkpbx_manage_edit_product_types_posts_custom_column' );
	add_filter( "manage_product_types_custom_column", 'mkpbx_manage_product_types_custom_column', 10, 3 );
}
add_action('init', 'mkpbx_product_types_thumbnail');

/**
 * 
 */
function mkpbx_product_types_edit_form_fields($tag){
	wp_enqueue_media();
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label>Image</label></th>
		<td>
			<div style="width:150px; height:150px; border:dashed 4px #BBBBBB; background-color:#E4E4E4; position:relative;" id="taxonomy-image">
				<a href="#add-taxonomy-image" id="add-taxonomy-image" style="display:block; position:absolute; top:0; right:0; bottom:0; left:0;" data-uploader_title="<?php _e( 'Galerie d\'image', 'mkpbx_postbox' );?>"></a>
				<?php if( ( $attachment_id = get_option( 'taxonomy-image-'.$tag->term_id ) ) && ( $image = wp_get_attachment_image_src($attachment_id, 'thumbnail') ) ) :?>
					<div id="selected-taxonomy-image"> 
						<img src="<?php echo $image[0];?>" />
						<input type="hidden" name="taxonomy-image" value="<?php echo $attachment_id;?>" />
						<a href="#remove" class="remove" style="position:absolute; display:block; z-index:99; right:0; top:0; color:red; text-decoration:none; width:30px;height:30px;"><i class="dashicons dashicons-no" style="font-size:30px;" ></i></a>
					</div>
				<?php endif;?>				
			</div>
		</td>
	</tr>
	<script>
		var taxo_img_frame;
		jQuery(document).ready( function($){
			// Affichage du selecteur de média
			$(document).on('click', '#add-taxonomy-image', function( e ){
				e.preventDefault();

				var name = $(this).data('name');

				if ( taxo_img_frame ) {
					taxo_img_frame.open();
					return;
				}

				taxo_img_frame = wp.media.frames.file_frame = wp.media({
					title: $( this ).data( 'uploader_title' ),
					editing:    true,
					button: {
						text: $( this ).data( 'uploader_button_text' )
					},
					multiple: false,
					library:{ type: 'image'}// ['image/gif','image/png']}
				});

				taxo_img_frame.on( 'select', function() {
					attachment = taxo_img_frame.state().get('selection').first().toJSON();
					var html = "<a href=\"#add-taxonomy-image\" id=\"add-taxonomy-image\" style=\"display:block; position:absolute; top:0; right:0; bottom:0; left:0;\" data-uploader_title=\"<?php _e( 'Galerie d\'image', 'mkpbx_postbox' );?>\"></a>";
					html += "<div id=\"selected-taxonomy-image\">"; 
					html += "<img src=\""+attachment.sizes.thumbnail.url+"\" />";
					html += "<input type=\"hidden\" name=\"taxonomy-image\" value=\""+attachment.id+"\" />";
					html += "<a href=\"#remove\" class=\"remove\" style=\"position:absolute; display:block; z-index:99; right:0; top:0; color:red; text-decoration:none; width:30px;height:30px;\"><i class=\"dashicons dashicons-no\" style=\"font-size:30px;\" ></i></a>";
					html += "</div>";
					$( "#taxonomy-image" ).html(  html );
				});	 

				taxo_img_frame.open();
			});
			$( "#taxonomy-image " ).disableSelection();
			// Suppression de l'image
			$( document ).on( 'click', '#taxonomy-image .remove', function(e){
				$container = $(this).parent();
				$container.fadeOut( function(){
					$container.remove();
				});
			});
		});
	</script>	
 <?php
 }

 /**
  * 
  */
 function mkpbx_edited_product_types( $term_id ){
	if ( isset( $_POST['taxonomy-image'] ) )
		update_option( 'taxonomy-image-'.$term_id, $_POST['taxonomy-image'] );
	else
		delete_option( 'taxonomy-image-'.$term_id );
 }

 /**
  * 
  */
function mkpbx_manage_edit_product_types_posts_custom_column( $columns ){
	$new_columns['cb'] = '<input type="checkbox" />';	
	$new_columns['thumbnail'] = __( 'Mini.', 'mktzr' );
	$new_columns += $columns;
	
	return $new_columns;
}

/**
 * 
 */
function mkpbx_manage_product_types_custom_column( $output, $column_name, $term_id ){
	if( $column_name == 'thumbnail' && ( $attachment_id = get_option( 'taxonomy-image-'.$term_id ) ) && ( $image = wp_get_attachment_image($attachment_id, 'thumbnail' ) ) )
		$output = $image;
	echo  $output;
}