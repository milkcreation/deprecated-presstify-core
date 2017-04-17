<?php
namespace tiFy\Components\Login;

class Factory
{
    /* = ARGUMENTS = */
    // Instance
    private static $Instance;

    // Identifiant
    protected $id                       = null;
    
    // Liste des erreurs courantes
    protected $Errors                   = null;
    
    /* = PARAMETRES = */
    // Identifiant du formulaire
    protected $FormID                   = null;
    
    // Url de redirection
    protected $RedirectUrl              = null;
    
    // Intitulé du champs de saisie de l'identifiant
    protected $LabelUsername            = '';
    
    // Intitulé du champs de saisie du mot de passe
    protected $LabelPassword            = '';
    
    // Intitulé de la selection de mémorisation des informations de connection
    protected $LabelRemember            = '';
    
    // Intitulé du bouton de soumission de l'authentification
    protected $LabelSubmitLogin         = '';
    
    // Texte d'aide du champs de saisie de l'identifiant
    protected $PlaceholderUsername      = '';
    
    // Texte d'aide du champs de saisie du mot de passe
    protected $PlaceholderPassword      = '';
    
    // Identifiant HTML de la zone de saisie de l'identifiant
    protected $IdUsername               = null;
    
    // Identifiant HTML de la zone de saisie du mot de passe
    protected $IdPassword               = null;
    
    // Identifiant HTML de la zone de selection de mémorisation des informations de connection
    protected $IdRemember               = null;
    
    // Identifiant HTML de la zone de soumission de l'authentification
    protected $IdSubmitLogin            = null;
    
    // Activation de la zone de selection de mémorisation des informations de connection
    protected $RememberActive           = true;
    
    // Valeur de l'identifiant de connection
    protected $ValueUsername            = '';
    
    // Valeur de la selection de mémorisation des informations de connection
    protected $ValueRemember            = false;
    
    // Liste des rôles autorisés a se connecter
    protected $AllowedRoles             = array( 'subscriber' );
    
    // Cartographie des messages d'erreurs
    protected $ErrorsMap                = array();
                
    // Liste des paramètres pouvant être définie
    protected static $settableParams    = array(
        'FormID',
        'RedirectUrl',  
        'LabelUsername', 'LabelPassword', 'LabelRemember', 'LabelSubmitLogin', 
        'PlaceholderUsername', 'PlaceholderPassword',
        'IdUsername', 'IdPassword', 'IdRemember', 'IdSubmitLogin',
        'RememberActive',
        'ValueUsername', 'ValueRemember',
        'AllowedRoles',
        'ErrorsMap'
    );
    
    /* = CONSTRUCTEUR = */
    public function __construct( $id = null, $config = array() )
    {
        self::$Instance++;
        
        $this->id = ( ! empty( $id ) ) ? $id : ( is_subclass_of( $this, __CLASS__ ) ? get_class( $this ) :  get_class( $this ) .'-'. self::$Instance );
        
        // Définition des paramètres par défaut
        // Identifiant du formulaire
        $this->FormID                       = 'tiFyLoginForm--'. self::$Instance;
        // Url de redirection
        $this->RedirectUrl                  = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];
        // Intitulé du champs de saisie de l'identifiant
        $this->LabelUsername                = __( 'Identifiant', 'tify' );
        // Intitulé du champs de saisie du mot de passe
        $this->LabelPassword                = __( 'Mot de passe', 'tify' );
        // Intitulé de la selection de mémorisation des informations de connection
        $this->LabelRemember                = __( 'Se souvenir de moi', 'tify' );
        // Intitulé du bouton de soumission de l'authentification
        $this->LabelSubmitLogin             = __( 'Connexion', 'tify' );
        // Texte d'aide du champs de saisie de l'identifiant
        $this->PlaceholderUsername          = __( 'Identifiant', 'tify' );
        // Texte d'aide du champs de saisie du mot de passe
        $this->PlaceholderPassword          = __( 'Mot de passe', 'tify' );
        // Identifiant HTML de la zone de saisie de l'identifiant
        $this->IdUsername                   = 'tify_login-username';
        // Identifiant HTML de la zone de saisie du mot de passe
        $this->IdPassword                   = 'tify_login-password';
        // Identifiant HTML de la zone de selection de mémorisation des informations de connection
        $this->IdRemember                   = 'tify_login-rememberme';
        // Identifiant HTML de la zone de soumission de l'authentification
        $this->IdSubmitLogin                = 'tify_login-submit_button';
        // Activation de la zone de selection de mémorisation des informations de connection
        $this->RememberActive               = true;
        // Valeur de l'identifiant de connection
        $this->ValueUsername                = '';
        // Valeur de la selection de mémorisation des informations de connection
        $this->ValueRemember                = false;        
        // Liste des rôles habilités à se connecter depuis cette interface
        $this->AllowedRoles                 = array( 'subscriber' );
        // Nombre de tentative de connection @todo
        $this->Attempt                      = -1;    
        
        // Définition de la cartographie des message d'erreurs
        $this->_setErrorsMap();
    }

    /* = PARAMETRAGE = */
    /** == Définition d'un paramètre == **/
    final public function setParam( $param, $value )
    {
        // Bypass
        if( ! in_array( $param, static::$settableParams ) )
            return;
        
        $this->{$param} = $value;
    }
    
    /** == Traitement des arguments du formulaire d'authentification == **/
    final protected function _loginFormParseArgs( $args = array() )
    {
        $defaults = array(
            'form_id'                   => $this->getParam( 'FormID' ),
            'redirect'                  => $this->getParam( 'RedirectUrl' ),            
            'label_username'            => $this->getParam( 'LabelUsername' ),
            'label_password'            => $this->getParam( 'LabelPassword' ),
            'placeholder_username'      => $this->getParam( 'PlaceholderUsername' ),
            'placeholder_password'      => $this->getParam( 'PlaceholderPassword' ),
            'label_remember'            => $this->getParam( 'LabelRemember' ),
            'label_log_in'              => $this->getParam( 'LabelSubmitLogin' ),
            'id_username'               => $this->getParam( 'IdUsername' ),
            'id_password'               => $this->getParam( 'IdPassword' ),
            'id_remember'               => $this->getParam( 'IdRemember' ),
            'id_submit'                 => $this->getParam( 'IdSubmitLogin' ),
            'remember'                  => $this->getParam( 'RememberActive' ),
            'value_username'            => $this->getParam( 'ValueUsername' ),
            'value_remember'            => $this->getParam( 'ValueRemember' )
        );

        return wp_parse_args( $args, apply_filters( 'tify_login_form_defaults', $defaults, $this ) );
    }

    /** == Définition de la catographie des message d'erreurs == **/
    final protected function _setErrorsMap()
    {
        $defaults = array(
            'empty_password'        => __( 'Veuillez renseigner le mot de passe', 'tify' ),
            'authentication_failed'    => __( 'Les idenfiants de connexion fournis sont invalides.', 'tify' ),
            'role_not_allowed'        => __( 'Votre utilisateur n\'est pas autorisé à se connecter depuis cette interface.' , 'tify' )
        );
        $this->ErrorsMap = wp_parse_args( $this->ErrorsMap, $defaults );
    }
    
    /* = CONTROLEURS */
    /** == Récupération de l'ID == **/
    final public function getID()
    {
        return $this->id;
    }
    
    /** == Récupération d'un paramètre == **/
    final public function getParam( $param, $default = '' )
    {
        // Bypass
        if( ! in_array( $param, static::$settableParams ) )
            return $default;
        
        if( method_exists( $this, 'getParam'.$param ) ) :
            return call_user_func( array( $this, 'getParam'. $param ), $default );
        elseif( isset( $this->{$param} ) ) :
            return $this->{$param};
        else :
            return $default;
        endif;
    }
        
    /** == Récupération de la liste des rôles == **/
    final public function getRoles()
    {
        return (array) $this->getParam( 'AllowedRoles' );
    }

    /** == Définition des erreurs == **/
    final public function setErrors( $errors )
    {
        $this->Errors = $errors;
    }
    
    /** == Récupération des erreurs == **/
    final public function getErrors()
    {
        return $this->Errors;
    }
    
    /** == Liste des erreurs == **/
    final protected function displayErrors()
    {
        $code = $this->getErrors()->get_error_code();

        if( isset( $this->ErrorsMap[$code] ) ) :
            return $this->ErrorsMap[$code];
        elseif( $message = $this->getErrors()->get_error_message() ) :
            return $message;
        else :
            return __( 'Erreur lors de la tentative d\'authentification', 'tify' );
        endif;
    }
    
    /* = SURCHARGE = */
    /** == Vérification des droits d'authentification d'un utilisateur == **/
    public function checkAuthenticate( $user, $username, $password )
    {
        return $user;
    }
    
    /* = AFFICHAGE = */
    /** == == **/
    public function display( $args = array(), $echo = false )
    {
        $output  = "";
        $output .= $this->errors();
        $output .= $this->form( $args = array() );
        
        if( $echo )
            echo $output;
    
        return $output;
    }
        
    /** == Formulaire == **/
    public function form( $args = array() )
    {
        // Définition des attributs        
        $args                       = $this->_loginFormParseArgs( $args );
        
        $login_form_top             = apply_filters( 'tify_login_form_top',         $this->formTop( $args ),         $args, $this );
        $login_form_content         = apply_filters( 'tify_login_form_content',        $this->formContent( $args ),     $args, $this );
        $login_form_remember        = apply_filters( 'tify_login_form_remember',    $this->formRemember( $args ),     $args, $this );
        $login_form_middle          = apply_filters( 'tify_login_form_middle',         $this->formMiddle( $args ),     $args, $this );
        $login_form_bottom          = apply_filters( 'tify_login_form_bottom',         $this->formBottom( $args ),     $args, $this );
        $login_form_submit          = apply_filters( 'tify_login_form_submit',         $this->formSubmit( $args ),     $args, $this );

        $output  = "";
        
        // Ouverture du formulaire
        $output .= "<form name=\"{$args['form_id']}\" id=\"{$args['form_id']}\" class=\"tiFyLogin-Form\" action=\"\" method=\"post\">";
        
        // Requis
        $output .= $this->hidden_fields( $args );
        
        // Pré-affichage du formulaire
        $output .= $login_form_top;
        
        // Contenu filtré
        $output .=    $login_form_content;
        
        // Affichage central
        $output .= $login_form_middle;
        
        // Mémorisation des informations de connection
        $output .= $login_form_remember;
        
        // Bouton de soumission filtré
        $output .=    $login_form_submit;
        
        // Post-affichage du formulaire
        $output .=    $login_form_bottom;
        
        // Fermeture du formulaire
        $output .= "</form>";
        
        // Sortie filtrée 
        return apply_filters( 'tify_login_display', $output, $args, $this );
    }
        
    /** == Pré-affichage == **/
    public function formTop( $args )
    {
        if( ! $this->getErrors() )
            return '';
        
        return $this->formErrors();
    }
    
    /** == Contenu == **/
    public function formContent( $args )
    {
        return     "<p class=\"tiFyLogin-Part tiFyLogin-Field tiFyLogin-Username\">".
                    "<label for=\"". esc_attr( $args['id_username'] ) ."\" class=\"tiFyLogin-Label tiFyLogin-UsernameLabel\">".
                        esc_html( $args['label_username'] ).
                    "</label>".
                    "<input type=\"text\"".
                        " name=\"log\"".
                        " id=\"". esc_attr( $args['id_username'] ) . "\"".
                        " class=\"input tiFyLogin-Input tiFyLogin-Input--text tiFyLogin-UsernameInput\"".
                        " value=\"". esc_attr( $args['value_username'] ) ."\"".
                        " placeholder=\"". esc_html( $args['placeholder_username'] ) ."\"".
                        " size=\"20\"".
                    "/>".
                "</p>".
                "<p class=\"tiFyLogin-Part tiFyLogin-Field tiFyLogin-Password\">".
                    "<label for=\"". esc_attr( $args['id_password'] ) ."\" class=\"tiFyLogin-Label tiFyLogin-PasswordLabel\">".
                        esc_html( $args['label_password'] ).
                    "</label>".
                    "<input type=\"password\"".
                        " name=\"pwd\"".
                        " id=\"". esc_attr( $args['id_password'] ) . "\"".
                        " class=\"input tiFyLogin-Input tiFyLogin-Input--text tiFyLogin-PasswordInput\"".
                        " value=\"\"".
                        " placeholder=\"". esc_html( $args['placeholder_password'] ) ."\"".
                        " size=\"20\"".
                    "/>".
                "</p>";
    }
    
    
    /** == Affichage central == **/
    public function formMiddle( $args )
    {
        return '';
    }
    
    /** == Mémorisation des informations de connection == **/
    public function formRemember( $args )
    {
        if( ! $args['remember']  )
            return;
        
        return    "<p class=\"tiFyLogin-Part tiFyLogin-Field tiFyLogin-Remember\">".
                    "<input name=\"rememberme\"".
                        " type=\"checkbox\"".
                        " id=\"". esc_attr( $args['id_remember'] ) . "\"".
                        " class=\"input tiFyLogin-Input tiFyLogin-Input--checkbox tiFyLogin-RememberInput\"".
                        " value=\"forever\"".
                        ( $args['value_remember'] ? " checked=\"checked\"" : "" ).
                    " />".
                    "<label for=\"". esc_attr( $args['id_remember'] ) ."\" class=\"tiFyLogin-Label tiFyLogin-RememberLabel\">".
                        esc_html( $args['label_remember'] ).
                    "</label>".
                "</p>";
    }
    
    /** == Post-affichage == **/
    public function formBottom( $args )
    {
        return '';
    }
    
    /** == Bouton de soumission == **/
    public function formSubmit( $args )
    {
        return     "<p class=\"tiFyLogin-Part tiFyLogin-Handler tiFyLogin-Submit\">".
                    "<input type=\"submit\"".
                        " name=\"tify_login-submit\"".
                        " id=\"". esc_attr( $args['id_submit'] ) ."\"".
                        " class=\"input tiFyLogin-Input tiFyLogin-Input--submit tiFyLogin-SubmitInput\"".
                        " value=\"". esc_attr( $args['label_log_in'] ) ."\"".
                    " />".
                "</p>";
    }
        
    /** == Champs cachés == **/
    public function hidden_fields( $args = array() )
    {
        return     "<input type=\"hidden\" name=\"tiFyLogin-formID\" value=\"{$this->id}\">".
                "<input type=\"hidden\" name=\"redirect_to\" value=\"". esc_url( $args['redirect'] ) ."\" />";
    }

    /** == Erreurs de formulaire == **/
    public function formErrors()
    {
        return tify_control_notices( array( 'class' => "tiFyLogin-Part tiFyLogin-Errors", 'text' => $this->displayErrors() ), false );
    }

    /** == Lien de récupération de mot de passe oublié == **/
    public function lostpassword_link( $args = array() )
    {
        $defaults = array(
            'redirect'     =>  $this->getParam( 'RedirectUrl' ),
            'text'        => __( 'Mot de passe oublié', 'tify' )
        );
        $args = wp_parse_args( $args, $defaults );

        $output =     "<a href=\"". wp_lostpassword_url( $args['redirect'] ) ."\"".
                " title=\"". __( 'Récupération de mot de passe perdu', 'tify' ) ."\"".
                " class=\"tiFyLogin-LostPasswordLink\">".
                $args['text'].
                "</a>";

        return apply_filters( 'tify_login_lostpassword_link', $output, $args, $this );
    }

    /** == Lien de déconnection == **/
    public function logout_link( $args = array() )
    {
        $defaults = array(
            'redirect'     => add_query_arg( 'loggedout', 'true', $this->getParam( 'RedirectUrl' ) ),
            'text'        => __( 'Se déconnecter', 'tify' ),
            'class'        => ''
        );
        $args = wp_parse_args( $args, $defaults );

        $output  =     "<a href=\"". $this->logout_url( array( 'redirect' => $args['redirect'] ) ) ."\"".
                " title=\"". __( 'Déconnection', 'tify' ) ."\"".
                " class=\"tify_login-logout_link {$args['class']}\">".
                $args['text'].
                "</a>";

        return apply_filters( 'tify_login_logout_link', $output, $args, $this );
    }
    
    /** == Url de déconnection == **/
    public function logout_url( $args = array() )
    {
        $defaults = array(
            'redirect'     => add_query_arg( 'loggedout', 'true', $this->getParam( 'RedirectUrl' ) ),
        );
        $args = wp_parse_args( $args, $defaults );

        $output = add_query_arg( 'tiFyLogin-formID', $this->id, wp_logout_url( $args['redirect'] ) );

        return apply_filters( 'tify_login_logout_url', $output, $args, $this );
    }
}