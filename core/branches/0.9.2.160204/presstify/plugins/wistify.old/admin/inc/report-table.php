<?php
class tiFy_Wistify_Report_AdminTable extends tiFy_AdminView_List_Table {
	/* = ARGUMENTS = */
	public	// Paramètres
			$status_description;
				
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->report;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
            	'singular'  => 'tify_wistify_report',
            	'plural'    => 'tify_wistify_reports',
            	'ajax'      => true,
            	'screen'	=> $this->master->hookname['report']
        	), 
        	$this->master->db_report
		);
		
		// Paramétrage
		/// Environnement
		$this->items_page_default = 50;
		$this->base_link = add_query_arg( array( 'page' => $this->master->menu_slug['report'] ), admin_url( '/admin.php' ) );

		// Vues Filtrées
		$state = ! empty( $_REQUEST['md_state'] ) ? $_REQUEST['md_state'] : 'any';
		$this->views = array(
			'any'			=> array(
				'label'				=> __( 'Tous', 'tify' ),
				'current'			=> ( $state === 'any' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'campaign_id' => $_REQUEST['campaign_id'] ) : array( ),
				'remove_query_args'	=> array( 'md_state' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'any', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'any' )
			),
			'posted'		=> array(
				'label'				=> __( 'Posté', 'tify' ),
				'current'			=> ( $state === 'posted' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'posted', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'posted' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'posted', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'posted' )
			),
			'deferred'		=> array(
				'label'				=> __( 'Différé', 'tify' ),
				'current'			=> ( $state === 'deferred' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'deferred', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'deferred' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'deferred', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'deferred' )
			),		
			'sent'			=> array(
				'label'				=> __( 'Délivré', 'tify' ),
				'current'			=> ( $state === 'sent' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'sent', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'sent' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'sent', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'sent' )
			),		
			'soft-bounced'	=> array(
				'label'				=> __( 'Soft Bounced', 'tify' ),
				'current'			=> ( $state === 'soft-bounced' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'soft-bounced', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'soft-bounced' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'soft-bounced', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'soft-bounced' )
			),
			'bounced'		=> array(
				'label'				=> __( 'Hard Bounced', 'tify' ),
				'current'			=> ( $state === 'bounced' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'bounced', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'bounced' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'bounced', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'bounced' )
			),
			'rejected'		=> array(
				'label'				=> __( 'Rejeté', 'tify' ),
				'current'			=> ( $state === 'rejected' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'rejected', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'rejected' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'rejected', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'rejected' )
			),						
			'unknown'				=> array(
				'label'				=> __( 'Inconnu', 'tify' ),
				'current'			=> ( $state === 'unknown' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'unknown', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'unknown' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'unknown', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'unknown' )
			),
			'spam'					=> array(
				'label'				=> __( 'Plainte pour spam', 'tify' ),
				'current'			=> ( $state === 'spam' ) ? true : false,
				'add_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'spam', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'spam' ),
				'count_query_args'	=> ! empty(  $_REQUEST['campaign_id'] ) ? array( 'md_state' => 'spam', 'campaign_id' => $_REQUEST['campaign_id'] ) : array( 'md_state' => 'spam' )
			)
		);
		
		$this->status_description = array(
			'posted'			=> __( 
				'Le message a été envoyé au serveur Mandrill mais n\'a pas encore été traité'
			),
			'deferred'			=> __( 
				'Le message est parvenu au serveur Mandrill mais reste pour l\'instant en attente de distribution pour des raisons de restriction du serveur :<br/>'.
				'<ul>'.
					'<li>- La boîte de réception était pleine au moment de l\'envoi</li>'.
					'<li>- La réception de mail est temporairement rejetée</li>'.
					'<li>- Problème temporaire</li>'.
					'<li>- ...</li>'.
				'</ul>'
			),
			'sent'				=> __( 
				'Le message a été délivré par le serveur et a été réceptionné par le destinataire.'
			),
			'soft-bounced'		=> __( 
				'(État temporaire) Adresse email de destination pour laquelle le message n\'a pu être distribué pour l\'une des raisons suivantes :<br/>'.
				'<ul>'.
					'<li>- La boîte de réception du destinataire est pleine</li>'.
					'<li>- Le serveur de réception du destinataire rencontre un dysfonctionnement</li>'.
					'<li>- Un système de filtrage du serveur du destinataire empêche la réception du message</li>'.
				'</ul>'
			),	
			'bounced'			=> __( 
				'(État permanent) Adresse email de destination pour laquelle le message n\'a pu être distribué pour l\'une des raisons suivantes :<br/>'.
				'<ul>'.
					'<li>- L\'adresse email est incorrecte (mal orthographiée)</li>'.
					'<li>- Cette adresse email n\'existe pas ou plus</li>'.
				'</ul>'
			),
			'rejected'			=> __( 
				'(État temporaire) Adresse email de destination enregistrée en liste noire pour les raisons suivantes :<br/>'.
				'<ul>'.				
					'<li>- L\'adresse email de destination est déclarée comme un spam</li>'.
					'<li>- Le destinataire est déclaré comme désinscrit</li>'.
					'<li>- L\'adresse email a été ajoutée automatiquement dans la liste noire (Statut d\'envoi précédent en bounced)</li>'.
					'<li>- L\'adresse email a été ajoutée manuellement dans la liste noire</li>'.
				'</ul>'
			),					
			'unknown'			=> __( 
				'Le système n\'a pas été en mesure de récupérer les informations du mail :<br/>'.
				'<ul>'.
					'<li>- L\'identifiant fourni au moment de l\'envoi par Mandrill ne correspond à aucun email traité</li>'.
					'<li>- La date de conservation des informations est dépassée (90 jours)</li>'.
				'</ul>'
			),
			'spam'				=> __( 
				'Le destinataire du message à considéré que votre envoi était du spam.'
			)
		);
		/// Arguments de récupération des éléments
		$this->prepare_query_args = array(
			'campaign_id' 	=> ! empty( $_REQUEST['campaign_id'] ) ? $_REQUEST['campaign_id'] : 'any',
			'orderby' 		=> ! empty( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'posted_ts',
			'order' 		=> ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'DESC'
		);
	}
				
	/* = ORGANES DE NAVIGATION = */	
	/** == Filtrage avancé  == **/
	protected function extra_tablenav( $which ) {
	?>
		<div class="alignleft actions">
		<?php if ( 'top' == $which ) : ?>
			<label class="screen-reader-text" for="campaign_id"><?php _e( 'Filtre par campagne', 'tify' ); ?></label>
			<?php
				global $wpdb;
				
				$campaign_ids = $wpdb->get_col( "SELECT DISTINCT report_campaign_id FROM {$wpdb->wistify_report} ORDER BY report_posted_ts DESC" );
				
				wistify_campaigns_dropdown( 
					array(
						'show_option_all'	=> __( 'Toutes les campagnes', 'tify' ),
						'show_date'			=> '[d/m/Y] ',
						'selected' 			=> ! empty( $_REQUEST['campaign_id'] ) ? $_REQUEST['campaign_id'] : 0,
						'include'			=> $campaign_ids,
						'status'			=> array( 'forwarded', 'send' ),
						'order'				=> 'DESC',
						'orderby'			=> 'id'
					)
				); 
				submit_button( __( 'Filtrer', 'tify' ), 'button', 'filter_action', false, array( 'id' => 'campaign-query-submit' ) );?>
		<?php endif;?>
		</div>
	<?php
	}
			
	/* = COLONNES = */
	/** == Définition des colonnes == **/
	public function get_columns() {
		$c = array(
			//'cb'       				=> '<input type="checkbox" />',			
			'report_campaign' 		=> __( 'Campagne', 'tify' ),
			'report_infos' 			=> __( 'Informations', 'tify' ),
			'report_md_state' 		=> __( 'Statut', 'tify' ),
			'report_md_email'    	=> __( 'Destinataire', 'tify' ),
			//'report_md_sender'  	=> __( 'Expéditeur', 'tify' ),			
			//'report_md_subject' 	=> __( 'Sujet', 'tify' ),
			'report_md_opens' 		=> __( 'Ouvert', 'tify' ),
			'report_md_clicks' 		=> __( 'Clic', 'tify' ),
		);	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		$c = array(	
			'report_campaign' 		=> array( 'posted_ts', true ),
			'report_md_opens' 		=> array( 'md_opens', false ),
			'report_md_clicks' 		=> array( 'md_clicks', false ),
			'report_md_email' 		=> array( 'md_email', false )
		);

		return $c;
	}	
	
	/** == Contenu personnalisé : Mise à jour des infos == **/
	function column_report_campaign( $item ){
		$output  = "";	
		
		// Nom de la campagne	
		$filter_link = add_query_arg( 'campaign_id', $item->report_campaign_id, $this->base_link );		
		$output .= "<a href=\"". esc_url( $filter_link ) ."\" style=\"display:block;\">". wp_unslash( wistify_campaign_title( $item->report_campaign_id ) ) ."</a>";
		
		$output .= "<span style=\"display:block;color:#AAA;font-size:11px;\">". $this->master->db_campaign->get_item_var_by_id( $item->report_campaign_id, 'uid' ) ."</span>";
		
		// Date de mise à jour
		$date =  ( ! $item->report_updated_ts ) ? __( 'Jamais', 'tify' ) : date( __( 'd/m/Y à H:i:s', 'tify' ), $item->report_updated_ts );		
		$links = "<a href=\"#\" class=\"report_update\" data-report_id=\"{$item->report_id}\" data-ajax_nonce=\"". wp_create_nonce( "wistify_report_update-". $item->report_id ) ."\">". __( 'Rafraichir les infos', 'tify' ) ."</a>";		
		$output .= sprintf( __( 'Mise à jour : %s', 'tify' ), $date ) ."<br>". $links;
		
		return $output;
	}

	/** == Contenu personnalisé : Informations == **/
	function column_report_infos( $item ){
		$output  = "";
		$output .= 	"<ul style=\"padding:0;margin:0;line-height:1.2;\">";
		$output .= 		"<li style=\"padding:0;margin:0;font-size:13px;\"><strong>". __( 'ID : ', 'tify' ) ."</strong><span style=\"color:#666;\">#". $item->report_id ."</span></li>";
		$output .= 		"<li style=\"padding:0;margin:0;font-size:11px;\">".
							"<strong style=\"display:block\">"
								. __( 'Identifiant Mandrill du message :', 'tify' ) .
							"</strong>".
							"<span style=\"color:#666;\">". $item->report_md__id  ."</span>".
						"</li>";
		$output .= 		"<li style=\"padding:0;margin:0;font-size:11px;\">".
							"<strong style=\"display:block\">". 
								__( 'Posté le :', 'tify' ) .
							"</strong>".	
							"<span style=\"color:#666;\">". sprintf( __( '%s à %s', 'tify' ), date( 'd/m/Y', $item->report_posted_ts ), date( 'H\hi\m\i\n s\s', $item->report_posted_ts ) ) ."</span>".
						"</li>";
		$output .= 		"<li style=\"padding:0;margin:0;font-size:11px;\">".
							"<strong style=\"display:block\">". 
								__( 'Par :', 'tify' ) .
							"</strong>".	
							"<span style=\"color:#666;\">". $item->report_md_sender ."</span>".
						"</li>";
		$output .= 		"<li style=\"padding:0;margin:0;font-size:11px;\">".
							"<strong style=\"display:block\">".
								__( 'Acheminé au serveur :', 'tify' ) .
							"</strong>";
		$output .=	! $item->report_md_ts ? "<span style=\"color:#666;\">". __( 'Pas encore', 'tify' ) ."<span>" : "<span style=\"color:#666;\">". sprintf( __( '%s à %s', 'tify' ), date( 'd/m/Y', $item->report_md_ts + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ), date( 'H\hi\m\i\n s\s', $item->report_md_ts + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) ."</span>".
						"</li>";
		$output .= 	"</ul>";
		
		return $output;
	}
	
	/** == Contenu personnalisé : Etat == **/
	function column_report_md_state( $item ){
		$title 		= ( isset( $this->status_available[$item->report_md_state] ) ) ? $this->status_available[$item->report_md_state] : $item->report_md_state;
		$content 	= ( isset( $this->status_description[$item->report_md_state] ) ) ? $this->status_description[$item->report_md_state] : $item->report_md_state;
		$txt		= "#333";
		$bkg		= "#FFF";
		$border		= "#CCC";		
		switch( $item->report_md_state ):
			case 'posted' :
				$txt = "#FFF"; $bkg = '#5BC0DE'; $border = '#46b8da';
				break;
			case 'deferred' :
				$txt = "#FFF"; $bkg = '#FF9E00'; $border = '#E68E00';
				break;
			case 'sent' :
				$txt = "#FFF"; $bkg = '#5CB85C'; $border = '#4cae4c';
				break;
			case 'rejected' :
				$txt = "#FFF"; $bkg = '#4B4B4B'; $border = '#000';
				break;	
			case 'bounced' :
				$txt = "#FFF"; $bkg = '#D9534F'; $border = '#d43f3a';
				break;
			case 'soft-bounced' :
				$txt = "#FFF"; $bkg = '#F0AD4E'; $border = '#eea236';
				break;
			case 'spam' :
				$txt = "#FFF"; $bkg = '#2B2B2B'; $border = '#1B1B1B';
				break;				
		endswitch;	
		
		return "<style>.popover .arrow{display:none;}</style><button type=\"button\" data-toggle=\"popover\" data-trigger=\"hover\" data-placement=\"right\" data-html=\"true\" title=\"{$title}\" data-content=\"{$content}\" style=\"display:inline-block;padding:2px 20px;border-radius:3px;border:solid 1px {$border};background-color:{$bkg};color:{$txt};cursor:pointer;line-height:1.1;\">{$title}</a>";
	}
}