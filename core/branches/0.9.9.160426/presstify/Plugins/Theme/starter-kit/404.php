<?php get_header(); ?>

<div id="site-content">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<article>
					<h1 class="text-center"><?php _e( 'Page introuvable', 'tify' ); ?></h1>
					
					<div class="text-center"><?php _e( 'La page que vous recherchez n\'existe pas.', 'tify' ); ?></p>
					<div class="text-center">
						<a href="<?php echo home_url(); ?>"><?php _e( 'Cliquez ici', 'tify' ); ?></a> 
						<?php _e( 'pour retourner sur la page d\'accueil', 'tify' ); ?>
					</div>
				</article>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>