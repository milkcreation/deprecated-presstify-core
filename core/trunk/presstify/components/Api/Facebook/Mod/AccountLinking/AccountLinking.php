<?php
/**
 * @name AccountLinking
 * @desc Liaison de compte utilisateur à un compte Facebook
 * @see https://developers.facebook.com/docs/php/howto/example_facebook_login#login
 * @package presstiFy
 * @namespace tiFy\Components\Api\Facebook\Mod\AccountLinking\AccountLinking
 * @version 1.1
 * @subpackage Core
 * @since 1.2.546
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Api\Facebook\Mod\AccountLinking;

use tiFy\Core\Control\Control;

class AccountLinking extends \tiFy\Components\Api\Facebook\Mod\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Récupération d'une instance de la classe
     *
     * @return AccountLinking
     */
    public static function create()
    {
        $instance = new static;

        if (!$instance->isInit()) :
            $instance->setInit();

            // Initialisation des événements de déclenchement
            $instance->tFyAppAddAction('show_user_profile', 'show_user_profile');
            $instance->tFyAppAddAction('tify_api_fb_connect_associate', 'on_associate');
            //$instance->tFyAppAddAction('tify_api_fb_connect_dissociate', 'on_dissociate');
        endif;

        return $instance;
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
    final public function isAssociated($user = null)
    {
        if (!$user) :
            $user = wp_get_current_user();
        endif;

        if ($user instanceof \WP_User) :
            $user_id = $user->ID;
        else :
            $user_id = (int)$user;
        endif;

        return get_user_meta($user_id, '_tify_facebook_user_id', true);
    }

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
            if ($this->isAssociated($user)) :
                $this->dissociation_link(
                    [
                        'redirect' => get_edit_profile_url()
                    ]
                );
            else :
                $this->association_link(
                    [
                        'redirect' => get_edit_profile_url()
                    ]
                );
            endif;
        ?>
        </td>
    </tr>
</table>
<?php
    }

    /**
     * Affichage du lien d'association
     *
     * @param array $args Liste des attributs de configuration du lien
     *
     * @return string
     */
    public function association_link($args = [])
    {
        $defaults = [
            'action'   => 'associate',
            'text'     => __('Associer à Facebook', 'tify'),
            'attrs'    => []
        ];
        $args     = array_merge($defaults, $args);

        $helper      = $this->fb()->getRedirectLoginHelper();
        $permissions = ['email'];

        $args['attrs']['href'] = $helper->getLoginUrl(
            add_query_arg(
                [
                    'tify_api_fb_connect' => (string)$args['action']
                ],
                home_url('/')
            ),
            $permissions);

        Control::Link(
            [
                'tag'     => 'a',
                'content' => $args['text'],
                'attrs'   => $args['attrs']
            ],
            true
        );
    }

    /**
     * Affichage du lien de dissociation
     *
     * @param array $args Liste des attributs de configuration du lien
     *
     * @return string
     */
    public function dissociation_link($args = [])
    {
        $defaults = [
            'action'   => 'dissociate',
            'text'     => __('Dissocier de Facebook', 'tify'),
            'attrs'    => []
        ];
        $args     = array_merge($defaults, $args);

        $helper      = $this->fb()->getRedirectLoginHelper();
        $permissions = ['email'];

        $args['attrs']['href'] = $helper->getLoginUrl(
            add_query_arg(
                [
                    'tify_api_fb_connect' => (string)$args['action']
                ],
                home_url('/')
            ),
            $permissions);

        Control::Link(
            [
                'tag'     => 'a',
                'content' => $args['text'],
                'attrs'   => $args['attrs']
            ],
            true
        );
    }

    /**
     * Action lancée au moment de l'association du compte utilisateur à un compte Facebook
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
    public function on_associate($response)
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
        elseif (!is_user_logged_in()) :
            return $this->wp_die(new \WP_Error(
                    500,
                    __('Action impossible, vous devez être connecté pour effectué cette action', 'tify'),
                    ['title' => __('Authentification non trouvée', 'tify')])
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

        \update_user_meta(get_current_user_id(), '_tify_facebook_user_id', $fb_user_id);

        // Redirection
        \wp_redirect(\get_edit_profile_url());
        exit;
    }
}