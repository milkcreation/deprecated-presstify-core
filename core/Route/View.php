<?php

namespace tiFy\Core\Route;

use tiFy\App;

class View extends App
{
    /**
     * Chemin vers le template d'affichage
     * @var string
     */
    private $Path = '';

    /**
     * Liste des arguments passés à la vue
     * @var array
     */
    private $Args = [];

    /**
     * Traitement de la classe comme une chaîne de caractère
     * @internal Affichage du rendu
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Déclaration
     *
     * @param $name
     *
     * @return self
     */
    public static function register($path, $args = [])
    {
        return (new self)
            ->path($path)
            ->with($args);
    }

    /**
     * Définition du chemin
     *
     * @param $path
     *
     * @return $this
     */
    private function path($path)
    {
        $this->Path = $path;

        return $this;
    }

    /**
     * Définition du chemin
     *
     * @param $path
     *
     * @return $this
     */
    private function with($args)
    {
        $this->Args = array_merge(
            $args,
            $this->Args
        );

        return $this;
    }


    /**
     * Affichage du gabarit
     *
     * @return string
     */
    public function render()
    {
        ob_start();
        self::tFyAppGetTemplatePart($this->Path, null, $this->Args);
        return ob_get_clean();
    }
}