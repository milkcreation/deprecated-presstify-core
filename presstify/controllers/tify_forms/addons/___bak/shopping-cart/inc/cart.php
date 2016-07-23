<?php
/**
 * Créer un panier (Création du Cookie et ajout des données en BDD)
 */
function mktzr_forms_cart_create( $cartdata ){
	global $wpdb;

	if( $wpdb->insert( $wpdb->carts, $cartdata ) )
		return $wpdb->insert_id;
}

/**
 * Met à jour le panier (Réactualisation du Cookie et mise à jour des données de la BDD)
 */
function mktzr_forms_cart_update( $cartdata ){
	global $wpdb;
	
	if( $wpdb->update( $wpdb->carts, $cartdata, array( 'id' => $cartdata['id'] ) ) )
		return $cartdata['id'];	
}

/**
 * Met à jour le statut du panier à "payé" (mise à jour des données de la BDD et destruction du cookie)
 */
function mktzr_forms_cart_update_status_to_paid( $cart ){
	global $wpdb;
	if( $wpdb->update( $wpdb->carts, array('status' => 'paid', 'date_update' => current_time( 'mysql' ) ), array( 'id' => $cart->id ) ) )
		return $cart->id;
}

/**
 * Récupération d'un panier selon son ID
 */
function mktzr_forms_cart_get( $cart_id ){
	global $wpdb;
	
	if( $cart = $wpdb->get_row( "SELECT * FROM {$wpdb->carts} WHERE id = $cart_id;" ) )
		return $cart;
}

/**
 * Récupèration du panier depuis le cookie
 */
function mktzr_forms_cart_get_from_cookie( ){
	global $wpdb;
	
	// Bypass
	if( ! $salt = mktzr_forms_cart_get_cookie( ) )
		return;
		
	if( $cart = $wpdb->get_row( "SELECT * FROM {$wpdb->carts} WHERE salt LIKE '$salt'" ) )
		return $cart;
}

/**
 * Suppression du panier depuis le cookie
 */
function mktzr_forms_cart_remove_from_cookie( ){
	global $wpdb;
	
	// Bypass
	if( ! $salt = mktzr_forms_cart_get_cookie( ) )
		return;
		
	$wpdb->delete(  $wpdb->carts, array( 'salt' => $salt ) );
}

/**
 *  Créer la référence d'un panier
 * 
 * @todo  La référence d'un panier ne doit évidemment pas être liée à un formulaire (un produit) 
 */
function mktzr_forms_cart_make_reference( $form ){
	global $mktzr_forms, $wpdb;
	
	// Bypass
	if( ! $_form = $mktzr_forms->forms->get( $form ) )
		return;
	if( ! $ref = $mktzr_forms->addons->get_form_option( 'ref', 'shopping-cart', $_form ) )
		return;
	
	$prefix 	= 'MKSC';
	$date 		= mysql2date('ymd',current_time('mysql'),false);
	//$sql_date 	= mysql2date('Y-m-d',current_time('timestamp'));
	$sql_date 	= mysql2date('Y-m-d',current_time('mysql'));
	$id = zeroise( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->carts} WHERE DATE(date_create) = '$sql_date'" ) + 1, 4 );
	
	return sprintf( $ref, $prefix, $date, $id );
}

/*
 * Retourne le prix du panier calculé
 */
function mktzr_forms_cart_calculate_price($form){
	global $mktzr_forms;

	// Bypass
	if( ! $_form = $mktzr_forms->forms->get( $form ) )
		return;	
	
	// Bypass
	if( ! $price_options = $mktzr_forms->addons->get_form_option( 'price', 'shopping-cart', $_form['ID'] ) )
		return;	
	if( ! $price = $price_options['value'] )
		return;
	if( ! $tax = $price_options['tax'] )
		return;
	
	foreach( $_form['fields'] as $field ):
		// Bypass
		if( empty( $field['value'] ) )
			continue;

		if( isset($field['add-ons']['shopping-cart']['price']) )
			$price += $field['add-ons']['shopping-cart']['price'];
	endforeach;

	return number_format( ( $price + ( $price * $tax ) ), 2 );
}
/**
 * Retourne la monnaie
 */
function mktzr_forms_cart_get_currency( $form ){
	global $mktzr_forms;
	
	// Bypass
	if( ! $_form = $mktzr_forms->forms->get( $form ) )
		return;	
	
	// Bypass
	if( ! $price_options = $mktzr_forms->addons->get_form_option( 'price', 'shopping-cart', $_form['ID'] ) )
		return;	
	if( ! $price_currency = $price_options['currency'] )
		return;
	
	return $price_currency;
}

/*
 * Lie le panier à l'utilisateur
 */
function mktzr_forms_bound_cart_to_user( $user_login, $user ){
	global $wpdb;
	
	if( ! $hash = mktzr_forms_cart_get_cookie() )
		return;	
	
	if( ! $cart = mktzr_forms_cart_get_from_cookie( $hash ) )
		return;
	
	// Ajout de la date de modification du panier
	$data = array( 'client_id' => $user->ID, 'id' => $cart->id );

	mktzr_forms_cart_update( $data );
}
add_action('wp_login', 'mktzr_forms_bound_cart_to_user', 10, 2 );

/**
 * Récupération des carts ayant un status "payé" pour un client donné 
 * @param $user_id int : identifiant du client
 * @return $carts Array : Array contenant les objets carts
 */
function mktzr_forms_get_customer_carts_paid( $user_id ){
	global $wpdb;
	
	if( $user_id === 0 )
		return;
	
	if( $carts = $wpdb->get_results( "SELECT * FROM {$wpdb->carts} WHERE client_id LIKE '$user_id' AND status LIKE 'paid'" ) )
		return $carts;
}
/**
 * Récupération du dernier cart ayant un status "payé" pour un client donné
 * @param $user_id int : identifiant du client 
 * @return $cart Object : le dernier cart 
 */
function mktzr_forms_get_customer_last_cart_paid( $user_id ){
	global $wpdb;
	
	if( $user_id === 0 )
		return;
	
	if( $cart = $wpdb->get_row( "SELECT * FROM {$wpdb->carts} WHERE client_id LIKE '$user_id' AND status LIKE 'paid' ORDER BY date_update DESC" ) )
		return $cart;
}
/**
 * Récupération des derniers carts ayant un status "payé" pour un client donné
 * @param $user_id int : identifiant du client 
 * @param $limit int : nombre de carts à récupérer
 * @return $carts Array : Array contenant les objets carts
 */
function mktzr_forms_get_customer_last_carts_paid( $user_id, $limit ){
	global $wpdb;
	
	if( $user_id === 0 )
		return;
	
	if( $carts = $wpdb->get_results( "SELECT * FROM {$wpdb->carts} WHERE client_id LIKE '$user_id' AND status LIKE 'paid' ORDER BY date_update DESC LIMIT $limit" ) )
		return $carts;
}
/**
 * Récupération d'un cart ayant un status "payé" pour un client donné 
 * @param $user_id int : identifiant du client
 * @param $cart_id int : identifiant du cart
 * @return $cart Object : le cart correspondant au $cart_id
 */
function mktzr_forms_get_customer_cart_paid( $user_id, $cart_id ){
	global $wpdb;
	
	if( $user_id === 0 )
		return;
	
	if( $cart = $wpdb->get_row( "SELECT * FROM {$wpdb->carts} WHERE client_id LIKE '$user_id' AND status LIKE 'paid' AND id LIKE '$cart_id'" ) )
		return $cart;
}
/**
 * Récupération des meta_datas d'un cart 
 * @param $cart_id int: identifiant du cart
 * @return $cart_meta_datas Array : meta_datas correspondant au $cart_id 
 */
function mktzr_forms_cart_get_meta_datas( $cart_id ){
	$cart_meta_datas = array();
	
	if( ! $meta_datas = get_metadata( 'cart', $cart_id ) )
		return;
	
	foreach( $meta_datas as $meta_data => $value )
		$cart_meta_datas[$meta_data] = current($value) ;
	
	return $cart_meta_datas;
}

/**
 * Retourne le token hashé d'un cart
 * @param $cart Object: Objet contenant tous les informations du cart
 */
function mktzr_get_cart_token( $cart ){
	return wp_hash_password( sha1( md5( $cart->id.$cart->date_create.$cart->date_update.$cart->order_reference.$cart->price ).$cart->salt ) );
}


/**
 * Vérifie si le token hashé est égal à la concaténation des informations du cart 
 * @return TRUE si le token est égal à la concaténation des informations du cart, FALSE sinon.
 */
function mktzr_check_cart_token( ){
	if( $cart = mktzr_forms_cart_get_from_cookie() )
		$hash = sha1( md5( $cart->id.$cart->date_create.$cart->date_update.$cart->order_reference.$cart->price ).$cart->salt );
	else 
		$hash = '';

	return wp_check_password( $hash, $_REQUEST['token'] );
}
