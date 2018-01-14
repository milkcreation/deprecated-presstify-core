<?php

namespace tiFy\Components\InfiniteScroll;

class InfiniteScroll extends \tiFy\App
{
    /**
     * Nombre d'instance d'appel
     * @var int
     */
    // Instances
    static $Instance = 0;

    /**
     * Définition des attributs de configuration par instance d'appel
     * @var array
     */
    static $Config = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des fonctions d'aide à la saisie
        $this->appAddHelper('tify_infinite_scroll_display', 'display');

        // Définition des événements
        $this->appAddAction('wp_enqueue_scripts');
        $this->appAddAction('wp_ajax_tify_infinite_scroll', 'wp_ajax');
        $this->appAddAction('wp_ajax_nopriv_tify_infinite_scroll', 'wp_ajax');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Mise en file des scripts de l'interface utilisateur
     *
     * @return void
     */
    public function wp_enqueue_scripts()
    {
        wp_enqueue_script(
            'tiFyComponents-infiniteScroll',
            self::tFyAppUrl(get_class()) . '/InfiniteScroll.js',
            ['jquery'],
            170328,
            true
        );
    }

    /**
     * Chargement des éléments via Ajax
     *
     * @return string
     */
    public function wp_ajax()
    {
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
                ob_start();
                get_template_part($template);
                $output .= $before . ob_get_contents() . $after;
                ob_end_clean();
            endwhile;
            if ($query_post->max_num_pages == $query_args['paged']) :
                $output .= "<!-- tiFy_Infinite_Scroll_End -->";
            endif;
        else :
            $output .= "<!-- tiFy_Infinite_Scroll_End -->";
        endif;

        echo $output;
        exit;
    }

    /**
     * Affichage
     *
     * @param array $args Attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    static function display($args = [], $echo = true)
    {
        global $wp_query;

        // Incrémentation de l'intance
        self::$Instance++;

        // Traitement des arguments
        $defaults = [
            'id'         => 'tify_infinite_scroll-' . self::$Instance,
            'label'      => __('Voir plus', 'tify'),
            'action'     => 'tify_infinite_scroll',
            'query_args' => $wp_query->query_vars,
            'target'     => '',
            'before'     => '<li>',
            'after'      => '</li>',
            'per_page'   => get_query_var('posts_per_page', get_option('posts_per_page', 10)),
            'template'   => 'content-archive',
        ];
        self::$Config[self::$Instance] = wp_parse_args($args, $defaults);
        extract(self::$Config[self::$Instance]);

        $posts_per_page = (!empty($query_args['posts_per_page'])) ? $query_args['posts_per_page'] : $per_page;
        if (!isset($query_args['post_status'])) {
            $query_args['post_status'] = 'publish';
        }
        $query_post = new \WP_Query($query_args);
        $is_complete = ((int)$query_post->found_posts <= $posts_per_page) ? 'ty_iscroll_complete' : '';

        $query_args = isset($query_args) ? _http_build_query($query_args) : '';

        // Caractères spéciaux
        $before = htmlentities($before);
        $after = htmlentities($after);

        $config = self::$Config[self::$Instance];
        $wp_footer = function () use ($config) {
            ?>
            <script type="text/javascript">/* <![CDATA[ */
                var tify_infinite_scroll_xhr;
                jQuery(document).ready(function ($) {
                    var handler = '#<?php echo $config['id'];?>', target = '<?php echo $config['target'];?>';
                    tify_infinite_scroll(handler, target);
                });
                /* ]]> */</script><?php
        };

        // Mise en file des scripts
        add_action('wp_footer', $wp_footer, 99);

        $output = "";
        $output .= "<a id=\"{$id}\" 
					   class=\"ty_iscroll $is_complete\" 
					   href=\"#tify_infinite_scroll-" . self::$Instance . "\" 
					   data-action=\"{$action}\" 
					   data-query_args=\"{$query_args}\" 
					   data-target=\"{$target}\" 
					   data-before=\"{$before}\"
					   data-after=\"{$after}\"
					   data-per_page=\"{$per_page}\" 
					   data-template=\"{$template}\">{$label}</a>";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}