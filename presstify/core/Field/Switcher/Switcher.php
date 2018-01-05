<?php
/**
 * @name Text
 * @desc Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 * @package presstiFy
 * @namespace tiFy\Core\Field\Text
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field\Switcher;

use tiFy\Core\Field\Field;

class Switcher extends \tiFy\Core\Field\Factory
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
        wp_register_style('tiFyCoreFieldSwitcher', self::tFyAppAssetsUrl('Switcher.css', get_class()), [], 170724);
        wp_register_script('tiFyCoreFieldSwitcher', self::tFyAppAssetsUrl('Switcher.js', get_class()), ['jquery'], 170724);
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final protected function enqueue_scripts()
    {
        wp_enqueue_style('tiFyCoreFieldSwitcher');
        wp_enqueue_script('tiFyCoreFieldSwitcher');
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
            'container_id'    => 'tiFyCoreField-switcher--' . $this->getIndex(),
            'container_class' => '',
            'name'            => 'tiFyCoreField-switcher-' . $this->getIndex(),
            'value'           => 'on',
            'label_on'        => _x('Oui', 'tiFyCoreFieldSwitcher', 'tify'),
            'label_off'       => _x('Non', 'tiFyCoreFieldSwitcher', 'tify'),
            'value_on'        => 'on',
            'value_off'       => 'off',
        ];
        $args = array_merge($defaults, $args);

        if (!isset($args['container_class'])) :
            $args['container_class'] = 'tiFyCoreField-switcher';
        else :
            $args['container_class'] = 'tiFyCoreField-switcher ' . $args['container_class'];
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
    <div class="tiFyCoreField-switcherWrapper">
        <?php
            Field::Radio(
                [
                    'after' => Field::Label(
                        [
                            'content' => $this->getAttr('label_on'),
                            'attrs' => [
                                'for' => $this->getId() . '--on',
                                'class' => 'tiFyCoreField-switcherLabel tiFyCoreField-switcherLabel--on'
                            ]
                        ]
                    ),
                    'attrs' => [
                        'id'    => $this->getId() . '--on',
                        'class' => 'tiFyCoreField-switcherRadio tiFyCoreField-switcherRadio--on',
                        'name'  => $this->getName(),
                        'value' => $this->getAttr('value_on'),
                        'autocomplete' => 'off'
                    ],
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
                                'class' => 'tiFyCoreField-switcherLabel tiFyCoreField-switcherLabel--off'
                            ]
                        ]
                    ),
                    'attrs' => [
                        'id'    => $this->getId() . '--off',
                        'class' => 'tiFyCoreField-switcherRadio tiFyCoreField-switcherRadio--off',
                        'name'  => $this->getName(),
                        'value' => $this->getAttr('value_off'),
                        'autocomplete' => 'off'
                    ],
                    'checked' => $this->getValue()
                ],
                true
            );
        ?>
        <span class="tiFyCoreField-switcherHandler"></span>
    </div>
</div>
<?php $this->after(); ?>
<?php
    }
}