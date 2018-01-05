<?php
/**
 * @name Spinkit
 * @desc Controleur d'affichage d'un indicateur de préchargement
 * @package presstiFy
 * @namespace tiFy\Core\Control\Spinkit
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 * @see http://tobiasahlin.com/spinkit/
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Spinkit;

/**
 * @Overrideable \App\Core\Control\Spinkit\Spinkit
 *
 * <?php
 * namespace \App\Core\Control\Spinkit
 *
 * class Spinkit extends \tiFy\Core\Control\Spinkit\Spinkit
 * {
 *
 * }
 */

class Spinkit extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'spinkit';

    /**
     * DECLENCHEURS
     */
    /**
     * Mise en file des scripts
     *
     * @param string|null $spinkit Type de préloader rotating-plane|fading-circle|folding-cube|double-bounce|wave|wandering-cubes|spinner-pulse|chasing-dots|three-bounce|circle|cube-grid
     *
     * @return void
     */
    public static function enqueue_scripts($spinkit = null)
    {
        if (!$spinkit || !in_array($spinkit, ['rotating-plane', 'fading-circle', 'folding-cube', 'double-bounce', 'wave', 'wandering-cubes', 'spinner-pulse', 'chasing-dots', 'three-bounce', 'circle', 'cube-grid'])) :
            \wp_enqueue_style('spinkit');
        else :
            \wp_enqueue_style("spinkit-{$spinkit}");
        endif;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du controleur
     *
     * @param array $attrs Attr
     * @return stringBrowserFolderContent-itemIcon
     */
    protected static function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'container_id'    => 'tiFyCoreControl-spinkit--' . self::$Instance,
            'container_class' => '',
            'type'            => 'spinner-pulse',
        ];
        $attrs = \wp_parse_args($attrs, $defaults);
        extract($attrs);

        if ($echo) :
            self::tFyAppGetTemplatePart($attrs['type'], null, compact(array_keys($defaults)));
        else :
            ob_start();
            self::tFyAppGetTemplatePart($attrs['type'], null, compact(array_keys($defaults)));
            return ob_get_clean();
        endif;
    }
}