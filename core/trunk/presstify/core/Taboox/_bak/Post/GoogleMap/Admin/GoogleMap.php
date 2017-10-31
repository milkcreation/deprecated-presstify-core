<?php
namespace tiFy\Core\Taboox\Post\GoogleMap\Admin;

class GoogleMap extends \tiFy\Core\Taboox\Admin
{
	/* = ARGUMENTS = */
	private $Markers;	
		
	/* = DECLENCHEURS = */
	/** == Initialisation de l'interface d'administration == **/
	public function admin_init()
	{
		$this->Markers = array(
			'main'			=> array( 
				'label' => __( 'Marqueur principal', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/marker.png',
				'max'	=> 1
			),
			'entertainment' =>  array( 
				'label' => __( 'Loisirs', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/entertainment.png',
				'max'	=> 0
			),
			'school' 		=>  array( 
				'label' => __( 'Écoles', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/school.png',
				'max'	=> 0
			),
			'shop' 			=>  array( 
				'label' => __( 'Commerces', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/shop.png',
				'max'	=> 0
			),
			'mayor' 		=>  array( 
				'label' => __( 'Mairies', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/mayor.png',
				'max'	=> 0
			),
			'transport' 	=>  array( 
				'label' => __( 'Transports', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/transport.png',
				'max'	=> 0
			),
			'service'		=>  array( 
				'label' => __( 'Services', 'tify' ),
				'ico' 	=> self::tFyAppUrl( get_class($this) ) .'/images/markers/service.png',
				'max'	=> 0
			)				
		);
				
		// Définition des arguments
		$this->args = wp_parse_args( 
			$this->args, 
			array(
				'name' 		=> '_geocode',
				'api_key'	=> ''
			)
		);
		
		// Déclaration des scripts
		wp_register_style( 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap', self::tFyAppUrl() .'/GoogleMap.css', array( ), 161207 );			
		wp_register_script( 'googleMap', '//maps.google.com/maps/api/js?key='. $this->args['api_key']. '&libraries=places', array(), 'v3', false );
		wp_register_script( 'gMap3', '//cdn.jsdelivr.net/gmap3/5.1.1/gmap3.min.js', array( 'jquery', 'googleMap' ), '5.1.1', true );		
		wp_register_script( 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap', self::tFyAppUrl() .'/GoogleMap.js', array( 'gMap3' ), 161207, true );
		wp_localize_script( 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap', 'TabooxPostAdminGMap', array( 
				// Configuration
				'url' 				=> self::tFyAppUrl(),
				'main_marker' 		=> $this->Markers['main'],
				'marker_types' 		=> array(),
				'gmap3' 			=> array(
					'map' 				=> array(
						'address' 			=> 'FRANCE',
						'options' 			=> array( 
							'zoom'				=> 5,
							'navigationControl' => true,
							'mapTypeControl' 	=> true,
					        'scrollwheel' 		=> false,
					        'streetViewControl' => true
						)
					)
				),
				'autocomplete' 		=> array(
					'bounds'			=> array( 'north' => 0, 'east' => 0, 'south' => 0, 'west' => 0 ),
					'country' 			=> 'fr'
				),
				'showPanelHelper' 	=> true,
				'errorDuration' 	=> 2000, // Durée d'affichage des message d'erreur
				// Traduction
				'buttonAdd' 		=> __( 'Ajouter', 'tify' ),
				'buttonUpdate' 		=> __( 'Mettre à jour', 'tify' ),
				'buttonEdit' 		=> __( 'Éditer', 'tify' ),
				'buttonDelete' 		=> __( 'Supprimer', 'tify' ),
				'maxTypeAttempt' 	=> __( 'Nombre maximum de géocode de ce type atteint sur cette carte', 'tify' ),
				'markerSaved' 		=> __( 'Le géocode a été créé avec succès', 'tify' ),
				'markerUpdated' 	=> __( 'Le géocode a été mis à jour avec succès', 'tify' ),
				'markerDeleted' 	=> __( 'Le géocode a été supprimé avec succès', 'tify' ),
				'mapInfos' 			=> __( 'Informations sur la carte', 'eveole' ),
				'north' 			=> __( 'Nord', 'eveole' ),
				'east' 				=> __( 'Est', 'eveole' ),
				'south' 			=> __( 'Sud', 'eveole' ),
				'west' 				=> __( 'Ouest', 'eveole' )
			) 
		);
		
		
		add_action( 'wp_ajax_tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap_saveMarker', array( $this, 'ajaxSaveMarker' ) );
		add_action( 'wp_ajax_tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap_deleteMarker', array( $this, 'ajaxDeleteMarker' ) );
	}
	
	/* = CHARGEMENT DE LA PAGE = */	
	public function current_screen( $current_screen )
	{
		// Déclaration des metadonnées à enregistrer
		\tify_meta_post_register( $current_screen->id, $this->args['name'], false );	
	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{	
		wp_enqueue_style( 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap' );
		wp_enqueue_script( 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap' );
	}
	
	/* = AFFICHAGE = */
	/** == Formulaire de saisie == **/
	public function form( $post )
	{		
		$metadatas = has_meta( $post->ID );
		foreach ( $metadatas as $key => $value ) :
			if ( $metadatas[ $key ][ 'meta_key' ] != '_geocode' ) :
				unset( $metadatas[ $key ] );
			else :
				$metadatas[ $key ]['meta_value'] = maybe_unserialize( $metadatas[ $key ]['meta_value'] );
			endif;
		endforeach;	
		$options = get_post_meta( $post->ID, '_gmap_map_options', true );
	?>
	<div id="gmap-postbox">		
		<div id="map-options">
			<ul>
				<li>
					<label><?php _e( 'Niveau de zoom', 'pgsd' )?> : 
						<input type="text" size="2" id="map_zoom" name="mkpbx_postbox[single][gmap_map_options][zoom]" value="<?php echo isset( $options['zoom'] )? esc_attr( $options['zoom'] ) : '';?>" readonly="readonly" autocomplete="off" />
					</label>
				</li>
				<li>
					<label>
						<?php _e('Centrage de la carte', 'pgsd')?> :
					</label>&nbsp;&nbsp;
					<label> 
						<?php _e('X', 'pgsd')?> 
						<input type="text" size="8" id="map_center_x" name="mkpbx_postbox[single][gmap_map_options][x]" value="<?php echo isset( $options['x'] )? esc_attr( $options['x'] ) : '';?>" readonly="readonly" autocomplete="off" />
					</label>
					<label> 
						<?php _e('Y', 'pgsd')?> 
						<input type="text" size="8" id="map_center_y" name="mkpbx_postbox[single][gmap_map_options][y]" value="<?php echo isset( $options['y'] )? esc_attr( $options['y'] ) : '';?>"  readonly="readonly" autocomplete="off" />
					</label>
				</li>
				<li>
					<a href="#" id="map-reload" class="button-secondary" title="<?php _e('Réinitialiser la carte', 'pgsd');?>">
						<i class="dashicons dashicons-update"></i>					
					</a>
				</li>
				<li id="marker-add">
					<a href="#" class="dashicons dashicons-plus-alt"></a>
				</li>
			</ul>
		</div>
			
		<div class="preview">
			<div class="overlay"></div>			
			
			<div id="googleMap" class="gmap3"></div>		
		
			<div id="marker-edit" class="details-panel">
				<input type="hidden" class="marker-data id" value="" autocomplete="off"/>
				<label>
					<?php _e( 'Intitulé', 'tify' );?>
					<input type="text" class="marker-data title" value="" autocomplete="off"/>
				</label>
				
				<label>
					<?php _e( 'Type de marqueur', 'tify' );?>
					<select class="type" autocomplete="off">
					<?php if( $main = $this->Markers['main'] ) :?>
						<option value="main_marker"><?php echo esc_attr( $main['label'] );?></option>
					<?php endif; ?>
					<?php $markers = $this->Markers; unset( $markers['main'] );?>
					<?php foreach( $markers as $type => $attrs ) : ?>
						<option value="<?php echo esc_attr( $type );?>"><?php echo esc_attr( $attrs['label'] );?></option>
					<?php endforeach;?>
					</select>
				</label>
	
				<label><?php _e( 'Infobulle', 'tify' );?>	
					<textarea class="marker-data tooltip widefat" autocomplete="off" row="5" style="resize:none;"></textarea>
				</label>
				
				<hr style="margin:10px -10px">
	
				<div>				
					<input type="text" id="search-input" class="marker-data search widefat" autocomplete="off" placeholder="<?php _e( 'Saisir l\'adresse du marqueur', 'tify' );?>">
				</div>
				
				<div class="latlng">
					<label style="width:50%; float:left;">
						<?php _e( 'Lat', 'tify' );?>
						<input type="text" class="marker-data lat" value="" autocomplete="off"/> 				
					</label>
					
					<label style="width:50%; float:left;">
						<?php _e( 'Long', 'tify' );?>
						<input type="text" class="marker-data lng" value="" autocomplete="off"/> 				
					</label>	
				</div>
						
				<hr class="clear" style="margin:10px -10px">
				
				<div class="action-buttons">
					<a href="#" class="save button-primary"><?php _e( 'Ajouter', 'tify' );?></a>&nbsp;&nbsp;
					<a href="#" class="reset button-secondary"><?php _e( 'Annuler', 'tify' );?></a>
					<a href="#" class="delete"><?php _e( 'Supprimer le géocode', 'tify');?></a>
				</div>
			</div>	
		</div>
		
		<div class="clear"></div>
		
		<div id="geocodes">
			<div class="main_marker">
				
			</div>
				<input type="hidden" class="title" name="mkpbx_postbox[single][gmap_marker_title]" value="" />
				<input type="hidden" class="lat" name="mkpbx_postbox[single][gmap_marker_lat]" value="" />
				<input type="hidden" class="lng" name="mkpbx_postbox[single][gmap_marker_lng]" value="" />
				<input type="hidden" class="tooltip" name="mkpbx_postbox[single][gmap_marker_tooltip]" value="" />
			<ul>
			<?php if( $this->main_marker && ( $lat = get_post_meta( $post->ID, '_gmap_marker_lat', true ) ) && ( $lng = get_post_meta( $post->ID, '_gmap_marker_lng', true ) ) ) :?>
				<li id="geocode-main_marker-<?php echo $post->ID;?>">
					<a href="#" id="main_marker-<?php echo $post->ID;?>" class="button-secondary" data-target="main_marker-<?php echo $post->ID;?>" >
						<img src="<?php echo $this->main_marker['ico'];?>" class="ico" width="24" height="auto" style="vertical-align:middle;"/>
						<span class="label"><?php echo get_post_meta( $post->ID, '_gmap_marker_title', true );?></span>
						<input type="hidden" class="title" name="mkpbx_postbox[single][gmap_marker_title]" value="<?php echo stripslashes( esc_attr( get_post_meta( $post->ID, '_gmap_marker_title', true ) ) );?>" />
						<input type="hidden" class="id" value="main_marker-<?php echo esc_attr( $post->ID );?>" />
						<input type="hidden" class="type" value="main_marker" />
						<input type="hidden" class="lat" name="mkpbx_postbox[single][gmap_marker_lat]" value="<?php echo esc_attr( $lat );?>" />
						<input type="hidden" class="lng" name="mkpbx_postbox[single][gmap_marker_lng]" value="<?php echo esc_attr( $lng );?>" />
						<input type="hidden" class="tooltip" name="mkpbx_postbox[single][gmap_marker_tooltip]" value="<?php echo stripslashes( esc_attr( get_post_meta( $post->ID, '_gmap_marker_tooltip', true ) ) );?>" />
					</a>				
				</li>
			<?php endif; ?>
				
			<?php if( $metadatas ) :?>
				<?php foreach( $metadatas as $metadata ) : ?>
				<li id="geocode-<?php echo $metadata['meta_id'];?>">
					<a href="#" id="<?php echo $metadata['meta_id'];?>" class="button-secondary" data-target="<?php echo $metadata['meta_id'];?>" >
						<img src="<?php echo $this->marker_types[$metadata['meta_value']['geocode_type']]['ico'];?>" class="ico" width="24" height="auto" style="vertical-align:middle;"/>
						<span class="label"><?php echo $metadata['meta_value']['geocode_title'];?></span>
						<input type="hidden" class="title" name="mkpbx_postbox[multi][geocode][<?php echo $metadata['meta_id'];?>][geocode_title]" value="<?php echo stripslashes( $metadata['meta_value']['geocode_title'] );?>" />
						<input type="hidden" class="id" value="<?php echo $metadata['meta_id'];?>" />
						<input type="hidden" class="type" name="mkpbx_postbox[multi][geocode][<?php echo $metadata['meta_id'];?>][geocode_type]" value="<?php echo $metadata['meta_value']['geocode_type'];?>" />
						<input type="hidden" class="lat" name="mkpbx_postbox[multi][geocode][<?php echo $metadata['meta_id'];?>][geocode_lat]" value="<?php echo $metadata['meta_value']['geocode_lat'];?>" />
						<input type="hidden" class="lng" name="mkpbx_postbox[multi][geocode][<?php echo $metadata['meta_id'];?>][geocode_lng]" value="<?php echo $metadata['meta_value']['geocode_lng'];?>" />
						<input type="hidden" class="tooltip" name="mkpbx_postbox[multi][geocode][<?php echo $metadata['meta_id'];?>][geocode_content]" value="<?php echo stripslashes( $metadata['meta_value']['geocode_content'] );?>" />
					</a>				
				</li>
				<?php endforeach;?>
			<?php endif; ?>
			</ul>
		</div>	
	</div>
	<?php
	}
	
	/* = CONTROLEURS = */
	/** == Sauvegarde Ajax d'un marqueur == **/
	public function ajaxSaveMarker()
	{	
		$post_id = $_REQUEST['post_id'];
		$meta_id = ! empty( $_REQUEST['data']['id'] ) ? (int) $_REQUEST['data']['id'] : 0;
		$data = array(
			'geocode_title' 	=> ! empty( $_REQUEST['data']['title'] ) ? stripslashes( $_REQUEST['data']['title'] ) : uniqid(),
			'geocode_content' 	=> ! empty( $_REQUEST['data']['tooltip'] ) ? stripslashes( $_REQUEST['data']['tooltip'] ) : '',
			'geocode_type' 		=> ! empty( $_REQUEST['data']['type'] ) ? $_REQUEST['data']['type'] : '',
			'geocode_lat' 		=> ! empty( $_REQUEST['data']['latLng'] ) ? $_REQUEST['data']['latLng'][0] : 0,
			'geocode_lng' 		=> ! empty( $_REQUEST['data']['latLng'] ) ? $_REQUEST['data']['latLng'][1] : 0,
		);	
		if( $data['geocode_type'] == 'main_marker' ) :		
			update_post_meta( $post_id, '_gmap_marker_title', $data['geocode_title'] );
			update_post_meta( $post_id, '_gmap_marker_tooltip', $data['geocode_content'] );
			update_post_meta( $post_id, '_gmap_marker_lat', $data['geocode_lat'] );
			update_post_meta( $post_id, '_gmap_marker_lng', $data['geocode_lng'] );
			$data['geocode_id'] = 'main_marker-'.$post_id;
		else :
			if( ! empty( $meta_id ) && get_post_meta_by_id( $meta_id ) )
				update_metadata_by_mid( 'post', $meta_id, $data );
			else
				$meta_id = add_post_meta( $post_id, '_geocode', $data );
			$data['geocode_id'] = $meta_id;
		endif;
		
		echo json_encode( $data );
		exit;
	}

	/** == Suppression Ajax d'un marqueur == **/
	public function ajaxDeleteMarker()
	{		
		if( preg_match_all( '/^main_marker-(\d+)/', $_REQUEST['geocode_id'], $post ) ) :
			$post_id = $post[1][0];
			delete_post_meta( $post_id, '_gmap_marker_title' );
			delete_post_meta( $post_id, '_gmap_marker_tooltip' );
			delete_post_meta( $post_id, '_gmap_marker_lat' );
			delete_post_meta( $post_id, '_gmap_marker_lng' );
		elseif( ( $meta = get_post_meta_by_id( $_REQUEST['geocode_id'] ) ) && ( $meta->meta_key == '_geocode' ) ) :
			delete_metadata_by_mid( 'post', $_REQUEST['geocode_id'] );
		endif;
		
		echo json_encode( 'ok' );
		exit;
	}
}