<?php
namespace tiFy\Core\Taboox;

abstract class Admin extends \tiFy\App\Factory
{
    /**
     * ID l'écran courant d'affichage du formulaire
     * @var \WP_Screen::$id;
     */
    protected $ScreenID;

    /**
     * Liste des attributs définissables
     */
    protected $SetAttrs                    = array('ScreenID');

    /**
     * Paramètres
     * @todo depreciation
     * 
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
     * 
     * @return void
     */
    public function init()
    {

    }

    /**
     * Initialisation de l'interface d'administration
     * 
     * @return void
     */
    public function admin_init()
    {

    }

    /**
     * Chargement de la page courante
     * 
     * @param \WP_Screen $current_screen
     * 
     * @return void
     */
    public function current_screen($current_screen)
    {

    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     * 
     * @return void
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