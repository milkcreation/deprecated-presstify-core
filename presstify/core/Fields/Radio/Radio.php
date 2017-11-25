<?php
namespace tiFy\Core\Fields\Radio;

class Radio extends \tiFy\Core\Fields\Factory
{
    /**
     * Vérification de selection du bouton radio
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->getAttr('checked') === $this->getHtmlAttrs('value');
    }

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
            'attrs'        => [
                'id'    => 'tiFyCoreFields-Radio--' . static::$Instance
            ],
            'checked'           => null
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);
        $field->setHtmlAttr('type', 'radio');
        if ($field->isChecked()) :
            $field->setHtmlAttr('checked', 'checked');
        endif;

        ?><?php $field->before(); ?><input <?php $field->htmlAttrs(); ?>/><?php $field->after(); ?><?php
    }
}