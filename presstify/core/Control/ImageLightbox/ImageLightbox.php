<?php
/**
 * @name ImageLightbox
 * @desc Controleur d'affichage de modale image
 * @package presstiFy
 * @namespace tiFy\Core\Control\ImageLightbox
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\ImageLightbox;

/**
 * @Overrideable \App\Core\Control\ImageLightbox\ImageLightbox
 *
 * <?php
 * namespace \App\Core\Control\ImageLightbox
 *
 * class ImageLightbox extends \tiFy\Core\Control\ImageLightbox\ImageLightbox
 * {
 *
 * }
 */

class ImageLightbox extends \tiFy\Core\Control\Factory
{
    /**
     * Groupes
     */
    protected static $Group = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        \wp_register_script(
            'tify_control-image_lightbox',
            self::tFyAppAssetsUrl('ImageLightbox.js', get_class()),
            ['tify-imagelightbox'],
            170724,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style('tify-imagelightbox');
        \wp_enqueue_script('tify_control-image_lightbox');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            // Marqueur d'identification unique
            'id'              => 'tiFyControl-imageLightbox-' . $this->getId(),
            // Id Html du conteneur
            'container_id'    => 'tiFyControlImageLightbox--' . $this->getId(),
            // Classe Html du conteneur
            'container_class' => '',
            // Groupe 
            'group'           => '',
            // Options
            'options'         => [],
            // Source de l'image
            'src'             => 'https://fr.facebookbrand.com/wp-content/uploads/2016/05/FB-fLogo-Blue-broadcast-2.png',
            // Liste des slides
            'content'         => ''
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        $options = wp_parse_args(
            $options,
            [
                // Couleur du theme
                'theme'           => 'dark',
                // Fond couleur
                'overlay'         => true,
                // Indicateur de chargement
                'spinner'         => true,
                // Bouton de fermeture
                'close_button'    => true,
                // Légende (basé sur le alt de l'image)
                'caption'         => true,
                // Flèche de navigation suivant/précédent
                'navigation'      => true,
                // Onglets de navigation
                'tabs'            => true,
                // Control au clavier
                'keyboard'        => true,
                // Fermeture au clic sur le fond
                'overlay_close'   => true,
                // Vitesse de défilement
                'animation_speed' => 250
            ]
        );

        if (!$content && !is_null($content)) {
            $content = "<img src=\"{$src}\" alt=\"" . basename($src) . "\">";
        }

        $output = "";
        $output .= "<a href=\"{$src}\" id=\"{$container_id}\" class=\"tiFyControlImageLightbox" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"image_lightbox\" data-options=\"" . htmlentities(json_encode($options)) . "\" data-group=\"{$group}\">\n";
        $output .= $content;
        $output .= "</a>\n";

        if ($group && !in_array($group, self::$Group)) :
            array_push(self::$Group, $group);
            add_action('wp_footer', function () use ($group, $options) {
                echo "<input id=\"tiFyControlImageLightbox-groupOption--{$group}\" type=\"hidden\" value=\"" . htmlentities(json_encode($options)) . "\" />";
            });
        endif;

        echo $output;
    }
}