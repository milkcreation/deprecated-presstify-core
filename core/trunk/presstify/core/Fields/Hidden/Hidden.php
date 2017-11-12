<?php
namespace tiFy\Core\Fields\Hidden;

class Hidden extends \tiFy\Core\Fields\Factory
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
            'id'                => 'tiFyCoreFields-Hidden-' . static::$Instance,
            'container_id'      => 'tiFyCoreFields-hidden--' . static::$Instance,
            'container_class'   => '',
            'html_attrs'        => [],
            'name'              => '',
            'value'             => ''
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);
?>
<input type="hidden" name="<?php echo $Field->getName();?>" class="tiFyCoreFields-Text<?php echo $Field->getContainerClass();?>" value="<?php echo $Field->getValue();?>" />
<?php
    }
}