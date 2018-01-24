<?php
namespace tiFy\Core\Forms\Form;

use tiFy\Core\Forms\Forms;
use tiFy\Core\Forms\Addons;
use tiFy\Core\Forms\Buttons;

class Form
{
    /* = ARGUMENTS = */
    // Configuration
    /// Attributs par défaut
    private $DefaultAttrs    = array(
        // Attributs de configuration
        'prefix'          => 'tiFyForm_',

        // DOM
        /// Identifiant HTML du conteneur
        'container_id'    => 'tiFyForm-Container--%s',
        /// Classe HTML du conteneur
        'container_class' => '',
        /// Identifiant HTML de la balise form
        'form_id'         => 'tiFyForm-Content--%s',
        /// Classe HTML de la balise form
        'form_class'      => '',
        /// Pré-affichage avant la balise form        
        'before'          => '',
        /// Post-affichage après la balise form
        'after'           => '',

        // Attributs HTML de la balise form    
        'method'          => 'post',
        'action'          => '',
        'enctype'         => '',

        // Attributs de paramètrage
        'addons'          => [],
        'buttons'         => [],
        'fields'          => [],
        'notices'         => [],
        'options'         => [],
        'callbacks'       => []
    );
    
    // Paramètres
    /// Identifiant
    private $ID                 = null;
    
    /// Attributs de configuration
    private $Attrs              = array();
        
    /// Buttons
    private $Buttons            = array();
            
    /// Options
    private $Options        = array();
    
    /// TabIndex
    private static $TabIndex        = 1000;
    
    /// Groupe
    public $FieldsGroup        = null;
    
    // Contrôleurs
    /// Addons
    private $Addons            = array();

    /**
     * Controleur des méthodes de rappel de court-circuitage
     * @var null|\tiFy\Core\Forms\Form\Callbacks
     */
    private $Callbacks        = null;
    
    /// Champs de formulaire
    private $Fields             = array();
    
    /// Traitement
    private $Handle            = null;
            
    /// Notices
    private $Notices        = null;
    
    /// Transports
    private $Transport        = null;

    /* = CONSTRUCTEUR = */
    public function __construct($id, $attrs = [])
    {
        // Définition de l'identifiant
        $this->ID = $id;
            
        // Chargement des contrôleurs
        $this->Callbacks    = new Callbacks( $this );
        $this->Handle       = new Handle( $this );
        $this->Transport    = new Transport( $this );
                
        // Définition des attributs par défaut dynamiques
        foreach( array( 'prefix', 'container_id', 'form_id' ) as $attr ) :
            $this->DefaultAttrs[ $attr ] = sprintf( $this->DefaultAttrs[ $attr ], $this->ID );
        endforeach;
            
        // Définition des attributs
        $this->Attrs = Helpers::parseArgs( $attrs, $this->DefaultAttrs );

        // Définition des méthodes de rappel de court-circuitage
        $this->_setCallbacks();

        // Définition des boutons
        $this->_setButtons();
        
        // Définition des addons
        $this->_setAddons();
        
        // Définition des options
        $this->_setNotices();
        
        // Définition des options
        $this->_setOptions();    
        
        // Définition des champs
        $this->_setFields();

        // Court circuitage des attributs formulaire
        $this->call( 'form_set_attrs', array( $this ) );
    }
                
    /* = PARAMETRAGE = */
    /** == Définition des addons == **/
    private function _setAddons()
    {        
        foreach( (array) $this->getAttr( 'addons' ) as $k => $v ) :
            if( ! $v ) :
                continue;
            elseif( is_string( $v ) ) :
                $id = $v; $attrs = array();
            else :
                $id = $k; $attrs = (array) $v;
            endif;                
            $this->Addons[$id] = Addons::set( $id, $this, $attrs );
        endforeach;

        //Callbacks::call( 'form_set_addons', array( &$this->Addons ) );
    }
    
    /** == Définition des boutons == **/
    private function _setButtons()
    {
        $unset = array();
        
        if( ! empty( $this->getAttr( 'buttons' ) ) ) : 
            foreach( (array) $this->getAttr( 'buttons' ) as $k => $v ) :
                if( is_bool( $v ) && ! $v ) :
                    array_push( $unset, 'submit' );
                    continue;
                elseif( is_string( $k ) ):
                    $id = $k; $attrs = $v;
                elseif( is_string( $v ) ) :
                    $id = $v; $attrs = array();
                else :
                    $id = $k; $attrs = $v;
                endif;
                            
                $this->Buttons[$id] = Buttons::set( $id, $this, $attrs );    
            endforeach;
        endif;    
        if( ! isset( $this->Buttons['submit'] ) && ! in_array( 'submit', $unset ) ) :
            $this->Buttons['submit'] = Buttons::set( 'submit', $this, array() );
        endif;    
        
        //Callbacks::call( 'form_set_buttons', array( &$this->Buttons ) );
    }
        
    /** == Définition des notifications == **/
    private function _setNotices()
    {
        $this->Notices = new Notices( $this );
        
        $attrs = Helpers::parseArgs(
            (array) $this->getAttr( 'notices' ),
            array(    
                // Erreurs
                'error'     => array(
                    // Intitulé de la liste des erreurs.
                    // string
                    'title'         => '',
                    // Affichage des erreurs. -1 : Toutes (par defaut) | 0 : Aucune | n : Nombre maximum à afficher    
                    'show'            => -1, 
                    // Affiché seulement si toutes les erreurs ne sont pas visible. Mettre à false pour masquer 
                    'teaser'         => '...',
                    // Affiche les erreurs relative à chaque champs
                    'field'            => false,
                    // Classe du conteneur 
                    'id'            => '',
                    // Classe du conteneur 
                    'class'            => '',
                    // Permettre la fermeture des messages d'erreurs
                    'dismissible'    => false
                ),
                // Succès    
                'success'    => array(
                    'message'    => __( 'Votre demande a bien été prise en compte et sera traitée dès que possible', 'tify' )
                )
            )
        );
        
        // Court-circuitage des attributs des notifications
        //Callbacks::call( 'form_set_notices_attrs', array( &$attrs, $this ) );
                
        $this->Notices->setAttrs( $attrs );
    }
    
    /** == Définition des options == **/
    private function _setOptions()
    {
        $this->Options = Helpers::parseArgs( 
            $this->getAttr( 'options' ), 
            array(
                // Ancrage 
                'anchor'         => $this->getAttr( 'container_id' ),
                // Gestion de formulaires par étape
                'paged'            => 0,
                // Affiche un résumé des soummissions au formulaire avant le traitement définitif
                'preview'        => false,
                // Affichage après le succès de soumission du formulaire ( form : affiche un nouveau formulaire )
                'success_cb'    => ''
            )        
        );
        
        // Post traitement de la définition des options de formulaire
        //Callbacks::call( 'form_set_options', array( &$this->Options ) );    
    }
    
    /** == Définition des champs == **/
    private function _setFields()
    {
        // Réinitialisation de l'instance
        Field::resetInstance();
        
        $fields         = array();
        $groups         = array();
        $orders         = array();
        $positions      = array(); 
        $i              = 0;
        
        foreach( (array) $this->getAttr( 'fields' ) as $attrs ) :
            $fields[] = $field = new Field( $this, $attrs );
            $groups[] = $field->getAttr( 'group', 0 );
            $orders[] = $field->getAttr( 'order', 0 );
            $positions[] = $i++; 
        endforeach;
                    
        array_multisort( $groups, $orders, $positions );
        
        foreach( $positions as $pos ) :
            $fields[$pos]->setOrder( $pos+1 );
            $this->Fields[] = $fields[$pos];
        endforeach;
    }

    /**
     * Définition des méthodes de rappel de court-circuitage
     *
     * @return void
     */
    private function _setCallbacks()
    {
        if (!$callbacks = $this->getAttr('callbacks')) :
            return;
        endif;

        foreach ($callbacks as $hookname => $attrs) :
            if (is_callable($attrs)) :
                $callable = $attrs;
                $priority = 10;
            elseif (isset($attrs['cb'])) :
                $callable = $attrs['cb'];
                $priority = isset($attrs['priority']) ? $attrs['priority'] : 10;
            else:
                continue;
            endif;

            $this->callbacks()->set($hookname, $callable, $priority);
        endforeach;
    }
    
    /* = PARAMETRES = */    
    /** == Récupération de l'ID du formulaire == **/
    public function getID()
    {
        return $this->getAttr();
    }
    
    /** == Récupération du prefixe du formulaire == **/
    public function getPrefix()
    {
        return $this->getAttr( 'prefix' );
    }
    
    /** == Récupération du slug de formulaire == **/
    public function getUID()
    {
        return $this->getPrefix() . $this->getID();
    }
    
    /** == Récupération du nonce de formulaire == **/
    public function getNonce()
    {
        return '_'. $this->getUID() .'_nonce';
    }
    
    /** == Récupération du titre du formulaire == **/
    public function getTitle()
    {
        return ( $title = $this->getAttr( 'title' ) ) ? $title : $this->getID();
    }
        
    /** == Récupération d'un attribut de formulaire == **/
    public function getAttr( $attr = 'ID' )
    {        
        if( isset( $this->Attrs[$attr] ) )
            return $this->Attrs[$attr];
    }
    
    /** == Définition d'un attribut de formulaire == **/
    public function setAttr( $attr, $value )
    {        
        $this->Attrs[$attr] = $value;
    }
    
    /** == Liste des classes du formulaire == **/
    public function getFormClasses()
    {
        if( is_string( $this->getAttr( 'form_class' ) ) ) :
            $classes = array_map( 'trim', explode( ',', $this->getAttr( 'form_class' ) ) );
        else :
            $classes = (array) $this->getAttr( 'form_class' );
        endif;
        
        $classes[] = "tiFyForm-Content";
        
        return $this->factory()->formClasses( $this, $classes );
    }
    
    /** == Vérifie si un addon est actif == **/
    public function hasAddon( $id )
    {
        return array_keys( $this->Addons, $id );
    }
    
    /** == Récupération d'un addon actif == **/
    public function getAddon( $id )
    {
        if( isset( $this->Addons[$id] ) )
            return $this->Addons[$id];
    }
    
    /** == Récupération d'un attribut de formulaire pour un addon actif == **/
    public function getAddonAttr( $id, $attr, $default = '' )
    {
        if( ! isset( $this->Addons[$id] ) )
            return $default;
        
        return $this->Addons[$id]->getFormAttr( $attr, $default );
    }
            
    /** == Récupération des options == **/
    public function getOptions()
    {
        return $this->Options;
    }
    
    /** == Récupération d'une option == **/
    public function getOption( $option )
    {
        if( isset( $this->Options[ $option ] ) )    
            return $this->Options[ $option ];
    }

    /**
     * Récupération de l'object champ selon son identifiant
     *
     * @param string $slug Identifiant de qualification du champs
     *
     * @return \tiFy\Core\Forms\Form\Field
     */
    public function getField($slug)
    {
        foreach ((array)$this->fields() as $field) :
            if ($field->getSlug() === $slug) :
                return $field;
            endif;
        endforeach;
    }
        
    /** == == **/
    public function increasedTabIndex()
    {
        return ++self::$TabIndex;
    }
    
    /** == Récupération de la session == **/
    public function getSession()
    {
        return $this->transport()->getSession();
    }
        
    /** == A l'initialisation du formulaire courant == **/
    public function onSetCurrent()
    {
        $this->call( 'form_set_current', array( &$this ) );
    }
    
    /** == A la réinitialisation du formulaire courant == **/
    public function onResetCurrent()
    {
        $this->call( 'form_reset_current', array( &$this ) );
    }
        
    /* = CONTROLEURS = */
    /** == Récupération des addons == **/
    public function addons()
    {
        return $this->Addons;    
    }
    
    /** == Récupération des boutons == **/
    public function buttons()
    {
        return $this->Buttons;
    }

    /**
     * Récupération du controleur des méthodes de rappel de court-circuitage
     * @var null|\tiFy\Core\Forms\Form\Callbacks
     */
    public function callbacks()
    {
        return $this->Callbacks;
    }
    
    /** == Récupération de la classe de construction == **/
    public function factory()
    {
        return Forms::get( $this->ID );
    }
    
    /** == Récupération des champs == **/
    public function fields()
    {
        return $this->Fields;
    }
    
    /** == Traitement du formulaire == **/ 
    public function handle()
    {
        return $this->Handle;
    }
    
    /** == Traitement du formulaire == **/ 
    public function notices()
    {
        return $this->Notices;
    }
    
    /** == Données embarquées == **/ 
    public function transport()
    {
        return $this->Transport;
    }
    
    /* = HELPERS = */
    /** == Execution d'un déclencheur == **/
    public function call( $hook, $args = array() )
    {
        return $this->callbacks()->call( $hook, $args );
    }
    
    /** == Récupération des valeurs de champs == **/
    public function getFieldsValues( $raw = false )
    {
        $values = array();
        
        foreach( $this->fields() as $field ) :
            $values[$field->getSlug()] = $field->getValue( $raw );
        endforeach;
        
        return $values;
    }    
    
    /* = AFFICHAGE = */
    /** == Affichage d'un formulaire == **/
    public function display()
    {            
        // Affichage du formulaire
        $output = "";                

        /// Ouverture du conteneur
        $output .= "<div id=\"". $this->getAttr( 'container_id' ) ."\" class=\"tiFyForm-Container". ( ( $container_class = $this->getAttr( 'container_class' ) ) ? ' '. $container_class : '' ) ."\">\n";
        
        /// Message en cas de succès de soumission du formulaire
        if( $this->handle()->isSuccessful() ) :
            $output .= "\t\t<div class=\"tiFyForm-Notices tiFyForm-Notices--success\">\n";
            $output .= $this->notices()->display( 'success' );
            $output .= "\t\t</div>\n";
            
            // Contenu affiché en cas de succès
            $output .= $this->successContent();            
        else :
            $output .= $this->displayForm();
        endif;
                
        /// Fermeture du conteneur
        $output .= "</div>\n";

        // Court-circuitage post-affichage
        $this->call( 'form_after_display', array( $this ) );
            
        return $output;    
    }
    
    /** == == **/
    public function displayForm()
    {
        $output = "";
        
        // Pré-affichage HTML
        $output .= $this->getAttr( 'before' );

        // Définition de l'action
        $action  = remove_query_arg( 'success' ); //$this->getAttr( 'action' );
        $action .= ( $anchor = $this->getOption( 'anchor' ) ) ? '#'. $anchor : '';
        
        // Balise d'ouverture du formulaire        
        $output .= "\t<form method=\"". $this->getAttr( 'method' ) ."\" id=\"". $this->getAttr( 'form_id' ) ."\" class=\"". join( ' ', $this->getFormClasses() ) ."\" action=\"{$action}\"";
        if( $enctype = $this->getAttr( 'enctype' ) )
            $output .= " enctype=\"{$enctype}\"";
        $output .= ">\n";        
        
        // Champs cachés requis 
        $output .= $this->hiddenFields();
        
        // Affichage des erreurs
        if( $this->notices()->has( 'error' ) ) :
            $output .= "\t\t<div class=\"tiFyForm-Notices tiFyForm-Notices--error\">\n";
            $output .= $this->notices()->display( 'error' );
            $output .= "\t\t</div>\n";
        endif;
            
        // Affichage des champs de formulaire
        $output .= "\t\t<div class=\"tiFyForm-Fields\">\n";
        foreach( (array) $this->fields() as $n => $field ) :
            // Ouverture du groupe de champs
            if( is_null( $this->FieldsGroup ) ) :
                $this->FieldsGroup = $field->getAttr( 'group' );
                $output .= "\t\t\t<div class=\"tiFyForm-FieldsGroup tiFyForm-FieldsGroup--{$this->FieldsGroup}\">";
            elseif( $this->FieldsGroup < $field->getAttr( 'group' ) ) :
                $this->FieldsGroup = $field->getAttr( 'group' );
                $output .= "\t\t\t</div>";
                $output .= "\t\t\t<div class=\"tiFyForm-FieldsGroup tiFyForm-FieldsGroup--{$this->FieldsGroup}\">";
            endif;
                
            $output .= $field->display();
            
            // Fermeture du groupe de champs
            if( count( $this->fields() )-1 === $n ) :
                $output .= "\t\t\t</div>";
            endif;            
        endforeach;
        $output .= "\t\t</div>";
                
        // Affichage des boutons
        $output .= "\t\t<div class=\"tiFyForm-Buttons\">\n";
            
        foreach( (array) $this->buttons() as $id => $button ) :        
            $output .= $button->_display();
        endforeach;        
        $output .= "\t\t</div>";
                
        // Balise de fermeture du formulaire    
        $output .= "\t</form>\n";
        
        // Post-affichage HTML
        $output .= $this->getAttr( 'after' );
        
        return $output;
    }
    
    /*** === Champs cachés de soumission de formulaire === ***/
    public function hiddenFields()
    {
        $output  = "";
        $output .= wp_nonce_field( 'submit_'. $this->getUID(), $this->getNonce(), true, false );
        
        if( $session = $this->getSession() )
            $output .= "<input type=\"hidden\" name=\"session_". $this->getUID() ."\" value=\"". esc_attr( $session ) ."\">";        
        
        $success = $this->getOption( 'success' );
        
        ////Callbacks::call( 'form_hidden_fields', array( &$output, $this ) );
        
        return $output;
    }
    
    /** == Contenu affiché sur la page de succès == **/
    public function successContent()
    {
        if( ! $callback = $this->getOption( 'success_cb' ) )
            return;
        
        if( $callback === 'form' ) :
            return $this->displayForm();
        elseif( is_callable( $callback ) ) :
            return call_user_func_array( $callback, array( $this ) );
        endif;
    }
}