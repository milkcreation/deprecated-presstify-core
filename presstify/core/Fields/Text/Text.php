<?php
namespace tiFy\Core\Fields\Text;

class Text extends \tiFy\Core\Fields\Factory
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
            'id'                => 'tiFyCoreFieldsText-' . static::$Instance,
            'container_id'      => 'tiFyCoreFieldsText--' . static::$Instance,
            'container_class'   => '',
            'html_attrs'        => [],
            'name'              => '',
            'value'             => ''
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);
?>
<input type="text" name="<?php echo $Field->getName();?>" value="<?php echo $Field->getValue();?>" />
<?php
    }
}