<?php
/**
 * @see \_deprecated_constructor();
 * @see \_deprecated_file();
 * @see \_deprecated_hook();
 */
namespace tiFy\Deprecated;

use tiFy\tiFy;

class Deprecated
{
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        // Anciennes fonction d'aide à la saisie
        require dirname(__FILE__) . '/helpers/Helpers.php';
        
        // Ancien environnement des applicatifs
        tiFy::classLoad('tiFy\Environment', dirname(__FILE__) .'/env');

        // Anciennes classe abstraites de la librairie libraries
        tiFy::classLoad('tiFy\Abstracts', dirname(__FILE__) .'/lib/Abstracts');

        // Anciennes core/Control
        tiFy::classLoad('tiFy\Core\Control', dirname(__FILE__) .'/app/core/Control', 'Deprecated');
    }
    
    /**
     * Déclaration d'une dépréciation
     * 
     * @param string $type function|constructor|file|argument|hook
     * 
     * @return void
     */
    public static function add($type, $target, $version)
    {
        $args = array_slice(func_get_args(), 1);
        call_user_func_array('_deprecated_'. $type, $args);
    }
    
    /**
     * Déclaration d'une fonction dépréciée
     * @see \_deprecated_function()
     * 
     * @param string $function Fonction appelée.
     * @param string $version Numéro de version depuis lequel la fonction est dépréciée.
     * @param string $replacement Optionnel. La fonction qui devrait être appelée en remplacement.
     * 
     * @return void
     */
    public static function addFunction($function, $version, $replacement = null)
    {
        self::add('function', $function, $version, $replacement);
    }
    
    /**
     * Déclaration d'un argument dépréciée
     * @see \_deprecated_argument()
     * 
     * @param string $function Fonction appelée.
     * @param string $version Numéro de version depuis lequel la fonction est dépréciée.
     * @param string $message Optionnel. Message concernant la modification à apporter.
     * 
     * @return void
     */
    public static function addArgument($function, $version, $message = null)
    {
        self::add('argument', $function, $version, $message);
    }
}