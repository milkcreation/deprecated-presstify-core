<?php
namespace tiFy\Core\Taboox\PostType\Fileshare\Admin;

class Fileshare extends \tiFy\Core\Taboox\PostType\Admin
{
    /**
     * DECLENCHEURS
     */
    /**
     * Chargement de la page courante
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        // Traitement des arguments
        $this->args = wp_parse_args($this->args, [
                'name'     => '_tify_taboox_fileshare',
                'filetype' => '', // video || application/pdf || video/flv, video/mp4,
                'max'      => -1,
                // Gestion des métadonnées en mode single
                'single'   => false
            ]);

        // Déclaration des metadonnées à enregistrer
        \tify_meta_post_register($current_screen->id, $this->args['name'], $this->args['single']);
        \tify_meta_post_register($current_screen->id, '_taboox_fileshare_names', true);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        if ($this->args['max'] !== 1) :
            wp_enqueue_style('tify_taboox_fileshare', self::tFyAppUrl(get_class()) . '/Fileshare.css', [], '151216');
            wp_enqueue_media();
            wp_enqueue_script('tify_taboox_fileshare', self::tFyAppUrl(get_class()) . '/Fileshare.js',
                ['jquery', 'jquery-ui-sortable'], '151216', true);
        else :
            tify_control_enqueue('media_file');
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     *
     * @param \WP_Post $post
     *
     * @return string
     */
    public function form($post)
    {
        extract($this->args, EXTR_SKIP);
        $metadatas = $single ? get_post_meta($post->ID, $name, true) : tify_meta_post_get($post->ID, $name);

        if ($this->args['max'] !== 1) :
            ?>
            <div id="fileshare-postbox">
                <input type="hidden" name="tify_meta_post[_taboox_fileshare_names][]"
                       value="<?php echo esc_attr($name); ?>"/>
                <ul id="fileshare-<?php echo sanitize_title($name); ?>-list" class="fileshare-list">
                    <?php if (!empty($metadatas)) : ?>
                        <?php foreach ((array)$metadatas as $meta_id => $meta_value) : ?>
                            <li>
                                <span class="icon"><?php echo wp_get_attachment_image($meta_value, [46, 60],
                                        true); ?></span>
                                <span class="title"><?php echo get_the_title($meta_value); ?></span>
                                <span class="mime"><?php echo get_post_mime_type($meta_value); ?></span>
                                <a href="#" class="remove tify_button_remove"></a>
                                <input type="hidden"
                                       name="<?php echo $single ? "tify_meta_post[{$name}][]" : "tify_meta_post[{$name}][{$meta_id}]"; ?>"
                                       value="<?php echo esc_attr($meta_value); ?>"/>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <a href="#" class="add-fileshare button-secondary"
                    <?php if ($filetype) {
                        echo "data-type=\"{$filetype}\"";
                    } ?>
                   data-item_name="<?php echo $name; ?>"
                   data-target="#fileshare-<?php echo sanitize_title($name); ?>-list"
                   data-max="<?php echo $max; ?>"
                   data-uploader_title="<?php _e('Sélectionner les fichiers à associer', 'tify'); ?>"
                >
                    <span class="dashicons dashicons-media-text" style="vertical-align:middle;"></span>&nbsp;
                    <?php echo _n(__('Ajouter le fichier', 'tify'), __('Ajouter des fichiers', 'tify'),
                        (($max === 1) ? 1 : 2), 'tify'); ?>
                </a>
            </div>
            <?php
        else :
            $_name = $single ? "tify_meta_post[{$name}][]" : "tify_meta_post[{$name}][" . ($metadatas ? key($metadatas) : '') . "]";

            tify_control_media_file([
                    'name'     => $_name,
                    'filetype' => $this->args['filetype'],
                    'value'    => $metadatas ? current($metadatas) : 0
                ]);
        endif;
    }
}