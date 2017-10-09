<?php
/**
 * @see https://developers.google.com/speed/docs/insights/rules
 * @see https://tools.pingdom.com
 * @see https://gtmetrix.com/
 * @see http://www.webpagetest.org/
 * @see https://developers.google.com/speed/pagespeed/insights/
 * @see http://yslow.org/
 */

namespace tiFy\Core\Cache;

use tiFy\Core\Cache\Minify\Styles;
use tiFy\Core\Cache\Minify\Scripts;

class Cache extends \tiFy\App\Core
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        return;
        parent::__construct();

        // Définition des actions de déclenchement
        self::tFyAppActionAdd('init');
        self::tFyAppActionAdd('wp_head', null, 0);
    }

    /**
     * DECLENCHEURS
     */
    /**
     *
     */
    final public static function wp_head()
    {
        new Styles;
        new Scripts;
    }
}