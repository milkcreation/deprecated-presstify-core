<?php

namespace tiFy\Core\Query\Controller;

use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractTermQuery
{
    use TraitsApp;

    /**
     * Taxonomie Wordpress du controleur
     * @var string
     */
    protected $objectName = '';

    /**
     * Controleur de données d'un élément
     * @var string
     */
    protected $itemController = 'tiFy\Core\Query\Controller\TermItem';

    /**
     * Controleur de données d'une liste d'éléments
     * @var string
     */
    protected $listController = 'tiFy\Core\Query\Controller\TermList';

    /**
     * Récupération de la taxonomie Wordpress du controleur
     *
     * @return string
     */
    public function getObjectName()
    {
        return (string) $this->objectName;
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
     * @param string|int|\WP_Term|null $id Nom de qualification (slug)|Identifiant de term Wordpress|Objet terme Wordpress|Terme de ma page courante
     *
     * @return null|object|TermItemInterface
     */
    public function get($id = null)
    {
        if (!$id) :
            $term = get_queried_object();
        elseif (is_int($id)) :
            if ((!$term = \get_term($id)) || is_wp_error($term)) :
                return null;
            endif;
        elseif (is_string($id)) :
            return $this->getBy('slug', $id);
        else :
            $term = $id;
        endif;

        if (!$term instanceof \WP_Term) :
            return null;
        endif;

        if ($term->taxonomy !== $this->getObjectName()) :
            return null;
        endif;

        $name = 'tify.query.term.' . $term->term_id;
        if (! $this->appHasContainer($name)) :
            $controller = $this->getItemController();
            $this->appAddContainer($name, new $controller($term));
        endif;

        return $this->appGetContainer($name);
    }

    /**
     * Récupération d'un élément selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut name.
     * @param string $value Valeur de l'attribut
     *
     * @return null|object|TermItemInterface
     */
    public function getBy($key = 'slug', $value)
    {
        switch ($key) :
            default :
                if (($term = \get_term_by($key, $value, $this->getObjectName())) && !is_wp_error($term)) :
                    return $this->get($term);
                endif;
                break;
        endswitch;

        return null;
    }

    /**
     * Récupération d'une liste d'élément selon des critères de requête
     *
     * @param array $query_args Liste des arguments de requête
     *
     * @return array|TermItemInterface[]
     */
    public function getList($query_args = [])
    {
        if (!$query_args['taxonomy'] = $this->getObjectName()) :
            return [];
        endif;

        $terms = \get_terms($query_args);

        if ($terms && !is_wp_error($terms)) :
            $items = array_map([$this, 'get'], $terms);
        else :
            $items = [];
        endif;

        $controller = $this->getListController();

        return new $controller($items);
    }
}

