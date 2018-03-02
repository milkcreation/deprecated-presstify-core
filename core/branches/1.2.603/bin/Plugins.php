<?php
namespace tiFy;

use tiFy\Apps;

final class Plugins
{
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * 
     * @param string $id Identifiant de l'extension
     * @param mixed $attrs Attributs de configuration de l'extension
     * 
     * @return NULL|object
     */
    public static function register($id, $attrs = array())
    {
        $classname = "tiFy\\Plugins\\{$id}\\{$id}";

        return Apps::register(
            $classname,
            'plugins',
            array(
                'Id'        => $id,
                'Config'    => $attrs
            )
        );
    }
}