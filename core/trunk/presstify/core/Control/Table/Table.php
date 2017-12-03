<?php
/**
 * @name Table
 * @desc Controleur d'affichage de Tableau HTML Responsive
 * @package presstiFy
 * @namespace tiFy\Core\Control\Table
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Table;

/**
 * @Overrideable \App\Core\Control\Table\Table
 *
 * <?php
 * namespace \App\Core\Control\Table
 *
 * class Table extends \tiFy\Core\Control\Table\Table
 * {
 *
 * }
 */

class Table extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'table';

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {
        \wp_register_style(
            'tify_control-table',
            self::tFyAppAssetsUrl('Table.css', get_class()),
            [],
            160714
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public static function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-table');
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
    public static function display($attrs = [], $echo = true)
    {
        // Incrémentation du nombre d'instance
        self::$Instance++;

        // Traitement des attributs de configuration
        $defaults = [
            'columns' => [],
            'datas'   => [],
            'none'    => __('Aucun élément à afficher dans le tableau', 'tify')
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        $n = count($columns);

        $output = "";
        $output .= "<div class=\"tiFyTable\">\n";
        $output .= "\t<div class=\"tiFyTableHead\">\n";
        $output .= "\t\t<div class=\"tiFyTableHeadTr tiFyTableTr\">\n";
        foreach ($columns as $column => $label) :
            $output .= "\t\t\t<div class=\"tiFyTableCell{$n} tiFyTableHeadTh tiFyTableHeadTh--{$column} tiFyTableTh tiFyTableTh--{$column}\">{$label}</div>\n";
        endforeach;
        $output .= "\t\t</div>\n";
        $output .= "\t</div>\n";
        reset($columns);

        $i = 0;
        $output .= "\t<div class=\"tiFyTableBody\">\n";
        if ($datas) :
            foreach ($datas as $row => $dr) :
                $output .= "\t\t<div class=\"tiFyTableBodyTr tiFyTableBodyTr--{$row} tiFyTableTr tiFyTableTr-" . (($i++ % 2 === 0) ? 'even' : 'odd') . "\">\n";
                foreach ($columns as $column => $label) :
                    $output .= "\t\t\t<div class=\"tiFyTableCell{$n} tiFyTableBodyTd tiFyTableBodyTd--{$column} tiFyTableTd\">{$dr[$column]}</div>\n";
                endforeach;
                $output .= "\t\t</div>\n";
            endforeach;
        else :
            $output .= "\t\t<div class=\"tiFyTableBodyTr tiFyTableBodyTr--empty tiFyTableTr\">\n";
            $output .= "\t\t\t<div class=\"tiFyTableCell1 tiFyTableBodyTd tiFyTableBodyTd--empty tiFyTableTd\">{$none}</div>\n";
            $output .= "\t\t</div>\n";
        endif;
        $output .= "\t</div>\n";
        reset($columns);

        $output .= "\t<div class=\"tiFyTableFoot\">\n";
        $output .= "\t\t<div class=\"tiFyTableFootTr tiFyTableTr\">\n";
        foreach ($columns as $column => $label) :
            $output .= "\t\t\t<div class=\"tiFyTableCell{$n} tiFyTableFootTh tiFyTableFootTh--{$column} tiFyTableTh tiFyTableTh--{$column}\">{$label}</div>\n";
        endforeach;
        $output .= "\t\t</div>\n";
        $output .= "\t</div>\n";

        $output .= "</div>\n";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}