<?php
/**
 * @name Button
 * @desc Bouton d'action
 * @package presstiFy
 * @namespace tiFy\Core\Field\Button
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\Button;

class Button extends \tiFy\Core\Field\Factory
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
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var string $content Contenu de la balise HTML
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
            'before'  => '',
            'after'   => '',
            'attrs'   => [],
            'name'    => '',
            'value'   => '',
            'content' => __('Envoyer', 'tify')
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyCoreField-button--' . $this->getIndex();
        endif;

        if (!isset($args['attrs']['type'])) :
            $args['attrs']['type'] = 'button';
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
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var string $content Contenu de la balise HTML
     * }
     *
     * @return string
     */
    protected function display($args = [])
    {
?><?php $this->before(); ?><button <?php $this->htmlAttrs(); ?>><?php $this->tagContent(); ?></button><?php $this->after(); ?><?php
    }
}