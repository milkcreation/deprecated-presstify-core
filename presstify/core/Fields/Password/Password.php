<?php
namespace tiFy\Core\Fields\Password;

class Password extends \tiFy\Core\Fields\Factory
{
    /**
     * Affichage
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @var array $attrs {
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
                'id'    => 'tiFyCoreFields-Password--' . static::$Instance,
            ]
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);
        $field->setHtmlAttr('type', 'password');

        ?><?php $field->before(); ?><input <?php $field->htmlAttrs(); ?>/><?php $field->after(); ?><?php
    }
}