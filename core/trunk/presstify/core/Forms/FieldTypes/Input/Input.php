<?php
/**
 * @Overridable
 */

namespace tiFy\Core\Forms\FieldTypes\Input;

class Input extends \tiFy\Core\Forms\FieldTypes\Factory
{
    /**
     * Identifiant
     * @var string
     */
    public $ID = 'input';

    /**
     * Support
     * @var array
     */
    public $Supports = [
        'integrity',
        'label',
        'placeholder',
        'request',
        'wrapper',
    ];

    /**
     * Liste des attributs HTML permis
     * @var array
     */
    // Attributs HTML
    // @see http://www.w3schools.com/html/html_form_attributes.asp
    public $HtmlAttrs = [
        'readonly',
        'disabled',
        'autocomplete',
        'onpaste',
        /* @todo */
        /* 'size', 'maxlength', 'autofocus', 'height', 'width', 'list', 'min', 'max', 'multiple', 'pattern', 'placeholder', 'required', 'step' */
    ];

    /**
     * CONSTRUCTEUR
     *
     * @void
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

    /* = COURT-CIRCUITAGE = */
    /** == Modification de la valeur de soumission == **/
    public function cb_handle_parse_query_field_value(&$value)
    {
        // Nettoyage des antislash d'echappemment des guillemets
        $value = wp_unslash($value);
    }

    /* = CONTROLEURS = */
    /** == Affichage == **/
    public function display()
    {
        $output = "";

        // Affichage du champ de saisie
        $output .= "<input type=\"text\"";
        /// ID HTML
        $output .= " id=\"" . $this->getInputID() . "\"";
        /// Classe HTML
        $output .= " class=\"" . join(' ', $this->getInputClasses()) . "\"";
        /// Name
        $output .= " name=\"" . esc_attr($this->field()->getDisplayName()) . "\"";
        /// Placeholder
        $output .= " placeholder=\"" . esc_attr($this->getInputPlaceholder()) . "\"";
        /// Attributs
        $output .= $this->getInputHtmlAttrs();
        /// Value
        $output .= " value=\"" . esc_attr($this->field()->getValue()) . "\"";
        /// TabIndex
        $output .= " " . $this->getTabIndex();
        /// Fermeture
        $output .= "/>";

        return $output;
    }
}