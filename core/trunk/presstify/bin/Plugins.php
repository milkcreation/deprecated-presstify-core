<?php
namespace tiFy;

use tiFy\Apps;

final class Plugins
{
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        $this->loadMustUse();
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * 
     * @param string $id Identifiant de l'extension
     * @param mixed $attrs Attributs de configuration de l'extension
     * 
     * @return NULL|object
     */
    public static function register($id, $attrs = array())
    {
        $classname = "tiFy\\Plugins\\{$id}\\{$id}";

        Apps::register(
            $classname,
            'plugins',
            array(
                'Id'        => $id,
                'Config'    => $attrs
            )
        );
    }
    
    /**
     * Chargement des must-use
     */
    protected function loadMustUse()
    {
        foreach(glob(TIFY_PLUGINS_DIR . '/*', GLOB_ONLYDIR) as $plugin_dir) :
            if(! file_exists($plugin_dir . '/MustUse'))
                continue;
            if(! $dh = opendir($plugin_dir . '/MustUse'))
                continue;
            while(($file = readdir( $dh )) !== false) :
                if(substr( $file, -4 ) == '.php') :
                    include_once($plugin_dir . '/MustUse/' . $file);
                endif;
            endwhile;
        endforeach;
    }
}