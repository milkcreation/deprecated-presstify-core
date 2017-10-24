<?php
namespace tiFy\Core\Fields\Spinner;

class Spinner extends \tiFy\Core\Fields\Factory
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
        'autocomplete',
        'autofocus',
        'form',
        'formaction',
        'formenctype',
        'formmethod',
        'formnovalidate',
        'formtarget',
        'width',
        'height',
        'inputmode',
        'list',
        'multiple',
        'readonly',
        'disabled',
        'max',
        'maxlength',
        'min',
        'pattern',
        'required',
        'placeholder',
        'size',
        'step'
    ];

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
        wp_register_style('tiFyCoreFieldsSpinner', self::tFyAppAssetsUrl('Spinner.css', get_class()), ['dashicons'], 171019);
        wp_register_script('tiFyCoreFieldsSpinner', self::tFyAppAssetsUrl('Spinner.js', get_class()), ['jquery-ui-spinner'], 171019, true);
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tiFyCoreFieldsSpinner');
        wp_enqueue_script('tiFyCoreFieldsSpinner');
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
            'id'              => 'tiFyCoreFieldsSpinnerInput-' . self::$Instance,
            'container_id'    => 'tiFyCoreFieldsSpinner-' . self::$Instance,
            'container_class' => '',
            'input_class'     => '',
            'label'           => true,
            'label_class'     => '',
            'label_text'      => __('Sélectionner une valeur', 'tify'),
            'html_attrs'      => [],
            'name'            => '',
            'value'           => 1
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $Field = new static($attrs);
        ?>
        <div id="<?php echo $Field->getContainerId(); ?>"
             class="tiFyCoreFieldsSpinner <?php echo $Field->getContainerClass(); ?>">
            <input id="<?php echo $Field->getId(); ?>"
                   class="tiFyCoreFieldsSpinner-input <?php echo $Field->getAttr('input_class'); ?>"
                   name="<?php echo $Field->getName(); ?>" value="<?php echo $Field->getValue(); ?>"
                <?php echo $Field->getHtmlAttrs(); ?>>
            <?php if ($attrs['label']) : ?>
                <label for="<?php echo $Field->getId(); ?>"
                       class="tiFyCoreFieldsSpinner-label <?php echo $Field->getAttr('label_class'); ?>"><?php echo $Field->getAttr('label_text'); ?></label>
            <?php endif; ?>
        </div>
        <?php
    }
}