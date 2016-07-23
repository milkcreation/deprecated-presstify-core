<?php
namespace tiFy\Components\CustomColumns\Post\PostThumbnail;

use tiFy\Components\CustomColumns\Factory;

class PostThumbnail extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> 	__( 'Mini.', 'tify' ),
			'position'	=> 1
		);
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
	}
		
	/* = STYLE DYNAMIQUE DE LA COLONNE = */
	public function admin_print_styles()
	{		
		$column = $this->getConfig( 'column' );
		?><style type="text/css">
		.wp-list-table th#<?php echo $column?>,
		.wp-list-table td.<?php echo $column?>{
			width:80px;
			text-align:center;
		}
		.wp-list-table td.<?php echo $column?> img{
			max-width: 80px;
			max-height: 60px;    		
		}
		</style><?php
	}
		
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $column, $post_id )
	{
		$attachment_id = ( $_attachment_id = get_post_thumbnail_id( $post_id ) )? $_attachment_id : 0;
		// Vérifie l'existance de l'image 
		if( ( $attachment = wp_get_attachment_image_src( $attachment_id ) ) 
			&& isset( $attachment[0] ) 
			&& ( $path = tify_get_relative_url( $attachment[0] ) ) 
			&& file_exists( ABSPATH. $path ) )
			$thumb =  wp_get_attachment_image( $attachment_id, array( 80, 60 ), true );
		else
			$thumb = "<div style=\"background-color:#E4E4E4; height:80px; font-size:0.9em; line-height:80px; color:#999;\">". __( 'Indisponible', 'tify' ) ."</div>";		
		
		echo $thumb;		
	}
}