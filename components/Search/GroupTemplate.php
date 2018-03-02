<?php
namespace tiFy\Components\Search;

class GroupTemplate
{
    /**
     * Instance de la classe
     * @var static
     */
    private static $Instance = null;

    /**
     * Classe de rappel
     * @var \tiFy\Components\Search\Factory
     */
    private static $Factory = null;

    /**
     * @return null|static
     */
    public static function getInstance()
    {
        if (!self::$Instance) :
            $instance = new static;

            self::$Factory = Search::get('_global');

            self::$Instance = $instance;
        endif;

        return self::$Instance;
    }

    /**
     * Récupération de l'intitulé du groupe
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    public function getTitle($group = 0)
    {
        if (!$attrs = $this->getAttrList($group)) :
            return;
        endif;

        return $attrs['search_results']['title'];
    }

    /**
     * Récupération du message de resultats indisponibles
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    public function getNoResultsFound($group = 0)
    {
        if (!$attrs = $this->getAttrList($group)) :
            return;
        endif;

        return $attrs['search_results']['no_results_found'];
    }

    /**
     * Récupération du nombre de resultats trouvés par groupe de recherche
     *
     * @param int $group indice du groupe
     *
     * @return int
     */
    public function getFoundPosts($group = 0)
    {
        if (!$factory = self::$Factory) :
            return 0;
        endif;

        if (!$group) :
            $group = $this->getCurrentGroup();
        endif;

        return (int)$factory->getFoundPosts($group);
    }

    /**
     * Récupération du lien d'affichage des résultats complet
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    public function getMoreLink($group = 0)
    {
        if (!$attrs = $this->getAttrList($group)) :
            return;
        endif;

        if (!$this->getFoundPosts($group)) :
            return;
        endif;

        return sprintf($attrs['search_results']['more_link'], get_query_var('_s', get_query_var('s', '')));
    }

    /**
     * Récupération des attributs d'un groupe
     *
     * @param int $group indice du groupe
     *
     * @return null|array
     */
    public function getAttrList($group = 0)
    {
        if (!$factory = self::$Factory) :
            return;
        endif;

        if (!$group) :
            $group = $this->getCurrentGroup();
        endif;

        return $factory->getAttrList($group);
    }

    /**
     * Récupération du groupe courant
     *
     * return $int
     */
    public function getCurrentGroup()
    {
        return Query::getInstance()->current_group;
    }
}