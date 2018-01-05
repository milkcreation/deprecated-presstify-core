<?php
/**
 * @name Label
 * @desc Libelé de champ
 * @package presstiFy
 * @namespace tiFy\Core\Field\Label
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\Label;

class Label extends \tiFy\Core\Field\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @var string $before Contenu placé avant le champ
     *      @var string $after Contenu placé après le champ
     *      @var string $content Contenu de la balise champ
     *      @var array $attrs Liste des propriétés de la balise HTML
     * }
     *
     * @return array
     */
    final protected function parseAttrs($args = [])
    {
        // Pré-traitement des attributs de configuration
        $args = parent::parseAttrs($args);

        // Traitement des attributs de configuration
        $defaults = [
            'before'       => '',
            'after'        => '',
            'content'      => '',
            'attrs'        => []
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyCoreField-label--' . $this->getIndex();
        endif;

        return $args;
    }

    /**
     * Affichage
     *
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @var string $before Contenu placé avant le champ
     *      @var string $after Contenu placé après le champ
     *      @var string $content Contenu de la balise champ
     *      @var array $attrs Liste des propriétés de la balise HTML
     * }
     *
     * @return string
     */
    protected function display($args = [])
    {
?><?php $this->before(); ?><label <?php $this->htmlAttrs(); ?>/><?php $this->tagContent(); ?></label><?php $this->after(); ?><?php
    }
}