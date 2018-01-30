<?php
/**
 * @name Trigger
 * @desc Bouton d'action
 * @package presstiFy
 * @namespace tiFy\Core\Control\Trigger\Trigger
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Trigger;

class Trigger extends \tiFy\Core\Control\Factory
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
            'tag'     => 'button',
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

?><<?php echo $args['tag']; ?> <?php echo implode(' ', $tag_attrs);?>><?php echo $args['content']; ?></<?php echo $args['tag']; ?>><?php
    }
}