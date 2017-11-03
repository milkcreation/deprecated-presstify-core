<?php
namespace tiFy\Core\Roles;

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
}