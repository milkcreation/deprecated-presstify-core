<?php while (tify_search_have_groups()) : tify_search_the_group(); ?>

    <h3><?php printf('%s (%d)', tify_search_group_get_the_title(), tify_search_group_get_found_posts()); ?></h3>

    <?php if (tify_search_have_posts()) : ?>
        <?php while (tify_search_have_posts()) : tify_search_the_post();?>
            <h4><?php the_title(); ?>
        <?php endwhile; ?>
        <?php if ($link = tify_search_group_get_more_link()) :?>
            <a href="<?php echo $link;?>" title="<?php echo __('Afficher plus de résultats', 'tify');?>"><?php echo __('Afficher plus de résultats', 'tify');?></a>
        <?php endif;?>
    <?php else : ?>
        <?php tify_search_group_no_results_found();?>
    <?php endif;?>

<?php endwhile; ?>