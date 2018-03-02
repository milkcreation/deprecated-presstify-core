<?php 
namespace tiFy\Lib;

use tiFy\tiFy;

class Country
{
    /**
     * Récupération du fichier drapeau d'un pays
     * @see https://fr.wikipedia.org/wiki/ISO_3166-1
     * 
     * @param string $country Identification du pays au format ISO 3166-1 alpha-2
     * 
     * @return void|string
     */
    public static function getFlagFilename($country)
    {
        $flagsLib = tiFy::$AbsDir . '/bin/assets/vendor/country-flags-master';
        $country = strtolower($country);
        $filename = "{$flagsLib}/svg/{$country}.svg";

        // Bypass
        if (file_exists($filename)) :
            return $filename;
        endif;
    }

    /**
     * Récupération de la source image du fichier d'un pays
     * @see https://fr.wikipedia.org/wiki/ISO_3166-1
     *
     * @param string $country Identification du pays au format ISO 3166-1 alpha-2
     *
     * @return void|string
     */
    public static function getFlagImgSrc($country)
    {
        if (!$filename = self::getFlagFilename($country)) :
            return;
        endif;

        if ($rel = preg_replace("#" . preg_quote(ABSPATH, '/') . "#", '', $filename)) :
            return site_url($rel);
        else :
            return "data:image/svg+xml;base64," . base64_encode(file_get_contents($filename));
        endif;
    }

}