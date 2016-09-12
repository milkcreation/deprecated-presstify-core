<?php
class tiFy_FacebookSDKAccountLinking{
	/* = ARGUMENTS = */
	private	$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_FacebookSDK $master ){
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		do_action( 'show_user_profile', array( $this, 'show_user_profile' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	public function show_user_profile(){
	?>
		<table class="form-table">
			<tr>
				<th><?php _e( 'Liaison au compte Facebook' ); ?></th>
				<td>
					<a href="#"><span class="dashicons dashicons-facebook-alt"></span><?php _e( 'Associer à Facebook', 'tify' );?></a>
				</td>
			</tr>
		</table>
	<?php
	}
}