<?php

/**
 * @name Route
 * @desc Gestionnaire de routage de page
 * @package presstiFy
 * @namespace \tiFy\Core\Route
 * @version 1.1
 * @subpackage Core
 * @since 1.2.596
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Route;

use tiFy\tiFy;
use League\Container\Container;
use League\Route\RouteCollection;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\Response\SapiEmitter;
use InvalidArgumentException;

class Route extends \tiFy\App\Core
{
    /**
     * Conteneur d'injection de dépendances
     * @var Container
     */
    private $Container;

    /**
     * Cartographie des routes déclarées
     * @var array
     */
    public $Map = [];

    /**
     * Valeur de retour
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $Response;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des dépendances
        $this->Container = new Container;

        // Définition du traitement de la réponse
        $this->Container->share('tiFy.core.route.response', function () {
            $response = new Response;

            return (new DiactorosFactory())->createResponse($response);
        });

        // Définition du traitement de la requête
        $this->Container->share('tiFy.core.route.request', function () {
            return (new DiactorosFactory())->createRequest(tiFy::getGlobalRequest());
        });

        // Définition du traitement de l'affichage
        $this->Container->share('tiFy.core.route.emitter', new SapiEmitter);

        // Définition du traitement des routes
        $this->Container->share('tiFy.core.route.collection', new RouteCollection($this->Container));

        // Déclaration des événements
        $this->appAddAction('init', null, 0);

        // Instanciation des fonctions d'aide à la saisie
        require_once $this->appDirname() . '/Helpers.php';
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        do_action('tify_route_register');

        // Bypass
        if (!$this->Map) :
            return;
        endif;

        // Définition de la cartographie des routes
        foreach ($this->Map as $name => $attrs) :
            $this->_set($name);
        endforeach;

        /**
         * Suppression du slash de fin dans l'url
         * @see https://symfony.com/doc/current/routing/redirect_trailing_slash.html
         * @see https://stackoverflow.com/questions/30830462/how-to-deal-with-extra-in-phpleague-route
         *
         * @var \Symfony\Component\HttpFoundation\Request $request
         */
        $request = tiFy::getGlobalRequest();

        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $method = $request->getMethod();

        if (($pathInfo != '/') && (substr($pathInfo, -1) == '/') && ($method === 'GET')) :

            $url = str_replace($pathInfo, rtrim($pathInfo, '/'), $requestUri);
            wp_safe_redirect($url, 301);
            exit;
        endif;

        // Traitement des routes
        try {
            $this->Response = $this->getContainer('collection')->dispatch(
                $this->Container->get('tiFy.core.route.request'),
                $this->Container->get('tiFy.core.route.response')
            );
        } catch (\League\Route\Http\Exception\NotFoundException $e) {

        } catch (\League\Route\Http\Exception\MethodNotAllowedException $e) {

        }

        return;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Définition d'une route
     *
     * @param string $name Identifiant de qualification de la route
     *
     * @return null|\League\Route\Route
     */
    private function _set($name)
    {
        // Bypass
        if (!isset($this->Map[$name])) :
            return null;
        endif;

        /**
         * @var string|array $method
         * @var string $group
         * @var string $path
         * @var string $cb
         */
        extract($this->Map[$name]);

        // Traitement du sous repertoire
        $path = ($sub = trim(basename(dirname($_SERVER['PHP_SELF'])), '/')) ? "/{$sub}/" . ltrim($path, '/') : $path;

        // Traitement de la méthode
        $method = ($method === 'any') ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'] : array_map('strtoupper', (array)$method);

        $scheme = tiFy::getGlobalRequest()->getScheme();
        $host = tiFy::getGlobalRequest()->getHost();

        return $this->getContainer('collection')->map(
            $method,
            $path,
            new Handler($name, $this->Map[$name])
        )
            ->setName($name)
            ->setScheme($scheme)
            ->setHost($host);
    }

    /**
     * Récupération du conteneur d'injection de dépendances
     *
     * @param string $alias response|request|emitter|collection
     *
     * @return Container
     */
    final public function getContainer($alias)
    {
        return $this->Container->get('tiFy.core.route.' . $alias);
    }

    /**
     * @return mixed
     */
    final public function getResponse()
    {
        return $this->Response;
    }

    /**
     * Déclaration
     *
     * @param string $name Identifiant de qualification de la route
     * @param array $attrs Attributs de configuration
     *
     * @return null|array
     *
     * @throws
     */
    final public static function register($name, $attrs = [])
    {
        /**
         * @var \tiFy\Core\Route\Route $instance
         */
        if (!$instance = self::tFyAppGetContainer('tiFy\Core\Route\Route')) :
            return null;
        endif;

        $defaults = [
            'method'   => 'any',
            'group'    => '',
            'path'     => '/',
            'cb'       => '',
            'strategy' => ''
        ];

        return $instance->Map[$name] = array_merge($defaults, $attrs);
    }

    /**
     * Vérifie la correspondance du nom de qualification d'une route existante avec la valeur soumise.
     *
     * @param string $name Identifiant de qualification de la route à vérifier
     *
     * @return bool
     */
    final public static function exists($name)
    {
        /**
         * @var \tiFy\Core\Route\Route $instance
         */
        if (!$instance = self::tFyAppGetContainer('tiFy\Core\Route\Route')) :
            return false;
        endif;

        return isset($instance->Map[$name]);
    }

    /**
     * Récupération de l'url d'une route déclarée
     *
     * @param string $name Identifiant de qualification de la route
     * @param array $replacements Arguments de remplacement
     *
     * @return string
     */
    final public static function url($name, $replacements = [])
    {
        /**
         * @var \tiFy\Core\Route\Route $instance
         */
        if (!$instance = self::tFyAppGetContainer('tiFy\Core\Route\Route')) :
            return '';
        endif;

        try {
            $router = $instance->getContainer('collection');
            $route = $router->getNamedRoute($name);
            $host = $route->getHost();
            $port = tiFy::getGlobalRequest()->getPort();
            $scheme = $route->getScheme();
            $path = $route->getPath();
            if ((($port === 80) && ($scheme = 'http')) || (($port === 443) && ($scheme = 'https'))) :
                $port = '';
            endif;

            $url = $scheme . '://' . $host . ($port ? ':' . $port : '') . $path;
            $url = preg_replace_callback(
                '#{(\w+|\d+)}#',
                function($matches) use (&$replacements) {
                    return array_shift($replacements);
                },
                $url
            );

            return $url;
        } catch (InvalidArgumentException $e) {
            return '';
        }
    }

    /**
     * Redirection de page vers une route déclarée.
     *
     * @param string $name Identifiant de qualification de la route
     * @param array $args Liste arguments passés en variable de requête dans l'url
     * @param int $status_code Code de redirection. @see https://fr.wikipedia.org/wiki/Liste_des_codes_HTTP
     *
     * @return void
     */
    final public static function redirect($name, $args = [], $status_code = 301)
    {
        if ($redirect_url = self::url($name, $args)) :
            \wp_redirect($redirect_url, $status_code);
            exit;
        endif;
    }

    /**
     * Récupération du nom de qualification de la route courante à afficher.
     *
     * @return string
     */
    final public static function currentName()
    {
        return (string)self::tFyAppGetRequestVar('tify_route_name', '', 'ATTRIBUTES');
    }

    /**
     * Récupération des arguments de requête passés dans la route courante.
     *
     * @return array
     */
    final public static function currentArgs()
    {
        return (array)self::tFyAppGetRequestVar('tify_route_args', [], 'ATTRIBUTES');
    }

    /**
     * Vérifie la correspondance du nom de qualification la route courante avec la valeur soumise.
     *
     * @param string $name Identifiant de qualification de la route à vérifier
     *
     * @return bool
     */
    final public static function isCurrent($name)
    {
        return ($name === self::currentName());
    }

    /**
     * Vérifie si la page d'affichage courante correspond à une route déclarée
     *
     * @return bool
     */
    final public static function hasCurrent()
    {
        return (self::currentName() !== '');
    }
}