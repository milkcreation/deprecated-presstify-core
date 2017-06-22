<?php
namespace tiFy\Core\Forms\Form;

class Handle
{
    /* = ARGUMENTS = */
    // Paramètres
    /// Formulaire de référence
    private $Form                    = null;
    
    /// Arguments de requête
    private $QueryVars                = array();
    
    /// Arguments de requête des champs de formulaire
    private $FieldsVars                = array();
        
    /* = CONSTRUCTEUR = */
    public function __construct( \tiFy\Core\Forms\Form\Form $Form )
    {            
        // Définition du formulaire de référence
        $this->Form = $Form;
        
        // Récupération des variables de requête
        $this->_getQueryVars();
    }    
    
    /** == == **/
    public function proceed()
    {        
        // Bypass
        if( ! $nonce = $this->getQueryVar( $this->Form->getNonce() ) )
            return false;    
                        
        /// Provenance de la soumission du formulaire    
        if( ! wp_verify_nonce( $nonce, 'submit_'. $this->Form->getUID() ) ) :
            wp_die( __( '<h2>Erreur lors de la vérification d\'origine de la soumission de formulaire</h2><p>Impossible de déterminer l\'origine de la soumission de votre formulaire.</p>', 'tify' ), __( 'Erreur de soumission de formulaire', 'tify' ), array( 'form_id' => $this->Form->getID() ) );
        endif;
        
        // Définition de la session
        $this->Form->transport()->initSession();
        
        /// Vérification de la validité de la session existante
        if( ! $this->Form->transport()->getTransient() )
            wp_die( __( '<h2>Erreur lors de la soumission du formulaire</h2><p>Votre session de soumission de formulaire est invalide ou arrivée à expiration</p>', 'tify' ) );
             
        // Traitement des variables de requête
        if( ! $this->_parseQueryVars() )
            return;
           
        // Vérification des champs de formulaire
        if( ! $this->_checkQueryVars() )
            return;
       
        // Affichage du formulaire pour l'étape suivante
        //if( $this->master->steps->next() )
            //return;

        // Court-cicuitage du traitement de la requête
        $this->Form->call( 'handle_submit_request', array( &$this ) );
        
        // Affichage du formulaire et des erreurs suite au traitement de la requête    
        if( $this->hasError() )
            return;        
            
        // Court-cicuitage du traitement avant la redirection
        //$this->Form->call( 'handle_before_redirect', array( &$this->parsed_request, $this->original_request, $this->master ) );
        if( ! $this->_setSuccess() )
            return;
        
        // Post traitement avant la redirection
        $this->Form->call( 'handle_successfully', array( &$this ) );    
        
        // Redirection après le traitement
        $redirect = add_query_arg( $this->_redirectQueryArgs(), $this->getQueryVar( '_wp_http_referer', home_url('/') ) ); 

        // Court-cicuitage de la redirection    
        $this->Form->call( 'handle_redirect', array( &$redirect ) );

        if( $redirect ) :
            //$this->reset_request();
            wp_redirect( $redirect );
            exit;
        endif;
    }
    
    /* = CONTRÔLEUR = */
    /** == Récupération d'un argument de requête == **/
    public function getQueryVar( $var, $default = '' )
    {
        if( isset( $this->QueryVars[$var] ) )
            return $this->QueryVars[$var];
            
        return $default;
    }
    
    /** == Récupération des saisies de champ == **/
    public function getFieldsVars()
    {
        return $this->FieldsVars;
    }
        
    /** == Vérifie si un formulaire a été soumis avec succès == **/
    public function isSuccessful()
    {
        if( ! $transient = get_transient( $this->Form->transport()->getTransientPrefix() . $this->getQueryVar( 'success' ) ) )
            return false;
            
        if( $transient['ID'] != $this->Form->getID() )
            return false;
            
        return ( ! empty( $transient['success'] ) && $transient['success'] );
    }    
    
    /** == Récupération des variables de requête == **/
    private function _getQueryVars()
    {
        switch( $this->Form->getAttr( 'method' ) ) :
            case 'post' :
                $args = $_POST;
            break;
            case 'get' :
                $args = $_GET;
            break;
            default :
            case 'request' :
                $args = $_REQUEST;
            break;
        endswitch;
                    
        return $this->QueryVars = $args;
    }
    
    /** == Traitement des variables de requête == **/
    private function _parseQueryVars()
    {                
        $values = $this->getQueryVar( $this->Form->getUID() );
        $fields = $this->Form->fields();
        $vars    = array();
        
        // Traitement des valeurs de champs de formulaire
        foreach( (array) $fields as $field ) :    
            $vars[ $field->getSlug() ] = null;
        
            // Bypass des champs qui ne doivent pas à être traiter par la requête        
            if( ! $field->typeSupport( 'request' ) )            
                continue;
                    
            $value = ( isset( $values[ $field->getName() ] ) ) ? $values[ $field->getName() ] : null;//$field->getValue();
                    
            $this->Form->call( 'handle_parse_query_field_value', array( &$value, $field, $this ) );
            
            $vars[ $field->getSlug() ] = $this->Form->factory()->parseQueryVar( $field->getSlug(), $value );
            
            $field->setValue( $vars[ $field->getSlug() ] );
        endforeach;
            
        $this->FieldsVars = $vars;
        
        // Court-circuitage de la définition des valeur de champ
        $this->Form->call( 'handle_parse_query_fields_vars', array( &$this->FieldsVars, $fields, $this ) );
                
        foreach( (array) $fields as $field ) :
            $field->setValue( $this->FieldsVars[ $field->getSlug() ] );
        endforeach;
   
        return $this->FieldsVars;
    }
        
    /** == Vérification des variables de requêtes == **/ 
    private function _checkQueryVars()
    {    
        $errors = array();
        $fields = $this->Form->fields();
        
        // Vérification des variables de saisie du formulaire.        
        foreach( (array) $fields as $field ) :
            $field_errors = array();
            
            /// Champs requis    
            if( $field->isRequired() && empty( $field->getValue() ) ) :
                $field_errors[] = array(
                    'message'   => sprintf( $field->getRequired( 'error' ) , $field->getLabel() ),
                    'type'      => 'field',
                    'slug'      => $field->getSlug(),
                    'check'     => 'required',
                    'order'     => $field->getOrder()
                );
        
                //// Court-circuitage de la vérification de champ requis
                //Callbacks::call( 'handle_check_required', array( &$errors, $request['fields'][ $field['slug'] ], $this->master ) );
            /// Tests d'integrité
            elseif( $callbacks = $field->getIntegrityCallbacks() ) :                
                //// Instanciation du vérificateur d'intégrité
                $Checker = new Checker( $field );
            
                foreach( $callbacks as $callback ) :
                    if( $Checker->call( $field->getValue( true ), $callback ) ) :
                        continue;
                    endif;
                
                    $field_errors[] = array(
                        'message'   => sprintf( $callback['error'], $field->getLabel(), $field->getValue() ),
                        'type'      => 'field',
                        'slug'      => $field->getSlug(),
                        'check'     => $callback,
                        'order'     => $field->getOrder()
                    );        
                endforeach;                
            endif;
            
            $field_errors = $this->Form->factory()->checkQueryVar( $field, $field_errors );
            
            //// Court-circuitage de la vérification d'intégrité d'un champ
            $this->Form->call( 'handle_check_field', array( &$field_errors, $field ) );
            
            if( ! empty( $field_errors ) ) :
                foreach( $field_errors as $field_error ) :                    
                    $errors[] = $field_error;
                endforeach;
            endif;
        endforeach;

        //// Court-circuitage de la vérification d'intégrité des champs
        $this->Form->call( 'handle_check_fields', array( &$errors, $fields ) );
        
        foreach( (array) $errors as $error ) :
            if( is_string( $error ) ) :
                $this->addError( $error );
            else :
                $defaults = array( 
                    'message'   => '',
                    'type'      => 'field'
                );   
                $data = wp_parse_args( (array) $error, $defaults );
                $message = $data['message']; unset( $data['message'] );
                
                $this->addError( $message, $data );
            endif;
        endforeach;    
    
        if( $this->hasError() ) :
            return false;
        else :
            return true;
        endif;
    }
    
    /** == == **/
    private function _setSuccess()
    {
        $success = true;
        //// Court-circuitage de la vérification d'intégrité d'un champ
        //Callbacks::call( 'handle_set_success', array( &success ) );

        if( $success ) :
            $this->QueryVars['success'] = $this->Form->transport()->getSession();
            return $this->Form->transport()->updateTransient( array( 'success' => true ) );   
        endif;
    }
    
    /** == == **/
    private function _redirectQueryArgs()
    {
        return array( 'success' => $this->Form->transport()->getSession() );
    }
    
    /* = ALIAS = */
    /** == == **/
    public function addError( $message, $data = '' )
    {
        $this->Form->notices()->add( 'error', $message, $data );
    }
    
    /** == == **/
    public function getErrors( $args = array() )
    {
        return $this->Form->notices()->getByData( 'error', $args );
    }
    
    /** == == **/
    public function hasError()
    {
        return $this->Form->notices()->has( 'error' );
    }
}