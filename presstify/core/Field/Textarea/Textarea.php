<?php
/**
 * @name Textarea
 * @desc Zone de texte de saisie libre
 * @package presstiFy
 * @namespace tiFy\Core\Field\Textarea
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\Textarea;

class Textarea extends \tiFy\Core\Field\Factory
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
            'before' => '',
            'after'  => '',
            'attrs'  => [],
            'name'   => '',
            'value'  => ''
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyCoreField-textarea--' . $this->getIndex();
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
    protected function parseAttrValue($args = [])
    {
        if (isset($args['value'])) :
            $args['content'] = $args['value'];
        endif;

        return $args;
    }

    /**
     * Affichage
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
     *
     * @return string
     */
    protected function display($args = [])
    {
?><?php $this->before(); ?><textarea <?php $this->htmlAttrs(); ?>><?php $this->tagContent(); ?></textarea><?php $this->after(); ?><?php
    }
}