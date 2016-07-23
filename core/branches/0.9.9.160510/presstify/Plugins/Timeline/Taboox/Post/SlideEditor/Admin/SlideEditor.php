<?php
/* = Formulaire de saisie = */
class Slideditor extends SlideEditor
{
	/* = Formulaire = */
	public function form( $post ){
	?>
	<table class="form-table">
		<?php /*<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Afficher dans la frise des temps', 'mktjs');?></label><br />
			</th>
			<td>
				<label><input type="radio" name="mktjs[active]" value="y" <?php checked( ( get_post_meta( $post->ID, '_mktjs_active', true )? get_post_meta( $post->ID, '_mktjs_active', true ) : 'y'  ) =='y' );?> /> <?php _e('Oui', 'mktjs');?></label>&nbsp;&nbsp;
				<label><input type="radio" name="mktjs[active]" value="n" <?php checked( ( get_post_meta( $post->ID, '_mktjs_active', true )? get_post_meta( $post->ID, '_mktjs_active', true ) : 'y'  ) =='n' );?> /> <?php _e('Non', 'mktjs');?></label>
			</td>
		</tr>*/ ?>				
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Date de début', 'mktjs');?></label><br />
				<em><?php _e('Date de publication de l\'article si vide', 'mktjs');?></em>
			</th>
			<td>
				<input type="text" class="mktjs-datepicker" value="<?php echo mysql2date( 'd/m/Y', get_post_meta( $post->ID, '_mktjs_startDate', true ));?>" />
				<input type="hidden" name="mktjs[startDate]" value="<?php echo get_post_meta( $post->ID, '_mktjs_startDate', true );?>"  />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Date de fin', 'mktjs');?></label><br />
				<em><?php _e('Date de début si vide', 'mktjs');?></em>
			</th>
			<td>
				<input type="text" class="mktjs-datepicker" value="<?php echo mysql2date( 'd/m/Y', get_post_meta( $post->ID, '_mktjs_endDate', true ));?>" />
				<input type="hidden" name="mktjs[endDate]" value="<?php echo get_post_meta( $post->ID, '_mktjs_endDate', true );?>"  />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Titre', 'mktjs');?></label><br />
				<em><?php _e( 'Titre original de l\'article si vide', 'mktjs');?></em>
			</th>
			<td>
				<input type="text" class="widefat" name="mktjs[headline]" value="<?php echo get_post_meta( $post->ID, '_mktjs_headline', true );?>" />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Masquer le texte de description', 'mktjs');?></label><br />
			</th>
			<td>
				<label><input type="radio" name="mktjs[hide_text]" value="y" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_text', true )? get_post_meta( $post->ID, '_mktjs_hide_text', true ) : 'n'  ) =='y' );?> /> <?php _e('Oui', 'mktjs');?></label>&nbsp;&nbsp;
				<label><input type="radio" name="mktjs[hide_text]" value="n" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_text', true )? get_post_meta( $post->ID, '_mktjs_hide_text', true ) : 'n'  ) =='n' );?> /> <?php _e('Non', 'mktjs');?></label>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Texte de description', 'mktjs');?></label><br />			
			</th>
			<td>
				<?php wp_editor( get_post_meta( $post->ID, '_mktjs_text', true ), 'mktjs-text', array( 'textarea_name' => 'mktjs[text]',  'wpautop' => false, 'media_buttons' => false, 'textarea_rows' =>5, 'teeny' => false ) );?>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Masquer le lien de suite de lecture vers le contenu', 'mktjs');?></label><br />
			</th>
			<td>
				<label><input type="radio" name="mktjs[hide_readmore]" value="y" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_readmore', true )? get_post_meta( $post->ID, '_mktjs_hide_readmore', true ) : 'n'  ) =='y' );?> /> <?php _e('Oui', 'mktjs');?></label>&nbsp;&nbsp;
				<label><input type="radio" name="mktjs[hide_readmore]" value="n" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_readmore', true )? get_post_meta( $post->ID, '_mktjs_hide_readmore', true ) : 'n'  ) =='n' );?> /> <?php _e('Non', 'mktjs');?></label>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Intitulé du lien de suite de lecture vers le contenu', 'mktjs');?></label>
			</th>
			<td>
				<input type="text" class="widefat" name="mktjs[readmore]"  placeholder="<?php _e('Lire la suite', 'mktjs');?>" value="<?php echo get_post_meta( $post->ID, '_mktjs_readmore', true );?>" />
			</td>
		</tr>	
	</table>
	<?php //Twitter, YouTube, Flickr, Instagram, TwitPic, Wikipedia, Dailymotion, SoundCloud and Vimeo 
	$asset = wp_parse_args( get_post_meta( $post->ID, '_mktjs_asset', true ), array(
					'media' => '',
					'thumbnail' => '',
					'credit' => '',
					'caption' => ''
				) 
			);	
	?>
	<table class="form-table">
		<?php $types = array(
				'blockquote' => __('Citation', 'mktjs'),
				'website' =>  __('Site Web', 'mktjs'),
				'image' => __( 'Image', 'mktjs'),
				'twitter' => __('Twitter', 'mktjs'),
				'youtube' => __('YouTube', 'mktjs'),
				'dailymotion' => __('Dailymotion', 'mktjs'),
				'vimeo' => __('Vimeo', 'mktjs'),
				'flickr' => __('Flickr', 'mktjs'),
				'soundcloud' => __('Soundcloud', 'mktjs'),
				'instagram' => __('Instagram', 'mktjs'), 
				'twitpic' => __('TwitPic', 'mktjs') 
			); 
		?>				
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Média', 'mktjs');?></label><br />
				<?php /*<select>
					<option value=""><?php _e( 'Choisir un type de média', 'mktjs' );?></option>
					<?php foreach(  $types as $slug => $label ) :?>
					<option value="<?php echo $slug;?>"><?php echo $label;?></option>
					<?php endforeach;?>
				</select> */?>
			</th>
			<td>
				<div>
					<textarea id="mktjs-asset-media" name="mktjs[asset][media]" class="widefat" rows="4" style=""><?php echo $asset['media'];?></textarea>
					<p>
						<a href="#" id="mktjs-add-local-image" class="button-secondary" data-target="#mktjs-asset-media" data-uploader_title="<?php _e( 'Sélectionner une image', 'mktjs' );?>">
							<?php _e('Image de la médiathèque', 'mktjs');?>
						</a>
						<a href="#" id="mktjs-add-local-video" class="button-secondary" data-target="#mktjs-asset-media" data-uploader_title="<?php _e( 'Sélectionner une video', 'mktjs' );?>">
							<?php _e('Video de la médiathèque', 'mktjs');?>
						</a>
					</p>
					<p>
						les contenus suivants sont autorisés : Citations (texte simple), Les images (utiliser le bouton), url de site, url de service de streaming vidéo (Youtube, Dailymotion, Vimeo ...),
						url de service de streaming image (Flickr, Instagram ...), url de service de streaming audio (Soundcloud), url de Tweet texte et image ... 					
					</p>
					<?php /*<div href="#" id="mktjs-asset-thumbnail" class="add-thumb-area-50" data-name="mktjs[asset][thumbnail]" data-uploader_title="<?php _e( 'Sélectionner une jaquette', 'mktzr_postbox' );?>">
						<div class="poster">
							<?php if( $asset['thumbnail'] && ( $image = wp_get_attachment_image_src( $asset['thumbnail'], 'thumbnail' ) ) ) :?>								
								<img width="50" height="50" src="<?php echo $image[0];?>" />
								<input type="hidden" name="mktjs[asset][thumbnail]" value="<?php echo $asset['thumbnail'];?>" />
								<a href="#" class="mkpbx-sprite remove" data-action="parent-empty"></a>								
							<?php endif;?>
						</div>
						<a href="#add" class="add"><?php _e('Cliquez ici', 'mktzr_postbox');?></a>
					</div>*/?>	
				</div>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Crédit', 'mktjs');?></label>
			</th>
			<td>
				<input type="text" class="widefat" name="mktjs[asset][credit]" value="<?php echo $asset['credit'];?>" />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Légende', 'mktjs');?></label>
			</th>
			<td>
				<textarea class="widefat" name="mktjs[asset][caption]"><?php echo $asset['caption'];?></textarea>
			</td>
		</tr>			
	</table>
	<?php
	}
}