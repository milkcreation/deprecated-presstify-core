<?php
/**
 * @name SelectJs
 * @desc Liste de selection enrichie
 * @package presstiFy
 * @namespace tiFy\Core\Field\SelectJs
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\SelectJs;

use tiFy\Core\Field\Field;

class SelectJs extends \tiFy\Core\Field\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final protected function init()
    {
        \wp_register_style(
            'tiFyCoreFieldSelectJs',
            self::tFyAppAssetsUrl('SelectJs.css', get_class()),
            ['tify-select'],
            171218
        );
        \wp_register_script(
            'tiFyCoreFieldSelectJs',
            self::tFyAppAssetsUrl('SelectJs.js', get_class()),
            ['tify-select'],
            171218,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final protected function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyCoreFieldSelectJs');
        \wp_enqueue_script('tiFyCoreFieldSelectJs');
    }

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
     *      @var string $container_id Id HTML du conteneur du champ
     *      @var string $container_class Classe HTML du conteneur du champ
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string|array $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var array $options Liste des choix de selection disponibles
     *      @var null|string $select_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de des éléments selectionnés
     *      @var null|string $picker_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de selection
     *      @var bool $disabled Activation/Désactivation du controleur de champ
     *      @var bool $multiple Autorise la selection multiple d'éléments
     *      @var bool $duplicate Autorise les doublons dans la liste de selection (multiple actif doit être actif)
     *      @var int $max Nombre d'élément maximum
     *      @var array $sortable {
     *          Liste des options du contrôleur ajax d'ordonnancement
     *          @see http://jqueryui.com/sortable/
     *      }
     *      @var bool|array $filter Activation/Attributs de filtrage de la liste de selection
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
            'before'          => '',
            'after'           => '',
            'container_id'    => 'tiFyCoreField-selectJs--' . $this->getId(),
            'container_class' => '',
            'name'            => '',
            'value'           => null,
            'options'         => [],
            'select_cb'       => [$this, 'selectRender'],
            'picker_cb'       => [$this, 'pickerRender'],
            'disabled'        => false,
            'multiple'        => false,
            'duplicate'       => false,
            'sortable'        => true,
            'max'             => -1,
            'picker_class'    => '',
            'picker'          => []
        ];
        $args = array_merge($defaults, $args);

        // Attributs de configuration du controleur
        if (!empty($args['container_class'])) :
            $args['container_class'] = 'tiFy-select tiFyCoreField-selectJs ' . $args['container_class'];
        else :
            $args['container_class'] = 'tiFy-select tiFyCoreField-selectJs';
        endif;

        // Attributs du selecteur de gestion de traitement
        $args['handler_args'] = [
            'name'     => $args['name'],
            'value'    => $args['value'],
            'disabled' => $args['disabled'],
            'multiple' => $args['multiple'],
            'attrs'    => []
        ];
        $args['handler_args']['attrs']['id'] = 'tiFyCoreField-selectJsHandler--' . $this->getId();
        $args['handler_args']['attrs']['class'] = 'tiFy-selectHandler tiFyCoreField-selectJsHandler';

        // Attributs de configuration du controleur Ajax
        // Sortable
        if ($args['sortable']) :
            if ($args['sortable'] === true) :
                $args['sortable'] = [];
            endif;
        endif;

        // Attributs de configuration des options du controleur Js
        $args['data-options'] = rawurlencode(
            json_encode(
                [
                    'disabled'  => $args['disabled'],
                    'multiple'  => $args['multiple'],
                    'duplicate' => $args['duplicate'],
                    'max'       => $args['max'],
                    'sortable'  => $args['sortable'],
                    'picker'    => array_merge(
                        [
                            'adminbar' => (is_admin() && (!defined('DOING_AJAX') || (DOING_AJAX !== true))) ?  false : true
                        ],
                        $args['picker']
                    ),
                    'source'    => false
                ],
                JSON_FORCE_OBJECT
            )
        );

        // Formatage de la liste des choix de selection disponibles
        foreach ($args['options'] as &$item) :
            if ($args['select_cb'] && is_callable($args['select_cb'])) :
                $item['select'] = call_user_func_array($args['select_cb'], compact('item', 'args'));
            endif;
            if (!isset($item['select'])) :
                $item['select'] = $item['label'];
            endif;

            if ($args['picker_cb'] && is_callable($args['picker_cb'])) :
                $item['picker'] = call_user_func_array($args['picker_cb'], compact('item', 'args'));
            endif;
            if (!isset($item['picker'])) :
                $item['picker'] = $item['label'];
            endif;
        endforeach;

        return $args;
    }

    /**
     * Récupération de la liste des valeurs initiales de soumission du champ "value"
     *
     * @return array
     */
    final protected function getValue()
    {
        $value = $this->getAttr('value', null);
        if (is_null($value)) :
            return;
        endif;

        // Formatage de la liste des valeur
        if (!is_array($value)) :
            $value = array_map('trim', explode(',', $value));
        endif;

        // Suppression des doublons
        $value = array_unique($value);

        if (!$this->getAttr('multiple')) :
            $value = [reset($value)];
        endif;

        return $value;
    }

    /**
     * Formatage de l'affichage de l'élément dans la liste des éléments selectionnés
     *
     * @param array $item {
     *      Attributs de configuration de l'élément
     *
     *      @var string $id Identifiant de qualification de l'élément
     *      @var mixed $value Valeur de retour
     *      @var string $label Intitulé de qualification
     *      @var bool $group
     *      @var string $parent
     * }
     * @param array $args Attributs de configuration du controleur d'affichage du champ
     *
     * @return string
     */
    public function selectRender($item, $args = [])
    {
        if (isset($item['select'])) :
            return $item['select'];
        endif;

        return $item['label'];
    }

    /**
     * Formatage de l'affichage de l'élément dans la liste de selection des éléments
     *
     * @param array $item {
     *      Attributs de configuration de l'élément
     *
     *      @var string $id Identifiant de qualification de l'élément
     *      @var mixed $value Valeur de retour
     *      @var string $label Intitulé de qualification
     *      @var bool $group
     *      @var string $parent
     * }
     * @param array $args Attributs de configuration du controleur d'affichage du champ
     *
     * @return string
     */
    public function pickerRender($item, $args = [])
    {
        if (isset($item['picker'])) :
            return $item['picker'];
        endif;

        return $item['label'];
    }

    /**
     * Récupération de la liste des éléments selectionnés à l'initialisation
     *
     * @param array $selected Liste des valeurs selectionnées
     *
     * @return array
     */
    public function getSelectItems($selected)
    {
        $items = [];

        if (!is_null($selected)) :
            $index = 0;
            foreach ($selected as $v) :
                if (!$item = $this->getOption($v)) :
                    continue;
                endif;

                $item['index'] = $index++;

                $items[] = $item;
            endforeach;
        endif;

        return $items;
    }

    /**
     * Affichage
     *
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @var string $before Contenu placé avant le champ
     *      @var string $after Contenu placé après le champ
     *      @var string $container_id Id HTML du conteneur du champ
     *      @var string $container_class Classe HTML du conteneur du champ
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string|array $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var array $options Liste des choix de selection disponibles
     *      @var null|string $select_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de des éléments selectionnés
     *      @var null|string $picker_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de selection
     *      @var bool $disabled Activation/Désactivation du controleur de champ
     *      @var bool $multiple Autorise la selection multiple d'éléments
     *      @var bool $duplicate Autorise les doublons dans la liste de selection (multiple actif doit être actif)
     *      @var int $max Nombre d'élément maximum
     *      @var array $sortable {
     *          Liste des options du contrôleur ajax d'ordonnancement
     *          @see http://jqueryui.com/sortable/
     *      }
     * }
     *
     * @return string
     */
    protected function display($args = [])
    {
        // Récupération des attributs de configuration
        $options = $this->getAttr('options', []);
        $value = $this->getValue();

?><?php $this->before(); ?>
    <div
        id="<?php echo $this->getAttr('container_id'); ?>"
        class="<?php echo $this->getAttr('container_class'); ?>"
        data-options="<?php echo $this->getAttr('data-options'); ?>"
    >
        <?php Field::Select($this->getAttr('handler_args'), true); ?>

        <div id="tiFyCoreField-selectJsResponse--<?php echo $this->getId(); ?>" class="tiFy-selectResponse tiFyCoreField-selectJsResponse"></div>

        <div id="tiFyCoreField-selectJsTrigger--<?php echo $this->getId(); ?>" class="tiFy-selectTrigger tiFyCoreField-selectJsTrigger">
            <ul id="tiFyCoreField-selectJsSelectedItems--<?php echo $this->getId(); ?>" class="tiFy-selectSelectedItems tiFyCoreField-selectJsSelectedItems">
            <?php if ($items = $this->getSelectItems($this->getValue())) : ?>
                <?php foreach($items as $item) :?>
                <li
                    data-label="<?php echo $item['label']; ?>"
                    data-value="<?php echo $item['value']; ?>"
                    data-index="<?php echo $item['index']; ?>"
                >
                    <?php echo $item['select'];?>
                </li>
                <?php endforeach;?>
            <?php endif; ?>
            </ul>
        </div>

        <div id="tiFyCoreField-selectJsPicker--<?php echo $this->getId(); ?>" class="tiFy-selectPicker tiFyCoreField-selectJsPicker <?php echo $this->getAttr('picker_class'); ?>">
            <ul id="tiFyCoreField-selectJsPickerItems--<?php echo $this->getId(); ?>" class="tiFy-selectPickerItems tiFyCoreField-selectJsPickerItems">
            <?php if ($items = $this->getOptionList()) : ?>
                <?php foreach($items as $item) :?>
                <li
                    data-label="<?php echo $item['label']; ?>"
                    data-value="<?php echo $item['value']; ?>"
                    data-select="<?php echo $item['select']; ?>"
                    <?php if (in_array('disabled', $item['attrs'])) : ?>
                    aria-disabled="true"
                    <?php endif; ?>
                >
                    <?php echo $item['picker'];?>
                </li
                    data-label="<?php echo $item['label']; ?>">
                <?php endforeach;?>
            <?php endif; ?>
            </ul>
        </div>
    </div>
<?php $this->after(); ?><?php
    }
}