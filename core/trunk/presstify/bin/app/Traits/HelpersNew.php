<?php
namespace tiFy\App\Traits;

trait HelpersNew
{
    /* = ARGUMENTS = */ 
    // Intitulés des prefixes des fonctions     
    protected $HelperPrefix         = 'tify_helper';
    
    // Identifiant des fonctions d'aide au développement        
    protected $HelperNamespace      = '';
    
    // Séparateur des parties du nom de la fonction
    protected $HelperSeparator      = '_';
    
    // Liste des methodes à translater en Helpers
    protected $HelperMethods        = array();
    
    // Liste de la cartographie des nom de fonction des Helpers
    protected $HelperMethodsMap     = array();
    
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        $path = get_class( $this );
        $path = addslashes( $path );

        foreach( $this->HelperMethods as $method ) :
            $parts = array();
            if( $this->HelperPrefix ) :
                array_push( $parts, $this->HelperPrefix );
            endif;
            if( $this->HelperNamespace ) :
                array_push( $parts, $this->HelperNamespace );
            endif;
            if( $suffix = ( isset( $this->HelperMethodsMap[$method] ) ) ? $this->HelperMethodsMap[$method] : strtolower( $method ) ) :
                array_push( $parts, $suffix );
            endif;
            
            $func = implode( $this->HelperSeparator, $parts );

            if( $func && ! function_exists( $func ) )
                eval( 
                    'function '. $func . '()'.
                    '{'.
                        'return call_user_func_array( array( "'. $path . '", "'. $method .'" ), func_get_args() );'.
                    '}' 
                );
        endforeach; 
    }
}