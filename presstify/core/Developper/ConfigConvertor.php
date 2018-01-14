<?php
namespace tiFy\Components\DevTools\Tools\ConfigConvertor;

class ConfigConvertor extends \tiFy\App
{
	// Configuration
	private $Hookname = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('admin_menu');
        $this->appAddAction('current_screen');
        $this->appAddAction('wp_ajax_tiFy_ConfigConvertor_Process', 'wp_ajax');
    }

    /**
     * EVENEMENTS
     */
	/** == == **/
	final public function admin_menu()
	{
		$this->Hookname = add_menu_page( 'DevToolsConfigConvertor', __( 'Convertisseur de configuration', 'tify' ), 'manage_options', 'tiFyDevToolsConfigConvertor', array( $this, 'adminRender' ) );
	}
	
	/** == == **/
	final public function current_screen( $current_screen )
	{
		if( $this->Hookname !== $current_screen->id )
			return;
		wp_enqueue_style( 'DevToolsConfigConvertor', self::tFyAppUrl() . '/ConfigConvertor.css', array(), 160609 );
		wp_enqueue_script( 'DevToolsConfigConvertor', self::tFyAppUrl() . '/ConfigConvertor.js', array( 'jquery' ), 160609, true );
	}
	
	/** == == **/
	final public function wp_ajax()
	{
		$data = stripslashes(  preg_replace('/(\n|\t|\r)+/', ' ', $_POST['data'] ) );
		eval( "\$output = ". $data );
				
		echo spyc_dump( $output );
		exit;
	}
	
	/* = CONTRÔLEUR = */
	/** == Rendu de l'interface d'administration == **/
	final public function adminRender()
	{
	?>
		<div class="wrap">
			<h1><?php _e( 'Converteur de configuration', 'tify' );?></h1>
			<div id="ConfigConvertor">
				<form method="post" action="">
					<input type="hidden" name="action" value="tiFy_ConfigConvertor_Process" />
					<ul id="langSelectors" class="tify_cols tify_cols-2">
						<li>
							<label>
								<?php _e( 'Langage d\'origine', 'tify' );?>
							</label>
							<select name="ori-lang" >
								<option value="php" selected="selected"><?php _e( 'PHP', 'tify' );?></option>
							</select>
						</li>
						<li>
							<label>
								<?php _e( 'Langage de destination', 'tify' );?>
							</label>
							<select name="dest-lang" >
								<option value="yml" selected="selected"><?php _e( 'YML', 'tify' );?></option>
							</select>
						</li>
					</ul>
					
					
					<div id="textInputs" class="tify_cols tify_cols-2">
						<div>
							<textarea name="data"></textarea>
						</div>
						<div>
							<textarea name="output" readonly="readonly"></textarea>
						</div>
					</div>
					
					<button type="submit"><?php _e( 'Convertir', 'tify' );?></button>
				</form>
			</div>
		</div>
	<?php
	}
}