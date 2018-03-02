<?php

namespace tiFy\Components\Layouts\MetaTitle;

class WpQueryPart
{
    /**
     * Liste des éléments à inclure dans le fil d'ariane
     * @var array
     */
    private $Parts = [];

    /**
     * Récupération de la liste des éléments de contenu relatif à la requête globale de Wordpress
     *
     * @return array
     */
    public function getList()
    {
        // Page 404 - Contenu introuvable
        if (is_404()) :
            $this->Parts[] = $this->current404();

        // Page liste de résultats de recherche
        elseif (is_search()) :
            $this->Parts[] = $this->currentSearch();

        // Page de contenus associés à une taxonomie
        elseif (is_tax()) :
            $this->Parts[] = $this->currentTax();

        // Page d'accueil du site
        elseif (is_front_page()) :
            $this->Parts[] = $this->currentPost();

        // Page liste des articles du blog
        elseif (is_home()) :
            $this->Parts[] = $this->currentHome();

        // Page de contenu de type fichier média
        elseif (is_attachment()) :
            $this->Parts[] = $this->currentPost();

        // Page de contenu de type post
        elseif (is_single()) :
            $this->Parts[] = $this->currentPost();

        // Page de contenu de type page
        elseif (is_page()) :
            $this->Parts[] = $this->currentPost();

        // Page liste de contenus associés à une catégorie
        elseif (is_category()) :
            $this->Parts[] = $this->currentCategory();

        // Page liste de contenus associés à un mot-clef
        elseif (is_tag()) :
            $this->Parts[] = $this->currentTag();

        // Page liste de contenus associés à un auteur
        elseif (is_author()) :
            $this->Parts[] = $this->currentAuthor();

        // Page liste de contenus relatifs à une date
        elseif (is_date()) :
            $this->Parts[] = $this->currentDate();

        // Pages liste de contenus
        elseif (is_archive()) :
            $this->Parts[] = $this->currentArchive();

        // Page liste de contenus paginé
        // @todo
        elseif (is_paged()) :

        endif;

        return $this->Parts;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page non trouvée 404
     *
     * @return string
     */
    public function current404()
    {
        $part = __('Erreur 404 - Page introuvable', 'tify');

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de résultats de recherche
     *
     * @return string
     */
    public function currentSearch()
    {
        $part = sprintf(__('Résultats de recherche pour : "%s"', 'tify'), get_search_query());

        return $part;
    }

    /**
     * Récupération de l'élèment de page liste de contenus associés à une taxonomie
     *
     * @return string
     */
    public function currentTax()
    {
        /**
         * @var \WP_Term $term Terme de taxonomie courant
         */
        $term = get_queried_object();

        $part = sprintf('%s : %s', get_taxonomy($term->taxonomy)->label, $term->name);

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste des articles d'actualités (blog)
     *
     * @return string
     */
    public function currentHome()
    {
        global $wp_query;

        $part = __('Actualités', 'tify');

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page de contenu seul (is_attachment|is_single|is_page)
     *
     * @return string
     */
    public function currentPost()
    {
        $part = $this->getPostTitle(get_the_ID());

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus relatifs à une catégorie
     *
     * @return string
     */
    public function currentCategory()
    {
        /**
         * @var \WP_Term $category
         */
        $category = get_category(get_query_var('cat'), false);

        $part = sprintf('Catégorie : %s', $category->name);

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus seul relatifs à un mot-clef
     *
     * @return string
     */
    public function currentTag()
    {
        /**
         * @var \WP_Term $tag
         */
        $tag = get_tag( get_query_var( 'tag' ), false );

        $part = sprintf('Mot-Clef : %s', $tag->name);

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus relatifs à un auteur
     *
     * @return string
     */
    public function currentAuthor()
    {
        $name = get_the_author_meta('display_name', get_query_var('author'));

        $part = sprintf('Auteur : %s', $name);

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus relatifs à une date
     *
     * @return string
     */
    public function currentDate()
    {
        if (is_day()) :
            $part = sprintf(__('Archives du jour : %s', 'tify'), get_the_date());
        elseif (is_month()) :
            $part = sprintf(__('Archives du mois : %s', 'tify'), get_the_date('F Y'));
        elseif (is_year()) :
            $part = sprintf(__('Archives de l\'année : %s', 'tify'), get_the_date('Y'));;
        endif;

        return $part;
    }

    /**
     * Récupération de l'élèment lors de l'affichage d'une page liste de contenus
     *
     * @return string
     */
    public function currentArchive()
    {
        $part = (is_post_type_archive())
            ? post_type_archive_title('', false)
            : __('Actualités', 'tify');

        return $part;
    }

    /**
     * Intitulé de d'un élément relatif à un post
     *
     * @param int|\WP_Post $post
     *
     * @return string
     */
    protected function getPostTitle($post)
    {
        $post = \get_post($post);

        return esc_html(
            wp_strip_all_tags(
                get_the_title($post->ID)
            )
        );
    }
}