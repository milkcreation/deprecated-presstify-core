<?php
namespace tiFy\Core\Fields\Spinner;

class Spinner extends \tiFy\Core\Fields\Factory
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
        \wp_register_style('tiFyCoreFieldsSpinner', self::tFyAppAssetsUrl('Spinner.css', get_class()), ['dashicons'], 171019);
        \wp_register_script('tiFyCoreFieldsSpinner', self::tFyAppAssetsUrl('Spinner.js', get_class()), ['jquery-ui-spinner'], 171019, true);
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyCoreFieldsSpinner');
        \wp_enqueue_script('tiFyCoreFieldsSpinner');
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
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @param array $attrs {
     *          Liste des attributs de balise
     *
     *      }
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
            'attrs'      => [
                'id'            => 'tiFyCoreFields-Spinner-' . self::$Instance
            ],
            'options'   => []
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);
        $field->setHtmlAttr(
            'data-options',
            \wp_parse_args(
                $args['options'],
                [
                    'icons' => [
                        'down' => 'dashicons dashicons-arrow-down-alt2',
                        'up' => 'dashicons dashicons-arrow-up-alt2'
                    ]
                ]
            )
        );

        ?><div class="tiFyCoreFields-SpinnerContainer"><?php $field->before(); ?><input <?php echo $field->htmlAttrs(); ?> /><?php $field->after(); ?></div><?php
    }
}