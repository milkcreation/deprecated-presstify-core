<?php

namespace tiFy\Core\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Handler extends \tiFy\App\FactoryConstructor
{
    /**
     * Valeur de retour du controleur
     */
    private $return;

    /**
     * Traitement de l'affichage de l'interface utilisateur
     *
     * @return string
     */
    final public function template_redirect()
    {
        /**
         * Bypass
         * @var \tiFy\Core\Route\Route $route
         */
        if (!$route = $this->appGetContainer('tiFy\Core\Route\Route')) :
            return;
        endif;
        if (!$response = $route->getResponse()) :
            return;
        endif;

        // Récupération de la sortie
        $body = '';
        if ($this->return instanceof \tiFy\Core\Route\View) :
            $body = $this->return->render();
        elseif(is_string($this->return)) :
            $body = $this->return;
        endif;

        // Déclaration de la sortie
        $response->getBody()->write($body);

        // Affichage de la sortie
        $route->getContainer('emitter')->emit($response);
        exit;
    }

    /**
     *
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     *
     * @return ResponseInterface
     */
    final public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        // Définition des attribut de requête de la route courante
        self::tFyAppAddRequestVar(
            [
                'tify_route_name' => $this->getId(),
                'tify_route_args' => $args
            ],
            'ATTRIBUTES'
        );

        // Appel du controleur de route
        $cb = $this->getAttr('cb');

        // Ajout de la requête et de la réponse HTTP (PSR-7) à la liste des arguments
        array_push($args, $request, $response);

        if (is_callable($cb)) :
            $this->return = call_user_func_array($cb, $args);
        elseif(class_exists($cb)) :
            $reflection = new \ReflectionClass($cb);
            $this->return = $reflection->newInstanceArgs($args);
        endif;

        // Instanciation de traitement du retour
        $this->appAddAction('template_redirect', null, 0);

        return $response;
    }
}