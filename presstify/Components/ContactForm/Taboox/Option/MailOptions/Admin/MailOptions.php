<?php
namespace tiFy\Components\ContactForm\Taboox\Option\MailOptions\Admin;

use tiFy\Core\Taboox\Admin;

class MailOptions extends Admin
{
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		register_setting( $this->page, 'page_for_'. $this->args['id'] );
		register_setting( $this->page, $this->args['id'] .'-confirmation' );
		register_setting( $this->page, $this->args['id'] .'-sender', array( $this, 'sanitize_sender' ) );
		register_setting( $this->page, $this->args['id'] .'-notification' );
		register_setting( $this->page, $this->args['id'] .'-recipients', array( $this, 'sanitize_recipients' ) );		
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'switch' );
		tify_control_enqueue( 'dynamic_inputs' );
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
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
								'name' 				=> 'page_for_'. $this->args['id'],
								'post_type' 		=> 'page',
								'selected' 			=> get_option( 'page_for_'. $this->args['id'], 0 ),
								'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ),
								'sort_column'  		=> 'menu_order' 
							)
						);
					?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3><?php _e( 'Message de confirmation de réception de la demande', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Envoyer un message de confirmation de réception à l\'utilisateur', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => $this->args['id'] .'-confirmation', 'checked' => get_option( $this->args['id'] .'-confirmation', 'on' ) ) );?>
				</td>
				
				<?php $s = get_option( $this->args['id'] .'-sender' );?>
				<?php $value['email'] = ! empty( $s['email'] ) ?  $s['email'] : get_option( 'admin_email' ); $value['name'] = ! empty( $s['name'] ) ? $s['name'] : '';?>
				<tr>
					<th scope="row"><?php _e( 'Email de l\'expéditeur (requis)', 'tify' );?></th>
					<td>
						<div class="tify_input_email">
							<input type="text" name="<?php echo $this->args['id'];?>-sender[email]" placeholder="<?php _e( 'Email (requis)', 'tify' );?>" value="<?php echo $value['email'];?>" size="40" autocomplete="off">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Nom de l\'expéditeur (optionnel)', 'tify' );?></th>
					<td>
						<div class="tify_input_user">
							<input type="text" name="<?php echo $this->args['id'];?>-sender[name]" placeholder="<?php _e( 'Nom (optionnel)', 'tify' );?>" value="<?php echo $value['name'];?>" size="40" autocomplete="off">
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
					<td><?php tify_control_switch( array( 'name' => $this->args['id'] .'-notification', 'checked' => get_option( $this->args['id'] .'-notification', 'off' ) ) );?>
				</td>
			</tbody>
		</table>
		<hr>
		<?php 
			tify_control_dynamic_inputs( 
				array( 
					'default' 			=> 	array( 'name'=> '', 'email' => get_option( 'admin_email' ) ),
					'add_button_txt'	=> 	__( 'Ajouter un destinataire', 'tify' ),
					'values' 			=>	get_option( $this->args['id'] .'-recipients' ), 
					'name' 				=> 	$this->args['id'] .'-recipients',
					'sample_html'		=> 	"<table class=\"form-table\">\n".
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