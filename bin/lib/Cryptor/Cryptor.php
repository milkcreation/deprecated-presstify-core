<?php
namespace tiFy\Lib\Cryptor;

class Cryptor
{
    /**
     * Clé secrète de hashage
     * @var string
     */
    private $Secret = null;

    /**
     * Clé privée de hashage
     * @var string
     */
    private $Private = null;

    /**
     * CONSTRUCTEUR
     *
     * @param string $secret Clé secrète de hashage
     * @param string $private Clé privée de hashage
     *
     * @return void
     */
    public function __construct($secret = null, $private = null)
    {
        if ($secret) :
            $this->Secret = $secret;
        else :
            $this->Secret = NONCE_KEY;
        endif;
        if ($secret) :
            $this->Private = $secret;
        else :
            $this->Private = NONCE_SALT;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la clée secrète de hashage
     *
     * @return string
     */
    final public function getSecret()
    {
        return $this->Secret;
    }

    /**
     * Récupération de la clé privée de hashage
     *
     * @return string
     */
    final public function getPrivate()
    {
        return $this->Private;
    }

    /**
     * Encryptage
     *
     * @param string $plain Texte à encrypter
     * @param string $secret Clé secrète de hashage
     * @param string $private Clé privée de hashage
     *
     *
     * @return string
     */
    final public static function encrypt($plain, $secret = null, $private = null)
    {
        return self::crypt($plain, 'encrypt', $secret, $private);
    }

    /**
     * Décryptage
     *
     * @param string $hash Texte à décrypter
     * @param string $secret Clé secrète de hashage
     * @param string $private Clé privée de hashage
     *
     * @return string
     */
    final public static function decrypt($hash, $secret = null, $private = null)
    {
        return self::crypt($hash, 'decrypt', $secret, $private);
    }

    /**
     * Lancement de l'action de cryptage ou de decryptage
     *
     * @param $string
     * @param string $action
     * @param string $secret Clé secrète de hashage
     * @param string $private Clé privée de hashage
     *
     * @return bool|string
     */
    final private static function crypt($string, $action = 'encrypt', $secret = null, $private = null)
    {
        $inst = new self($secret, $private);
        $secret_key = $inst->getSecret();
        $secret_iv = $inst->getPrivate();

        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        switch($action) :
            default :
            case 'encrypt' :
                return base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
                break;
            case 'decrypt' :
                return openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
                break;
        endswitch;
    }
}