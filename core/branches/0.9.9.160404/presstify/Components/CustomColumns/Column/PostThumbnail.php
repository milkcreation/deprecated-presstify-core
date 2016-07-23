<?php
namespace tiFy\Components\CustomColumns\Column;

use tiFy\Components\CustomColumns\Factory;

class PostThumbnail extends Factory
{
	/* = CONSTRUCTEUR = */
	public function __construct( $args = array() )
	{ 
		$defaults = array(
			'title'		=> 	__( 'Mini.', 'tify' ),
			'position'	=> 1
		);		
		$args = wp_parse_args( $args, $defaults );
		
		parent::__construct( $args );
		
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
	}
		
	/* = Affichage des données de la colonne = */
	final public function Content( $column, $post_id ){
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
	
	/* = Style de la colonne = */
	final public function admin_print_styles()
	{
		$post_type = get_current_screen()->post_type;
		
		if( ( get_current_screen()->base != 'edit' ) && ! in_array( $post_type, $this->PostTypes ) )
			return;			
		?><style type="text/css">
		.wp-list-table th#<?php echo $this->Column[$post_type]?>,
		.wp-list-table td.<?php echo $this->Column[$post_type]?>{
			width:80px;
			text-align:center;
		}
		.wp-list-table td.<?php echo $this->Column[$post_type]?> img{
			max-width: 80px;
			max-height: 60px;    		
		}
		</style><?php
	}
}