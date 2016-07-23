<?php
class tiFy_Forum_ContributionMain{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Declaration de la classe de référence principale
		$this->master = $master;
	}
		
	/* = CONTROLEUR = */
	/** == Récupération d'une contribution == **/
	public function get( $contrib_id ){
		return $this->master->db->contribution->get_item_by_id( $contrib_id );
	}
	
	/** == Récupération d'une liste de contribution == **/
	public function get_list( $args = array() ){
		return $this->master->db->contribution->get_items( $args );
	}
	
	/** == Récupération de la dernière contribution == **/
	public function last( $topic_id = null, $args = array() ){
		$defaults = array(
			'limit' 	=> 1,
			'order'		=> 'DESC',
			'orderby'	=> 'date'
		);
		if( $topic_id )
			$defaults['topic_id'] = $topic_id;
		
		$args = wp_parse_args( $args, $defaults );
		
		return $this->master->db->contribution->get_item( $args );
	}
	
	/** == Récupération du status d'une contribution == **/
	public function get_status( $contrib_id ) {
		$contrib = $this->get( $contrib_id );
		if ( ! $contrib )
			return false;
	
		$approved = $contrib->contrib_approved;
	
		if ( $approved == null )
			return false;
		elseif ( $approved == '1' )
			return 'approved';
		elseif ( $approved == '0' )
			return 'unapproved';
		elseif ( $approved == 'spam' )
			return 'spam';
		elseif ( $approved == 'trash' )
			return 'trash';
		else
			return false;
	}
	
	/** == Insertion d'un nouveau commentaire en base de données == **/
	public function insert( $contribdata ) {
		$data = wp_unslash( $contribdata );
	
		$contrib_author       	= ! isset( $data['contrib_author'] )       	? '' : $data['contrib_author'];
		$contrib_author_email 	= ! isset( $data['contrib_author_email'] ) 	? '' : $data['contrib_author_email'];
		$contrib_author_url   	= ! isset( $data['contrib_author_url'] )   	? '' : $data['contrib_author_url'];
		$contrib_author_IP    	= ! isset( $data['contrib_author_IP'] )    	? '' : $data['contrib_author_IP'];
	
		$contrib_date     		= ! isset( $data['contrib_date'] )     		? current_time( 'mysql' )            : $data['contrib_date'];
		$contrib_date_gmt 		= ! isset( $data['contrib_date_gmt'] ) 		? get_gmt_from_date( $contrib_date ) : $data['contrib_date_gmt'];
	
		$contrib_topic_id 		= ! isset( $data['contrib_topic_id'] )  	? '' : $data['contrib_topic_id'];
		$contrib_content  		= ! isset( $data['contrib_content'] )  		? '' : $data['contrib_content'];
		$contrib_karma   		= ! isset( $data['contrib_karma'] )    		? 0  : $data['contrib_karma'];
		$contrib_approved 		= ! isset( $data['contrib_approved'] ) 		? 1  : $data['contrib_approved'];
		$contrib_agent    		= ! isset( $data['contrib_agent'] )    		? '' : $data['contrib_agent'];
		$contrib_type     		= ! isset( $data['contrib_type'] )     		? '' : $data['contrib_type'];
		$contrib_parent   		= ! isset( $data['contrib_parent'] )   		? 0  : $data['contrib_parent'];
	
		$contrib_user_id  = ! isset( $data['contrib_user_id'] ) ? 0 : $data['contrib_user_id'];
	
		$compacted = compact( 'contrib_topic_id', 'contrib_author', 'contrib_author_email', 'contrib_author_url', 'contrib_author_IP', 'contrib_date', 'contrib_date_gmt', 'contrib_content', 'contrib_karma', 'contrib_approved', 'contrib_agent', 'contrib_type', 'contrib_parent', 'contrib_user_id' );
		if ( ! $contrib_id = $this->master->db->contribution->insert_item( $compacted ) )
			return false;
	
		if ( $contrib_approved == 1 )
			$this->master->topic->update_contrib_count_now( $contrib_topic_id );

		$contrib = $this->get( $contrib_id );
	
		if ( isset( $contribdata['contrib_meta'] ) && is_array( $contribdata['contrib_meta'] ) )
			foreach ( $contribdata['contrib_meta'] as $meta_key => $meta_value )
				$this->master->db->contribution->add_item_meta( $contrib->contrib_id, $meta_key, $meta_value );
	
		do_action( 'tify_forum_insert_contrib', $contrib_id, $contrib );
	
		return $contrib_id;
	}
	
	/** == Filtrage des données d'une contribution == **/
	public function filter( $contribdata ) {
		if ( isset( $contribdata['user_ID'] ) )
			$contribdata['contrib_user_id'] = apply_filters( 'pre_user_id', $contribdata['user_ID'] );
		elseif ( isset( $contribdata['contrib_user_id'] ) )
			$contribdata['contrib_user_id'] = apply_filters( 'pre_user_id', $contribdata['contrib_user_id'] );
	
		$contribdata['contrib_agent'] 			= apply_filters( 'tify_forum_pre_contrib_user_agent', ( isset( $contribdata['contrib_agent'] ) ? $contribdata['contrib_agent'] : '' ) );
		$contribdata['contrib_author'] 			= apply_filters( 'tify_forum_pre_contrib_author_name', $contribdata['contrib_author'] );
		$contribdata['contrib_content'] 		= apply_filters( 'tify_forum_pre_contrib_content', $contribdata['contrib_content'] );
		$contribdata['contrib_author_IP'] 		= apply_filters( 'tify_forum_pre_contrib_user_ip', $contribdata['contrib_author_IP'] );
		$contribdata['contrib_author_url'] 		= apply_filters( 'tify_forum_pre_contrib_author_url', $contribdata['contrib_author_url'] );
		$contribdata['contrib_author_email'] 	= apply_filters( 'tify_forum_pre_contrib_author_email', $contribdata['contrib_author_email'] );
		$contribdata['filtered'] = true;
		
		return $contribdata;
	}

	/** == Permission d'enregistrement de contribution == **/
	public function allow( $contribdata ) {
		global $wpdb;
	
		// Vérification des doublons
		$dupe = $wpdb->prepare(
			"SELECT contrib_id FROM {$wpdb->tify_forum_contribution} WHERE contrib_topic_id = %d AND contrib_parent = %s AND contrib_approved != 'trash' AND ( contrib_author = %s ",
			wp_unslash( $contribdata['contrib_topic_id'] ),
			wp_unslash( $contribdata['contrib_parent'] ),
			wp_unslash( $contribdata['contrib_author'] )
		);
		if ( $contribdata['contrib_author_email'] ) {
			$dupe .= $wpdb->prepare(
				"OR contrib_author_email = %s ",
				wp_unslash( $contribdata['contrib_author_email'] )
			);
		}
		$dupe .= $wpdb->prepare(
			") AND contrib_content = %s LIMIT 1",
			wp_unslash( $contribdata['contrib_content'] )
		);
	
		$dupe_id = $wpdb->get_var( $dupe );

		$dupe_id = apply_filters( 'tify_forum_duplicate_contrib_id', $dupe_id, $contribdata );
	
		if ( $dupe_id ) :
			do_action( 'tify_forum_contrib_duplicate_trigger', $contribdata );
			if ( defined( 'DOING_AJAX' ) )
				die( __( 'Il semblerait que vous ayez déjà posté cette contribution', 'tify' ) );
			
			wp_die( __(  'Il semblerait que vous ayez déjà posté cette contribution', 'tify' ), 409 );
		endif;
	
		do_action(
			'tify_forum_check_contrib_flood',
			$contribdata['contrib_author_IP'],
			$contribdata['contrib_author_email'],
			$contribdata['contrib_date_gmt']
		);
	
		if ( ! empty( $contribdata['contrib_user_id'] ) ) :
			$user = get_userdata( $contribdata['contrib_user_id'] );
			$topic_author = $wpdb->get_var( $wpdb->prepare(
				"SELECT topic_author FROM {$wpdb->tify_forum_topic} WHERE topic_id = %d LIMIT 1",
				$contribdata['contrib_topic_id']
			) );
		endif;
	
		if ( isset( $user ) && ( $contribdata['contrib_user_id'] == $topic_author /*|| $user->has_cap( 'moderate_comments' )*/ ) ) :
			$approved = 1;
		else :
			if ( 	$this->check(
						$contribdata['contrib_author'],
						$contribdata['contrib_author_email'],
						$contribdata['contrib_author_url'],
						$contribdata['contrib_content'],
						$contribdata['contrib_author_IP'],
						$contribdata['contrib_agent'],
						$contribdata['contrib_type']
					) 
			) 
				$approved = 1;
			else
				$approved = 0;
			
			/**
			 * 	@todo
			if ( 	$this->blacklist_check(
						$contribdata['contrib_author'],
						$contribdata['contrib_author_email'],
						$contribdata['contrib_author_url'],
						$contribdata['contrib_content'],
						$contribdata['contrib_author_IP'],
						$contribdata['contrib_agent']
					) 
			)
				$approved = EMPTY_TRASH_DAYS ? 'trash' : 'spam';*/
		endif;

		$approved = apply_filters( 'tify_forum_pre_contrib_approved', $approved, $contribdata );
		
		return $approved;
	}
	
	/** == Vérification des données d'une contribution == **/
	public function check( $author, $email, $url, $content, $user_ip, $user_agent, $comment_type ) {
		global $wpdb;
	
		// Si la moderation manuelle est active tous les tests de vérification sont alors court-circuité.
		if ( 1 == $this->master->options->get( 'moderation', 'contrib_moderation' ) )
			return false;

		$content = apply_filters( 'tify_forum_contrib_text', $content );
		
		/**
		 * Vérification du nombre maximum de lien externe
		if ( $max_links = get_option( 'comment_max_links' ) ) {
			$num_links = preg_match_all( '/<a [^>]*href/i', $comment, $out );
	
			$num_links = apply_filters( 'comment_max_links_url', $num_links, $url );
	
			/*
			 * If the number of links in the comment exceeds the allowed amount,
			 * fail the check by returning false.
			 
			if ( $num_links >= $max_links )
				return false;
		}*/
		/**
		$mod_keys = trim(get_option( 'moderation_keys' ));
	
		// If moderation 'keys' (keywords) are set, process them.
		if ( !empty($mod_keys) ) {
			$words = explode("\n", $mod_keys );
	
			foreach ( (array) $words as $word) {
				$word = trim($word);
	
				// Skip empty lines.
				if ( empty($word) )
					continue;
				$word = preg_quote($word, '#');
	
				$pattern = "#$word#i";
				if ( preg_match($pattern, $author) ) return false;
				if ( preg_match($pattern, $email) ) return false;
				if ( preg_match($pattern, $url) ) return false;
				if ( preg_match($pattern, $comment) ) return false;
				if ( preg_match($pattern, $user_ip) ) return false;
				if ( preg_match($pattern, $user_agent) ) return false;
			}
		}
	
		if ( 1 == get_option('comment_whitelist')) {
			if ( 'trackback' != $comment_type && 'pingback' != $comment_type && $author != '' && $email != '' ) {
				// expected_slashed ($author, $email)
				$ok_to_comment = $wpdb->get_var("SELECT comment_approved FROM $wpdb->comments WHERE comment_author = '$author' AND comment_author_email = '$email' and comment_approved = '1' LIMIT 1");
				if ( ( 1 == $ok_to_comment ) &&
					( empty($mod_keys) || false === strpos( $email, $mod_keys) ) )
						return true;
				else
					return false;
			} else {
				return false;
			}
		}*/
		return true;
	}
	
	
	function blacklist_check( $author, $email, $url, $comment, $user_ip, $user_agent ) {
		/**
		 * Fires before the comment is tested for blacklisted characters or words.
		 *
		 * @since 1.5.0
		 *
		 * @param string $author     Comment author.
		 * @param string $email      Comment author's email.
		 * @param string $url        Comment author's URL.
		 * @param string $comment    Comment content.
		 * @param string $user_ip    Comment author's IP address.
		 * @param string $user_agent Comment author's browser user agent.
		 */
		do_action( 'wp_blacklist_check', $author, $email, $url, $comment, $user_ip, $user_agent );
	
		$mod_keys = trim( get_option('blacklist_keys') );
		if ( '' == $mod_keys )
			return false; // If moderation keys are empty
		$words = explode("\n", $mod_keys );
	
		foreach ( (array) $words as $word ) {
			$word = trim($word);
	
			// Skip empty lines
			if ( empty($word) ) { continue; }
	
			// Do some escaping magic so that '#' chars in the
			// spam words don't break things:
			$word = preg_quote($word, '#');
	
			$pattern = "#$word#i";
			if (
				   preg_match($pattern, $author)
				|| preg_match($pattern, $email)
				|| preg_match($pattern, $url)
				|| preg_match($pattern, $comment)
				|| preg_match($pattern, $user_ip)
				|| preg_match($pattern, $user_agent)
			 )
				return true;
		}
		return false;
	}
}