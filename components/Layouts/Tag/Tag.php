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

/**
 * @param array $attrs {
 *      Liste des attributs de configuration
 *
 *      @var string $id Identifiant de qualification du controleur d'affichage.
 *      @var string $tag Balise HTML div|span|a|... défaut div.
 *      @var array $attrs Liste des attributs de balise HTML.
 *      @var string $content Contenu de la balise HTML.
 * }
 */
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
     * @param array $attrs Liste des attributs de configuration de la classe
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
        $id = $this->getId();
        $index = $this->getIndex();
        $tag = $this->get('tag', 'div');
        $tag_attrs = $this->TagAttrs;
        $_tag_attrs = $tag_attrs ? ' '. implode(' ', $tag_attrs) : '';
        $content = $this->get('content');

        ob_start();
        self::tFyAppGetTemplatePart('tag', $this->getId(), compact('id', 'index', 'tag', 'tag_attrs', '_tag_attrs', 'content'));

        return ob_get_clean();
    }
}