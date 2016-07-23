<?php
/**
 * ASSOCIATION DE VIDEO
 * 
 * @package Wordpress
 * @subpackage Postbox
 * @author Jordy Manner
 * @copyright Milkcreation
 * @version 1.1
 */

/**
 * Metaboxe de saisie
 */ 
function mkpbx_simple_video_render( $post, $args = array() ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'mkpbx_postbox'
		)
	);
		
	$video = wp_parse_args( get_post_meta( $post->ID, '_video_attachment', true ), array(
			'src' => '',
			'poster' => '',
		)
	);	
?>	
	<div class="simple_video-postbox">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('Url ou code d\'intégration de la vidéo (iframe)', 'mktzr_postbox' );?>							
						</label>
					</th>
					<td>
						<textarea id="simple_video-src" name="<?php echo $args['name'];?>[single][video_attachment][src]" rows="5" cols="40" class="widefat" style="resize:none; vertical-align: top;"><?php echo $video['src'];?></textarea>
						<p><?php _e( 'ou', 'mktzr_postbox' );?>&nbsp;&nbsp;<a href="#" id="select-simple-video-src" class="button-secondary" data-target="#simple_video-src" data-uploader_title="<?php _e( 'Sélectionner une vidéo', 'mktzr_postbox' );?>"><?php _e('Depuis la médiathèque', 'mktzr_postbox');?></a></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Jaquette de la video', 'mktzr_postbox' );?>
					</th>
					<td>
						<?php $bkg = ( $video['poster'] && ( $image = wp_get_attachment_image_src( $video['poster'], 'full' ) ) )?  $image[0] : '' ?>
						<div id="mkpbx-simple_video_poster" style="text-align:center; background-color:#F4F4F4; border:dashed 3px #DDD; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover; background-repeat:no-repeat; background-position:center;width:100%;height:480px; position:relative; 
							<?php if( $bkg ) :?> background-image:url(<?php echo $bkg;?>);<?php endif;?>">
							<a 	href="#mkpbx-simple_video_poster-add"
								id="mkpbx-simple_video_poster-add"
								data-media_library_title="<?php _e('Personnalisation de la jaquette de la vidéo', 'mkpbx');?>"
								data-media_library_button="<?php _e('Utiliser comme jaquette', 'mkpbx');?>"
								style="position:relative; display:block; z-index:5;font-size:24px; color:#AAA; text-decoration:none; font-weight:600; line-height:480px; text-shadow: #666 1px 1px 1px">
								<?php _e( 'Modifier la jaquette', 'mkpbx' );?>
							</a>						
							<input type="hidden" name="<?php echo $args['name'];?>[single][video_attachment][poster]" value="<?php echo $video['poster'];?>" />							
							<a href="#reset-video_poster" title="<?php _e('Supprimer la jaquette', 'mkpbx');?>" class="dashicons dashicons-no reset" style="color:#AA0000; display:block; width:32px; height:32px; position:absolute; z-index:9999; right:10px; top:10px; font-size:32px; display:<?php echo $bkg?'inherit':'none';?>"></a>
						</div>	
					</td>
				</tr>			
			</tbody>
		</table>			
	</div>				
<?php	
}

/**
 * Vérifie l'existance d'une vidéo associée à un post
 */
function has_video( $post_id = null ){
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	return ( ( $video = get_post_meta( $post_id, '_video_attachment', true ) ) && is_array( $video )  && ! empty( $video['src'] ) );
}

/**
 * Affiche la vidéo associée
 */
function the_video( $post_id = null, $args = array() ){	
	echo get_the_video( $post_id, $args );	
}

/**
 * Récupére la vidéo associée
 *
 * @see wp-includes/medias.php - wp_video_shortcode
 */
function get_the_video( $post_id = null, $attr = array() ){
	global $content_width;	
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	static $instances = 0;
	$instances++;
	
	// Bypass
	if( ! has_video( $post_id ) )
		return;
	
	$video = get_post_meta( $post_id, '_video_attachment', true );	
	if( ! empty( $video['poster'] ) && ( $image = wp_get_attachment_image_src( $video['poster'], 'full' ) ) )
		$video['poster'] = $image[0];	
	$attr = wp_parse_args( $attr, $video );
	
	$video = null;
	
	$default_types = wp_get_video_extensions();	
	$defaults_atts = array(
		'src'      => '',
		'poster'   => '',
		'loop'     => '',
		'autoplay' => '',
		'preload'  => 'metadata',		
		'width'    => ! empty( $content_width )? $content_width : 640,
		'height'   => 360
	);
	
	foreach ( $default_types as $type )
		$defaults_atts[$type] = '';
	
	$atts = wp_parse_args( $attr, $defaults_atts );
	extract( $atts );
	
	// if the video is bigger than the theme
	if ( ! empty( $content_width ) && $width > $content_width ) {
		$height = round( ( $height * $content_width ) / $width );
		$width = $content_width;
	}	
	
	$yt_pattern = '#^https?://(:?www\.)?(:?youtube\.com/watch|youtu\.be/)#';

	$primary = false;
	if ( ! empty( $src ) ) {
		if ( ! preg_match( $yt_pattern, $src ) ) {
			$type = wp_check_filetype( $src, wp_get_mime_types() );
				// Iframe
				if( preg_match('/<iframe.*src=\"(.*)\".*><\/iframe>/isU', $src, $matches ) ) {
					return  $matches[0];		
				// Service de streaming video	
				} elseif( $html = @wp_oembed_get( $src, $atts ) ) {
					return $html;		
				// Vidéo serveur local (repertoire médias)	
				} elseif ( ! in_array( strtolower( $type['ext'] ), $default_types ) ) {
					return sprintf( '<a class="wp-embedded-video" href="%s">%s</a>', esc_url( $src ), esc_html( $src ) );
				}
		}
		$primary = true;
		array_unshift( $default_types, 'src' );
	} else {
		foreach ( $default_types as $ext ) {
			if ( ! empty( $$ext ) ) {
				$type = wp_check_filetype( $$ext, wp_get_mime_types() );
				if ( strtolower( $type['ext'] ) === $ext )
					$primary = true;
			}
		}
	}

	if ( ! $primary ) {
		$videos = get_attached_media( 'video', $post_id );
		if ( empty( $videos ) )
			return;

		$video = reset( $videos );
		$src = wp_get_attachment_url( $video->ID );
		if ( empty( $src ) )
			return;

		array_unshift( $default_types, 'src' );
	}

	/**
	 * Filter the media library used for the video shortcode.
	 *
	 * @since 3.6.0
	 *
	 * @param string $library Media library used for the video shortcode.
	 */
	$library = apply_filters( 'wp_video_shortcode_library', 'mediaelement' );
	if ( 'mediaelement' === $library && did_action( 'init' ) ) {
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );
	}

	/**
	 * Filter the class attribute for the video shortcode output container.
	 *
	 * @since 3.6.0
	 *
	 * @param string $class CSS class or list of space-separated classes.
	 */
	$atts = array(
		'class'    => apply_filters( 'wp_video_shortcode_class', 'wp-video-shortcode' ),
		'id'       => sprintf( 'video-%d-%d', $post_id, $instances ),
		'width'    => absint( $width ),
		'height'   => absint( $height ),
		'poster'   => esc_url( $poster ),
		'loop'     => $loop,
		'autoplay' => $autoplay,
		'preload'  => $preload,
	);

	// These ones should just be omitted altogether if they are blank
	foreach ( array( 'poster', 'loop', 'autoplay', 'preload' ) as $a ) {
		if ( empty( $atts[$a] ) )
			unset( $atts[$a] );
	}

	$attr_strings = array();
	foreach ( $atts as $k => $v ) {
		$attr_strings[] = $k . '="' . esc_attr( $v ) . '"';
	}

	$html = '';

	if ( 'mediaelement' === $library && 1 === $instances )
		$html .= "<!--[if lt IE 9]><script>document.createElement('video');</script><![endif]-->\n";
	$html .= sprintf( '<video %s controls="controls">', join( ' ', $attr_strings ) );

	$fileurl = '';
	$source = '<source type="%s" src="%s" />';
	foreach ( $default_types as $fallback ) {
		if ( ! empty( $$fallback ) ) {
			if ( empty( $fileurl ) )
				$fileurl = $$fallback;

			if ( 'src' === $fallback && preg_match( $yt_pattern, $src ) ) {
				$type = array( 'type' => 'video/youtube' );
			} else {
				$type = wp_check_filetype( $$fallback, wp_get_mime_types() );
			}
			$url = add_query_arg( '_', $instances, $$fallback );
			$html .= sprintf( $source, $type['type'], esc_url( $url ) );
		}
	}

	if ( ! empty( $content ) ) {
		if ( false !== strpos( $content, "\n" ) )
			$content = str_replace( array( "\r\n", "\n", "\t" ), '', $content );

		$html .= trim( $content );
	}

	if ( 'mediaelement' === $library )
		$html .= wp_mediaelement_fallback( $fileurl );
	$html .= '</video>';
	
	$width_rule = $height_rule = '';
	if ( ! empty( $atts['width'] ) ) {
		$width_rule = sprintf( 'width: %dpx; ', $atts['width'] );
	}
	if ( ! empty( $atts['height'] ) ) {
		$height_rule = sprintf( 'height: %dpx; ', $atts['height'] );
	}
	$html = sprintf( '<div style="%s%s" class="wp-video">%s</div>', $width_rule, $height_rule, $html );
	
	return $html;
}