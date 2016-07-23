<?php
namespace tiFy\Component\ContactForm\Taboox\Option;

use tiFy\Taboox\Admin;

class ContactForm extends Admin
{
	/* = ARGUMENTS = */
	private $master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $master )
	{	
		parent::__construct();
		
		$this->master = $master;	
	}
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		if( $this->master->Options['hookpage'] )	
			register_setting( $this->page, 'page_for_'. $this->master->ID );
		register_setting( $this->page, $this->master->ID .'-confirmation' );
		register_setting( $this->page, $this->master->ID .'-sender', array( $this, 'sanitize_sender' ) );
		register_setting( $this->page, $this->master->ID .'-notification' );
		register_setting( $this->page, $this->master->ID .'-recipients', array( $this, 'sanitize_recipients' ) );		
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'switch' );
		tify_control_enqueue( 'dynamic_inputs' );
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public function form( $_args = array() )
	{
		if( $this->master->Options['hookpage'] ) :
	?>
		<h3><?php _e( 'Affichage du formulaire', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Page  d\'affichage du formulaire de contact', 'tify' ); ?></th>
					<td>
					<?php 
						wp_dropdown_pages(
							array(
								'name' => 'page_for_'. $this->master->ID,
								'post_type' => 'page',
								'selected' => get_option( 'page_for_'. $this->master->ID, 0 ),
								'show_option_none' => __( 'Aucune page choisie', 'tify' ),
								'sort_column'  => 'menu_order' 
							)
						);
					?>
					</td>
				</tr>
			</tbody>
		</table>
	<?php	endif;?>
		<h3><?php _e( 'Message de confirmation de réception de la demande', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Envoyer un message de confirmation de réception à l\'utilisateur', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => $this->master->ID .'-confirmation', 'checked' => get_option( $this->master->ID .'-confirmation', 'on' ) ) );?>
				</td>
				
				<?php $s = get_option( $this->master->ID .'-sender' );?>
				<?php $value['email'] = ! empty( $s['email'] ) ?  $s['email'] : get_option( 'admin_email' ); $value['name'] = ! empty( $s['name'] ) ? $s['name'] : '';?>
				<tr>
					<th scope="row"><?php _e( 'Email de l\'expéditeur (requis)', 'tify' );?></th>
					<td>
						<div class="tify_input_email">
							<input type="text" name="<?php echo $this->master->ID;?>-sender[email]" placeholder="<?php _e( 'Email (requis)', 'tify' );?>" value="<?php echo $value['email'];?>" size="40" autocomplete="off">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Nom de l\'expéditeur (optionnel)', 'tify' );?></th>
					<td>
						<div class="tify_input_user">
							<input type="text" name="<?php echo $this->master->ID;?>-sender[name]" placeholder="<?php _e( 'Nom (optionnel)', 'tify' );?>" value="<?php echo $value['name'];?>" size="40" autocomplete="off">
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<h3><?php _e( 'Message de notification aux administrateurs', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Envoyer un message de notification aux administrateurs du site', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => $this->master->ID .'-notification', 'checked' => get_option( $this->master->ID .'-notification', 'off' ) ) );?>
				</td>
			</tbody>
		</table>
		<hr>
		<?php 
			tify_control_dynamic_inputs( 
				array( 
					'default' 			=> array( 'name'=> '', 'email' => get_option( 'admin_email' ) ),
					'add_button_txt'	=> __( 'Ajouter un destinataire', 'tify' ),
					'values' 			=> get_option( $this->master->ID .'-recipients' ), 
					'name' 				=> $this->master->ID .'-recipients',
					'sample_html'		=> 	
						"<table class=\"form-table\">\n".
						"\t<tbody>\n".
						"\t\t<tr>\n".
						"\t\t\t<th scope=\"row\">". __( 'Email du destinataire (requis)', 'tify' ) ."</th>\n".
						"\t\t\t<td>\n".
						"\t\t\t\t<div class=\"tify_input_email\">\n".
						"\t\t\t\t\t<input type=\"text\" name=\"%%name%%[%%index%%][email]\" value=\"%%value%%[email]\" placeholder=\"". __( 'Email de l\'expéditeur', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n".
						"\t\t\t\t</div>\n".
						"\t\t\t</td>\n".
						"\t\t</tr>\n".
						"\t\t<tr>\n".
						"\t\t\t<th scope=\"row\">". __( 'Nom du destinataire (optionnel)', 'tify' ) ."</th>\n".
						"\t\t\t<td>\n".
						"\t\t\t\t<div class=\"tify_input_user\">\n".
						"\t\t\t\t\t<input type=\"text\" name=\"%%name%%[%%index%%][name]\" value=\"%%value%%[name]\" placeholder=\"". __( 'Nom de l\'expéditeur', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n".
						"\t\t\t\t</div>\n".
						"\t\t\t</td>\n".
						"\t\t</tr>\n".	
						"\t</tbody>\n".
						"</table>"	 
				) 
			);
		?>
	<?php
	}
	
	/* = INTEGRITE = */
	/** == Vérification du format de l'email de l'expéditeur == **/
	function sanitize_sender( $sender )
	{
		if( empty( $sender['email'] ) ) :
			add_settings_error( $this->page, 'sender-email_empty', sprintf( __( 'L\'email "%s" ne peut être vide', 'tify' ), __( 'Expéditeur du message de confirmation de reception', 'tify' ) ) ); 
		elseif( ! is_email( $sender['email'] ) ) :
			add_settings_error( $this->page, 'sender-email_format', sprintf( __( 'Le format de l\'email "%s" n\'est pas valide' ), __( 'Expéditeur du message de confirmation de reception', 'tify' ) ) ); 
		endif;
		
		return $sender;
	} 
	
	/** == Vérification du format de l'email du destinataire de notification == **/
	function sanitize_recipients( $recipients )
	{
		foreach( (array) $recipients as $recipient => $recip ) :
			if( empty( $recip['email'] ) ) :
				add_settings_error( $this->page, $recipient .'-email_empty', sprintf( __( 'L\'email du destinataire des messages de notification #%d ne peut être vide', 'tify' ), $recipient+1 ) ); 
			elseif( ! is_email( $recip['email'] ) ) :
				add_settings_error( $this->page, $recipient .'-email_format', sprintf( __( 'Le format de l\'email du destinataire des messages de notification #%d n\'est pas valide', 'tify' ), $recipient+1 ) ); 
			endif;
		endforeach;
		
		return $recipients;
	}
}