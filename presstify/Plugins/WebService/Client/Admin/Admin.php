<?php
namespace tiFy\Plugins\WebService\Client\Admin;

use tiFy\Core\View\Admin\AjaxListTable\AjaxListTable as tiFy_AjaxListTable;

class Admin extends tiFy_AjaxListTable
{
	protected $Debug = false;
	
	/** ==  == **/
	public function column_date_gmt( $item )
	{		
		$output = "";

		if( isset( $item->post_status ) )
			$output .= $item->post_status .'<br/>';
		
		$date = new \DateTime( $item->date_gmt );
		$output .= "<abbr title=\"". $date->format( __( 'Y/m/d g:i:s a' ) ) ."\">". $date->format( __( 'Y/m/d' ) ) ."</abbr>";		
			
		return $output;
    } 
    
	/** ==  == **/
	public function column_modified_gmt( $item )
	{		
		$date = new \DateTime( $item->modified_gmt );
		
		return "<abbr title=\"". $date->format( __( 'Y/m/d g:i:s a' ) ) ."\">". $date->format( __( 'Y/m/d' ) ) ."</abbr>";
    }  
	
	/** == Rendu de la page  == **/
	public function Render()
	{
		$this->prepare_items();
	?>
		<div class="wrap">
    		<h2>
    			<?php _e( 'Interface client', 'tify' );?>
    		</h2>
        	
        	<?php if( $this->Debug ) :?>
			<div id="debug"><?php var_dump( json_decode( $this->Debug['body'], true ) );?></div>
        	<?php endif;?>
        		
    	    <?php $this->search_box( $this->View->getLabel( 'search_items' ), $this->View->getID() );?>		
    		<?php $this->display();?>
    	</div>
	<?php
	}
}