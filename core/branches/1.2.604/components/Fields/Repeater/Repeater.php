<?php

/**
 * @name Repeater
 * @desc Répétiteur de champs
 * @package presstiFy
 * @namespace tiFy\Components\Fields\Repeater
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Fields\Repeater;

use tiFy\Core\Field\AbstractFactory;
use tiFy\Core\Field\Field;

/**
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
 */
class Repeater extends AbstractFactory
{
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des Actions Ajax
        $this->tFyAppAddAction(
            'wp_ajax_tify_field_repeater',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tify_field_repeater',
            'wp_ajax'
        );

        // Déclaration des scripts
        \wp_register_style(
            'tiFyFieldRepeater',
            self::tFyAppAssetsUrl('Repeater.css', get_class()),
            [],
            171224
        );
        \wp_register_script(
            'tiFyFieldRepeater',
            self::tFyAppAssetsUrl('Repeater.js', get_class()),
            ['jquery', 'jquery-ui-sortable'],
            171224,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyFieldRepeater');
        \wp_enqueue_script('tiFyFieldRepeater');
    }

    /**
     * Récupération des résultats via Ajax
     *
     * @return string
     */
    final public function wp_ajax()
    {
        check_ajax_referer('tiFyField-repeater');

        $index = $this->appRequestGet('index', [], 'POST');
        $value = $this->appRequestGet('value', [], 'POST');
        $args = $this->appRequestGet('args', [], 'POST');
        $args = \wp_unslash($args);

        ob_start();
        if (!empty($args['item_cb'])) :
            call_user_func(\wp_unslash($args['item_cb']), $index, $value, $args);
        else :
            $this->item($index, $value, $args);
        endif;
        $item = ob_get_clean();

        echo $this->itemWrap($item, $index, $value, $args);

        wp_die();
    }

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
            'before'          => '',
            'after'           => '',
            'container_id'    => 'tiFyField-repeater--' . $this->getId(),
            'container_class' => '',
            'attrs'           => [],
            'name'            => '',
            'value'           => '',
            // Valeur par défaut string | array à une dimension
            'default'          => '',
            // Action de récupération via ajax
            'ajax_action'      => 'tify_control_repeater_item',
            // Agent de sécurisation de la requête ajax
            'ajax_nonce'       => wp_create_nonce('tiFyControlRepeater'),
            // Fonction de rappel d'affichage d'un élément (méthode statique ou fonction)
            'item_cb'          => '',
            // Intitulé du bouton d'ajout d'une interface d'édition
            'button'       =>   [
                    'content' => __('Ajouter', 'tify'),
                    'attrs'   => [
                        'class' => 'tiFyField-repeaterTrigger button-secondary'
                    ]
            ],
            // Nombre maximum de valeur pouvant être ajoutées
            'max'              => -1,
            // Ordonnacemment des éléments
            'sortable'         => true
        ];
        $args = array_merge($defaults, $args);

        // Attributs de configuration du conteneur
        if (!empty($args['container_class'])) :
            $args['container_class'] = 'tiFyField-repeater ' . $args['container_class'];
        else :
            $args['container_class'] = 'tiFyField-repeater';
        endif;

        return $args;
    }

    /**
     * Champs d'édition d'un élément
     *
     * @return void
     */
    protected function item($index, $value, $args = [])
    {
        echo Field::Text(
            [
                'name'  => $args['name'][$index],
                'value' => $value,
                'attrs' => $args['attrs']
            ]
        );
    }

    /**
     * Affichage
     *
     * @return string
     */
    protected function display()
    {
        ob_start();
?><?php $this->before(); ?>
    <div
            id="<?php echo $this->get('container_id'); ?>"
            class="<?php echo $this->get('container_class'); ?>"
            data-options="<?php echo $this->get('data-options'); ?>"
    >
        <?php echo Field::Button($this->get('button')); ?>
    </div>
<?php $this->after(); ?><?php

        return ob_get_clean();
    /*
        // Traitement des attributs
        if ($order) :
            $order = '__order_' . $name;
        endif;
        $parsed_attrs = compact(array_keys($defaults));

        $output = "";
        $output .= "<div id=\"{$id}\" class=\"tiFyControlRepeater" . ($class ? " {$class}" : "") . "\" data-tify_control=\"repeater\">\n";

        // Liste d'éléments
        $output .= "\t<ul class=\"tiFyControlRepeater-Items" . ($order ? ' tiFyControlRepeater-Items--sortable' : '') . "\">";
        if (!empty($value)) :
            foreach ((array)$value as $i => $v) :
                $v = (!is_array($v)) ? ($v ? $v : $default) : wp_parse_args($v, (array)$default);
                ob_start();
                $parsed_attrs['item_cb'] ? call_user_func($parsed_attrs['item_cb'], $i, $v,
                    $parsed_attrs) : self::item($i, $v, $parsed_attrs);
                $item = ob_get_clean();

                $output .= self::itemWrap($item, $i, $v, $parsed_attrs);
            endforeach;
        endif;
        $output .= "\t</ul>\n";

        // Interface de contrôle
        $output .= "\t<div class=\"tiFyControlRepeater-Handlers\">\n";
        $output .= "\t\t<a href=\"#{$id}\" data-attrs=\"" . htmlentities(json_encode($parsed_attrs)) . "\" class=\"tiFyControlRepeater-Add" . ($add_button_class ? ' ' . $add_button_class : '') . "\">\n";
        $output .= $add_button_txt;
        $output .= "\t\t</a>\n";
        $output .= "\t</div>\n";

        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            echo $output;
        endif;

        return $output;*/
    }
}