<?php 
namespace tiFy\Core\Control\Table;

class Table extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'table';
	
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
	    wp_register_style( 'tify_control-table', self::tFyAppAssetsUrl('Table.css', get_class()), array(), 160714 );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{	
		wp_enqueue_style( 'tify_control-table' );
	}	
	
	/* = Affichage du controleur = */
	public static function display( $args = array(), $echo = true )
	{
		$defaults = array(
			'columns'	=> array(),
			'datas'		=> array(),
			'none'		=> __( 'Aucun élément à afficher dans le tableau', 'tify' )
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );	
		
		$n = count($columns);
		
		$output  = "";
		$output .= "<div class=\"tiFyTable\">\n";
		$output .= "\t<div class=\"tiFyTableHead\">\n";
		$output .= "\t\t<div class=\"tiFyTableHeadTr tiFyTableTr\">\n";
		foreach( $columns as $column => $label ) :
			$output .= "\t\t\t<div class=\"tiFyTableCell{$n} tiFyTableHeadTh tiFyTableHeadTh--{$column} tiFyTableTh tiFyTableTh--{$column}\">{$label}</div>\n";	
		endforeach;
		$output .= "\t\t</div>\n";
		$output .= "\t</div>\n";		
		reset( $columns );
		
		$i = 0;
		$output .= "\t<div class=\"tiFyTableBody\">\n";	
		if( $datas ) :
			foreach( $datas as $row => $dr ) :
				$output .= "\t\t<div class=\"tiFyTableBodyTr tiFyTableBodyTr--{$row} tiFyTableTr tiFyTableTr-". ( ( $i++%2 === 0 ) ? 'even' :'odd' ) ."\">\n";
				foreach( $columns as $column => $label ) :
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
		reset( $columns );
		
		$output .= "\t<div class=\"tiFyTableFoot\">\n";
		$output .= "\t\t<div class=\"tiFyTableFootTr tiFyTableTr\">\n";
		foreach( $columns as $column => $label ) :
			$output .= "\t\t\t<div class=\"tiFyTableCell{$n} tiFyTableFootTh tiFyTableFootTh--{$column} tiFyTableTh tiFyTableTh--{$column}\">{$label}</div>\n";	
		endforeach;
		$output .= "\t\t</div>\n";
		$output .= "\t</div>\n";
		
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		
		return $output;
	}
}