<?php

/**
 * @name Label
 * @desc Libelé de champ
 * @package presstiFy
 * @namespace tiFy\Components\Fields\Label
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Fields\Label;

use tiFy\Core\Field\AbstractFactory;

/**
 * @param array $args {
 *      Liste des attributs de configuration du champ
 *
 *      @var string $before Contenu placé avant le champ
 *      @var string $after Contenu placé après le champ
 *      @var string $content Contenu de la balise champ
 *      @var array $attrs Liste des propriétés de la balise HTML
 * }
 */
class Label extends AbstractFactory
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
            'before'       => '',
            'after'        => '',
            'content'      => '',
            'attrs'        => []
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyField-label--' . $this->getIndex();
        endif;

        return $args;
    }

    /**
     * Affichage
     *
     *
     * @return string
     */
    protected function display()
    {
        ob_start();
?><?php $this->before(); ?><label <?php $this->attrs(); ?>/><?php $this->content(); ?></label><?php $this->after(); ?><?php

        return ob_get_clean();
    }
}