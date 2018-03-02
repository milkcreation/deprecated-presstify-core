<?php
/**
 * @see https://github.com/google/recaptcha
 */

namespace tiFy\Components\Api\Recaptcha;

use \ReCaptcha\RequestMethod;
use ReCaptcha\RequestMethod\SocketPost;

class Recaptcha extends \ReCaptcha\ReCaptcha
{
    /**
     * Attributs de configuration.
     * @var array
     */
    private $Attrs = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($attrs = [])
    {
        try {
            parent::__construct($attrs['secretkey'], (ini_get('allow_url_fopen') ? null : new SocketPost));
            $this->Attrs = $attrs;
        } catch (\RuntimeException $e) {
            wp_die($e->getMessage(), __('Api reCaptcha : Erreur de configuration', 'tify'), $e->getCode());
        }
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $secretkey Clé secrète, requise pour la communication entre le site et Google. Veillez à ne surtout pas divulger cette clé !
     *      @var string $sitekey Clé du site, utilisée dans le code HTML
     * }
     */
    public static function create($attrs = [])
    {
        $defaults = [
            'secretkey' => '',
            'sitekey'   => '',
        ];
        $attrs = array_merge($defaults, $attrs);

        return new static($attrs);
    }

    /**
     * Récupération de la clé de site publique
     *
     * @return string
     */
    final public function getSiteKey()
    {
        if (isset($this->Attrs['sitekey'])) :
            return $this->Attrs['sitekey'];
        endif;
    }
}