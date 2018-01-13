<?php
/**
 * @deprecated
 */
namespace tiFy\Core\Control\Token;

class Token extends \tiFy\Core\Control\Factory {
    /* = INITIALISATION DE WORDPRESS = */
    protected function init()
    {
        add_action( 'wp_ajax_tify_control_token_keygen', [ $this, 'AjaxKeyGen' ] );
        add_action( 'wp_ajax_nopriv_tify_control_token_keygen', [ $this, 'AjaxKeyGen' ] );
        add_action( 'wp_ajax_tify_control_token_unmask', [ $this, 'AjaxUnMask' ] );
        add_action( 'wp_ajax_tify_control_token_encrypt', [ $this, 'AjaxEncrypt' ] );
        add_action( 'wp_ajax_nopriv_tify_control_token_encrypt', [ $this, 'AjaxEncrypt' ] );

        wp_register_style( 'tify_control-token', self::tFyAppUrl() . '/Token.css', [ 'dashicons' ], '141212' );
        wp_register_script( 'tify_control-token', self::tFyAppUrl() . '/Token.js', [ 'jquery' ], '141212', true );
    }

    /* = MISE EN FILE DES SCRIPTS = */
    protected function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-token' );
        wp_enqueue_script( 'tify_control-token' );
    }

    /* = AFFICHAGE = */
    protected function display($args = [])
    {
        $defaults = [
            // Conteneur
            'container_id'    => 'tify_control_token-' . $this->getId(),
            'container_class' => '',
            'class'           => '',
            'name'            => 'tify_control_token-' . $this->getId(),
            'value'           => '',
            'length'          => 32,
            'maskable'        => true,
            'editable'        => false,
            'disabled'        => false,
            'public_key'      => null,
            'private_key'     => null,
            'keygen'          => false,
            'default'         => true,
            'echo'            => 1
        ];

        $args = wp_parse_args( $args, $defaults );
        extract( $args );

        // Décryptage
        $plain = ! $value ? \tiFy\Lib\Token::KeyGen( $length ) : \tiFy\Lib\Token::Decrypt( $value, $public_key,
            $private_key );
        // Encryptage
        $hash = ! $value ? \tiFy\Lib\Token::Encrypt( $plain, $public_key, $private_key ) : $value;
        // Masque
        $mask = '';
        while ( strlen( $mask ) < $length ) {
            $mask .= '*';
        }

        // Selecteur HTML
        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tify_control_token" . ( $container_class ? ' ' . $container_class : '' ) . "" . ( $maskable ? ' maskable mask_plain' : 'unmask_plain' ) . "\" data-tify_control=\"token\" data-mask=\"{$mask}\" data-length=\"{$length}\" data-public=\"" . \tiFy\Lib\Token::Encrypt( $public_key ) . "\" data-private=\"" . \tiFy\Lib\Token::Encrypt( $private_key ) . "\">\n";

        if ( $maskable ) {
            $output .= "\t<a href=\"#{$container_id}\" class=\"tify_control_token-unmask\" data-tify_control_token=\"unmask\"></a>\n";
        }
        $output .= "\t<div class=\"tify_control_token-wrapper\" style=\"width:21em;\">\n";

        $output .= "\t\t<input class=\"tify_control_token-plain {$class}\" type=\"" . ( $maskable ? 'password' : 'text' ) . "\" size=\"{$length}\" value=\"" . ( $maskable ? $mask : $plain ) . "\" data-tify_control_token=\"input\" autocomplete=\"off\"";
        /** @todo Rendre la chaîne éditable - implique la récupération JS du hashage lors de l'événement keyup * */
        if ( ! $editable ) {
            $output .= " readonly=\"readonly\"";
        }
        $output .= "/>\n";
        $output .= "\t\t<input class=\"tify_control_token-hash\" type=\"hidden\" name=\"{$name}\" value=\"{$hash}\" autocomplete=\"off\"";
        if ( $disabled ) {
            $output .= " disabled=\"disabled\"";
        }
        $output .= "/>\n";

        $output .= "\t</div>";
        if ( $keygen ) {
            $output .= "\t<a href=\"#{$container_id}\" class=\"tify_control_token-keygen\" data-tify_control_token=\"keygen\">" . __( 'Générer',
                    'tify' ) . "</a>\n";
        }
        $output .= "</div>\n";

        echo $output;
    }

    /** == Génération de clé via AJAX == **/
    final public function AjaxKeyGen()
    {
        $plain       = \tiFy\Lib\Token::KeyGen( (int) $_POST['length'] );
        $public_key  = \tiFy\Lib\Token::Decrypt( $_POST['public_key'] );
        $private_key = \tiFy\Lib\Token::Decrypt( $_POST['private_key'] );

        if ( empty( $public_key ) ) {
            $public_key = null;
        }
        if ( empty( $private_key ) ) {
            $private_key = null;
        }

        $hash = \tiFy\Lib\Token::Encrypt( $plain, $public_key, $private_key );

        wp_send_json_success( compact( 'plain', 'hash' ) );
    }

    /** == Affichage de clé via AJAX == **/
    final public function AjaxUnMask()
    {
        $public_key  = \tiFy\Lib\Token::Decrypt( $_POST['public_key'] );
        $private_key = \tiFy\Lib\Token::Decrypt( $_POST['private_key'] );

        if ( empty( $public_key ) ) {
            $public_key = null;
        }
        if ( empty( $private_key ) ) {
            $private_key = null;
        }

        $plain = \tiFy\Lib\Token::Decrypt( $_POST['hash'], $public_key, $private_key );

        wp_send_json_success( compact( 'plain' ) );
    }

    /** == Encryptage de clé via AJAX == **/
    final public function AjaxEncrypt()
    {
        $public_key  = \tiFy\Lib\Token::Decrypt( $_POST['public_key'] );
        $private_key = \tiFy\Lib\Token::Decrypt( $_POST['private_key'] );

        if ( empty( $public_key ) ) {
            $public_key = null;
        }
        if ( empty( $private_key ) ) {
            $private_key = null;
        }

        $hash = \tiFy\Lib\Token::Encrypt( $_POST['plain'], $public_key, $private_key );

        wp_send_json_success( compact( 'hash' ) );
    }
}