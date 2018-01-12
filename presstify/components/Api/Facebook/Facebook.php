<?php
/**
 * @see https://github.com/facebook/php-graph-sdk
 * @see https://developers.facebook.com/docs/php/howto/example_facebook_login
 */

namespace tiFy\Components\Api\Facebook;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class Facebook extends \Facebook\Facebook
{
    use \tiFy\App\Traits\App;

    /**
     * Instance de la classe
     * @var Facebook
     */
    private static $Instance;

    /**
     * Attributs de configuration du SDK Facebook
     * @var array
     */
    protected $Config = [];

    /**
     * Classe de rappel des modules actifs
     * @var array
     */
    protected $Mods = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($args)
    {
        // Initialisation de la session
        if (!session_id()) :
            session_start();
        endif;

        // Traitement des attributs de configuration du SDK PHP Facebook
        $allowed = [
            'app_id',
            'app_secret',
            'default_graph_version',
            'enable_beta_mode',
            'http_client_handler',
            'persistent_data_handler',
            'pseudo_random_string_generator',
            'url_detection_handler',
        ];
        $this->Config = array_intersect_key($args, array_flip($allowed));

        // Instanciation du SDK PHP Facebook
        parent::__construct($this->Config);

        // Initialisation des modules
        if (isset($args['mod'])) :
            foreach($args['mod'] as $mod => $attrs) :
                if ($attrs === false) :
                    continue;
                elseif (!is_array($attrs)) :
                    $attrs = [];
                endif;

                $Mod = self::tFyAppFormatAsClassname($mod);
                $Cb = "tiFy\\Components\\Api\\Facebook\\Mod\\{$Mod}\\{$Mod}::create";

                if (is_callable($Cb)) :
                    call_user_func_array($Cb, $attrs);
                endif;
            endforeach;
        endif;

        // Initialisation des événements de déclenchement
        $this->tFyAppAddAction('wp_loaded');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Après le chargement complet de Wordpress
     */
    final public function wp_loaded()
    {
        if (!$action = self::tFyAppGetRequestVar('tify_api_fb_connect', false)) :
            return;
        endif;

        // Tentative de connection via Facebook
        if ($response = $this->connect($action)) :
            // Déclenchement de l'action de sortie en cas de succès d'authentification
            return do_action('tify_api_fb_connect_' . $action, $response);
        endif;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du SDK Facebook
     *
     *      @var string $app_id (requis)
     *      @var string $app_secret
     *      @var string $default_graph_version
     *      @var bool $enable_beta_mode
     *      @var $http_client_handler
     *      @var $persistent_data_handler
     *      @var $pseudo_random_string_generator
     *      @var $url_detection_handler
     * }
     * @param array $mod {
     *      Activation/Attributs des modules Facebook tiFy
     *
     *      @var bool|array $login
     * }
     */
    public static function create($args = [])
    {
        if (self::$Instance instanceof Facebook) :
            return self::$Instance;
        else :
            return self::$Instance = new static($args);
        endif;
    }

    /**
     * Connection à Facebook
     *
     * @param string $action Action à exécuter (login|associate|dissociate|signup|custom)
     *
     * @return array
     */
    final public function connect($action)
    {
        // Définition des éléments de réponse
        $pieces = ['accessToken', 'tokenMetadata', 'error'];

        /**
         * Classe de rappel du jeton d'authentification
         * @var null|\Facebook\Authentication\AccessToken $accessToken
         */
        $accessToken = null;

        /**
         * Classe de rappel de traitement des métadonnées du jeton d'authentification
         * @var null|\Facebook\Authentication\AccessTokenMetadata $tokenMetadata
         */
        $tokenMetadata = null;

        /**
         * Classe de rappel des erreurs de traitement
         * @var null|\WP_Error $error
         */
        $error = null;

        // Classe de rappel de redirection
        $helper = $this->getRedirectLoginHelper();

        // Récupération du jeton d'accès
        try {
            $accessToken = $helper->getAccessToken(
                add_query_arg(
                    [
                        'tify_api_fb_connect' => $action
                    ],
                    home_url('/')
                )
            );
        } catch (FacebookResponseException $e) {
            $error = new \WP_Error(
                $e->getCode(),
                'Graph returned an error: ' . $e->getMessage(),
                ['title' => __('Graph returned an error', 'tify')]
            );

            return compact($pieces);
        } catch (FacebookSDKException $e) {
            $error = new \WP_Error(
                401,
                'Facebook SDK returned an error: ' . $e->getMessage(),
                ['title' => __('Le kit de développement Facebook renvoi une erreur', 'tify')]
            );

            return compact($pieces);
        }

        // Bypass - La récupération du jeton d'accès tombe en échec
        if ( ! isset($accessToken)) :
            if ($helper->getError()) :
                $error = new \WP_Error(
                    401,
                    "Error: " . $helper->getError() . "\n" .
                    "Error Code: " . $helper->getErrorCode() . "\n" .
                    "Error Reason: " . $helper->getErrorReason() . "\n" .
                    "Error Description: " . $helper->getErrorDescription() . "\n"
                );

                return compact($pieces);
            else :
                $error = new \WP_Error(400, 'Bad request');

                return compact($pieces);
            endif;
        endif;

        // Classe de rappel de traitement des jetons d'accès
        $oAuth2Client = $this->getOAuth2Client();

        // Classe de rappel de traitement des métadonnées de jetons
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Contrôle de la correspondance entre l'app_id de l'api Facebook et celle du jeton
        try {
            $tokenMetadata->validateAppId($this->getAppId());
        } catch (FacebookSDKException $e) {
            $error = new \WP_Error(
                $e->getCode(),
                $e->getMessage(),
                ['title' => __('Correspondance du jeton d\'accès en échec', 'tify')]
            );

            return compact($pieces);
        }

        // Contrôle de la validité du jeton
        try {
            $tokenMetadata->validateExpiration();
        } catch (FacebookSDKException $e) {
            $error = new \WP_Error(
                $e->getCode(),
                $e->getMessage(),
                ['title' => __('Expiration du jeton d\'accès', 'tify')]
            );

            return compact($pieces);
        }

        // Tentative d'échange du jeton d'accès courte durée pour un jeton d'accès longue durée
        if ( ! $accessToken->isLongLived()) :
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
                $error = new \WP_Error(
                    $e->getCode(),
                    "Error getting long-lived access token: " . $e->getMessage(),
                    ['title' => __('Récupération du jeton d\'accès longue durée en échec', 'tify')]
                );

                return compact($pieces);
            }
        endif;

        // Bypass - La classe de rappel du jeton d'authentification n'est pas conforme
        if (!$accessToken instanceof \Facebook\Authentication\AccessToken) :
            $error = new \WP_Error(
                401,
                __('Impossible de définir le jeton d\'authentification Facebook.', 'tify'),
                ['title' => __('Récupération du jeton d\'accès en échec', 'tify')]
            );

            return compact($pieces);
        endif;

        // Bypass - La classe de rappel de traitement des métadonnées du jeton d'authentification
        if (!$tokenMetadata instanceof \Facebook\Authentication\AccessTokenMetadata) :
            $error = new \WP_Error(
                401,
                __('Impossible de définir les données du jeton d\'authentification Facebook.', 'tify'),
                ['title' => __('Récupération des données du jeton d\'accès en échec', 'tify')]
            );

            return compact($pieces);
        endif;

        // Définition du jeton dans les variables de session
        $_SESSION['fb_access_token'] = (string)$accessToken;

        // Transmission de la réponse
        return compact($pieces);
    }

    /**
     * Déconnection de Facebook
     *
     * @return array
     */
    final public function clear()
    {
        if ( ! $id = self::tFyAppGetRequestVar('tify_api_fb_clear', false)) :
            return;
        endif;

        // Suppression des information de jeton dans les variables de session
        $_SESSION['fb_access_token'] = '';
    }

    /**
     * Récupération de l'App ID
     *
     * @return int
     */
    public function getAppId()
    {
        if (!empty($this->Config['app_id'])) :
            return (string)$this->Config['app_id'];
        endif;
    }
}