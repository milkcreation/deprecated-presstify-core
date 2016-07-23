<?php
/*
Plugin Name: TinyMCE
Plugin URI: http://presstify.com/plugins/tinymce
Description: Personnalisation de l'éditeur de texte Wordpress (tinyMCE)
Version: 2.151206
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

new tiFy_tinyMCE;
class tiFy_tinyMCE{
	/* = ARGUMENTS = */
	public	$uri,
			$config;
			
	private	$buttons = array(),
			$external_plugins,
			$external_plugins_init;
		
	/* = CONSTRUCTEUR = */
	public function __construct(){
		$this->dir = tiFY_Plugin::get_dirname( $this );
		$this->uri = tiFY_Plugin::get_url( $this );
		$this->config = tiFY_Plugin::get_config( $this );
		// Chargement des plugins
		require_once $this->dir .'/external_plugins/dashicons/dashicons.php';
		new tiFy_tinyMCE_PluginDashicons( $this );	
		require_once $this->dir .'/external_plugins/fontawesome/fontawesome.php';
		new tiFy_tinyMCE_PluginFontAwesome( $this );
		require_once $this->dir .'/external_plugins/genericons/genericons.php';
		new tiFy_tinyMCE_PluginGenericons( $this );
		/*require_once $this->dir .'/external_plugins/glyphicons/glyphicons.php';
		new tiFy_tinyMCE_PluginGlyphicons( $this );*/
		require_once $this->dir .'/external_plugins/ownglyphs/ownglyphs.php';
		new tiFy_tinyMCE_PluginOwnGlyphs( $this );
		require_once $this->dir .'/external_plugins/table/table.php';
		new tiFy_tinyMCE_PluginTable( $this );
		require_once $this->dir .'/external_plugins/template/template.php';
		new tiFy_tinyMCE_PluginTemplate( $this );
		require_once $this->dir .'/external_plugins/visualblocks/visualblocks.php';
		new tiFy_tinyMCE_PluginVisualBlocks( $this );
		
		// Actions et Filtres Wordpress	
		add_filter( 'tiny_mce_before_init', array( $this, 'mce_before_init' ) );
		add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );		
	}
	
	/* = PARAMETRAGE = */
	/** == Liste des plugins externes == **/
	public function register_external_plugin( $name, $url, $config = array() ){
		$this->external_plugins[$name] = $url;
		
		if( ! empty( $config ) )
			$this->external_plugins_init[$name] = $config;
	}
	
	public function register_buttons( $buttons = array() ){
		foreach( (array) $buttons as $button )
			if( ! in_array( $button, $this->buttons ) )
				array_push( $this->buttons, $button );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation des paramètres de tinyMCE == **/
	final public function mce_before_init( $mceInit ){
		// Traitement de la configuration personnalisée
		if( isset( $this->config['init'] ) )
			foreach( (array) $this->config['init'] as $key => $value ) :
				switch( $key ) :
					default			:
						if( is_array( $value ) )
							$mceInit[$key] = json_encode( $value );
						elseif( is_string( $value ) )
							$mceInit[$key] = $value;
						break;
					case 'toolbar'	:
						break;
					case 'toolbar1'	:
					case 'toolbar2'	:
					case 'toolbar3'	:
					case 'toolbar4'	:
						$mceInit[$key] = $value;
						$this->register_buttons( explode( ' ', $value ) );	
						break;	
				endswitch;
			endforeach;
		
		// Traitement des plugins externes
		if( $external_plugins = $this->get_external_plugins() )
			foreach( $external_plugins as $name ) :
				// Ajout des boutons de plugins non initiés dans la barre d'outil
				if( isset( $this->external_plugins[$name] ) && ! in_array( $name, $this->buttons ) ) 
					if( ! empty( $mceInit['toolbar3'] ) )
						$mceInit['toolbar3'] .= ' '.$name;
					else
						$mceInit['toolbar3'] = $name;
				
				// Traitement de la configuration
				if( isset( $this->external_plugins_init[$name] ) )
					foreach( (array) $this->external_plugins_init[$name] as $key => $value )
						if( isset( $mceInit[$key] ) )
							continue;
						elseif( is_array( $value ) )
							$mceInit[$key] = json_encode( $value );
						elseif( is_string( $value ) )
							$mceInit[$key] = $value;
			endforeach;
	
		return $mceInit;
	}
	
	/** == Mise en file des plugins complémentaires == **/
	final public function mce_external_plugins( $plugins = array() ){
 		foreach( $this->get_external_plugins() as $name )
			if( isset( $this->external_plugins[$name] ) )
				$plugins[$name] = $this->external_plugins[$name];		
		
        return $plugins;
    }	
		
    /* = CONTRÔLEUR = */
    /** == == **/
    public function get_external_plugins(){
    	if( empty( $this->config['external_plugins'] ) )
			return array();

		$names = array();	
		foreach( (array) $this->config['external_plugins'] as $k => $v )
			if( is_string( $k ) )
				$names[] = $k;
			elseif( is_string( $v ) )
				$names[] = $v;
		
		return $names;
    }
	
    /** == Linéarisation des paramétres de couleur == 
	 * colors = array( 'Noir' => '#000000', 'Blanc' => '#FFFFFF' )
	 **/
    private function textcolor_map_serialize( $colors = array() ){
		$color_string = "";
		foreach( (array) $colors as $name=> $hex )
			$color_string .= "\"". preg_replace( '/\#/', '', $hex ). "\",\"$name\",\n";
		
		return $color_string;
	}	
}