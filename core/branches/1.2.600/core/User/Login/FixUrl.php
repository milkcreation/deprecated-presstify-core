<?php
namespace tiFy\Components\Login;

class FixUrl extends \tiFy\App\Factory {
    /* = ARGUMENTS = */
    protected $CallFilters = [
        'lostpassword_url',
        'network_site_url',
        'retrieve_password_message',
        'retrieve_password_title'
    ];
    // Nombre d'arguments autorisés
    protected $CallFiltersArgsMap = [
        'lostpassword_url'          => 2,
        'network_site_url'          => 3,
        'retrieve_password_message' => 2
    ];

    /* = FILTRES = */
    // fixes "Lost Password?" URLs on login page
    final public function lostpassword_url( $url, $redirect )
    {
        $args = [ 'action' => 'lostpassword' ];

        if ( ! empty( $redirect ) ) {
            $args['redirect_to'] = $redirect;
        }

        return add_query_arg( $args, site_url( 'wp-login.php' ) );
    }

    // fixes other password reset related urls
    final public function network_site_url( $url, $path, $scheme )
    {
        if ( stripos( $url, "action=lostpassword" ) !== false ) {
            return site_url( 'wp-login.php?action=lostpassword', $scheme );
        }

        if ( stripos( $url, "action=resetpass" ) !== false ) {
            return site_url( 'wp-login.php?action=resetpass', $scheme );
        }

        return $url;
    }

    // fixes URLs in email that goes out.
    final public function retrieve_password_message( $message, $key )
    {
        return str_replace( get_site_url( 1 ), get_site_url(), $message );
    }

    // fixes email title
    final public function retrieve_password_title( $title )
    {
        return sprintf( __( "[%s] Réinitialisation de mot de passe", 'tify' ),
            wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
    }
}