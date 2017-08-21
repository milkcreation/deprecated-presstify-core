<?php
namespace tiFy\Environment\Traits;

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
            self::$_AssetsUrl[$CalledClass] = tiFy::$AbsUrl . '/bin/assets/'. untrailingslashit( File::getRelativeFilename( self::getDirname( $CalledClass ), tiFy::$AbsDir ) );
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
                
        return self::getDirname( $CalledClass ) . '/templates/';
    }
    
    /**
     * Récupération du répertoire de stockage des gabarits du theme pour l'appli
     */
    public static function getThemeTemplateDir( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        $subdir = ltrim( File::getRelativeFilename( self::getDirname( $CalledClass ), tiFy::$AbsDir ), '/' );
        
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
    public static function getTemplatePart( $slug, $name = null, $args = array(), $CalledClass = null )
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
     * DEPRECATED
     */
    /** 
     * Récupération du nom court de la classe
     * @deprecated
     */
    public static function classShortName( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        if( ! isset( self::$_ClassShortName[$CalledClass] ) ) :
            self::$_ClassShortName[$CalledClass] = ( new \ReflectionClass( $CalledClass ) )->getShortName();
              
        endif;
        
        return self::$_ClassShortName[$CalledClass];
    }
    
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated
     */
    public static function getFilename( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        if( ! isset( self::$_Filename[$CalledClass] ) ) :
            $reflection = new \ReflectionClass( $CalledClass );
            self::$_Filename[$CalledClass] = $reflection->getFileName();
        endif;
        
        return self::$_Filename[$CalledClass];
    }
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated
     */
    public static function getDirname( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        if( ! isset( self::$_Dirname[$CalledClass] ) ) :
            self::$_Dirname[$CalledClass] = dirname( self::getFileName( $CalledClass ) );
        endif;
        
        return self::$_Dirname[$CalledClass];
    }
    
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated
     */
    public static function getUrl( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        if( ! isset( self::$_Url[$CalledClass] ) ) :
            self::$_Url[$CalledClass] = untrailingslashit( File::getFilenameUrl( self::getDirname( $CalledClass ), tiFy::$AbsPath ) );
        endif;
        
        return self::$_Url[$CalledClass];
    }
    
    /**
     * Récupération du répertoire de déclaration de la classe
     * @deprecated
     */
    public static function getRelPath( $CalledClass = null )
    {
        if( ! $CalledClass )
            $CalledClass = get_called_class();
        
        if( ! isset( self::$_RelPath[$CalledClass] ) ) :
            self::$_RelPath[$CalledClass] = untrailingslashit( File::getRelativeFilename( self::getDirname( $CalledClass ), tiFy::$AbsPath ) );
        endif;
        
        return self::$_RelPath[$CalledClass];
    }
    
    /**
     * Informations sur la classe
     * @deprecated
     */
    private $ReflectionClass;
    
    /**
     * Nom court de la classe
     * @deprecated
     */
    private $ClassShortName;
    
    /**
     * Chemin absolu vers le fichier de déclaration de la classe
     * @deprecated
     */
    private $Filename;
    
    /**
     * Chemin absolu vers le dossier racine de la classe
     * @deprecated
     */ 
    private $Dirname;

    /**
     * Nom du dossier racine de la classe
     * @deprecated
     */
    private $Basename;
    
    /**
     * Url absolue vers la racine de la classe
     * @deprecated
     */
    private $Url;
    
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
    
    
    /**
     * Liste des arguments pouvant être récupérés
     * @deprecated
     */
    private $GetPathAttrs       = array( 'ReflectionClass', 'ClassShortName', 'Filename', 'Dirname', 'Basename', 'Url' );
    
    /**
     * Définition des informations de la classe
     * @deprecated
     */
    private function setReflectionClass()
    {
        return $this->ReflectionClass = new \ReflectionClass(get_called_class());
    }
     
    /**
     * Définition du chemin absolu vers le fichier de déclaration de la classe fille
     * @deprecated
     */
    private function setFilename()
    {
        return $this->Filename = self::getFilename();
    }
    
    /**
     * Définition du chemin absolu vers le dossier racine de la classe fille
     * @deprecated
     */
    private function setDirname()
    {
        return $this->Dirname = self::getDirname();
    }
    
    /**
     * Définition du nom du dossier racine de la classe fille
     * @deprecated
     */
    private function setBasename()
    {                
        return $this->Basename = basename( self::getDirname() );
    }
    
    /**
     * Définition de l'url absolue vers le dossier racine de la classe fille
     * @deprecated
     */
    private function setUrl()
    {        
        return $this->Url = self::getUrl();
    }
}