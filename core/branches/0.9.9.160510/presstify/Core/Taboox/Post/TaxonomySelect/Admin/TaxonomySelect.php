<?php
namespace tiFy\Core\Taboox\Post\TaxonomySelect\Admin;

use tiFy\Core\Taboox\Admin;

class TaxonomySelect extends Admin
{
	private static $Instance;
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = 	wp_parse_args(
			$this->args,
			array(
				'id'				=> 'tify_taboox_taxonomy_select-'. ++self::$Instance,	
				'selector' 			=> 'checkbox',
				'taxonomy' 			=> '',
				'show_option_none'	=> __( 'Aucun', 'tify' ),
				'col'				=> 4
			)
		);
	}
	
	/* = MISE EN FILE DE SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'taboox-taxonomy_select-admin', $this->Url .'/admin.css' );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		extract( $this->args );
		
		$_selects = get_terms( $taxonomy, array( 'hide_empty' => false, 'orderby'=> 'title', 'order'=>'ASC' ) );
		
		if( is_wp_error( $_selects ) )
			return;
		
		$_itemscount 		= count( $_selects );
		$_itemscount 		+= $show_option_none ? 1 : 0;
		$_itemsbycol		= ceil( $_itemscount/$col );
		$_currentcol		= 1;
		$_currentcount		= 1
	?>
		<div id="<?php echo $id;?>" class="tify_taboox_taxonomy_select tify_taboox_taxonomy_select-<?php echo $taxonomy;?>">
			<ul class="cols-<?php echo $col;?>">
				<li class="col-<?php echo $_currentcol;?>">
					<?php if( ! $show_option_none ) :?>
					<input type="hidden" name="tax_input[<?php echo $taxonomy;?>][]" data-term_id="0" value="" <?php checked( ! get_the_terms( $post->ID, $taxonomy ) );?> />
					<?php endif;?>
					
					<ul>
					
					<?php if( $show_option_none ) : ?>
						<li>
							<label>
								<input type="<?php echo $selector;?>" name="tax_input[<?php echo $taxonomy;?>][]" data-term_id="0" value="" <?php checked( ! get_the_terms( $post->ID, $taxonomy ) );?> />
								<?php echo $show_option_none;?>
							</label>
						</li>
					<?php $_currentcount++; endif;?>	
							
					<?php foreach( (array) $_selects as $key => $select ) :?>						
						<li>
							<label>
								<input type="<?php echo $selector;?>" name="tax_input[<?php echo $taxonomy;?>][]" data-term_id="<?php echo $select->term_id;?>" value="<?php echo $select->name;?>" <?php checked( has_term( $select->term_id, $taxonomy, $post->ID ) );?>>
								<?php echo $select->name;?>
							</label>
						</li>
						<?php if( $_currentcount++%$_itemsbycol === 0 ) : ?>
							</ul></li><li class="col-<?php echo ++$_currentcol;?>"><ul>
						<?php endif;?>	
					<?php endforeach;?>
					</ul>
				</li>
			</ul>
		</div>			
	<?php
	}
}	