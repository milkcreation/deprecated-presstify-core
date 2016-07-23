<?php
class tiFy_WebAgencyCRM_AdminEditPartner extends tiFy_AdminView_EditForm{
	/* = ARGUMENTS = */	
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master, tiFy_Query $query ){
		// Définition des classe de référence
		$this->master 	= $master;

		// Instanciation de la classe parente
       	parent::__construct( $query );
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_box', array( $this, 'tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
	}
	
	/* = CHARGEMENT = */
	public function current_screen( $current_screen ){
		wp_enqueue_style( 'tify_controls-text_remaining' );
		wp_enqueue_script( 'tify_controls-text_remaining' );
		wp_enqueue_script( 'tify-fixed_submitdiv' );
	}
		
	/* = VUES = */
	/** == Champs cachés == **/
	public function hidden_fields(){
	?>
		<input type="hidden" name="author" value="<?php echo $this->item->partner_author;?>" />
		<input type="hidden" name="date" value="<?php echo $this->item->partner_date;?>" />
		<input type="hidden" name="date_gmt" value="<?php echo $this->item->partner_date_gmt;?>" />
	<?php
	}
	
	/** == Formulaire d'édition == **/
	public function form(){
	?>
	<input type="text" id="title" name="title" value="<?php echo $this->get_field( 'title' );?>" placeholder="<?php _e( 'Intitulé du projet', 'tify' );?>">						
	<?php 
		tify_control_text_remaining( 
			array( 
				'id' 	=> 'content', 
				'name' 	=> 'content', 
				'value' => $this->item->partner_content, 
				'attrs' => array( 
					'placeholder' => __( 'Description du partenaire', 'tify' )
				) 
			) 
		);
		
		tify_taboox_display();
	}
	
	/* = TRAITEMENT DES DONNEES = */
	public function parse_postdata( $data ){
		if( $data['title'] )
			$data['title'] = wp_unslash( $data['title'] );
		if( $data['content'] )
			$data['content'] = wp_unslash( $data['content'] );
		if( empty( $data['date'] ) || ( $data['date'] === '0000-00-00 00:00:00' ) )
			$data['date'] = current_time( 'mysql', false );
		if( empty( $data['date_gmt'] ) || ( $data['date_gmt'] === '0000-00-00 00:00:00' ) )
			$data['date_gmt'] = current_time( 'mysql', true );		
		
		return $data;
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == == **/
	public function tify_taboox_register_box(){
		tify_taboox_register_box( 
			'espace-clients_page_tify_wacrm_partner',
			'custom',
			array( 
				'title' 	=> __( 'Options du contenu', 'phenix' )
			) 
		);
	}
	
	/** == Déclaration des sections de boîtes à onglet == **/
	public function tify_taboox_register_node(){
		tify_taboox_register_node( 
			'espace-clients_page_tify_wacrm_partner', 
			array(
				'id' 		=> 'tify_wacrm_partner-details',
				'title' 	=> __( 'Coordonnées', 'tify' ),
				'order'		=> 1
			)
		);
		// Coordonnées Administratives
		tify_taboox_register_node( 
			'espace-clients_page_tify_wacrm_partner', 
			array(
				'id' 		=> 'tify_wacrm_partner-administrative-details',
				'parent' 	=> 'tify_wacrm_partner-details',
				'title' 	=> __( 'Coordonnées administratives', 'tify' ),
				'cb'		=> 'WACRM_AdministrativeDetails_Taboox',
				'order'		=> 1
			)
		);
		// Localisation
		tify_taboox_register_node( 
			'espace-clients_page_tify_wacrm_partner', 
			array(
				'id' 		=> 'tify_wacrm_partner-location',
				'parent' 	=> 'tify_wacrm_partner-details',
				'title' 	=> __( 'Localisation', 'tify' ),
				'cb'		=> 'WACRM_Location_Taboox',
				'order'		=> 2
			)
		);
		// 
		tify_taboox_register_node( 
			'espace-clients_page_tify_wacrm_partner', 
			array(
				'id' 		=> 'theme-options-sidebar-gender',
				'parent' 	=> 'tify_wacrm_partner-details',
				'title' 	=> __( 'Coordonnées de contact', 'tify' ),
				'cb'		=> 'WACRM_Contact_Taboox',
				'order'		=> 3
			)
		);
	}
	
	/** == Déclaration des contenus de section de boîte à onglet == **/
	public function tify_taboox_register_form(){
		tify_taboox_register_form( 'WACRM_AdministrativeDetails_Taboox', $this );
		tify_taboox_register_form( 'WACRM_Location_Taboox', $this );
		tify_taboox_register_form( 'WACRM_Contact_Taboox', $this );
	}
}

/* = FORMULAIRES DE SAISIE = */
class WACRM_AdministrativeDetails_Taboox extends tiFy\Core\Taboox\Admin
{
		/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_AdminEditPartner $master ){
		$this->master = $master;
		parent::__construct();
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form(){
	?>	
	<table class="form-table">
			<tr>
				<th>
					<?php _e( 'Dénomination', 'tify');?>
					<em><?php _e( 'Raison sociale, nom de la société, nom de l\'association', 'tify' );?></em>
				</th>
				<td>
					<input type="text" id="name" name="item_meta[name]" value="<?php echo $this->master->get_meta( 'name' );?>" placeholder="<?php _e( 'Indiquez la dénomination', 'tify' );?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'INSEE ou SIREN', 'tify' );?>
					<em><?php _e( 'Retrouver cette information sur societe.com. ', 'tify' );?></em>
				</th>
				<td>
					<input type="text" id="insee" name="item_meta[insee]" value="<?php echo $this->master->get_meta( 'insee' );?>" placeholder="<?php _e( 'Identification de l\'entité', 'tify' );?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'N° de TVA', 'tify' );?>
				</th>
				<td>
					<input type="text" id="vat" name="item_meta[vat]" value="<?php echo $this->master->get_meta( 'vat' );?>" >
				</td>
			</tr>
		</body>
	</table>
	<?php
	}
}

class WACRM_Location_Taboox extends tiFy\Core\Taboox\Admin
{
		/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_AdminEditPartner $master ){
		$this->master = $master;
		parent::__construct();
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form(){
	?>	
	<table class="form-table">
		<tbody>					
			<tr>
				<th>
					<?php _e( 'Adresse postale', 'tify' );?>
				</th>
				<td>
					<textarea name="item_meta[address]"><?php echo $this->master->get_meta( 'address' );?></textarea>
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Code postal', 'tify' );?>
				</th>
				<td>
					<input type="text" id="zipcode" name="item_meta[zipcode]" value="<?php echo $this->master->get_meta( 'zipcode' );?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Ville', 'tify' );?>
				</th>
				<td>
					<input type="text" id="town" name="item_meta[city]" value="<?php echo $this->master->get_meta( 'city' );?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Pays', 'tify' );?>
				</th>
				<td>
					<input type="text" id="country" name="item_meta[country]" value="<?php echo $this->master->get_meta( 'country' );?>">
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Etat', 'tify' );?>
				</th>
				<td>
					<input type="text" id="region" name="item_meta[region]" value="<?php echo $this->master->get_meta( 'region' );?>">
				</td>
			</tr>
		</body>
	</table>
	<?php
	}
}

class WACRM_Contact_Taboox extends tiFy\Core\Taboox\Admin
{
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_AdminEditPartner $master ){
		$this->master = $master;
		parent::__construct();
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form(){
	?>	
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php _e( 'Téléphone', 'tify' );?>
				</th>
				<td>
					<input type="text" id="phone" name="item_meta[phone]" value="<?php echo $this->master->get_meta( 'phone' );?>" >
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Fax', 'tify' );?>
				</th>
				<td>
					<input type="text" id="fax" name="item_meta[fax]" value="<?php echo $this->master->get_meta( 'fax' );?>" >
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Portable', 'tify' );?>
				</th>
				<td>
					<input type="text" id="mobile" name="item_meta[mobile]" value="<?php echo $this->master->get_meta( 'mobile' );?>" >
				</td>
			</tr>
			<tr>
				<th>
					<?php _e( 'Email', 'tify' );?>
				</th>
				<td>
					<input type="text" id="email" name="item_meta[email]" value="<?php echo $this->master->get_meta( 'email' );?>" >
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}