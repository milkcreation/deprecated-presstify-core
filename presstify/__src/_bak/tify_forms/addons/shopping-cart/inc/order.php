<?php 

function mktzr_forms_cart_order_handle(){
	//Bypass
	if( ! isset( $_REQUEST['mktzr_forms_cart'] ) )
		return;
	
	if( $_REQUEST['mktzr_forms_cart'] != 'order' )
		return;
	
	var_dump('OK');
	exit;
}
