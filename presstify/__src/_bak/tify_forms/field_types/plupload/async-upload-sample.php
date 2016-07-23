<?php
define( 'DOING_AJAX', true );

if ( defined('ABSPATH') )
	require_once(ABSPATH . 'wp-load.php');
else
	require_once( '../../../../../../../wp-load.php' );

send_nosniff_header();
nocache_headers();

// Traitement

// Valeur de retour
echo json_encode( array( 'toto' => 'tutu' )  );
exit;