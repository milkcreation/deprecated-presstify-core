<?php get_header();?>

<?php if( have_posts() ) : $current_section = false; ?>
    <div class="tiFySearchResults">
    <?php while( have_posts() ) : the_post(); if( ! tify_search_section_post_count() ) continue;?>
        <?php if( $current_section !== tify_search_post_section() ) : $current_section = tify_search_post_section(); $i = 1;?>
        <div class="tiFySearchResults-section">
            <h2 class="tiFySearchResults-sectionTitle">
                <?php printf( __( '%s (%d)', 'tify' ), tify_search_section_label(), tify_search_section_found_posts() );?>
            </h2>
            <ul class="tiFySearchResults-sectionItems">
        <?php endif; ?>
                <li class="tiFySearchResults-sectionItem">
                    <?php get_template_part( 'content', 'archive' ); ?>
                </li>
        <?php if( ++$i > tify_search_section_post_count() ) :?>
            </ul>
            <?php tify_search_section_showall_link(); ?>
        </div>
        <?php endif;?>
    <?php endwhile;?>
    </div>
    <footer class="ContentFooter"><?php theme_content_footer();?></footer>
<?php endif;?>

<?php get_footer();?>