<?php
namespace tiFy;

use tiFy\tiFy;

class Languages extends \tiFy\App\Factory
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions                = array(
        'plugins_loaded'
    );
    
    /**
     * DECLENCHEURS
     */
    /**
     * Après le chargement des plugins
     * 
     * @return void
     */
    public function plugins_loaded()
    {
        // Chargement des traductions
        load_textdomain(
            'tify', 
            tiFy::$AbsDir . '/bin/languages/tify-' . get_locale() . '.mo' 
        );
    }
}