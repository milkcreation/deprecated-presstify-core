<?php
/**
 * --------------------------------------------------------------------------------
 *	Tools
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Site Relocate
 * @package    	Worpdress Extend Milkcreation Pack
 * @copyright 	Milkcreation 2013
 * @link 		http://presstify.com/site-relocate
 * @author 		Jordy Manner
 * @version 	1.1
 */

/**
 * Création de la page de gestion des imports
 */
function mkreloc_management_menu(){	
	$option_page = add_management_page(
		__( 'Déménagement du site', 'milk-site-relocate' ),
		__( 'Déménageur de site', 'milk-site-relocate' ),
		apply_filters( 'mkreloc_tools_cap', 'manage_options' ),
		'mkreloc_tools',
		'mkreloc_tools_render_page'
	);
}
add_action( 'admin_menu', 'mkreloc_management_menu' );

/**
 * Rendu de la page d'édition des options du thème
 */
function mkreloc_tools_render_page(){
	global $wpdb;
	
	//var_dump( $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%%%s%%' LIMIT %d,%d", 'http://grand-sud-developpement.fr', 1, 20000 ) ) );
?>
	<div class="wrap">
		<div id="mkreloc-tools">	
			<ul id="controllers">
				<li>
					<a href='#' data-count_action="options" data-execute_action="relocate_options" class="action button-primary"><?php _e('Modification des options', 'milk-site-relocate');?></a>
				</li>
				<li>
					<a href='#' data-count_action="medias" data-execute_action="relocate_medias" class="action button-primary"><?php  _e('Modification des médias', 'milk-site-relocate');?></a>
				</li>
				<li>
					<a href='#' data-count_action="posts" data-execute_action="relocate_posts" class="action button-primary"><?php _e('Modification des posts', 'milk-site-relocate');?></a>
				</li>
				<li>
					<a href='#' data-count_action="postmeta" data-execute_action="relocate_postmeta" class="action button-primary"><?php _e('Modification des metadonnées de post', 'milk-site-relocate');?></a>
				</li>
			</ul>
			<div id="options">				
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label><?php _e('Url à remplacer', 'milk-site-relocate');?></label></th>
							<td>
								<input id="old_url" type="text" value="<?php echo get_option('siteurl');?>" size="60"/>					
							</td>	
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Url de remplacement', 'milk-site-relocate');?></label></th>
							<td>
								<input id="new_url" type="text" value="" size="60"/>					
							</td>	
						</tr>
					</tbody>
					
					<tbody>
						<tr valign="top">
							<th scope="row"><label><?php _e('Commencer l\'intégration à partir de la ligne', 'milk-site-relocate');?></label></th>
							<td>
								<input id="from" type="text" value="1" size="6"/>					
							</td>	
						</tr>
					</tbody>
					
					<tbody>
						<tr valign="top">
							<th scope="row"><label><?php _e( 'Arrêter l\'intégration à la ligne', 'milk-site-relocate');?></label></th>
							<td>
								<input id="to" type="text" value="" size="6"/>					
							</td>	
						</tr>
					</tbody>
					
					<tbody>
						<tr valign="top">
							<th scope="row"><label><?php _e( 'Tout afficher dans les logs (erreurs et succès)', 'milk-site-relocate');?></label></th>
							<td>
								<input id="log-all" type="checkbox" value="1"/>					
							</td>	
						</tr>
					</tbody>
					
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label><?php _e( 'Traitement par lot', 'lds');?></label>
								<br /><em style="font-size:10px; line-height:8px;"><?php _e('Une requête lance plusieurs enregistrements à la fois', 'milk-site-relocate');?></em> 
							</th>
							<td>
								<input id="batch" type="text" value="" size="2"/>					
							</td>	
						</tr>
					</tbody>
				</table>
				
				<div id="progress">
					<div id="progressbar"><div class="label"><?php _e('Chargement', 'milk-site-relocate');?></div></div>				
					<a href="#" id="interrupt" class="button-secondary"><?php _e('Interruption', 'milk-site-relocate');?></a>
					<div id="details">
						<h3><?php _e('Détails du déroulement de l\'import', 'milk-site-relocate');?></h3>
						<div class="detail">
							<label><?php _e( 'Enregistrement(s) en cours de traitement:', 'milk-site-relocate');?></label>
							<div id="countdown" class="detail-valor"></div>
							<div class="clear"></div>
						</div>
						<div class="detail">
							<label><?php _e( 'Temps restant :', 'lds');?></label>
							<div id="estimated-time" class="detail-value"></div>
							<div class="clear"></div>
						</div>
						<div class="detail">
							<label><?php _e( 'Dernier(s) enregistrement(s) traité(s) :', 'milk-site-relocate');?></label>
							<div id="last-registred" class="detail-value"></div>
							<div class="clear"></div>
						</div>
						
					</div>
				</div>
				
			</div>
			
			<div id="logs"></div>
		</div>
	</div>		
<?php
}

/**
 * Scripts de la page d'édition des options du thème
 */
function mkreloc_tools_enqueue_scripts( $hookname ){
	if( $hookname != 'tools_page_mkact_tools' )
		return;
	if( ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] != 'relocate' ) 
		return;
	wp_enqueue_style( 'jquery-ui-progressbar', MKRELOC_URL.'/css/jquery.ui.progressbar.min.css' );
	wp_enqueue_script( 'jquery-ui-progressbar' );	
	wp_enqueue_script( 'mkreloc-tools', MKRELOC_URL.'/js/tools.js', array('jquery'), '20130613', true );
	wp_enqueue_style( 'mkreloc-tools', MKRELOC_URL.'/css/tools.css', array(), '20130613' );
}
add_action('admin_enqueue_scripts', 'mkreloc_tools_enqueue_scripts' );

/**
 * Compte le nombre d'options concernés
 */
function mkreloc_count_options( $old_url ){
	global $wpdb;
	
	$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_id) FROM {$wpdb->options} WHERE option_value LIKE '%%%s%%'", $old_url ) );	

	return $res;	
}

/**
 * Compte le nombre de médias concernés
 */
function mkreloc_count_medias( $old_url ){
	global $wpdb;
	
	$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE '%%%s%%'", $old_url ) );	

	return $res;	
}

/**
 * Compte le nombre de post concernés
 */
function mkreloc_count_posts( $old_url ){
	global $wpdb;
	
	$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type != 'attachment' AND post_content LIKE '%%%s%%'", $old_url ) );	

	return $res;	
}

/**
 * Compte le nombre de meta concernés
 */
function mkreloc_count_postmeta( $old_url ){
	global $wpdb;
	
	$res = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_id) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%%%s%%'", $old_url ) );	

	return $res;	
}

/**
 * Compte ajax
 */
function mkreloc_ajax_count_action( ){
	if( empty( $_POST['count_action'] ) || empty( $_POST['old_url'] ) )
		die(0);
	
	$res = 0;	
	switch( $_POST['count_action'] ) :
		case 'options' :
			$res = mkreloc_count_options( $_POST['old_url'] );
			break;
		case 'medias' :
			$res = mkreloc_count_medias( $_POST['old_url'] );
			break;
		case 'posts' :
			$res = mkreloc_count_posts( $_POST['old_url'] );
			break;
		case 'postmeta' :
			$res = mkreloc_count_postmeta( $_POST['old_url'] );
			break;				
	endswitch;
	
	echo json_encode( $res );
	exit;	
}
add_action( 'wp_ajax_count_relocate_action', 'mkreloc_ajax_count_action' );

/**
 * Remplacement des options
 */
function mkreloc_parse_args(){
	//bypass	
	if( empty( $_POST['from'] )
		|| empty( $_POST['to'] )
		|| empty( $_POST['nb'] )
		|| empty( $_POST['old_url'] )
		|| empty( $_POST['new_url'] )
	)
		return;
	
	// Récupération des arguments de la requête
	$from = (int) $_POST['from'] - 1;
	$to = (int) $_POST['to'];
	$nb = ! empty( $_POST['nb'] )? (int)$_POST['nb']: 1;
	if( $from+$nb > $to )
		$nb = $to - $from;
		
	$old_url = $_POST['old_url'];
	$new_url = $_POST['new_url'];	
	
	return compact( 'from', 'to', 'nb', 'old_url', 'new_url' ); 		
} 

/**
 * 
 */
function mkreloc_ajax_relocate_options(){
	global $wpdb;
		
	$response = array( 'html' => '', 'error' => '' );

	if( ! $args = mkreloc_parse_args() ) :
		$response['error'] .= "<p>&Eeacute;rreur système - veuillez recommencer</p>";
	else :
		extract($args);		
		
		$response = array( 'html' => '', 'error' => '' );
		$option_ids = $wpdb->get_col( $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_value LIKE '%%%s%%' ORDER BY option_id DESC LIMIT %d,%d ", $old_url, $from, $nb ) );
				
		foreach( $option_ids as $option_id ) :		
			if( $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = replace(option_value, '%s', '%s') WHERE option_id = %d", $old_url, $new_url, $option_id ) ) ) 
				$response['html'] .= "<p>Le contenu {$option_id} a été modifié avec succès</p>";
			else
				$response['error'] .= "<p>&Eacute;chec de modification de {$option_id}</p>";
		endforeach;			
	endif;
	
	echo json_encode( $response );
	exit;	
}
add_action( 'wp_ajax_relocate_options', 'mkreloc_ajax_relocate_options' );
 
/**
 * 
 */ 
function mkreloc_ajax_relocate_medias(){
	global $wpdb;
		
	$response = array( 'html' => '', 'error' => '' );

	if( ! $args = mkreloc_parse_args() ) :
		$response['error'] .= "<p>&Eeacute;rreur système - veuillez recommencer</p>";
	else :
		extract($args);		
		
		$response = array( 'html' => '', 'error' => '' );
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE '%%%s%%' LIMIT %d,%d", $old_url, $from, $nb ) );
				
		foreach( $post_ids as $post_id ) :		
			if( $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET guid = replace(guid, '%s', '%s') WHERE ID = %d", $old_url, $new_url, $post_id ) ) ) 
				$response['html'] .= "<p>Le contenu {$post_id} a été modifié avec succès</p>";
			else
				$response['error'] .= "<p>&Eacute;chec de modification de {$post_id}</p>";	
		endforeach;			
	endif;
	
	echo json_encode( $response );
	exit;	
}
add_action( 'wp_ajax_relocate_medias', 'mkreloc_ajax_relocate_medias' );

/**
 * 
 */
function mkreloc_ajax_relocate_posts(){
	global $wpdb;
		
	$response = array( 'html' => '', 'error' => '' );

	if( ! $args = mkreloc_parse_args() ) :
		$response['error'] .= "<p>&Eeacute;rreur système - veuillez recommencer</p>";
	else :
		extract($args);		
		
		$response = array( 'html' => '', 'error' => '' );
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type != 'attachment' AND post_content LIKE '%%%s%%' LIMIT %d,%d", $old_url, $from, $nb ) );
				
		foreach( $post_ids as $post_id ) :		
			if( $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = replace(post_content, '%s', '%s') WHERE ID = %d", $old_url, $new_url, $post_id ) ) ) 
				$response['html'] .= "<p>Le contenu {$post_id} a été modifié avec succès</p>";
			else
				$response['error'] .= "<p>&Eacute;chec de modification de {$post_id}</p>";
		endforeach;			
	endif;
	
	echo json_encode( $response );
	exit;	
}
add_action( 'wp_ajax_relocate_posts', 'mkreloc_ajax_relocate_posts' );

/**
 * 
 */
function mkreloc_ajax_relocate_postmeta(){
	global $wpdb;
		
	$response = array( 'html' => '', 'error' => '' );

	if( ! $args = mkreloc_parse_args() ) :
		$response['error'] .= "<p>&Eeacute;rreur système - veuillez recommencer</p>";
	else :
		extract($args);		
		
		$response = array( 'html' => '', 'error' => '' );
		$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%%%s%%' LIMIT %d,%d", $old_url, $from, $nb ) );
				
		foreach( $meta_ids as $meta_id ) :		
			if( $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = replace(meta_value, '%s', '%s') WHERE meta_id = %d", $old_url, $new_url, $meta_id ) ) ) 
				$response['html'] .= "<p>Le contenu {$meta_id} a été modifié avec succès</p>";
			else
				$response['error'] .= "<p>&Eacute;chec de modification de $meta_id}</p>";
		endforeach;			
	endif;
	
	echo json_encode( $response );
	exit;	
}
add_action( 'wp_ajax_relocate_postmeta', 'mkreloc_ajax_relocate_postmeta' );