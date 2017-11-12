<?php
namespace tiFy\Core\Roles;

use tiFy\Core\Templates\Templates;

class Factory extends \tiFy\App\Factory
{
    /**
     * Identifiant de qualification unique
     */
    private $Id     = null;

    /**
     * Liste des attributs de configuration
     * @var array
     */
    private $Attrs  = [];

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant unique de qualification du role
     * @param array $attrs {
     *      Liste des attributs de configuration.
     *
     *      @param string $display_name Nom d'affichage.
     *      @param string $desc Texte de description.
     *      @param array $capabilities {
     *          Liste des habilitations Tableau indexés des habilitations permises ou tableau dimensionné
     *
     *          @var string $cap Nom de l'habilitation => @var bool $grant privilege
     *      }
     * }
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        parent::__construct();

        // Définition de l'identifiant
        $this->Id = $id;

        // Definition des attributs de configuration
        $this->Attrs = $this->parseAttrs($attrs);

        // Définition des événements de déclenchement
        $this->tFyAppActionAdd('init', 'init', 1);
        $this->tFyAppActionAdd('tify_templates_register');
    }

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
        $role_name = $this->getId();

        // Création du rôle
        if (!$role = \get_role($role_name)) :
            $role = \add_role($role_name, $this->getAttr('display_name'));
        endif;

        // Mise à jour des habilitations
        if ($capabilities = $this->getAttr('capabilities')) :
            foreach ($capabilities as $cap => $grant) :
                if (!isset($role->capabilities[$cap]) || ($role->capabilities[$cap] !== $grant)) :
                    $role->add_cap($cap, $grant);
                endif;
            endforeach;
        endif;
    }

    /**
     * Déclaration d'interfaces utilisateur d'administration
     */
    public function tify_templates_register()
    {
        if (!$admin_ui = $this->getAttr('admin_ui', false)) :
            return;
        endif;

        $admin_ui = $this->parseAdminUi($admin_ui);

        Templates::register(
            'tiFyCoreRole-AdminUiUsers--' . $this->getId(),
            $admin_ui['global'],
            'admin'
        );

        Templates::register(
            'tiFyCoreRole-AdminUiUserList--' . $this->getId(),
            $admin_ui['list'],
            'admin'
        );

        Templates::register(
            'tiFyCoreRole-AdminUiUserEdit--' . $this->getId(),
            $admin_ui['edit'],
            'admin'
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des arguments de configuration
     *
     * @param array $attrs {
     *      Liste des attributs de configuration.
     *
     *      @param string $display_name Nom d'affichage.
     *      @param string $desc Texte de description.
     *      @param array $capabilities {
     *          Liste des habilitations Tableau indexés des habilitations permises ou tableau dimensionné
     *
     *          @var string $cap Nom de l'habilitation => @var bool $grant privilege
     *      }
     * }
     *
     * @return array
     */
    protected function parseAttrs($attrs = [])
    {
        $defaults = [
            'display_name'  => $this->getId(),
            'desc'          => '',
            'capabilities'  => []
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        // Traitement des habilitations
        if ($capabilities = $attrs['capabilities']) :
            $caps = [];
            foreach ($capabilities as $capability => $grant) :
                if (is_int($capability)) :
                    $capability = $grant;
                    $grant = true;
                endif;
                $caps[$capability] = $grant;
            endforeach;
            $attrs['capabilities'] = $caps;
        endif;

        return $attrs;
    }

    /**
     * Récupération de l'identifiant de qualification
     *
     * @return string
     */
    final public function getId()
    {
        return $this->Id;
    }

    /**
     * Récupération de la liste de attributs de configuration
     *
     * @return array
     */
    final public function getAttrList()
    {
        return $this->Attrs;
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Nom de l'attribut de configuration
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getAttr($name, $default = '')
    {
        if (!isset($this->Attrs[$name])) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }

    /**
     * Traitement des attributs de configuration des interface utilisateurs d'administration
     */
    final public function parseAdminUi($attrs = [])
    {
        $defaults = [
            'global'    => [
                'admin_menu'    => [
                    'menu_slug'     => 'tiFyCoreRole-AdminUiUsers--' . $this->getId(),
                    'menu_title'    => $this->getAttr('display_name'),
                    'position'      => 70
                ]
            ],
            'list'      =>  [
                'cb'            => 'tiFy\Core\Roles\Templates\Admin\UserList',
                'admin_menu'    => [
                    'menu_slug'     => 'tiFyCoreRole-AdminUiUsers--' . $this->getId(),
                    'parent_slug'   => 'tiFyCoreRole-AdminUiUsers--' . $this->getId(),
                    'menu_title'    => __('Tous les utilisateurs', 'tify'),
                    'position'      => 1
                ],
                'args'          => [
                    'roles'         => [$this->getId()]
                ]
            ],
            'edit'      =>  [
                'cb'            => 'tiFy\Core\Roles\Templates\Admin\UserEdit',
                'admin_menu'    => [
                    'menu_slug'     => 'tiFyCoreRole-AdminUiUserEdit--' . $this->getId(),
                    'parent_slug'   => 'tiFyCoreRole-AdminUiUsers--' . $this->getId(),
                    'menu_title'    => __('Ajouter', 'tify'),
                    'position'      => 2
                ],
                'args'          => [
                    'roles'         => [$this->getId()]

                ]
            ]
        ];
        if (is_bool($attrs)) :
            return $defaults;
        endif;

        foreach (['global', 'list', 'edit'] as $ui) :
            if (!isset($attrs[$ui])) :
                $attrs[$ui] = $defaults[$ui];
            else :
                if (isset($attrs[$ui]['admin_menu'])) :
                    $attrs[$ui]['admin_menu'] = \wp_parse_args($attrs[$ui]['admin_menu'], $defaults[$ui]['admin_menu']);
                endif;
            endif;
        endforeach;

        return $attrs;
    }
}