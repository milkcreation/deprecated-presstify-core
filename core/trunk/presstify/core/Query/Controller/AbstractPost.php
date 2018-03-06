<?php

namespace tiFy\Core\Query\Controller;

use tiFy\App\Traits\App as TraitsApp;
use Illuminate\Support\Fluent;

abstract class AbstractPost extends Fluent implements PostInterface
{
    use TraitsApp;

    /**
     * Objet Post Wordpress
     * @var \WP_Post
     */
    protected $object;

    /**
     * Type d'objet Wordpress
     * @var string
     */
    private $objectType = 'post';

    /**
     * Controleur
     * @var string
     */
    const CONTROLLER = '';

    /**
     * Identifiant de qualification du type de post Wordpress relatif
     * @var array|string
     */
    const OBJECTNAME = '';

    /**
     * CONSTRUCTEUR
     *
     * @param \WP_Post $wp_post
     *
     * @return void
     */
    public function __construct(\WP_Post $wp_post)
    {
        $this->object = $wp_post;

        parent::__construct($this->object->to_array());
    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __clone()
    {

    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __wakeup()
    {

    }

    /**
     * Instanciation
     *
     * @param string|int|\WP_Post|null $id Nom de qualification du post WP (slug, post_name)|Identifiant de qualification du post WP|Object post WP|Post WP  de la page courante
     *
     * @return null|static|object
     */
    public static function make($id = null)
    {
        if (is_string($id)) :
            return self::by('name', $id);
        elseif (!$id) :
            $post = get_the_ID();
        else :
            $post = $id;
        endif;

        if (!$post = \get_post($post)) :
            return null;
        endif;

        if (!$post instanceof \WP_Post) :
            return null;
        endif;

        $objectName = static::getObjectName();
        if ($objectName !== 'any') :
            $post_types =  (array)$objectName;
            if (!in_array($post->post_type, $post_types)) :
                return null;
            endif;
        endif;

        $name = 'tify.query.post.' . $post->ID;
        if (self::tFyAppHasContainer($name)) :
            return self::tFyAppGetContainer($name);
        endif;

        $Instance = ($controller = static::getController()) ? new $controller($post): new static($post);
        self::tFyAppShareContainer($name, $Instance);

        return $Instance;
    }

    /**
     * Instanciation selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut name.
     * @param string $value Valeur de l'attribut
     *
     * @return null|static|object
     */
    public static function by($key = 'name', $value)
    {
        $args = [
            'post_type'      => static::getObjectName(),
            'posts_per_page' => 1
        ];

        switch ($key) :
            default :
            case 'post_name' :
            case 'name' :
                $args['name'] = $value;
                break;
        endswitch;

        $wp_query = new \WP_Query;
        $posts = $wp_query->query($args);
        if ($wp_query->found_posts) :
            return self::make(reset($posts));
        endif;

        return null;
    }

    /**
     * Récupération du controleur
     *
     * @return string
     */
    public static function getController()
    {
        return static::CONTROLLER;
    }

    /**
     * Récupération de l'identifiant de qualification du rôle de l'utilisateur Wordpress relatif
     *
     * @return string|array
     */
    public static function getObjectName()
    {
        return static::OBJECTNAME ? (array)static::OBJECTNAME : 'any';
    }

    /**
     * Récupération de l'object Post Wordpress associé
     *
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->object;
    }

    /**
     * Récupération de l'identifiant de qualification Wordpress du post
     * @return int
     */
    public function getId()
    {
        return (int)$this->get('ID', 0);
    }

    /**
     * Récupération de l'identifiant de qualification Wordpress (post_name)
     *
     * @return string
     */
    public function getSlug()
    {
        return (string)$this->get('post_name', '');
    }

    /**
     * Alias de récupération de l'identifiant de qualification Wordpress (post_name)
     *
     * @return string
     */
    public function getName()
    {
        return $this->getSlug();
    }

    /**
     * Récupération de l'identifiant unique de qualification global
     * @internal Ne devrait pas être utilisé en tant que lien
     * @see https://developer.wordpress.org/reference/functions/the_guid/
     *
     * @return string
     */
    public function getGuid()
    {
        return (string)$this->get('guid', '');
    }

    /**
     * Récupération de la date de création au format datetime
     *
     * @return bool $gmt Activation de la valeur basée sur le temps moyen de Greenwich
     *
     * @return string
     */
    public function getDate($gmt = false)
    {
        if ($gmt) :
            return (string)$this->get('post_date', '');
        else :
            return (string)$this->get('post_date_gmt', '');
        endif;
    }

    /**
     * Récupération de la date de la dernière modification au format datetime
     *
     * @return bool $gmt Activation de la valeur basée sur le temps moyen de Greenwich
     *
     * @return string
     */
    public function getModified($gmt = false)
    {
        if ($gmt) :
            return (string)$this->get('post_modified', '');
        else :
            return (string)$this->get('post_modified_gmt', '');
        endif;
    }

    /**
     * Récupération de l'identifiant de qualification de l'auteur original
     *
     * @return int
     */
    public function getAuthorId()
    {
        return (int)$this->get('post_author', 0);
    }

    /**
     * Récupération de l'identifiant de qualification du post parent relatif
     *
     * @return int
     */
    public function getParentId()
    {
        return (int)$this->get('post_parent', 0);
    }

    /**
     * Récupération du type de post
     *
     * @return string
     */
    public function getType()
    {
        return (string)$this->get('post_type', '');
    }

    /**
     * Récupération du statut de publication
     *
     * @return string
     */
    public function getStatus()
    {
        return (string)$this->get('post_status', '');
    }

    /**
     * Récupération de la valeur brute ou formatée de l'intitulé de qualification
     *
     * @param bool $raw Formatage de la valeur
     *
     * @return string
     */
    public function getTitle($raw = false)
    {
        $title = (string)$this->get('post_title', '');

        if ($raw) :
            return $title;
        else :
            return \apply_filters('the_title', $title, $this->getId());
        endif;
    }

    /**
     * Récupération de la valeur brute ou formatée de l'extrait
     *
     * @param bool $raw Formatage de la valeur
     *
     * @return string
     */
    public function getExcerpt($raw = false)
    {
        $excerpt = (string)$this->get('post_excerpt', '');

        if ($raw) :
            return $excerpt;
        else :
            return \apply_filters('get_the_excerpt', $excerpt, $this->getPost());
        endif;
    }

    /**
     * Récupération du contenu de description
     *
     * @param bool $raw Formatage de la valeur
     *
     * @return string
     */
    public function getContent($raw = false)
    {
        $content = (string)$this->get('post_content', '');

        if (!$raw) :
            $content = \apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);
        endif;

        return $content;
    }

    /**
     * Récupération du lien d'édition du post dans l'interface administrateur
     *
     * @return string
     */
    public function getEditLink()
    {
        return \get_edit_post_link($this->getId());
    }

    /**
     * Récupération du permalien d'affichage du post dans l'interface utilisateur
     *
     * @return string
     */
    public function getPermalink()
    {
        return \get_permalink($this->getId());
    }
}