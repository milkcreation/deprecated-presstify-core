<?php
/**
 * Bouton de bascule on/off
 * @see http://php.quicoto.com/toggle-switches-using-input-radio-and-css3/
 * @see https://github.com/ghinda/css-toggle-switch
 * @see http://bashooka.com/coding/pure-css-toggle-switches/
 * @see https://proto.io/freebies/onoff/
 */ 

namespace tiFy\Core\Fields\Switcher;

class Switcher extends \tiFy\Core\Fields\Factory
{
    /**
     * Instance
     * @var int
     */
    protected static $Instance = 0;

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
        wp_register_style('tiFyCoreFieldsSwitcher', self::tFyAppAssetsUrl('Switcher.css', get_class()), array( ), '150310');
        wp_register_script('tiFyCoreFieldsSwitcher', self::tFyAppAssetsUrl('Switcher.js', get_class()), array( 'jquery' ), 170724);
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
     * @return string
     */
    public static function display($attrs = [])
    {
        self::$Instance++;

        $defaults = [
            'id'                => 'tiFyCoreFieldsSwitcher-' . self::$Instance,
            'container_id'      => 'tiFyCoreFieldsSwitcher--' . self::$Instance,
            'container_class'   => '',
            'name'              => '',
            'checked'           => null,
            'default'           => 'on',
            'label_on'          => _x( 'Oui', 'tiFyCoreFieldsSwitcher', 'tify' ),
            'label_off'         => _x( 'Non', 'tiFyCoreFieldsSwitcher', 'tify' ),
            'value_on'          => 'on',
            'value_off'         => 'off'
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        if(is_null($attrs['checked'])) :
            $attrs['checked'] = $attrs['default'];
        endif;

        $Field = new static($attrs);
?>
<div id="<?php echo $Field->getContainerId();?>" class="tiFyCoreFieldsSwitcher<?php echo $Field->getContainerClass();?>">
    <div class="tiFyCoreFieldsSwitcher-wrapper">
        <input type="radio" id="<?php echo $Field->getId(); ?>-on" class="tiFyCoreFieldsSwitcher-input tiFyCoreFieldsSwitcher-input--on" name="<?php echo $Field->getName(); ?>" value="<?php echo $Field->getAttr('value_on'); ?>" autocomplete="off" <?php checked(($Field->getAttr('value_on') === $Field->getAttr('checked')), true, true); ?>>
        <label for="<?php echo $Field->getId(); ?>-on" class="tiFyCoreFieldsSwitcher-label tiFyCoreFieldsSwitcher-label--on"><?php echo $Field->getAttr('label_on'); ?></label>
        <input type="radio" id="<?php echo $Field->getId(); ?>-off" class="tiFyCoreFieldsSwitcher-input tiFyCoreFieldsSwitcher-input--off" name="<?php echo $Field->getName(); ?>" value="<?php echo $Field->getAttr('value_off'); ?>" autocomplete="off" <?php checked(($Field->getAttr('value_off') === $Field->getAttr('checked')), true, true); ?>>
        <label for="<?php echo $Field->getId(); ?>-off" class="tiFyCoreFieldsSwitcher-label tiFyCoreFieldsSwitcher-label--off"><?php echo $Field->getAttr('label_off'); ?></label>
        <span class="tiFyCoreFieldsSwitcher-handler"></span>
    </div>
</div>
<?php
    }
}