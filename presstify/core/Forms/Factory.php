<?php
namespace tiFy\Core\Forms;

use \tiFy\Core\Forms\Forms;

class Factory
{
    /**
     * Classe de rappel du Formulaire
     */
    private $Form;    
    
    /**
     * CONSTRUCTEUR
     * 
     * @param string $id
     * @param array $attrs
     */
    public function __construct( $id, $attrs = array() )
    {
        $this->Form = new \tiFy\Core\Forms\Form\Form( $id, $attrs );
        
        add_action( 'tify_form_loaded', array( $this, 'tify_form_loaded') );
    }
     
    /**
     * DECLENCHEURS
     */
    /** 
     * Au chargement complet des formulaires
     */
    final public function tify_form_loaded()
    {
        tify_control_enqueue( 'notices' );
        
        Forms::setCurrent( $this );        
        $this->Form->handle()->proceed();        
        Forms::resetCurrent();
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la classe de rappel du formulaire
     */
    final public function getForm()
    {
        return $this->Form;
    }
    
    /**
     * Récupération d'un champs
     */
    final public function getField( $field_slug )
    {
        return $this->getForm()->getField( $field_slug );
    }
    
    /**
     * Traitement des variables de requête au moment de la soumission
     */
    final public function parseQueryVar( $field_slug, $value )
    {
        if( method_exists( $this, 'parse_query_var_' . $field_slug ) ) :
            return call_user_func( array( $this, 'parse_query_var_' . $field_slug ), $value );
        else :
            return call_user_func( array( $this, 'parse_query_var_default' ), $field_slug, $value );
        endif;       
    }
    
    /**
     * Vérification d'intégrité des variables de requêtes
     */ 
    final public function checkQueryVar( $field_obj, $errors )
    {    
        if( method_exists( $this, 'check_query_var_' . $field_obj->getSlug() ) ) :
            return call_user_func( array( $this, 'check_query_var_' . $field_obj->getSlug() ), $errors, $field_obj );
        else :
            return call_user_func( array( $this, 'check_query_var_default' ), $errors, $field_obj );
        endif; 
    }
    
    /**
     * Liste des classes HTML du formulaire
     */
    final public function formClasses( $form, $classes )
    {
        return is_callable( array( $this, 'form_classes' ) ) ? 
            call_user_func( array( $this, 'form_classes' ), $form, $classes ) :
            $classes;
    }
    
    /**
     * Ouverture de l'affichage d'un champ
     */
    final public function fieldOpen( $field, $id, $class )
    {
        return is_callable( array( $this, 'field_open'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_open_'. $field->getSlug() ), $field, $id, $class ) :
            call_user_func( array( $this, 'field_open_default' ), $field, $id, $class );
    }
    
    /**
     * Fermeture de l'affichage d'un champ
     */
    final public function fieldClose( $field )
    {
        return is_callable( array( $this, 'field_close_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_close_'. $field->getSlug() ), $field ) :
            call_user_func( array( $this, 'field_close_default' ), $field );
    }
    
    /**
     * Libellé de l'affichage d'un champ
     */
    final public function fieldLabel( $field, $input_id, $class, $label, $required )
    {
        return is_callable( array( $this, 'field_label_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_label_'. $field->getSlug() ), $field, $input_id, $class, $label, $required ) :
            call_user_func( array( $this, 'field_label_default' ), $field, $input_id, $class, $label, $required );
    }
    
    /**
     * Pré-affichage du contenu d'un champ
     */
    final public function fieldBefore( $field, $before )
    {
        return is_callable( array( $this, 'field_before_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_before_'. $field->getSlug() ), $field, $before ) :
            call_user_func( array( $this, 'field_before_default' ), $field, $before );
    }
    
    /**
     * Post-affichage du contenu d'un champ
     */
    final public function fieldAfter( $field, $after )
    {
        return is_callable( array( $this, 'field_after_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_after_'. $field->getSlug() ), $field, $after ) :
            call_user_func( array( $this, 'field_after_default' ), $field, $after );
    }
    
    /**
     * Liste des classes HTML du contenu d'un champ
     */
    final public function fieldClasses( $field, $classes )
    {
        return is_callable( array( $this, 'field_classes_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_classes_'. $field->getSlug() ), $field, $classes ) :
            call_user_func( array( $this, 'field_classes_default' ), $field, $classes );
    }
    
    /**
     * Ouverture de l'affichage d'un bouton
     */
    final public function buttonOpen( $button, $id, $class )
    {
        return is_callable( array( $this, 'button_open'. $button->getID() ) ) ? 
            call_user_func( array( $this, 'button_open_'. $button->getID() ), $button, $id, $class ) :
            call_user_func( array( $this, 'button_open_default' ), $button, $id, $class );
    }
    
    /**
     * Fermeture de l'affichage d'un bouton
     */
    final public function buttonClose($button )
    {
        return is_callable( array( $this, 'button_close_'. $button->getID() ) ) ? 
            call_user_func( array( $this, 'button_close_'. $button->getID() ), $button ) :
            call_user_func( array( $this, 'button_close_default' ), $button );
    }
    
    /**
     * Liste des classes HTML d'un bouton
     */
    final public function buttonClasses( $button, $classes )
    {
        return is_callable( array( $this, 'button_classes_'. $button->getID() ) ) ? 
            call_user_func( array( $this, 'button_classes_'. $button->getID() ), $button, $classes ) :
            call_user_func( array( $this, 'button_classes_default' ), $button, $classes );
    }
    
    /**
     * SURCHARGE
     */
    /**
     * Traitement par défaut des variables de requête au moment de la soumission
     */
    public function parse_query_var_default( $field_slug, $value )
    {
        return $value;
    }
    
    /**
     * Vérification par défaut de l'intégrité des variables de requêtes
     */
    public function check_query_var_default( $errors, $field_obj )
    {
        return $errors;
    }

    /**
     * Affichage du formulaire
     */
    public function display( $echo = false )
    {
        $output = $this->getForm()->display();
        if( $echo )
            echo $output;
        
        return $output;
    }
    
    /**
     * Liste des classes HTML d'un formulaire
     * 
     * @see \tiFy\Core\Forms\Form
     */
    public function form_classes( $form, $classes )
    {
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un champ
     * 
     * @see \tiFy\Core\Forms\Form\Field
     */
    public function field_open_default( $field, $id, $class )
    {
        if( ! $field->typeSupport( 'wrapper' ) )
            return;

        return "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"{$class}\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un champ
     * 
     * @see \tiFy\Core\Forms\Form\Field
     */
    public function field_close_default( $field )
    {
        if( ! $field->typeSupport( 'wrapper' ) )
            return;

        return "</div>\n";
    }
    
    /**
     * Libellé par défault de l'affichage d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function field_label_default( $field, $input_id, $class, $label, $required )
    {
        return "<label for=\"{$input_id}\" class=\"{$class}\">{$label}{$required}</label>\n";
    }
    
    /**
     * Pré-affichage par défaut du contenu d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function field_before_default( $field, $before )
    {
        return $before;
    }
    
    /**
     * Post-affichage par défaut du contenu d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function field_after_default( $field, $after )
    {
        return $after;
    }
    
    /**
     * Liste des classes HTML du contenu d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function field_classes_default( $field, $classes )
    {
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function button_open_default( $button, $id, $class )
    {
        return "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"{$class}\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function button_close_default( $button )
    {
        return "</div>\n";
    }
    
    /**
     * Liste des classes HTML d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function button_classes_default( $button, $classes )
    {
        return $classes;
    }
    
}