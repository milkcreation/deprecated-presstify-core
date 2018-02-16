<?php
/**
 * @name Tag
 * @desc Affichage de balise Html
 * @package presstiFy
 * @namespace tiFy\Components\Layouts\Tag\Tag
 * @version 1.1
 * @subpackage Components
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Layouts\Tag;

use tiFy\Core\Layout\AbstractFactory;

class Tag extends AbstractFactory
{
    /**
     * Liste des attributs de la balise Html
     * @var array
     */
    private $TagAttrs = [];

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    public function parse($args = [])
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
        $tag = $this->get('tag', 'div');
        $attrs = $this->TagAttrs ? ' '. implode(' ', $this->TagAttrs) : '';
        $content = $this->get('content');

        ob_start();
        self::tFyAppGetTemplatePart('tag', null, compact('tag', 'attrs', 'content'));

        return ob_get_clean();
    }
}