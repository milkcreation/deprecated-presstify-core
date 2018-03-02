<article>
    <h2><?php the_title();?></h2>

    <div><?php the_excerpt();?></div>

    <a href="<?php the_permalink()?>" title="<?php printf(__('Consulter - %s', 'tify'), get_the_title()); ?>"><?php _e( 'Lire la suite', 'tify'); ?></a>
</article>