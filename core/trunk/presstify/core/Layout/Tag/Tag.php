<?php
/**
 * @name Tag
 * @desc Affichage de balise Html
 * @package presstiFy
 * @namespace tiFy\Core\Layout\Tag\Tag
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Layout\Tag;

class Tag extends \tiFy\Core\Layout\Factory
{
    /**
     * Liste des attributs de la balise Html
     * @var array
     */
    private $TagAttrs = [];

    /**
     * CONTROLEURS
     */
    /**
     *
     */
    public function parseAttrs($args = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            'tag'     => 'div',
            'attrs'   => [],
            'content' => __('Cliquer', 'tify')
        ];
        $args = array_merge($defaults, $args);

        if (!empty($args['attrs'])) :
            foreach ($args['attrs'] as $k => $v) :
                if (is_array($v)) :
                    $v = rawurlencode(json_encode($v));
                endif;
                if (is_int($k)) :
                    $this->TagAttrs[]= "{$v}";
                else :
                    $this->TagAttrs[]= "{$k}=\"{$v}\"";
                endif;
            endforeach;
        endif;

        return $args;
    }

    /**
     * Affichage
     *
     * @return string
     */
    protected function display()
    {
        $tag = $this->getAttr('tag', 'div');
        $attrs = $this->TagAttrs ? ' '. implode(' ', $this->TagAttrs) : '';
        $content = $this->getAttr('content');

        ob_start();
        self::tFyAppGetTemplatePart('tag', null, compact('tag', 'attrs', 'content'));

        return ob_get_clean();
    }
}