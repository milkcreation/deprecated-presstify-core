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
			<div class="wisti-logo" style="border-radius:64px; width:64px; height:64px; border:solid 4px #000; display:block; text-align:center; float:left; vertical-align:center; margin-right:10px;">
				<img style="color:#000; padding:5px; width:54px; height:54px;" src="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYWxxdWVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSI2My41NTZweCIgaGVpZ2h0PSI3NC4wNzRweCIgdmlld0JveD0iMTguNjkxIC0xLjU5MyA2My41NTYgNzQuMDc0IiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDE4LjY5MSAtMS41OTMgNjMuNTU2IDc0LjA3NCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgZmlsbD0iIzAwMCI+PGc+PHBhdGggZD0iTTc5LjkzMiw0MS43bC0yLjU1MS00LjQ0NWMtMC41NzYsNC4wMzMtMi4yMjMsNi45OTYtNC4xOTcsNi45OTZWMjQuNDk4YzAuODIyLDAsMS41NjMsMC40OTQsMi4xNCwxLjMxN3YtNC4zNjJjMC0xMi43NTctMTAuMjg4LTIzLjA0NS0yMy4wNDYtMjMuMDQ1aC0zLjc4NmMtMTIuNzU3LDAtMjMuMDQ1LDEwLjI4OC0yMy4wNDUsMjMuMDQ1djQuNDQ0YzAuNjU5LTAuODIzLDEuMzk5LTEuMzE3LDIuMTQtMS4zMTd2MTkuNzUzYy0xLjk3NSwwLTMuNjIyLTIuOTYzLTQuMTk4LTYuOTk2bC0yLjU1MSw0LjQ0NGMtNC42MDksOC4zOTUtMS41NjQsMTUuNTU2LDYuOTE0LDE3LjM2N2MzLjYyMiw3LjksMTEuNjA1LDEzLjMzMywyMC44MjMsMTMuMzMzaDMuNzg1YzkuMjE5LDAsMTcuMjAyLTUuNDMzLDIwLjkwNi0xMy4zMzNDODEuNzQyLDU3LjMzOCw4NC43ODgsNTAuMTc3LDc5LjkzMiw0MS43eiBNNTEuMTI1LDY3LjM3OWgtMS4zMTZjLTQuNjkxLDAtOC40NzgtMy43ODYtOC40NzgtOC40Nzh2LTQuNzc0YzIuOTYzLTAuNjU4LDYuMDA4LTIuNTUxLDkuMDU0LTIuNTUxYzMuMDQ1LDAsNi4wOSwxLjg5Myw5LjEzNiwyLjU1MXY0Ljc3NEM1OS42MDQsNjMuNTkzLDU1LjgxNiw2Ny4zNzksNTEuMTI1LDY3LjM3OXogTTY4LjY1NiwzOS44MDdjMCw1LjUxNC0zLjQ1NywxMC4xMjQtOC4zOTUsMTEuOTM0Yy0wLjQxMi0wLjY1OC0xLjIzNC0xLjMxNi0xLjk3Ni0xLjQ4MWMtNS4xODYtMC45MDUtMTAuNDUzLTAuOTA1LTE1LjYzOCwwYy0wLjc0MSwwLjE2NS0xLjU2MywwLjc0MS0xLjk3NSwxLjM5OWMtNC44NTYtMS44MTEtOC4yMy02LjQxOS04LjIzLTExLjkzNFYyNy4wNDljMC00LjAzMyw0LjYwOS03LjQwNywxMC4yMDYtNy40MDdjMy4yMSwwLDYuMDA4LDEuMDcsNy45MDEsMi43MTZjMS44OTQtMS42NDYsNC42OTEtMi43MTYsNy45MDItMi43MTZjNS41OTYsMCwxMC4yMDUsMy4yOTIsMTAuMjA1LDcuNDA3VjM5LjgwN3oiLz48cGF0aCBkPSJNNDguNzM5LDM4LjczN2MtMS42NDYtMS4wNy0zLjIxLTAuODIzLTMuNDU3LDAuNjU4Yy0wLjMyOSwxLjM5OSwxLjA3LDIuMzA1LDIuOTYzLDEuOTc1QzUwLjEzOCw0MC45NTksNTAuMzg1LDM5LjgwNyw0OC43MzksMzguNzM3eiIvPjxwYXRoIGQ9Ik01Mi4zNTksMzguNzM3Yy0xLjY0NiwxLjA3LTEuMzk4LDIuMjIyLDAuNDk0LDIuNjMzYzEuODk0LDAuMzMsMy4yMTEtMC40OTQsMi45NjMtMS45NzVDNTUuNDg4LDM3Ljk5Niw1My45MjQsMzcuNjY3LDUyLjM1OSwzOC43Mzd6Ii8+PHBhdGggZD0iTTQxLjMzMSwyNy45NTVjLTIuNTUxLDAtNC42MDksMi4wNTgtNC42MDksNC42MDloOS4xMzZDNDUuODU4LDMwLjAxMiw0My44LDI3Ljk1NSw0MS4zMzEsMjcuOTU1eiIvPjxwYXRoIGQ9Ik01OS42MDQsMjcuOTU1Yy0yLjU1MywwLTQuNjA5LDIuMDU4LTQuNjA5LDQuNjA5aDkuMTM2QzY0LjIxMiwzMC4wMTIsNjIuMTU0LDI3Ljk1NSw1OS42MDQsMjcuOTU1eiIvPjwvZz48L3N2Zz4=">
			</div>
			<div>
				<h1 style="display:inline-block; margin:0;font-weight:800; text-transform:uppercase; font-size:43px;"><?php _e( 'Wistify', 'tify' );?></h1>
				<br>
				<h2 style="display:inline-block; line-height:1; margin:0; padding:0 0 10px 7px;  position:relative;"><?php _e( 'Le mailing malin', 'tify' );?><span style="position:absolute; bottom:0; right:0;font-weight:300; color:#666; font-size:9px; margin:0; padding:0; text-align:right;">comme un singe</span></h2>
			</div>
		</div>
	<?php
	}
}