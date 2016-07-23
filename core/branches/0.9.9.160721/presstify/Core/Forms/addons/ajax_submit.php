<?php
/*
Addon Name: AjaxSubmit
Addon ID: ajax_submit
Callback: tiFy_Forms_Addon_AjaxSubmit
Version: 2.160707
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

class tiFy_Forms_Addon_AjaxSubmit extends tiFy_Forms_Addon
{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forms $master )
	{		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_redirect'			=> array( 'function' => array( $this, 'cb_handle_redirect' ), 'order' => 99 ),
			'form_after_output_display'	=> array( $this, 'cb_form_after_output_display' )
		);
		
		add_action( 'wp_ajax_tify_forms_ajax_submit', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_forms_ajax_submit', array( $this, 'wp_ajax' ) );
		
		parent::__construct( $master );
	}
	
	/* = CALLBACKS = */
	public function cb_handle_redirect( &$location )
	{
		return $location = false;
	}
	
	
	/** == Mise en queue du script de tratiement dans le footer == **/
	/* = @todo Limiter le nombre d'instance à 1 execution par formulaire =*/
	public function cb_form_after_output_display( &$output, $form )
	{	
		$ID = $this->master->forms->get_ID();
		$wp_footer = function() use ( $ID )
		{
			?><script type="text/javascript">/* <![CDATA[ */
			// Tâche à effectuer au retour de la soumission de formulaire
			// @todo : permettre de desactiver  -> $( document ).off( 'tify_forms.ajax_submit.response','#tify_form_1', tify_forms_ajax_submit ); **/
		
			function tify_forms_ajax_submit_response( e, resp ){
				if( resp.data !== undefined )
					$( e.target ).closest( '.tify_form_container' ).replaceWith( resp.data.html );
			}
			
			jQuery( document ).ready( function($){
				// Définition des variables		
				var container = '#tify_form_<?php echo $ID;?>';
				$( container ).append( '<div class="tify_forms_overlay tify_forms_overlay-<?php echo $ID;?>" />' );
								
				// Evénement déclenché au retour de la soumission du formulaire
				$( document ).on( 'tify_forms.ajax_submit.response', container, tify_forms_ajax_submit_response );	

				// Action déclenchée à la soumission du formulaire
				$( document ).on( 'submit', 'form'+ container, function(e){			
					e.preventDefault();
					
					var data = $( this ).serialize();
		
					$( container ).trigger( 'tify_forms.ajax_submit.init', data );

					$.ajax({
						url 		: tify_ajaxurl +'?action=tify_forms_ajax_submit',
						data  		: data,
						type 		: 'post',
						beforeSend 	: function(){
							$( '.tify_forms_overlay-<?php echo $ID;?>', container ).fadeIn();
						},
						success 	: function( resp ){						
							$( container ).trigger( 'tify_forms.ajax_submit.response', resp );
						},
						complete 	: function(){
							$( container ).trigger( 'tify_forms.ajax_submit.complete' );
							$( container ).append( '<div class="tify_forms_overlay tify_forms_overlay-<?php echo $ID;?>" />');	
						}					
					});		
					
					return false;
				});			
			});
			/* ]]> */</script><?php
		};		
		add_action( 'wp_footer', $wp_footer, 99 );
		
		return $output;
	}
	
	/* = = */
	final public function wp_ajax()
	{
		if( ! $current = $this->master->forms->get() )
			return;
		
		if( $this->master->errors->has( $current['ID'] ) )	:
			wp_send_json_error( array( 'html' => $this->master->forms->display( $current['ID'], false ), 'message' => $this->master->errors->display( $current['ID'] ) ) );
		else :	
			$session = $this->master->datas->session_get();	
			if( ! $this->master->datas->transient_has( $session ) ) :
				$message = 'Votre session de soumission de formulaire est invalide ou arrivée à expiration';
			else :
				$success = $this->master->forms->get_option( 'success' );
				$message = ( ( $cache = $this->master->datas->transient_get() ) && ! empty( $cache['success']['message'] ) ) ? $cache['success']['message'] : $success['message'];
			endif;
			wp_send_json_success( array( 'html' => $this->master->forms->display( $current['ID'], false ), 'message' => $message ) );
		endif;
	}
	
}