<?php
/**
 * @name Login
 * @desc Authentification d'un utilisateur via un compte Facebook
 * @see https://developers.facebook.com/docs/php/howto/example_facebook_login#login
 * @package presstiFy
 * @namespace tiFy\Components\Api\Facebook\Mod\Login\Login
 * @version 1.1
 * @subpackage Core
 * @since 1.2.546
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Api\Facebook\Mod\Login;

use tiFy\Core\Layout\Layout;

class Login extends \tiFy\Components\Api\Facebook\Mod\Factory
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
    final public static function url($action = 'login', $permissions = ['email'])
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
                home_url('/')
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
     * @var string $action
     * @var array $permissions
     * @var string $text
     * @var array $attr Attributs de la balise HTML
     * }
     *
     * @return string
     */
    final public static function trigger($args = [])
    {
        $defaults = [
            'action'      => 'login',
            'permissions' => ['email'],
            'text'        => __('Connexion avec Facebook', 'tify'),
            'attrs'       => []
        ];
        $args = array_merge($defaults, $args);

        $url = self::url($args['action'], $args['permissions']);

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
     * Traitement de l'authentification via Facebook
     *
     * @param string $action
     * @param \tiFy\Components\Api\Facebook\Facebook $fb Classe de rappel du SDK Facebook
     *
     * @return string
     */
    public function handler($action = 'login', $fb)
    {
        // Bypass
        if ($action !== 'login') :
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
        endif;

        // Bypass - L'identifiant utilisateur Facebook n'est pas disponible
        if (!$fb_user_id = $tokenMetadata->getUserId()) :
            return $fb->error(new \WP_Error(
                    401,
                    __('Impossible de de définir les données du jeton d\'authentification Facebook.', 'tify'),
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
        if (!$count = $user_query->get_total()) :
            return $fb->error(new \WP_Error(
                    401,
                    __('Aucun utilisateur ne correspond à votre compte Facebook.', 'tify'),
                    ['title' => __('Utilisateur non trouvé', 'tify')])
            );
        elseif ($count > 1) :
            return $fb->error(new \WP_Error(
                    401,
                    __('ERREUR SYSTEME : Votre compte Facebook semble être associé à plusieurs compte > Authentification impossible.', 'tify'),
                    ['title' => __('Nombre d\'utilisateurs trouvés, invalide', 'tify')])
            );
        endif;
        $results = $user_query->get_results();

        // Définition des données utilisateur
        $user = reset($results);

        // Authentification
        \wp_clear_auth_cookie();
        \wp_set_auth_cookie((int)$user->ID);

        // Redirection
        \wp_redirect(home_url('/'));
        exit;
    }
}