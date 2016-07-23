<?php
namespace tiFy\Components\HookArchive\Taboox\Option\HookSelector\Admin;

use tiFy\Core\Taboox\Admin;

class HookSelector extends Admin
{	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, "tify_hook_". $this->args['type'] ."_". $this->args['archive'] );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		\wp_enqueue_style( 'Hook_Taboox_Option_Selector_Admin', $this->Url .'/HookSelector.css', array( 'tify_control-switch' ), '160315' );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		$output  = "";
		
		$output .= "<div class=\"tify_Hook_TabooxHookSelector\">";
		switch( $this->args['type'] ) : 
			case 'post' :
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
							'name' 				=> "tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][id]",
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
						$output .= "<input type=\"hidden\" name=\"tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][permalink]\" value=\"0\">";
					else :
						$output .= 			"<tr>";
						$output .=				"<th role=\"scope\">". __( 'Réécriture des permaliens', 'tify' )."</th>";
						$output .=				"<td>";
						$output .=	tify_control_switch( 
							array( 
								'name' 				=> "tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][permalink]",
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
    					'hide_empty' => false,
					)
				);
				
				$exists = array();
				foreach( (array) $this->args['hooks'] as $n => $hook ) :
					if( ! $hook['term'] )
						continue;
					$exists[ $hook['term'] ] = $hook;
				endforeach;				

				foreach( (array) $terms as $n => $term ) : 
					if( false === ( ( isset( $exists[$term->term_id] ) ) ? (bool) $exists[$term->term_id]['edit'] : (bool) $this->args['options']['edit'] ) )
						continue;
					$output .= "<input type=\"hidden\" name=\"tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][term]\" value=\"{$term->term_id}\" />";
					$output .= 	"<table class=\"form-table\">";
					$output .=		"<tbody>";
					$output .= 			"<tr>";
					$output .=				"<th role=\"scope\">". sprintf( __( 'Afficher "%s" sur :', 'tify' ), $term->name ) ."</th>";
					$output .=				"<td>";
					$output .= 	wp_dropdown_pages(
						array(
							'name' 				=> "tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][id]",
							'post_type' 		=> 'page',
							'selected' 			=> isset( $exists[$term->term_id] ) ? $exists[$term->term_id]['id'] : 0,
							'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ),
							'sort_column'  		=> 'menu_order',
							'echo'				=> false
						)
					);
					$output .= 				"</td>";
					$output .=			"</tr>";

					if( false === (bool) $this->args['options']['rewrite'] ) :
						$output .= "<input type=\"hidden\" name=\"tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][permalink]\" value=\"0\">";
					elseif( ! $post_types = get_taxonomy( $this->args['archive'] )->object_type ) :					
					else :						
						$output .= 			"<tr>";
						$output .=				"<th role=\"scope\">". __( 'Réécriture des permaliens', 'tify' )."</th>";
						$output .=				"<td>";
						$has_post_type = false;
						foreach( (array) $post_types as $post_type ) :
							if( ! $post_type_object = get_post_type_object( $post_type ) )
								continue;
							if( ! $has_post_type ) :
								$output .= "<ul>";
							endif;
							$checked = isset( $exists[$term->term_id] ) ? $exists[$term->term_id]['permalink'] : $this->args['options']['permalink'];
							if( is_array( $checked ) ) :
								$checked = in_array( $post_type, $checked );
							endif;									
							
							$output .= "<li><label><input type=\"checkbox\" name=\"tify_hook_". $this->args['type'] ."_". $this->args['archive'] ."[$n][permalink][]\" value=\"{$post_type}\" ". checked( (bool) $checked, true, false )." autocomplete=\"off\">". $post_type_object->label ."</label></li>";
						endforeach;
						if( $has_post_type ) :
							$output .= "</ul>";
						endif;
						$output .= 				"</td>";
						$output .=			"</tr>";
					endif;
					$output .=		"</tbody>";
					$output .=	"</table>";		
				endforeach;
				break;
		endswitch;
		$output .= "</div>";
		
		echo $output;
	}
}