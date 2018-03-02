<?php
/**
 * @name SignUp
 * @desc Inscription d'un utilisateur via un compte Facebook
 * @package presstiFy
 * @namespace tiFy\Components\Api\Facebook\Mod\Login\Login
 * @version 1.1
 * @subpackage Core
 * @since 1.2.553
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Api\Facebook\Mod\SignUp;

use tiFy\Core\Layout\Layout;

class SignUp extends \tiFy\Components\Api\Facebook\Mod\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Url de l'action
     *
     * @param string $action
     *
     * @return string
     */
    final public static function url($action = 'signup', $permissions = ['email'], $base_url = null)
    {
        if (!$fb = self::tFyAppGetContainer('tiFy\Components\Api\Facebook\Facebook')) :
            return;
        endif;

        $helper = $fb->getRedirectLoginHelper();

        return $helper->getLoginUrl(
            add_query_arg(
                [
                    'tify_api_fb' => (string)$action
                ],
                $base_url ? : home_url('/')
            ),
            (array)$permissions
        );
    }

    /**
     * Bouton d'action
     *
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var string $action
     *      @var array $permissions
     *      @var string $text
     *      @var array $attr Attributs de la balise HTML
     * }
     *
     * @return string
     */
    final public static function trigger($args = [])
    {
        $defaults = [
            'action'     => 'signup',
            'permissions' => ['email'],
            'text'       => __('Inscription avec Facebook', 'tify'),
            'attrs'      => [],
            'base_url'   => home_url('/')
        ];
        $args     = array_merge($defaults, $args);

        $url = self::url($args['action'], $args['permissions'], $args['base_url']);

        $args['attrs']['href'] = esc_url($url);
        $args['attrs']['title'] = empty($args['attrs']['title']) ? $args['text'] : $args['attrs']['title'];
        $args['attrs']['class'] = empty($args['attrs']['class']) ? 'FacebookTrigger' : 'FacebookTrigger ' . $args['attrs']['class'];

        echo Layout::Tag(
            [
                'tag'     => 'a',
                'attrs'   => $args['attrs'],
                'content' => $args['text']
            ]
        );
    }

    /**
     * Traitement de l'inscription via Facebook
     *
     * @param string $action
     * @param \tiFy\Components\Api\Facebook\Facebook $fb Classe de rappel du SDK Facebook
     *
     * @return string
     */
    public function handler($action = 'signup', $fb)
    {
        // Bypass
        if ($action !== 'signup') :
            return;
        endif;

        // Tentative de connection
        $response = $fb->connect(
            add_query_arg(
                [
                    'tify_api_fb' => $action
                ],
                home_url('/')
            )
        );

        /**
         * @var null|\Facebook\Authentication\AccessToken $accessToken
         * @var null|\Facebook\Authentication\AccessTokenMetadata $tokenMetadata
         * @var null|\WP_Error $error
         * @var string $action
         * @var string $redirect
         */
        extract($response);

        // Bypass - La demande d'authentification Facebook retourne des erreurs
        if (\is_wp_error($error)) :
            return $fb->error($error);

        // Bypass - L'utilisateur est déjà authentifié
        elseif (is_user_logged_in()) :
            return $fb->error(new \WP_Error(
                500,
                __('Action impossible, vous êtes déjà authentifié sur le site', 'tify'),
                ['title' => __('Authentification existante', 'tify')])
            );

        // Bypass - L'identifiant utilisateur Facebook n'est pas disponible
        elseif (!$fb_user_id = $tokenMetadata->getUserId()) :
            return $fb->error(new \WP_Error(
                401,
                __('Impossible de définir les données du jeton d\'authentification Facebook.', 'tify'),
                ['title' => __('Récupération des données du jeton d\'accès en échec', 'tify')])
            );
        endif;

        // Réquête de récupération d'utilisateur correspondant à l'identifiant Facebook
        $user_query = new \WP_User_Query([
                'meta_query' => [
                    [
                        'key'   => '_tify_facebook_user_id',
                        'value' => $fb_user_id
                    ]
                ]
            ]);

        // Bypass - Aucun utilisateur correspondant à l'identifiant utilisateur Facebook.
        if ($count = $user_query->get_total()) :
            return $fb->error(new \WP_Error(
                401,
                __('Un utilisateur est déjà enregistré avec ce compte Facebook.', 'tify'),
                ['title' => __('Utilisateur existant', 'tify')])
            );
        endif;

        // Récupération des informations utilisateur
        $response = $fb->userInfos(['id', 'email', 'name', 'first_name', 'last_name', 'short_name']);
        if (is_wp_error($response['error'])) :
            return $fb->error($response['error']);
        endif;

        // Cartographie des données utilisateur
        $userdata = [
            'user_login'    => 'fb-' . $response['infos']['id'],
            'user_pass'     => '',
            'user_email'    => $response['infos']['email'],
            'display_name'  => $response['infos']['name'],
            'first_name'    => $response['infos']['first_name'],
            'last_name'     => $response['infos']['last_name'],
            'nickname'      => $response['infos']['short_name'],
            'role'          => 'subscriber'
        ];
        $user_id = \wp_insert_user($userdata);

        if (is_wp_error($user_id)) :
            return $fb->error($user_id);
        elseif (\update_user_meta($user_id, '_tify_facebook_user_id', $response['infos']['id'])) :
            // Authentification
            \wp_clear_auth_cookie();
            \wp_set_auth_cookie((int)$user_id);

            // Redirection
            \wp_redirect(home_url('/'));
            exit;
        endif;
    }
}