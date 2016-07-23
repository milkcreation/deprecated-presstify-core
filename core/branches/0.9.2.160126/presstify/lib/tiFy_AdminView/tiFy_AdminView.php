<?php
foreach( array( 'edit', 'list', 'import' ) as $view )
	require_once( dirname( __FILE__ ). '/tify_admin_view-'. $view .'.php' );