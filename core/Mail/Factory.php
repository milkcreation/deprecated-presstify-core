<?php 
namespace tiFy\Core\Mail;

use \tiFy\Lib\Mailer\MailerNew;

class Factory extends \tiFy\App\Factory
{
    /**
     * Destinataire(s)
     * @var string|array
     */
    public $To = [];

    /**
     * Expéditeur
     * @var string|array
     */
    public $From = [];

    /**
     * Destinataire de la réponse au mail
     * @var array
     */
    public $ReplyTo = [];

    /**
     * Destinataires de copie carbone
     * @var array
     */
    public $Cc = [];

    /**
     * Destinataires de copie cachée
     * @var array
     */
    public $Bcc = [];

    /**
     * Sujet
     * @var string
     */
    public $Subject = '';

    /**
     * Message
     * @var string
     */
    public $Message = '';

    /**
     * Entête du message
     * @var string
     */
    public $MessageHeader = '';

    /**
     * Pied de page du message
     * @var string
     */
    public $MessageFooter = '';

    /**
     * Variables d'environnement
     * @var array
     */
    public $MergeTags = [];

    /**
     * Format des variables d'environnements
     * @var string
     */
    public $MergeTagsFormat = '\*\|(.*?)\|\*';

    /**
     * Personnalisation des styles CSS
     * @var string
     */
    public $CustomCSS = '';

    /**
     * Format d'expédition du message (html ou plain ou multi)
     * @var string
     */
    public $ContentType = 'multi';

    /**
     * Encodage des caractères du message
     * @var string
     */
    public $Charset = 'UTF-8';

    /**
     * CONSTRUCTEUR
     *
     * @param array $params Liste des paramètres de configuration du mail
     *
     * @return void
     */
    public function __construct($params = [])
    {
        // Traitement des paramètres d'envoi de l'email
        $this->setParams($params);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Définition des paramètres
     */
    final public function setParams($params = [])
    {
        foreach ((array)$params as $param => $value) :
            $this->setParam($param, $value);
        endforeach;
    }

    /**
     * Définition d'un paramètre
     */
    final public function setParam( $param, $value )
    {
        $param = MailerNew::sanitizeName( $param );
        if( MailerNew::isAllowedParam( $param ) ) :
            $this->{$param} = $value;  
        endif;
    }

    /**
     * Récupération des paramètres
     */
    final public function getParams()
    {
        $params = array();
        foreach( MailerNew::getAllowedParams() as $param ) :
            $params[$param] = $this->getParam( $param );
        endforeach;
        
        return $params;
    }

    /**
     * Récupération d'un paramètre
     */
    final public function getParam( $param )
    {
        $param = MailerNew::sanitizeName( $param );
        if( ! MailerNew::isAllowedParam( $param ) )
            return;
        
        if( method_exists( $this, 'get'. $param ) ) :
            return call_user_func( array( $this, 'get'. $param ) );
        elseif( isset( $this->{$param} ) ) :
            return $this->{$param};
        endif;      
    }

    /**
     * Envoi de l'email
     */
    final public function send()
    {        
        return MailerNew::send($this->getParams());
    }

    /**
     * Prévisualisation de l'email
     */
    final public function preview()
    {        
        echo MailerNew::preview($this->getParams());
        exit;
    }
}