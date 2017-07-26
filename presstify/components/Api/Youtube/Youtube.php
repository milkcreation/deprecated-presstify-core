<?php
/**
 * @see https://github.com/madcoda/php-youtube-api
 */
namespace tiFy\Components\Api\Youtube;

class Youtube extends \Madcoda\Youtube\Youtube
{
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $params = array(), $sslPath = null )
    {
        parent::__construct( $params, $sslPath );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     * @param array $attrs
     */
    public static function tiFyApiInit( $attrs = array() )
    {
        return new static( $attrs, is_ssl() );
    }
    
    /**
     * Vérification de correspondance d'url
     */
    public static function isUrl( $url )
    {
        return preg_match( '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $url );
    }
}