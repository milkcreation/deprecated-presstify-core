<?php
/**
 * @name TakeOver - SwitcherForm
 * @desc Controleur d'affichage de fomulaire de bascule de prise de contrôle d'un utilisateur
 * @package presstiFy
 * @subpackage Core
 * @namespace tiFy\Core\User\TakeOver\SwitcherForm\SwitcherForm
 * @version 1.1
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User\TakeOver\SwitcherForm;

use tiFy\Core\Field\Field;
use tiFy\Core\User\TakeOver\TakeOver;
use tiFy\Lib\User\User;

class SwitcherForm extends \tiFy\Core\Control\Factory
{
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
        // Actions ajax
        $this->appAddAction(
            'wp_ajax_tiFyTakeOverSwitcherForm_get_users',
            'wp_ajax_get_users'
        );
        $this->appAddAction(
            'wp_ajax_nopriv_tiFyTakeOverSwitcherForm_get_users',
            'wp_ajax_get_users'
        );

        \wp_register_script(
            'tify_control-take_over_switcher_form',
            self::tFyAppAssetsUrl('SwitcherForm.js', get_class()),
            [],
            171218,
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
        Field::enqueue('SelectJs');
        \wp_enqueue_script('tify_control-take_over_switcher_form');
    }

    /**
     * Récupération de la liste de selection des utilisateurs via Ajax
     *
     * @return string
     */
    public function wp_ajax_get_users()
    {
        // Contrôle de sécurité
        check_ajax_referer('tiFyTakeOverSwitcherForm-getUsers');

        // Récupération des attributs de champ
        $fields = self::tFyAppGetRequestVar('fields', ['role' => [], 'user' => []], 'POST');
        $fields = wp_unslash($fields);

        // Récupération de la liste de choix des utilisateurs
        $user_options = User::userQueryKeyValue(
            'ID',
            'display_name',
            [
                'role'      => self::tFyAppGetRequestVar('role', '', 'POST'),
                'number'    => -1
            ]
        );
        $disabled = empty($user_options);

        $user_options = [-1 => __('Choix de l\'utilisateur', 'tify')] + $user_options;

        $fields['user']['options'] = $user_options;
        $fields['user']['disabled'] = $disabled;

        echo Field::SelectJs($fields['user']);
        exit;
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
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage
     *      @var array $fields {
     *          Liste des champs de selection role et utilisateur
     *
     *          @var array $role {
     *              Attributs de configuration du champ de selection des role
     *              @see \tiFy\Core\Field\SelectJs\SelectJs
     *
     *          }
     *          @var array $user {
     *              Attributs de configuration du champ de selection des role
     *              @see \tiFy\Core\Field\SelectJs\SelectJs
     *
     *          }
     *
     *      }
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'take_over_id' => '',
            'fields'       => [
                'role'  => [],
                'user'  => []
            ]
        ];
        $attrs = array_merge($defaults, $attrs);

        /**
         * @var string $take_over_id Identifiant de qualification du contrôleur d'affichage
         * @var array $fields {
         *      Liste des champs de selection role et utilisateur
         *
         *      @var array $role {
         *          Attributs de configuration du champ de selection des role
         *          @see \tiFy\Core\Field\SelectJs\SelectJs
         *
         *      }
         *      @var array $user {
         *          Attributs de configuration du champ de selection des role
         *          @see \tiFy\Core\Field\SelectJs\SelectJs
         *
         *      }
         * }
         */
        extract($attrs);

        // Bypass - L'identification de qualification ne fait référence à aucune classe de rappel déclarée
        if (!$takeOver = TakeOver::get($take_over_id)) :
            return;

        // Bypass - L'utilisateur n'est pas habilité à utiliser l'interface
        elseif (!$takeOver->isAuth('switch')) :
            return;

        // Bypass - Aucun rôle permis n'est défini
        elseif (!$allowed_roles = $takeOver->getAllowedRoleList()) :
            return;
        endif;

        // Action de récupération de la liste de choix des utilisateurs via ajax
        $ajax_action = 'tiFyTakeOverSwitcherForm_get_users';

        /// Agent de sécurisation de la requête ajax
        $ajax_nonce = wp_create_nonce('tiFyTakeOverSwitcherForm-getUsers');

        // Attributs de configuration des champs
        $fields['role'] = array_merge(
            [
                'name'            => 'role',
                'value'           => -1,
                'filter'          => false
            ],
            (array)$fields['role']
        );

        $fields['user'] = array_merge(
            [
                'name'            => 'user_id',
                'value'           => -1,
                'disabled'        => true,
                'picker'          => [
                    'filter'    => true
                ]
            ],
            (array)$fields['user']
        );

        // Définition de la liste des choix des selecteurs
        // Selecteur des Rôles
        $role_options = [];
        foreach($allowed_roles as $allowed_role) :
            if (!$role = \get_role($allowed_role)) :
                continue;
            endif;
            $role_options[$allowed_role] = User::roleDisplayName($allowed_role);
        endforeach;
        $role_options = [-1 => __('Choix du role', 'tify')] + $role_options;

        // Selecteur des Utilisateurs
        $user_options = [];
        $user_options = [-1 => __('Choix de l\'utilisateur', 'tify')] + $user_options;


        // Affichage du formulaire
        $output = "";
        $output .= "<form class=\"tiFyTakeOver-Control--switch_form\" method=\"post\" action=\"\" data-options=\"" . rawurlencode(json_encode(compact('ajax_action', 'ajax_nonce', 'fields'))) . "\" >";
        $output .= \wp_nonce_field('tiFyTakeOver-switch', '_wpnonce', false, false);
        $output .= Field::Hidden(
            [
                'name'  => 'action',
                'value' => 'switch',
            ]
        );
        $output .= Field::Hidden(
            [
                'name'  => 'tfy_take_over_id',
                'value' => $take_over_id,
            ]
        );
        $output .= Field::SelectJs(
            array_merge(
                [
                    'options' => $role_options
                ],
                $fields['role']
            )
        );
        $output .= Field::SelectJs(
            array_merge(
                [
                    'options' => $user_options
                ],
                $fields['user']
            )
        );
        $output .= "</form>";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}