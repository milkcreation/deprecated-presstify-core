<?php
namespace tiFy;

use tiFy\Apps;

final class Components
{
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * 
     * @param string $id Identifiant du composant dynamique
     * @param mixed $attrs Attributs de configuration du composant dynamique
     * 
     * @return NULL|object
     */
    public static function register($id, $attrs = array())
    {
        $classname = "tiFy\\Components\\{$id}\\{$id}";

        return Apps::register(
            $classname,
            'components',
            array(
                'Id'        => $id,
                'Config'    => $attrs
            )
        );
    }
}