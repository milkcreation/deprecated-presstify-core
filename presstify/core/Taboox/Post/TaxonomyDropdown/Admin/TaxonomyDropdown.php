<?php
namespace tiFy\Core\Taboox\Post\TaxonomyDropdown\Admin;

class TaxonomyDropdown extends \tiFy\Core\Taboox\Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = 	wp_parse_args(
			$this->args,
			array(
				'taxonomy' 			=> '',
				'show_option_none'	=> __( 'Aucun', 'tify' )
			)
		);
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		extract( $this->args );
		
		$selects = get_terms( $taxonomy, array( 'hide_empty' => false, 'orderby'=> 'title', 'order'=>'ASC' ) );
		
		if( is_wp_error( $selects ) )
			return;
	?>
		<div class="taxonomy-postbox taxonomy-<?php echo $taxonomy;?>-postbox">
			<select name="tax_input[<?php echo $taxonomy;?>][]" autocomplete="off">
			<?php if( $show_option_none ) :?>
				<option value="" <?php selected( ! get_the_terms( $post->ID, $taxonomy ) );?>>
					<?php echo $show_option_none;?>
				</option>
			<?php endif;?>
			<?php foreach( (array) $selects as $key => $select ) :?>
				<option value="<?php echo $select->name;?>" <?php selected( has_term( $select->term_id, $taxonomy, $post->ID ) );?>>
					<?php echo $select->name;?>
				</option>	
			<?php endforeach;?>
			</select>	
		</div>			
	<?php
	}
}	