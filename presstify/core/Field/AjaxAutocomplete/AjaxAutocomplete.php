<?php
/**
 * @name AjaxAutocomplete
 * @desc Selecteur d'éléments via un champ de saisie avec autocompletion
 * @see http://api.jqueryui.com/autocomplete/
 * @package presstiFy
 * @namespace tiFy\Core\Field\AjaxAutocomplete
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\AjaxAutocomplete;

use tiFy\Core\Control\Control;
use tiFy\Core\Field\Field;

class AjaxAutocomplete extends \tiFy\Core\Field\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        // Déclaration des Actions Ajax
        $this->tFyAppAddAction(
            'wp_ajax_tify_field_ajax_autocomplete',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tify_field_ajax_autocomplete',
            'wp_ajax'
        );

        // Déclaration des scripts
        \wp_register_style(
            'tiFyCoreFieldAjaxAutocomplete',
            self::tFyAppAssetsUrl('AjaxAutocomplete.css', get_class()),
            ['tify-select'],
            171224
        );
        \wp_register_script(
            'tiFyCoreFieldAjaxAutocomplete',
            self::tFyAppAssetsUrl('AjaxAutocomplete.js', get_class()),
            ['tify-select'],
            171224,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyCoreFieldAjaxAutocomplete');
        \wp_enqueue_script('tiFyCoreFieldAjaxAutocomplete');
    }

    /**
     * Récupération des résultats via Ajax
     *
     * @return \wp_send_json
     */
    public function wp_ajax()
    {
        check_ajax_referer('tiFyCoreField-ajaxAutocomplete');

        // Récupération des arguments de requête
        $query_args = self::tFyAppGetRequestVar('query_args', [], 'POST');
        $args = self::tFyAppGetRequestVar('args', [], 'POST');
        $args = \wp_unslash($args);

        // Définition des arguments de requête par défaut
        if (!isset($query_args['post_type'])) :
            $query_args['post_type'] = 'any';
        endif;

        $query_args['paged'] = self::tFyAppGetRequestVar('page', 1, 'POST');
        $query_args['fields'] = 'ids';
        $query_args['s'] = self::tFyAppGetRequestVar('term', '', 'POST');

        // Récupération des identifiants de post
        if (!$post_ids = (new \WP_Query)->query($query_args)) :
            $items = [];
        elseif ($items = $this->getSelectItems($post_ids, $args)) :
        endif;

        wp_send_json($items);
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
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var mixed $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var array $source Liste des attributs de requête de récupération des éléments
     *      @var string $select_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de des éléments selectionnés
     *      @var string $picker_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de selection
     *      @var bool $disabled Activation/Désactivation du controleur de champ
     *      @var bool $multiple Autorise la selection multiple d'éléments
     *      @var bool $duplicate Autorise les doublons dans la liste de selection (multiple actif doit être actif)
     *      @var int $max Nombre d'élément maximum
     *      @var array $autocomplete {
     *          Liste des options du contrôleur ajax d'autocompletion
     *          @see http://api.jqueryui.com/autocomplete
     *      }
     *      @var array $sortable {
     *          Liste des options du contrôleur ajax d'ordonnancement
     *          @see http://jqueryui.com/sortable/
     *      }
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
            'container_id'    => 'tiFyCoreField-ajaxAutocomplete--' . $this->getId(),
            'container_class' => '',
            'attrs'           => [],
            'name'            => '',
            'value'           => '',
            'source'          => [],
            'select_cb'  => [__CLASS__, 'selectRender'],
            'picker_cb'  => [__CLASS__, 'pickerRender'],
            'disabled'        => false,
            'multiple'        => false,
            'duplicate'       => false,
            'sortable'        => true,
            'max'             => -1,
            'trigger'         => [],
            'picker'          => []

        ];
        $args = array_merge($defaults, $args);

        // Attributs de configuration du conteneur
        if (!empty($args['container_class'])) :
            $args['container_class'] = 'tiFy-select tiFyCoreField-ajaxAutocomplete ' . $args['container_class'];
        else :
            $args['container_class'] = 'tiFy-select tiFyCoreField-ajaxAutocomplete';
        endif;

        // Attributs de configuration de la liste de selection
        $args['handler_args'] = [];
        $args['handler_args']['attrs'] = [
            'id'        => 'tiFyCoreField-ajaxAutocompleteHandler--' . $this->getId(),
            'class'     => 'tiFy-selectHandler tiFyCoreField-ajaxAutocompleteHandler'
        ];
        $args['handler_args']['name'] = $args['name'];
        $args['handler_args']['value'] = $args['value'];
        $args['handler_args']['multiple'] = $args['multiple'];

        // Attributs de configuration du controleur Ajax
        // Sortable
        if ($args['sortable']) :
            if ($args['sortable'] === true) :
                $args['sortable'] = [];
            endif;
        endif;

        // Formatage des options du controleur Ajax
        $args['data-options'] = rawurlencode(
            json_encode(
                [
                    'disabled'      => $args['disabled'],
                    'multiple'      => $args['multiple'],
                    'duplicate'     => $args['duplicate'],
                    'max'           => $args['max'],
                    'sortable'      => $args['sortable'],
                    'trigger'    => array_merge(
                        [
                            'arrow' => false
                        ],
                        $args['trigger']
                    ),
                    'picker'    => array_merge(
                        [
                            'adminbar' => (is_admin() && (!defined('DOING_AJAX') || (DOING_AJAX !== true))) ?  false : true
                        ],
                        $args['picker']
                    ),
                    'source'        => array_merge(
                        [
                            'action'         => 'tify_field_ajax_autocomplete',
                            '_ajax_nonce'    => \wp_create_nonce('tiFyCoreField-ajaxAutocomplete'),
                            'query_args'     => [],
                            'args'           => [
                                'select_cb'    => $args['select_cb'],
                                'picker_cb'    => $args['picker_cb']
                            ]
                        ],
                        $args['source']
                    )
                ],
                JSON_FORCE_OBJECT
            )
        );

        return $args;
    }

    /**
     * Récupération de l'attribut de configuration de la valeur initiale de soumission du champ "value"
     *
     * @return array
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
        if (!$this->getAttr('duplicate')) :
            $value = array_unique($value);
        endif;
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
     *      @var string $label Initulé de qualification
     *      @var bool $group
     *      @var string $parent
     * }
     * @param array $args Attributs de configuration du controleur d'affichage du champ
     *
     * @return string
     */
    final protected static function selectRender($item, $args = [])
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
     *      @var string $label Initulé de qualification
     *      @var bool $group
     *      @var string $parent
     * }
     * @param array $args Attributs de configuration du controleur d'affichage du champ
     *
     * @return string
     */
    final protected static function pickerRender($item, $args = [])
    {
        if (isset($item['picker'])) :
            return $item['picker'];
        endif;

        return $item['label'];
    }

    /**
     * Récupération de la liste des éléments selectionnés
     *
     * @param array $selected Liste des valeurs selectionnées
     * @param array $args Attributs de configuration du controleur d'affichage du champ
     *
     * @return array
     */
    public function getSelectItems($selected, $args = [])
    {
        $items = [];

        if (!is_null($selected)) :
            $index = 0;

            // Récupération des élements depuis la liste de seelection
            $query_items = new \WP_Query(['post_type' => 'any', 'post__in' => $selected, 'posts_per_page' => -1]);

            while ($query_items->have_posts()) : $query_items->the_post();
                $item = [];

                $item['index'] = $index++;
                $item['label'] = get_the_title();
                $item['value'] = get_the_ID();

                // Formatage du rendu de l'élément
                if (!empty($args['select_cb']) && is_callable($args['select_cb'])) :
                    $item['select'] = call_user_func_array($args['select_cb'], compact('item', 'args'));
                endif;
                if (!isset($item['select'])) :
                    $item['select'] = $item['label'];
                endif;

                if (!empty($args['picker_cb']) && is_callable($args['picker_cb'])) :
                    $item['picker'] = call_user_func_array($args['picker_cb'], compact('item', 'args'));
                endif;
                if (!isset($item['picker'])) :
                    $item['picker'] = $item['label'];
                endif;

                $items[] = $item;
            endwhile;

            wp_reset_query();
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
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var mixed $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var array $source Liste des attributs de requête de récupération des éléments
     *      @var string $select_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de des éléments selectionnés
     *      @var string $picker_cb Classe ou méthode ou fonction de rappel d'affichage d'un élément dans la liste de selection
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
?><?php $this->before(); ?>
    <div
        id="<?php echo $this->getAttr('container_id'); ?>"
        class="<?php echo $this->getAttr('container_class'); ?>"
        data-options="<?php echo $this->getAttr('data-options'); ?>"
    >
        <?php Field::Select($this->getAttr('handler_args'), true); ?>

        <div id="tiFyCoreField-ajaxAutocompleteResponse--<?php echo $this->getId(); ?>" class="tiFy-selectResponse tiFyCoreField-ajaxAutocompleteResponse"></div>

        <div id="tiFyCoreField-ajaxAutocompleteTrigger--<?php echo $this->getId();?>" class="tiFy-selectTrigger tiFyCoreField-ajaxAutocompleteTrigger">
            <ul id="tiFyCoreField-ajaxAutocompleteSelectedItems--<?php echo $this->getId(); ?>" class="tiFy-selectSelectedItems tiFyCoreField-ajaxAutocompleteSelectedItems">
            <?php if ($items = $this->getSelectItems($this->getValue(), $this->getAttrList())) : ?>
                <?php foreach($items as $item) :?>
                <li data-label="<?php echo $item['label']; ?>"
                    data-value="<?php echo $item['value']; ?>"
                    data-index="<?php echo $item['index']; ?>"
                >
                    <?php echo $item['select'];?>
                </li>
                <?php endforeach;?>
            <?php endif; ?>
            </ul>
        </div>

        <div id="tiFyCoreField-ajaxAutocompletePicker--<?php echo $this->getId(); ?>"  class="tiFy-selectPicker tiFyCoreField-ajaxAutocompletePicker">
            <ul id="tiFyCoreField-ajaxAutocompletePickerItems--<?php echo $this->getId(); ?>"  class="tiFy-selectPickerItems tiFyCoreField-ajaxAutocompletePickerItems">
            <?php if ($items = $this->getSelectItems($this->getValue(), $this->getAttrList())) : ?>
                <?php foreach($items as $item) :?>
                    <li
                        data-label="<?php echo $item['label']; ?>"
                        data-value="<?php echo $item['value']; ?>"
                        data-index="<?php echo $item['index']; ?>"
                    >
                        <?php echo $item['picker'];?>
                    </li>
                <?php endforeach;?>
            <?php endif; ?>
            </ul>

            <div id="tiFyCoreField-ajaxAutocompletePickerLoader--<?php echo $this->getId(); ?>" class="tiFy-selectPickerLoader tiFyCoreField-ajaxAutocompletePickerLoader">
                <?php
                Control::Spinkit(
                    [
                        'container_id'    => 'tiFyCoreField-ajaxAutocompletePickerSpinkit--' . $this->getId(),
                        'container_class' => 'tiFy-selectPickerSpinkit tiFyCoreField-ajaxAutocompletePickerSpinkit',
                        'type'            => 'three-bounce',
                    ]
                );
                ?>
            </div>
        </div>
    </div>
<?php $this->after(); ?><?php
    }
}