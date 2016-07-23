<?php
/**
 * Metaboxe de saisie
 */ 
function mkpbx_taxonomy_select_render( $post, $args = array() ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'selector' => 'dropdown',
			'taxonomy' => '',
			'show_option_none'	=> __( 'Aucun', 'mktzr' )
		)
	);
	extract( $args );
	$selects = get_terms( $taxonomy, array( 'hide_empty' => false, 'orderby'=> 'title', 'order'=>'ASC' ) );
	
	if( is_wp_error( $selects ) )
		return;
?>
	<div class="taxonomy-postbox taxonomy-<?php echo $taxonomy;?>-postbox">
	<?php switch( $selector ) :
			case 'dropdown' : ?>
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
		<?php break;?>
		<?php case 'checkbox' : ?>				
				<?php if( $show_option_none ) :?>
				<ul>
					<li>
						<label>
							<input type="checkbox" name="tax_input[<?php echo $taxonomy;?>][]" value="" <?php checked( ! get_the_terms( $post->ID, $taxonomy ) );?> />
							<?php echo $show_option_none;?>
						</label>
					</li>
				<?php else :?>
					<input type="hidden" name="tax_input[<?php echo $taxonomy;?>][]" value="" <?php checked( ! get_the_terms( $post->ID, $taxonomy ) );?> />
				<ul>
				<?php endif;?>
				
				<?php foreach( (array) $selects as $key => $select ) :?>
					<li>
						<label>
							<input type="checkbox" name="tax_input[<?php echo $taxonomy;?>][]" value="<?php echo $select->name;?>" <?php checked( has_term( $select->term_id, $taxonomy, $post->ID ) );?>>
							<?php echo $select->name;?>
						</label>
					</li>	
				<?php endforeach;?>
				</ul>	
		<?php break;?>
		<?php case 'radio' : ?>				
				<?php if( $show_option_none ) :?>
				<ul>
					<li>
						<label>
							<input type="radio" name="tax_input[<?php echo $taxonomy;?>][]" value="" <?php checked( ! get_the_terms( $post->ID, $taxonomy ) );?> />
							<?php echo $show_option_none;?>
						</label>
					</li>
				<?php else :?>
					<input type="hidden" name="tax_input[<?php echo $taxonomy;?>][]" value="" <?php checked( ! get_the_terms( $post->ID, $taxonomy ) );?> />
				<ul>
				<?php endif;?>

				<?php foreach( (array) $selects as $key => $select ) :?>
					<li>
						<label>
							<input type="radio" name="tax_input[<?php echo $taxonomy;?>][]" value="<?php echo $select->name;?>" <?php checked( has_term( $select->term_id, $taxonomy, $post->ID ) );?>>
							<?php echo $select->name;?>
						</label>
					</li>	
				<?php endforeach;?>
				</ul>	
		<?php break;?>	
	<?php endswitch;?>
	</div>			
<?php	
}		