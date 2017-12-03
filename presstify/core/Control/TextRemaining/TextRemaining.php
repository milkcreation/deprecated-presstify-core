<?php
/**
 * @name TextRemaining
 * @desc Controleur d'affichage de champ de saisie de texte avec limitation
 * @see http://www.w3schools.com/tags/tag_textarea.asp -> attributs possibles pour le selecteur textarea
 * @see http://www.w3schools.com/jsref/dom_obj_text.asp -> attributs possibles pour le selecteur input
 * @package presstiFy
 * @namespace tiFy\Core\Control\TextRemaining
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\TextRemaining;

use tiFy\Lib\Chars;

/**
 * @Overrideable \App\Core\Control\TextRemaining\TextRemaining
 *
 * <?php
 * namespace \App\Core\Control\TextRemaining
 *
 * class TextRemaining extends \tiFy\Core\Control\TextRemaining\TextRemaining
 * {
 *
 * }
 */
class TextRemaining extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'text_remaining';

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {
        \wp_register_style(
            'tify_control-text_remaining',
            self::tFyAppAssetsUrl('TextRemaining.css', get_class()),
            [],
            141213
        );
        \wp_register_script(
            'tify_control-text_remaining',
            self::tFyAppAssetsUrl('TextRemaining.js', get_class()),
            ['jquery'],
            141213,
            true
        );
        \wp_localize_script(
            'tify_control-text_remaining',
            'tifyTextRemaining',
            [
                'plural'   => __('caractères restants', 'tify'),
                'singular' => __('caractère restant', 'tify'),
                'none'     => __('Aucun caractère restant', 'tify')
            ]
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-text_remaining');
        wp_enqueue_script('tify_control-text_remaining');
    }

    /**
     * Affichage du controleur
     *
     * @param array $attrs {
     *      Attributs d'affichage du controleur
     *
     * @param string $id Identifiant de qualification.
     * @param string $container_id Id HTML du conteneur du controleur.
     * @param string $feedback_area Id HTML du conteneur d'affichage des informations de saisie.
     * @param string $name Nom du champ d'enregistrement
     * @param string $selector Type de selecteur. textarea (défaut)|input.
     * @param string $value Valeur du champ de saisie.
     * @param array $attrs Attributs HTML du champ.
     * @param int $length Nombre maximum de caractères attendus. 150 par défaut.
     * @param bool $maxlength Activation de l'arrêt de la saisie en cas de dépassement. true par défaut.
     *  }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    public static function display($attrs = [], $echo = true)
    {
        // Incrémentation du nombre d'instance
        self::$Instance++;

        // Traitement des attributs de configuration
        $defaults = [
            'id'            => 'tify_control_text_remaining-' . self::$Instance,
            'container_id'  => 'tify_control_text_remaining-container-' . self::$Instance,
            'feedback_area' => '#tify_control_text_remaining-feedback-' . self::$Instance,
            'name'          => 'tify_control_text_remaining-' . self::$Instance,
            'selector'      => 'textarea',    // textarea (default) // @TODO | input
            'value'         => '',
            'value_filter'  => true,
            'attrs'         => [],
            'length'        => 150,
            'maxlength'     => true     // Stop la saisie en cas de dépassement
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        if ($value_filter) :
            $value = nl2br($value);
            $value = Chars::br2nl($value);
            $value = wp_unslash($value);
        endif;

        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tify_control_text_remaining-container\">\n";
        switch ($selector) :
            default :
            case 'textarea' :
                $output .= "\t<textarea id=\"{$id}\" data-tify_control=\"text_remaining\" data-feedback_area=\"{$feedback_area}\"";
                if ($name) {
                    $output .= " name=\"{$name}\"";
                }
                if ($maxlength) {
                    $output .= " maxlength=\"{$length}\"";
                }
                if ($attrs) {
                    foreach ($attrs as $iattr => $vattr) {
                        $output .= " {$iattr}=\"{$vattr}\"";
                    }
                }
                $output .= ">" . $value . "</textarea>\n";
                $output .= "\t<span id=\"" . str_replace('#', '',
                        $feedback_area) . "\" class=\"feedback_area\" data-max-length=\"{$length}\" data-length=\"" . strlen($value) . "\"></span>\n";
                break;
            case 'input' :
                $output .= "\t<input id=\"{$id}\" data-tify_control=\"text_remaining\" data-feedback_area=\"{$feedback_area}\"";
                if ($name) {
                    $output .= " name=\"{$name}\"";
                }
                if ($maxlength) {
                    $output .= " maxlength=\"{$length}\"";
                }
                if ($attrs) {
                    foreach ($attrs as $iattr => $vattr) {
                        $output .= " {$iattr}=\"{$vattr}\"";
                    }
                }
                $output .= " value=\"" . $value . "\">\n";
                $output .= "\t<span id=\"" . str_replace('#', '',
                        $feedback_area) . "\" class=\"feedback_area\" data-max-length=\"{$length}\" data-length=\"" . strlen($value) . "\"></span>\n";
                break;
        endswitch;
        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}