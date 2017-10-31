<?php
namespace tiFy\Core\Control\HolderImage;

class HolderImage extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'holder_image';

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        wp_register_style('tify_control-holder_image', self::tFyAppUrl() . '/HolderImage.css', [], 160714);
    }

    /**
     * Mise en file des scripts
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-holder_image');
    }

    /**
     * Affichage du controleur
     * @param array $args
     * @param bool $echo
     *
     * @return string
     */
    public static function display($args = [], $echo = true)
    {
        $defaults = [
            'text'             => "<span class=\"tiFyControlHolderImage-content--default\">" . __('Aucun visuel disponible', 'tify') . "</span>",
            'ratio'            => '1:1',
            'background-color' => '#E4E4E4',
            'foreground-color' => '#AAA',
            'font-size'        => '1em'
        ];
        $args = \wp_parse_args($args, $defaults);

        list($w, $h) = preg_split('/:/', $args['ratio'], 2);
        $sizer = ($w && $h) ? "<span class=\"tiFyControlHolderImage-sizer\" style=\"padding-top:" . (ceil((100 / $w) * $h)) . "%\" ></span>" : "";

        $output = "";
        $output .= "<div class=\"tiFyControlHolderImage\" data-tify_control=\"holder_image\" style=\"background-color:{$args['background-color']};color:{$args['foreground-color']};\">\n";
        $output .= $sizer;
        $output .= "\t<div class=\"tiFyControlHolderImage-content\" style=\"font-size:{$args['font-size']}\">{$args['text']}</div>\n";
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        endif;

        return $output;
    }
}