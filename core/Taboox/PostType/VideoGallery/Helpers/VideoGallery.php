<?php

namespace tiFy\Core\Taboox\PostType\VideoGallery\Helpers;

class VideoGallery extends \tiFy\App
{
    /**
     * Liste des attributs de configuration par défaut
     * @var array
     */
    public static $DefaultAttrs = [
        'name' => '_tify_taboox_video_gallery',
        'max'  => -1,
    ];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_video_gallery_has', 'has');
        $this->appAddHelper('tify_taboox_video_gallery_get', 'get');
    }

    /**
     * Vérification d'existance d'éléments
     *
     * @param int $post_id Identifiant de qualification du post
     * @param array $args Liste des attributs de récupération
     *
     * @return bool
     */
    public static function has($post_id = null, $args = [])
    {
        return self::get($post_id, $args);
    }

    /**
     * Récupération de la liste des éléments
     *
     * @param int Identifiant de qualification du post
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($post_id = null, $args = [])
    {
        $post_id = (null === $post_id) ? get_the_ID() : $post_id;

        // Traitement des arguments
        $args = wp_parse_args($args, self::$DefaultAttrs);

        return get_post_meta($post_id, $args['name'], true);
    }
}