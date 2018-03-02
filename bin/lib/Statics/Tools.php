<?php
namespace tiFy\Statics;

/**
 * @since 1.0.344
 * @author pitcho
 */
class Tools
{
    /**
     * Traitement résursif d'arguments
     * @param array $a Tableau d'arguments à traiter
     * @param array $b Tableau de valeurs par défaut
     * @return array Tableau d'arguements traité
     */
    public static function parseArgsRecursive( &$a, $b )
    {
        $a = (array) $a;
    	$b = (array) $b;
    	$result = $b;
    	foreach ($a as $k => &$v) :
    		if (is_array($v) && isset($result[$k])) :
    			$result[$k] = self::parseArgsRecursive($v, $result[$k]);
    		else :
    			$result[$k] = $v;
    		endif;
    	endforeach;
    	return $result;
    }
}