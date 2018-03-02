<?php

/**
 * @name NumberJs
 * @desc Champ de selection de valeur numérique JS
 * @package presstiFy
 * @namespace tiFy\Components\Fields\NumberJs
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Fields\NumberJs;

use tiFy\Core\Field\AbstractFactory;

/**
 * @param array $args {
 *      Liste des attributs de configuration du champ
 *
 *      @var string $before Contenu placé avant le champ
 *      @var string $after Contenu placé après le champ
 *      @var string $container_id Id HTML du conteneur du champ
 *      @var string $container_class Classe HTML du conteneur du champ
 *      @var array $attrs Liste des propriétés de la balise HTML
 *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
 *      @var int $value Attribut de configuration de la valeur initiale de soumission du champ "value"
 *      @var array $data-options {
 *          Liste des options du contrôleur ajax
 *          @see http://api.jqueryui.com/spinner/
 *      }
 * }
 */
class NumberJs extends AbstractFactory
{
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        \wp_register_style(
            'tiFyFieldNumberJs',
            self::tFyAppAssetsUrl('NumberJs.css', get_class()),
            ['dashicons'],
            171019
        );
        \wp_register_script(
            'tiFyFieldNumberJs',
            self::tFyAppAssetsUrl('NumberJs.js', get_class()),
            ['jquery-ui-spinner'],
            171019,
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
        \wp_enqueue_style('tiFyFieldNumberJs');
        \wp_enqueue_script('tiFyFieldNumberJs');
    }

    /**
     * Traitement des attributs de configuration
     *
     * @return array
     */
    final protected function parse($args = [])
    {
        // Pré-traitement des attributs de configuration
        $args = parent::parse($args);

        // Traitement des attributs de configuration
        $defaults = [
            'before'          => '',
            'after'           => '',
            'container_id'    => 'tiFyField-numberJsContainer--' . $this->getIndex(),
            'container_class' => '',
            'attrs'           => [],
            'name'            => '',
            'value'           => 0,
            'data-options'    => []
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['container_class'])) :
            $args['container_class'] = 'tiFyField-numberJsContainer ' . $args['container_class'];
        else :
            $args['container_class'] = 'tiFyField-numberJsContainer';
        endif;

        if (!isset($args['attrs']['id'])) :
            $args['attrs']['id'] = 'tiFyField-numberJs--' . $this->getIndex();
        endif;
        $args['attrs']['type'] = 'text';
        $args['attrs']['data-options'] = array_merge(
            [
                'icons' => [
                    'down' => 'dashicons dashicons-arrow-down-alt2',
                    'up'   => 'dashicons dashicons-arrow-up-alt2',
                ]
            ],
            $args['data-options']
        );

        return $args;
    }

    /**
     * Affichage
     *
     * @return string
     */
    protected function display()
    {
        ob_start();
?><?php $this->before(); ?>
    <div id="<?php echo $this->get('container_id'); ?>" class="<?php echo $this->get('container_class'); ?>">
        <input <?php echo $this->attrs(); ?> />
    </div>
<?php $this->after(); ?><?php

        return ob_get_clean();
    }
}