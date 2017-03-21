<?php
namespace tiFy\Core;

use tiFy\tiFy;

class Params extends \tiFy\Environment\App
{
    /* = ARGUMENTS = */    
    // Liste des actions à déclencher
    protected $CallActions                = array(
        'after_setup_theme'    
    );
    // Ordres de priorité d'exécution des actions
    protected $CallActionsPriorityMap    = array(
        'after_setup_theme' => 0
    );
    
    /* = PARAMETRAGE = */    
    final public function after_setup_theme()
    {        
        $attrs = array(
            'config'        => array(
                'eval'            => true        
            ),
            'components'    => array(
                'eval'            => true        
            ),
            'core'            => array(
                'eval'            => true        
            ),
            'plugins'        => array(
                'eval'            => true        
            ),
            'schema'        => array(
                'eval'            => true        
            ),
            'set'            => array(
                'eval'            => true        
            )            
        );

        // Récupération du paramétrage natif
        $_dir = @ opendir( tiFy::$AbsDir ."/config" );
        if( $_dir ) :
            while ( ( $file = readdir( $_dir ) ) !== false ) :
                if ( substr( $file, 0, 1 ) == '.' ) :
                        continue;
                endif;
                
                $basename = basename( $file, ".yml" );
                if( ! isset( $attrs[$basename] ) ) :
                     continue;
                endif;
                
                $attr = $attrs[$basename];
                if( ! isset( ${$basename} ) ) :
                    ${$basename} = array();        
                endif;
                
                ${$basename} = self::_parseFilename( tiFy::$AbsDir ."/config/{$file}", ${$basename}, 'yml', $attr );
                
            endwhile;
            closedir( $_dir );
        endif;
        
        // Récupération de la configuration    
        $_dir = @ opendir( TIFY_CONFIG_DIR );
        if( $_dir ) :
            while ( ( $file = readdir( $_dir ) ) !== false ) :
                if ( substr( $file, 0, 1 ) == '.' )
                        continue;
                $basename = basename( $file, ".". TIFY_CONFIG_EXT );
                if( $basename !== 'config' ) :
                     continue;
                endif;
                
                if( ! isset( $config ) ) :
                    $config = array();        
                endif;

                $config = self::_parseFilename( TIFY_CONFIG_DIR ."/". $file, $config, TIFY_CONFIG_EXT, $attrs['config'] );
            endwhile;
            closedir( $_dir );            
        endif;
        tiFy::$Params['config'] = $config;    
        
        // Définition de l'espace de nom du thème
        if( $theme = tiFy::getConfig( 'theme' ) ) :
            /// Traitement des attributs du thème
            //// Espace de nom
            if( ! isset( $theme['namespace'] ) ) :
                $theme['namespace'] = 'Theme';
            endif;
            tiFy::$Params['config']['theme']['namespace'] = trim( $theme['namespace'], '\\' );  
            
            //// Répertoire
            if( ! isset( $theme['base_dir'] ) ) :
                $theme['base_dir'] = get_template_directory() ."/tiFy";
            endif;
            tiFy::$Params['config']['theme']['base_dir'] = $theme['base_dir'];
            
            //// Point d'entrée
            if( ! isset( $theme['bootstrap'] ) ) :
                $theme['bootstrap'] = 'Autoload';
            endif;
            tiFy::$Params['config']['theme']['bootstrap'] = $theme['bootstrap'];
            
            tiFy::classLoad( $theme['namespace'], $theme['base_dir'], ( ! empty( $theme['bootstrap'] ) ? $theme['bootstrap'] : false ) );
        endif;
        
        // Chargement des traductions
        do_action( 'tify_load_textdomain' );
        
        // Récupération du paramétrage personnalisé
        $_dir = @ opendir( TIFY_CONFIG_DIR );
        if( $_dir ) :
            while ( ( $file = readdir( $_dir ) ) !== false ) :
                // Bypass
                if ( substr( $file, 0, 1 ) == '.' ) :
                    continue;
                endif;
                
                $basename = basename( $file, ".". TIFY_CONFIG_EXT );        
                if( ! isset( $attrs[$basename] ) ) :
                     continue;
                endif;
                
                $attr = $attrs[$basename];
                if( ! isset( ${$basename} ) ) :
                    ${$basename} = array();    
                endif;
                
                ${$basename} += self::_parseFilename( TIFY_CONFIG_DIR ."/". $file, ${$basename}, TIFY_CONFIG_EXT, $attr );
            endwhile;
            closedir( $_dir );            
        endif;        
        tiFy::$Params += compact( 'components', 'core', 'plugins', 'schema', 'set' );                    
                		
		// Chargement des plugins
		tiFy::classLoad( '\tiFy\Plugins', TIFY_PLUGINS_DIR );
		if( ! empty( tiFy::$Params['plugins'] ) ) :						
    		foreach( (array) array_keys( tiFy::$Params['plugins'] ) as $plugin ) :
    			if( class_exists( $plugin ) ) :
    				$ClassName	= $plugin;
    			elseif( class_exists( "tiFy\\Plugins\\{$plugin}\\{$plugin}" ) ) :
    				$ClassName	= "tiFy\\Plugins\\{$plugin}\\{$plugin}";
    			else :
    				continue;
    			endif;
    			
    			new $ClassName;			
    		endforeach;
		endif;
        
		// Chargement des jeux de fonctionnalités complémentaires
		tiFy::classLoad( '\tiFy\Set', tiFy::$AbsDir .'/set', 'Autoload' );
        
        // Personnalisation de la définition des paramètres 
        do_action( 'tify_params_set' );
        
        // Déclenchement des actions post-paramétrage
        do_action( 'after_setup_tify' );        
    }
    
    /* = Traitement d'un chemin = */
    public static function _parseFilename( $filename, $current,  $ext = 'yml', $attr = array() )
    {
        if( ! is_dir( $filename ) ) :
            if ( substr( $filename, -4 ) == ".{$ext}" ) :    
                return self::_parseConfig( $filename, $current, $attr['eval'] );
            endif;
        elseif( $subdir = @ opendir( $filename ) ) :
            $res = array();
            while ( ( $subfile = readdir( $subdir ) ) !== false ) :
                if ( substr( $subfile, 0, 1 ) == '.' ) 
                    continue;                        
                $subbasename = basename( $subfile, ".{$ext}" );    

                $current[$subbasename] = isset( $current[$subbasename] ) ? $current[$subbasename] : array();
                $res[$subbasename] = self::_parseFilename( "$filename/{$subfile}", $current[$subbasename], $ext, $attr );
            endwhile;
            closedir( $subdir );
            return $res;
        endif;
    }
    
    /* = = */
    private static function _parseConfig( $filename, $defaults = array(), $eval = true )
    {
        if( $eval ) :
            return wp_parse_args( self::parseAndEval( $filename ), $defaults );
        else :
            return wp_parse_args( self::parseFile( $filename ), $defaults );
        endif;
    }
        
    /* = TRAITEMENT DU FICHIER DE CONFIGURATION = */
    public static function parseFile( $filename )
    {
        $input = file_get_contents( $filename );
        
        return spyc_load( $input );
    }
    
    /* = TRAITEMENT ET INTERPRETATION PHP DU FICHIER DE CONFIGURATION = */
    public static function parseAndEval( $filename )
    {
        $input = self::parseFile( $filename );
        
        return self::evalPHP( $input );
    }
    
    /* = INTERPRETATION PHP = */
    /** == Evaluation PHP == **/    
    public static function evalPHP( $input )
    {
        array_walk_recursive( $input, array( __CLASS__, '_pregReplacePHP' ) );

        return $input;
    }
    
    /* = = */
    public static function set( $type, $params, $value, $merge = true )
    {
        $type = strtolower($type);
        
        if( isset( tiFy::$Params[$type] ) ) :
            if( $merge ) :
                tiFy::$Params[$type][$params] = wp_parse_args( $value, tiFy::$Params[$type][$params] );
            else :
               tiFy::$Params[$type][$params] = $value;
            endif;
        endif;
    }
    
    /** == Remplacement du code PHP par sa valeur == **/
    private static function _pregReplacePHP( &$input )
    {
        if( preg_match( '/<\?php(.+?)\?>/is', $input ) )
            $input = preg_replace_callback( '/<\?php(.+?)\?>/is', function( $matches ){ return self::_phpEvalOutput( $matches );}, $input );

        return $input;
    }
    
    /** == Récupération de la valeur du code PHP trouvé == **/
    private static function _phpEvalOutput( $matches )
    {
        ob_start();
        eval( $matches[1] );
        $output = ob_get_clean();
        
        return $output;
    }
}