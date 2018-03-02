<?php

namespace tiFy\Core\Forms\Addons;

use tiFy\Core\Forms\Addons;
use tiFy\Core\Forms\Form\Helpers;

class Factory extends \tiFy\App\Factory
{

    /* = ARGUMENTS = */
    // Configuration
    // Identifiant de l'addon
    public $ID = null;

    // Options de formulaire par défaut
    public $default_form_options = [];

    // Option de champ par défaut
    public $default_field_options = [];

    // Fonction de rappel (declencheurs)
    public $callbacks;

    // Paramètres
    /// Formulaire de référence
    private $Form = null;

    /// Attributs de formulaire
    private $FormAttrs = [];

    /// Attributs de champ de formulaire
    private $FieldsAttrs = [];

    /** == Initialisation de l'addon pour un formulaire == **/
    final public function _initForm($form, $attrs)
    {
        // Définition du formulaire de référence
        $this->Form = $form;

        // Définition des attributs de formulaire
        $this->FormAttrs = Helpers::parseArgs($attrs, $this->default_form_options);

        // Définition des fonctions de court-circuitage
        foreach ((array)$this->callbacks as $hookname => $args) :
            if (is_callable($args)) :
                $this->Form->callbacks()
                           ->setAddons($hookname, $this->getID(), $args);
            elseif (isset($args['function']) && is_callable($args['function'])) :
                $args = wp_parse_args($args, ['order' => 10]);
                $this->Form->callbacks()
                           ->setAddons($hookname, $this->ID, $args['function'],
                               $args['order']);
            endif;
        endforeach;

        if (method_exists($this, 'afterInit')) :
            call_user_func([$this, 'afterInit']);
        endif;
    }

    /** == Initialisation du formulaire courant == **/
    final public function setField($field, $attrs = [])
    {
        // Définition des attributs de champs
        $attrs                                = Helpers::parseArgs($attrs,
            $this->default_field_options);
        $this->FieldsAttrs[$field->getSlug()] = $attrs;

        return $attrs;
    }

    /** == Récupération de l'identifiant == **/
    final public function getID()
    {
        return $this->ID;
    }

    /** == Récupération d'un attribut de formulaire == **/
    final public function getFormAttr($attr, $default = '')
    {
        if (isset($this->FormAttrs[$attr])) {
            return $this->FormAttrs[$attr];
        }

        return $default;
    }

    /** == Définition d'un attribut de formulaire == **/
    final public function setFormAttr($attr, $value)
    {
        return $this->FormAttrs[$attr] = $value;
    }

    /** == Récupération des attributs de formulaire == **/
    final public function getFieldAttr($field, $attr, $default = '')
    {
        if ( ! is_object($field)) {
            $field = $this->Form->getField($field);
        }

        if (isset($this->FieldsAttrs[$field->getSlug()][$attr])) {
            return $this->FieldsAttrs[$field->getSlug()][$attr];
        }

        return $default;
    }

    /**
     * Récupération de l'objet formulaire
     *
     * @return \tiFy\Core\Forms\Form\Form
     */
    final public function form()
    {
        return $this->Form;
    }

    /**
     * Récupération de l'objet champs du formulaire
     *
     * @return \tiFy\Core\Forms\Form\Fields
     */
    final public function fields()
    {
        return $this->Form->fields();
    }
}