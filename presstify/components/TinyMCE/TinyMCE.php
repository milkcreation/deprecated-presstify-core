<?php
namespace tiFy\Components\TinyMCE;

class TinyMCE extends \tiFy\App\Component
{
	/* = ARGUMENTS = */
	/** == FILTRES == **/
	// Liste des Filtres à déclencher
	protected $CallFilters				= array(
		'tiny_mce_before_init',
		'mce_external_plugins'
	);
	
	/** == CONFIGURATION == **/
	// Liste des boutons actifs
	private static	$Buttons 						= array();
	// Liste des url vers les plugins externes déclarés
	private static	$RegistredExternalPluginsUrl	= array();
	// Configuration des plugins externes déclarés
	private	static 	$RegistredExternalPluginsConf	= array();	
	// Liste des plugins externes actifs
	private static	$ExternalPluginsActive			= array();
	// Liste des plugins externes actifs
	private static	$ExternalPluginsConfig			= array();
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Récupération de la configuration des plugins externe
		if( $external_plugins = self::tFyAppConfig( 'external_plugins' ) ) :
			self::$ExternalPluginsConfig = $external_plugins;
        endif;
        
		// Chargement des plugins
		new ExternalPlugins\Dashicons\Dashicons;	
		new ExternalPlugins\FontAwesome\FontAwesome;
		new ExternalPlugins\Genericons\Genericons;
		//new tiFy_tinyMCE_PluginGlyphicons( $this );
		new ExternalPlugins\JumpLine\JumpLine;
		new ExternalPlugins\OwnGlyphs\OwnGlyphs;
		new ExternalPlugins\Table\Table;
		new ExternalPlugins\Template\Template;
		new ExternalPlugins\VisualBlocks\VisualBlocks;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation des paramètres de tinyMCE == **/
	final public function tiny_mce_before_init( $mceInit )
	{
		// Traitement de la configuration personnalisée
		if( $init = self::tFyAppConfig( 'init' ) ) :
			foreach( (array) $init as $key => $value ) :
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
						$this->registerButtons( explode( ' ', $value ) );
						break;
					endswitch;
			endforeach;
		endif;
			
		// Traitement des plugins externes
		foreach( (array) $this->getExternalPluginsActive() as $name ) :
			// Ajout des boutons de plugins non initiés dans la barre d'outil
			if( ! in_array( $name, self::$Buttons ) ) :
				if( ! empty( $mceInit['toolbar3'] ) ) :
					$mceInit['toolbar3'] .= ' '. $name;
				else :
					$mceInit['toolbar3'] = $name;
				endif;
			endif;
			
			// Traitement de la configuration
			if( isset( self::$RegistredExternalPluginsConf[$name] ) ) :
				foreach( (array) self::$RegistredExternalPluginsConf[$name] as $key => $value ) :
					if( isset( $mceInit[$key] ) ) :
						continue;
					elseif( is_array( $value ) ) :
						$mceInit[$key] = json_encode( $value );
					elseif( is_string( $value ) ) :
						$mceInit[$key] = $value;
					endif;
				endforeach;
			endif;
		endforeach;
	
		return $mceInit;
	}
	
	/** == Mise en file des plugins complémentaires == **/
	final public function mce_external_plugins( $plugins = array() )
	{
		foreach( $this->getExternalPluginsActive() as $name ) :
			$plugins[$name] = self::$RegistredExternalPluginsUrl[$name];
		endforeach;
	
		return $plugins;
	}
		
    /* = CONTRÔLEUR = */
	/** == Déclaration de plugin externe == **/
	final static function registerExternalPlugin( $name, $url, $config = array() )
	{
		self::$RegistredExternalPluginsUrl[$name] = $url;
	
		if( ! empty( $config ) )
			self::$RegistredExternalPluginsConf[$name] = $config;
	}
	
	/** == Récupération des plugins externes actifs == **/
	public function getExternalPluginsActive()
	{
		if( ! empty( self::$ExternalPluginsActive ) )
			return self::$ExternalPluginsActive;
		
		if( ! self::tFyAppConfig( 'external_plugins' ) )
			return array();
	
		$plugins = array();
		foreach( (array) self::tFyAppConfig( 'external_plugins' ) as $k => $v ) :
			$name = false;
			if( is_string( $k ) )
				$name = $k;
			elseif( is_string( $v ) )
				$name = $v;
			
			if( $name && in_array( $name, array_keys( self::$RegistredExternalPluginsUrl ) ) && ! in_array( $name, $plugins ) )
				array_push( $plugins, $name );
		endforeach;
		
		return self::$ExternalPluginsActive = $plugins;
	}
	
	/** == Récupération de la configuration d'un plugin déclaré == **/
	final static function getExternalPluginConfig( $name )
	{
		if( isset( self::$ExternalPluginsConfig[$name] ) )
			return self::$ExternalPluginsConfig[$name];
	}
	
	/** == Déclaration des boutons == **/
	final static function registerButtons( $buttons = array() )
	{
		foreach( (array) $buttons as $button ) :
			if( ! in_array( $button, self::$Buttons ) )
				array_push( self::$Buttons, $button );
		endforeach;
	}   
	
    /** == @todo Linéarisation des paramétres de couleur == 
	 * colors = array( 'Noir' => '#000000', 'Blanc' => '#FFFFFF' )
	
    private function textcolor_map_serialize( $colors = array() )
    {
		$color_string = "";
		foreach( (array) $colors as $name=> $hex )
			$color_string .= "\"". preg_replace( '/\#/', '', $hex ). "\",\"$name\",\n";
		
		return $color_string;
	} **/
}