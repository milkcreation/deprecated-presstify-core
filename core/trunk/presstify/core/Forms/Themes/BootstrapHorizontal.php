<?php
namespace tiFy\Core\Forms\Themes;

class BootstrapHorizontal extends \tiFy\Core\Forms\Factory
{
    /**
     * Liste des classes HTML d'un formulaire
     */
    public function form_classes( $form, $classes )
    {
        $classes[] = "form-horizontal";
        
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un champ
     */ 
    public function fields_open( $field, $id, $class )
    {
       return   "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"{$class}\">\n<div class=\"form-group\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un champ
     */ 
    public function fields_close( $field )
    {
       return   "</div>\n</div>\n";
    }
    
    /**
     * Libellé par défault de l'affichage d'un champ
     */
    public function fields_label( $field, $input_id, $class, $label, $required )
    {
        return "<label for=\"{$input_id}\" class=\"col-sm-2 control-label {$class}\">{$label}{$required}</label>\n";
    }
    
    /**
     * Pré-affichage par défaut du contenu d'un champ
     */
    public function fields_before( $field, $before )
    {
        return "<div class=\"col-sm-10\">". $before;
    }
    
    /**
     * Post-affichage par défaut du contenu d'un champ
     */
    public function fields_after( $field, $after )
    {
        return $after ."</div>";
    }
    
    /**
     * Liste des classes HTML du contenu d'un champ
     */
    public function fields_classes( $field, $classes )
    {
        $classes[] = 'form-control';
        
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un champ
     */
    public function buttons_open( $button, $id, $class )
    {
        return "<div class=\"form-group\">\n<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"col-sm-offset-2 col-sm-10 {$class}\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un champ
     */
    public function buttons_close( $button )
    {
        return "</div>\n</div>\n";
    }
    
    /**
     * Liste des classes HTML d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function buttons_classes( $button, $classes )
    {
        $classes[] = 'btn btn-primary';
        
        return $classes;
    }
}