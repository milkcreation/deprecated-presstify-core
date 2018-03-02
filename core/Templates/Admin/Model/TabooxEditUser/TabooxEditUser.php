<?php
namespace tiFy\Core\Templates\Admin\Model\TabooxEditUser;

use tiFy\Core\Taboox\Taboox;

class TabooxEditUser extends \tiFy\Core\Templates\Admin\Model\EditUser\EditUser
{					
	/* = ARGUMENTS = */
	private $MenuSlug;
	private $Hookname;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Actions et Filtres PressTiFy		
		add_action( 'tify_taboox_register_box', array( $this, '_tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, '_tify_taboox_register_node' ) );
	}
	
	/* = PARAMETRES = */
	/** == Définition des sections d'édition == **/
	public function set_sections()
	{
	    if( $sections = $this->getConfig( 'sections' ) ) :			
		else :		    
			$sections = array( 'general' => __( 'Informations générales', 'tify' ) );
		endif;
		
		return $sections;		
	}
				
	/* = DECLENCHEURS = */	
	/** == Déclaration de la boîte à onglets == **/
	final public function _tify_taboox_register_box()
	{
		$this->MenuSlug = $this->getConfig( '_menu_slug' );
		$parent_slug 	= $this->getConfig( '_parent_slug' );
		$this->Hookname = $this->MenuSlug .'::'. $parent_slug;

        Taboox::registerBox(
            $this->Hookname,
			[
				'title'	=> __( 'Réglages des données utilisateur', 'tify' )
			]
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	final public function _tify_taboox_register_node()
	{
		$order = 0;
		foreach( (array) $this->set_sections() as $id => $args ) :
			if( is_string( $args ) )
				$args = array( 'title' => $args );
			
			$defaults = array(
				'id' 			=> $id,
				'order'			=> ++$order
			);
			$args = wp_parse_args( $args, $defaults );
			
			if( method_exists( $this, "section_{$id}" ) )
				$args['cb'] = array( $this, 'section_'. $id );

            Taboox::registerNode(
                $this->Hookname,
                $args
            );
		endforeach;
	}
	
	/* = AFFICHAGE = */	
	/** == Affichage des champs de saisie généraux == **/
	public function section_general()
	{
		$user_login 	= isset( $_POST['user_login'] ) 	? wp_unslash( $_POST['user_login'] ) 	: ( $this->item ? $this->item->user_login : '' );
		$user_firstname = isset( $_POST['first_name'] ) 	? wp_unslash( $_POST['first_name'] ) 	: ( $this->item ? $this->item->first_name : '' );
		$user_lastname 	= isset( $_POST['last_name'] ) 		? wp_unslash( $_POST['last_name'] ) 	: ( $this->item ? $this->item->last_name : '' );
		$user_nickname 	= isset( $_POST['nickname'] ) 		? wp_unslash( $_POST['nickname'] ) 		: ( $this->item ? $this->item->nickname : '' );
		$user_email 	= isset( $_POST['email'] ) 			? wp_unslash( $_POST['email'] ) 		: ( $this->item ? $this->item->user_email : '' );
		$user_uri 		= isset( $_POST['url'] ) 			? wp_unslash( $_POST['url'] ) 			: ( $this->item ? $this->item->user_url : '' );
	?>
		<h3><?php _e( 'Nom', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<tr scope="row">
					<th>
						<label><?php _e( 'Identifiant  (obligatoire)', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="user_login" id="user_login" value="<?php echo $user_login;?>" <?php if( $this->item ) : ?> disabled="disabled" <?php endif;?> class="regular-text">					
					</td>
				</tr>				
				<tr scope="row">
					<th>
						<label><?php _e( 'Prénom', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="first_name" id="first_name" value="<?php echo $user_firstname;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Nom', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="last_name" id="last_name" value="<?php echo $user_lastname;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Pseudonyme', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="nickname" id="nickname" value="<?php echo $user_nickname;?>" class="regular-text ltr">					
					</td>
				</tr>
			</tbody>
		</table>
		
		<h3><?php _e( 'Informations de contact', 'tify' );?></h3>
		<table class="form-table">
			<tbody>	
				<tr scope="row">
					<th>
						<label><?php _e( 'E-mail (obligatoire)', 'tify' );?></label>
					</th>
					<td>
						<input type="email" name="email" id="email" value="<?php echo $user_email;?>" class="regular-text ltr">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Site web', 'tify' );?></label>
					</th>
					<td>
						<input type="text" name="url" id="url" value="<?php echo $user_uri;?>" class="regular-text ltr">					
					</td>
				</tr>
			</tbody>
		</table>
		<h3><?php _e( 'Informations de connexion', 'tify' );?></h3>
		<table class="form-table">
			<tbody>
				<?php $password = ( ! $this->item ) ? wp_generate_password( 24, false, false ) : '';?>
				<tr scope="row">
					<th>
						<label><?php _e( 'Nouveau mot de passe', 'tify' );?></label>
					</th>
					<td>
						<input type="password" name="pass1" id="pass1" value="<?php echo $password;?>" class="regular-text" autocomplete="off">					
					</td>
				</tr>
				<tr scope="row">
					<th>
						<label><?php _e( 'Répétez le mot de passe', 'tify' );?></label>
					</th>
					<td>
						<input type="password" name="pass2" id="pass2" value="<?php echo $password;?>" class="regular-text" autocomplete="off">					
					</td>
				</tr>
			</tbody>
		</table>
	<?php	
	}
				
	/** == Rendu == **/
	public function render()
	{
	?>
		<div class="wrap">
			<h2><?php echo $this->label( 'edit_item' );?></h2>
		
			<form method="post" action="">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php tify_taboox_display( $this->item );?>
					</div>					
					<div style="margin-right:-300px; width: 280px; float:right;">
						<?php $this->submitdiv();?>
					</div>
				</div>
			</form>		
		</div>
	<?php 
	}
}