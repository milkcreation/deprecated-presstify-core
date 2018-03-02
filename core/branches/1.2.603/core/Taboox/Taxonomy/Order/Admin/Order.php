<?php
namespace tiFy\Core\Taboox\Taxonomy\Order\Admin;

use tiFy\Core\Taboox\Admin;

class Order extends Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		tify_meta_term_register( $current_screen->taxonomy, '_order', true );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $term, $taxonomy )
	{
	?>
		<table class="form-table">
			<tbody>
				<tr>
					<th>
						<label><?php _e( 'Choix de l\'ordre', 'Theme');?></label>
						<em style="display:block;color:#999;font-size:11px;font-weight:normal;"><?php _e( '(-1 pour masquer l\'élément)', 'Theme');?></em>
					</th>
					<td><input type="number" min="-1" value="<?php echo ( $order = (int) get_term_meta( $term->term_id, '_order', true ) ) ? $order : 0 ;?>" name="tify_meta_term[_order]" /></td>
				</tr>
			</tbody>
		</table>
	<?php 
	}
}