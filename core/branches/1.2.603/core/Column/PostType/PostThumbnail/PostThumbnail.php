<?php

namespace tiFy\Core\Column\PostType\PostThumbnail;

use tiFy\Core\Column\ColumnPostType;
use tiFy\Core\Control\Control;

class PostThumbnail extends ColumnPostType
{
    /**
     * Récupération de l'intitulé de la colonne
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Title ? : '<span class="dashicons dashicons-format-image"></span>';
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        Control::enqueue_scripts('HolderImage');
        $this->appAddAction('admin_print_styles');
    }

    /**
     * Styles dynamiques de l'interface d'administration
     *
     * @return string
     */
    public function admin_print_styles()
    {

        $column_name = $this->getColumnName();
        ?><style type="text/css">.wp-list-table th#<?php echo $column_name; ?>,.wp-list-table td.<?php echo $column_name; ?>{width:80px;text-align:center;} .wp-list-table td.<?php echo$column_name; ?> img{max-width:80px;max-height:60px;}</style><?php
    }

    /**
     * Affichage du contenu de la colonne
     *
     * @param string $column Identification de la colonne
     * @param int $post_id Identifiant du post
     *
     * @return string
     */
    public function content($column, $post_id)
    {
        $attachment_id = get_post_thumbnail_id($post_id) ? : 0;

        // Vérifie l'existance de l'image
        if (($attachment = wp_get_attachment_image_src($attachment_id)) && isset($attachment[0]) && ($path = tify_get_relative_url($attachment[0])) && file_exists(ABSPATH . $path)) :
            $thumb = wp_get_attachment_image($attachment_id, [80, 60], true);
        else :
            $thumb = Control::HolderImage();
        endif;

        echo $thumb;
    }
}