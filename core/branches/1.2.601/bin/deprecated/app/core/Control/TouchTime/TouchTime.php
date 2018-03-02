<?php
/**
 * @name TouchTime
 * @desc Controleur d'affichage de champ de saisie de date et/ou heure
 * @package presstiFy
 * @namespace tiFy\Core\Control\TouchTime
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 *
 * @deprecated \tiFy\Core\Field\TouchTime\TouchTime
 */

namespace tiFy\Core\Control\TouchTime;

/**
 * @Overrideable \App\Core\Control\TouchTime\TouchTime
 *
 * <?php
 * namespace \App\Core\Control\TouchTime
 *
 * class TouchTime extends \tiFy\Core\Control\TouchTime\TouchTime
 * {
 *
 * }
 */

class TouchTime extends \tiFy\Core\Control\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        \wp_register_style(
            'tify_control-touch_time',
            self::tFyAppUrl() . '/TouchTime.min.css',
            [],
            150418
        );
        \wp_register_script(
            'tify_control-touch_time',
            self::tFyAppUrl() . '/TouchTime.min.js',
            ['jquery', 'moment'],
            150418,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-touch_time');
        \wp_enqueue_script('tify_control-touch_time');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        global $wp_locale;

        // Traitement des attributs de configuration
        $defaults = [
            'container_id'    => 'tify_control_touch_time-wrapper-' . $this->getId(),
            'container_class' => '',
            'id'              => 'tify_control_touch_time-' . $this->getId(),
            'name'            => 'tify_control_touch_time-' . $this->getId(),
            'value'           => false,
            'show_none'       => false,
            // Permettre les dates de type 0000-00-00 00:00:00
            'type'            => 'datetime',
            // (default) datetime - ex : 1970-01-01 00:00:00 | date - ex : 1970-00-00 | time - ex 00:00:00

            // Affichage des éléments
            'day'             => true,
            // Permettre le contrôle du jour
            'month'           => true,
            // Permettre le contrôle du mois
            'year'            => true,
            // Permettre le contrôle de l'année
            'hour'            => true,
            // Permettre le contrôle de l'heure
            'minute'          => true,
            // Permettre le contrôle des minutes
            'second'          => true,
            // Permettre le contrôle des secondes
            'time_sep'        => ':',
            // Séparateur de temps

            'handler' => true,

            'debug' => false
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        if (is_bool($handler) && $handler) :
            $handler = ['date', 'time'];
        elseif (empty($handler)) :
            $handler = [];
        else :
            $handler = (array)$handler;
        endif;

        switch ($type) :
            case 'datetime' :
                if (!$value) {
                    $value = current_time('mysql');
                }
                $event_date = ($value && preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',
                        $value)) ? $value : current_time('mysql');
                preg_match('/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $event_date, $matches);
                $Y = (int)$matches[1];
                $m = (int)$matches[2];
                $d = (int)$matches[3];
                $H = (int)$matches[4];
                $i = (int)$matches[5];
                $s = (int)$matches[6];
                break;
            case 'date' :
                if (!$value) {
                    $value = date('Y-m-d', current_time('timestamp'));
                }
                $event_date = ($value && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) ? $value : date('Y-m-d',
                    current_time('timestamp'));
                preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $event_date, $matches);
                $Y = (int)$matches[1];
                $m = (int)$matches[2];
                $d = (int)$matches[3];
                break;
            case 'time' :
                if (!$value) {
                    $value = date('H:i:s', current_time('timestamp'));
                }
                $event_date = ($value && preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $value)) ? $value : date('H:i:s',
                    current_time('timestamp'));
                preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $event_date, $matches);
                $H = (int)$matches[1];
                $i = (int)$matches[2];
                $s = (int)$matches[3];
                break;
        endswitch;

        // Affichage de la date
        switch ($type) :
            case 'datetime' :
            case 'date' :
                /// Affichage de l'année
                $output_year = "<input type=\"number\" id=\"{$id}-handler-yyyy\" class=\"tify_control_touch_time-handler tify_control_touch_time-handler-yyyy\" value=\"" . zeroise($Y,
                        4) . "\" size=\"4\" maxlength=\"4\" autocomplete=\"off\" />\n";
                /// Affichage du mois
                for ($n = 1; $n <= 12; $n++) {
                    $choices[zeroise($n, 2)] = $wp_locale->get_month_abbrev($wp_locale->get_month($n));
                }
                $output_month = "";
                $output_month .= "<select id=\"{$id}-handler-mm\" class=\"tify_control_touch_time-handler tify_control_touch_time-handler-mm\" autocomplete=\"off\" name=\"\">";
                if ($show_none) {
                    $output_month .= "<option value=\"0\" " . selected(!$m, true, false) . "></option>";
                }
                foreach ($choices as $mval => $mlabel) {
                    $output_month .= "<option value=\"{$mval}\" " . selected($m == $mval, true,
                            false) . ">{$mlabel}</option>";
                }
                $output_month .= "</select>";
                /// Affichage du jour
                $output_day = "<input type=\"number\" id=\"{$id}-handler-dd\" class=\"tify_control_touch_time-handler tify_control_touch_time-handler-dd\" value=\"" . zeroise($d,
                        2) . "\" size=\"2\" min=\"" . ($show_none ? 0 : 1) . "\" max=\"31\" maxlength=\"2\" autocomplete=\"off\" />\n";
                break;
        endswitch;

        // Affichage de l'heure
        switch ($type) :
            case 'datetime' :
            case 'time' :
                // Affichage de l'heure
                $output_hour = "<input type=\"" . ($hour ? 'number' : 'hidden') . "\" id=\"{$id}-handler-hh\" class=\"tify_control_touch_time-handler tify_control_touch_time-handler-hh\" value=\"" . zeroise($H,
                        2) . "\"  " . ($hour ? "size=\"2\" maxlength=\"2\" min=\"0\" max=\"23\" autocomplete=\"off\"" : "") . " /> " . (is_string($hour) ? $hour : '') . "\n";

                // Affichage des minutes
                $output_minute = (($minute && $time_sep) ? ' : ' : '') . "<input type=\"" . ($minute ? 'number' : 'hidden') . "\" id=\"{$id}-handler-ii\" class=\"tify_control_touch_time-handler tify_control_touch_time-handler-ii\" value=\"" . zeroise($i,
                        2) . "\" " . ($minute ? "size=\"2\" maxlength=\"2\" min=\"0\" max=\"59\" autocomplete=\"off\"" : "") . " /> " . (is_string($minute) ? $minute : '') . "\n";

                // Affichage des secondes
                $output_second = (($second && $time_sep) ? ' : ' : '') . "<input type=\"" . ($second ? 'number' : 'hidden') . "\" id=\"{$id}-handler-ss\" class=\"tify_control_touch_time-handler tify_control_touch_time-handler-ss\" value=\"" . zeroise($s,
                        2) . "\" " . ($second ? "size=\"2\" maxlength=\"2\" min=\"0\" max=\"59\" autocomplete=\"off\"" : "") . " /> " . (is_string($second) ? $second : '') . "\n";
                break;
        endswitch;

        // Sortie
        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tify_control_touch_time-wrapper" . ($container_class ? ' ' . $container_class : '') . "\" data-tify_control=\"touch_time\">\n";

        /// Réglage de la date
        switch ($type) :
            case 'datetime' :
            case 'date' :
                $output .= "\t<span id=\"{$id}-date\">\n";
                $output .= sprintf(__('%2$s %1$s %3$s', 'tify'), $output_month, $output_day, $output_year);
                if (in_array('date', $handler)) {
                    $output .= "\t\t<i class=\"datepicker-handler dashicons dashicons-calendar-alt\"></i>";
                }
                $output .= "\t</span>";
                break;
        endswitch;

        /// Réglage de l'heure
        switch ($type) :
            case 'datetime' :
            case 'time' :
                $output .= "\t<span id=\"{$id}-time\">\n";
                $output .= sprintf(__('%1$s %2$s %3$s', 'tify'), $output_hour, $output_minute, $output_second);
                if ((!empty($hour) || !empty($minute) || !empty($second)) && in_array('time', $handler)) :
                    $output .= "\t\t<i class=\"timepicker-handler dashicons dashicons-clock\"></i>";
                endif;
                $output .= "\t</span>\n";
                break;
        endswitch;

        $output .= "\t<input type=\"" . ($debug ? 'text' : 'hidden') . "\" value=\"{$value}\" id=\"{$id}\" class=\"tify_control_touch_time-input\" name=\"{$name}\" autocomplete=\"off\" readonly=\"readonly\">\n";
        $output .= "</div>\n";

        echo $output;
    }
}