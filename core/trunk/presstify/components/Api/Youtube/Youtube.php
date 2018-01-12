<?php
/**
 * @see https://github.com/madcoda/php-youtube-api
 */
namespace tiFy\Components\Api\Youtube;

class Youtube extends \Madcoda\Youtube\Youtube
{
    /**
     * Instance de la classe
     * @var tiFy\Components\Api\Youtube\Youtube
     */
    static $Inst        = null;

    /**
     * CONSTRUCTEUR
     *
     * @param array $param
     * @param $sslPath
     *
     * @return void
     */
    public function __construct($params = [], $sslPath = null)
    {
        parent::__construct($params, $sslPath);
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     *
     * @param array $attrs
     *
     * @return void
     */
    public static function create($attrs = [])
    {
        return self::$Inst = new static($attrs, is_ssl());
    }
    
    /**
     * Vérification de correspondance d'url
     *
     * @param string $url
     *
     * @return string
     */
    public static function isUrl($url)
    {
        return preg_match('#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $url);
    }

    /**
     * Récupération de miniature
     *
     * @param string $formats Format de l'image, par ordre de préférence.
     *
     * @return array
     */
    public static function getThumbnailSrc($url, $formats = ['maxres','standard', 'height', 'medium', 'default'])
    {
        if (!$inst = self::$Inst) :
            return;
        endif;

        // Vérification de l'url de la vidéo
        if (! self::isUrl($url)) :
            return new \WP_Error('tFyComponentsApiYtInvalidSrc', __('Url YouTube invalide', 'tify'));
        endif;

        // Récupération de l'ID de la vidéo
        if (! $ytid = self::parseVIdFromURL($url)) :
            return new \WP_Error('tFyComponentsApiYtParseVIdFailed', __('Récupération de l\ID de la vidéo depuis l\'url en échec', 'tify'));
        endif;

        // Récupération de infos de la vidéo
        if (! $infos = $inst->getVideoInfo($ytid)) :
            return new \WP_Error('tFyComponentsApiYtGetVideoInfos', __('Impossible de récupérer les informations de la vidéo', 'tify'));
        endif;

        // Récupération de la liste des miniatures
        if (empty($infos->snippet->thumbnails)) :
            return new \WP_Error('tFyComponentsApiYtAnyThumbnailAvailable', __('Aucune miniature disponible', 'tify'));
        endif;

        foreach ($formats as $format) :
            if(empty($infos->snippet->thumbnails->{$format})) :
                continue;
            endif;
            $attrs = $infos->snippet->thumbnails->{$format};
            if(empty($attrs->url) || empty($attrs->width) || empty($attrs->height) ) :
                continue;
            endif;

            $src = array_values((array) $attrs);
            $src[] = $format;

            return $src;
        endforeach;
    }
}