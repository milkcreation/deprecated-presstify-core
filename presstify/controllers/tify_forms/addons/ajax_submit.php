<?php
/*
Addon Name: AjaxSubmit
Addon ID: ajax_submit
Callback: tiFy_Forms_Addon_AjaxSubmit
Version: 2.160130
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
			jQuery( document ).ready( function($){
				var container = '#tify_form_<?php echo $ID;?>';
				$( container ).append( '<div class="overlay" />');
				$( document ).on( 'submit', 'form'+ container, function(e){			
					e.preventDefault();
					
					var data = { };
					$.each( $(this).serializeArray(), function(n, i){
				    	data[i.name] = i.value;
					});

					$.ajax({
						url 		: tify_ajaxurl +'?action=tify_forms_ajax_submit',
						data  		: data,
						type 		: 'post',
						beforeSend 	: function(){
							$( '.overlay', container ).fadeIn();
						},
						success 	: function( result ){
							$( container ).closest( '.tify_form_container' ).replaceWith( result );
							$( document ).trigger( 'tify_forms_ajax_submit' );
						},
						complete 	: function(){
							$( '.overlay', container ).fadeOut();
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
		$this->master->init();
		if( ! $current = $this->master->forms->get() )
			return;

		wp_send_json( $this->master->forms->display( $current['ID'], false ) );
	}
	
}