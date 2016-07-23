<?php
namespace tiFy\Plugins\Wistify\Admin;

use tiFy\Plugins\Wistify\Wistify;

class Dashboard{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( Wistify $master ){
		// Définition de la classe de référence
		$this->master = $master;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */	
	/* = VUES = */
	public function admin_render(){
	?>
		<div class="wrap">
			<i class="wisti-logo" style="font-size:55px; border-radius:64px; width:64px; height:64px; line-height:75px; border:solid 4px #444; display:block; text-align:center; float:left; vertical-align:center; margin-right:10px;"></i>
			<div>
				<h1 style="display:inline-block; margin:10px 0 0;font-weight:800; text-transform:uppercase; font-size:43px;">Wistify</h1>
				<br>
				<h2 style="display:inline-block; line-height:1;padding:0 0 10px; position:relative;">Le mailing malin <span style="position:absolute; bottom:0; right:0;font-weight:300; color:#666; font-size:9px; margin:0; padding:0; text-align:right;">comme un singe</span></h2>
			</div>
		</div>
	<?php
	}
}