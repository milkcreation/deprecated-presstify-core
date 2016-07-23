<?php
/**
 * ADMIN
 */
/**
 * Declaration des scripts
 */
function mktzr_mainmenu_register_scripts(){
	wp_register_style( 'mktzr-main-menu', MKTZR_URL.'/plugins/main-menu/css/main-menu.css' );	
	wp_register_script( 'mktzr-main-menu', MKTZR_URL.'/plugins/main-menu/js/main-menu.js', array( 'mktzr-jquery-tinymce' ) );	
}
add_action('admin_init', 'mktzr_mainmenu_register_scripts');

/**
 * 
 */
function mktzr_mainmenu_options_render_panel( $options ){
?>
<div class="header-image" style="background-image:url(<?php header_image(); ?>);">
	<div id="mainmenu-editor" >
		<div class="mainmenu-link" >
			<input type="hidden" name="adtc_mainmenu[homelink_display]" value="n" />
			<input type="checkbox" name="adtc_mainmenu[homelink_display]" value="y" <?php checked( $options['homelink_display'] == 'y' );?>/>
			<input type="text" name="adtc_mainmenu[homelink_text]" class="menu-entry" value="<?php echo $options['homelink_text']?>" />
		</div>
		</ul>
		<ul id="mainmenu-entries" class="sortable">	
			<?php 
				if( is_array( $options['custom_link'] ) ) :
					$customlinks = $options['custom_link'];						
					foreach( $customlinks as $index=> $datas ) 
						mktzr_mainmenu_options_entry( $index, $datas);
				endif;
			?>							
		</ul>						
		
		<div class="mainmenu-link">
			<input type="hidden" name="adtc_mainmenu[newslink_display]" value="n" />
			<input type="checkbox" name="adtc_mainmenu[newslink_display]" value="y" <?php checked( $options['newslink_display'] == 'y' );?>/>
			<input type="text" name="adtc_mainmenu[newslink_text]" value="<?php echo $options['newslink_text']?>" class="menu-entry" />
		</div>
		<div style="margin-top:10px">
			<a href="#add-mainmenu-entry" id="add-mainmenu-entry" class="button-secondary"><?php _e('Ajouter une entrée de menu', 'adtc');?></a>
		</div>
	</div>
</div>
<?php	
}

/**
 * Affichage d'une entrée de menu
 */
function mktzr_mainmenu_options_entry( $index = null, $datas = array() ){
	if( is_null($index) )	
		$index = uniqid();
	$defaults = array(
		'display' => 'y',
		'title' => '',
		'ptitle' => '',
		'ptext' => '',
		'pthumb' => 0
	);
	$datas = wp_parse_args( $datas, $defaults ); 	
?>
<li class="mainmenu-link mainmenu-customlink">
	<input type="hidden" name="adtc_mainmenu[custom_link][<?php echo $index;?>][display]" value="n" />
	<input type="checkbox" name="adtc_mainmenu[custom_link][<?php echo $index;?>][display]" value="y" <?php checked( $datas['display'] == 'y' );?>/>
	<input type="text" name="adtc_mainmenu[custom_link][<?php echo $index;?>][title]" class="menu-entry" placeholder="<?php _e('Titre de l\'entrée de menu', 'adtc');?>" value="<?php echo $datas['title'];?>" />
	<div class="customlink-panel">
		<span class="adtc-sprite main-menu-cursor cursor"></span>
		<ul >
			<li>
				<input type="text" name="adtc_mainmenu[custom_link][<?php echo $index;?>][ptitle]" class="panel-title" placeholder="<?php _e('Titre du panneau', 'adtc');?>" value="<?php echo $datas['ptitle'];?>" />										
			</li>
			<li>
				<textarea name="adtc_mainmenu[custom_link][<?php echo $index;?>][ptext]" class="panel-text" placeholder="<?php _e('Texte du panneau', 'adtc');?>"><?php echo $datas['ptext'];?></textarea>										
				<hr>
			</li>
			<li class="customlink-panel-subpart">
				<input type="text" class="search-content" placeholder="<?php _e('Choix du contenu à lier', 'pgsd');?>" data-post_type="any" data-target="#customlink-panel-links-<?php echo $index;?>"  />
				<input type="button" class="add-custom-sublink button-secondary" data-index="<?php echo $index;?>" value="<?php _e('Ajouter', 'pgsd');?>" disabled="disabled"/>
				<ul id="customlink-panel-links-<?php echo $index;?>" class="customlink-panel-links">
					<?php 
						if( ( $sublinks = adtc_get_option( 'adtc_mainmenu_sublinks' ) ) && isset( $sublinks[$index]) )
							foreach( $sublinks[$index] as $subindex => $subdatas )
								mktzr_mainmenu_options_subentry( $index, $subindex, $subdatas );				
					 ?>
				</ul>
				<a href="#" class="add-submenu-image customlink-panel-thumb" data-index="<?php echo $index;?>">
					<?php if( $datas['pthumb'] && ( $image = wp_get_attachment_image_src($datas['pthumb']) ) ): ?>
						<img src="<?php echo $image[0];?>" width="180" height="auto" />
						<input type="hidden" name="adtc_mainmenu[custom_link][<?php echo $index;?>][pthumb]" value="<?php $datas['pthumb'];?>" />'; 
					<?php endif; ?>					
				</a>									
			</li>
		</ul>
	</div>	
	<a href="#" class="mktzr_remove"><?php _e('Supprimer l\'entrée de menu', 'adtc');?></a>
	<span class="deploy"></span>
	<span class="move"></span>
</li>
<?php	
}

/**
 * Affichage d'une sous enntrée de menu
 */
function mktzr_mainmenu_options_subentry( $index, $subindex, $datas = array() ){
	if( !$index || !$subindex )
		return;	
	$defaults =array(
		'txt' => '',
		'id' => 0
	);
	$datas = wp_parse_args( $datas, $defaults );	
?>
<li>
	<textarea rows="1" name="adtc_mainmenu_sublinks[<?php echo $index;?>][<?php echo $subindex;?>][txt]" class="title"><?php echo $datas['txt'];?></textarea>
	<input type="hidden" name="adtc_mainmenu_sublinks[<?php echo $index;?>][<?php echo $subindex;?>][id]" value="<?php echo $datas['id'];?>" />
	<a href="#remove" class="mktzr_remove"></a>	
</li>
<?php					
}

/**
 * Affichage d'une entrée de menu via Ajax
 */
function adtc_theme_option_ajax_mainmenu_entry(){
	mktzr_mainmenu_options_entry();
	exit;
}
add_action( 'wp_ajax_add_mainmenu_entry', 'adtc_theme_option_ajax_mainmenu_entry' );

/**
 * Recherche par autocompletion d'une sous entrée de menu
 */
/**
 * Récupération Ajax de contenus pour l'autocompletion
 */
function adtc_theme_option_mainmenu_autocomplete_get_post(){
	$return = array();
	
	// Vérification du type de requête
	if ( isset( $_REQUEST['autocomplete_type'] ) )
		$type = $_REQUEST['autocomplete_type'];
	else
		$type = 'add';
	
	$post_type = 'any';

	$query_post = new WP_Query;
	
	$posts = $query_post->query( array(
		'post_type' => $post_type,
		's' => $_REQUEST['term'],
		'posts_per_page' => -1
		)
	);
	foreach ( $posts as $post ) {
		$return[] = array(
			'label' => $post->post_title,
			'value' => $post->post_title,
			'type' => get_post_type_object($post->post_type)->label,
			'id' => $post->ID
		);
	}
	wp_die( json_encode( $return ) );	
}
add_action( 'wp_ajax_adtc_autocomplete_mainmenu', 'adtc_theme_option_mainmenu_autocomplete_get_post' );

/**
 * GENERAL TEMPLATE
 */ 
/**
 * 
 */
function mktzr_mainmenu_display( $options ){
?>
<div id="mktzr-mainmenu" class="menu">
	<div id="mktzr-mainmenu-wrapper" class="clearfix">
	<?php if( $options['homelink_display'] == 'y' ) :?>
	<div class="mainmenu-link" >
		<a href="<?php echo home_url('/');?>" title="<?php _e('Retour à l\'accueil', 'adtc');?>" class="menu-entry">			
			<span class="text"><?php echo $options['homelink_text']?></span>			
		</a>
		<span class="adtc-sprite main-menu-separator"></span>
	</div>
	<?php endif;?>
	</ul>
	<ul id="mainmenu-entries" class="sortable">	
		<?php 
			if( is_array( $options['custom_link'] ) ) :
				$customlinks = $options['custom_link'];						
				foreach( $customlinks as $index=> $datas ) 
					mktzr_mainmenu_entry_display( $index, $datas);
			endif;
		?>							
	</ul>						
	<?php if( $options['newslink_display'] == 'y' ) :?>
	<div class="mainmenu-link">
		<a href="<?php echo ( $pfp = get_option('page_for_posts') )? get_permalink($pfp) : get_post_type_archive_link('post');?>" title="<?php _e('Voir les actualités', 'adtc');?>" class="menu-entry">	
		<?php echo $options['newslink_text']?>
		</a>
	</div>
	<?php endif;?>
	</div>
</div>
<?php	
}

/**
 * Affichage d'une entrée de menu
 */
function mktzr_mainmenu_entry_display( $index = null, $datas = array() ){
	$defaults = array(
		'display' => 'n',
		'title' => '',
		'ptitle' => '',
		'ptext' => '',
		'pthumb' => 0
	);
	$datas = wp_parse_args( $datas, $defaults ); 

if( $datas['display'] != 'y') 
	return;	
?>

<li class="mainmenu-link mainmenu-customlink">
	<a href="#customlink-panel-<?php echo $index;?>">
		<span class="text"><?php echo $datas['title'];?></span>		
	</a>
	<span class="adtc-sprite main-menu-separator"></span>
	
	<div id="customlink-panel-<?php echo $index;?>" class="customlink-panel">
		<span class="adtc-sprite main-menu-cursor cursor"></span>
		<ul>
			<?php if( $datas['ptitle'] ) :?>
			<li class="panel-title">
				<?php echo $datas['ptitle'];?>										
			</li>
			<?php endif;?>
			<?php if( $datas['ptext'] ) :?>
			<li class="panel-text">
				<?php echo $datas['ptext'];?>										
			</li>
			<?php endif;?>
			<?php if( $datas['ptitle'] || $datas['ptext'] ) :?>
			<li>
				<hr>
			</li>
			<?php endif;?>
			<li class="customlink-panel-subpart">
				<ul id="customlink-panel-links-<?php echo $index;?>" class="customlink-panel-links clearfix">
					<?php 
						if( ( $sublinks = adtc_get_option( 'adtc_mainmenu_sublinks' ) ) && isset( $sublinks[$index]) )
							foreach( $sublinks[$index] as $subindex => $subdatas )
								mktzr_mainmenu_subentry_display( $index, $subindex, $subdatas );				
					?>
				</ul>
				<?php if( $datas['pthumb'] && ( $image = wp_get_attachment_image_src($datas['pthumb']) ) ): ?>
				<div class="add-submenu-image customlink-panel-thumb">					
					<img src="<?php echo $image[0];?>" width="180" height="auto" />
				</div>
				<?php endif; ?>										
			</li>
		</ul>
	</div>
</li>
<?php	
}
/**
 * Affichage d'une sous enntrée de menu
 */
function mktzr_mainmenu_subentry_display( $index, $subindex, $datas = array() ){
	if( !$index || !$subindex )
		return;	
	$defaults =array(
		'txt' => '',
		'id' => 0
	);
	$datas = wp_parse_args( $datas, $defaults );
	if( $datas['txt'] && $datas['id'] ) :	
?>
<li>
	<a href="<?php echo get_permalink($datas['id']);?>" title="<?php printf(__('En savoir plus sur %s', 'adtc'), get_the_title($datas['id']) );?>">
		<i class="adtc-sprite list-bullet-delta-orange"></i>
		<span class="text"><?php echo $datas['txt'];?></span>
	</a>
</li>	
<?php
endif;					
}

 