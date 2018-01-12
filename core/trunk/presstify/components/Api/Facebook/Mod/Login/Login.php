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

class Login extends \tiFy\Components\Api\Facebook\Mod\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Récupération d'une instance de la classe
     *
     * @return Login
     */
    public static function create()
    {
        $instance = new static;

        if (!$instance->isInit()) :
            $instance->setInit();

            // Initialisation des événements de déclenchement
            $instance->tFyAppAddAction('tify_api_fb_connect_login', 'on_login');
        endif;

        return $instance;
    }

    /**
     * Affichage du lien
     *
     * @param array $args Liste des attributs de configuration du lien
     *
     * @return string
     */
    public function link($args = [])
    {
        $defaults = [
            'action'   => 'login',
            'text'     => __('Se connecter avec Facebook', 'tify'),
            'attrs'    => []
        ];
        $args     = array_merge($defaults, $args);

        $helper      = $this->fb()->getRedirectLoginHelper();
        $permissions = ['email'];

        $loginUrl = $helper->getLoginUrl(
            add_query_arg(
                [
                    'tify_api_fb_connect' => (string)$args['action']
                ],
                home_url('/')
            ),
            $permissions
        );

        echo '<a href="' . esc_url($loginUrl) . '">' . $args['text'] . '</a>';
    }

    /**
     * Action lancée au moment de l'authentification via Facebook
     *
     * @param array $response {
     *      Liste des arguments de la réponse de l'authentification Facebook
     *
     *      @var null|\Facebook\Authentication\AccessToken $accessToken
     *      @var null|\Facebook\Authentication\AccessTokenMetadata $tokenMetadata
     *      @var null|\WP_Error $error
     * }
     *
     * @return string
     */
    public function on_login($response)
    {
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
            return $this->wp_die($error);

        // Bypass - L'utilisateur est déjà authentifié
        elseif (is_user_logged_in()) :
            return $this->wp_die(new \WP_Error(
                500,
                __('Action impossible, vous êtes déjà authentifié sur le site', 'tify'),
                ['title' => __('Authentification existante', 'tify')])
            );
        endif;

        // Bypass - L'identifiant utilisateur Facebook n'est pas disponible
        if (!$fb_user_id = $tokenMetadata->getUserId()) :
            return $this->wp_die(new \WP_Error(
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
            return $this->wp_die(new \WP_Error(
                401,
                __('Aucun utilisateur ne correspond à votre compte Facebook.', 'tify'),
                ['title' => __('Utilisateur non trouvé', 'tify')])
            );
        elseif ($count > 1) :
            return $this->wp_die(new \WP_Error(
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