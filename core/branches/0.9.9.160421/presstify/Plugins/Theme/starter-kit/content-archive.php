<article <?php post_class();?>>
	<h2 class="entry-title">
		<?php the_title();?>
	</h2>
	<div class="entry-content">
		<?php the_excerpt();?>
	</div>
	<div class="entry-readmore">
		<a href="<?php the_permalink()?>" title="<?php printf( __( 'Consulter - %s', 'tity' ), get_the_title() );?>"><?php _e( 'Lire la suite', 'tify' );?></a>
	</div>
</article>