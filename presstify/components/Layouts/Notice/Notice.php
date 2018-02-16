<?php
/**
 * @name Notice
 * @desc Controleur d'affichage de message de notification
 * @package presstiFy
 * @namespace tiFy\Components\Layouts\Notice
 * @version 1.1
 * @subpackage Components
 * @since 1.2.593
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Layouts\Notice;

use tiFy\Core\Layout\AbstractFactory;

class Notice extends AbstractFactory
{
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tiFyLayoutNotice',
            self::tFyAppAssetsUrl('Notice.css', get_class()),
            [],
            180214
        );
        \wp_register_script(
            'tiFyLayoutNotice',
            self::tFyAppAssetsUrl('Notice.js', get_class()),
            ['jquery'],
            180214,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyLayoutNotice');
        \wp_enqueue_script('tiFyLayoutNotice');
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *
     * }
     * @return array
     */
    final protected function parse($attrs = [])
    {
        $defaults = [
            'container_id'    => 'tiFyLayout-notice--' . $this->getIndex(),
            'container_class' => '',
            'text'            => 'Lorem ipsum dolor site amet',
            'dismissible'     => false,
            'type'            => 'info'
        ];
        $attrs = array_merge($defaults, $attrs);

        $class = "tiFyLayout-notice tiFyLayout-notice--" . strtolower($attrs['type']);
        $attrs['container_class'] = $attrs['container_class']
            ? $class . " " . $attrs['container_class']
            : $class;

        if ($attrs['dismissible'] !== false) :
            $attrs['dismissible'] = is_bool($attrs['dismissible'])
                ? '&times;'
                : (string)$attrs['dismissible'];
        endif;

        return $attrs;
    }

    /**
     * Affichage
     *
     * @return string
     */
    final protected function display()
    {
        ob_start();
        self::tFyAppGetTemplatePart('notice', $this->getId(), $this->compact());

        return ob_get_clean();
    }
}