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

class ScrollPaginate extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'scroll_paginate';

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
            'wp_ajax_tify_control_scroll_paginate',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tify_control_scroll_paginate',
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
    public static function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tify_control-repeater',
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
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('ify_control-scroll_paginate');
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

        wp_send_json('tata');


        exit;
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
     *
     *      @param string $before_item Chaine d'ouverture d'encapsulation d'un élément
     *      @param string $after_item Chaine de fermeture d'encapsulation d'un élément
     *      @param string $item_cb Methode ou fonction de rappel d'affichage d'un élément
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected static function display($attrs = [], $echo = true)
    {
        global $wp_query;

        // Traitement des arguments
        $defaults = [
            'id'                => 'tiFyCoreControl-ScrollPaginate-' . self::$Instance,
            'container_id'      => 'tiFyCoreControl-ScrollPaginate--' . self::$Instance,
            'container_class'   => '',
            'text'              => __('Voir plus', 'tify'),
            'ajax_action'       => 'tify_control_scroll_paginate',
            'ajax_nonce'        => wp_create_nonce('tiFyControl-ScrollPaginate'),
            'query_args'        => $wp_query->query_vars,
            'per_page'          => \get_query_var('posts_per_page', get_option('posts_per_page', 10)),
            'target'            => '',
            'before_item'       => '<li>',
            'after_item'        => '</li>',
            'query_cb'          => __CLASS__ . '::query',
            'item_cb'           => __CLASS__ . '::item'
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $output  = "";
        $output .= "<a href=\"#{$attrs['container_id']}\"";
        $output .= " id=\"{$attrs['container_id']}\"";
        $output .= " class=\"tiFyCoreControl-ScrollPaginate" . ($attrs['container_class'] ? " {$attrs['container_class']}" : "") . "\"";
        $output .= " data-options=\"" . rawurlencode(json_encode($attrs)) . "\">";
        $output .= $attrs['text'];
        $output .= "</a>";

        // Mise en file du script dynamique
        \add_action(
            (is_admin() ? 'admin_footer' : 'wp_footer'),
            function () use ($attrs)
            {
            ?><script type="text/javascript">/* <![CDATA[ */
                var tify_scroll_paginate_xhr;
                jQuery(document).ready(function ($) {
                    var handler = '#<?php echo $attrs['container_id']; ?>', target = '<?php echo $attrs['target'];?>';
                    tify_scroll_paginate(handler, target);
                });
                /* ]]> */</script><?php
            },
            99
        );

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }

    /**
     * Récupération de la liste des éléments
     *
     * @return array|\WP_Query
     */
    public static function query($attrs = [])
    {
        $defaults = [

        ];

        $posts_per_page = (!empty($query_args['posts_per_page'])) ? $query_args['posts_per_page'] : $per_page;
        if (!isset($query_args['post_status'])) {
            $query_args['post_status'] = 'publish';
        }
        $query_post = new \WP_Query($query_args);
        $is_complete = ((int)$query_post->found_posts <= $posts_per_page) ? 'ty_iscroll_complete' : '';

        // Récupération des arguments
        $query_args = $_POST['query_args'];
        $before = stripslashes(html_entity_decode($_POST['before']));
        $after = stripslashes(html_entity_decode($_POST['after']));
        $template = $_POST['template'];

        // Traitement des arguments
        parse_str($_POST['query_args'], $query_args);
        $query_args['posts_per_page'] = (!empty($query_args['posts_per_page'])) ? $query_args['posts_per_page'] : $_POST['per_page'];
        $query_args['paged'] = ceil($_POST['from'] / $query_args['posts_per_page']) + 1;
        if (!isset($query_args['post_status'])) {
            $query_args['post_status'] = 'publish';
        }

        // Requête
        $query_post = new \WP_Query;
        $posts = $query_post->query($query_args);

        $output = "";
        if ($query_post->found_posts) :
            while ($query_post->have_posts()) : $query_post->the_post();
                $output .= $before;
                ob_start();
                get_template_part($template);
                $output .= ob_get_clean();
                $output .= $after;
            endwhile;
            if ($query_post->max_num_pages == $query_args['paged']) :
                $output .= "<!-- tiFy_Infinite_Scroll_End -->";
            endif;
        else :
            $output .= "<!-- tiFy_Infinite_Scroll_End -->";
        endif;

        echo $output;
    }
}