<?php

namespace tiFy\Core\Query\Controller;

use Illuminate\Support\Fluent;
use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractPostItem extends Fluent implements PostItemInterface
{
    use TraitsApp;

    /**
     * Objet Post Wordpress
     * @var \WP_Post
     */
    protected $object;

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