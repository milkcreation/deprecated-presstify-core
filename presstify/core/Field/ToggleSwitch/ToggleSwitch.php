<?php
/**
 * @name ToggleSwitch
 * @desc Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 * @package presstiFy
 * @namespace tiFy\Core\Field\ToggleSwitch
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\ToggleSwitch;

use tiFy\Core\Field\Field;

class ToggleSwitch extends \tiFy\Core\Field\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final protected function init()
    {
        wp_register_style('tiFyCoreFieldToggleSwitch', self::tFyAppAssetsUrl('ToggleSwitch.css', get_class()), [], 170724);
        wp_register_script('tiFyCoreFieldToggleSwitch', self::tFyAppAssetsUrl('ToggleSwitch.js', get_class()), ['jquery'], 170724);
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final protected function enqueue_scripts()
    {
        wp_enqueue_style('tiFyCoreFieldToggleSwitch');
        wp_enqueue_script('tiFyCoreFieldToggleSwitch');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des attributs de configuration
     *
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @var string $before Contenu placé avant le champ
     *      @var string $after Contenu placé après le champ
     *      @var string $container_id Id HTML du conteneur du champ
     *      @var string $container_class Classe HTML du conteneur du champ
     *      @var string $name Attribut de configuration de la qualification de soumission du champ "name"
     *      @var string $value Attribut de configuration de la valeur initiale de soumission du champ "value"
     *      @var string $label_on
     *      @var string $label_off
     *      @var bool|int|string $value_on
     *      @var bool|int|string $value_off
     * }
     *
     * @return array
     */
    final protected function parseAttrs($args = [])
    {
        // Pré-traitement des attributs de configuration
        $args = parent::parseAttrs($args);

        // Traitement des attributs de configuration
        $defaults = [
            'before'          => '',
            'after'           => '',
            'container_id'    => 'tiFyCoreField-toggleSwitch--' . $this->getIndex(),
            'container_class' => '',
            'name'            => 'tiFyCoreField-toggleSwitch-' . $this->getIndex(),
            'value'           => 'on',
            'label_on'        => _x('Oui', 'tiFyCoreFieldToggleSwitch', 'tify'),
            'label_off'       => _x('Non', 'tiFyCoreFieldToggleSwitch', 'tify'),
            'value_on'        => 'on',
            'value_off'       => 'off',
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['container_class'])) :
            $args['container_class'] = 'tiFyCoreField-toggleSwitch';
        else :
            $args['container_class'] = 'tiFyCoreField-toggleSwitch ' . $args['container_class'];
        endif;

        return $args;
    }

    /**
     * Affichage
     *
     * @return string
     */
    protected function display($args = [])
    {
?><?php $this->before(); ?>
<div id="<?php echo $this->getAttr('container_id');?>" class="<?php echo $this->getAttr('container_class');?>">
    <div class="tiFyCoreField-toggleSwitchWrapper">
        <?php
            Field::Radio(
                [
                    'after'   => Field::Label( [
                            'content' => $this->getAttr( 'label_on' ),
                            'attrs'   => [
                                'for'   => $this->getId() . '--on',
                                'class' => 'tiFyCoreField-toggleSwitchLabel tiFyCoreField-toggleSwitchLabel--on'
                            ]
                        ] ),
                    'attrs'   => [
                        'id'           => $this->getId() . '--on',
                        'class'        => 'tiFyCoreField-toggleSwitchRadio tiFyCoreField-toggleSwitchRadio--on',
                        'autocomplete' => 'off'
                    ],
                    'name'    => $this->getName(),
                    'value'   => $this->getAttr( 'value_on' ),
                    'checked' => $this->getValue()
                ],
                true
            );
        ?>
        <?php
            Field::Radio(
                [
                    'after' => Field::Label(
                        [
                            'content' => $this->getAttr('label_off'),
                            'attrs' => [
                                'for' => $this->getId() . '--off',
                                'class' => 'tiFyCoreField-toggleSwitchLabel tiFyCoreField-toggleSwitchLabel--off'
                            ]
                        ]
                    ),
                    'attrs' => [
                        'id'    => $this->getId() . '--off',
                        'class' => 'tiFyCoreField-toggleSwitchRadio tiFyCoreField-toggleSwitchRadio--off',
                        'autocomplete' => 'off'
                    ],
                    'name'    => $this->getName(),
                    'value'   => $this->getAttr( 'value_off' ),
                    'checked' => $this->getValue()
                ],
                true
            );
        ?>
        <span class="tiFyCoreField-toggleSwitchHandler"></span>
    </div>
</div>
<?php $this->after(); ?>
<?php
    }
}