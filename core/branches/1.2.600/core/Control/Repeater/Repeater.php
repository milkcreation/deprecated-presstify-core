<?php
/**
 * @name Repeater
 * @desc Controleur d'affichage de jeux de champs de formulaire pouvant être ajoutés dynamiquement
 * @package presstiFy
 * @namespace tiFy\Core\Control\Repeater
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Repeater;

/**
 * @Overrideable \App\Core\Control\Repeater\Repeater
 *
 * <?php
 * namespace \App\Core\Control\Repeater
 *
 * class Repeater extends \tiFy\Core\Control\Repeater\Repeater
 * {
 *
 * }
 */

class Repeater extends \tiFy\Core\Control\Factory
{
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        // Déclaration des Actions Ajax
        $this->tFyAppAddAction(
            'wp_ajax_tify_control_repeater_item',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tify_control_repeater_item',
            'wp_ajax'
        );

        // Déclaration des scripts
        \wp_register_style(
            'tify_control-repeater',
            self::tFyAppAssetsUrl('Repeater.css', get_class()),
            [],
            170421
        );
        \wp_register_script(
            'tify_control-repeater',
            self::tFyAppAssetsUrl('Repeater.js', get_class()),
            ['jquery', 'jquery-ui-sortable'],
            170421,
            true
        );
        \wp_localize_script(
            'tify_control-repeater',
            'tiFyControlRepeater',
            [
                'maxAttempt' => __('Nombre de valeur maximum atteinte', 'tify')
            ]
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-repeater');
        \wp_enqueue_script('tify_control-repeater');
    }

    /**
     * Récupération des champs via Ajax
     *
     * @return string
     */
    public function wp_ajax()
    {
        check_ajax_referer('tiFyControlRepeater');

        $index = self::parseIndex($_POST['index']);
        $value = $_POST['value'];
        $attrs = $_POST['attrs'];

        ob_start();
        if (!empty($_POST['attrs']['item_cb'])) :
            call_user_func(\wp_unslash($_POST['attrs']['item_cb']), $index, $value, $attrs);
        else :
            static::item($index, $value, $attrs);
        endif;
        $item = ob_get_clean();

        echo self::itemWrap($item, $index, $value, $attrs);

        wp_die();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            // Id Html du conteneur
            'id'               => 'tiFyControlRepeater--' . $this->getId(),
            // Classe Html du conteneur
            'class'            => '',
            // Nom de la valeur a enregistrer
            'name'             => 'tiFyControlRepeater-' . $this->getId(),
            // Valeur string | array indexé de liste des valeurs
            'value'            => '',
            // Valeur par défaut string | array à une dimension
            'default'          => '',
            // Action de récupération via ajax
            'ajax_action'      => 'tify_control_repeater_item',
            // Agent de sécurisation de la requête ajax
            'ajax_nonce'       => wp_create_nonce('tiFyControlRepeater'),
            // Fonction de rappel d'affichage d'un élément (méthode statique ou fonction)
            'item_cb'          => '',
            // Intitulé du bouton d'ajout d'une interface d'édition
            'add_button_txt'   => __('Ajouter', 'tify'),
            // Classe du bouton d'ajout d'une interface d'édition
            'add_button_class' => 'button-secondary',
            // Nombre maximum de valeur pouvant être ajoutées
            'max'              => -1,
            // Ordonnacemment des éléments
            'order'            => true
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

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
                $i = self::parseIndex($i);

                $v = (!is_array($v)) ? ($v ? $v : $default) : wp_parse_args($v, (array)$default);
                ob_start();
                $parsed_attrs['item_cb']
                    ? call_user_func($parsed_attrs['item_cb'], $i, $v, $parsed_attrs)
                    : self::item($i, $v, $parsed_attrs);
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

        echo $output;
    }

    /**
     * Génération d'un indice aléatoire
     *
     * @return string
     */
    public static function parseIndex($index)
    {
        if (!is_numeric($index)) :
            return $index;
        endif;

        return uniqid();
    }

    /**
     * Champs d'édition d'un élément
     *
     * @return string
     */
    public static function item($index, $value, $attrs = [])
    {
        ?><input type="text" name="<?php echo $attrs['name']; ?>[<?php echo $index; ?>]" value="<?php echo $value; ?>" class="widefat"/><?php
    }

    /**
     * Encapsulation Html d'un élément
     *
     * @return string
     */
    public static function itemWrap($item, $index, $value, $attrs)
    {
        $output = "";
        $output .= "\t\t<li class=\"tiFyControlRepeater-Item\" data-index=\"{$index}\">\n";
        $output .= $item;
        $output .= "\t\t\t<a href=\"#\" class=\"tiFyControlRepeater-ItemRemove tify_button_remove\"></a>";
        if ($attrs['order']) :
            $output .= "\t\t\t<input type=\"hidden\" name=\"{$attrs['order']}[]\" value=\"{$index}\"/>\n";
        endif;
        $output .= "\t\t</li>\n";

        return $output;
    }
}