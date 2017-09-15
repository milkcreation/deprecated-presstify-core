<?php
/**
 * @Overridable
 */
namespace tiFy\Core\Forms\FieldTypes\Number;

class Number extends \tiFy\Core\Forms\FieldTypes\Factory
{
    /* = ARGUMENTS = */
    // Identifiant
    public $ID = 'number';

    // Support
    public $Supports = [
        'integrity',
        'label',
        'placeholder',
        'request',
        'wrapper',
    ];

    // Attributs HTML
    // @see http://www.w3schools.com/html/html_form_attributes.asp
    public $HtmlAttrs = [
        'readonly',
        'disabled',
        'max',
        'maxlength',
        'min',
        'pattern',
        'readonly',
        'required',
        'size',
        'step'
    ];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        // Définition des fonctions de callback
        $this->Callbacks = [
            'handle_parse_query_field_value' => [
                $this,
                'cb_handle_parse_query_field_value',
            ],
        ];
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     * @return string
     */
    public function display()
    {
        $output = "";

        // Affichage du champ de saisie
        $output .= "<input type=\"number\"";
        /// ID HTML
        $output .= " id=\"" . $this->getInputID() . "\"";
        /// Classe HTML
        $output .= " class=\"" . join(' ', $this->getInputClasses()) . "\"";
        /// Name
        $output .= " name=\"" . esc_attr($this->field()->getDisplayName()) . "\"";
        /// Value
        $output .= " value=\"" . esc_attr($this->field()->getValue()) . "\"";
        /// Attributs
        $output .= $this->getInputHtmlAttrs();
        /// TabIndex
        $output .= " " . $this->getTabIndex();
        /// Fermeture
        $output .= "/>";

        return $output;
    }
}