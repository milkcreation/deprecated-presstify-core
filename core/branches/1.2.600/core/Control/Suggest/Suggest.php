<?php
/**
 * @name Suggest
 * @desc Controleur d'affichage de champ de recherche par autocomplétion
 * @package presstiFy
 * @namespace tiFy\Core\Control\Suggest
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Suggest;

/**
 * @Overrideable \App\Core\Control\Suggest\Suggest
 *
 * <?php
 * namespace \App\Core\Control\Suggest
 *
 * class Suggest extends \tiFy\Core\Control\Suggest\Suggest
 * {
 *
 * }
 */

class Suggest extends \tiFy\Core\Control\Factory
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des Actions Ajax
        $this->tFyAppAddAction(
            'wp_ajax_tify_control_suggest_term',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tify_control_suggest_term',
            'wp_ajax'
        );
    }

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
        // Déclaration des scripts
        \wp_register_style(
            'tify_control-suggest',
            self::tFyAppAssetsUrl('Suggest.css', get_class()),
            ['tiFyTheme'],
            160222
        );
        \wp_register_script(
            'tify_control-suggest',
            self::tFyAppAssetsUrl('Suggest.js', get_class()),
            ['jquery-ui-autocomplete'],
            160222,
            true
        );
        \wp_localize_script(
            'tify_control-suggest',
            'tiFyControlSuggest',
            [
                'noResultsFound' => __('Aucun resultat trouvé', 'tify')
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
        \wp_enqueue_style('tify_control-suggest');
        \wp_enqueue_script('tify_control-suggest');
    }

    /**
     * Récupération de la liste des résultats via Ajax
     *
     * @return \wp_send_json
     */
    public function wp_ajax()
    {
        check_ajax_referer('tiFyControlSuggest');

        // Arguments par defaut à passer en $_POST
        $args = [
            /// Chaîne de caractère des termes recherchés
            'term'       => '',
            /// Arguments de requête WP_Query
            'query_args' => [],
            // Liste d'éléments affichés par le rendu des résultats
            'elements'   => [],
            /// Données complémentaires traitées par la requête (optionel)
            'extras'     => []
        ];
        extract($args);

        // Traitement des arguments de requête
        if (isset($_POST['term'])) :
            $term = $_POST['term'];
        endif;

        if (!empty($_POST['elements'])) :
            $elements = (array)$_POST['elements'];
        endif;

        if (isset($_POST['query_args'])) :
            $query_args = (array)$_POST['query_args'];
        endif;

        if (!isset($query_args['posts_per_page'])) :
            $query_args['posts_per_page'] = -1;
        endif;

        if (!isset($query_args['post_type'])) :
            $query_args['post_type'] = 'any';
        endif;

        $query_args['s'] = $term;

        // Récupération des posts
        $query_post = new \WP_Query;
        $posts = $query_post->query($query_args);

        // Valeur de retour par défaut
        $response = [];
        while ($query_post->have_posts()) : $query_post->the_post();
            // Données requises
            $label = get_the_title();
            $value = get_the_ID();

            foreach ($elements as $el) :
                if (method_exists($this, 'item_value_' . $el)) :
                    ${$el} = call_user_func([$this, 'item_value_' . $el]);
                else :
                    ${$el} = null;
                endif;
            endforeach;

            // Génération du rendu
            $render = call_user_func([$this, 'itemRender'], compact($elements));

            // Valeur de retour
            $response[] = compact('label', 'value', 'render', $elements);
        endwhile;
        wp_reset_query();

        wp_send_json($response);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs
     *
     * @return string
     */
    protected function display($args = [])
    {

        // Traitement des attributs de configuration
        $defaults = [
            // Identification du controleur
            'id'              => 'tiFyControlSuggest--' . $this->getId(),
            // Id Html du conteneur
            'container_id'    => 'tiFyControlSuggest--' . $this->getId(),
            // Classe Html du conteneur
            'container_class' => '',
            // Nom de la variable de requête du champ de recherche
            'name'            => 'tiFyControlSuggest-' . $this->getId(),
            // Valeur du champs de recherche
            'value'           => '',
            // Texte de remplacement du champ de recherche
            'placeholder'     => __('Votre recherche', 'tify'),
            'readonly'        => false,

            'attrs'  => [],
            'before' => '',
            'after'  => '',

            'select' => false,

            'button_text'        => '',
            'delete_button_text' => '',

            // Options Autocomplete
            /// @see http://api.jqueryui.com/autocomplete/
            /// Mettre la valeur à 'container' pour accrocher la liste de résultat au conteneur
            'options'            => [],
            // Classe de la liste de selection    
            'picker'             => '',

            // Action de récupération via ajax
            'ajax_action'        => 'tify_control_suggest_term',
            // Agent de sécurisation de la requête ajax
            'ajax_nonce'         => wp_create_nonce('tiFyControlSuggest'),
            /// Arguments de requête WP_Query
            'query_args'         => [],
            /// Liste d'éléments affichés par le rendu des résultats
            'elements'           => ['title', 'permalink' /*'id', 'thumbnail', 'ico', 'type', 'status'*/],
            /// Données complémentaires traitées par la requête (optionel)
            'extras'             => []
        ];
        $args = wp_parse_args($args, $defaults);
        extract($args);

        // Traitement des arguments
        /// Liste de selection
        if (!$picker) {
            $picker = 'tiFyControlSuggest-picker--' . $id;
        }

        /// Options du plugin autocomplete
        $options = wp_parse_args(
            $options,
            [
                'minLength' => 2
            ]
        );
        $options['appendTo'] = (isset($options['appendTo']) && ($options['appendTo'] === 'container')) ? '#tiFyControlSuggest-response--' . $id : 'body';

        /// Boutons et indicateur de chargement
        $search_before = '<button type="button" class="tiFyControlSuggest-button tiFyControlSuggest-button--search">';
        $search_after = '</button>';

        if (!$button_text) :
            $button_text = $search_before . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve" fill="#000000"><g><rect x="20.2" y="28.4" transform="matrix(0.7071 0.7071 -0.7071 0.7071 30.809 -12.7615)" width="21.3" height="4.7"/><path d="M4.6,4.6c-6.1,6.1-6.1,15.9,0,22s15.9,6.1,22,0s6.1-15.9,0-22S10.6-1.5,4.6,4.6z M23.2,23.4   c-4.2,4.2-11.1,4.2-15.3,0s-4.2-11.1,0-15.3s11.1-4.2,15.3,0S27.4,19.2,23.2,23.4z"/></g></svg>' . $search_after;
        else :
            $button_text = $search_before . $button_text . $search_after;
        endif;

        if ($value) :
            $container_class .= ' tiFyControlSuggest--selected';
            $readonly = true;
        endif;
        $delete_button_before = '<button type="button" class="tiFyControlSuggest-button tiFyControlSuggest-button--delete">';
        $delete_button_after = '</button>';
        if (!$delete_button_text) :
            $delete_button_text = $delete_button_before . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 357 357"><polygon points="312.13 71.6 285.4 44.88 178.5 151.78 71.6 44.88 44.88 71.6 151.78 178.5 44.88 285.4 71.6 312.13 178.5 205.22 285.4 312.13 312.13 285.4 205.22 178.5 312.13 71.6"/></svg>' . $delete_button_after;
        else :
            $delete_button_text = $delete_button_before . $delete_button_text . $delete_button_after;
        endif;
        $button_text .= $delete_button_text;

        // Liste des arguments pour le traitement de la requête Ajax
        $ajax_attrs = compact('ajax_action', 'ajax_nonce', 'query_args', 'elements', 'extras', 'select', 'options',
            'picker');

        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlSuggest" . ($container_class ? ' ' . $container_class : '') . "\"";
        $output .= "data-tify_control=\"suggest\" data-attrs=\"" . htmlentities(json_encode($ajax_attrs)) . "\"";
        foreach ((array)$attrs as $k => $v) {
            $output .= " {$k}=\"{$v}\"";
        }
        $output .= ">\n";
        $output .= $before;
        $output .= "\t<input type=\"text\" class=\"tiFyControlSuggest-textInput\" placeholder=\"{$placeholder}\" autocomplete=\"off\"" . ($readonly ? ' readonly' : '') . " value=\"" . (($select && !is_bool($select)) ? $select : $value) . "\">\n";
        $output .= "\t<input type=\"hidden\" class=\"tiFyControlSuggest-altInput\" name=\"{$name}\" value=\"{$value}\">";
        $output .= $button_text;
        $output .= $after;

        $output .= "\t<div class=\"tify_spinner\"><span></span></div>\n";
        $output .= "\t<div id=\"tiFyControlSuggest-response--" . $id . "\" class=\"tiFyControlSuggest-response\"></div>\n";
        $output .= "</div>\n";

        echo $output;
    }

    /**
     * Rendu de l'autocomplete
     */
    final public function itemRender($attrs = [])
    {
        $output = "";
        foreach ($attrs as $attr => $value) :
            if (method_exists($this, 'item_render_' . $attr)) :
                $output .= call_user_func([$this, 'item_render_' . $attr], $value, $attrs);
            else :
                $output .= "<span class=\"tiFyControlSuggest-pickerItemAttr tiFyControlSuggest-pickerItemAttr--{$attr}\">{$value}</span>";
            endif;
        endforeach;

        if (!isset($attrs['permalink'])) :
            $output .= $this->item_render_permalink('', $attrs);
        endif;

        return $output;
    }

    /**
     * Valeur de l'attribut - ID
     */
    public function item_value_id()
    {
        return get_the_ID();
    }

    /**
     * Valeur de l'attribut - Titre
     */
    public function item_value_title()
    {
        return get_the_title();
    }

    /**
     * Valeur de l'attribut - Permalien
     */
    public function item_value_permalink()
    {
        return get_the_permalink();
    }

    /**
     * Valeur de l'attribut - Image à la une
     */
    public function item_value_thumbnail()
    {
        return get_the_post_thumbnail(null, 'thumbnail', false);
    }

    /**
     * Valeur de l'attribut - Icône representative
     */
    public function item_value_ico()
    {
        return get_the_post_thumbnail(null, [50, 50], false);
    }

    /**
     * Valeur de l'attribut - Type de post
     */
    public function item_value_type()
    {
        return get_post_type_object(get_post_type())->label;
    }

    /**
     * Valeur de l'attribut - Status de publication
     */
    public function item_value_status()
    {
        return get_post_status_object(get_post_status())->label;
    }

    /**
     * Rendu de l'attribut - Permalien
     */
    public function item_render_permalink($value, $attrs)
    {
        return "<a href=\"" . ($value ? $value : '#' . $attrs['id']) . "\" class=\"tiFyControlSuggest-pickerItemAttr tiFyControlSuggest-pickerItemAttr--permalink\"></a>";
    }
}