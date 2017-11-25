<?php
namespace tiFy\Core\Fields\Button;

class Button extends \tiFy\Core\Fields\Factory
{
    /**
     * Liste des attributs HTML autorisés
     * @see @see https://www.w3schools.com/tags/tag_button.asp
     * @var array
     */
    protected $AllowedHtmlAttrs = [
        'autofocus', /** HTML5 */
        'disabled', /** HTML5 */
        'form', /** HTML5 */
        'formaction', /** HTML5 */
        'formenctype', /** HTML5 */
        'formmethod', /** HTML5 */
        'formtarget', /** HTML5 */
        'name',
        'type',
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
     *      @param string $content Contenu de la balise champ
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
            'before'  => '',
            'after'   => '',
            'content' => __('Envoyer', 'tify'),
            'attrs'   => [
                'id' => 'tiFyCoreFields-Button--' . static::$Instance,
            ]
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);
        if (!$field->getHtmlAttr('type')) :
            $field->setHtmlAttr('type', 'submit');
        endif;

        ?><?php $field->before(); ?><button <?php $field->htmlAttrs(); ?>><?php $field->tagContent(); ?></button><?php $field->before(); ?><?php
    }
}