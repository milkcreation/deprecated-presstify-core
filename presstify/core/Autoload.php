<?php
namespace tiFy\Core;

class Autoload extends \tiFy\Environment\Core
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $CallActions                = array(
        'after_setup_tify'
    );

    /**
     * Ordre de priorité d'exécution des actions
     * @var mixed
     */
    protected $CallActionsPriorityMap    = array(
        // Après l'instanciation des plugins et des sets
        'after_setup_tify' => 1
    );

    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // Chargement des contrôleurs non-configurables
        new AjaxActions;
        new Capabilities;
        new Meta\Meta;
        new Params;
        new ScriptLoader\ScriptLoader;
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Au chargement de tiFy
     * 
     * @return void
     */
    final public function after_setup_tify()
    {
        // Chargement des contrôleurs configurables
        new Control\Control;
        new Cron\Cron;
        new CustomType\CustomType;
        new Db\Db;
        new Mail\Mail;
        new Templates\Templates;
        new Options\Options;
        new Taboox\Taboox;
        new Update\Update;
        new Upload\Upload;
        new Forms\Forms;
        new Security\Security;
    }
}