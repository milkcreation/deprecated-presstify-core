<?php
/**
 * USAGE :
 * - Méthode 1 (rapide et simple: les scripts sont chargés automatiquement)
 *        <?php echo do_shortcode('tify_taboox_slideshow');?>
 * - Méthode 2
 *        <?php tify_taboox_slideshow_display();?>
 */

namespace tiFy\Core\Taboox\Options\Slideshow\Helpers;

use tiFy\Core\Control\Control;

class Slideshow extends \tiFy\App
{
    /**
     * Nombre d'instance d'appel
     * @var int
     */
    public static $Instance = 0;

    /**
     * Listes des attributs de configuration par instance d'appel
     * @var array
     */
    public static $Attrs = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_slideshow_has', 'has');
        $this->appAddHelper('tify_taboox_slideshow_get', 'get');
        $this->appAddHelper('tify_taboox_slideshow_display', 'display');

        // Déclaration des événements
        $this->appAddAction('wp_loaded', null, 11);
    }

    /**
     * EVENEMENT
     */
    /**
     * A l'issue du chargement complet de Wordpress
     *
     * @return void
     */
    final public function wp_loaded()
    {
        add_shortcode(
            'tify_taboox_slideshow',
            function ($atts) {
                return static::display($atts, false);
            }
        );
    }

    /**
     * Définition de la liste des attributs de configuration
     *
     * @param array $args Liste des attributs de configuration à définir
     *
     * @return array
     */
    public static function setAttrs($args = [])
    {
        // Bypass
        if (!empty(static::$Attrs)) :
            return static::$Attrs;
        endif;

        return static::$Attrs = static::parseAttrs($args);
    }

    /**
     * Traitement de la liste des attributs de configuration
     *
     * @param array $args Liste des attributs de configuration à définir
     *
     * @return array
     */
    public static function parseAttrs($args = [])
    {
        static::$Instance++;

        $defaults = [
            'name'    => 'tify_taboox_slideshow',
            // ID HTML
            'id'      => 'tify_taboox_slideshow-' . static::$Instance,
            // Class HTML
            'class'   => '',
            // Taille des images
            'size'    => 'full',
            // Nombre de vignette maximum
            'max'     => -1,
            // Attribut des vignettes
            'attrs'   => ['title', 'link', 'caption', 'planning'],
            // Options
            'options' => [
                'engine'      => 'tify',
                // Résolution du slideshow
                'ratio'       => '16:9',
                // Navigation suivant/précédent
                'arrow_nav'   => true,
                // Vignette de navigation
                'tab_nav'     => true,
                // Barre de progression
                'progressbar' => false,
            ],
        ];
        $args = (array)$args;

        // Traitement des options
        $name = isset($args['name']) ? $args['name'] : $defaults['name'];

        if (!isset($args['options']) && ($db = get_option($name, false)) && isset($db['options'])) :
            $args['options'] = $db['options'];
        endif;

        foreach ((array)$defaults['options'] as $k => $v) :
            if (!isset($args['options'][$k])) :
                $args['options'][$k] = $v;
            endif;
        endforeach;

        $args = wp_parse_args($args, $defaults);

        // Classe
        $args['class'] = 'tify_taboox_slideshow' . ($args['class'] ? ' ' . $args['class'] : '');

        // Traitement du moteur d'affichage
        if (is_string($args['options']['engine'])) :
            $args['options']['engine'] = [$args['options']['engine'], []];
        endif;

        return $args;
    }

    /**
     * Récupération d'un attribut ou de la liste complète des attributs de configuration
     *
     * @param array $args Liste des attributs de configuration à définir
     *
     * @return array
     */
    public static function getAttrs($attr = null)
    {
        if (is_null($attr)) :
            return static::$Attrs;
        elseif (isset(static::$Attrs[$attr])) :
            return static::$Attrs[$attr];
        endif;
    }

    /**
     * Vérification d'existance d'éléments
     *
     * @param array $args Liste des attributs de récupération
     *
     * @return bool
     */
    public static function has($args = [])
    {
        return static::get($args);
    }

    /**
     * Récupération de la liste des éléments
     *
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($args = [])
    {
        $args = static::setAttrs($args);

        if (!$slideshow = get_option($args['name'], false)) {
            return ['options' => $args['options'], 'slide' => []];
        }

        $slide = isset($slideshow['slide']) ? $slideshow['slide'] : [];

        if (!empty($slide)) :
            $slide = mk_multisort($slide);
        endif;

        $slides = [];

        foreach ((array)$slide as $i => $s) :
            if (empty($s['attachment_id'])) :
                if ($s['post_id'] && ($thumbnail_id = get_post_thumbnail_id($s['post_id']))) :
                    $s['attachment_id'] = $thumbnail_id;
                else :
                    unset($slide[$i]);
                    continue;
                endif;
            endif;

            if (in_array('planning', $args['attrs'])) :
                if (!empty($s['planning']['from']) && (current_time('U') < mysql2date('U',
                            $s['planning']['start']))) :
                    unset($slide[$i]);
                    continue;
                endif;
                if (!empty($s['planning']['to']) && (current_time('U') > mysql2date('U',
                            $s['planning']['end']))) :
                    unset($slide[$i]);
                    continue;
                endif;
            endif;

            $slides[] = wp_parse_args(
                [
                    'src'     => wp_get_attachment_image_url($s['attachment_id'],
                        $args['size']),
                    'alt'     => esc_attr(($alt = get_post_meta($s['attachment_id'],
                        '_wp_attachment_image_alt',
                        true)) ? $alt : get_the_title($s['attachment_id'])),
                    'url'     => (in_array('link',
                            $args['attrs']) && !empty($s['clickable']) && !empty($s['url'])) ? $s['url'] : '',
                    'title'   => (in_array('title',
                            $args['attrs']) && !empty($s['title'])) ? $s['title'] : '',
                    'caption' => (in_array('caption',
                            $args['attrs']) && !empty($s['caption'])) ? $s['caption'] : '',
                ],
                $s
            );
        endforeach;

        $id = $args['id'];
        $class = $args['class'];
        $options = $args['options'];

        return compact('id', 'class', 'slides', 'options');
    }

    /**
     * Affichage de la liste des éléments
     *
     * @param array $args Liste des attributs de récupération
     * @param bool $echo Activation de l'affichage
     *
     * @return string Gabarit d'affichage de la liste des éléments.
     */
    public static function display($args = [], $echo = true)
    {
        // Bypass
        if (!$attrs = static::get($args)) :
            return;
        endif;

        $output = Control::Slider($attrs);

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}