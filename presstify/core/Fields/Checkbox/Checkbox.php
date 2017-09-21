<?php
namespace tiFy\Core\Fields\Checkbox;

class Checkbox extends \tiFy\Core\Fields\Factory
{
    /**
     * Instance
     * @var int
     */
    protected static $Instance = 0;

    /**
     * Affichage
     *
     * @param array $attrs
     * @param bool $echo
     *
     * return string
     */
    public static function display($attrs = [])
    {
        ++static::$Instance;

        $defaults = [
            'id'                => 'tiFyCoreFieldsCheckbox-' . static::$Instance,
            'container_id'      => 'tiFyCoreFieldsCheckbox--' . static::$Instance,
            'container_class'   => '',
            'html_attrs'        => [],
            'label'             => '',
            'name'              => '',
            'value'             => '',
            'checked'           => null
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);
?>
<input type="checkbox" name="<?php echo $Field->getName();?>" value="<?php echo $Field->getValue();?>" <?php checked($attrs['checked'] === $attrs['value']);?> id="<?php echo $Field->getContainerId();?>" class="tiFyCoreFieldsCheckbox<?php echo $Field->getContainerClass();?>" <?php echo $Field->getHtmlAttrs();?>/>
<?php if ($attrs['label']) : ?>
<label for="<?php echo $Field->getContainerId();?>"><?php echo $attrs['label'];?></label>
<?php endif;?>
<?php
    }
}