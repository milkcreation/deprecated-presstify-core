<?php
namespace tiFy\Core\Taboox;

abstract class Admin extends \tiFy\App\Factory
{
    /**
     * ID l'écran courant d'affichage du formulaire
     */
    protected $ScreenID;

    /**
     * Liste des attributs définissables
     */
    protected $SetAttrs                    = array( 'ScreenID' );

    /**
     * Paramètres
     * @var unknown $screen
     * @var unknown $page
     * @var unknown $env
     * @var array $args
     */
    public
        $screen,
        $page,                        
        $env,                                
        $args            = array();

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {

    }

    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {

    }

    /**
     * Chargement de la page courante
     */
    public function current_screen( $current_screen )
    {

    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    //abstract public function form();
}