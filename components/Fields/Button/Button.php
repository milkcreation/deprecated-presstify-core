<?php

/**
 * @name Button
 * @desc Bouton d'action
 * @package presstiFy
 * @namespace tiFy\Components\Fields\Button
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Fields\Button;

use tiFy\Core\Field\AbstractFactory;

/**
 * @param array $args {
 *      Liste des attributs de configuration du champ
 *
 *      @var string $before Contenu placé avant le champ
 *      @var string $after Contenu placé après le champ
 *      @var array $attrs Liste des propriétés de la balise HTML
 *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
 *      @var string $value Attribut de configuration de la valeur initiale de soumission du champ "value"
 *      @var string $content Contenu de la balise HTML
 * }
 */
class Button extends AbstractFactory
{
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
            'before'  => '',
            'after'   => '',
            'attrs'   => [],
            'name'    => '',
            'value'   => '',
            'content' => __('Envoyer', 'tify')
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyField-button--' . $this->getIndex();
        endif;

        if (!isset($args['attrs']['type'])) :
            $args['attrs']['type'] = 'button';
        endif;

        return $args;
    }

    /**
     * Affichage
     *
     * @return string
     */
    final protected function display()
    {
        ob_start();
?><?php $this->before(); ?><button <?php $this->attrs(); ?>><?php $this->content(); ?></button><?php $this->after(); ?><?php

        return ob_get_clean();
    }
}