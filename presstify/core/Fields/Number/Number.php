<?php
namespace tiFy\Core\Fields\Number;

class Number extends \tiFy\Core\Fields\Factory
{
    /**
     * Instance
     * @var int
     */
    protected static $Instance = 0;

    /**
     * Liste des attributs HTML autorisés
     * @var array
     */
    protected $AllowedHtmlAttrs = [
        'readonly',
        'disabled',
        'max',
        'maxlength',
        'min',
        'pattern',
        'readonly',
        'required',
        'size',
        'step'
    ];

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
            'id'                => 'tiFyCoreFieldsInputText-' . static::$Instance,
            'container_id'      => 'tiFyCoreFieldsInputText--' . static::$Instance,
            'container_class'   => '',
            'html_attrs'        => [],
            'name'              => '',
            'value'             => ''
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);
?>
<input type="number" name="<?php echo $Field->getName();?>" value="<?php echo $Field->getValue();?>" <?php echo $Field->getHtmlAttrs(); ?>/>
<?php
    }
}