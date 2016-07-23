<?php
/**
 * @name FORMS - AJAX SUBMIT
 * 
 * @package Milk_Thematzr
 * @subpackage Forms
 */

class mktzr_forms_addon_ajax_submit{
	/**
	 * Initialisation
	 */
	function __construct(){
		global $mktzr_forms;
		$this->mkcf = $mktzr_forms;
		
		// Court circuitage de la redirection
		$this->mkcf->callbacks->addons_set( 'handle_redirect', 'ajax_submit', '__return_empty_string' );
	}
}
new mktzr_forms_addon_ajax_submit(); 

/**
 * Mise en queue des scripts dans le footer
 */
function mkcf_forms_ajax_wp_footer(){
	global $mktzr_forms;
	
	if( ! $mktzr_forms )
		return;
	
	// Récupération des formulaires actifs pour l'add-on
	$containers = array();
	foreach( (array) $mktzr_forms->addons->get_forms_active( 'ajax_submit' ) as $form )
		$containers[] = 'mkcf_container_'.$form;
		
?><script type="text/javascript">/* <![CDATA[ */	
jQuery( document ).ready( function($){
	var container = $.parseJSON( '<?php echo addslashes( json_encode( $containers ) );?>');
	$.each( container, function(u,v){
		var $container = $( '#'+v );
		$container.append( '<div class="overlay" />');
		$( document ).on( 'click', '#'+v+' form button[type="submit"]', function(e){			
			e.preventDefault();
			
			var data = { action : 'mktzr_forms_ajax_submit' };
			data[ $(this).attr('name') ] = $(this).val();
			$.each( $(this).closest('form').serializeArray(), function(n, i){
		    	data[i.name] = i.value;
			});

			$.ajax({
				url : '<?php echo admin_url('/admin-ajax.php');?>',
				data  : data,
				type : 'post',
				dataType : 'json',
				beforeSend : function(){
					$( '.overlay', $container ).fadeIn();
				},
				success : function( result ){
					console.log( result );
					if( result.errors ){												
						$( '.error', $container ).fadeOut( function(){
							$container.html( result.form ).append( '<div class="overlay" />');
							$(this).html( result.errors ).fadeIn();
						});
					} else if( result.success ){
						$( '.success', $container ).fadeIn();
						$( 'form', $container ).hide();
					}
				},
				complete : function(){
					$( '.overlay', $container ).fadeOut();
				}					
			});
			return false;
		});
	});
});
/* ]]> */</script><?php
}
add_action( 'wp_footer', 'mkcf_forms_ajax_wp_footer' );

/**
 * Soumission Ajax du formulaire 
 */
function mkcf_forms_ajax_submit(){
	global $mktzr_forms;	

	$current = false;
	foreach( (array) $mktzr_forms->addons->get_forms_active( 'ajax-submit' ) as $fid ) :
		$_form = $mktzr_forms->forms->get( $fid );		
		if( ! isset( $_POST['_'.$_form['prefix'].'_nonce'] ) )
			continue;		
		// Récupération de l'id du formulaire	
		if( empty( $_POST[ $_form['prefix'].'-form_id'] ) )
			continue;		
		// Récupération de session du formulaire	
		if( empty( $_POST[ 'session-'.$_form['prefix'].'-'.$_form['ID'] ] ) )
			continue;		
		$current = $mktzr_forms->forms->set_current($_form);
		break;	
	endforeach;

	if( ! $current )
		return;	
	
	if( $errors = $mktzr_forms->errors->has( ) )
		$result = array( 'errors' => $mktzr_forms->errors->display(), 'form' => $mktzr_forms->forms->display( $current['ID'], array( 'echo' => false ) ) );	
	else 
		$result = array( 'success' => $mktzr_forms->handle->get_session() );

	echo json_encode( $result );
	exit;		
}
add_action( 'wp_ajax_mktzr_forms_ajax_submit', 'mkcf_forms_ajax_submit' );
add_action( 'wp_ajax_nopriv_mktzr_forms_ajax_submit', 'mkcf_forms_ajax_submit' );