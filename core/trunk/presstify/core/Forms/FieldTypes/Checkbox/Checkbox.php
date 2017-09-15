<?php
/**
 * @Overridable
 */
namespace tiFy\Core\Forms\FieldTypes\Checkbox;

class Checkbox extends \tiFy\Core\Forms\FieldTypes\Factory
{
    /**
     * Identifiant
     * @var string
     */
    public $ID = 'checkbox';

    /**
     * Support
     * @var array
     */
    public $Supports = [
        'integrity',
        'label',
        'request',
        'wrapper',
    ];


    /* = CONTROLEURS = */
    /** == Affichage == **/
    public function display()
    {
        $output = "";
        $output .= "<ul class=\"tiFyForm-FieldChoices\">\n";

        $i = 0;
        foreach (
            (array)$this->field()
                        ->getAttr('choices') as $value => $label
        ) :
            $output .= "\t<li class=\"tiFyForm-FieldChoice tiFyForm-FieldChoice--" . $this->getID() . " tiFyForm-FieldChoice--" . $this->field()
                                                                                                                                       ->getSlug() . " tiFyForm-FieldChoice--" . preg_replace('/[^a-zA-Z0-9_\-]/',
                    '', $value) . "\">\n";
            $output .= "\t\t<input type=\"checkbox\"";
            $output .= " id=\"" . $this->getInputID() . "-" . $i . "\"";
            $output .= "class=\"tiFyForm-FieldChoiceInput tiFyForm-FieldChoiceInput--checkbox\"";
            $output .= " value=\"" . esc_attr($value) . "\"";
            $output .= " name=\"" . esc_attr($this->field()
                                                  ->getDisplayName()) . "[]\"";
            $output .= "" . checked(in_array($value, (array)$this->field()->getValue()), true, false) . "";
            $output .= $this->getInputHtmlAttrs();

            /// TabIndex
            $output .= " " . $this->getTabIndex();
            $output .= "/>";
            $output .= "\t\t<label for=\"" . $this->getInputID() . "-" . $i . "\" class=\"tiFyForm-FieldChoiceLabel\">$label</label>";
            $output .= "\t</li>";
            $i++;
        endforeach;

        $output .= "</ul>";

        return $output;
    }
}