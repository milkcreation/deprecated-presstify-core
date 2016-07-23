<?php
/**
 * METABOXES
 *
 * @package WordPress
 */

/**
 * Types de post
 */ 
function mkcrm_get_posttype(){
	return array( 'mkcrm_hosting', 'mkcrm_project' );
} 
 
/**
 * Chargement des scripts 
 */
function mkcrm_metabox_admin_enqueue_scripts( $hook_suffix ){
	global $post_type;	
	
	// Bypass
	if( ( $hook_suffix!= 'post.php' ) && ( $hook_suffix!= 'post-new.php' )  )	
		return;

	if( !in_array( $post_type, mkcrm_get_posttype() ) )
		return;
		
	wp_enqueue_style( 'mkcrm-styles' );
	wp_enqueue_script( 'mkcrm-scripts' );
	wp_enqueue_style( 'mkcrm-tabs-styles' );
	wp_enqueue_script( 'mkcrm-tabs-scripts' );
	
	wp_enqueue_style( 'mkcrm-hosting', MKCRM_URL.'/css/mkcrm-hosting.css' );
	wp_enqueue_script( 'mkcrm-hosting', MKCRM_URL.'/js/mkcrm-hosting.js', array('jquery'), 20140221 );
}
add_action( 'admin_enqueue_scripts', 'mkcrm_metabox_admin_enqueue_scripts' );

/**
 * Déclaration de la metaboxe de saisie des métadonnées
 */
function mkcrm_metabox_add_postbox( $post_type, $post = array() ){		
	if( in_array( $post_type, mkcrm_get_posttype() ) )
		add_action( 'edit_form_advanced', 'mkcrm_metabox_render' );
}
add_action( 'add_meta_boxes', 'mkcrm_metabox_add_postbox', null, 2 );

/**
 * Boîte de saisie des métadonnées
 */
function mkcrm_metabox_render( $post ){	
	$tab_current = array();
	if( $current =  get_user_meta( get_current_user_id(), 'mkcrm_metabox-'.$post->post_type, true ) )
		$tab_current = explode( ',',$current );	
	else
		$tab_current = 	array( 'post-details' );		
?>
<div id="mkcrm_metabox" class="postbox-container mkcrm-metabox">
	<h3 class="hndle"><span><?php _e('Options de l\'article', 'mkcrm');?></span></h3>
	<div class="mkcrm-metabox-wrapper no-sidebar">
		<div class="mkcrm-metabox-back"></div>
		<div class="mkcrm-metabox-wrap">
			<div class="tabbable tabs-left">
				<ul class="mkcrm-metabox-tabs nav nav-tabs">					
					<li class="<?php if( in_array('post-details', $tab_current ) ) echo 'active';?>">
						<a data-toggle="tab" data-current="post-details" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-details"><?php _e( 'Détails', 'mkcrm' );?></a>
					</li>
					<li class="<?php if( in_array('post-services', $tab_current ) ) echo 'active';?>">
						<a data-toggle="tab" data-current="post-services" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-services"><?php _e( 'Services', 'mkcrm' );?></a>
					</li>						
				</ul>

				<div class="mkcrm-metabox-sidebar"></div>
				
				<div class="mkcrm-metabox-tabs-wrap tab-content">									
					<div id="post-details" class="tab-pane <?php if( in_array( 'post-details', $tab_current ) ) echo 'active';?>">
						<?php mkcrm_metabox_details_render( $post, null );?>
					</div>
					<div id="post-services" class="tab-pane <?php if( in_array( 'post-services', $tab_current ) ) echo 'active';?>">
						<?php mkcrm_metabox_services_render( $post, null );?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>		
<?php
}

/**
 * DETAILS
 */
/**
 * Metaboxe de saisie des détails d'un contenu
 */
function mkcrm_metabox_details_render( $post, $box ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;		
	
	$container = 'post-details'; 
	
	$tab_current = array();
	if( $current =  get_user_meta( get_current_user_id(), 'mkcrm_metabox-'.$post->post_type, true ) )
		$tab_current = explode( ',',$current );	
	else
		$tab_current = 	array( 'post-offer' );
	
?>
	<ul class="mkcrm-metabox-topnav nav nav-tabs">
		<li class="<?php if( in_array('post-offer', $tab_current ) ) echo 'active';?>">
			<a data-toggle="tab" data-current="<?php echo $container;?>,post-offer" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-offer"><?php _e( 'Offre d\'hébergement', 'mkcrm' );?></a>
		</li>
		<li class="<?php if( in_array('post-domain_name', $tab_current ) ) echo 'active';?>">
			<a data-toggle="tab" data-current="<?php echo $container;?>,post-domain_name" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-domain_name"><?php _e( 'Noms de domaines', 'mkcrm' );?></a>
		</li>
		<li class="<?php if( in_array('post-datasheet', $tab_current ) ) echo 'active';?>">
			<a data-toggle="tab" data-current="<?php echo $container;?>,post-datasheet" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-datasheet"><?php _e( 'Fiche technique', 'mkcrm' );?></a>
		</li>
	</ul>
	<div class="mkcrm-metabox-inside tab-content">
		<div id="post-offer" class="tab-pane <?php if( in_array('post-offer', $tab_current ) ) echo 'active';?>">
			<?php mkcrm_metabox_offer_render( $post, $box );?>
		</div>
		<div id="post-domain_name" class="tab-pane <?php if( in_array('post-domain_name', $tab_current ) ) echo 'active';?>">
			<?php mkcrm_metabox_domain_name_render( $post, $box );?>
		</div>
		<div id="post-datasheet" class="tab-pane <?php if( in_array('post-datasheet', $tab_current ) ) echo 'active';?>">
			<?php mkcrm_metabox_datasheet_render( $post, $box );?>
		</div>
	</div>
<?php 		
}

/**
 * Metaboxe de saisie des détails d'un contenu
 */
function mkcrm_metabox_offer_render( $post, $box ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;		
?>	
	<table class="form-table">
		<tbody>					
			<tr valign="top" class="name">
				<th scope="row"><?php _e( 'Formule d\'hébergement', 'mkcrm' );?></th>
				<td>
					<?php $offers = get_terms( 'mkcrm_offer', array( 'hide_empty' => false, 'orderby'=>'ID', 'order'=>'ASC' ) ); ?>
					<select name="tax_input[mkcrm_offer][]">
						<option id="mkcrm_offer-none" value="" <?php selected( !$offers );?>>
							<?php _e( 'Aucune offre sélectionnée', 'mkcrm' );?>
						</option>
					<?php foreach( (array) $offers as $key => $offer ) :?>
						<option id="mkcrm_offer-<?php echo $key;?>" value="<?php echo $offer->name;?>" <?php selected( has_term( $offer->term_id, 'mkcrm_offer', $post->ID ) );?>>
							<?php echo $offer->name;?>
						</option>
					<?php endforeach;?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Renouvellement', 'mkcrm' );?></th>
				<td>
					<select name="mkcrm_meta[single][renewal]">
						<option id="mkcrm_offer-none" value="" <?php selected( ! get_post_meta( $post->ID, '_renewal', true ) );?>>
							<?php _e( 'Choisir la fréquence', 'mkcrm' );?>
						</option>
						<option id="mkcrm_offer-none" value="monthly" <?php selected( get_post_meta( $post->ID, '_renewal', true ) == "monthly" );?>>
							<?php _e( 'Mensuel', 'mkcrm' );?>
						</option>
						<option id="mkcrm_offer-none" value="quarterly" <?php selected( get_post_meta( $post->ID, '_renewal', true ) == "quarterly" );?>>
							<?php _e( 'Trimestriel', 'mkcrm' );?>
						</option>
						<option id="mkcrm_offer-none" value="half-yearly" <?php selected( get_post_meta( $post->ID, '_renewal', true ) == "half-yearly" );?>>
							<?php _e( 'Semestriel', 'mkcrm' );?>
						</option>
						<option id="mkcrm_offer-none" value="yearly" <?php selected( get_post_meta( $post->ID, '_renewal', true ) == "yearly" );?>>
							<?php _e( 'Annuel', 'mkcrm' );?>
						</option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>				
<?php	
}

/**
 * Metaboxe de saisie des noms de domaine
 */
function mkcrm_metabox_domain_name_render( $post, $box ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;		
	
	// Récupération des métadonnées multiples
	$metadatas = has_meta($post->ID);
	foreach ( $metadatas as $key => $value )
		if ( ! in_array( $metadatas[ $key ][ 'meta_key' ], array( '_domain_name' ) ) ) :
			continue;
		else :
			${$metadatas[ $key ][ 'meta_key' ]}[ $key ] = $metadatas[ $key ];
			${$metadatas[ $key ][ 'meta_key' ]}[ $key ]['meta_value'] = maybe_unserialize($metadatas[ $key ]['meta_value']);
		endif;
?>	
	<div id="domain_name-list">	
	<?php if( isset( $_domain_name ) ) : ?>
		<?php foreach( (array) $_domain_name as $dn ) : if( ! $dn['meta_value']['name'] ) continue;?>
		<div id="domain_name-<?php echo $dn['meta_id'];?>" class="domain_name ">
			<table class="form-table">
				<tbody>
					
					<tr valign="top" class="name">
						<th scope="row"><?php _e( 'Nom du domaine', 'mkcrm' );?></th>
						<td>
							<input placeholder="<?php _e('Nom du domaine', 'mkcrm')?>" type="text" name="mkcrm_meta[multi][domain_name][<?php echo $dn['meta_id'];?>][name]" value="<?php echo $dn['meta_value']['name'];?>" autocomplete="off" />
						</td>
					</tr>
					<tr valign="top" class="sub_domains">
						<th scope="row"><?php _e( 'Sous-domaines', 'mkcrm' );?></th>
						<td>
							<ul>
							<?php if( isset( $dn['meta_value']['sub'] ) && is_array( $dn['meta_value']['sub'] ) && !empty( $dn['meta_value']['sub'] ) ) :?>
								<?php foreach( $dn['meta_value']['sub'] as $sub ) :?>
								<li>
									<input type="text" name="mkcrm_meta[multi][domain_name][<?php echo $dn['meta_id'];?>][sub][]" value="<?php echo $sub;?>" autocomplete="off" size="5" />
									<a href="#del-sub" class="remove fa fa-minus-circle"></a>
									<a href="#del-sub" class="clone fa fa-plus-circle"></a>										
								</li>
								<?php endforeach;?>
							<?php else : ?>
								<li>
									<input type="text" name="mkcrm_meta[multi][domain_name][<?php echo $dn['meta_id'];?>][sub][]" value="" autocomplete="off" size="5" />
									<a href="#del-sub" class="remove fa fa-minus-circle"></a>
									<a href="#del-sub" class="clone fa fa-plus-circle"></a>										
								</li>
							<?php endif; ?>
							</ul>
						</td>
					</tr>
					<tr valign="top" class="admin">
						<th scope="row"><?php _e( 'Gestion administrative', 'mkcrm' );?></th>
						<td>
							<label><input type="radio" data-target-show="#domain_name-<?php echo $dn['meta_id'];?> .turn_over" name="mkcrm_meta[multi][domain_name][<?php echo $dn['meta_id'];?>][admin]" value="0" <?php checked( !$dn['meta_value']['admin'] );?>" /> oui</label>&nbsp;&nbsp;
							<label><input type="radio" data-target-show="#domain_name-<?php echo $dn['meta_id'];?> .turn_over" name="mkcrm_meta[multi][domain_name][<?php echo $dn['meta_id'];?>][admin]" value="2" <?php checked( $dn['meta_value']['admin'] == 2 );?>" /> automatique</label>&nbsp;&nbsp;
							<label><input type="radio" data-target-hide="#domain_name-<?php echo $dn['meta_id'];?> .turn_over" name="mkcrm_meta[multi][domain_name][<?php echo $dn['meta_id'];?>][admin]" value="1" <?php checked( $dn['meta_value']['admin'] == 1 );?>" /> non</label>
						</td>
					</tr>
					<tr valign="top" class="turn_over">
						<th scope="row"><?php _e( 'prochain renouvellement', 'mkcrm' );?></th>
						<td>
							<?php mk_touch_time( array( 'name' => 'mkcrm_meta[multi][domain_name]['.$dn['meta_id'].'][turnover]', 'time' =>false, 'selected' => mk_translate_touchtime( $dn['meta_value']['turnover'] ) ) );?>
						</td>
					</tr>
				</tbody>
			</table>
			<a href="#remove-domain" class="remove remove-domain-name fa fa-trash-o"></a>
		</div>
		<?php endforeach;?>
	<?php endif;?>
	</div>
	<div class="domain_name_edit domain_name">
		<h3><?php _e('Ajouter un domaine', 'mkcrm');?> </h3>
		<?php mkcrm_domain_name_sample(); ?>
		<a href="#add-new-domain" id="add-new-domain" class="movesample button-secondary" data-action="domain_name_sample" data-target="#domain_name-list"><?php _e('Ajouter le domaine', 'mkcrm' );?></a>
		<span class="spinner" style="display:none;"></span>
	</div>									
}

/** 
 * Sample de création d'un nouveau domaine
 */
function mkcrm_domain_name_sample(){
	$uniqid = uniqid();
?>
	<div id="domain_name-<?php echo $uniqid;?>" class="domain_name sample">
		<table class="form-table">
			<tbody>						
				<tr valign="top" class="name">
					<th scope="row"><?php _e( 'Nom du domaine', 'mkcrm' );?></th>
					<td>
						<input placeholder="<?php _e('Nom du domaine', 'mkcrm')?>" type="text" name="mkcrm_meta[multi][domain_name][<?php echo $uniqid;?>][name]" value="" autocomplete="off" />
					</td>
				</tr>
				<tr valign="top" class="sub_domains">
					<th scope="row"><?php _e( 'Sous-domaines', 'mkcrm' );?></th>
					<td>
						<ul>
							<li>
								<input type="text" name="mkcrm_meta[multi][domain_name][<?php echo $uniqid;?>][sub][]" value="@" autocomplete="off" size="5" />
								<a href="#del-sub" class="remove fa fa-minus-circle"></a>
								<a href="#del-sub" class="clone fa fa-plus-circle"></a>										
							</li>
						</ul>
					</td>
				</tr>
				<tr valign="top" class="admin">
					<th scope="row"><?php _e( 'Gestion administrative', 'mkcrm' );?></th>
					<td>
						<label><input type="radio" data-target-show="#domain_name-<?php echo $uniqid;?> .turn_over" name="mkcrm_meta[multi][domain_name][<?php echo $uniqid;?>][admin]" value="0" <?php checked( true );?>" /> oui</label>&nbsp;&nbsp;
						<label><input type="radio" data-target-show="#domain_name-<?php echo $uniqid;?> .turn_over" name="mkcrm_meta[multi][domain_name][<?php echo $uniqid;?>][admin]" value="2" <?php checked( false );?>" /> automatique</label>&nbsp;&nbsp;
						<label><input type="radio" data-target-hide="#domain_name-<?php echo $uniqid;?> .turn_over" name="mkcrm_meta[multi][domain_name][<?php echo $uniqid;?>][admin]" value="1" <?php checked( false );?>" /> non</label>
					</td>
				</tr>
				<tr valign="top" class="turn_over">
					<th scope="row"><?php _e( 'prochain renouvellement', 'mkcrm' );?></th>
					<td>
						<?php mk_touch_time( array( 'name' => 'mkcrm_meta[multi][domain_name]['.$uniqid.'][turnover]', 'time' =>false ) );?>
					</td>
				</tr>
			</tbody>					
		</table>
		<a href="#remove-domain" class="remove remove-domain-name fa fa-trash-o"></a>
	</div>
<?php
}

/** 
 * Récupération Ajax d'un sample de création de nouveau domaine
 */
function mkcrm_ajax_domain_name_sample(){ echo mkcrm_domain_name_sample(); exit; }
add_action('wp_ajax_domain_name_sample', 'mkcrm_ajax_domain_name_sample');

/**
 * 
 */
function mkcrm_metabox_datasheet_render( $post, $box ){
?>
	<table class="form-table">
		<tbody>					
			<tr valign="top" class="name">
				<th scope="row"><?php _e( 'Serveur d\'hébergement', 'mkcrm' );?></th>
				<td>
					<?php $servers = get_terms( 'mkcrm_server', array( 'hide_empty' => false, 'orderby'=>'ID', 'order'=>'ASC' ) ); ?>
					<select name="tax_input[mkcrm_server][]">
						<option id="products_cats-none" value="" <?php selected( !$servers );?>>
							<?php _e( 'Aucun hébergement', 'mkcrm' );?>
						</option>
					<?php foreach( (array) $servers as $key => $server ) :?>
						<option id="products_cats-<?php echo $key;?>" value="<?php echo $server->name;?>" <?php selected( has_term( $server->term_id, 'mkcrm_server', $post->ID ) );?>>
							<?php echo $server->name;?>
						</option>
					<?php endforeach;?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>	
<?php
}

/**
 * SERVICES
 */
/**
 * Metaboxe de saisie des services
 */
function mkcrm_metabox_services_render( $post, $box ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;		
	
	$container = 'post-services'; 
	
	$tab_current = array();
	if( $current =  get_user_meta( get_current_user_id(), 'mkcrm_metabox-'.$post->post_type, true ) )
		$tab_current = explode( ',',$current );	
	else
		$tab_current = 	array( 'post-mailing' );
	
?>
	<ul class="mkcrm-metabox-topnav nav nav-tabs">
		<li class="<?php if( in_array('post-mailing', $tab_current ) ) echo 'active';?>">
			<a data-toggle="tab" data-current="<?php echo $container;?>,post-mailing" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-mailing"><?php _e( 'Mailing', 'mkcrm' );?></a>
		</li>
		<li class="<?php if( in_array('post-ftp', $tab_current ) ) echo 'active';?>">
			<a data-toggle="tab" data-current="<?php echo $container;?>,post-ftp" data-group="mkcrm_metabox-<?php echo $post->post_type;?>" href="#post-ftp"><?php _e( 'FTP', 'mkcrm' );?></a>
		</li>
	</ul>
	<div class="mkcrm-metabox-inside tab-content">
		<div id="post-mailing" class="tab-pane <?php if( in_array('post-mailing', $tab_current ) ) echo 'active';?>">

		</div>
		<div id="post-ftp" class="tab-pane <?php if( in_array('post-ftp', $tab_current ) ) echo 'active';?>">

		</div>
	</div>
<?php 		
}
