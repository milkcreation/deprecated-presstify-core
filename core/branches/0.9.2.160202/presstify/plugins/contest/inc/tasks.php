<?php
class tiFy_Contest_Tasks{
	/* = ARGUMENTS = */
	public	// Configuration
			$transient_lock 	= 'tify_contest_ranking_lock_edit',
			$transient_handle 	= 'tify_contest_ranking_handle';
			
	private	// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Contest_Master $master ){
		// Déclaration des Références
		$this->master = $master;
			
		// Actions et Filtres Wordpress
		add_action( 'admin_notices', array( $this, 'wp_admin_notices' ) );
		add_filter( 'cron_schedules', array( $this, 'wp_cron_schedules' ) );
		
		if( ! wp_get_schedule( 'tify_contest_parse_ranks' ) )
			wp_schedule_event( time(), 'quarter_of_an_hour', 'tify_contest_parse_ranks'  );
		add_action( 'tify_contest_parse_ranks', array( $this, 'update_ranking' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	public function wp_cron_schedules( $schedules ){
		$schedules['quarter_of_an_hour'] = array( 
			'interval' 	=> 15 * MINUTE_IN_SECONDS,
			'display' 	=> __( 'Every Quarter Hour' ) 
		);
		
		return $schedules; 
	}
	
	/* = CONTROLEURS = */
	/** == Initialisation == **/
	public function update_ranking(){
		// Récupération des jeux concours ouvert au calcul de classement ( le vote doit être ouvert )
		/** @todo ou la date du dernier traitement supérieure à la date de fermeture des votes **/
		if( ! $opens = $this->master->get_poll_opens() )
			return;		
		
		global $wpdb;
		
		// Définition de la date de traitement (au format GMT pour la compatibilité SQL)
		$timestamp = current_time( 'timestamp', true );
		
		// Mise en place du verrou de saisie
		set_transient( $this->transient_lock, $timestamp, HOUR_IN_SECONDS );
		$this->master->edit_locked = true;
				
		// Calcul du nombre de participation à traiter par jeu concours
		$counts = array();
		foreach( $opens as $contest_id => $contest_args )
			$counts[$contest_id] = $this->master->db_participation->count_items( array( 'status' => 'publish' ) );	
		
		// Définition des arguments de la phase de traitement
		if( ! $handle = get_transient( $this->transient_handle ) )
			$handle = array( 'ts' => $timestamp );			
		$handle = wp_parse_args( array( 'counts' => $counts ), $handle );			
		set_transient( $this->transient_handle, $handle, HOUR_IN_SECONDS );

		// Création des entrées de base de données des participations à traiter
		$paged = 1; $per_page = 100;
		foreach( $opens as $contest_id => $contest_args ) :
			if( ! $count = $handle['counts'][$contest_id] )
				return;
			
			$_count = 0;
			for( $i = 0; $i < ceil( $count/$per_page ); $i++ ) :
				if( ! $parts = $this->master->db_participation->get_items( array( 'per_page' => $per_page, 'paged' => $paged++, 'order' => 'ASC', 'status' => 'publish' ) ) )
					continue;
				foreach( $parts as $p ) :
					// Pré-sauvegarde du classement et translation des données courantes vers les données précédentes
					if( $r = $this->master->db_ranking->get_item( array( 'part_id' => $p->part_id ) ) ) :
						if( $r->ranking_current_ts !=  $handle['ts'] ) :
							$this->master->db_ranking->update_item( 
								$r->ranking_id, 
								array( 
								 	'part_id' 			=> $p->part_id, 
								 	'current_ts' 		=> $handle['ts'],
								 	// Translation 
								 	'previous_pos' 		=> $r->ranking_current_pos,
								 	'previous_count' 	=> $r->ranking_current_count,
								 	'previous_ts' 		=> $r->ranking_current_ts,
								) 
							);
						endif;
					else :
						$this->master->db_ranking->insert_item( 
							array( 
								'part_id' 			=> $p->part_id, 
								'current_ts' 		=> $handle['ts'] 
							) 
						);
					endif;
					$_count++;
				endforeach;								
			endfor;
			$handle['counts'][$contest_id] = $count;		
		endforeach;
		
		// Mise à jour des comptes des arguments de la phase de traitement
		$handle = wp_parse_args( array( 'counts' => $handle['counts'] ), $handle );	
		set_transient( $this->transient_handle, $handle, HOUR_IN_SECONDS );

		// Suppression du verrou de saisie
		delete_transient( $this->transient_lock );
		$this->master->edit_locked = false;
			
		// Récolte du compte des votes participations à traiter
		$ranks = $this->master->db_ranking->get_items( array( 'order' => 'ASC', 'orderby' => 'part_id' ) );		
		foreach( $ranks as $r ) :	
			$count = 0;	
			// Récupération du compte des votes par participation dont la date est inférieur à la date de traitement
			$query = 	"SELECT COUNT(poll_id) FROM {$wpdb->tify_contest_poll}".
						" WHERE 1".
							" AND poll_part_id = %d".
							" AND poll_active = 1".
							" AND UNIX_TIMESTAMP(poll_date) < %d";
							
			$count = $wpdb->get_var( $wpdb->prepare( $query, $r->ranking_part_id, (int) $handle['ts'] ) );
			
			/** == AJOUT DES COMPTES DES RESEAUX SOCIAUX == **/
			$fblikes = $this->master->social->part_fb_likes( $r->ranking_part_id );
			$count += $fblikes;
			
			/** == AJOUT DES POINTS BONUS == **/
			if( $bonus_image_likes = (int) $this->master->db_participation->get_item_meta( $r->ranking_part_id, 'bonus_image_likes' ) );
				$count += $bonus_image_likes;
		
			// Mise à jour des comptes de vote dans la base
			$this->master->db_ranking->update_item( 
				$r->ranking_id, 
				array( 'current_count' => $count ) 
			);		
		endforeach;
		reset( $ranks );
		
		// Récolte et enregistrement des rangs
		foreach( $opens as $contest_id => $contest_args ) :
			/// Récupération du classement d'un jeu coucours
			$query = 	"SELECT ranking_part_id, rank FROM". 
						" (SELECT ranking_part_id, @curRank := IF(@prevRank = ranking_current_count, @curRank, @incRank) AS rank, @incRank := @incRank + 1, @prevRank := ranking_current_count".
						" FROM {$wpdb->tify_contest_ranking} p".
						",(SELECT @curRank :=0, @prevRank := NULL, @incRank := 1) r".
						" INNER JOIN {$wpdb->tify_contest_part} q".
						" WHERE ranking_part_id = q.part_id".
							" AND q.part_contest_id = %s".
						" ORDER BY ranking_current_count DESC) s";
			$results = $wpdb->get_results( $wpdb->prepare( $query, $contest_id ) );
			$ranking = array();

			foreach( $results as $res )
				$ranking[$res->ranking_part_id] = $res->rank;

			// Mise à jour du rang dans la base de chaque participation		
			foreach( $ranks as $r )
				if( ! empty( $ranking[$r->ranking_part_id] ) )
					$this->master->db_ranking->update_item( 
						$r->ranking_id, 
						array( 'current_pos' => $ranking[$r->ranking_part_id] ) 
					);
		endforeach;

		// Suppression du jeton de traitement		
		delete_transient( $this->transient_handle );		
		// Mise en cache de la date du dernier traitement
		update_option( 'tify_contest_ranking_last_handle_ts', $handle['ts'] );
	}
	
	/** == Notification de l'interface d'administration == **/
	public function wp_admin_notices(){
		if( ! $this->master->edit_locked )
			return;
		if( get_current_screen()->parent_base !== 'tify_contest' )
			return;
		
		echo "<div class=\"error\"><p>". __( 'TRAITEMENT DU CLASSEMENT EN COURS ... Les fonctionnalités d\'édition ont été désactivée, merci de patienter quelques instants.', 'tify' )."</p></div>";
	}
}