<?php
class tiFy_Wistify_Maintenance{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Wistify_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
	}
	
	/* = = */
	public function admin_render(){
	?>		
	<div class="wrap">
		<h2><?php _e( 'Maintenance (Utilisateurs avancés)', 'tify' ); ?></h2>
		<div>
			<?php printf( __( 'version courante : %d', 'tify' ), $this->master->version );?>&nbsp;-&nbsp;
			<?php printf( __( 'Votre système %s', 'tify' ), ( version_compare( (int) $this->master->installed, (int) $this->master->version, '==' ) ? __( 'est à jour', 'tify' ) : sprintf( __( 'est à la version %d', 'tify' ) , $this->master->installed ) ) );?>
		</div>
		
		<h3><?php _e( 'Tâches planifiées', 'tify' );?></h3>
		<?php $cron_jobs = get_option( 'cron' ); $offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;?>
		<ul>
		<?php 
		foreach( $this->master->tasks->shedules as $hook => $args ) :
			foreach( $cron_jobs as $timestamp => $cronhooks ) :
				if( isset( $cronhooks[$hook] ) ) :
					echo "<li style=\"font-size:14px;\"><strong style=\"width:280px;line-height:1.1;display:inline-block;margin-right:5px;vertical-align:top;\">". $args['title'] ." </strong>". date( 'd-m-Y H:i:s', wp_next_scheduled( $hook ) + $offset ) ."</li>";
					break;
				endif;
			endforeach;
		endforeach; 
		?>
		</ul>
	</div>
	<?php
	}	
}