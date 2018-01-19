<?php

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
        $this->Container->share('tfy.route.response', function () {
            $response = new Response;

            return (new DiactorosFactory())->createResponse($response);
        });

        // Définition du traitement de la requête
        $this->Container->share('tfy.route.request', function () {
            $request = tiFy::getGlobalRequest();

            /**
             * Suppression du slash de fin dans l'url
             * @see https://symfony.com/doc/current/routing/redirect_trailing_slash.html
             * @see https://stackoverflow.com/questions/30830462/how-to-deal-with-extra-in-phpleague-route

            $url = $request->getRequestUri();
            if (substr($url, -1) === '/') :
                wp_redirect(rtrim($url, '/'));
                exit;
            endif;
             */
            return (new DiactorosFactory())->createRequest($request);
        });

        // Définition du traitement de l'affichage
        $this->Container->share('tfy.route.emitter', new SapiEmitter);

        // Définition du traitement des routes
        $this->Container->share('tfy.route.collection', new RouteCollection($this->Container));

        // Déclaration des événements
        $this->appAddAction('init', null, 0);

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_route_url', 'url');
        $this->appAddHelper('tify_route_current', 'current');
        $this->appAddHelper('tify_route_is', 'is');
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

        // Définition de la cartographie des routes
        foreach ($this->Map as $name => $attrs) :
            $this->_set($name);
        endforeach;

        try {
            $this->Response = $this->getContainer('collection')->dispatch(
                $this->Container->get('tfy.route.request'),
                $this->Container->get('tfy.route.response')
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
     * @param $name Identifiant de qualification de la route
     *
     * @return \League\Route\Route
     */
    private function _set($name)
    {
        // Bypass
        if (!isset($this->Map[$name])) :
            return;
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
        $method = ($method === 'any') ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'] : $method;

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
        return $this->Container->get('tfy.route.' . $alias);
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
     * @return array
     */
    final public static function register($name, $attrs = [])
    {
        if (!$instance = self::tFyAppGetContainer('tiFy\Core\Route\Route')) :
            return;
        endif;

        $defaults = [
            'method' => 'any',
            'group' => '',
            'path' => '/',
            'cb' => '',
            'strategy' => ''
        ];
        $instance->Map[$name] = array_merge($defaults, $attrs);
    }

    /**
     * Récupération de l'url d'une route
     *
     * @param $name Identifiant de qualification de la route
     *
     * @return string
     */
    final public static function url($name)
    {
        if (!$instance = self::tFyAppGetContainer('tiFy\Core\Route\Route')) :
            return;
        endif;

        try {
            $route = $instance->getContainer('collection')->getNamedRoute($name);
            $port = tiFy::getGlobalRequest()->getPort();

            return $route->getScheme() . '://' . $route->getHost() . ($port ? ':' . $port : '') . $route->getPath();
        } catch (InvalidArgumentException $e) {
            return;
        }
    }

    /**
     * Récupération du nom de la route courante
     *
     * @return string
     */
    final public static function current()
    {
        return self::tFyAppGetRequestVar('tify_route_name', '', 'ATTRIBUTES');
    }

    /**
     * Récupération du nom de la route courante
     *
     * @param $name Identifiant de qualification de la route
     *
     * @return string
     */
    final public static function is($name)
    {
        return ($name === self::current());
    }
}