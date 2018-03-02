<?php
/**
 * @name Profile
 * @desc Interface de liaison de compte utilisateur à un compte Facebook
 * @see https://developers.facebook.com/docs/php/howto/example_facebook_login#login
 * @package presstiFy
 * @namespace tiFy\Components\Api\Facebook\Mod\Profile\Profile
 * @version 1.1
 * @subpackage Core
 * @since 1.2.546
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Api\Facebook\Mod\Profile;

use tiFy\Core\Layout\Layout;

class Profile extends \tiFy\Components\Api\Facebook\Mod\Factory
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($callable = null)
    {
        parent::__construct($callable);

        // Déclaration des événements
        $this->appAddAction('show_user_profile', 'show_user_profile');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Interface de gestion du profil de l'interface d'administation
     * Affilier/Dissocier un compte Facebook
     *
     * @param \WP_User $user Données utilisateur
     *
     * @return string
     */
    public function show_user_profile($user)
    {
?>
<table class="form-table">
    <tr>
        <th><?php _e('Affiliation à un compte Facebook', 'tify'); ?></th>
        <td>
        <?php
            self::trigger(
                [
                    'redirect' => get_edit_profile_url()
                ]
            );
        ?>
        </td>
    </tr>
</table>
<?php
    }

    /**
     * CONTROLEURS
     */
    /**
     * Vérification d'association d'un compte utilisateur à Facebook
     *
     * @param int|WP_User $user
     *
     * @return bool
     */
    final public static function is($user = null)
    {
        if (!$user) :
            $user = wp_get_current_user();
        endif;

        if ($user instanceof \WP_User) :
            $user_id = $user->ID;
        else :
            $user_id = (int)$user;
        endif;

        return !empty(get_user_meta($user_id, '_tify_facebook_user_id', true));
    }

    /**
     * Url de l'action
     *
     * @param string $action
     *
     * @return string
     */
    final public static function url($action = 'profile', $permissions = ['email'])
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
                get_edit_profile_url()
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
            'action'     => 'profile',
            'permissions' => ['email'],
            'text'       => "<span class=\"dashicons dashicons-facebook-alt\" style=\"line-height:28px;\"></span>&nbsp;" . (!self::is() ? __('Associer avec Facebook', 'tify') : __('Dissocier de Facebook', 'tify')),
            'attrs'      => [
                'class' => 'button-primary'
            ]
        ];
        $args = array_merge($defaults, $args);

        $args['attrs']['href'] = self::url($args['action'], $args['permissions']);

        echo Layout::Tag(
            [
                'tag'    => 'a',
                'content' => $args['text'],
                'attrs'   => $args['attrs']
            ]
        );
    }

    /**
     * Traitement de l'authentification via Facebook
     *
     * @param \tiFy\Components\Api\Facebook\Facebook $fb Classe de rappel du SDK Facebook
     *
     * @return string
     */
    public function handler($action = 'profile', $fb)
    {
        // Bypass
        if ($action !== 'profile') :
            return;
        endif;

        // Bypass - L'utilisateur est déjà authentifié
        if (!is_user_logged_in()) :
            return $fb->error(new \WP_Error(
                    500,
                    __('Action impossible, vous devez être connecté pour effectué cette action', 'tify'),
                    ['title' => __('Authentification non trouvée', 'tify')])
            );
        endif;

        // Récupération des données utilisateur
        $user_id = get_current_user_id();

        if (!self::is($user_id)) :
            // Tentative de connection
            $response = $fb->connect(
                add_query_arg(
                    [
                        'tify_api_fb' => $action
                    ],
                    get_edit_profile_url()
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

            // Bypass - L'identifiant utilisateur Facebook n'est pas disponible
            elseif (!$fb_user_id = $tokenMetadata->getUserId()) :
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
            if ($count = $user_query->get_total()) :
                return $fb->error(new \WP_Error(
                        401,
                        __('Un utilisateur est déjà enregistré avec ce compte Facebook.', 'tify'),
                        ['title' => __('Utilisateur existant', 'tify')])
                );
            endif;

            \update_user_meta($user_id, '_tify_facebook_user_id', $fb_user_id);
        else :
            \delete_user_meta($user_id, '_tify_facebook_user_id');
        endif;

        // Redirection
        \wp_redirect(\get_edit_profile_url());
        exit;
    }
}