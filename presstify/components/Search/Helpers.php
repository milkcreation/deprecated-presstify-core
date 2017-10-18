<?php
namespace {
    use tiFy\Components\Search\Query;
    use tiFy\Components\Search\GroupTemplate;

    /**
     * @return Query
     */
    function tify_search_query()
    {
        return Query::getInstance();
    }

    /**
     * @return bool
     */
    function tify_search_have_groups()
    {
        return tify_search_query()->have_groups();
    }

    /**
     *
     */
    function tify_search_the_group()
    {
        return tify_search_query()->the_group();
    }

    /**
     * @return bool
     */
    function tify_search_have_posts()
    {
        return tify_search_query()->have_posts();
    }

    /**
     *
     */
    function tify_search_the_post()
    {
        return tify_search_query()->the_post();
    }

    /**
     * @return GroupTemplate
     */
    function tify_search_group()
    {
        return GroupTemplate::getInstance();
    }

    /**
     * Affichage de l'intitulé du groupe
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_the_title($group = 0)
    {
        echo tify_search_group()->getTitle($group);
    }

    /**
     * Récupération de l'intitulé du groupe
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_get_the_title($group = 0)
    {
        return tify_search_group()->getTitle($group);
    }

    /**
     * Affichage du message de resultats indisponibles
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_no_results_found($group = 0)
    {
        echo tify_search_group()->getNoResultsFound($group);
    }

    /**
     * Récupération du message de resultats indisponibles
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_get_no_results_found($group = 0)
    {
        return tify_search_group()->getNoResultsFound($group);
    }

    /**
     * Affichage du nombre de resultats trouvés
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_found_posts($group = 0)
    {
        echo tify_search_group()->getFoundPosts($group);
    }

    /**
     * Récupération du nombre de resultats trouvés
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_get_found_posts($group = 0)
    {
        return tify_search_group()->getFoundPosts($group);
    }

    /**
     * Affichage du liens d'affichage des resultats complets
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_more_link($group = 0)
    {
        echo tify_search_group()->getMoreLink($group);
    }

    /**
     * Récupération du nombre de resultats trouvés
     *
     * @param int $group indice du groupe
     *
     * @return null|string
     */
    function tify_search_group_get_more_link($group = 0)
    {
        return tify_search_group()->getMoreLink($group);
    }
}