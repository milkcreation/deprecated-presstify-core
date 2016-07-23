jQuery(document).ready(function($){
	// Déclaration des variables
	var count_action, // Action de récupération du nombre d'enregistrement 
		execute_action, // Action à executer		
		from, // Ligne à partir de laquelle commencer l'action 
		to, // Ligne au delà de laquelle stopper l'action (Dernière ligne à traiter)
		total_rows, // Nombre total de lignes d'enregistrements à traiter
		interrupt_action, // Statuts d'interruption de l'action
		batch, // Traitement par lot
		logs, // Affichage des logs 
		time_start; // Démarrage de la requête
		
	var old_url,
		new_url;
		
	// Elements DOM  
	var $container = $("#mkreloc-tools ");
	var $progress = $( "#progress", $container );
	var $progressbar = $( "#progressbar", $container );
	var $progressLabel = $( "#progressbar .label", $container );
	var $countdown = $( "#countdown", $container );
	var $logs = $('#logs', $container);
	
	// Clic sur un des bouton d'import
	$( '.action', $container ).click( function(e){
		e.preventDefault();
		// Initialisation des variables
		interrupt_action = false;
		time_start = new Date();		
		old_url = $( '#old_url', $container ).val();
		new_url = $( '#new_url', $container ).val();	
			
		// Nettoyage des élément du DOM
		$progress.hide();
		$logs.empty();
		$( '.detail-value', $progress ).empty();
		
		// Récupération des options
		from = parseInt( $( '#from', $container).val() ) || 1;
		to = parseInt( $( '#to', $container).val() ) || 0;
		batch = parseInt( $( '#batch', $container).val() ) || 1;
		if( $('#log-all', $container ).is( ':checked' ) )
			logs = 1;
		else
			logs = 0;	
			
		count_action = $(this).data('count_action');
		execute_action = $(this).data('execute_action'); 
		total_rows = to-from;
		
		// Lancement de l'import
		if( total_rows < 0 ){
			count_rows( count_action );
		} else {
			init_progressbar( total_rows+1 );
			populate_details( from );
			current_line_handle( execute_action );
		}		
	});
	
	// Interruption de traitement
	$( '#interrupt', $container ).click( function(e){
		e.preventDefault();
		interrupt_action = true;
		$progressLabel.text( "En cours d'interruption ..." );
		false;
	});
	
	// Calcul du nombre ligne a traiter
	function count_rows( count_action ){		
			$.ajax({
			url 		: ajaxurl, 
			data		: { action: 'count_relocate_action', old_url : old_url, count_action : count_action },
			dataType 	: 'json',
			type		: 'post',
			success		: function( max ){
				to = max;
				total_rows = to-from;
				init_progressbar(total_rows+1);
				populate_details( from );			
				current_line_handle( execute_action );
			}
		});
	}
	
	// Initialisation de la barre de progression
	function init_progressbar( total ){
		$progress.show();
		$progressbar.progressbar({
			value: 0,
			max : total,
			change: function() {
				$progressLabel.text( ( ( 100/total )*$progressbar.progressbar( "value" ) ).toFixed(2) + "%" );
			},
			complete: function() {
				$progressLabel.text( "Importation terminée" );
			}
		});
	}
		
	// Traitement de la ligne courante
	function current_line_handle( action ){
		time_start = new Date();

		$.ajax({
			url 		: ajaxurl, 
			data		: { action:action, from:from, to:to, nb:batch, old_url:old_url, new_url:new_url },
			dataType 	: 'json',
			type		: 'post',
			success		: function(data){
				from +=batch;
				if( data.html ){										
					$( '#last-registred', $container ).empty().html( data.html );
					
					if( logs )
						$logs.prepend( data.html );
					
					if( interrupt_action )
						$progressLabel.text( "Interrompu" );
					
					if( ! interrupt_action )
						$progressbar.progressbar( 'value', ( from - 1 ) );
													
					if( ! interrupt_action && ( from <= to ) )
						current_line_handle( action );
				}
				if( data.error )
					$logs.prepend( data.error );
			},
			complete: function(jqXHR, textStatus){				
				populate_details( from );						
			}
		});
	}
	// Renseigne les détails de l'action
	function populate_details( start ){
		if( start<=to ){
			// Décompte
			if( batch > 1 ){
				var end = start+batch;
				if( end > total_rows ) end = total_rows;
				$countdown.html( start+'-'+end+'/'+to );
			} else {
				$countdown.html( start+'/'+to );
			}
			// Temps restant estimé				
			var time_end = new Date();
			var time_diff = ( ( time_end - time_start)/1000)*( total_rows-(start-1) );
			$( '#estimated-time', $container ).html( time_diff+'s' );
		} else {
			$countdown.html( to+'/'+to );
			$( '#estimated-time', $container ).html( '0s' );
		}
	}	
});