<?php

namespace tiFy\Core\Taboox\Options\Slideshow\Admin;

use tiFy\Core\Control\Control;

class Slideshow extends \tiFy\Core\Taboox\Options\Admin
{
    /**
     * Instance courante
     * @var integer
     */
    static $Instance = 0;

    /**
     * Action Ajax de récupération de contenu
     * @var string
     */
    public $Action = null;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {
        self::$Instance++;

        // Traitement des arguments
        $this->args = wp_parse_args(
            $this->args,
            [
                'name'      => 'tify_taboox_slideshow',
                // Interface de recherche de contenu du site
                'suggest'   => true,     // Booléen | array @see tify_control_suggest
                // Autorise les doublons
                'duplicate' => false,
                // Interface de création d'une vignette personnalisée
                'custom'    => true,
                // Nombre de vignette maximum
                'max'       => -1,
                // Attribut des vignettes
                'attrs'     => ['title', 'link', 'caption', 'planning'],
                // Options d'affichage
                'options'   => []
            ]
        );

        $this->args['options'] = wp_parse_args(
            $this->args['options'],
            [
                // Moteur d'affichage
                'engine'      => 'tify',
                // Résolution du slideshow
                'ratio'       => '16:9',
                // Taille des images
                'size'        => 'full',
                // Navigation suivant/précédent
                'nav'         => true,
                // Vignette de navigation
                'tab'         => true,
                // Barre de progression
                'progressbar' => false
            ]
        );

        \register_setting($this->page, $this->args['name']);

        $this->Action = 'tify_taboox_slideshow_item-' . self::$Instance;
        add_action('wp_ajax_' . $this->Action, [$this, 'wp_ajax']);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        // Déclaration des scripts
        wp_register_script('tinyMCE-editor', includes_url('js/tinymce') . '/tinymce.min.js', [], ' 4.1.4', true);
        wp_register_script('jQuery-tinyMCE', '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.1.4/jquery.tinymce.min.js', ['jquery', 'tinyMCE-editor'], true);

        wp_enqueue_media();
        wp_enqueue_style('tiFyTabooxOptionSlideshowAdmin', self::tFyAppUrl(get_class()) . '/Slideshow.css', ['tify_control-suggest', 'tify_control-media_image', 'tify_control-touch_time'], '160222');
        wp_enqueue_script('tiFyTabooxOptionSlideshowAdmin', self::tFyAppUrl(get_class()) . '/Slideshow.js', ['jQuery-tinyMCE', 'tify_control-suggest', 'tify_control-media_image', 'tify_control-touch_time', 'jquery-ui-sortable'], '160222', true);
        wp_localize_script(
            'tiFyTabooxOptionSlideshowAdmin',
            'tiFyTabooxOptionSlideshowAdmin',
            [
                'l10nMax' => __('Nombre maximum de vignettes atteint', 'tify')
            ]
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    public function form()
    {
        ?>
        <div id="tify_taboox_slideshow-<?php echo self::$Instance; ?>" class="tify_taboox_slideshow tiFyTabooxSlideshow"
             data-max="<?php echo $this->args['max']; ?>" data-action="<?php echo $this->Action; ?>">
            <div class="selectors tiFyTabooxSlideshow-selectors">
                <?php if ($this->args['suggest']) : ?>
                    <div class="suggest tiFyTabooxSlideshowSelector-suggest">
                        <?php
                        $suggest_args = (is_array($this->args['suggest'])) ? $this->args['suggest'] : [];
                        $suggest_args = wp_parse_args(
                            $suggest_args,
                            [
                                'class'       => 'tify_taboox_slideshow-suggest',
                                'placeholder' => __('Rechercher parmi les contenus du site', 'tify'),
                                'elements'    => ['ico', 'title', 'type', 'status', 'id'],
                                'query_args'  => ['post_type' => 'any', 'posts_per_page' => -1],
                                'attrs'       => ['data-duplicate' => $this->args['duplicate']]
                            ]
                        );
                        Control::Suggest($suggest_args, true);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->args['suggest'] && $this->args['custom']) : ?>
                    <p><?php _e('ou', 'tify'); ?></p>
                <?php endif; ?>

                <?php if ($this->args['custom']) : ?>
                    <div class="custom tiFyTabooxSlideshowSelector-custom">
                        <a href="#" class="button-secondary tiFyTabooxSlideshowSelector-customAdd">
                            <?php _e('Vignette personnalisée', 'tify'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php $slideshow = \wp_parse_args(\get_option($this->args['name'], []), ['options' => $this->args['options'], 'slide' => []]); ?>
            <div class="items tiFyTabooxSlideshowList">
                <div class="tiFyTabooxSlideshowList-overlay"><?php _e('Chargement ...', 'tify'); ?></div>
                <?php foreach ((array)$this->args['options'] as $k => $v) : ?>
                    <input type="hidden" name="<?php echo $this->args['name']; ?>[options][<?php echo $k; ?>]"
                           value="<?php echo $v; ?>"/>
                <?php endforeach; ?>
                <ul class="tiFyTabooxSlideshowList-items">
                    <?php if (!empty($slideshow['slide'])) :
                        // Trie selon l'attribut d'ordonnancement
                        $slideshow['slide'] = mk_multisort($slideshow['slide']);
                        // Affichage des entrées
                        foreach ((array)$slideshow['slide'] as $index => $slide)
                            echo $this->item_render($slide, $index);
                    endif; ?>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Affichage d'un élément
     * @param array $slide
     * @param number $index
     * @return string
     */
    protected function item_render($slide, $index = 0)
    {
        if (!$index) :
            $index = uniqid();
        endif;

        $defaults = [
            'post_id'       => 0,
            'title'         => '',
            'caption'       => '',
            'attachment_id' => 0,
            'clickable'     => 0,
            'url'           => '',
            'planning'      => [
                'from'  => 0,
                'start' => '',
                'to'    => 0,
                'end'   => '',
            ]
        ];
        $slide = wp_parse_args($slide, $defaults);
        $name = "{$this->args['name']}[slide]";

        // Récupération de l'image d'illustration
        if ($image = wp_get_attachment_image_src($slide['attachment_id'], 'slide')) :
            $attachment_id = $slide['attachment_id'];
        elseif (($attachment_id = get_post_thumbnail_id($slide['post_id'])) && ($image = wp_get_attachment_image_src($attachment_id, 'slide'))) :
        endif;

        $output = "";
        $output .= "\n<li class=\"tiFyTabooxSlideshowList-item\">";

        // Champs de saisie
        $output .= "\n<div class=\"tiFyTabooxSlideshowList-inputFields\">";
        $output .= "\n\t<div class=\"col col-left\">";
        /// Image d'illustration
        $output .= "\n\t\t<div class=\"tiFyTabooxSlideshowInputField-thumbnail\">";
        $output .= Control::MediaImage(
            [
                'name'      => "{$name}[{$index}][attachment_id]",
                'value'     => $attachment_id,
                'default'   => get_post_thumbnail_id($slide['post_id']),
                'size'      => 'thumbnail',
                'size_info' => false,
                'echo'      => false,
                'width'     => 150,
                'height'    => 150
            ],
            false
        );

        $output .= "\n\t\t</div>";
        $output .= "\n\t</div>";

        $output .= "\n\t<div class=\"col col-right\">";

        /// ID
        $output .= "\n\t\t<input type=\"hidden\" class=\"tiFyTabooxSlideshowInputField-postIdInput\" name=\"{$name}[{$index}][post_id]\" value=\"" . $slide['post_id'] . "\" />";

        foreach ($this->args['attrs'] as $attr) :
            if (!isset($slide[$attr]))
                $slide[$attr] = '';
            $output .= apply_filters(
                "tify_taboox_slideshow_item_{$attr}",
                (method_exists($this, "item_set_{$attr}") ?
                    call_user_func_array([$this, "item_set_{$attr}"], ['', $name, $index, $slide])
                    : ''),
                $name,
                $index,
                $slide
            );
        endforeach;

        $output .= "\n\t</div>";
        $output .= "\n</div>";

        // AIDE A LA SAISIE
        $output .= "\n\t<div class=\"tiFyTabooxSlideshowItem-helpers\">";
        /// Ordre d'affichage
        $output .= "\n\t\t<div class=\"tiFyTabooxSlideshowItemHelper-order\"><input type=\"text\" name=\"{$name}[{$index}][order]\" class=\"tiFyTabooxSlideshowItemHelper-orderInput\" value=\"" . $slide['order'] . "\" readonly/></div>";
        /// Poignée de trie
        $output .= "\n\t\t<a href=\"\" class=\"tiFyTabooxSlideshowItemHelper-sort tify_handle_sort dashicons dashicons-sort\"></a>";
        /// Bouton de suppression
        $output .= "\n\t\t<a href=\"\" class=\"tiFyTabooxSlideshowItemHelper-remove tify_button_remove remove\"></a>";

        /// Infromations
        $output .= "\n\t\t<span class=\"tiFyTabooxSlideshow-ItemHelper tiFyTabooxSlideshow-ItemHelper--informations\">" . ($slide['post_id'] ? __('Contenu du site', 'tify') : __('Vignette personnalisée', 'tify')) . "</span>";

        $output .= "\n\t\t</div>";

        $output .= "\n</li>";

        return $output;
    }

    /**
     * Section TITRE d'un élément
     */
    public function item_set_title($output, $name, $index, $slide)
    {
        $title = $slide['post_id'] ? (isset($slide['title']) ? $slide['title'] : get_the_title($slide['post_id'])) : $slide['title'];

        $output = "\n\t\t<div class=\"tiFyTabooxSlideshowInputField-title\">";
        $output .= "\n\t\t\t<h3>" . __('Titre', 'tify') . "</h3>";
        $output .= "\n\t\t\t<input type=\"text\" class=\"tiFyTabooxSlideshowInputField-titleInput\" name=\"{$name}[{$index}][title]\" value=\"{$title}\">";
        $output .= "\n\t\t</div>";

        return $output;
    }

    /**
     * Section LIEN d'un élément
     */
    public function item_set_link($output, $name, $index, $slide)
    {
        $output = "\n\t\t<div class=\"tiFyTabooxSlideshowInputField-link\">";
        $output .= "\n\t\t\t<h3>" . __('Lien', 'tify') . "</h3>";
        $output .= "\n\t\t\t<label>";
        $output .= "\n\t\t\t\t<input data-hide_unchecked=\".tiFyTabooxSlideshowInputField-linkInput\" type=\"checkbox\" name=\"{$name}[{$index}][clickable]\" value=\"1\" " . checked(1, (isset($slide['clickable']) ? $slide['clickable'] : 0), false) . " autocomplete=\"off\"/>";
        $output .= "\n\t\t\t\t" . __('Vignette cliquable', 'tify');
        $output .= "\n\t\t\t</label>";

        $url = $slide['post_id'] ? get_permalink($slide['post_id']) : $slide['url'];

        $output .= "\n\t\t\t<input type=\"text\" class=\"tiFyTabooxSlideshowInputField-linkInput\" placeholder=\"" . __('Saisissez l\'url du site', 'tify') . "\" name=\"{$name}[{$index}][url]\" value=\"{$url}\"";
        if ($slide['post_id'])
            $output .= " readonly=\"readonly\"";
        $output .= "/>";
        $output .= "\n\t\t</div>";

        return $output;
    }

    /**
     * Section LEGENDE d'un élément
     */
    public function item_set_caption($output, $name, $index, $slide)
    {
        $caption = $slide['post_id'] ? (isset($slide['caption']) ? $slide['caption'] : apply_filters('the_excerpt', get_post_field('post_excerpt', $slide['post_id']))) : $slide['caption'];

        $output = "\n\t\t<div class=\"tiFyTabooxSlideshowInputField-caption\">";
        $output .= "\n\t\t\t<h3>" . __('Légende', 'tify') . "</h3>";
        $output .= "\n\t\t\t<div id=\"{$name}[{$index}][caption]\" class=\"tinymce-editor tiFyTabooxSlideshowInputField-captionInput\" >{$caption}</div>";
        $output .= "\n\t\t</div>";

        return $output;
    }

    /**
     * Section PLANNING d'un élément
     */
    public function item_set_planning($output, $name, $index, $slide)
    {
        $output = "\n\t\t<div class=\"tiFyTabooxSlideshowInputField-Planning\">";
        $output .= "\n\t\t\t<h3>Programmation</h3>";
        $output .= "\n\t\t\t<div class=\"start_datetime\">";
        $output .= "\n\t\t\t\t<label>";
        $output .= "\n\t\t\t\t\t<input type=\"checkbox\" data-hide_unchecked=\".planning-start\" name=\"{$name}[{$index}][planning][from]\" value=\"1\" " . checked(1, (isset($slide['planning']['from']) ? $slide['planning']['from'] : 0), false) . " autocomplete=\"off\"/>";
        $output .= "\n\t\t\t\t\t" . __('A partir du', 'tify');
        $output .= "\n\t\t\t\t</label>";
        $output .= "\n\t\t\t\t" . tify_control_touch_time(['name' => "{$name}[{$index}][planning][start]", 'container_class' => 'planning-start', 'value' => $slide['planning']['start'], 'echo' => false]);
        $output .= "\n\t\t\t</div>";
        $output .= "\n\t\t\t<div class=\"end_datetime\">";
        $output .= "\n\t\t\t\t<label>";
        $output .= "\n\t\t\t\t\t<input type=\"checkbox\" data-hide_unchecked=\".planning-end\" name=\"{$name}[{$index}][planning][to]\" value=\"1\" " . checked(1, (isset($slide['planning']['to']) ? $slide['planning']['to'] : 0), false) . " autocomplete=\"off\"/>";
        $output .= "\n\t\t\t\t\t" . __('Jusqu\'au', 'tify');
        $output .= "\n\t\t\t\t</label>";
        $output .= "\n\t\t\t\t" . tify_control_touch_time(['name' => "{$name}[{$index}][planning][end]", 'container_class' => 'planning-end', 'value' => $slide['planning']['end'], 'echo' => false]);
        $output .= "\n\t\t\t</div>";
        $output .= "\n\t\t</div>";

        return $output;
    }

    /**
     * Action de récupération Ajax
     */
    public function wp_ajax()
    {
        $args = [
            'post_id'   => $_POST['post_id'],
            'title'     => get_the_title($_POST['post_id']),
            'caption'   => apply_filters('the_excerpt', get_post_field('post_excerpt', $_POST['post_id'])),
            'clickable' => $_POST['post_id'] ? 1 : 0,
            'order'     => $_POST['order']
        ];

        global $tify_events;
        if (($tify_events instanceof \tiFy_Events) && in_array(get_post_type($_POST['post_id']), $tify_events->get_post_types()) && ($range = tify_events_get_range($_POST['post_id'])))
            $args['planning'] = [
                'from'  => 1,
                'start' => $range->start_datetime,
                'to'    => 1,
                'end'   => $range->end_datetime,
            ];

        echo $this->item_render($args);
        exit;
    }
}