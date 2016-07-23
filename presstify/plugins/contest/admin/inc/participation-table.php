<?php
class tiFy_Contest_Participation_AdminListTable extends tiFy_AdminView_List_Table{
	/* = PARAMETRES = */
	private	// Référence
			$master,
			$main;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->participation;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_contest_participant',
	            'plural'    => 'tify_contest_participants',
	            'ajax'      => true,
	            'screen'	=> $this->master->admin->hookname['participation']
        	),
        	$this->master->db_participation 
		);
	}
	
	/* = CONFIGURATION = */
	/** == Définition des status == **/
	function set_status(){
		return 	array( 
			'available' 		=> array(			
				'publish'			=> __( 'Approuvé (en ligne)', 'tify' ),
				'moderate'			=> __( 'A modérer (en attente)', 'tify' ),
				'trash'				=> __( 'Corbeille', 'tify' )
			)
		);
	}
	
	/* = ORGANES DE NAVIGATION = */	
	/** == Filtrage avancé  == **/
	protected function extra_tablenav( $which ) {

	}
	
	/* = AFFICHAGE = */	
	/** == COLONNES == **/
	/*** === Liste des colonnes === ***/
	public function get_columns(){
		return array(
			'recipe' 			=> __( 'Recette', 'tify' ),	
			'ranking' 			=> __( 'Classement', 'tify' ),	
			'poll' 				=> __( 'Votes', 'tify' ),
			'part_details' 		=> __( 'Détails de la participation', 'tify' ),
			'user_id' 			=> __( 'Proposé par', 'tify' ),
			'social_actions' 	=> __( 'Partages', 'tify' )
		);
	}
	
	/*** === Colonne Nom de la recette === ***/
	public function column_recipe( $item ){
		$img = "";
		if( $recipe_image = $this->db->get_item_meta( $item->part_id, 'recipe_image' ) ) :
			$img = "<img src=\"". tiFy_Utils::get_context_img_src( $recipe_image['upload_dir']['path'] . $recipe_image['name'], 60, 60, true ) ."\" width=\"60\" height=\"60\" style=\"width:60px;height:60px;object-fit:cover;margin-right:5px;float:left;\"/>";
		endif;
				
		$output  = "";
		$output .= $this->db->get_item_meta( $item->part_id, 'recipe_name' );
		
		$actions = array();
		if( $item->part_status !== 'trash' ) :
			if( $item->part_status === 'publish' ) :
				$actions['unapproved'] = "<a href=\"". 
		        					wp_nonce_url( 
		        						add_query_arg( 
		        							array( 
		        								'page' 					=> $_REQUEST['page'], 
		        								'action' 				=> 'unapproved', 
		        								$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
		        								'_wp_http_referer' 		=> strtok( urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), "#") 
											),
											admin_url( 'admin.php' ) 
										),
										$this->actions_prefix .'_unapproved_'. $item->{$this->db->primary_key}
									) 
									."\" title=\"". __( 'Désapprouver l\'élément', 'tify' ) ."\" style=\"color: #d98500;\">". 
									__( 'Désapprouver', 'tify' ) 
									."</a>";
			endif;
				
			if( $item->part_status === 'moderate' ) :
				if( ! $this->master->edit_locked )
					$actions['approved'] = "<a href=\"". 
			        					wp_nonce_url( 
			        						add_query_arg( 
			        							array( 
			        								'page' 					=> $_REQUEST['page'], 
			        								'action' 				=> 'approved', 
			        								$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
			        								'_wp_http_referer' 		=> strtok( urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), "#")  
												),
												admin_url( 'admin.php' ) 
											),
											$this->actions_prefix .'_approved_'. $item->{$this->db->primary_key}
										) 
										."\" title=\"". __( 'Approuver l\'élément', 'tify' ) ."\" style=\"color: #006505;\">". 
										__( 'Approuver', 'tify' ) 
										."</a>";
				if( ! $this->master->edit_locked )
					$actions['trash'] = "<a href=\"". 
			        					wp_nonce_url( 
			        						add_query_arg( 
			        							array( 
			        								'page' 					=> $_REQUEST['page'], 
			        								'action' 				=> 'trash', 
			        								$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
			        								'_wp_http_referer' 		=> strtok( urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), "#") 
												),
												admin_url( 'admin.php' ) 
											),
											$this->actions_prefix .'_trash_'. $item->{$this->db->primary_key}
										) 
										."\" title=\"". __( 'Mise à la corbeille de l\'élément', 'tify' ) ."\">". 
										__( 'Mettre à la corbeille', 'tify' ) 
										."</a>";
			endif;	
									
			$actions['view'] = "<a href=\"". 
	        						add_query_arg( 
	        							array( 
											'tify_template'			=> 'participation',
	        								'tify_contest_part' 	=> $item->{$this->db->primary_key}, 
										),
										site_url( '/' ) 
									) 
								."\" title=\"". __( 'Voir l\'élément', 'tify' ) ."\" target=\"_blank\">". 
								__( 'Afficher', 'tify' ) 
								."</a>";
								
			$output = sprintf('<a class="row-title" href="#">%1$s</a>', $output );					
		elseif( ! $this->master->edit_locked ) :										
			$actions['untrash'] = "<a href=\"". 
	        					wp_nonce_url( 
	        						add_query_arg( 
	        							array( 
	        								'page' 					=> $_REQUEST['page'], 
	        								'action' 				=> 'untrash', 
	        								$this->db->primary_key	=> $item->{$this->db->primary_key}, 
	        								'_wp_http_referer' 		=> strtok( urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), "#") 
										),
										admin_url( 'admin.php' ) 
									),
									$this->actions_prefix .'_untrash_'. $item->{$this->db->primary_key} 
								) 
								."\" title=\"". __( 'Rétablissement de l\'élément', 'tify' ) ."\">". 
								__( 'Rétablir', 'tify' ) 
								."</a>";					
			$actions['delete'] = "<a href=\"". 
	        					wp_nonce_url( 
	        						add_query_arg( 
	        							array( 
	        								'page' 					=> $_REQUEST['page'], 
	        								'action' 				=> 'delete', 
	        								$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
	        								'_wp_http_referer' 		=> strtok( urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), "#")  
										),
										admin_url( 'admin.php' ) 
									),
									$this->actions_prefix .'_delete_'. $item->{$this->db->primary_key} 
								) 
								."\" title=\"". __( 'Suppression définitive de l\'élément', 'tify' ) ."\">". 
								__( 'Supprimer définitivement', 'tify' ) 
								."</a>";
								
			$output = sprintf('<strong>%1$s</strong>', $output );					
		endif;
		
		
		return sprintf('%1$s%2$s', $img, "<div style=\"margin-left:65px;\">". $output . $this->row_actions( $actions ) ."</div>" );
	}
	
	/*** === Colonne Classement === ***/
	public function column_ranking( $item ){
		echo "<span style=\"font-size:40px;font-weight:900;display:block;text-align:center;margin-top:10px;\">". tiFy_Contest_Ranking::part_pos( $item->part_id ) ."</span>";
	}	
	
	/*** === Colonne Détails des votes === ***/
	public function column_poll( $item ){
		$total = 0;
		// Vote du site
		$site_poll 		= (int) tiFy_Contest_Poll::part_count( $item->part_id );
		$total += $site_poll;
		// Bonus image selfie
		$bonus_likes	= (int) $this->db->get_item_meta( $item->part_id, 'bonus_image_likes' );
		$total += $bonus_likes;
		
		$output  = "";
		$output .= "<ul style=\"margin:0; width:150px; float:left;\">";
		$output .= "\t<li><b style=\"font-size:14px;\">Vote sur le site : </b>". $site_poll ."</li>";
		$output .= "\t<li><b style=\"font-size:14px;\">Bonus Selfie : </b>". $bonus_likes ."</li>";
		$output .= "\t<li>";
		$output .= "\t\t<h3 style=\"margin:0;font-size:14px;\">Facebook</h3>";
		$output .= "\t\t\t<ul>";
		// Vote sur la fanpage
		if( $this->master->social->is_page_feed( $item->part_contest_id ) ) :
			$fb_page_likes = (int) $this->db->get_item_meta( $item->part_id, 'fb_page_feed_post_likes' );
			$total += $fb_page_likes;
			$output .= "\t\t\t\t<li style=\"margin:0;font-size:11px;\">Likes de la fanpage : ". $fb_page_likes ."</li>";
		endif;
		// Likes page utilisateur facebook
		if( $this->master->social->is_user_feed( $item->part_contest_id ) ) : 
			$fb_user_likes = (int) $this->db->get_item_meta( $item->part_id, 'fb_user_feed_post_likes' );
			$total += $fb_user_likes;
			$output .= "\t\t\t\t<li style=\"margin:0;font-size:11px;\">Likes des visiteurs : ". $fb_user_likes ."</li>";
		endif;
		$output .= "\t\t\t</ul>";
		$output .= "\t</li>";
		$output .= "</ul>";
		$output .= "<div style=\"float:right;font-size:32px;font-weight:900;display:block;text-align:center;margin-top:10px; letter-spacing:-3px;\" >{$total}</div>";
		
		echo $output;
	}
	
	/*** === Colonne Détails du formulaire === ***/
	public function column_part_details( $item ){
		echo 	"<b>ID: {$item->part_contest_id}</b>" .
				"<span style=\"display:block;font-size:0.9em;\">". mysql2date( 'd/m/Y à H:i', $item->part_date ) ."</span>".
				"<em style=\"display:block;font-size:0.8em;color:#666;line-height:1;\">". sprintf( __( 'Session: %s', 'tify' ), $item->part_session ) ."</em>";				
	}
	
	/*** === Colonne Utilisateur === ***/
	public function column_user_id( $item ){
		$output = "";
		
		if( ( $bonus_image = $this->db->get_item_meta( $item->part_id, 'bonus_image' ) ) )
			if( $src = tiFy_Utils::get_context_img_src( $bonus_image['upload_dir']['path'] . $bonus_image['name'], 50, 50, true ) )
				$output .= "<img src=\"{$src}\" width=\"50\" height=\"50\" style=\"width:50px;height:50px;object-fit:cover;margin-right:5px;float:left;\"/>";
		
		// Image de remplacement
		if( ! $output ) :
			$url = ( is_ssl() ) ? 'https://secure.gravatar.com/avatar/' : sprintf( 'http://%d.gravatar.com/avatar/', rand( 0,2 ) );
			$url = add_query_arg( array( 'f'=> 'y', 'd' => 'mm', 's' => 50 ), $url );
			$output .= "<img src=\"". $url ."\" width=\"50\" height=\"50\" style=\"width:50px;height:50px;object-fit:cover;margin-right:5px;float:left;\"/>";	
		endif;
		
		$user = get_userdata( $item->part_user_id );
		$output .= 	"<ul style=\"margin:0; padding:0;\">".
						"<li style=\"margin:0; padding:0; ". ( ! $user ? 'color:red;' : '' ) ."\"><b>". ( $user ? $user->nickname : __( 'L\'utilisateur n\'existe plus', 'tify' ) ) ."</b></li>".
						"<li style=\"margin:0; padding:0;\"><i style=\"font-size:0.9em;color:#666;\">". ( $user ? $user->user_email : __( 'Email indisponible', 'tify' ) ) ."</i></li>".
					"</ul>";
		echo $output;
	}

	/*** === Colonne de Partage === ***/
	public function column_social_actions( $item ){
		$output = "";

		if( $item->part_status !== 'publish' ) :
			$output = _e( 'Vous ne pouvez pas partager une participation en attente de modération', 'tify' );
		else :
			// PARTAGE FACEBOOK
			if( $this->master->social->is_page_feed( $item->part_contest_id ) ) :
				$fb_page_feed 	= wp_nonce_url( 
									add_query_arg( 
										array( 
											'page' 					=> $_REQUEST['page'], 
											'action' 				=> 'facebook_page_feed', 
											$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
											'_wp_http_referer' 		=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) 
										),
										admin_url( 'admin.php' ) 
									),
									$this->actions_prefix .'_facebook_page_feed_'. $item->{$this->db->primary_key} 
								);		
				$fb_offline 	= wp_nonce_url( 
									add_query_arg( 
										array( 
											'page' 					=> $_REQUEST['page'], 
											'action' 				=> 'facebook_offline', 
											$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
											'_wp_http_referer' 		=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) 
										),
										admin_url( 'admin.php' ) 
									),
									$this->actions_prefix .'_facebook_offline_'. $item->{$this->db->primary_key} 
								);
				$fb_likes = wp_nonce_url( 
									add_query_arg( 
										array( 
											'page' 					=> $_REQUEST['page'], 
											'action' 				=> 'facebook_likes', 
											$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
											'_wp_http_referer' 		=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) 
										),
										admin_url( 'admin.php' ) 
									),
									$this->actions_prefix .'_facebook_likes_'. $item->{$this->db->primary_key} 
								);
				
				$fb_published = $this->db->get_item_meta( $item->part_id, 'fb_page_feed_post_id', true );
				$fburl =  ! $fb_published ? $fb_page_feed : '#';
				
				// BONUS SELFIE
				$has_bonus = (int) $this->db->get_item_meta( $item->part_id, 'bonus_image_likes' );
				$bonus_url = 		( $has_bonus )	
									? 	wp_nonce_url( 
											add_query_arg( 
												array( 
													'page' 					=> $_REQUEST['page'], 
													'action' 				=> 'bonus_remove', 
													$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
													'_wp_http_referer' 		=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) 
												),
												admin_url( 'admin.php' ) 
											),
											$this->actions_prefix .'_bonus_remove_'. $item->{$this->db->primary_key} 
										)
									: 	wp_nonce_url( 
											add_query_arg( 
												array( 
													'page' 					=> $_REQUEST['page'], 
													'action' 				=> 'bonus_add', 
													$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
													'_wp_http_referer' 		=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) 
												),
												admin_url( 'admin.php' ) 
											),
											$this->actions_prefix .'_bonus_add_'. $item->{$this->db->primary_key} 
										)
									;
									
				// EMAIL PARTICIPATION APPROUVEE
				$email_sended = (int) $this->db->get_item_meta( $item->part_id, 'mail_approved_send' );
				$email_url = 	wp_nonce_url( 
									add_query_arg( 
										array( 
											'page' 					=> $_REQUEST['page'], 
											'action' 				=> 'mail_approved', 
											$this->db->primary_key 	=> $item->{$this->db->primary_key}, 
											'_wp_http_referer' 		=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) 
										),
										admin_url( 'admin.php' ) 
									),
									$this->actions_prefix .'_mail_approved_'. $item->{$this->db->primary_key} 
								);
						
				$output .= "<ul style=\"margin:0; padding:0;\">\n";
				$output .= "\t<li>";
				$output .= "\t\t<a href=\"{$fburl}\" style=\"". ( $fb_published ? '' : 'opacity:0.5;') ."\">";		
				$output .= "\t\t\t<span class=\"dashicons dashicons-facebook\"></span> ". ( $fb_published ? __( 'Publiée sur la fanpage du jeu', 'deficl' )  : __( 'Mettre en ligne sur la fanpage du jeu', 'deficl' ) );
				$output .= "\t\t</a>";
				if( $fb_published )
					$output .= "<ul><li style=\"float:left;line-height:1\"><a href=\"$fb_offline\" style=\"font-size:0.8em;color:#a00;\">". __( 'Déclarer comme dépubliée de la fanpage <br>(utilisateur avancé)', 'tify' ) ."</a></li></ul>";		
				$output .= "\t<li>\n";
				$output .= "\t<li style=\"clear:both;\">";
				$output .= "\t\t<a href=\"{$bonus_url}\" style=\"". ( $has_bonus ? '' : 'opacity:0.5;') ."\">". ( ! $has_bonus ? "<span class=\"dashicons dashicons-thumbs-up\"></span> ". __( 'Ajouter les 150 points de bonus Selfie', 'tify' ) : "<span class=\"dashicons dashicons-thumbs-down\"></span> ".__( 'Supprimer les 150 points de bonus Selfie', 'tify' ) ) ."</a>";
				$output .= "\t</li>";
				$output .= "\t<li style=\"clear:both;\">";
				$output .= "\t\t<a href=\"{$email_url}\" style=\"". ( $email_sended ? '' : 'opacity:0.5;') ."\"><span class=\"dashicons dashicons-email\"></span> ". __( 'Envoyer l\'email d\'approbation au participant', 'tify' ) ."</a>";
				$output .= "\t</li>";
				$output .= "</ul>\n";
			endif;
		endif;
		
		echo $output;
	}

	/* = TRAITEMENT DES ACTIONS = */
	function process_bulk_action(){
		if( $this->current_action() ) :
			switch( $this->current_action() ) :
				case 'delete' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_delete_'. $item_id );
					$this->db->delete_item( $item_id );
					$this->db->delete_item_metadatas( $item_id );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'deleted', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;				
				case 'trash' :
					$item_id = (int) $_GET[$this->db->primary_key];		
					check_admin_referer( $this->actions_prefix .'_trash_'. $item_id );
									
					// Récupération du statut original de la campagne et mise en cache
					if( $original_status = $this->db->get_item_var_by_id( $item_id, 'status' ) )
						$this->db->update_item_meta( $item_id, '_trash_meta_status', $original_status );
					
					// Modification du statut de la campagne
					$this->db->update_item( $item_id, array( 'part_status' => 'trash' ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'trashed', $sendback );
									
					wp_redirect( $sendback );
					exit;
				break;
				case 'untrash' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_untrash_'. $item_id );
					
					// Récupération du statut original de la campagne et suppression du cache
					$original_status = ( $_original_status = $this->db->get_item_meta( $item_id, '_trash_meta_status', true ) ) ? $_original_status : 'draft';				
					if( $_original_status ) $this->db->delete_item_meta( $item_id, '_trash_meta_status' );
					
					// Récupération du statut de la campagne
					$this->db->update_item( $item_id, array( 'part_status' => $original_status ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'untrashed', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'approved' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
										
					// Mise à jour du statut
					$this->db->update_item( $item_id, array( 'part_status' => 'publish' ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'approved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'unapproved' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
										
					// Mise à jour du statut
					$this->db->update_item( $item_id, array( 'part_status' => 'moderate' ) );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'unapproved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'update_ranking' :
					check_admin_referer( 'tify_contest_'. $this->current_action() .'_'. get_current_user_id() );

					$this->master->tasks->update_ranking();
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'unapproved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				
				// Social
				/// Partage sur la fanpage du jeu concours		
				case 'facebook_page_feed' :					
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
					
					if( ! $item = $this->db->get_item_by_id( $item_id ) )
						return;	
					
					$contest_id = $item->part_contest_id;
											
					if( ! $fb_page_id = $this->master->social->get_page_feed( $contest_id ) )
						return;
					if( ! $page_feed_access_token = $this->master->social->get_page_feed_access_token( $contest_id ) )
						return;
					
					$redirect = wp_get_referer();
					$error = false;

					$linkData = array(
						'link' 			=> add_query_arg( array( 'tify_template' => 'participation', 'tify_contest_part' => $item->part_id ), site_url( '/' ) ),
						'name'			=> sprintf( __( '%s proposée par %s', 'tify' ), strip_tags( html_entity_decode( $this->db->get_item_meta( $item->part_id, 'recipe_name' ) ) ), get_userdata( $item->part_user_id )->nickname ), 
						'description'	=> esc_attr( tify_excerpt( html_entity_decode( $this->db->get_item_meta( $item->part_id, 'recipe_description' ) ) ) ),
					
						'message' 		=> 	sprintf( __( 'Faites comme "%s" et osez relever le défi lancé par Cyril Lignac !', 'tify' ), get_userdata( $item->part_user_id )->nickname ) // 
					);
					
					try {
						$response = tify_facebook_sdk()->post( $fb_page_id. '/feed', $linkData, $page_feed_access_token );
					} catch(Facebook\Exceptions\FacebookResponseException $e) {
						$error = array( 'title' => 'Graph returned an error', 'message' => $e->getMessage() );
						exit;
					} catch(Facebook\Exceptions\FacebookSDKException $e) {
						$error = array( 'title' => 'Facebook SDK returned an error', 'message' => $e->getMessage() );
						exit;
					}
					if( $error ) 
						wp_die( '<h2>'. $error['title'] .'</h2>'.
								'<p>'. $error['message'] .'</p>'.
								'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
								$error['title'], 
								500 
						);					
					$graphNode = $response->getGraphNode();
					$this->db->add_item_meta( $item->part_id, 'fb_page_feed_post_id', $graphNode['id'], true );
		
					$sendback = remove_query_arg( array( 'action', 'action2' ), $redirect );
					$sendback = add_query_arg( 'message', 'fb_page_feed_published', $sendback );
					
					wp_redirect( $sendback );
				break;
				case 'facebook_offline' :					
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
					
					if( ! $item = $this->db->get_item_by_id( $item_id ) )
						return;					
					if( ! $post_id = $this->db->get_item_meta( $item->part_id, 'fb_page_feed_post_id', true ) )
						return;
					
					$redirect = wp_get_referer();
					$error = false;
					
					$this->db->delete_item_meta( $item->part_id, 'fb_page_feed_post_id' );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), $redirect );
					$sendback = add_query_arg( 'message', 'fb_offline', $sendback );
					
					wp_redirect( $sendback );
				break;
				case 'bonus_add' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
										
					// Mise à jour du statut
					$this->db->add_item_meta( $item_id, 'bonus_image_likes', 150 );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'approved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				case 'bonus_remove' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
										
					// Mise à jour du statut
					$this->db->delete_item_meta( $item_id, 'bonus_image_likes' );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'approved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;
				// Email d'approbation
				case 'mail_approved' :
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
					
					$view_url = add_query_arg( 
						array( 
							'tify_template'			=> 'participation',
							'tify_contest_part' 	=> $item_id, 
						),
						site_url( '/' ) 
					);
					$user_id = $this->db->get_item_var_by_id( $item_id, 'user_id' );
					if( ! $user_email = get_userdata( $user_id )->user_email )
						wp_die( __( 'Impossible de définir l\'email du destinataire', 'tify' ), __( 'Email introuvable', 'tify' ), 440 );
						
					// Envoi du mail					
					tify_require_lib( 'mailer' );
					$tiFy_Mailer = new tiFy_Mailer(
						array(
							'to'   			=> 	array( 'email' => $user_email ),
							'from' 			=> 	array( 'name' => get_bloginfo('blogname'), 'email' => 'noreply@ledefidecyril.fr' ),							
							'subject' 		=> 	__( get_bloginfo('blogname').' | Votre participation est en ligne', 'deficl' ),
							'html' 			=> 	"<table width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n".
													"<tbody>\n".
														"<tr>\n".
															"<td style=\"width:600px;\">\n".
																"<img src=\"". get_template_directory_uri() ."/images/mail-header-approved-min.png\" style=\"width:100%; margin-bottom:50px;\" />\n".
															"</td>\n".
														"</tr>\n".	
														"<tr>\n".
												 			"<td style=\"width:600px;\">\n".
																"<p class=\"century\" style=\"color:#000000;letter-spacing:1px;margin-bottom:5px;\">\n".
																	__( 'Bravo !<br> Cliquez sur le lien ci-dessous pour partager la recette et inviter vos amis à voter.', 'deficl' ) ."\n".
																"</p>\n".					
																"<p style=\"margin-bottom:30px;\">\n".
																	"<a href=\"{$view_url}\" class=\"century\" style=\"font-size:13px;line-height:1.1;\">{$view_url}</a>\n".
																"</p>\n".
																"<p class=\"century\" style=\"color:#000000;letter-spacing:1px;margin-bottom:5px;\">\n".
																	__( 'Bonne chance !', 'deficl' ) ."\n".
																"</p>\n".
															"</td>\n".
														"</tr>\n".	
													"</tbody>\n".
												"</table>\n",							
							'custom_css'	=> 	"<style type='text/css'>\n".
			        								".century {\n".
														"font-family:'Century Gothic',CenturyGothic,AppleGothic,sans-serif;\n".
		 												"font-size:14px;\n".
														"font-style:normal;\n".
														"font-variant:normal;\n".
														"font-weight:500;\n".
														"line-height:1.2;\n".
													"}\n".  
												"</style>\n",
						) 
					);
							
					$this->db->add_item_meta( $item_id, 'mail_approved_send', true );					
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
					$sendback = add_query_arg( 'message', 'approved', $sendback );
	
					wp_redirect( $sendback );
					exit;
				break;				
				
				/*case 'facebook_likes' :					
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
					
					if( ! $item = $this->db->get_item_by_id( $item_id ) )
						return;							
					
					if( ! $post_id = $this->db->get_item_meta( $item->part_id, 'fb_page_feed_post_id', true ) )
						return;
					
					$redirect = wp_get_referer();
					$error = false;
					
					try {
						$response = tify_facebook_sdk()->get( '/'. $post_id  .'/likes?summary=true', get_user_meta( get_current_user_id(), 'fb_page_access_token', true ) );
					} catch(Facebook\Exceptions\FacebookResponseException $e) {
						$error = array( 'title' => 'Graph returned an error', 'message' => $e->getMessage() );
					} catch(Facebook\Exceptions\FacebookSDKException $e) {
						$error = array( 'title' => 'Facebook SDK returned an error', 'message' => $e->getMessage() );
					}
					if( $error ) 
						wp_die( '<h2>'. $error['title'] .'</h2>'.
								'<p>'. $error['message'] .'</p>'.
								'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
								$error['title'], 
								500 
						);
					
					$total_count = $response->getGraphEdge()->getTotalCount();
					$this->db->update_item_meta( $item->part_id, 'fb_page_feed_post_likes', $total_count );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), $redirect );
					$sendback = add_query_arg( 'message', 'fb_likes', $sendback );
					
					wp_redirect( $sendback );
				break;				
				case 'facebook_delete' :					
					$item_id = (int) $_GET[$this->db->primary_key];			
					check_admin_referer( $this->actions_prefix .'_'. $this->current_action() .'_'. $item_id );
					
					if( ! $item = $this->db->get_item_by_id( $item_id ) )
						return;							
					
					if( ! $post_id = $this->db->get_item_meta( $item->part_id, 'fb_page_feed_post_id', true ) )
						return;
					
					$redirect = wp_get_referer();
					$error = false;
					
					try {
						$response = tify_facebook_sdk()->delete( $post_id, array(), get_user_meta( get_current_user_id(), 'fb_page_access_token', true ) );
					} catch(Facebook\Exceptions\FacebookResponseException $e) {
						$error = array( 'title' => 'Graph returned an error', 'message' => $e->getMessage() );
					} catch(Facebook\Exceptions\FacebookSDKException $e) {
						$error = array( 'title' => 'Facebook SDK returned an error', 'message' => $e->getMessage() );
					}
					if( $error ) 
						wp_die( '<h2>'. $error['title'] .'</h2>'.
								'<p>'. $error['message'] .'</p>'.
								'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
								$error['title'], 
								500 
						);
					
					$graphNode = $response->getGraphNode();
					$this->db->delete_item_meta( $item->part_id, 'fb_page_feed_post_id' );
					
					$sendback = remove_query_arg( array( 'action', 'action2' ), $redirect );
					$sendback = add_query_arg( 'message', 'fb_delete', $sendback );
					
					wp_redirect( $sendback );
				break;*/
			endswitch;
		elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
			wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) );
	 		exit;
		endif;
	} 
}