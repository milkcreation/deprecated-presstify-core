<?php
namespace tiFy\Core\Taboox\Display;

use tiFy\Core\Control\Control;
use tiFy\Core\Control\Tabs\Tabs;


class Display extends \tiFy\App\Factory
{
    /**
     * Ecran courant
     * @var \WP_Screen
     */
    private $Screen         = null;

    /**
     * Identifiant d'accroche
     * @var string
     */
    private $Hookname       = null;

    /**
     * Boite à onglet
     * @var \tiFy\Core\Taboox\Box
     */
    private $Box            = null;

    /**
     * Liste des greffons de la boite à onglet
     * @var \tiFy\Core\Taboox\Node[]
     */
    private $Nodes          = [];

    /**
     * Arborescence des greffons de la boîte à onglet
     * @var array
     */
    private $TreeNodes      = [];

    /**
     * CONSTRUCTEUR
     *Action
     * @param array $attrs Attributs de configuration
     * @return void
     */
    public function __construct($attrs = [])
    {
        parent::__construct();

        // Définition des paramètres
        $this->Screen = $attrs['screen'];
        $this->Hookname = $attrs['hookname'];
        $this->Box = $attrs['box'];
        $this->Nodes = $attrs['nodes'];

        // Déclaration des événements de déclenchement
        switch($this->getBox()->getObjectType()) :
            case 'post_type' :
                if ($this->getHookname() === 'page') :
                    self::tFyAppAddAction('edit_page_form', 'render');
                else :
                    self::tFyAppAddAction('edit_form_advanced', 'render');
                endif;
                break;
            case 'options' :
                add_settings_section($this->getId(), null, [$this, 'render'], $this->getBox()->getObjectName());
                break;
            case 'taxonomy' :
                self::tFyAppAddAction($this->getScreen()->taxonomy . '_edit_form', 'render', 10, 2);
                break;
            case 'user' :
                self::tFyAppAddAction('show_user_profile', 'render');
                self::tFyAppAddAction('edit_user_profile', 'render');
                break;
        endswitch;
        self::tFyAppAddAction('admin_enqueue_scripts');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    final public function admin_enqueue_scripts()
    {
        if (\is_admin()) :
            wp_enqueue_style(
                'tiFyCoreTabooxDisplayAdmin',
                self::tFyAppUrl('tiFy\Core\Taboox\Taboox') . '/assets/css/Admin.css',
                ['tify_control-tabs'],
                150216
            );
            wp_enqueue_script(
                'tiFyCoreTabooxDisplayAdmin',
                self::tFyAppUrl('tiFy\Core\Taboox\Taboox') . '/assets/js/Admin.js',
                ['tify_control-tabs'],
                151019,
                true
            );
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'objet écran courant
     *
     * @return string
     */
    final public function getScreen()
    {
        return $this->Screen;
    }

    /**
     * Récupération de l'identifiant de qualification
     *
     * @return string
     */
    final public function getId()
    {
        return $this->getScreen()->id;
    }

    /**
     * Récupération de l'identifiant d'accroche de la page d'affichage
     *
     * @return string
     */
    final public function getHookname()
    {
        return $this->Hookname;
    }


    /**
     * Récupération de la classe de rappel de la boite à onglets.
     *
     * @return \tiFy\Core\Taboox\Box
     */
    final public function getBox()
    {
        return $this->Box;
    }

    /**
     * Récupération des classes de rappel des greffons.
     *
     * @return \tiFy\Core\Taboox\Node[]
     */
    final public function getNodes()
    {
        return $this->Nodes;
    }

    /**
     * Initialisation des greffons de boîte à onglet.
     *
     * @return array
     */
    private function initNodes()
    {
        $nodes = [];

        if ($_nodes = $this->getNodes()) :
            foreach($_nodes as $id => $node) :
                $attrs = [];
                $attrs['id']        = $node->getId();
                $attrs['title']     = $node->getAttr('title');
                $attrs['parent']    = $node->getAttr('parent', '');
                $attrs['content']   = call_user_func_array([$node, 'getAdminUiContent'], func_get_args());
                $attrs['position']  = $node->getAttr('position', null);
                $nodes[] = $attrs;
            endforeach;
        endif;

        return $nodes;
    }

    /**
     * Affichage de la boîte à onglets
     *
     * @return string
     */
    public function render()
    {
        // Définition des arguments passés dans la méthode
        $args = func_num_args() ? func_get_args() : null;

        // Création de l'arborescence des onglets
        $nodes = call_user_func_array([$this, 'initNodes'], $args);

        $output = "";
        $output .= "<div id=\"tiFyTaboox-Container--" . $this->getId() . "\" class=\"tiFyTaboox-Container\">";

        // Traitement de l'entête de la boîte de saisie
        $output .= "\t<div class=\"hndle tiFyTaboox-ContainerHeader\">";
        $title = $this->Box->getAttr('title', '');
        if (is_callable($title)) :
            ob_start();
            call_user_func_array($title, $args);
            $output .= ob_get_clean();
        else :
            $output .= "\t\t<h3 class=\"hndle\"><span>" . ($title ? $title : __('Réglages', 'tify')) . "</span></h3>";
        endif;
        $output .= "\t</div>";

        $output .= "\t<div id=\"tiFyTaboox-Wrapper--" . $this->getId() . "\" class=\"tiFyTaboox-Wrapper\">";
        $output .= "\t\t<div class=\"tiFyTaboox-WrapperBack\"></div>";
        $output .= "\t\t<div class=\"tiFyTaboox-WrapperContent\">";
        $output .= Control::Tabs(
            [
                'nodes' => $nodes
            ]
        );
        $output .= "\t\t</div>";
        $output .= "\t</div>";
        $output .= "</div>";

        echo $output;
    }
}