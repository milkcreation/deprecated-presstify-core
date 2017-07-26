<?php
/**
 * @see https://github.com/google/recaptcha
 */
namespace tiFy\Components\Api\Recaptcha;

use \ReCaptcha\RequestMethod;

class Recaptcha extends \ReCaptcha\ReCaptcha
{
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $secret, RequestMethod $requestMethod = null )
    {
        parent::__construct( $secret, $requestMethod );
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
        if( ! ini_get( 'allow_url_fopen' ) ) :
            // allow_url_fopen = Off            
            return new static( $attrs['secretkey'], new \ReCaptcha\RequestMethod\SocketPost );
        else :
            // allow_url_fopen = On
            return new static( $attrs['secretkey'] );
        endif;
    }
}