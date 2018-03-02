<?php
/**
 * @name TakeOver - ActionLink
 * @desc Controleur d'affichage de lien de récupération de l'utilisateur principal ou de bascule de compte utilisateur
 * @package presstiFy
 * @subpackage Core
 * @namespace tiFy\Core\User\TakeOver\ActionLink\ActionLink
 * @version 1.1
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User\TakeOver\ActionLink;

use tiFy\Core\User\TakeOver\TakeOver;

class ActionLink extends \tiFy\Core\Control\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
     *      @var int $user_id Identifiant de qualification de l'utilisateur (requis). Mais uniquement pour l'action 'switch'.
     *      @var string $action $action Type d'action. 'switch': prise de contrôle d'un utilisateur|'restore': Récupération de l'utilsateur principal (défaut).
     *      @var string $text Texte du bouton.
     *      @var array $attrs Attributs de la balise du lien. Hors 'href' défini automatiquement par le controleur.
     *      @var string $redirect_url Url de redirection après l'action.
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected function display($args = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'take_over_id'  => '',
            'user_id'       => 0,
            'action'        => 'restore',
            'text'          => '',
            'attrs'         => [],
            'redirect_url'  => home_url('/')
        ];
        $_args = array_merge($defaults, $args);

        /**
         * @var string $take_over_id Identifiant de qualification du contrôleur d'affichage.
         * @var int $user_id Identifiant de qualification de l'utilisateur (requis). Mais uniquement pour l'action 'switch'.
         * @var string $action $action Type d'action. 'switch': prise de contrôle d'un utilisateur|'restore': Récupération de l'utilsateur principal (défaut).
         * @var string $text Texte du bouton.
         * @var array $attrs Attributs de la balise du lien. Hors 'href' défini automatiquement par le controleur.
         * @var string $redirect_url Url de redirection après l'action.
         */
        extract($_args);

        // Bypass - L'identification de qualification ne fait référence à aucune classe de rappel déclarée
        if (!$takeOver = TakeOver::get($take_over_id)) :
            return;
        endif;

        switch($action) :
            case 'switch' :
                // Bypass - L'utilisateur principal n'est pas habilité à utiliser l'interface
                if (!$takeOver->isAuth($action)) :
                    return;
                // Bypass - L'utilisateur n'existe pas
                elseif (!$user = $takeOver->getUserData($user_id)) :
                    return;
                // Bypass - L'utilisateur n'est pas habilité à prendre le contrôle
                elseif (!$takeOver->isAllowed($user->ID)) :
                    return;
                endif;

                // Définition de l'intitulé par défaut du lien si ce dernier n'est pas court-circuité dans les attributs personnalisés.
                if (!isset($args['text'])) :
                    $text = __('Naviguer comme', 'tify');
                endif;

                // Définition du titre de la balise par défaut si ce dernier n'est pas court-circuité dans les attributs personnalisés.
                if (!isset($args['attrs']['title'])) :
                    $attrs['title'] = sprintf(__('Naviguer sur le site en tant que %s', 'tify'), $user->display_name);
                endif;

                // Définition de l'url
                $url = wp_nonce_url($redirect_url, 'tiFyTakeOver-switch');
                $url = add_query_arg(['action' => $action, 'tfy_take_over_id' => $take_over_id, 'user_id' => $user->ID], $url);
                break;
            case 'restore' :
                // Bypass - L'utilisateur n'est pas autorisé à utiliser l'interface
                if (!$takeOver->isAuth($action)) :
                    return;
                endif;

                // Définition de l'intitulé par défaut du lien si ce dernier n'est pas court-circuité dans les attributs personnalisés.
                if (!isset($args['text'])) :
                    $text = __('Rétablir', 'tify');
                endif;

                // Définition du titre de la balise par défaut si ce dernier n'est pas court-circuité dans les attributs personnalisés.
                if (!isset($args['attrs']['title'])) :
                    $attrs['title'] = __('Rétablissement l\'utilisateur principal', 'tify');
                endif;

                // Définition de l'url
                $url = wp_nonce_url($redirect_url, 'tiFyTakeOver-restore');
                $url = add_query_arg(['action' => $action, 'tfy_take_over_id' => $take_over_id], $url);
                break;
        endswitch;

        // Définition de la classe de qualification du lien.
        $attrs['class'] = !isset($attrs['class']) ? "tiFyTakeOver-Control--action_link" : "tiFyTakeOver-Control--action_link " . $attrs['class'];

        // Traitement des attributs de balise
        $_attrs = "";
        foreach ($attrs as $k => $v) :
            if ($k === 'href') :
                continue;
            endif;

            $_attrs .= " {$k}=\"{$v}\"";
        endforeach;

        // Affichage du controleur
        $output = "<a href=\"{$url}\"{$_attrs}>{$text}</a>";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}