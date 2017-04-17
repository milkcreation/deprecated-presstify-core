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
    public function field_open_default( $field, $id, $class )
    {
       return   "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"{$class}\">\n<div class=\"form-group\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un champ
     */ 
    public function field_close_default( $field )
    {
       return   "</div>\n</div>\n";
    }
    
    /**
     * Libellé par défault de l'affichage d'un champ
     */
    public function field_label_default( $field, $input_id, $class, $label, $required )
    {
        return "<label for=\"{$input_id}\" class=\"col-sm-2 control-label {$class}\">{$label}{$required}</label>\n";
    }
    
    /**
     * Pré-affichage par défaut du contenu d'un champ
     */
    public function field_before_default( $field, $before )
    {
        return "<div class=\"col-sm-10\">". $before;
    }
    
    /**
     * Post-affichage par défaut du contenu d'un champ
     */
    public function field_after_default( $field, $after )
    {
        return $after ."</div>";
    }
    
    /**
     * Liste des classes HTML du contenu d'un champ
     */
    public function field_classes_default( $field, $classes )
    {
        $classes[] = 'form-control';
        
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un champ
     */
    public function button_open_default( $button, $id, $class )
    {
        return "<div class=\"form-group\">\n<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"col-sm-offset-2 col-sm-10 {$class}\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un champ
     */
    public function button_close_default( $button )
    {
        return "</div>\n</div>\n";
    }
    
    /**
     * Liste des classes HTML d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function button_classes_default( $button, $classes )
    {
        $classes[] = 'btn btn-primary';
        
        return $classes;
    }
}