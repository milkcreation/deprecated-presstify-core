<?php
namespace tiFy\Core\Update;

class Factory
{
    /**
     * Identifiant unique
     *
     * @var string
     */
    protected $Id = null;

    /**
     * Habilitations de mise à jour
     *
     * @var string
     * @see \map_meta_cap(string $cap, int $user_id)
     */
    protected $Cap = 'manage_options';

    /**
     * Notification de mise à jour de l'interface d'administation
     *
     * @var mixed[]
     */
    protected $AdminNotice = array(
        'message' => '',
        'screen_id' => ''
    );

    /**
     * Url de redirection après la mise à jour
     *
     * @var string
     */
    protected $Redirect = '';
    
    /**
     * Format du numéro de version
     *
     * @var string
     */
    protected $Format = '(\d{1,})';

    /**
     * Numéro de version courante
     *
     * @var string
     */
    private $Current = null;

    /**
     * Liste des mises à jours
     *
     * @var string[]
     */
    private $Updates = array();

    /**
     * Liste des mises à jours à effectuer
     *
     * @var string[]
     */
    private $Availables = array();

    /**
     * Liste des mises à jours effectuées
     * 
     * @var string[]
     */
    private $Updated = array();
    
    /**
     * CONSTRUCTEUR
     *
     * @param string $id
     * @param array $attrs
     *
     * @return void
     */
    public function __construct($id, $attrs)
    {
        // Traitement des attributs
        $this->Id = $id;
        $defaults = array(
            'cap'           => 'manage_options',
            'admin_notice'  => array(),
            'redirect'      => admin_url('/'),
            'format'        => '(\d{1,})'
        );
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);
        
        $this->Cap = $cap;
        $this->AdminNotice = $this->parseAdminNotice($admin_notice);
        $this->Redirect = $redirect;
        $this->Format = $format;

        // Initialisation des déclencheurs
        add_action('init', array(
            $this,
            'init'
        ), 25);
        add_action('current_screen', array(
            $this,
            'current_screen'
        ));
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Vérifie si un appel de mise à jour est lancé
        if (! isset($_REQUEST['tFyUp']) || ($_REQUEST['tFyUp'] !== $this->getId()))
            return;
        // Contrôle s'il s'agit d'une routine de sauvegarde automatique.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;
        // Contrôle s'il s'agit d'une execution de page via ajax.
        if (defined('DOING_AJAX') && DOING_AJAX)
            return;
        
        // Mise à jour d'une version ponctuelle
        if(! empty($_REQUEST['v'])) :
            $version = $_REQUEST['v'];
            if($this->isAvailable($version)) :
                $this->update($version);
            else :
                \wp_die('tFyUpdateUnavailable', __('La mise à jour est indisponible', 'tify'), 500);
            endif;
        // Mise à jour de toutes les versions disponibles
        else:
            foreach ((array) $this->getAvailables() as $version) :
                $this->update($version);
            endforeach;
        endif;
        
        if ($this->Updated)
            $this->updated();
    }

    /**
     * Page courante
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    final public function current_screen($current_screen)
    {
        if (! $this->hasAvailable())
            return;
        if (! empty($this->AdminNotice['screen_id']) && ($this->AdminNotice['screen_id'] !== $current_screen->id))
            return;
        
        $update = $this->Id;
        add_action('admin_notices', function () use ($upgrade) {
?>
<div class="notice notice-info">
    <p><?php printf( $this->AdminNotice['message'], "<a href=\"". esc_attr( add_query_arg( 'tFyUp', $update, admin_url() ) ) ."\">". __( 'Mettre à jour', 'tify' ) ."</a>" ); ?></p>
</div>
<?php
        });
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'identifiant unique
     *
     * @return string
     */
    final public function getId()
    {
        return $this->Id;
    }
    
    /**
     * Récupération de l'habilitation de mise à jour
     *
     * @return string
     */
    final public function getCap()
    {
        return $this->Cap;
    }

    /**
     * Récupération de la version courante
     *
     * @return string
     */
    final public function current()
    {
        if (! is_null($this->Current))
            return $this->Current;
        
        return \get_option('tFyUp--' . $this->getId(), 0);
    }

    /**
     * Récupération de la liste complète des mises à jours
     *
     * @return string[]
     */
    final public function getList()
    {
        if (! empty($this->Updates))
            return $this->Updates;
        if (! current_user_can($this->getCap()))
            return $this->Updates = array();
        
        foreach ((array) get_class_methods($this) as $method) :
            if (! preg_match('/^version_([\d]*)/', $method, $version))
                continue;
            array_push($this->Updates, $version[1]);
        endforeach;
        
        return $this->Updates;
    }

    /**
     * Vérification d'existance de mises à jours à effectuer
     *
     * @return bool
     */
    final public function hasAvailable()
    {
        $availables = $this->getAvailables();
        
        return ! empty($availables);
    }

    /**
     * Récupération de la liste des mises à jours à effectuer
     *
     * @return bool
     */
    final public function getAvailables()
    {
        if (! empty($this->Availables))
            return $this->Availables;
        
        foreach ((array) $this->getList() as $version) :
            $_version = $this->formatVersion($version);
            if (version_compare($this->current(), $_version, '>='))
                continue;
            array_push($this->Availables, $version);
        endforeach;
        
        return $this->Availables;
    }
    
    /**
     * Vérification d'existance d'une mise à jour
     * 
     * @param string $version
     * 
     * @return bool
     */
    final public function isAvailable($version)
    {
        $availables = $this->getAvailables();
        
        return in_array($availables, $version);
    }

    /**
     * Mise à jour de la version
     *
     * @param string $version
     *
     * @return
     */
    final public function updateVersion($version)
    {
        return \update_option('tFyUp--' . $this->getId(), $version);
    }

    /**
     * Définition de la notification de mise à jour de l'interface d'administration
     *
     * @param mixed[] $args
     *
     * @return mixed[]
     */
    final public function parseAdminNotice($args = array())
    {
        $defaults = array(
            'message' => __('Des mises à jour sont disponibles %s', 'tify'),
            'screen_id' => ''
        );
        return wp_parse_args($args, $defaults);
    }

    /**
     * Formatage du numéro de version
     *
     * @param int $version
     *
     * @return string $version
     */
    final public function formatVersion($version)
    {
        if( preg_match_all('#'. $this->Format .'#', $version, $matches) ) :
            return implode('.', $matches[1]);
        endif;
    }
    
    /**
     * Mise à jour vers un numéro de version
     * 
     * @param string $version
     * 
     * @return mixed|\wp_die()
     */
    final public function update($version)
    {
        $return = call_user_func(array(
            $this,
            'version_' . $version
        ));
        
        if (is_wp_error($return)) :
            \wp_die($return->get_error_message(), __('Erreur rencontrée lors de la mise à jour', 'tify'), 500);
        else :
            return $this->Updated[$version] = $return;
        endif;
    }
    
    /**
     * Action après la mise à jour
     * @todo
     */
    final public function updated()
    {
        if (! $this->Location)
            $this->Location = (stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        if (! $this->Verbose) :
            \wp_redirect($this->Location);
            exit();
        else :
            // Composition du message
            $message = "<h2>" . __('Mise à jour effectuée avec succès', 'tify') . "</h2>" . "<ol>";
            foreach ($this->Upgraded as $version => $result) :
                $message .= "<li>" . sprintf(__('version : %d', 'tify'), $version);
                if (is_string($result))
                    $message .= "<br><em style=\"color:#999;font-size:0.8em;\">{$result}</em>";
                $message .= "</li>";
            endforeach;
            
            $message .= "</ol>" . "<hr style=\"border:none;background-color:rgb(238, 238, 238);height:1px;\">" . "<a href=\"{$this->Location}\" title=\"" . __('Retourner sur le site', 'tify') . "\" style=\"font-size:0.9em\">&larr; " . __('Retour au site', 'tify') . "</a>";
            // Titre
            $title = __('Mise à jour réussie', 'tify');
            
            \wp_die($message, $title, 426);
            exit();
        endif;
    }
}