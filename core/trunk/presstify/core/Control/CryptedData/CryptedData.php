<?php
namespace tiFy\Core\Control\CryptedData;

use \Defuse\Crypto\Crypto;

class CryptedData extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'crypted_data';
    
    /**
     * Instance Courante
     */ 
    protected static $Instance = 0;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale de Wordpress
     */
    final public function init()
    {
        wp_register_style( 'tify_control-crypted_data', self::tFyAppUrl( get_class() ) .'/CryptedData.css', array( 'dashicons' ), 170501 );
        wp_register_script( 'tify_control-crypted_data', self::tFyAppUrl( get_class() ) .'/CryptedData.js', array( 'jquery' ), 170501, true );
        
        // Actions ajax       
        add_action( 'wp_ajax_tiFyControlCryptedData_encrypt', array( $this, 'wp_ajax_encrypt' ) );
        add_action( 'wp_ajax_tiFyControlCryptedData_decrypt', array( $this, 'wp_ajax_decrypt' ) );
        add_action( 'wp_ajax_tiFyControlCryptedData_generate', array( $this, 'wp_ajax_generate' ) ); 
    }

    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-crypted_data' );
        wp_enqueue_script( 'tify_control-crypted_data' );       
    }
    
    /**
     * Récupération Ajax de la valeur décryptée
     */
    final public function wp_ajax_encrypt()
    {
        $callback = ! empty( $_REQUEST['encrypt_cb'] ) ? wp_unslash( $_REQUEST['encrypt_cb'] ) : "tiFy\\Core\\Control\\CryptedData\\CryptedData::encrypt";
        $response = call_user_func( $callback, $_REQUEST['value'], $_REQUEST['data'] );
        
        if( is_wp_error( $response ) ) :
            wp_send_json_error( $response->get_error_message() );  
        else :
            wp_send_json_success( $response );
        endif;
    }
    
    /**
     * Récupération Ajax de la valeur décryptée
     */
    final public function wp_ajax_decrypt()
    {
        $callback = ! empty( $_REQUEST['decrypt_cb'] ) ? wp_unslash( $_REQUEST['decrypt_cb'] ) : "tiFy\\Core\\Control\\CryptedData\\CryptedData::decrypt";
        $response = call_user_func( $callback, $_REQUEST['value'], $_REQUEST['data'] );
        
        if( is_wp_error( $response ) ) :
            wp_send_json_error( $response->get_error_message() );  
        else :            
            wp_send_json_success( $response );
        endif;
    }
    
    /**
     * Génération Ajax d'une valeur décryptée
     */
    final public function wp_ajax_generate()
    {
        $callback = ! empty( $_REQUEST['generate_cb'] ) ? wp_unslash( $_REQUEST['generate_cb'] ) : "tiFy\\Core\\Control\\CryptedData\\CryptedData::generate";
        $response = call_user_func( $callback, $_REQUEST['data'] );
        
        if( is_wp_error( $response ) ) :
            wp_send_json_error( $response->get_error_message() );  
        else :            
            wp_send_json_success( $response );
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage du controleur
     * @param array $args
     * @return string
     */
    public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // ID HTML du conteneur
            'container_id'          => 'tiFyControlCryptedData--'. self::$Instance,
            // Classe HTML du conteneur
            'container_class'       => '',
            // Classe du champ de saisie
            'class'                 => '',
            // Nom d'enregistrement de la valeur (readonly doit être à false)
            'name'                  => 'tiFyControlCryptedData'. self::$Instance,            
            // Valeur encryptée (hashée)
            'value'                 => '',
            // Controleur en lecture seule (désactive aussi l'enregistrement et le générateur)
            'readonly'              => false,
            
            'length'                => 32,
            // Masquage des données true | false les données aparaissent en clair
            'masked'                => true,
            
            // Fonction de rappel d'encryptage
            'encrypt_cb'            => 'tiFy\Core\Control\CryptedData\CryptedData::encrypt',
            // Fonction de rappel de decryptage
            'decrypt_cb'            => 'tiFy\Core\Control\CryptedData\CryptedData::decrypt',
            // Fonction de rappel de générateur, mettre à false pour désactiver ou redonly à true
            'generate_cb'           => 'tiFy\Core\Control\CryptedData\CryptedData::generate',
            // Données passée dans les fonctions de rappel 
            'data'                  => array()
        );        
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Masque
        $mask = '';
        while( strlen( $mask ) < $length ) :
            $mask .= '*';
        endwhile;
        
        // Selecteur HTML
        $output  = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlCryptedData". ( $masked ? ' masked' : '' ) . ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"crypted_data\" data-encrypt_cb=\"{$encrypt_cb}\" data-decrypt_cb=\"{$decrypt_cb}\" data-generate_cb=\"{$generate_cb}\" data-transport=\"". urlencode( json_encode( $data ) ) ."\" data-mask=\"{$mask}\">\n";
        
        $output .= "\t<div class=\"tiFyControlCryptedData-wrapper\">\n";        
        $output .= "\t\t<a href=\"#{$container_id}\" class=\"tiFyControlCryptedData-toggleMask\" data-tify_control_crypted_data=\"toggle-mask\"></a>\n";
        
        $output .= "\t\t<input class=\"tiFyControlCryptedData-input". ( $class ? ' '. $class : '' ) ."\" type=\"". ( $masked ? 'password' : 'text' ) ."\" size=\"{$length}\" value=\"". ( $masked ? $mask : $plain ) ."\" data-tify_control_crypted_data=\"input\" autocomplete=\"off\"";
        if( $readonly ) :
            $output .= " readonly=\"readonly\"";
        endif;
        $output .= "/>\n";
        $output .= "\t\t<input type=\"hidden\" class=\"tiFyControlCryptedData-cypher\" ". ( ! $readonly ? "name=\"{$name}\"" : "" ) ." value=\"{$value}\" />\n";        
        $output .= "\t</div>";
        
        if( $generate_cb && ! $readonly ) :
            $output .= "\t<a href=\"#{$container_id}\" class=\"tiFyControlCryptedData-generator\" data-tify_control_crypted_data=\"generate\">". __( 'Générer', 'tify' ) ."</a>\n";
        endif;
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
    
    /**
     * Methode de rappel de la clé d'encryptage
     */
    public static function secretKey()
    {
        return SECURE_AUTH_SALT;
    }
    
    /**
     * Methode de rappel d'encryptage
     */
    public static function encrypt( $input, $data = array() )
    {
        try {
            $output = Crypto::encryptWithPassword( $input, static::secretKey() ); 
        } catch( \Defuse\Crypto\Exception\CryptoException $e ) {
            $output = new \WP_Error( 'CryptedDataEncryptError', $e->getMessage() );
        }
        
        return $output;
    }
    
    /**
     * Methode de rappel de décryptage
     */
    public static function decrypt( $input, $data = array() )
    {
        try {
            $output = Crypto::decryptWithPassword( $input, static::secretKey() ); 
        } catch( \Defuse\Crypto\Exception\CryptoException $e ) {
            $output = new \WP_Error( 'CryptedDataDecryptError', $e->getMessage() );
        }
        
        return $output;
    }
    
    /**
     * Methode de rappel de generation
     */
    public static function generate( $data = array() )
    {
        return wp_generate_password( 12, false, false );
    }
}