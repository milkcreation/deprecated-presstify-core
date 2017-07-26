<?php
/**
 * @see https://github.com/vimeo/vimeo.php
 * @see https://developer.vimeo.com/api/start
 */
namespace tiFy\Components\Api\Vimeo;

class Vimeo extends \Vimeo\Vimeo
{
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $client_id, $client_secret, $access_token = null )
    {
        parent::__construct( $client_id, $client_secret, $access_token );
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
        return new static( $attrs['client_id'], $attrs['client_secret'], ( ! empty( $attrs['access_token'] ) ? $attrs['access_token'] : null ) );
    }
    
    /**
     * Vérification de correspondance d'url
     */
    public static function isUrl( $url )
    {
        return preg_match( '#^https?://(.+\.)?vimeo\.com/.*#', $url );
    }
    
    /**
     * Récupération de l'identifiant depuis une url valide 
     */
    public static function getUrlId( $url )
    {
        if( self::isUrl( $url ) )
            return (int) substr(parse_url( $url, PHP_URL_PATH ), 1);
    }
    
    /**
     * Récupération des images
     */
    public function getThumbnails( $url )
    {
        if( ! $id = self::getUrlId( $url ) )
            return;
        if( ! $request = $this->request( "/videos/{$id}/pictures") )
            return;
        
        return $request['body']['data'][0];
    }
}