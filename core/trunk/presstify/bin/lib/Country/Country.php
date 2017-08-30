<?php 
namespace tiFy\Lib;

use tiFy\tiFy;

class Country
{
    /**
     * Récupération du drapeau d'un pays
     * @see https://fr.wikipedia.org/wiki/ISO_3166-1
     * 
     * @param string $country Identification du pays au format ISO 3166-1 alpha-2
     * 
     * @return void|string
     */
    public static function flag($country)
    {
        $flagsLib = tiFy::$AbsDir . '/bin/assets/vendor/country-flags-master';
        $country = strtolower($country);
        
        // Bypass
        if (! file_exists($flagsLib . '/svg/' . $country . '.svg'))
            return;
        
        return file_get_contents($flagsLib . '/svg/' . $country . '.svg');
    }
}