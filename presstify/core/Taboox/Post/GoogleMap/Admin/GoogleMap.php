<?php
namespace tiFy\Core\Taboox\Post\GoogleMap\Admin;

use tiFy\Lib\Utils as tiFyLibUtils;
/**
 * Interface de gestion de localisations
 * @since 1.0.344 Refonte de l'interface de gestion de localisations
 * @author pitcho
 */
class GoogleMap extends \tiFy\Core\Taboox\Admin
{
    /**
     * Nom de la métadonnée
     * @var string
     */
    protected $name = null;
    
    /**
     * Options de l'édition de la Google Map
     * @var array
     * Position des contrôleurs
     * @see https://developers.google.com/maps/documentation/javascript/examples/control-positioning
     * @see https://developers.google.com/maps/documentation/javascript/controls#ControlPositioning
     * Options du champ d'autocomplétion
     * @see https://developers.google.com/maps/documentation/javascript/places-autocomplete?hl=fr#address_forms
     */
    protected $options = array();
    
    /**
     * Champs à sauvegarder pour un marqueur
     * @var array
     */
    protected $fieldsToSave = array('title','type','address','lng','lat','formatted_address','street_number','route','locality','postal_code','country');
    
    /**
     * Types de marqueur
     * @var array
     */
    private $markersTypes = array();
    
    /**
     * Instance de classe
     */
    private static $instance = 0;
    
    /**
     * MÉTHODES
     */
    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->setMarkersTypes();
        $this->setOptions();
        // Actions ajax
        /// Ajout d'un nouveau marqueur
        add_action('wp_ajax_tiFyGoogleMapAddMarker', array($this, 'wp_ajax_new_marker'));
        add_action('wp_ajax_nopriv_tiFyGoogleMapAddMarker', array($this, 'wp_ajax_new_marker'));
        /// Sauvegarde d'un marqueur
        add_action('wp_ajax_tiFyGoogleMapSaveMarker', array($this, 'wp_ajax_save_marker'));
        add_action('wp_ajax_nopriv_tiFyGoogleMapSaveMarker', array($this, 'wp_ajax_save_marker'));
        /// Suppression d'un marqueur
        add_action('wp_ajax_tiFyGoogleMapRemoveMarker', array($this, 'wp_ajax_remove_marker'));
        add_action('wp_ajax_nopriv_tiFyGoogleMapRemoveMarker', array($this, 'wp_ajax_remove_marker'));
        /// Édition d'un marqueur
        add_action('wp_ajax_tiFyGoogleMapEditMarker', array($this, 'wp_ajax_edit_marker'));
        add_action('wp_ajax_nopriv_tiFyGoogleMapEditMarker', array($this, 'wp_ajax_edit_marker'));
        /// Déplacement manuel d'un marqueur
        add_action('wp_ajax_tiFyGoogleMapDragMarker', array($this, 'wp_ajax_drag_marker'));
        add_action('wp_ajax_nopriv_tiFyGoogleMapDragMarker', array($this, 'wp_ajax_drag_marker'));
    }
    
    /**
     * Initialisation des options par défaut
     */
    private function setOptions()
    {
        $this->options = array( 
            'name'              => '_tify_google_map_marker',
            'GeolocationText'   => '',
            'ApiKey'            => null,
            'MapOptions'        => array(
                'zoom'                  => 10,
                'zoomControl'           => true,
                'zoomControlOptions'    => array(
                    'position'  => 'TOP_LEFT'
                ),
                'mapTypeControl'        => false,
                'scaleControl'          => false,
                'streetViewControl'     => false,
                'rotateControl'         => false,
                'fullscreenControl'     => false,
                'style'                 => null
            ),
            'Autocomplete'      => array(),
            'MarkersTypes'       => $this->markersTypes
        );
    }
    
    /**
     * Initialisation des types de marqueurs
     * @example Ancrage des icones TOP_LEFT | TOP_CENTER | TOP_RIGHT | CENTER_LEFT | CENTER | CENTER_RIGHT | BOTTOM_LEFT | BOTTOM_CENTER | BOTTOM_RIGHT
     */
    private function setMarkersTypes()
    {
        $this->markersTypes = array(
            'main'  => array(
                'label' => __('Marqueur principal', 'tify' ),
				'icon' 	=> array(
				    'src'       => self::getDirname(get_class($this)).'/markers/marker.svg',
				    'anchor'    => 'BOTTOM_CENTER'
				),
				'max'	=> 1,
                'alert' => __('Vous ne pouvez pas ajouter davantage de marqueurs principaux.', 'tify')
            ),
            'poi'  => array(
                'label' => __("Point d'intérêt", 'tify' ),
				'icon' 	=> array(
				    'src'       => self::getDirname(get_class($this)).'/markers/flag.svg',
				    'anchor'    => 'BOTTOM_LEFT'
                ),
				'max'	=> 99,
                'alert' => __("Vous ne pouvez pas ajouter davantage de points d'intérêts.", 'tify')
            )
        );
    }
    
    /**
     * Récupération de l'icône représentative d'un type de marqueur
     * @param string $marker_type Type de marqueur
     * @return NULL|string Code d'affichage de l'icône
     */
    private function getMarkerTypeIcon($marker_type)
    {
        if(empty($this->markersTypes[$marker_type])) :
            return null;
        elseif(!empty($this->markersTypes[$marker_type]['icon']['src'])) :
            if ($this->isSimpleMarkerIcon($this->markersTypes[$marker_type]['icon']['src'])) :
                return "<img src=\"{$this->markersTypes[$marker_type]['icon']['src']}\" alt=\"{$this->markersTypes[$marker_type]['label']}\">";
            else :
                return tiFyLibUtils::get_svg($this->markersTypes[$marker_type]['icon']['src'], false);
            endif;
        endif;
    }
    
    /**
     * Récupération des types de marqueurs formattés
     * @return void[]|stdClass[]
     */
    private function getMapMarkersTypes()
    {
        $markersTypes = array();
        foreach ($this->markersTypes as $id => $markerType) :
            if (empty($markerType['icon']['src'])) :
                continue;
            endif;
            if (!$this->isSimpleMarkerIcon($markerType['icon']['src'])) :
                $markerType['icon']['src'] = $this->setComplexMarkerIcon($markerType['icon']['src']);
            endif;
            $markersTypes[$id] = $markerType;
        endforeach;
        
        return $markersTypes;
    }
    
    /**
     * Vérification du type de l'icône d'un type de marqueur
     * @param string $icon Url de l'icône
     * @return boolean
     */
    private function isSimpleMarkerIcon($icon)
    {
        if (filter_var($icon, FILTER_VALIDATE_URL)) :
            if (in_array(pathinfo(parse_url($icon)['path'], PATHINFO_EXTENSION), array('jpg','jpeg','png'))) :
                return true;
            endif;
        else :
            return false;
        endif;
    }
    
    /**
     * Définition d'un icône complexe (SVG) pour un marqueur
     * @param string $icon Chemin de l'iĉone
     * @return void|\stdClass
     */
    private function setComplexMarkerIcon($icon)
    {
        if(!file_exists($icon)) :
			return;
        endif;
		if(!$icon_infos = pathinfo($icon)) :
			return;
		endif;
		if($icon_infos['extension'] != 'svg') :
		    return;
		endif;
        // Récupération du contenu du fichier SVG
		$dom = new \DOMDocument;
		$dom->loadXML(tiFyLibUtils::get_svg($icon, false));
		$svgs = $dom->getElementsByTagName('path');
		$svg_containers = $dom->getElementsByTagName('svg');
		
		// Bypass
		if ($svg_containers->length > 1 && $svgs->length > 1) :
			return;
        endif;		
		$svg_icon = new \stdClass;
		
		// Traitement de la balise <svg>
		foreach ($svg_containers as $n => $svg_container) :
			$svg_containers->item($n)->C14N();
		endforeach;
		// Taille du SVG
		/// Largeur
		if ($svg_container->getAttribute('width')) :
			$svg_icon->width = $svg_container->getAttribute('width');
		endif;
		
		/// Hauteur
		if ($svg_container->getAttribute('height')) :
			$svg_icon->height = $svg_container->getAttribute('height');
		endif;
		
		// Traitement du chemin <path>
		foreach ($svgs as $n => $svg) :
			$svgs->item($n)->C14N();
		endforeach;
		
		// Chemin SVG
		if ($svg->getAttribute('d')) :
			$svg_icon->path = $svg->getAttribute('d');
		endif;
		
		// Couleur de remplissage
		if ($svg->getAttribute('fill')) :
			$svg_icon->fillColor = $svg->getAttribute('fill');
		else :
		  $svg_icon->fillColor = '#32373C';
		endif;
		
		// Opacity de la couleur de remplissage
		if ($svg->getAttribute('fill-opacity')) :
			$svg_icon->fillOpacity = $svg->getAttribute('fill-opacity');
		else :
			$svg_icon->fillOpacity = 1;
		endif;
		
		// Mise à l'échelle
		// Épaisseur du contour
		if ($svg->getAttribute('scale')) :
			$svg_icon->scale = $svg->getAttribute('scale');
		else :
			$svg_icon->scale = 1;
		endif;
		
		// Couleur du contour
		if ($svg->getAttribute('stroke')) :
			$svg_icon->strokeColor = $svg->getAttribute('stroke');
		endif;
		
		// Épaisseur du contour
		if($svg->getAttribute('stroke-width')) :
			$svg_icon->strokeWeight = $svg->getAttribute('stroke-width');
		else :
			$svg_icon->strokeWeight = 0;
		endif;
        
		return $svg_icon;
    }
    
    /**
     * Récupération des types de marqueurs (slug => label) pour utilisation dans une liste déroulante
     * @return array Tableau contenant le couple (slug => label) pour chaque type de marqueur
     */
    private function getMarkersChoices()
    {
        $markers = array();
        foreach($this->markersTypes as $name => $marker) :
            if ($marker['max'] > 0) :
                $markers[$name] = $marker['label'];
            endif;
        endforeach;
        return $markers;
    }
    
    /**
     * Récupération du nombre de marqueurs enregistrés selon un type fourni en paramètre
     * @param string $type Type de marqueur
     * @return number Nombre de marqueurs
     */
    private function getNumberSavedMarkersByType($post_id, $type)
    {
        if($markers = tify_meta_post_get($post_id, $this->name)) :
            foreach ($markers as $id => $marker) :
                if ($marker['type'] !== $type) :
                    unset($markers[$id]);
                endif;
            endforeach;
            return count($markers);
        else :
            return 0;
        endif;
    }
    
    /**
     * Vérification de l'existence d'un marqueur
     * @param int $post_id Identifiant du post
     * @param array $marker Marqueur
     * @return boolean
     */
    private function isMarkerAlreadyExists($post_id, $marker)
    {
        if($_markers = tify_meta_post_get($post_id, $this->name)) :
            foreach ($_markers as $_marker) :
                if (($_marker['lat'] === $marker['lat']) && ($_marker['lng'] === $marker['lng'])) :
                    return true;
                endif;
            endforeach;
        else :
            return false;
        endif;
    }
    
    /**
     * Récupération du preloader
     * @return string
     */
    private function getSpinner()
    {
        ob_start();
        ?>
        <div class="sk-circle">
       	    <div class="sk-circle1 sk-child"></div>
          	<div class="sk-circle2 sk-child"></div>
        	<div class="sk-circle3 sk-child"></div>
          	<div class="sk-circle4 sk-child"></div>
         	<div class="sk-circle5 sk-child"></div>
         	<div class="sk-circle6 sk-child"></div>
         	<div class="sk-circle7 sk-child"></div>
          	<div class="sk-circle8 sk-child"></div>
          	<div class="sk-circle9 sk-child"></div>
         	<div class="sk-circle10 sk-child"></div>
         	<div class="sk-circle11 sk-child"></div>
          	<div class="sk-circle12 sk-child"></div>
        </div>
        <?php 
        return ob_get_clean();
    }
    
    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {
        $this->args = \tiFy\Statics\Tools::parseArgsRecursive($this->args, $this->options);
        $this->name = $this->args['name'];
        $this->markersTypes = \tiFy\Statics\Tools::parseArgsRecursive($this->args['MarkersTypes'], $this->markersTypes);
    }
    
    /**
     * Chargement de la page courante
     */
    public function current_screen($current_screen)
    {
        static::$instance++;
        //tify_meta_post_register($current_screen->id, $this->name, false);
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        $min = SCRIPT_DEBUG ? '' : '.min';
        tify_control_enqueue('admin_panel');
        wp_enqueue_style('tiFyTabooxGoogleMap', self::getAssetsUrl(get_class()).'/GoogleMap'.$min.'.css', array('dashicons','spinkit-circle'), '170703');
        wp_enqueue_script('GoogleMapApi', "https://maps.googleapis.com/maps/api/js?key={$this->args['ApiKey']}&libraries=places", array(), false );
        wp_enqueue_script('tiFyTabooxGoogleMap', self::getAssetsUrl(get_class()).'/GoogleMap'.$min.'.js', array('jquery', 'jquery-ui-widget'), '170703', true);
    }
    
    /**
     * Formulaire de saisie
     * @param object $post Post
     */
    public function form($post)
    {
        $map_id = "tify-google-map-".static::$instance;
        $autocomplete_id = "tify-google-map-autocomplete-".static::$instance;
        if(!$this->args['GeolocationText']) :
            $geoloc_text = __('Vous êtes ici.', 'tify');
        endif;
        $markers = ($_markers = tify_meta_post_get($post->ID, $this->name)) ? $_markers : array();
        // Action AJAX
        /// Ajout d'un nouveau marqueur
        $new_action = 'tiFyGoogleMapAddMarker';
        /// Sauvegarde d'un marqueur
        $save_action = 'tiFyGoogleMapSaveMarker';
        /// Suppression d'un marqueur
        $remove_action = 'tiFyGoogleMapRemoveMarker';
        /// Édition d'un marqueur
        $edit_action = 'tiFyGoogleMapEditMarker';
        /// Déplacement manuel d'un marqueur
        $drag_action = 'tiFyGoogleMapDragMarker';
        // Agent de sécurisation de la requête ajax
        $ajax_nonce = wp_create_nonce('tiFyGoogleMapMarker');
        ?>
        <div class="tiFyGoogleMap" 
        	data-map="<?php echo $map_id; ?>" 
        	data-map_options="<?php echo htmlentities(json_encode($this->args['MapOptions'])); ?>"
        	data-geoloc_text="<?php echo $geoloc_text; ?>"
        	data-autocomplete_form="<?php echo $autocomplete_id; ?>"
        	data-autocomplete_options="<?php echo htmlentities(json_encode($this->args['Autocomplete']));?>"
        	data-new_action="<?php echo $new_action; ?>"
        	data-save_action="<?php echo $save_action; ?>"
        	data-remove_action="<?php echo $remove_action; ?>"
        	data-edit_action="<?php echo $edit_action; ?>"
        	data-drag_action="<?php echo $drag_action; ?>"
        	data-ajax_nonce="<?php echo $ajax_nonce; ?>"
        	data-fields="<?php echo htmlentities(json_encode($this->fieldsToSave)); ?>"
        	data-markers_types="<?php echo htmlentities(json_encode($this->getMapMarkersTypes()));?>"
        	data-markers="<?php echo htmlentities(json_encode($markers)); ?>"
        	>
        	<div class="tiFyGoogleMap-editor">
        		<div class="tiFyGoogleMap-editorMap" id="<?php echo $map_id; ?>"></div>
            	<button type="button" class="tiFyGoogleMap-editorNewMarker" id="tiFyGoogleMap-newMarker">
            		<div class="tiFyGoogleMap-editorNewMarkerIcon dashicons dashicons-plus"></div>
            		<div class="tiFyGoogleMap-editorNewMarkerSpinner">
            			<?php echo $this->getSpinner(); ?>
            		</div>
            	</button>
            	<div class="tiFyGoogleMap-editorPanel"><?php echo $this->newMarker($autocomplete_id); ?></div>
        	</div>
        	<ul class="tiFyGoogleMap-markers">
        		<?php 
        		if (!empty($markers)) :
        		    foreach ($markers as $id => $marker) :
        		        $this->markerRender($id, $marker);
        		    endforeach;
        		endif;
        		?>
        	</ul>
        </div>
        <?php
    }

    /**
     * Ajout d'un nouveau marqueur
     * @param string $autocomplete_id Identifiant du champ d'autocomplétion
     */
    public function newMarker($autocomplete_id)
    {
        return tify_control_admin_panel(
		    array(
		        'toggle'      => '#tiFyGoogleMap-newMarker',
		        'attrs'       => array('id' => 0),
		        'controls'    => array(
		            'save'    => __('Ajouter', 'tify')
		        ),
		        'header'      => array(
		            'title'   => __( 'Nouveau marqueur', 'tify' ),
		            'icon'    => \tiFy\Lib\Utils::get_svg(self::getDirname().'/markers/marker.svg', false)
		        ),
		        'nodes'       => array(
		            'marker'  => array(
		                'title'   => __('Paramètres du marqueur', 'tify'),
		                'fields'  => array(
		                    array(
		                        'type'    => 'input',
		                        'value'   => __('Nouveau marqueur', 'tify'),
		                        'title'   => __("Intitulé du marqueur", 'tify'),
		                        'attrs'   => array(
		                            'save'    => 'title'    
                                )
		                    ),
		                    array(
		                        'type'    => 'dropdown',
		                        'title'   => __('Type du marqueur', 'tify' ),
		                        'choices' => $this->getMarkersChoices(),
		                        'attrs'   => array(
		                            'save'    => 'type'    
                                )
		                    ),
		                    array(
		                        'type'        => 'input',
		                        'id'          => $autocomplete_id,
		                        'title'       => __("Entrez l'adresse du marqueur", 'tify'),
		                        'placeholder' => __("Saisissez l'adresse du marqueur", 'tify'),
		                        'attrs'   => array(
		                            'save'    => 'address'    
                                )
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Longitude', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'lng',
		                            'save'            => 'lng'
		                        )
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Latitude', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'lat',
		                            'save'            => 'lat'
		                        )
		                    )
		                )
		            ),
		            'address'  => array(
		                'title'   => __('Adresse', 'tify'),
		                'fields'  => array(
		                    array(
		                        'type'    => 'hidden',
		                        'attrs'   => array(
		                            'autocomplete'    => 'formatted_address',
		                            'save'            => 'formatted_address'
		                        )
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Numéro', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'street_number',
		                            'save'            => 'street_number'
		                        ) 
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Rue', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'route',
		                            'save'            => 'route'
		                        ) 
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Ville', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'locality',
		                            'save'            => 'locality'
		                        ) 
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Code postal', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'postal_code',
		                            'save'            => 'postal_code'
		                        )
		                    ),
		                    array(
		                        'type'    => 'input',
		                        'title'   => __('Pays', 'tify'),
		                        'attrs'   => array(
		                            'autocomplete'    => 'country',
		                            'save'            => 'country'
		                        ) 
		                    )
		                )
		            )
		        )
		    ),
            false
		);
    }
    
    /**
     * Affichage d'un marqueur enregistré
     */
    public function markerRender($id, $marker, $echo = true)
    {
        $output = "<li class=\"tiFyGoogleMap-marker\" data-id=\"{$id}\" data-marker=\"".htmlentities(json_encode($marker))."\">\n";
        $output .= "\t<div class=\"tiFyGoogleMap-markerInner\">\n";
        $output .= "\t\t<span class=\"tiFyGoogleMap-markerIcon\">".$this->getMarkerTypeIcon($marker['type'])."</span>\n";
        $output .= "\t\t<span class=\"tiFyGoogleMap-markerTitle\">{$marker['title']}</span>\n";
        $output .= "\t\t<a href=\"#\" class=\"tiFyGoogleMap-markerEdit\" title=\"".__('Éditer ce marqueur', 'tify')."\"></a>\n";
        $output .= "\t\t<a href=\"#\" class=\"tiFyGoogleMap-markerRemove tify_button_remove\" title=\"".__('Supprimer ce marqueur', 'tify')."\"></a>\n";
        $output .= "\t</div>\n";
        $output .= "</li>";
        
        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
    
    /**
     * Action ajax d'ajout d'un nouveau marqueur
     */
    public function wp_ajax_new_marker()
    {
        check_ajax_referer('tiFyGoogleMapMarker');
        wp_send_json_success($this->newMarker($_POST['autocomplete_id']));
    }
    
    /**
     * Action ajax de sauvegarde d'un marqueur
     */
    public function wp_ajax_save_marker()
    {
        check_ajax_referer('tiFyGoogleMapMarker');
        $post_id = $_POST['post_id'];
        $meta_id = $_POST['meta_id'];
        $marker = $_POST['marker'];
        if (empty($marker['lat']) || empty($marker['lng'])) :
            wp_send_json_error(__("Impossible d'ajouter ou mettre à jour ce marqueur car les coordonnées de celui-ci sont incomplètes.", 'tify'));
        else :
            foreach ($marker as $key => $value) :
                $marker[$key] = wp_unslash($value);
            endforeach;
            if (!empty($meta_id) && get_post_meta_by_id($meta_id)) :
    			update_metadata_by_mid('post', $meta_id, $marker);
    		else :
    		    if ($this->getNumberSavedMarkersByType($post_id, $marker['type']) >= $this->markersTypes[$marker['type']]['max']) :
    			    wp_send_json_error($this->markersTypes[$marker['type']]['alert']);
    		    elseif ($this->isMarkerAlreadyExists($post_id, $marker)) :
    		        wp_send_json_error(__("Un marqueur existe déjà pour les coordonnées que vous avez saisies.", 'tify'));
    		    else :
    		        $meta_id = add_post_meta($post_id, $this->name, $marker);
    		    endif;
    		endif;
    		$datas = array(
    		    'id'      => $meta_id,
    		    'marker'  => $marker,
    		    'render'  => $this->markerRender($meta_id, $marker, false)
    		);
            wp_send_json_success($datas);
        endif;
    }
    
    /**
     * Action ajax de suppression d'un marqueur
     */
    public function wp_ajax_remove_marker()
    {
        check_ajax_referer('tiFyGoogleMapMarker');
        $meta_id = $_POST['meta_id'];
        if (($meta = get_post_meta_by_id($meta_id)) && ($meta->meta_key == $this->name)) :
			delete_metadata_by_mid('post', $meta_id);
		endif;
        wp_send_json_success($meta_id);   
    }
    
    /**
     * Action ajax d'édition d'un marqueur
     */
    public function wp_ajax_edit_marker()
    {
        check_ajax_referer('tiFyGoogleMapMarker');
        $meta_id = $_POST['meta_id'];
        if (($marker = get_metadata_by_mid('post', $meta_id)) && ($marker->meta_key == $this->name)) :
            $marker = $marker->meta_value;
            $output = tify_control_admin_panel(
    		    array(
    		        'toggle'      => '#tiFyGoogleMap-newMarker',
    		        'attrs'       => array('id' => $meta_id),
    		        'controls'    => array(
    		            'save'    => __('Mettre à jour', 'tify'),
    		            'remove'  => __('Supprimer', 'tify')
    		        ),
    		        'header'      => array(
    		            'title'   => !empty($marker['title']) ? $marker['title'] : '',
    		            'icon'    => !empty($marker['type']) ? $this->getMarkerTypeIcon($marker['type']) : false
    		        ),
    		        'nodes'       => array(
    		            'marker'  => array(
    		                'title'   => __('Paramètres du marqueur', 'tify'),
    		                'fields'  => array(
    		                    array(
    		                        'type'    => 'input',
    		                        'value'   => !empty($marker['title']) ? $marker['title'] : '',
    		                        'title'   => __("Intitulé du marqueur", 'tify'),
    		                        'attrs'   => array(
    		                            'save'    => 'title'    
                                    )
    		                    ),
    		                    array(
    		                        'type'    => 'dropdown',
    		                        'title'   => __('Type du marqueur', 'tify' ),
    		                        'value'   => !empty($marker['type']) ? $marker['type'] : '',
    		                        'choices' => $this->getMarkersChoices(),
    		                        'attrs'   => array(
    		                            'save'    => 'type' 
                                    )
    		                    ),
    		                    array(
    		                        'type'        => 'input',
    		                        'id'          => $_POST['autocomplete_id'],
    		                        'title'       => __("Entrez l'adresse du marqueur", 'tify'),
    		                        'value'       => !empty($marker['address']) ? $marker['address'] : '',
    		                        'placeholder' => __("Saisissez l'adresse du marqueur", 'tify'),
    		                        'attrs'   => array(
    		                            'save'    => 'address'    
                                    )
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Longitude', 'tify'),
    		                        'value'   => !empty($marker['lng']) ? $marker['lng'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'lng',
    		                            'save'            => 'lng'
    		                        )
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Latitude', 'tify'),
    		                        'value'   => !empty($marker['lat']) ? $marker['lat'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'lat',
    		                            'save'            => 'lat'
    		                        )
    		                    )
    		                )
    		            ),
    		            'address'  => array(
    		                'title'   => __('Adresse', 'tify'),
    		                'fields'  => array(
    		                    array(
    		                        'type'    => 'hidden',
    		                        'value'   => !empty($marker['formatted_address']) ? $marker['formatted_address'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'formatted_address',
    		                            'save'            => 'formatted_address'
    		                        )
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Numéro', 'tify'),
    		                        'value'   => !empty($marker['street_number']) ? $marker['street_number'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'street_number',
    		                            'save'            => 'street_number'
    		                        ) 
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Rue', 'tify'),
    		                        'value'   => !empty($marker['route']) ? $marker['route'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'route',
    		                            'save'            => 'route'
    		                        ) 
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Ville', 'tify'),
    		                        'value'   => !empty($marker['locality']) ? $marker['locality'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'locality',
    		                            'save'            => 'locality'
    		                        ) 
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Code postal', 'tify'),
    		                        'value'   => !empty($marker['postal_code']) ? $marker['postal_code'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'postal_code',
    		                            'save'            => 'postal_code'
    		                        )
    		                    ),
    		                    array(
    		                        'type'    => 'input',
    		                        'title'   => __('Pays', 'tify'),
    		                        'value'   => !empty($marker['country']) ? $marker['country'] : '',
    		                        'attrs'   => array(
    		                            'autocomplete'    => 'country',
    		                            'save'            => 'country'
    		                        ) 
    		                    )
    		                )
    		            )
    		        )
    		    ),
                false
    		);
        else :
            $output = false;
        endif;
        
        if ($output) :
            wp_send_json_success($output);
        else :
            wp_send_json_error($output);
        endif;
    }
    
    /**
     * Action ajax de déplacement manuel d'un marqueur
     */
    public function wp_ajax_drag_marker()
    {
        check_ajax_referer('tiFyGoogleMapMarker');
        $meta_id = $_POST['meta_id'];
        if (($marker = get_metadata_by_mid('post', $meta_id)) && ($marker->meta_key == $this->name)) :
            $marker = $marker->meta_value;
            if ($_POST['lat'] !== $marker['lat']) :
                $marker['lat'] = $_POST['lat'];
            endif;
            if ($_POST['lng'] !== $marker['lng']) :
                $marker['lng'] = $_POST['lng'];
            endif;
            update_metadata_by_mid('post', $meta_id, $marker);
        endif;
    }
}