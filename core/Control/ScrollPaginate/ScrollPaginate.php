<?php
/**
 * @name ScrollPaginate
 * @desc Controleur d'affichage d'un controleur de pagination au scroll
 * @package presstiFy
 * @namespace tiFy\Core\Control\ScrollPaginate
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 * @see http://tobiasahlin.com/spinkit/
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\ScrollPaginate;

use Symfony\Component\HttpFoundation\Request;

/**
 * @Overrideable \App\Core\Control\ScrollPaginate\ScrollPaginate
 *
 * <?php
 * namespace \App\Core\Control\ScrollPaginate
 *
 * class ScrollPaginate extends \tiFy\Core\Control\ScrollPaginate\ScrollPaginate
 * {
 *
 * }
 */

class ScrollPaginate extends \tiFy\Core\Control\Factory
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
            'wp_ajax_tify_control_scroll_paginate',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tify_control_scroll_paginate',
            'wp_ajax'
        );

        // Déclaration des scripts
        \wp_register_style(
            'tify_control-scroll_paginate',
            self::tFyAppAssetsUrl('ScrollPaginate.css', get_class()),
            [],
            171204
        );
        \wp_register_script(
            'tify_control-scroll_paginate',
            self::tFyAppAssetsUrl('ScrollPaginate.js', get_class()),
            ['jquery'],
            171204,
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
        \wp_enqueue_style('tify_control-scroll_paginate');
        \wp_enqueue_script('tify_control-scroll_paginate');
    }

    /**
     * Récupération des éléments via Ajax
     *
     * @return string
     */
    public static function wp_ajax()
    {
        check_ajax_referer('tiFyControl-ScrollPaginate');

        if ($options = self::tFyAppGetRequestVar('options', false, 'POST')) :
            $options = wp_unslash($options);
        endif;
        $offset = self::tFyAppGetRequestVar('offset', 0, 'POST');

        $response = \call_user_func_array($options['query_items_cb'], compact('options', 'offset'));

        wp_send_json($response);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @param string $id Identifiant de qualification du controleur
     *      @param string $container_id ID HTML du controleur d'affichage
     *      @param string $container_class Classe HTML du controleur d'affichage
     *      @param string $text Texte du controleur d'affichage
     *      @param string $ajax_action Action Ajax de récupération des éléments
     *      @param string $ajax_nonce Chaîne de sécurisation de l'action Ajax
     *      @param array $query_args Argument de requête de récupération des éléments
     *      @param array $per_page Nombre d'éléments par passe de récupération
     *      @param string $target Identifiant de qualification du selecteur du DOM d'affichage de la liste des éléments
     *      @param string $before_item Chaine d'ouverture d'encapsulation d'un élément
     *      @param string $after_item Chaine de fermeture d'encapsulation d'un élément
     *      @param string $query_items_cb Methode ou fonction de rappel de récupération de la liste des éléments
     *      @param string $item_display_cb Methode ou fonction de rappel d'affichage d'un élément
     * }
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        global $wp_query;

        // Traitement des arguments
        $defaults = [
            'id'                => 'tiFyCoreControl-ScrollPaginate-' . $this->getId(),
            'container_id'      => 'tiFyCoreControl-ScrollPaginate--' . $this->getId(),
            'container_class'   => '',
            'text'              => __('Voir plus', 'tify'),
            'ajax_action'       => 'tify_control_scroll_paginate',
            'ajax_nonce'        => wp_create_nonce('tiFyControl-ScrollPaginate'),
            'query_args'        => $wp_query->query_vars,
            'per_page'          => 0,
            'target'            => '',
            'before_item'       => '<li>',
            'after_item'        => '</li>',
            'query_items_cb'    => get_called_class() . "::queryItems",
            'item_display_cb'   => get_called_class() . "::itemDisplay"
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        /**
         * @var string $id Identifiant de qualification du controleur
         * @var string $container_id ID HTML du controleur d'affichage
         * @var string $container_class Classe HTML du controleur d'affichage
         * @var string $text Texte du controleur d'affichage
         * @var string $ajax_action Action Ajax de récupération des éléments
         * @var string $ajax_nonce Chaîne de sécurisation de l'action Ajax
         * @var array $query_args Argument de requête de récupération des éléments
         * @var array $per_page Nombre d'éléments par passe de récupération
         * @var string $target Identifiant de qualification du selecteur du DOM d'affichage de la liste des éléments
         * @var string $before_item Chaine d'ouverture d'encapsulation d'un élément
         * @var string $after_item Chaine de fermeture d'encapsulation d'un élément
         * @var string $query_items_cb Methode ou fonction de rappel de récupération de la liste des éléments
         * @var string $item_cb Methode ou fonction de rappel d'affichage d'un élément
         */
        extract($attrs);

        // Formatage des arguments de requete
        $query_args = isset($query_args) ? _http_build_query($query_args) : '';

        $output  = "";
        $output .= "<a href=\"#{$container_id}\"";
        $output .= " id=\"{$container_id}\"";
        $output .= " class=\"tiFyCoreControl-ScrollPaginate" . ($attrs['container_class'] ? " {$attrs['container_class']}" : "") . "\"";
        $output .= " data-options=\"" . rawurlencode(json_encode(compact(array_keys($defaults)))) . "\">";
        $output .= $text;
        $output .= "</a>";

        // Mise en file du script dynamique
        \add_action(
            (is_admin() ? 'admin_footer' : 'wp_footer'),
            function () use ($attrs)
            {
            ?><script type="text/javascript">/* <![CDATA[ */
                jQuery(document).ready(function ($) {
                    var handler = '#<?php echo $attrs['container_id']; ?>', target = '<?php echo $attrs['target'];?>';
                    tify_scroll_paginate(handler, target);
                });
                /* ]]> */</script><?php
            },
            99
        );

        echo $output;
    }

    /**
     * Récupération de la liste des éléments
     *
     * @return string
     */
    final public static function queryItems($options = [], $offset = 0)
    {
        parse_str($options['query_args'], $query_args);

        // Définition du nombre d'éléments par passe
        if (!$options['per_page']) :
            if(!empty($query_args['posts_per_page'])) :
                $per_page = $query_args['posts_per_page'];
            elseif(!$per_page = get_option('posts_per_page', 10)) :
                $per_page = 10;
            endif;
        else :
            $per_page = $options['per_page'];
        endif;

        // Traitement des arguments de requête WP_Query
        // Définition du statut par défaut
        if (empty($query_args['post_status'])) :
            $query_args['post_status'] = 'publish';
        endif;

        // Définition de post par page
        $query_args['posts_per_page'] = $per_page;

        // Définition de l'élément à partir duquel récupérer la liste des élements suivant
        $query_args['offset'] = $offset;

        // Lancement de la requête de récupération WP_Query
        $query_post = new \WP_Query;
        $posts = $query_post->query($query_args);
        $total = (int)$query_post->found_posts;
        $complete = (($offset+$per_page) >= $total);

        // Génération de l'affichage
        $html = "";
        if ($query_post->found_posts) :
            while ($query_post->have_posts()) : $query_post->the_post();
                $html .= $options['before_item'];
                ob_start();
                call_user_func_array($options['item_display_cb'], [$options, $offset, $query_post->current_post]);
                $html .= ob_get_clean();
                $html .= $options['after_item'];
            endwhile;

            wp_reset_query();
        endif;

        return compact('html', 'complete');
    }

    /**
     * Affichage d'un élément
     *
     * @return string
     */
    final public static function itemDisplay($options = [], $offset = 0, $index = 0)
    {
        return self::tFyAppGetTemplatePart('item', $options['id'], compact('options', 'offset', 'index'));
    }
}
