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

use tiFy\Core\User\User;
use tiFy\Core\Field\Field;

use tiFy\Lib\User\User as UserLib;

class SwitcherForm extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'take_over_switcher_form';

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Actions ajax
        $this->tFyAppAddAction(
            'wp_ajax_tiFyTakeOverSwitcherForm_get_users',
            'wp_ajax_get_users'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tiFyTakeOverSwitcherForm_get_users',
            'wp_ajax_get_users'
        );
    }

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
        \wp_register_script(
            'tify_control-take_over_switcher_form',
            self::tFyAppUrl(get_class()). '/SwitcherForm.js',
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
    public static function enqueue_scripts()
    {
        Field::enqueue_scripts('SelectJs');
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

        // Récupération de la liste de choix des utilisateurs
        $user_options = UserLib::userQueryKeyValue(
            'ID',
            'display_name',
            [
                'role'      => self::tFyAppGetRequestVar('role', '', 'POST'),
                'number'    => -1
            ]
        );
        $disabled = empty($user_options);

        $user_options = [-1 => __('Choix de l\'utilisateur', 'tify')] + $user_options;

        Field::SelectJs(
            [
                'name'            => 'user_id',
                'container_class' => 'tiFyTakeOverSwitcherForm-selectField--user',
                'options'         => $user_options,
                'value'           => -1,
                'disabled'        => $disabled,
                'picker'          => [
                    'filter'    => true
                ]
            ],
            true
        );
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

     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected static function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'take_over_id' => ''
        ];
        $attrs = array_merge($defaults, $attrs);

        /**
         * @var string $take_over_id Identifiant de qualification du contrôleur d'affichage
         */
        extract($attrs);

        // Bypass - L'identification de qualification ne fait référence à aucune classe de rappel déclarée
        if (!$takeOver = User::getTakeOver($take_over_id)) :
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

        // Définition de la liste des choix des selecteurs
        // Selecteur des Rôles
        $role_options = [];
        foreach($allowed_roles as $allowed_role) :
            if (!$role = \get_role($allowed_role)) :
                continue;
            endif;
            $role_options[$allowed_role] = UserLib::roleDisplayName($allowed_role);
        endforeach;
        $role_options = [-1 => __('Choix du role', 'tify')] + $role_options;

        // Selecteur des Utilisateurs
        $user_options = [];
        $user_options = [-1 => __('Choix de l\'utilisateur', 'tify')] + $user_options;

        // Affichage du formulaire
        $output = "";
        $output .= "<form class=\"tiFyTakeOver-Control--switch_form\" method=\"post\" action=\"\" data-options=\"" . rawurlencode(json_encode(compact('ajax_action', 'ajax_nonce'))) . "\" >";
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
            [
                'name'            => 'role',
                'container_class' => 'tiFyTakeOverSwitcherForm-selectField--role',
                'options'         => $role_options,
                'value'           => -1,
                'filter'          => false
            ]
        );
        $output .= Field::SelectJs(
            [
                'name'            => 'user_id',
                'container_class' => 'tiFyTakeOverSwitcherForm-selectField--user',
                'options'         => $user_options,
                'value'           => -1,
                'disabled'        => true,
                'picker'          => [
                    'filter'    => true
                ]
            ]
        );
        $output .= "</form>";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}