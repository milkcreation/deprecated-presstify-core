<?php
/**
 * @see https://github.com/facebook/php-graph-sdk
 * @see https://developers.facebook.com/docs/php/howto/example_facebook_login
 */
namespace tiFy\Components\Api\Facebook;

class Facebook extends \Facebook\Facebook
{
    /**
     * CONSTRUCTEUR
     */
    public function __construct( array $config = [] )
    {
        parent::__construct( $config );
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
        session_start();
        
        return new static( $attrs );
    }
}