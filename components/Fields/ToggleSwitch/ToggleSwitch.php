<?php

/**
 * @name ToggleSwitch
 * @desc Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 * @package presstiFy
 * @namespace tiFy\Components\Fields\ToggleSwitch
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Components\Fields\ToggleSwitch;

use tiFy\Core\Field\AbstractFactory;
use tiFy\Core\Field\Field;

/**
 * @param array $args {
 *      Liste des attributs de configuration du champ
 *
 * @var string $before Contenu placé avant le champ
 * @var string $after Contenu placé après le champ
 * @var string $container_id Id HTML du conteneur du champ
 * @var string $container_class Classe HTML du conteneur du champ
 * @var string $name Attribut de configuration de la qualification de soumission du champ "name"
 * @var string $value Attribut de configuration de la valeur initiale de soumission du champ "value"
 * @var string $label_on
 * @var string $label_off
 * @var bool|int|string $value_on
 * @var bool|int|string $value_off
 * }
 */
class ToggleSwitch extends AbstractFactory
{
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        \wp_register_style(
            'tiFyFieldToggleSwitch',
            self::tFyAppAssetsUrl('ToggleSwitch.css', get_class()),
            [],
            170724
        );
        \wp_register_script(
            'tiFyFieldToggleSwitch',
            self::tFyAppAssetsUrl('ToggleSwitch.js', get_class()),
            ['jquery'],
            170724
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyFieldToggleSwitch');
        \wp_enqueue_script('tiFyFieldToggleSwitch');
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
            'container_id'    => 'tiFyField-toggleSwitch--' . $this->getIndex(),
            'container_class' => '',
            'name'            => 'tiFyField-toggleSwitch-' . $this->getIndex(),
            'value'           => 'on',
            'label_on'        => _x('Oui', 'tiFyFieldToggleSwitch', 'tify'),
            'label_off'       => _x('Non', 'tiFyFieldToggleSwitch', 'tify'),
            'value_on'        => 'on',
            'value_off'       => 'off',
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['container_class'])) :
            $args['container_class'] = 'tiFyField-toggleSwitch';
        else :
            $args['container_class'] = 'tiFyField-toggleSwitch ' . $args['container_class'];
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
        ob_start();
        ?><?php $this->before(); ?>
        <div id="<?php echo $this->get('container_id'); ?>" class="<?php echo $this->get('container_class'); ?>">
            <div class="tiFyField-toggleSwitchWrapper">
                <?php
                echo Field::Radio(
                    [
                        'after'   => (string)Field::Label([
                            'content' => $this->get('label_on'),
                            'attrs'   => [
                                'for'   => $this->getId() . '--on',
                                'class' => 'tiFyField-toggleSwitchLabel tiFyField-toggleSwitchLabel--on'
                            ]
                        ]),
                        'attrs'   => [
                            'id'           => $this->getId() . '--on',
                            'class'        => 'tiFyField-toggleSwitchRadio tiFyField-toggleSwitchRadio--on',
                            'autocomplete' => 'off'
                        ],
                        'name'    => $this->getName(),
                        'value'   => $this->get('value_on'),
                        'checked' => $this->getValue()
                    ],
                    true
                );
                ?>
                <?php
                echo Field::Radio(
                    [
                        'after'   => (string)Field::Label(
                            [
                                'content' => $this->get('label_off'),
                                'attrs'   => [
                                    'for'   => $this->getId() . '--off',
                                    'class' => 'tiFyField-toggleSwitchLabel tiFyField-toggleSwitchLabel--off'
                                ]
                            ]
                        ),
                        'attrs'   => [
                            'id'           => $this->getId() . '--off',
                            'class'        => 'tiFyField-toggleSwitchRadio tiFyField-toggleSwitchRadio--off',
                            'autocomplete' => 'off'
                        ],
                        'name'    => $this->getName(),
                        'value'   => $this->get('value_off'),
                        'checked' => $this->getValue()
                    ],
                    true
                );
                ?>
                <span class="tiFyField-toggleSwitchHandler"></span>
            </div>
        </div>
        <?php $this->after(); ?>
        <?php

        return ob_get_clean();
    }
}