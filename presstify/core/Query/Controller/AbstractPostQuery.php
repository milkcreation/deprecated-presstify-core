<?php

namespace tiFy\Core\Query\Controller;

use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractPostQuery
{
    use TraitsApp;

    /**
     * Type de post Wordpress du controleur
     * @var string|array
     */
    protected $objectName = 'any';

    /**
     * Controleur de données d'un élément
     * @var string
     */
    protected $itemController = 'tiFy\Core\Query\Controller\PostItem';

    /**
     * Controleur de données d'une liste d'éléments
     * @var string
     */
    protected $listController = 'tiFy\Core\Query\Controller\PostList';

    /**
     * Récupération du(es) type(s) de post Wordpress du controleur
     *
     * @return string|array
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Récupération du controleur de données d'un élément
     *
     * @return string
     */
    public function getItemController()
    {
        return $this->itemController;
    }

    /**
     * Récupération du controleur de données d'une liste d'éléments
     *
     * @return string
     */
    public function getListController()
    {
        return $this->listController;
    }

    /**
     * Récupération d'un élément
     *
     * @param string|int|\WP_Post|null $id Nom de qualification du post WP (slug, post_name)|Identifiant de qualification du post WP|Object post WP|Post WP  de la page courante
     *
     * @return null|object|PostItemInterface
     */
    public function get($id = null)
    {
        if (is_string($id)) :
            return self::getBy('name', $id);
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

        if (($post->post_type !== 'any') && !in_array($post->post_type, (array) $this->getObjectName())) :
            return null;
        endif;

        $name = 'tify.query.post.' . $post->ID;
        if (! $this->appHasContainer($name)) :
            $controller = $this->getItemController();
            $this->appAddContainer($name, new $controller($post));
        endif;

        return $this->appGetContainer($name);
    }

    /**
     * Récupération d'un élément selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut name.
     * @param string $value Valeur de l'attribut
     *
     * @return null|object|PostItemInterface
     */
    public function getBy($key = 'name', $value)
    {
        $args = [
            'post_type'      => 'any',
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
            return $this->get(reset($posts));
        endif;

        return null;
    }

    /**
     * Récupération des données d'une liste d'élément selon des critères de requête
     *
     * @param array $query_args Liste des arguments de requête
     *
     * @return array|PostItemInterface[]
     */
    public function getList($query_args = [])
    {
        if (!$query_args['post_type'] = $this->objectName) :
            return [];
        endif;

        if (!isset($query_args['posts_per_page'])) :
            $query_args['posts_per_page'] = -1;
        endif;

        $wp_query = new \WP_Query;
        $posts = $wp_query->query($query_args);

        if ($posts) :
            $items =  array_map([$this, 'get'], $posts);
        else :
            $items = [];
        endif;

        $controller = $this->getListController();

        return new $controller($items);
    }
}

