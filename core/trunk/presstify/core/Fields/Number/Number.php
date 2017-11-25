<?php
namespace tiFy\Core\Fields\Number;

class Number extends \tiFy\Core\Fields\Factory
{
    /**
     * Liste des attributs HTML autorisés
     * @see https://www.w3schools.com/html/html_form_input_types.asp
     * @var array
     */
    protected $AllowedHtmlAttrs = [
        'disabled',
        'max', /** HTML5 */
        'maxlength',
        'min', /** HTML5 */
        'pattern', /** HTML5 */
        'readonly',
        'required', /** HTML5 */
        'size',
        'step',
        'value'
    ];

    /**
     * Affichage
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @param array $attrs {
     *          Liste des attributs de balise
     *
     *      }
     * }
     *
     * @return string
     */
    public static function display($id = null, $args = [])
    {
        static::$Instance++;

        $defaults = [
            'before'       => '',
            'after'        => '',
            'attrs'        => [
                'id'    => 'tiFyCoreFields-Number--' . static::$Instance
            ]
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);
        $field->setHtmlAttr('type', 'number');

        ?><?php $field->before(); ?><input <?php $field->htmlAttrs(); ?>/><?php $field->after(); ?><?php
    }
}