<?php get_header();?>

<div id="site-content">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
			<?php if( have_posts() ) :?>
				<div class="<?php echo is_singular() ? 'content-singular' : 'content-archive'; ?>">
				<?php while( have_posts() ) : the_post();?>
					<?php get_template_part( ( is_singular() ? 'content-singular' : 'content-archive' ), get_post_type() );?>
				<?php endwhile;?>
				</div>
			<?php else :?>
				<?php get_template_part( 'content', 'none' );?>
			<?php endif;?>
			</div>
		</div>
	</div>
</div>

<?php get_footer();?>