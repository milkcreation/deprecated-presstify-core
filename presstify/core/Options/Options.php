<?php
namespace tiFy\Core\Options;

use tiFy\Core\Taboox\Taboox;

class Options extends \tiFy\App\Core
{
    /**
     * Configuration
     * @todo deprecated
     * 
     * @var unknown $page_title
     * @var unknown $menu_title
     * @var unknown $admin_bar_title
     * @var unknown $menu_slug
     * @var unknown $hookname
     * @var array $nodes
     * @var unknown $options
     */
    public  $page_title,
            $menu_title,
            $admin_bar_title,
            $menu_slug,
            $hookname,
            
            // Paramètres
            $nodes = array(),
            $options;
    
    /**
     * Contexte d'accroche
     * @var string
     */
    protected $Hookname                     = 'settings_page_tify_options';
    
    /**
     * Habilitation d'accès à l'interface d'administration
     * @var string
     */
    protected $Cap                          = 'manage_options';
    
    /**
     * Liste des sections de boîte à onglets déclarées
     * @var array[] {
     *      
     * }
     */
    protected static $Nodes                 = array();
    
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions                = array(
        'init',
        'admin_menu',
        'admin_bar_menu'
    );
    
    /**
     * Ordre de priorité d'exécution des actions
     * @var mixed
     */
    protected $tFyAppActionsPriority    = array(
        'init'                      => 15,
        'after_setup_tify'          => 98
    );
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {        
        // Traitement des paramètres
        /// Déclaration des sections de boîtes à onglets
        foreach ((array) self::tFyAppConfig('nodes') as $node_id => $args) :
            // Rétrocompatibilité
            if (is_int($node_id) && isset($args['id'])):
            else :
                $args['id'] = $node_id;
            endif;
            
            $this->registerNode($args);
        endforeach;

        // Configuration
        $this->page_title       = __('Réglages des options du thème', 'tify');
        $this->menu_title       = get_bloginfo('name');
        $this->admin_bar_title  = false;
        $this->menu_slug        = 'tify_options';

        do_action('tify_options_register_node');

        add_action('tify_taboox_register_box', array($this, 'registerBox'));
        add_action('tify_taboox_register_node', array($this, 'registerNodes'));
    }
    
    /**
     * Déclaration du menu d'administration
     * 
     * @return void
     */
    protected function admin_menu()
    {
        \add_options_page($this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array($this, 'admin_render'));
    }
    
    /**
     * Modification de la barre d'administration
     */
    protected function admin_bar_menu( $wp_admin_bar )
    {
        // Bypass - La modification n'est effective que sur l'interface utilisateurs
        if (is_admin())
            return;
        // Bypass - L'utilisateur doit être habilité
        if(! \current_user_can($this->Cap))
            return;
        
        // Déclaration du lien d'accès à l'interface
        $wp_admin_bar->add_node(
            array(
                'id'        => $this->menu_slug,
                'title'     => $this->admin_bar_title ? $this->admin_bar_title : $this->page_title,
                'href'      => admin_url("/options-general.php?page={$this->menu_slug}"),
                'parent'    => 'site-name'
            )
        );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration de la boîte à onglets
     * 
     * @return void
     */
    public function registerBox()
    {
        Taboox::registerBox( 
            $this->Hookname,
            'option',
            array(
                'title'     => $this->page_title,
                'page'      => $this->menu_slug
            )
        );
    }
    
    /**
     * Déclaration des sections de la boîte à onglets
     * 
     * @return void
     */
    final public function registerNodes()
    {
        foreach ((array) self::$Nodes as $attrs):
            Taboox::registerNode(
                $this->Hookname,
                $attrs
            );
        endforeach;
    }
    
    /**
     * Déclaration d'une section de boîte à onglets
     * 
     * @param array $args {
     *      Attributs de configuration de la section de boîte à onglets
     *      
     *      @var string $id Requis. Identifiant de la section.
     *      @var string $title Requis. Titre de la section.
     *      @var string $cb Classe de rappel d'affichage de la section.
     *      @var string $parent Identifiant de la section parente
     *      @var mixed $args Argument passé à la classe de rappel
     *      @var string $cap Habilitation d'accès à la section
     *      @var bool $show Affichage de la section
     *      @var int $order Ordre d'affichage
     *      @var string|string[] $helpers Chaine de caractères séparés par de virgules|Tableau indexé des classes de rappel d'aides à la saisie
     * }
     * 
     * @return void
     */
    public static function registerNode($args)
    {
        self::$Nodes[] = $args;
    }
    
    /**
     * Rendu de l'interface d'administration
     */
    public function admin_render()
    {
?>
<div class="wrap">
    <h2><?php echo $this->page_title;?></h2>
    
    <form method="post" action="options.php">
        <div style="margin-right:300px; margin-top:20px;">
            <div style="float:left; width: 100%;">
                <?php \settings_fields( $this->menu_slug );?>    
                <?php \do_settings_sections( $this->menu_slug );?>
            </div>                    
            <div style="margin-right:-300px; width: 280px; float:right;">
                <div id="submitdiv">
                    <h3 class="hndle"><span><?php _e( 'Enregistrer', 'tify' );?></span></h3>
                    <div style="padding:10px;">
                        <div class="submit">
                        <?php \submit_button(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
    }
}