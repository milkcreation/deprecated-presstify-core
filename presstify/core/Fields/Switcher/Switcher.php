<?php
/**
 * Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 */ 
namespace tiFy\Core\Fields\Switcher;

use tiFy\Core\Fields\Fields;

class Switcher extends \tiFy\Core\Fields\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public static function init()
    {
        wp_register_style('tiFyCoreFieldsSwitcher', self::tFyAppAssetsUrl('Switcher.css', get_class()), [], 170724);
        wp_register_script('tiFyCoreFieldsSwitcher', self::tFyAppAssetsUrl('Switcher.js', get_class()), ['jquery'], 170724);
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tiFyCoreFieldsSwitcher');
        wp_enqueue_script('tiFyCoreFieldsSwitcher');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     * }
     *
     * @return string
     */
    public static function display($id = null, $args = [])
    {
        static::$Instance++;

        $defaults = [
            'before'  => '',
            'after'   => '',
            'container_id'      => 'tiFyCoreFields-Switcher--' . self::$Instance,
            'container_class'   => '',
            'name'              => '',
            'checked'           => null,
            'default'           => 'on',
            'label_on'          => _x('Oui', 'tiFyCoreFieldsSwitcher', 'tify' ),
            'label_off'         => _x('Non', 'tiFyCoreFieldsSwitcher', 'tify' ),
            'value_on'          => 'on',
            'value_off'         => 'off'
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);

?><?php $field->before(); ?>
<div id="<?php echo $field->getAttr('container_id');?>" class="tiFyCoreFields-Switcher<?php echo $field->getAttr('container_class');?>">
    <div class="tiFyCoreFields-SwitcherWrapper">
        <?php
            Fields::Radio(
                [
                    'after' => Fields::Label(
                        [
                            'content' => $field->getAttr('label_on'),
                            'attrs' => [
                                'for' => $field->getId() . '--on',
                                'class' => 'tiFyCoreFields-SwitcherLabel tiFyCoreFields-SwitcherLabel--on'
                            ]
                        ],
                        false
                    ),
                    'attrs' => [
                        'id'    => $field->getId() . '--on',
                        'class' => 'tiFyCoreFields-SwitcherRadio tiFyCoreFields-SwitcherRadio--on',
                        'name'  => $field->getHtmlAttr('name'),
                        'value' => $field->getAttr('value_on'),
                        'autocomplete' => 'off'
                    ],
                    'checked' => $field->getAttr('checked')
                ],
                true
            );
        ?>
        <?php
            Fields::Radio(
                [
                    'after' => Fields::Label(
                        [
                            'content' => $field->getAttr('label_off'),
                            'attrs' => [
                                'for' => $field->getId() . '--off',
                                'class' => 'tiFyCoreFields-SwitcherLabel tiFyCoreFields-SwitcherLabel--off'
                            ]
                        ],
                        false
                    ),
                    'attrs' => [
                        'id'    => $field->getId() . '--off',
                        'class' => 'tiFyCoreFields-SwitcherRadio tiFyCoreFields-SwitcherRadio--off',
                        'name'  => $field->getHtmlAttr('name'),
                        'value' => $field->getAttr('value_off'),
                        'autocomplete' => 'off'
                    ],
                    'checked' => $field->getAttr('checked')
                ],
                true
            );
        ?>
        <span class="tiFyCoreFields-SwitcherHandler"></span>
    </div>
</div>
<?php $field->after(); ?>
<?php
    }
}