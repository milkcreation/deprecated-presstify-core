<?php

/**
 * @name Textarea
 * @desc Zone de texte de saisie libre
 * @package presstiFy
 * @namespace tiFy\Components\Fields\Textarea
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Fields\Textarea;

use tiFy\Core\Field\AbstractFactory;

/**
 * @see https://www.w3schools.com/tags/tag_textarea.asp
 *
 * @param array $args {
 *      Liste des attributs de configuration du champ
 *
 *      @var string $before Contenu placé avant le champ
 *      @var string $after Contenu placé après le champ
 *      @var array $attrs Liste des propriétés de la balise HTML
 *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
 *      @var string $value Attribut de configuration de la valeur initiale de soumission du champ "value"
 * }
 */
class Textarea extends AbstractFactory
{
    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @return array
     */
    final protected function parse($args = [])
    {
        // Pré-traitement des attributs de configuration
        $args = parent::parse($args);

        // Traitement des attributs de configuration
        $defaults = [
            'before' => '',
            'after'  => '',
            'attrs'  => [],
            'name'   => '',
            'value'  => ''
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyField-textarea--' . $this->getIndex();
        endif;

        return $args;
    }

    /**
     * Traitement de l'attribut de configuration de la valeur de soumission du champ "value"
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseValue($args = [])
    {
        if (isset($args['value'])) :
            $args['content'] = $args['value'];
        endif;

        return $args;
    }

    /**
     * Affichage
     *
     * @return string
     */
    protected function display()
    {
        ob_start();
?><?php $this->before(); ?><textarea <?php $this->attrs(); ?>><?php $this->content(); ?></textarea><?php $this->after(); ?><?php

        return ob_get_clean();
    }
}