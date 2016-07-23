<?php
class tiFy_Contest_Capabilities{
	/* = ARGUMENTS =*/
	
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Contest_Master $master ){
		// Déclaration des Références
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_filter( 'map_meta_cap', array( $this, 'wp_map_meta_cap' ), null, 4 );	
	}
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Mapping des habilitations == **/
	public function wp_map_meta_cap( $caps, $cap, $user_id, $args ){
		switch( $cap ) :
			case 'tify_contest_participate' :
				$contest_id = ( ! isset( $args[0] ) ) ? get_query_var( 'tify_contest_id', null ) : $args[0];
				// Vérifie l'existance d'un identifiant de jeu concours
				if( ! $contest_id ) :
					$this->master->errors = new WP_Error( 'tify_contest_undefined_contest', __( 'Le jeux concours auxquel vous tentez d\'accéder n\'a pu être défini', 'tify' ), array( 'title' => __( 'Participation au jeu concours impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );
				// Vérifie si le jeux concours est déclaré
				elseif( ! $this->master->is_registred( $contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_not_registred', __( 'Le jeux concours auxquel vous tentez d\'accéder n\'existe pas', 'tify' ), array( 'title' => __( 'Participation au jeu concours impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );					
				// Vérifie si le jeux concours est ouvert
				elseif( ! $this->is_participation_open( $contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_closed', __( 'Le jeux concours auxquel vous tentez d\'accéder n\'est actuellement pas ouvert aux participations', 'tify' ), array( 'title' => __( 'Participation au jeu concours impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				// Vérifie si le nombre maximum de participation est atteint
				elseif( $this->is_participation_max( $contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_part_max_attempt', __( 'Le nombre maximum de participation au jeu concours est atteint', 'tify' ), array( 'title' => __( 'Participation au jeu concours impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				// Vérifie si l'utilisateur courant est habilité
				elseif( ! $this->is_participation_user_allowed( $user_id, $contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_part_user_not_allowed', __( 'Vous n\'êtes pas autorisé à participer à ce jeu concours', 'tify' ), array( 'title' => __( 'Participation au jeu concours impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				// Vérifie si le nombre maximum de participation pour l'utilisateur courant est atteint
				elseif( $this->is_participation_user_max( $user_id, $contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_part_max_attempt', __( 'Vous avez atteint le nombre maximum de participation autorisée à ce jeu concours', 'tify' ), array( 'title' => __( 'Participation au jeu concours impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				else :
					$caps = array( 'exist' );
				endif;
			break;
			case 'tify_contest_read_part' :
				$part_id = ( ! isset( $args[0] ) ) ? get_query_var( 'tify_contest_part', 0 ) : (int) $args[0];				
				// Vérifie l'existance d'un identifiant de participation à un jeu concours
				if( ! $part_id ) :
					$this->master->errors = new WP_Error( 'tify_contest_read_part_undefined', __( 'La participation au jeu concours à laquelle vous tentez d\'accéder n\'a pu être définie', 'tify' ), array( 'title' => __( 'Affichage de la participation impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );
				// Vérifie si la participation existe
				elseif( ! $part = $this->master->db_participation->get_part( $part_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_read_not_exist', __( 'La participation au jeu concours à laquelle vous tentez d\'accéder n\'existe pas', 'tify' ), array( 'title' => __( 'Affichage de la participation impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );
				// Vérifie si la participation est publiée ou si l'utilisateur est admistrateur ou le propriétaire de la participation
				elseif( ( $part->part_status !== 'publish' ) && ( ! current_user_can( 'administrator' ) && ( $user_id !==  (int) $part->part_user_id ) ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_read_not_publish', __( 'La participation au jeu concours à laquelle vous tentez d\'accéder n\'est pas accessible', 'tify' ), array( 'title' => __( 'Affichage de la participation impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );
				else :
					$caps = array( 'exist' );
				endif;
			break;
			case 'tify_contest_poll' :
				$part_id = ( ! isset( $args[0] ) ) ? get_query_var( 'tify_contest_part', 0 ) : (int) $args[0];
				$part = $this->master->db_participation->get_part( $part_id );
				// Vérifie si l'utilisateur a les habilitations suffisante pour la participation concernée par le vote
				if( ! current_user_can( 'tify_contest_read_part' ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_poll_check_part', __( 'Vous n\'êtes pas habilité à voter pour cette participation', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );
				// Vérifie si la participation est publiée
				elseif( $part->part_status !== 'publish' ) :
					$this->master->errors = new WP_Error( 'tify_contest_poll_part_published', __( 'La participation doit être acceptée pour pouvoir voter', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );
				// Vérifie si le jeux concours est déclaré
				elseif( ! $this->master->is_registred( $part->part_contest_id ) ) :					
					$this->master->errors = new WP_Error( 'tify_contest_not_registred', __( 'Vous n\'êtes pas autorisé à voter pour ce jeu concours', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					$caps = array( 'do_not_allow' );					
				// Vérifie si le jeux concours est ouvert
				elseif( ! $this->is_poll_open( $part->part_contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_closed', __( 'Le jeux concours n\'est actuellement pas ouvert aux votes', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				// Vérifie si le nombre maximum de participation est atteint
				elseif( $this->is_poll_max( $part->part_contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_part_max_attempt', __( 'Le nombre maximum de vote à ce jeu concours est atteint', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				// Vérifie si l'utilisateur courant est habilité
				elseif( ! $this->is_poll_user_allowed( $user_id, $part->part_contest_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_part_user_not_allowed', __( 'Vous n\'êtes pas autorisé à voter à ce jeu concours', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				// Vérifie si le nombre maximum de vote pour l'utilisateur courant est atteint
				elseif( $this->is_poll_user_max( $user_id, $part->part_id ) ) :
					$this->master->errors = new WP_Error( 'tify_contest_part_max_attempt', __( 'Vous avez atteint le quota de votes autorisés pour cette participation', 'tify' ), array( 'title' => __( 'Vote impossible', 'tify' ) ) );
					return $caps = array( 'do_not_allow' );
				else :
					$caps = array( 'exist' );
				endif;
			break;
		endswitch;
		return $caps;
	}

	/** == TEST D'INTEGRITE == **/
	/*** === PARTICIPATIONS === ***/
	/**** ==== Vérifie un jeu concours est ouvert aux participations ==== 
	 * Le jeux concours doit avoir été ouvert et pas encore fermé
	 ****/
	public function is_participation_open( $contest_id ){
		return ( $this->is_participation_opened( $contest_id ) && ! $this->is_participation_closed( $contest_id ) );
	}
	
	/**** ==== Vérifie un jeu concours à été ouvert aux participations ==== 
	 * Le jeux concours doit avoir été ouvert et peu l'être encore ou non
	 ****/
	public function is_participation_opened( $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return false;
		
		return ( current_time( 'mysql' ) > $this->master->registred_contest[$contest_id]['participations']['start'] );
	}
	
	/**** ==== Vérifie un jeu concours a été fermé aux participations ==== 
	 * Le jeux concours doit avoir été ouvert et peu l'être encore ou non
	 ****/
	public function is_participation_closed( $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return false;
		
		return ( current_time( 'mysql' ) > $this->master->registred_contest[$contest_id]['participations']['end'] );
	}
	
	/**** ==== Vérifie si le nombre maximum de participation au jeu concours est atteint ==== ****/
	public function is_participation_max( $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return true;
		
		if( $this->master->registred_contest[$contest_id]['participations']['max'] === -1 )
			return false;

		return ( $this->master->db_participation->count( $contest_id ) >= $this->master->registred_contest[$contest_id]['participations']['max'] );
	}
	
	/**** ==== Vérifie si le nombre maximum de participation par utilisateur est atteint ==== ****/
	public function is_participation_user_allowed( $user_id, $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return false;

		if( ! $user_id )
			return false;
		
		if( ! $roles = $this->master->registred_contest[$contest_id]['participations']['user']['roles'] )
			return true;
		
		if( is_string( $roles ) )
			$roles = array( $roles );
		
		foreach( $roles as $role )
			if( current_user_can( $role ) )
				return true;
		
		return false;
	}
	
	/**** ==== Vérifie si le nombre maximum de participation par utilisateur est atteint ==== ****/
	public function is_participation_user_max( $user_id, $contest_id ){		
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return true;
		
		if( $this->master->registred_contest[$contest_id]['participations']['user']['max'] === -1 )
			return false;

		return ( $this->master->db_participation->count( $contest_id, array( 'user_id' => $user_id ) ) >= $this->master->registred_contest[$contest_id]['participations']['user']['max'] );
	}
	
	/*** === VOTES === ***/
	/**** ==== Vérifie un jeu concours est ouvert aux votes ==== ****/
	public function is_poll_open( $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return false;
		
		return ( ( current_time( 'mysql' ) > $this->master->registred_contest[$contest_id]['poll']['start'] ) && ( current_time( 'mysql' ) < $this->master->registred_contest[$contest_id]['poll']['end'] ) );
	}
	
	/**** ==== Vérifie si le nombre maximum de vote au jeu concours est atteint ==== ****/
	public function is_poll_max( $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return true;
		
		if( $this->master->registred_contest[$contest_id]['poll']['max'] === -1 )
			return false;
		
		return ( tiFy_Contest_Poll::contest_count( $contest_id ) >= $this->master->registred_contest[$contest_id]['poll']['max'] );
	}
	
	/**** ==== Vérifie si le nombre maximum de participation par utilisateur est atteint ==== ****/
	public function is_poll_user_allowed( $user_id = 0, $contest_id ){
		// Bypass
		if( ! $this->master->is_registred( $contest_id ) )
			return false;
		
		if( ! $roles = $this->master->registred_contest[$contest_id]['poll']['user']['roles'] )
			return true;
		
		if( is_string( $roles ) )
			$roles = array( $roles );
		
		foreach( $roles as $role )
			if( current_user_can( $role ) )
				return true;
		
		return false;
	}
	
	/**** ==== Vérifie si le nombre maximum de participation par utilisateur est atteint ==== ****/
	public function is_poll_user_max( $user_id = 0, $part_id ){		
		// Bypass
		if( ! $user_id )
			return false;
			
		if( ! $contest_id = $this->master->db_participation->get_part_contest( $part_id ) )
			return true;
		if( ! $this->master->is_registred( $contest_id ) )
			return true;		
		
		if( $this->master->registred_contest[$contest_id]['poll']['user']['max'] === -1 )
			return false;

		return ( tiFy_Contest_Poll::user_count_by( 'id', $user_id, $part_id ) >= $this->master->registred_contest[$contest_id]['poll']['user']['max'] );
	}	
	
	/*** === GAGNANTS === ***/
	/**** ==== ==== ****/
	public function has_winner( $contest_id ){
		return ! empty( $this->master->registred_contest[$contest_id]['winners'] );	
	}
	
	/**** ==== ==== ****/
	public function get_winners( $contest_id ){
		if( $this->has_winner( $contest_id ) )
			return $this->master->registred_contest[$contest_id]['winners'];	
	}	
}