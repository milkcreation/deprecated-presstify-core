<?php
/**
 * @name Link
 * @desc Lien
 * @package presstiFy
 * @namespace tiFy\Core\Control\Link
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Link;

class Link extends \tiFy\Core\Control\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Liste des propriétés de la balise HTML
     *      @var string $content Contenu de la balise HTML
     * }
     *
     * @return string
     */
    protected function display($args = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            'attrs'   => [],
            'content' => __('Cliquer', 'tify')
        ];
        $args = array_merge($defaults, $args);

        $tag_attrs = [];
        if (!empty($args['attrs'])) :
            foreach ($args['attrs'] as $k => $v) :
                if (is_array($v)) :
                    $v = rawurlencode(json_encode($v));
                endif;
                if (is_int($k)) :
                    $tag_attrs[]= "{$v}";
                else :
                    $tag_attrs[]= "{$k}=\"{$v}\"";
                endif;
            endforeach;
        endif;

?><a <?php echo implode(' ', $tag_attrs);?>><?php echo $args['content']; ?></a><?php
    }
}