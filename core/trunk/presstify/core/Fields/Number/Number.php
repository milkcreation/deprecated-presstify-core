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
            'id'                => 'tiFyCoreFields-Number-' . static::$Instance,
            'container_id'      => 'tiFyCoreFields-Number--' . static::$Instance,
            'container_class'   => '',
            'html_attrs'        => [],
            'name'              => '',
            'value'             => ''
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);
?>
<input type="number" name="<?php echo $Field->getName();?>" value="<?php echo $Field->getValue();?>" class="tiFyCoreFields-Number<?php echo $Field->getContainerClass();?>" <?php echo $Field->getHtmlAttrs(); ?>/>
<?php
    }
}