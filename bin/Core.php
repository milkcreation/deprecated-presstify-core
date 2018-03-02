<?php
namespace tiFy;

use tiFy\Apps;

final class Core
{
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * 
     * @param string $id Identifiant du composant natif
     * @param mixed $attrs Attributs de configuration du composant natif
     * 
     * @return NULL|object
     */
    public static function register($id, $attrs = array())
    {
        $classname = "tiFy\\Core\\{$id}\\{$id}";
        
        return Apps::register(
            $classname,
            'core',
            array(
                'Id'        => $id,
                'Config'    => $attrs
            )
        );
    }
}