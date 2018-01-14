<?php
namespace tiFy\Components\CustomColumns\PostType\PostThumbnail;

use tiFy\Core\Control\HolderImage\HolderImage;

class PostThumbnail extends \tiFy\Components\CustomColumns\PostType
{
    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [
            'title'    => __('Mini.', 'tify'),
            'position' => 1
        ];
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        HolderImage::enqueue_scripts('holder_image');
        $this->appAddAction('admin_print_styles');
    }

    /**
     * Styles dynamiques de l'interface d'administration
     *
     * @return string
     */
    public function admin_print_styles()
    {
        $column = $this->getAttr('column');
        ?><style type="text/css">.wp-list-table th#<?php echo $column?>,.wp-list-table td.<?php echo $column?>{width:80px;text-align:center;} .wp-list-table td.<?php echo $column?> img{max-width:80px;max-height:60px;}</style><?php
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
        $attachment_id = ($_attachment_id = \get_post_thumbnail_id($post_id)) ? $_attachment_id : 0;

        // Vérifie l'existance de l'image
        if (($attachment = wp_get_attachment_image_src($attachment_id)) && isset($attachment[0]) && ($path = tify_get_relative_url($attachment[0])) && file_exists(ABSPATH . $path)) :
            $thumb = wp_get_attachment_image($attachment_id, [80, 60], true);
        else :
            $thumb = HolderImage::display(null, false);
        endif;

        echo $thumb;
    }
}