<?php

namespace tiFy\Core\Query\Controller;

interface PostItemInterface
{
    /**
     * Récupération de l'object Post Wordpress associé
     *
     * @return \WP_Post
     */
    public function getPost();

    /**
     * Récupération de l'identifiant de qualification Wordpress du post
     *
     * @return int
     */
    public function getId();
    /**
     * Récupération de l'identifiant de qualification Wordpress (post_name)
     *
     * @return string
     */
    public function getSlug();

    /**
     * Alias de récupération de l'identifiant de qualification Wordpress (post_name)
     *
     * @return string
     */
    public function getName();

    /**
     * Récupération de l'identifiant unique de qualification global
     * @internal Ne devrait pas être utilisé en tant que lien
     * @see https://developer.wordpress.org/reference/functions/the_guid/
     *
     * @return string
     */
    public function getGuid();

    /**
     * Récupération de la date de création au format datetime
     *
     * @return bool $gmt Activation de la valeur basée sur le temps moyen de Greenwich
     *
     * @return string
     */
    public function getDate($gmt = false);

    /**
     * Récupération de la date de la dernière modification au format datetime
     *
     * @return bool $gmt Activation de la valeur basée sur le temps moyen de Greenwich
     *
     * @return string
     */
    public function getModified($gmt = false);

    /**
     * Récupération de l'identifiant de qualification de l'auteur original
     *
     * @return int
     */
    public function getAuthorId();

    /**
     * Récupération de l'identifiant de qualification du post parent relatif
     *
     * @return int
     */
    public function getParentId();
    /**
     * Récupération du type de post
     *
     * @return string
     */
    public function getType();

    /**
     * Récupération du statut de publication
     *
     * @return string
     */
    public function getStatus();

    /**
     * Récupération de la valeur brute ou formatée de l'intitulé de qualification
     *
     * @param bool $raw Formatage de la valeur
     *
     * @return string
     */
    public function getTitle($raw = false);

    /**
     * Récupération de la valeur brute ou formatée de l'extrait
     *
     * @param bool $raw Formatage de la valeur
     *
     * @return string
     */
    public function getExcerpt($raw = false);

    /**
     * Récupération du contenu de description
     *
     * @param bool $raw Formatage de la valeur
     *
     * @return string
     */
    public function getContent($raw = false);

    /**
     * Récupération du lien d'édition du post dans l'interface administrateur
     *
     * @return string
     */
    public function getEditLink();

    /**
     * Récupération du permalien d'affichage du post dans l'interface utilisateur
     *
     * @return string
     */
    public function getPermalink();
}