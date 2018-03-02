<?php
/**
 * @var array $args {
 *      Attributs de configuration
 *
 *      @var string $name
 *      @var string $post_type
 *      @var string $post_status
 *      @var int $max
 * }
 * @var $wp_query Requête de récupération des éléments
 */
?>

<div class="tiFyTabooxRelatedPosts tiFyTabooxRelatedPosts--<?php echo sanitize_key($args['name']); ?>">
    <ul class="tiFyTabooxRelatedPosts-Items">
        <?php while ($wp_query->have_posts()): $wp_query->the_post(); ?>
        <li class="tiFyTabooxRelatedPosts-Item">
            <a href="<?php the_permalink(); ?>" title="<?php printf(__('En savoir plus sur %s'), get_the_title());?>" class="tiFyTabooxRelatedPosts-ItemLink">
                <figure class="tiFyTabooxRelatedPosts-ItemThumbnail">
                    <?php the_post_thumbnail('thumbnail'); ?>
                </figure>
                <h3 class="tiFyTabooxRelatedPosts-ItemTitle">
                    <?php the_title(); ?>
                </h3>
            </a>
        </li>
        <?php endwhile; ?>
    </ul>
</div>