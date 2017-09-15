<?php

namespace tiFy\Core\Forms\FieldTypes;

use tiFy\Core\Forms\FieldTypes;
use tiFy\Core\Forms\Form\Helpers;

abstract class Factory extends \tiFy\App\Factory
{

    /* = ARGUMENTS = */
    // Configuration
    /// Identifiant du type de champ
    public $ID = null;

    /// Support
    public $Supports = [];

    /// Attributs HTML
    public $HtmlAttrs = [];

    /// Options par défaut
    public $Defaults = [];

    /// Fonctions de rappel
    public $Callbacks = [];

    // PARAMETRES
    /// Champ de référence
    private $Field = null;

    /// Formulaire de référence
    private $Form = null;

    /// Options
    private $Options = null;

    /* = PARAMETRAGE = */
    /** == Initialisation du type de champ pour un champ de formulaire == **/
    public function initField($field)
    {
        // Définition du champ de référence
        $this->Field = $field;

        // Définition du formulaire de référence
        $this->Form = $field->form();

        // Définition des fonctions de court-circuitage
        foreach ((array)$this->Callbacks as $hookname => $args) :
            if (is_callable($args)) :
                $this->Form->callbacks()
                           ->setFieldType($hookname, $this->getID(), $args);
            elseif (isset($args['function']) && is_callable($args['function'])) :
                $args = wp_parse_args($args, ['order' => 10]);
                $this->Form->callbacks()
                           ->setFieldType($hookname, $this->ID,
                               $args['function'], $args['order']);
            endif;
        endforeach;
    }

    /** == Définition des options == **/
    public function initOptions($options)
    {
        $this->Options = Helpers::parseArgs($options, $this->Defaults);
    }

    /* = PARAMETRES = */
    /** == Récupération de l'identifiant == **/
    final public function getID()
    {
        return $this->ID;
    }

    /** == Vérification de support == **/
    final public function isSupport($support)
    {
        return in_array($support, $this->Supports);
    }

    /** == Récupération des options == **/
    final public function getOptions()
    {
        return $this->Options;
    }

    /** == Récupération d'une option == **/
    final public function getOption($option, $default = '')
    {
        if (isset($this->Options[$option])) {
            return $this->Options[$option];
        }

        return $default;
    }

    /** == Récupération d'une option == **/
    final public function setOption($option, $value)
    {
        return $this->Options[$option] = $value;
    }

    /** == Identifiant HTML de l'interface de saisie == **/
    public function getInputID()
    {
        return "tiFyForm-FieldInput--" . $this->field()
                                              ->formID() . "_" . $this->field()
                                                                      ->getSlug();
    }

    /** == Classes HTML de l'interface de saisie == **/
    public function getInputClasses()
    {
        $classes = [];

        if ($this->field()->getAttr('input_class')) {
            $classes[] = $this->field()->getAttr('input_class');
        }
        $classes[] = "tiFyForm-FieldInput";
        $classes[] = "tiFyForm-FieldInput--" . $this->getID();
        $classes[] = "tiFyForm-FieldInput--" . $this->field()->getSlug();

        return $this->form()->factory()->fieldClasses($this->field(), $classes);
    }

    /** == Texte d'aide de l'interface de saisie == **/
    public function getInputPlaceholder()
    {
        if ( ! $placeholder = $this->field()->getAttr('placeholder')) {
            return;
        }

        if (is_bool($placeholder)) :
            $placeholder = $this->field()->getAttr('label');
        endif;

        return (string)$placeholder;
    }

    /** == Attributs HTML du champs de saisie == **/
    public function getInputHtmlAttrs()
    {
        $attrs = [];
        foreach ($this->HtmlAttrs as $attr) :
            $attrs[] = HtmlAttrs::getValue($attr, $this->field()->getAttr($attr));
        endforeach;

        if(! empty($attrs))
            return implode(" ", $attrs);
    }

    /** == Attributs tabindex de navigation au clavier == **/
    public function getTabIndex()
    {
        return "tabindex=\"{$this->field()->getTabIndex()}\"";
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'objet champ de référence
     *
     * @return \tiFy\Core\Forms\Form\Field
     */
    final public function field()
    {
        return $this->Field;
    }

    /** == Récupération de l'objet formulaire de référence == **/
    final public function form()
    {
        return $this->Form;
    }

    /* = AFFICHAGE = */
    /** == == **/
    final public function _display()
    {
        $output = "";
        // Affichage de l'intitulé
        if ($this->isSupport('label')) :
            $output .= $this->displayLabel();
        endif;

        // Pré-affichage
        $output .= $this->form()
                        ->factory()
                        ->fieldBefore($this->field(),
                            $this->field()->getAttr('before'));

        $output .= $this->display();

        // Post-affichage
        $output .= $this->form()
                        ->factory()
                        ->fieldAfter($this->field(),
                            $this->field()->getAttr('after'));

        return $output;
    }

    /** == Affichage == **/
    abstract protected function display();

    /** == Affichage de l'intitulé de champ == **/
    public function displayLabel()
    {
        $input_id = $this->getInputID();
        $class    = [];
        if ($this->field()->getAttr('label_class')) {
            $class[] = $this->field()->getAttr('label_class');
        }
        $class[]  = "tiFyForm-FieldLabel";
        $class[]  = "tiFyForm-FieldLabel--" . $this->getID();
        $class[]  = "tiFyForm-FieldLabel--" . $this->field()->getSlug();
        $label    = $this->field()->getLabel();
        $required = ($this->field()
                          ->getRequired('tagged')) ? "<span class=\"tiFyForm-FieldRequiredTag\">*</span>" : '';

        return $this->form()
                    ->factory()
                    ->fieldLabel($this->field(), $input_id, join(' ', $class),
                        $label, $required);
    }
}