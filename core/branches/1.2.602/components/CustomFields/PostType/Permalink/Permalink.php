<?php

namespace tiFy\Components\CustomFields\PostType\Permalink;

use tiFy\Core\Meta\Post as MetaPost;
use tiFy\Core\Control\Control;

class Permalink extends \tiFy\App
{
	/**
     * Type de post courant
     * @var string
     */
	private $PostType = '';

    /**
     * Liste de choix de permaliens
     * @var array
     */
	private static $Permalinks	= [];

    /**
     * CONSTRUCTEUR
     *
     * @param string $post_type Identifiant de qualification du type de post
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var array $permalinks {
     *          Liste de choix de permaliens
     *      }
     * }
     *
     * @return void
     */
	public function __construct($post_type, $attrs = [])
	{
		parent::__construct();

		// Définition du type de post
		$this->PostType = $post_type;

		// Définition de la liste de choix des permaliens
        if (isset($attrs['permalinks'])) :
            foreach ($attrs['permalinks'] as $permalink_id => $permalink_attrs) :
                self::register($permalink_id, $permalink_attrs);
            endforeach;
        endif;

        // Déclaration des événements
        $this->appAddAction('current_screen');
        $this->appAddAction('admin_enqueue_scripts');
        $this->appAddAction('wp_loaded');
        $this->appAddAction('wp_ajax_tiFyComponentsCustomFieldsPostTypePermalink', 'wp_ajax');
	}

    /**
     * EVENEMENTS
     */
    /**
     * Chargement de la page courante de l'interface d'adiministration
     *
     * @return void
     */
    final public function current_screen($current_screen)
    {
        if ($current_screen->id !== $this->PostType) :
            return;
        endif;

        MetaPost::register($this->PostType, '_permalink', true, 'esc_attr');

        $this->appAddAction('edit_form_top');
        $this->appAddFilter('get_sample_permalink_html', 'get_sample_permalink_html', 10, 5);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    final public function admin_enqueue_scripts()
    {
        Control::enqueue_scripts('Dropdown');
        Control::enqueue_scripts('Findposts');
        Control::enqueue_scripts('Suggest');
        \wp_enqueue_style('tiFyComponents-customFields-post_type_permalink',
            self::tFyAppUrl(get_class()) . '/Permalink.css',
            [],
            160526
        );
        \wp_enqueue_script(
            'tiFyComponents-customFields-post_type_permalink',
            self::tFyAppUrl() . '/Permalink.js',
            ['jquery'],
            160526,
            true
        );
    }

    /**
     * A l'issue du chargement complet de Wordpress
     */
    final public function wp_loaded()
    {
        // Action de déclaration d'une liste de choix personnalisée de choix
        do_action_ref_array('tify_permalink_register', [$this]);
        do_action_ref_array('tify_permalink_register_post_type_' . $this->PostType, [$this]);

        // Court-circuitage du permalien vers un contenu
        if ($this->PostType == 'page') :
            //apply_filters( 'page_link', $link, $post->ID, $sample );
            $this->appAddFilter('page_link', 'permalink', 10, 3);

        elseif ($this->PostType == 'attachment') :
            //apply_filters( 'attachment_link', $link, $post->ID );
            $this->appAddFilter('attachment_link', 'permalink', 10, 2);

        elseif (in_array($this->PostType, get_post_types(['_builtin' => false]))) :
            //apply_filters( 'post_type_link', $post_link, $post, $leavename, $sample );
            $this->appAddFilter('post_type_link', 'permalink', 10, 4);

        else :
            //apply_filters( 'post_link', $permalink, $post, $leavename );
            $this->appAddFilter('post_link', 'permalink', 10, 3);
        endif;
    }

    /**
     * Affichage d'un message d'avertissement de l'interface d'administration d'un post; lorsque celui-ci fait usage d'un lien est personnalisé
     *
     * @param \WP_Post $post Object Post Wordpress
     *
     * @return string
     */
    final public function edit_form_top($post)
    {
        if (!get_post_meta($post->ID, '_permalink', true)) :
            return;
        endif;

        echo
            "<div id=\"tiFyComponentsCustomFieldsPostTypePermalink-notice\" class=\"notice notice-info inline\">\n" .
            "\t<p>" . __('Le permalien qui mène à ce contenu fait appel à un lien personnalisé.', 'tify') . "</p>\n" .
            "</div>";
    }

    /**
     * Interface d'édition d'un permalien dans l'interface d'édition d'un post depuis l'administration
     *
     * @param string $output Rendu de l'interface
     * @param int $post_id Identifiant de qualification d'un post
     * @param $new_title
     * @param $new_slug
     * @param \WP_Post $post Objet post Wordpress
     *
     * @return string
     */
    final public function get_sample_permalink_html( $output, $post_id, $new_title, $new_slug, $post )
    {
        $_permalink = get_post_meta($post_id, '_permalink', true);

        $output .= "<section id=\"tiFyComponentsCustomFieldsPostTypePermalink\">\n";
        $output .= "\t<input id=\"tiFyComponentsCustomFieldsPostTypePermalink-active\" type=\"checkbox\" autocomplete=\"off\"/>";
        $output .= "\t<input id=\"tiFyComponentsCustomFieldsPostTypePermalink-selected\" type=\"hidden\" name=\"tify_meta_post[_permalink]\" value=\"{$_permalink}\"/>\n";
        $output .= "\t<label for=\"tiFyComponentsCustomFieldsPostTypePermalink-active\">\n";
        $output .= __('Personnalisation du lien', 'tify');
        $output .= "\t</label>\n";
        $output .= "\t&nbsp;&nbsp;<a href=\"#\" id=\"tiFyComponentsCustomFieldsPostTypePermalink-cancel\" data-post_permalink=\"\" style=\"" . (!empty($_permalink) ? 'display:inline;' : 'display:none;') . "\">" . __('Annuler',
                'tify') . "</a>";
        $output .= "\t<div id=\"tiFyComponentsCustomFieldsPostTypePermalink-selectors\">\n";

        // Interface des permaliens prédéfini
        if (static::$Permalinks) :
            $output .= "\t\t<section>";
            $output .= "\t\t\t<h4>- " . __('Choisir parmi une liste de liens prédéfinis :', 'tify') . "</h4>";

            /// Formatage des permaliens prédéfini
            $permalinks = [];
            foreach ((array)static::$Permalinks as $id => $attrs) :
                $permalinks[$id] = $attrs['title'];
            endforeach;
            $output .= Control::Dropdown(
                [
                    'id'                => 'tiFyComponentsCustomFieldsPostTypePermalink-dropdown',
                    'selected'          => $_permalink,
                    'choices'           => $permalinks,
                    'option_none_value' => '',
                    'picker'            => [
                        'id' => 'tiFyComponentsCustomFieldsPostTypePermalink-dropdown-picker',
                    ],
                ]
            );
            $output .= "\t\t</section>";
        endif;

        ///
        $output .= "\t\t<section>\n";
        $output .= "\t\t\t<h4>- " . __('Recherher parmi les contenus du site :', 'tify') . "</h4>\n";
        $output .= Control::Suggest(
            [
                'id'       => 'tiFyComponentsCustomFieldsPostTypePermalink-suggest',
                'elements' => ['title', 'permalink', 'type', 'status', 'id']
            ]
        );
        $output .= "\t\t</section>\n";

        ///
        $output .= "\t\t<section>\n";
        $output .= "\t\t\t<h4>- " . __('Saisir un lien personnalisé :', 'tify') . "</h4>\n";
        $output .= "\t\t\t<div id=\"tiFyComponentsCustomFieldsPostTypePermalink-custom\">\n";
        $output .= "\t\t\t\t<input type=\"text\" value=\"\" placeholder=\"" . __('Saisir l\'url du site',
                'tify') . "\"/>\n";
        $output .= "\t\t\t\t<a href=\"#\">" . __('Valider', 'tify') . "</a>";
        $output .= "\t\t\t</div>\n";
        $output .= "\t\t</section>\n";

        $output .= "\t</div>\n";
        $output .= "</section>\n";

        return $output;
    }

    /**
     * Court-circuitage d'un permalien de post
     *
     * @param string $permalink Valeur originale du permalien
     * @param \WP_Post $post Objet post Wordpress
     *
     * @return string
     */
    final public function permalink( $permalink, $post )
    {
        if (!$post = get_post($post)) :
            return $permalink;
        endif;

        if (!$_permalink = get_post_meta($post->ID, '_permalink', true)) :
            return $permalink;
        endif;

        if (preg_match('/^key:(.*)/', $_permalink, $match) && isset(self::$Permalinks[$match[1]]['url'])) :
            $permalink = self::$Permalinks[$match[1]]['url'];

        elseif (preg_match('/^post_id:(\d*)/', $_permalink, $match) && ($permalink_post = get_post((int)$match[1]))) :
            if ($permalink_post->ID !== $post->ID) :
                $permalink = get_permalink($permalink_post);
            endif;

        elseif (!preg_match('/^http/', $_permalink)) :
            $permalink = site_url() . '/' . ltrim($_permalink, '/');

        else :
            $permalink = $_permalink;

        endif;

        return $permalink;
    }

    /**
     * Action Ajax de définition du permalien personnalisé
     *
     * @return \wp_send_json
     */
    final public function wp_ajax()
    {
        $data = [];

        if (isset($_POST['key']) && isset(self::$Permalinks[$_POST['key']]['url'])) :
            $data['url'] = self::$Permalinks[$_POST['key']]['url'];
            $data['selected'] = 'key:' . $_POST['key'];

        elseif (isset($_POST['post_id']) && ($permalink = get_permalink((int)$_POST['post_id']))) :
            $data['url'] = $permalink;
            $data['selected'] = 'post_id:' . $_POST['post_id'];

        elseif (isset($_POST['url'])) :
            $data['url'] = (!preg_match('/^http/', $_POST['url']))
                ? site_url() . '/' . ltrim($_POST['url'], '/')
                : $_POST['url'];

            $data['selected'] = $_POST['url'];

        elseif (isset($_POST['cancel'])) :
            \delete_post_meta($_POST['cancel'], '_permalink');

            $this->appAddFilter('get_sample_permalink_html', 'get_sample_permalink_html', 10, 5);
            $data['url'] = \get_sample_permalink_html((int)$_POST['cancel']);

        endif;

        wp_send_json($data);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un permalien disponible dans la liste de choix globale
     *
     * @param $id Identifiant de qualification unique du permalien
     * @param array $attrs {
     *      Liste des attributs de configuration du permalien
     *
     *      @var string $title Intitulé de qualification du permalien
     *      @var string $url Url du permalien
     * }
     *
     * @return array
     */
    public static function register($id, $attrs = [])
    {
        if (is_string($attrs)) :
            $attrs = [
                'title' => $id,
                'url'   => $attrs
            ];
        endif;

        static::$Permalinks[sanitize_key($id)] = array_merge(
            [
                'title' => '',
                'url'   => '',
            ],
            $attrs
        );
    }
}