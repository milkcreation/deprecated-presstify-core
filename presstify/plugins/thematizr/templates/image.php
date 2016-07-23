<?php get_header(); ?>
<div id="content">
<?php if ( have_posts() ) : ?>
	<?php while (have_posts()) : the_post(); ?>
	
	<div id="post-<?php the_ID(); ?>" <?php post_class('attachment-image post format-image'); ?>>
		<span class="post_icon"><a href="<?php the_permalink() ?>" class="fade" title="<?php the_title(); ?>"><?php the_title(); ?></a></span>
		
		<div class="post_top">
			<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
			<div class="meta clearfix">
				<span class="date"><?php the_time( get_option('date_format').' '. get_option('time_format') ); ?></span>
				<span class="comments"> <?php comments_popup_link( __('0 comments', 'wpzoom'), __('1 comment', 'wpzoom'), __('% comments', 'wpzoom'), '', __('Comments are Disabled', 'wpzoom')); ?></span>
				<?php edit_post_link( __('Edit', 'wpzoom'), '<span>', '</span>'); ?>
				<p>
				<?php
								$metadata = wp_get_attachment_metadata();
								printf( __( 'Taille originale : <a href="%1$s" title="Lien vers la taille originale de l\'image">%2$s &times; %3$s</a> Apparaît dans : <a href="%4$s" title="Retour à  %5$s" rel="gallery">%6$s</a>.', 'wpzoom' ),
									esc_url( wp_get_attachment_url() ),
									$metadata['width'],
									$metadata['height'],
									esc_url( get_permalink( $post->post_parent ) ),
									esc_attr( strip_tags( get_the_title( $post->post_parent ) ) ),
									get_the_title( $post->post_parent )
								);
							?>
				</p>							
			</div>
		</div>
		<div style="text-align:center;">
<?php
	$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
	foreach ( $attachments as $k => $attachment )
		if ( $attachment->ID == $post->ID )
			break;
	$k++;
	// If there is more than 1 attachment in a gallery
	if ( count( $attachments ) > 1 )
		if ( isset( $attachments[ $k ] ) )
			$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
		else
			$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
	else
		$next_attachment_url = wp_get_attachment_url();
?>
			<a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php the_title_attribute(); ?>" rel="attachment">
				<?php echo wp_get_attachment_image( $post->ID, 'maxi-page' );
			?></a>

			<?php if ( ! empty( $post->post_excerpt ) ) : ?>
			<div class="entry-caption">
				<?php the_excerpt(); ?>
			</div>
			<?php endif; ?>		
		</div>
	</div>
	
	<div class="meta clearfix">
		<div id="image-navigation" class="thumbnav" role="navigation" style="text-align:center;">
			<div class="left"><?php previous_image_link( false, '&nbsp;' ); ?></div>		
			<span class="previous-image"><?php previous_image_link( array( 124,124 ) ); ?></span>
			<span class="current-image"><?php echo wp_get_attachment_link( $post->ID, array( 124,124 ), true, false, false );?></span>
			<span class="next-image"><?php next_image_link( array( 124,124 )  ); ?></span>
			<div class="right"><?php next_image_link( false, '&nbsp;' ); ?></div>
		</div><!-- #image-navigation -->
	</div>
	
	<div id="comments">
		<?php comments_template(); ?>  
	</div>
	
	<?php endwhile;?>
<?php else: ?>
	<p><?php _e('Sorry, no posts matched your criteria', 'wpzoom');?>.</p>
<?php endif; ?>

</div>

<?php get_footer(); ?>