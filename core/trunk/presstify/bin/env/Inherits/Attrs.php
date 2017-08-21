<?php
namespace tiFy\Environment\Inherits;

use \tiFy\tiFy;
use \tiFy\Lib\File;

class Attrs
{
    /**
     * Attributs de l'app
     * @var array
     */
    private $Attrs = array(
        /**
         * Informations sur la classe
         * @var null|\ReflectionClass[]
         */
        'ReflectionClass'           => null,
        /**
         * Nom de la classe
         * @var string
         */
        'ClassName'                 => '',
        /**
         * Nom court de la classe
         * @var string
         */
        'ShortName'                 => '',
        /**
         * Espace de Nom
         * @var string[]
         */
        'Namespace'                 => '',
        /**
         * Type d'application
         * @var void|string Components|Core|Plugins|Set
         */
        'Type'                      => '',
        /**
         * Chemin absolu vers le fichier de la classe
         * @var void|string
         */
        'Filename'                  => '',
        /**
         * Chemin absolu vers le repertoire racine de la classe
         * @var void|string
         */
        'Dirname'                   => '',
        /**
         * Url absolu vers le repertoire de la classe
         * @var void|string
         */
        'Url'                       => '',
        /**
         * Chemin relatif vers le repertoire de la classe
         * @var void|string
         */
        'Rel'                       => ''
    );
    
    /**
     * CONSTRUCTEUR
     * @param string $classname
     * 
     * @return void
     */
    public function __construct($classname)
    {
        $reflection = new \ReflectionClass($classname);
        $classname = $reflection->getName();
        $namespace = $reflection->getNamespaceName();
        $shortname = $reflection->getShortName();        
        
        if($app = tiFy::getApp($classname)) :
            $type = $app['type'];
        else :
            $type = null;
        endif;        
        
        // Chemins
        $filename = $reflection->getFileName();
        $dirname = dirname($filename);
        $url = untrailingslashit(File::getFilenameUrl($dirname, tiFy::$AbsPath));
        $rel = untrailingslashit(File::getRelativeFilename($dirname, tiFy::$AbsPath));

        // Définition des attributs
        $this->Attrs = array(
            'ReflectionClass'   => $reflection,
            'ClassName'         => $classname,
            'ShortName'         => $shortname,
            'Namespace'         => $namespace,
            'Type'              => $type,
            'Filename'          => $filename,
            'Dirname'           => dirname($filename),
            'Url'               => $url,
            'Rel'               => $rel
        );
    }
    
    /**
     * Récupération de la liste des attributs
     */
    public function getList()
    {
        return $this->Attrs;
    }
    
    /**
     * Récupération d'un attribut
     */
    public function get($attr)
    {
        if(isset($this->Attrs[$attr]))
            return $this->Attrs[$attr];
    }
}