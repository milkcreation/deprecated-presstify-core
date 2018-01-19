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
        // Bypass
        if (!$route = $this->appGetContainer('tiFy\Core\Route\Route')) :
            return;
        endif;
        if (!$response = $route->getResponse()) :
            return;
        endif;

        // Récupération des arguments
        $args = self::tFyAppGetRequestVar('tify_route_args', [], 'ATTRIBUTES');

        // Récupération de la sortie
        $body = '';
        if ($this->return instanceof \tiFy\Core\Route\View) :
            ob_start();
            $this->return->render($args);
            $body = ob_get_clean();
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