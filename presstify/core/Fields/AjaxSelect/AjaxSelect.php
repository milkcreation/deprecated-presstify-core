<?php
/**
 * @name AjaxSelect
 * @desc Champ de selection Ajax
 * @package presstiFy
 * @namespace tiFy\Core\Fields\AjaxSelect
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Fields\AjaxSelect;

use tiFy\Core\Fields\Fields;

class AjaxSelect extends \tiFy\Core\Fields\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {
        \wp_register_style(
            'tiFyCoreFieldsAjaxSelect',
            self::tFyAppAssetsUrl('AjaxSelect.css', get_class()),
            [],
            171218
        );
        \wp_register_script(
            'tiFyCoreFieldsAjaxSelect',
            self::tFyAppAssetsUrl('AjaxSelect.js', get_class()),
            ['jquery-ui-widget'],
            171218,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyCoreFieldsAjaxSelect');
        \wp_enqueue_script('tiFyCoreFieldsAjaxSelect');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    public static function display($id = null, $args = [])
    {
        self::$Instance++;

        // Traitement des attributs de configuration
        $defaults = [
            // Conteneur
            'name'              => '',
            'container_id'      => 'tiFyCoreFields-ajaxSelect-' . self::$Instance,
            'container_class'   => '',
            'attrs'             => [
                'id'                => 'tiFyCoreFields-ajaxSelectHandler--' . self::$Instance,
            ],

            // Valeur
            'selected'          => null,
            'options'           => [],
            'disabled'          => false,
            'multiple'          => false
        ];
        $args = wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);

        // Récupération des attributs de configuration
        $options = $field->getAttr('options', []);
        $selected = $field->getAttr('selected');

        // Pré-Traitement des arguments de configuration de la liste de gestion du traitement
        // Classe d'identification
        if (isset($args['attrs']['class'])) :
            $args['attrs']['class'] .= ' tiFyCoreFields-ajaxSelectHandler';
        else :
            $args['attrs']['class'] = 'tiFyCoreFields-ajaxSelectHandler';
        endif;

        // Nettoyage de l'affichage des options
        $args['options'] = array_map('strip_tags', $options);

    ?><?php $field->before(); ?>
        <div
            id="<?php echo $field->getAttr('container_id'); ?>"
            class="tiFyCoreFields-ajaxSelect<?php echo ($class = $field->getAttr('container_class')) ? " {$class}" : ''; ?>"
            data-options="<?php echo rawurlencode(json_encode(['disabled' => $field->getAttr('disabled', false)])); ?>"
            data-value="<?php echo $selected; ?>"
        >

            <?php Fields::Select($args); ?>

            <div class="tiFyCoreFields-ajaxSelectController">
                <?php echo (isset($options[$selected]))? $options[$selected] : reset($options); ?>
            </div>

            <ul class="tiFyCoreFields-ajaxSelectPicker">
            <?php foreach ($field->getAttr('options') as $value => $label) : ?>
                <li class="tiFyCoreFields-ajaxSelectOption"><?php echo $label; ?></li>
            <?php endforeach; ?>
            </ul>

        </div>
        <?php $field->after(); ?><?php
    }
}