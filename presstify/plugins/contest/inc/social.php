<?php
class tiFy_Contest_Social{
	/* = ARGUMENTS = */
	public	// Configuration
			
			// Paramètres
			$contest_params	= array();
			
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Déclaration de la classe de référence
		$this->master = $master;

		// Actions et Filtres PressTiFy
		add_filter( 'tify_fb_api_share_button_args', array( $this, 'tify_fb_api_share_button_args' ), null, 2 );	
		add_filter( 'tify_fb_post2feed_callback_handle', array( $this, 'tify_fb_post2feed_callback_handle' ), null, 3 );
		add_action( 'tify_facebook_sdk_login', array( $this, 'tify_facebook_sdk_login' ), null, 3 );
	}
	
	/* = ACTIONS ET FILTRES PRESSITFY = */
	/** == Association des comptes d'administrateurs de site au compte Facebook == **/
	public function tify_facebook_sdk_login( $access_token, $app_id, $redirect ){
		if( ! is_user_logged_in() )
			return;
		if( ! current_user_can( 'administrator' ) )
			return;
		
		$error = false;
		
		// Récupération de l'utilisateur courant
		$current_user = wp_get_current_user();		
		
		// Vérification de la correspondance email
		try {
			$response = tify_facebook_sdk( $app_id )->get( '/me?fields=email', $access_token );
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			$error = array( 'title' => 'Graph returned an error', 'message' => $e->getMessage() );
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			$error = array( 'title' => 'Facebook SDK returned an error', 'message' => $e->getMessage() );
		}
		if( $error ) 
			wp_die( '<h2>'. $error['title'] .'</h2>'.
					'<p>'. $error['message'] .'</p>'.
					'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
					__( 'Association Facebook - Impossible', 'deficl' ), 
					500 
			);		
		$userInfos = $response->getGraphObject();
		if( $userInfos['email'] !== $current_user->user_email )
			wp_die( '<h2>'. __( 'Impossible d\'associer votre compte Facebook', 'deficl' ) .'</h2>'.
					'<p>'. __( 'L\'email de votre utilisateur Facebook diffère de celui de votre compte Wordpress', 'deficl' ) .'</p>'.
					'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
					__( 'Association Facebook - Impossible', 'deficl' ), 
					500 
			);
				
		$oAuth2Client 	= tify_facebook_sdk( $app_id )->getOAuth2Client();
		if( $fb_user_id = $oAuth2Client->debugToken( $access_token )->getUserId() )
			update_user_meta( get_current_user_id(), 'fb_user_id', $fb_user_id );
		update_user_meta( get_current_user_id(), 'fb_access_token', $access_token );
		
		wp_redirect( strtok( $redirect, "#" ) );

		exit;
	}

	/** == Modification des arguments de bouton de partage Facebook == **/
	public function tify_fb_api_share_button_args( $args, $defaults ){		
		if( get_query_var( 'tify_template' ) !== 'participation' )
			return $args;		
		if( ! $part_id = (int) get_query_var( 'tify_contest_part', 0 ) )
			return $args;
		
		$args['callback_attrs']['tify_contest_part_id'] = $part_id;

		return $args;
	}
	
	/** == Traitement du partage Facebook sur la page des utilisateurs == **/
	public function tify_fb_post2feed_callback_handle( $output, $response, $attrs ){
		if( empty( $attrs['tify_contest_part_id'] ) )
			return $output;
		if( empty( $response['post_id'] ) )
			return $output;
			
		$success = $this->master->db_participation->add_item_meta( $attrs['tify_contest_part_id'], 'fb_user_feed_post_id', $response['post_id'],  false );
		
		return json_encode( $success );
	}
	
	/* = CONTROLEUR = */
	/** == Vérifie si l'utilisateur est en droit de partager sur la page Facebook du jeu concours == 
	 * @todo A FAIRE ET TESTER 
	 **/
	public function get_page_feed_access_token( $contest_id ){
		if( ! $page_id = $this->get_page_feed( $contest_id ) )
			return;
		if( ! $app_id = $this->get_app_id( $contest_id ) )
			return;
		if( ! $access_token = get_user_meta( get_current_user_id(), 'fb_access_token', true ) )
			return;
		
		// Vérification des droits de publication sur la page Facebook
		try {
			$response = tify_facebook_sdk( $app_id )->get( '/me/accounts', $access_token );
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
					__( 'Association Facebook - Impossible', 'deficl' ), 
					500 
			);					
		$pages = $response->getGraphEdge()->asArray();
		$allowed = false;
		foreach( $pages as $page_args ) :
			if( $page_args['id'] == $page_id ) :
				$allowed = $page_args; 
				break;
			endif;
		endforeach;

		if( ! $allowed )
			wp_die( '<h2>'. __( 'Impossible d\'associer votre compte Facebook', 'deficl' ) .'</h2>'.
					'<p>'. __( 'Vous n\'êtes pas autorisé à gérer la page Facebook associée', 'tify' ) .'</p>'.
					'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
					__( 'Association Facebook - Impossible', 'deficl' ), 
					500 
			);
		if( ! in_array( 'CREATE_CONTENT', $allowed['perms'] ) )
			wp_die( '<h2>'. __( 'Impossible d\'associer votre compte Facebook', 'deficl' ) .'</h2>'.
					'<p>'. sprintf( __( 'Vous n\'êtes pas autorisé à publier du contenu sur la page Facebook : <b>%s</b>', 'tify' ), $allowed['name'] ).'</p>'.
					'<p><a href="'. $redirect .'">&larr;&nbsp;'. __( 'Rééssayer', 'deficl' ) .'</a>', 
					__( 'Association Facebook - Impossible', 'deficl' ), 
					500 
			);			
		return $allowed['access_token'];		
	}
	
	/** == Récupère la app_id du jeu concours == **/
	public function get_app_id( $contest_id ){
		if( ! empty( $this->contest_params[$contest_id]['fb_app_id'] ) )
			return $this->contest_params[$contest_id]['fb_app_id'];
	}
	
	/** == Récupère la page_id de la fanpage Facebook du jeu concours == **/
	public function get_page_feed( $contest_id ){
		if( ! empty( $this->contest_params[$contest_id]['fb_page_feed'] ) )
			return $this->contest_params[$contest_id]['fb_page_feed'];
	}
	
	/** == Vérifie si le partage sur une fanpage est actif pour un jeu concours== **/
	public function is_page_feed( $contest_id ){
		return ! empty( $this->contest_params[$contest_id]['fb_page_feed'] );
	}
	
	/** == Vérifie si le partage sur la page des visiteurs est actif == **/
	public function is_user_feed( $contest_id ){
		return $this->contest_params[$contest_id]['fb_user_feed'] === true;
	}
	
	/** == Récolte des Likes Facebook d'une participation == **/
	public function part_fb_likes( $part_id ){
		// Bypass
		if( ! $part = $this->master->db_participation->get_item_by_id( $part_id ) )
			return null;
		// Déclaration de l'id du jeu de concours
		$contest_id = $part->part_contest_id;
		
		// Récupération du jeton d'accès de l'application Facebook
		if( ( ! $app_id =  $this->contest_params[$contest_id]['fb_app_id'] ) || ( ! $app_secret =  $this->contest_params[$contest_id]['fb_app_secret'] ) )
			return null;
		
		$app_access_token = tify_facebook_sdk_app_access_token( $app_id, $app_secret );
				
		global $wpdb;
		$count = 0; 
		// Mise à jour des likes de la fanpage
		if( $this->is_page_feed( $contest_id ) ) :
			// Suppression des likes de fanpage orphelins
			$query = 	"SELECT meta_id". 
						" FROM {$wpdb->tify_contest_partmeta} AS p".
						" WHERE meta_key = 'fb_page_feed_post_likes'".
						" AND (SELECT tify_contest_part_id FROM {$wpdb->tify_contest_partmeta} WHERE meta_key = 'fb_page_feed_post_id' AND tify_contest_part_id = p.tify_contest_part_id LIMIT 1) IS NULL";
			if( $orphan_ids = $wpdb->get_col( $query ) )
				foreach( $orphan_ids as $orphan_id )
					$wpdb->query( $wpdb->prepare( "DELETE FROM  {$wpdb->tify_contest_partmeta} WHERE meta_id = %d", $orphan_id ) );	
					
			// Récupération des likes de la fanpage
			if( $fb_page_feed_post_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value".
						" FROM {$wpdb->tify_contest_partmeta}".
						" WHERE tify_contest_part_id = %d".
							" AND meta_key = 'fb_page_feed_post_id'",
						$part_id
					)	
				)
			) :			
				$error = false;
				try {
					$response = tify_facebook_sdk()->get( '/'. $fb_page_feed_post_id .'/likes?summary=true', $app_access_token );
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
					$error = array( 'title' => 'Graph returned an error', 'message' => $e->getMessage() );
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
					$error = array( 'title' => 'Facebook SDK returned an error', 'message' => $e->getMessage() );
				}
				if( ! $error && $total_count = $response->getGraphEdge()->getTotalCount() ) :
					$this->master->db_participation->update_item_meta( $part_id, 'fb_page_feed_post_likes', $total_count );
					$count += $total_count;
				endif;
			endif;
		endif;		
		
		// Mise à jour des likes de partage utilisateur
		if( $this->is_user_feed( $contest_id ) ) :			
			// Suppression des likes utilisateur orphelins
			$query = 	"SELECT meta_id". 
						" FROM {$wpdb->tify_contest_partmeta} AS p".
						" WHERE meta_key = 'fb_user_feed_post_likes'".
						" AND ( SELECT tify_contest_part_id FROM {$wpdb->tify_contest_partmeta} WHERE meta_key = 'fb_user_feed_post_id' AND tify_contest_part_id = p.tify_contest_part_id LIMIT 1 ) IS NULL";
			if( $orphan_ids = $wpdb->get_col( $query ) )
				foreach( $orphan_ids as $orphan_id )
					$wpdb->query( $wpdb->prepare( "DELETE FROM  {$wpdb->tify_contest_partmeta} WHERE meta_id = %d", $orphan_id ) );
			
			// Récupération des likes utilisateur
			if( $fb_user_feed_post_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT meta_value".
						" FROM {$wpdb->tify_contest_partmeta}".
						" WHERE tify_contest_part_id = %d".
							" AND meta_key = 'fb_user_feed_post_id'",
						$part_id
					)	
				) 
			) :
				$fb_user_feed_post_likes = 0;
				foreach( $fb_user_feed_post_ids as $post_id ) :	
					$error = false;			
					try {
						$response = tify_facebook_sdk()->get( '/'. $post_id .'/likes?summary=true', $app_access_token );
					} catch(Facebook\Exceptions\FacebookResponseException $e) {
						$error = array( 'title' => 'Graph returned an error', 'message' => $e->getMessage() );
					} catch(Facebook\Exceptions\FacebookSDKException $e) {
						$error = array( 'title' => 'Facebook SDK returned an error', 'message' => $e->getMessage() );
					}
					if( ! $error && $total_count = $response->getGraphEdge()->getTotalCount() ) 					
						$fb_user_feed_post_likes += $total_count;
				endforeach;
				$this->master->db_participation->update_item_meta( $part_id, 'fb_user_feed_post_likes', $fb_user_feed_post_likes );
				$count += $total_count;
			endif;
		endif;

		return $count;	
	}			
}