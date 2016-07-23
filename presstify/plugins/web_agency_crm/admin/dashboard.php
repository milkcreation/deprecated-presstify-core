<?php
class tiFy_WebAgencyCRM_AdminDashboard{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;
	}

	/* = VUES = */
	public function admin_render(){
	?>
		<div class="wrap">
			<h2><?php _e( 'Web Agency CRM', 'tify' );?></h2>
			<?php
				/** @see http://doc.rpc.gandi.net/index.html **/ 
				set_include_path( tify_get_directory() .'/assets/PEAR-1.10.1' );				
				require_once( 'XML/RPC2/Client.php' );
								
				$apikey 		= 'usTTvLBJOGl6F9lU2rZepABQ';
				$api_uri	 	= 'https://rpc.gandi.net/xmlrpc/'; 
				
				$api = XML_RPC2_Client::create( $api_uri, array( 'prefix' => 'domain.mailbox.', 'sslverify' => False ) );
				$result = call_user_func_array( array( $api, 'list' ), array( $apikey, 'milkcreation.fr' ) );
				var_dump( $result );
			?>
		</div>
	<?php
	}
}