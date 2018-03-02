<?php
/**
 * @name TakeOver - AdminBar
 * @desc Controleur d'affichage d'une interface barre d'administration de bascule de compte utilisateur et de récupération de l'utilisateur principal
 * @package presstiFy
 * @subpackage Core
 * @namespace tiFy\Core\User\TakeOver\AdminBar\AdminBar
 * @version 1.1
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User\TakeOver\AdminBar;

use tiFy\Core\Control\Control;
use tiFy\Core\User\TakeOver\TakeOver;

class AdminBar extends \tiFy\Core\Control\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final protected function init()
    {
        \wp_register_style(
            'tify_control-take_over_admin_bar',
            self::tFyAppAssetsUrl('AdminBar.css', get_class()),
            [],
            171218
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final protected function enqueue_scripts()
    {
        Control::enqueue_scripts('take_over_action_link');
        Control::enqueue_scripts('take_over_switcher_form');
        \wp_enqueue_style('tify_control-take_over_admin_bar');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
     *      @var bool $in_footer Affichage automatique dans le pied de page du site.
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'take_over_id'  => '',
            'in_footer'     => true
        ];
        $attrs = array_merge($defaults, $attrs);

        /**
         * @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
         * @var bool $in_footer Affichage automatique dans le pied de page du site.
         */
        extract($attrs);

        // Bypass - L'identification de qualification ne fait référence à aucune classe de rappel déclarée
        if (!$takeOver = TakeOver::get($take_over_id)) :
            return;
        endif;

        $output  = "";
        $output .= "<div class=\"tiFyTakeOver-Control--admin_bar\">";
        $output .= Control::TakeOverSwitcherForm(compact('take_over_id'));
        $output .= Control::TakeOverActionLink(compact('take_over_id'));
        $output .= "</div>";

        if ($in_footer) :
            $footer = function () use ($output) { echo $output; };
            \add_action((!is_admin() ? 'wp_footer' : 'admin_footer'), $footer);
        elseif ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}