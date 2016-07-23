<?php get_header();?>

<div id="SiteContent" class="SiteContent">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
			 	<div class="WhiteWrapper">		
				<?php if( have_posts() ) : $current_section = false; ?>		
				<div class="ContentArchive ContentArchive--search">
						<header class="ContentHeader"><?php theme_content_header();?></header>
											
						<?php while( have_posts() ) : the_post(); if( ! tify_search_section_post_count() ) continue;?>
						<?php if( $current_section !== tify_search_post_section() ) : ?>						
						<?php $current_section = tify_search_post_section(); $i = 1; $post_type = get_post_type(); ?>
						<div class="ContentArchive-headLines">	
							<h2 class="ContentArchive--title">
								<?php printf( __( '%s (%d)', 'siadep' ), tify_search_section_label(), tify_search_section_found_posts() );?>
							</h2>
							<ul class="ModelList ModelList--block">
						<?php endif; ?>					
								<li>
									<?php get_template_part( 'content-archive' ); ?>
								</li>
						<?php if( ++$i > tify_search_section_post_count() ) :?>
							</ul>
							<?php tify_search_section_showall_link(); ?>	
						</div>	
						<?php endif;?>																			
					<?php endwhile;?>
					
					<footer class="ContentFooter"><?php theme_content_footer();?></footer>											
				<?php else : ?>	
					<div class="ContentNone <?php /* BackgroundWhite */?>">
						<section class="ContentBody">	
						<?php get_template_part( 'content', 'none' );?>
						</section>
					</div>
				<?php endif;?>
				</div>
			</div>	
		</div>		
	</div>	
</div>

<?php get_footer();?>	