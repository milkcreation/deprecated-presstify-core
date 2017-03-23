<?php 
namespace tiFy\Set;

use tiFy\tiFy;

class Autoload extends \tiFy\Environment\App
{
    /* = ARGUMENTS = */   
    // Liste des jeux de fonctionnalités
    private static $Sets       = array();
    
    /* = DECLENCHEURS = */
    /** == Initialisation == **/
    public function __construct()
    {
        // Liste des sets inclus dans le paquet tiFy
        foreach( glob( __DIR__.'/*', GLOB_ONLYDIR ) as $filename ) :
            $set_id     = basename( $filename );
            $attrs      = array( 'cb' => "\\tiFy\\Set\\$set_id\\$set_id" );
            
            self::$Sets[$set_id] = $attrs;
        endforeach;
        
        if( $customs = tiFy::getConfig( 'set', array() ) ) :
            foreach( $customs as $set_id => $attrs ) :
                if( isset( self::$Sets[$set_id] ) )
                    continue;
                
                // Formatage de l'espace de nom
                if( isset( $attrs['namespace'] ) )
                    $attrs['namespace'] = "\\". trim( $attrs['namespace'], "\\" ) ."\\";
                // Formatage du point d'entrée unique
                if( isset( $attrs['bootstrap'] ) )
                    $attrs['bootstrap'] = trim( $attrs['bootstrap'], "\\" );
                                    
                if( empty( $attrs['cb'] ) && isset( $attrs['namespace'] ) && isset( $attrs['bootstrap'] ) ) :
                    $attrs['cb'] =  $attrs['namespace'] . $attrs['bootstrap'];
                endif;
                               
                self::$Sets[$set_id] = $attrs;
            endforeach;
        endif;   
        
        // Instanciation
        $namespaces = array();
        if( isset( tiFy::$Params['set'] ) ) :
            foreach( (array) array_keys( tiFy::$Params['set'] ) as $set_id ) :
                if( ! isset( self::$Sets[$set_id] ) )
                    continue;
                $attrs = self::$Sets[$set_id];
                
                if( isset( $attrs['namespace'] ) && isset( $attrs['base_dir'] ) ) :
                    if( ! isset( $namespaces[$attrs['namespace']] ) || ! in_array( $attrs['base_dir'], $namespaces[$attrs['namespace']] ) ) :
                        $namespaces[$attrs['namespace']][] = $attrs['base_dir'];
                        tify_class_loader( $attrs['namespace'], $attrs['base_dir'] );
                    endif;
                endif;
                
                if( empty( $attrs['cb'] ) )
                    continue;
                
                // @todo Personnaliser l'override
                if( class_exists( $attrs['cb'] ) ) :
                    $path = ( isset( $attrs['bootstrap'] ) ) ? array( "\\". self::getOverrideNamespace() ."\\tiFy\\Set\\". $set_id ."\\".$attrs['bootstrap'] ) : array();
                    $class = self::getOverride( $attrs['cb'], $path );    

                    new $class;
                endif;
            endforeach;
        endif;
    }  
}