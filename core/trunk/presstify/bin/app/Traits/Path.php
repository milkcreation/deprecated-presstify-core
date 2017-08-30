<?php
namespace tiFy\App\Traits;

use \tiFy\tiFy;
use \tiFy\Lib\File;

trait Path
{    
    /**
     * Url absolue vers  la racine de la classe
     */
    protected static $_ClassShortName;
    
    /**
     * Url absolue vers  la racine de la classe
     */
    protected static $_Filename;
    
    /**
     * Chemin absolu vers le dossier racine de la classe
     */
    protected static $_Dirname;
    
    /**
     * Url absolue vers la racine de la classe
     */
    protected static $_Url;
    
    /**
     * Chemin relatif à la racine de la classe
     */
    protected static $_RelPath;
    
    /**
     * Url absolue vers la racine de la classe
     */
    protected static $_AssetsUrl;
    
    /**
     * Chemin vers un gabarit d'affichage en contexte
     */
    protected static $_TemplatePath;

    /**
     * CONTROLEURS
     */
    /** == Récupération du répertoire de déclaration de la classe == **/
    public static function getAssetsUrl( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
                    
        if( ! isset( self::$_AssetsUrl[$CalledClass] ) ) :
            self::$_AssetsUrl[$CalledClass] = tiFy::$AbsUrl . '/bin/assets/'. untrailingslashit( File::getRelativeFilename( self::tFyAppDirname( $CalledClass ), tiFy::$AbsDir ) );
        endif;
        
        return self::$_AssetsUrl[$CalledClass];
    }
    
    /**
     * Récupération du répertoire de stockage des gabarits de l'appli
     */
    public static function getAppTemplateDir( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
                
        return self::tFyAppDirname( $CalledClass ) . '/templates/';
    }
    
    /**
     * Récupération du répertoire de stockage des gabarits du theme pour l'appli
     */
    public static function getThemeTemplateDir( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        $subdir = ltrim( File::getRelativeFilename( self::tFyAppDirname( $CalledClass ), tiFy::$AbsDir ), '/' );
        
        return $subdir ? get_template_directory() . '/templates/' . trailingslashit( $subdir ) : get_template_directory() . '/templates/';
    }
    
    /**
     * Récupération du gabarit d'affichage
     */
    public static function getQueryTemplate( $template = null, $type, $templates = array(), $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();

        if( ! isset( self::$_TemplatePath[$CalledClass] ) ) :
           self::$_TemplatePath[$CalledClass] = array(); 
        endif;
        
        if( $template && ! in_array( $template, $templates ) )
            array_unshift ( $templates, $template );
            
        if( ! isset( self::$_TemplatePath[$CalledClass][$type] ) ) :
            $located = '';
            // Récupération du gabarit depuis le thème
            foreach( $templates as $template_name ) :
                if ( ! $template_name )
                    continue;
                if ( ! file_exists( self::getThemeTemplateDir( $CalledClass ) . $template_name ) ) 
                    continue;
                
                $located = self::getThemeTemplateDir( $CalledClass ) . $template_name;
                break;
            endforeach;
            
            // Récupération du gabarit depuis l'application tiFy
            if( ! $located ) :
                // Récupération du gabarit depuis le thème
                foreach( $templates as $template_name ) :
                    if ( ! $template_name )
                         continue;
                    if ( ! file_exists( self::getAppTemplateDir( $CalledClass ) . $template_name ) )
                        continue;
                    
                    $located = self::getAppTemplateDir( $CalledClass ) . $template_name;
                    break;
                endforeach;
            endif;
            
            if( ! $located ) :
                $located = $template;
            endif;
            self::$_TemplatePath[$CalledClass][$type] = $located;
        endif;
      
        return self::$_TemplatePath[$CalledClass][$type];
    }
    
    /**
     * Chargement du gabarit d'affichage
     */
    public static function getTemplatePart($slug, $name = null, $args = array(), $CalledClass = null)
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();

        if ( '' !== $name )
            $templates[] = "{$slug}-{$name}.php";
        $templates[] = "{$slug}.php";
        
        $_template_file = self::getQueryTemplate( null, $CalledClass .'-'. $slug . ( $name ? '-'. $name : '' ), $templates, $CalledClass );

        extract( $args );
        require( $_template_file );
    }
    
    /**
     * Récupération des données accessibles
     * @deprecated
     */
    public function __get( $name ) 
    {            
        if ( in_array( $name, $this->GetPathAttrs ) ) :
            if( ! $this->{$name} ) :
                if( method_exists( $this, 'set'. $name ) ) :
                    return call_user_func( array( $this, 'set'. $name ) );
                endif;
            else :
                return $this->{$name};
            endif;
        endif;
        
        return false;
    }
    
    /**
     * Vérification d'existance des données accessibles
     * @deprecated
     */
    public function __isset( $name )
    {
        if ( in_array( $name, $this->GetPathAttrs ) ) :
            if( ! $this->{$name} ) :
                if( method_exists( $this, 'set'. $name ) ) :
                    return call_user_func( array( $this, 'set'. $name ) );
                endif;
            endif;
            return isset( $this->{$name} );
        endif;
        
        return false;
    }
}