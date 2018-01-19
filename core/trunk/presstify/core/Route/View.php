<?php

namespace tiFy\Core\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class View extends \tiFy\App
{
    /**
     * Chemin vers le template d'affichage
     * @var string
     */
    private $Path = '';

    /**
     * Déclaration
     *
     * @param $name
     *
     * @return self
     */
    public static function register($path)
    {
        return (new self)->setPath($path);
    }

    /**
     * Définition du chemin
     *
     * @param $path
     *
     * @return $this
     */
    private function setPath($path)
    {
        $this->Path = $path;

        return $this;
    }

    /**
     * Affichage du gabarit
     *
     * @param array $args
     *
     * @return string
     */
    public function render($args = [])
    {
        self::tFyAppGetTemplatePart($this->Path, null, $args);
    }
}