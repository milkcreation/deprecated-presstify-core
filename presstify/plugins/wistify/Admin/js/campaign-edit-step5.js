jQuery(document).ready( function($){
	/** == ENVOI D'UN MAIL DE TEST POUR LA CAMPAGNE == */
	$( '#send-test-submit > button' ).click( function(e){
		e.preventDefault();
		
		// Déclaration des variables
		$this		= $(this);
		$closest 	= $this.closest('div');
		
		// Activation du moniteur d'activité et bloquage de saisie
		$this.addClass( 'active' );
		$( 'input[type="text"]', $closest ).prop( 'readonly', true );
		
		$.post( 
			tify_ajaxurl,
			{ 
				action 			: 'wistify_messages_send',
				_wty_ajax_nonce : $( '#_wty_messages_send_ajax_nonce', $closest ).val(),
				campaign_id		: $( '#campaign_id' ).val(),
				service_account : $( '#wistify_messages_send_service_account', $closest ).val(),
				recipient_email	: $( '#wistify_messages_send_to_email', $closest ).val(),
				message 		: {
					subject : $( '#wistify_messages_send_subject', $closest ).val()											
				}
			}, 
			function( resp ){
				// Désactivation du moniteur d'activité et débloquage de saisie
				$this.removeClass( 'active' );
				$( 'input[type="text"]', $closest ).prop( 'readonly', false );
				
				// Affichage des résultats
				$.each( resp['data'], function( u, v ){					
					$( '#send-test-resp > .'+u ).html( v );
				});				
			},
			'json'
		);
	});
	
	/** == PREPARATION DES ENVOIS == **/
	/*** === Déclaration des variables === ***/
	/* = ARGUMENTS = */
	var campaign_id = 0, 	// Identifiant de la campagne
		/// Totaux
		total 		= 0,	// Nombre total d'abonnés à traité
		processed 	= 0,	// Nombre total d'abonnés traités
		enqueue		= 0,	// Nombre total d'abonnés dans la file
				 
		recipients 	= [],	// Abonnements à traiter
		types 		= [],	// Liste des types d'abonnement à traiter
		count 		= [],	// Nombre d'abonnés par type		
		per_page 	= 100,	// Nombre d'abonné par passe 
		paged 		= 1,	// Passe courante 
		type 		= 0,	// Type d'abonnement en cours de traitement
		
		/// Réponse
		emails		= [],	// Emails des abonnés à traiter
		/// Erreurs
		duplicate	= [],	// Doublons
		invalid		= [],	// Email invalide
		
		list_id 	= 0, 
		list_index 	= 0;	
				
	/*** === Eléments du DOM === ***/
	var	$progress 	= $( '#tify_progress' ),
		$backdrop 	= $( '#tify_backdrop' ),		
		$logs		= $( '#prepare #logs' ),
		$totals		= $( '#prepare #totals' ),
		$set_send	= $( '#programmation #set_send' );		
		
	/*** === Lancement de la préparation de la campagne === ***/			
	$( '#campaign-prepare' ).click( function(e){
		e.preventDefault();
		
		// Réinitialisation globale
		reset( true, true, true, true, true );
		
		// Récupération de l'id de la campagne		
		campaign_id = $( 'input#campaign_id' ).val();
		
		// Informations de progression		
		$( '.progress-bar', $progress ).css( 'width', 0 );
		$( 'infos', $progress ).html( wistify_campaign.preparing );
		$progress.addClass( 'active' ); $backdrop.addClass( 'active' );
		
		wistify_campaign_prepare();
	});
	
	/*** === Lancement de la préparation de la campagne === ***/
	function wistify_campaign_prepare(){
		$.ajax({
			url 		: tify_ajaxurl,
			data 		: { action : 'wistify_campaign_prepare', campaign_id : campaign_id }, 
			success		: function( resp ){
				recipients = resp.recipients;
				types = resp.types;
				count = resp.count;
				total = resp.total;
				$( ".expected .value", $totals ).text( total );
				$( '.text-bar .total', $progress ).html( ' '+ wistify_campaign.total_in +' '+ total );
			
				wistify_campaign_prepare_recipients();				
			},
			dataType	: 'json'			
		});
	}
	
	/*** === Mise en file des destinataires en base === ***/
	function wistify_campaign_prepare_recipients(){
		if( processed < total ){
			$( '.progress-bar', $progress ).css( 'width', parseInt( ( ( processed/total )*100 ) )+'%' );
			$( '.text-bar .current', $progress ).html( processed +' '+ wistify_campaign.emails_ready +' ' );	
					
			switch( types[type]){
				case 'wystify_subscriber' :
					var start =  (paged-1)*per_page, end = start+per_page,					
						subscriber_ids = recipients[types[type]].slice( start, end );
					
					if( subscriber_ids.length ){
						$.ajax({
							url 		: tify_ajaxurl,
							data 		: { 
								action 			: 'wistify_campaign_prepare_recipients_subscriber', 
								campaign_id 	: campaign_id,
								subscriber_ids	: subscriber_ids				
							}, 
							success		: function( resp ){
								processed 	+= resp.processed;
								enqueue 	+= resp.enqueue;
								
								if( resp.errors ){
									if( resp.errors.duplicate )
										$.each( resp.errors.duplicate, function(u,v){ duplicate.push(v); });
									if( resp.errors.invalid )
										$.each( resp.errors.invalid, function(u,v){ invalid.push(v); });
								}
									
								if( ! resp.processed ){
									paged = 1;
									type ++;
								} else {
									paged ++;
								}					
								wistify_campaign_prepare_recipients();
							},
							dataType	: 'json'			
						});
					} else {
						paged = 1;
						type ++;
						wistify_campaign_prepare_recipients();
					}					
					break;
				case 'wystify_mailing_list' :
					var list_id = recipients[types[type]][list_index];
					if( list_id ){						
						$.ajax({
							url 		: tify_ajaxurl,
							data 		: { 
								action 		: 'wistify_campaign_prepare_recipients_mailing_list', 
								campaign_id : campaign_id,
								list_id		: list_id,
								paged 		: paged,
								per_page 	: per_page					
							}, 
							success		: function( resp ){
								processed 	+= resp.processed;
								enqueue 	+= resp.enqueue;
								
								if( resp.errors ){
									if( resp.errors.duplicate )
										$.each( resp.errors.duplicate, function(u,v){ duplicate.push(v); });
									if( resp.errors.invalid )
										$.each( resp.errors.invalid, function(u,v){ invalid.push(v); });
								}
								
								if( ! resp.processed ){
									paged = 1;
									list_index++;
									if( ! recipients[types[type]][list_index] )
										type ++;
								} else {
									paged ++;
								}					
								wistify_campaign_prepare_recipients();
							},
							dataType	: 'json'			
						});
					} else {
						paged = 1;
						type ++;
						wistify_campaign_prepare_recipients();
					}	
					break;
			}	
		} else {
			// Rapport de préparation
			/// Doublons
			if( duplicate ){
				$( ".duplicates", $logs ).show();
				$( ".duplicates .total", $logs ).text( duplicate.length );
				$.each( duplicate, function( u, v ){
					$( ".duplicates > ul", $logs ).append( '<li>'+ v +'</li>' );	
				});
			}
			/// Emails invalides
			if( invalid ){
				$( ".invalids", $logs ).show();
				$( ".invalids .total", $logs ).text( invalid.length );
				$.each( invalid, function( u, v ){
					$( ".invalids > ul", $logs ).append( '<li>'+ v +'</li>' );	
				});
			}
			
			// Total des emails traités
			$( ".processed .value", $totals ).text( enqueue );			
			
			// Barre de progression
			$( '.progress-bar', $progress ).css( 'width', '100%' );
			$( '.text-bar .current', $progress ).html( processed +' '+ wistify_campaign.emails_ready +' ' );
			
			// Mise à jour du status de la campagne
			$.post( 
				tify_ajaxurl, 
				{ action : 'wistify_campaign_prepare_update_status', campaign_id : campaign_id, enqueue : enqueue }, 
				function( resp ){
					if( resp.success ){
						$set_send.addClass( 'active' ).find('input[type="checkbox"]').prop( 'disabled', false );
						$( '#campaign_status' ).val( 'ready' );
					}
					// Réintialisation
					reset( true, true, false, false, false );
				}			
			);										
		}	
	}
	
	/*** === Réinitialisation des éléments === ***/
	function reset( vars, progress, logs, totals, set_send ){
		// Argumments
		if( vars === true ){
			campaign_id = 0, 	// Identifiant de la campagne
			/// Totaux
			total 		= 0,	// Nombre total d'abonnés à traité
			processed 	= 0,	// Nombre total d'abonnés traités
			enqueue		= 0,	// Nombre total d'abonnés dans la file
					 
			recipients 	= [],	// Abonnements à traiter
			types 		= [],	// Liste des types d'abonnement à traiter
			count 		= [],	// Nombre d'abonnés par type		
			per_page 	= 250,	// Nombre d'abonné par passe 
			paged 		= 1,	// Passe courante 
			type 		= 0,	// Type d'abonnement en cours de traitement
			
			/// Réponse
			emails		= [],	// Emails des abonnés à traiter
			duplicates	= [],	// Doublons
			
			list_id 	= 0, 
			list_index 	= 0;
		}
		
		// Barre de progression
		if( progress === true ){
			$progress.removeClass( 'active' );
			$( '.progress-bar', $progress ).css( 'width', 0 );
			$( '.text-bar .current', $progress ).text('');
			$( '.text-bar .total', $progress ).text('');
			$( 'infos', $progress ).text('');

			$backdrop.removeClass( 'active' );
		}
		
		// Logs
		if( logs === true ){
			$( '> *', $logs ).hide();
			$( '.total', $logs ).each( function(){ $(this).text(''); });
			$( 'ul', $logs ).empty();
		}
		
		// Totaux
		if( totals === true ){
			$( '.value', $totals ).each( function(){ $(this).text(''); });
		}
		
		// 
		if( set_send === true ){
			$set_send.removeClass( 'active' ).find( 'input[type="checkbox"]' ).prop( 'disabled', 'disabled' ).prop( 'checked', false );
		}
	}
});