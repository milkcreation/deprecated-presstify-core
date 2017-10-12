<?php
namespace tiFy\Components\HookArchive\Taboox\Option\HookSelector\Admin;

use tiFy\Core\Taboox\Admin;

class HookSelector extends Admin
{	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, "tify_hook_". $this->args['obj'] ."_". $this->args['archive'] );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		\wp_enqueue_style( 'Hook_Taboox_Option_Selector_Admin', self::tFyAppUrl() . '/HookSelector.css', array( 'tify_control-switch' ), '160315' );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		$output  = "";
		
		$output .= "<div class=\"tify_Hook_TabooxHookSelector\">";
		switch( $this->args['obj'] ) : 
			case 'post_type' :
				foreach( (array) $this->args['hooks'] as $n => $hook ) : 
					if( ! $hook['edit'] ) 
						continue;
					$output .= 	"<table class=\"form-table\">";
					$output .=		"<tbody>";
					$output .= 			"<tr>";
					$output .=				"<th role=\"scope\">". __( 'Afficher sur :', 'tify' )."</th>";
					$output .=				"<td>";
					$output .= 	wp_dropdown_pages(
						array(
							'name' 				=> "tify_hook_". $this->args['obj'] ."_". $this->args['archive'] ."[$n][id]",
							'post_type' 		=> $hook['post_type'],
							'selected' 			=> $hook['id'],
							'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ),
							'sort_column'  		=> 'menu_order',
							'echo'				=> false
						)
					);
					$output .= 				"</td>";
					$output .=			"</tr>";
					if( false === (bool) $this->args['options']['rewrite'] ) :
						$output .= "<input type=\"hidden\" name=\"tify_hook_". $this->args['obj'] ."_". $this->args['archive'] ."[$n][permalink]\" value=\"0\">";
					else :
						$output .= 			"<tr>";
						$output .=				"<th role=\"scope\">". __( 'Réécriture des permaliens', 'tify' )."</th>";
						$output .=				"<td>";
						$output .=	tify_control_switch( 
							array( 
								'name' 				=> "tify_hook_". $this->args['obj'] ."_". $this->args['archive'] ."[$n][permalink]",
								'value_on'			=> 1,
								'value_off'			=> 0,
								'checked' 			=> (int) $hook['permalink'],
								'echo'				=> false
							) 
						);			
						$output .= 				"</td>";
						$output .=			"</tr>";
					endif;
					$output .=		"</tbody>";
					$output .=	"</table>";		
				endforeach;
				break;
			case 'taxonomy' :
				$terms = get_terms( 
					$this->args['archive'], 
					array(
    					'get' => 'all'
					)
				);

				$args = $this->args;

				$args['exists'] = array();
				
				foreach( (array) $this->args['hooks'] as $n => $hook ) :
					if( ! $hook['term'] )
						continue;
					$args['exists'][ $hook['term'] ] = $hook;
				endforeach;						
								
				$walker = new Walker_Taxonomy;
				$output .= "<ul>";
				$output .= call_user_func_array( array( $walker, 'walk' ), array( $terms, 0, $args ) );
				$output .= "</ul>";
				
				break;
		endswitch;
		$output .= "</div>";
		
		echo $output;
	}
	
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}