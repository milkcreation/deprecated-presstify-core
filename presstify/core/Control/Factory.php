<?php

namespace tiFy\Core\Control;

abstract class Factory extends \tiFy\App\Factory
{

    /**
     * Intitulés des prefixes des fonctions
     */
    protected $Prefix = 'tify_control';

    /**
     * Identifiant des fonctions
     */
    protected $ID = '';

    /**
     * Liste des actions à déclencher
     */
    protected $tFyAppActions = [
        'init',
    ];

    /**
     * Liste des arguments pouvant être récupérés
     */
    protected $GetAttrs = ['ID'];

    /**
     * Liste des methodes à translater en Helpers
     */
    protected $Helpers = ['display'];

    /**
     * Liste de la cartographie des nom de fonction des Helpers
     */
    protected $HelpersMap = ['display' => ''];
}