<?php
namespace tiFy\Core\Fields\Touchtime;

use tiFy\Core\Fields\Fields;

class Touchtime extends \tiFy\Core\Fields\Factory
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
        \wp_register_style('tiFyCoreFieldsTouchtime', self::tFyAppAssetsUrl('Touchtime.css', get_class()), [], 171112);
        \wp_register_script('tiFyCoreFieldsTouchtime', self::tFyAppAssetsUrl('Touchtime.js', get_class()), ['jquery', 'moment'], 171112, true);
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public static function enqueue_scripts()
    {
        \wp_enqueue_style('tiFyCoreFieldsTouchtime');
        \wp_enqueue_script('tiFyCoreFieldsTouchtime');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @return string
     */
    public static function display($id = null, $args = [])
    {
        static::$Instance++;

        $defaults = [
            'container_id'    => 'tiFyCoreFields-touchtime--' . self::$Instance,
            'container_class' => '',
            'name'            => '',
            'value'           => '',

            // Permettre la saisie de date type 0000-00-00 00:00:00
            'none_allowed'    => false,
            // Format d'enregistrement de la date (default) datetime - ex : 1970-01-01 00:00:00 | date - ex : 1970-00-00 | time - ex 00:00:00
            'format'          => 'datetime',

            // Liste des champs de saisie et attributs de configuration - Tableau indexés des champs de saisie (day|month|year|hour|minute|second) ou tableau associatif
            'fields'          => [],
        ];
        $args = \wp_parse_args($args, $defaults);

        /**
         * @var string $id Identifiant de qualification du champ
         * @var string $container_id Id HTML du conteneur
         * @var string $container_class Classe HTML du conteneur
         * @var string $name Nom d'enregistrement de la valeur
         * @var string $value Valeur initiale
         * @var bool $none_allowed Permettre la saisie de date nulle (0000-00-00 00:00:00)
         * @var string $format Format d'enregistrement de la date -> datetime (par defaut) - ex : 1970-01-01 00:00:00 | date - ex : 1970-00-00 | time - ex 00:00:00
         * @var array $fields Attributs de configuration des champs de saisie
         */
        extract($args);

        // Traitement des champs de saisie
        if (!$fields) :
            switch ($format) :
                default :
                case 'datetime' :
                    $fields = ['year', 'month', 'day', 'hour', 'minute', 'second'];
                    break;
                case 'date' :
                    $fields = ['year', 'month', 'day'];
                    break;
                case 'time' :
                    $fields = ['year', 'month', 'day'];
                    break;
            endswitch;
        endif;

        // Traitement de la valeur
        $date = new \DateTime($value);
        $Y = $date->format('Y');
        $m = $date->format('m');
        $d = $date->format('d');
        $H = $date->format('H');
        $i = $date->format('i');
        $s = $date->format('s');

        // Traitement des arguments des champs de saisie
        $year = '';
        $month = '';
        $day = '';
        $hour = '';
        $minute = '';
        $second = '';

        foreach ($fields as $field_name => $field_attrs) :
            if (is_int($field_name)) :
                $field_name = (string)$field_attrs;
                $field_attrs = [];
            endif;

            switch ($field_name) :
                case 'year' :
                    $defaults = [
                        'container_id'    => "{$id}-handler-yyyy",
                        'container_class' => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--year',
                        'html_attrs'      => [
                            'size'         => 4,
                            'maxlength'    => 4,
                            'min'          => 0,
                            'autocomplete' => 'off'
                        ],
                        'value'           => zeroise($Y, 4)
                    ];
                    $field_attrs = \wp_parse_args($field_attrs, $defaults);

                    $year = Fields::Number($field_attrs, false);
                    break;

                case 'month' :
                    global $wp_locale;

                    $field_options = [];
                    if ($none_allowed) :
                        $field_options[0] = __('Aucun', 'tify');
                    endif;
                    for ($n = 1; $n <= 12; $n++) :
                        $field_options[zeroise($n, 2)] = $wp_locale->get_month_abbrev($wp_locale->get_month($n));
                    endfor;
                    $defaults = [
                        'container_id'    => "{$id}-handler-mm",
                        'container_class' => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--month',
                        'html_attrs'      => [
                            'autocomplete' => 'off'
                        ],
                        'selected'        => $m,
                        'options'         => $field_options,
                        'value'           => zeroise($Y, 4)
                    ];
                    $field_attrs = \wp_parse_args($field_attrs, $defaults);

                    $month = Fields::Select($field_attrs, false);
                    break;

                case 'day' :
                    $defaults = [
                        'container_id'    => "{$id}-handler-dd",
                        'container_class' => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--day',
                        'html_attrs'      => [
                            'size'         => 2,
                            'maxlength'    => 2,
                            'min'          => $none_allowed ? 0 : 1,
                            'max'          => 31,
                            'autocomplete' => 'off'
                        ],
                        'value'           => zeroise($d, 2)
                    ];
                    $field_attrs = \wp_parse_args($field_attrs, $defaults);

                    $day = Fields::Number($field_attrs, false);
                    break;

                case 'hour' :
                    $defaults = [
                        'container_id'    => "{$id}-handler-hh",
                        'container_class' => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--hour',
                        'html_attrs'      => [
                            'size'         => 2,
                            'maxlength'    => 2,
                            'min'          => 0,
                            'max'          => 23,
                            'autocomplete' => 'off'
                        ],
                        'value'           => zeroise($H, 2)
                    ];
                    $field_attrs = \wp_parse_args($field_attrs, $defaults);

                    $hour = Fields::Number($field_attrs, false);
                    break;

                case 'minute' :
                    $defaults = [
                        'container_id'    => "{$id}-handler-ii",
                        'container_class' => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--minute',
                        'html_attrs'      => [
                            'size'         => 2,
                            'maxlength'    => 2,
                            'min'          => 0,
                            'max'          => 59,
                            'autocomplete' => 'off'
                        ],
                        'value'           => zeroise($i, 2)
                    ];
                    $field_attrs = \wp_parse_args($field_attrs, $defaults);

                    $minute = Fields::Number($field_attrs, false);
                    break;

                case 'second' :
                    $defaults = [
                        'container_id'    => "{$id}-handler-ss",
                        'container_class' => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--second',
                        'html_attrs'      => [
                            'size'         => 2,
                            'maxlength'    => 2,
                            'min'          => 0,
                            'max'          => 59,
                            'autocomplete' => 'off'
                        ],
                        'value'           => zeroise($s, 2)
                    ];
                    $field_attrs = \wp_parse_args($field_attrs, $defaults);

                    $second = Fields::Number($field_attrs, false);
                    break;
            endswitch;
        endforeach;

        // Sortie
        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyCoreFields-touchtime" . ($container_class ? ' ' . $container_class : '') . "\">\n";
        $output .= sprintf('%3$s %2$s %1$s %4$s %5$s %6$s', $year, $month, $day, $hour, $minute, $second);
        $output .= Fields::Hidden(
            [
                'attrs' => [
                    'id'           => 'tiFyCoreFields-touchtimeInput--' . self::$Instance,
                    'class'        => 'tiFyCoreFields-touchtimeField tiFyCoreFields-touchtimeField--value',
                    'name'         => $name,
                    'value'        => $value,
                    'autocomplete' => 'off',
                    'readonly'     => 'readonly'
                ]
            ],
            false
        );
        $output .= "</div>\n";

        echo $output;
    }
}