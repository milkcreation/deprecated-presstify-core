<?php
/**
 * @name Select
 * @desc Liste de selection
 * @package presstiFy
 * @namespace tiFy\Core\Field\Select
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\Select;

class Select extends \tiFy\Core\Field\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ
     *
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string|array $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var bool $multiple Activation de la liste de selection multiple
     *      @var array $options Liste de selection d'éléments
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
            'before'   => '',
            'after'    => '',
            'attrs'    => [],
            'name'     => '',
            'value'    => null,
            'multiple' => false,
            'options'  => []
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyCoreField-select--' . $this->getIndex();
        endif;
        $args['attrs']['type'] = 'text';
        if ($args['multiple']) :
            array_push($args['attrs'], 'multiple');
        endif;

        return $args;
    }

    /**
     * Traitement de l'attribut de configuration de la qualification de soumission du champ "name"
     *
     * @param array $args Liste des attributs de configuration
     *
     * @return array
     */
    final protected function parseAttrName($args = [])
    {
        if (isset($args['name'])) :
            $args['attrs']['name'] = !empty($args['multiple']) ? "{$args['name']}[]" : $args['name'];
        endif;

        return $args;
    }

    /**
     * Récupération de l'attribut de configuration de la valeur initiale de soumission du champ "value"
     *
     * @return mixed
     */
    final protected function getValue()
    {
        $value = $this->getAttr('value', null);
        if (is_null($value)) :
            return;
        endif;

        if (!is_array($value)) :
            $value = array_map('trim', explode(',', (string)$value));
        endif;

        // Suppression des doublons
        $value = array_unique($value);

        if (!$this->getAttr('multiple')) :
            $value = [reset($value)];
        endif;

        return $value;
    }

    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ
     *
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string|array $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var bool $multiple Activation de la liste de selection multiple
     *      @var array $options Liste de selection d'éléments
     * }
     *
     * @return string
     */
    protected function display($args = [])
    {
?><?php $this->before(); ?>
    <select <?php $this->htmlAttrs(); ?>>
        <?php $this->tagOptions(); ?>
    </select>
<?php $this->after(); ?><?php
    }
}