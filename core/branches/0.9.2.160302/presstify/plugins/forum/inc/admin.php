<?php
class tiFy_Forum_AdminMain{
	/* = ARGUMENTS = */
	public 	// Configuration
			$dir,
			$uri,
			$menu_slug = array(),
			
			// Paramètres
			$hookname = array(),
						
			// Contrôleurs
			$contribution,
			$contributor,
			$options,
			$topic;
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Définition de la classe de référence
		$this->master = $master;
		
		// Définition des chemins
		$this->dir = $this->master->dir .'/admin';
		$this->uri = $this->master->uri .'admin';
						
		// Actions et Filtres Wordpress
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );	
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement complet de Wordpress == **/
	public function wp_loaded(){
		// Chargement du gestionnaire de vue
		tify_require_lib( 'admin_view' );
		// Instanciation des contrôleurs	
		/// Sujets
		require_once $this->dir .'/topic.php';
		$this->topic = new tiFy_Forum_AdminTopic( $this->master );
		/// Contribution
		require_once $this->dir .'/contribution.php';
		$this->contribution = new tiFy_Forum_AdminContribution( $this->master );		
		/// Options
		require_once $this->dir .'/options.php';
		$this->options = new tiFy_Forum_AdminOptions( $this->master );		
		/// Contributeurs
		require_once $this->dir .'/contributor.php';
		$this->contributor = new tiFy_Forum_AdminContributor( $this->master );
	}	
	
	/** == Menu d'administration == **/
	public function wp_admin_menu(){
		$this->master->hookname['parent'] = add_menu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Forum', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['parent'], 
			$this->master->is_multi ? array( $this->multi, 'admin_render' ) : array( $this->topic, 'admin_render' ),
			'dashicons-megaphone'
		);
		// Sujets
		$this->master->hookname['topic'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Sujets de forum', 'tify' ),
			__( 'Sujets', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['topic'], 
			array( $this->topic, 'admin_render' ) 
		);
		// Contribution
		$this->master->hookname['contribution'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Contributions aux sujets de forum', 'tify' ), 
			__( 'Contributions', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['contribution'], 
			array( $this->contribution, 'admin_render' ) 
		);
		// Options
		$this->master->hookname['options'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Options de forum', 'tify' ), 
			__( 'Options', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['options'], 
			array( $this->options, 'admin_render' ) 
		);
		// Contributeurs
		$this->master->hookname['contributor'] = add_submenu_page( 
			$this->master->menu_slug['parent'], 
			__( 'Contributeurs de forum', 'tify' ),
			__( 'Contributeurs', 'tify' ), 
			'manage_options', 
			$this->master->menu_slug['contributor'], 
			array( $this->contributor, 'admin_render' ) 
		);
	}

	/** == Initialisation globale == **/
	public function wp_admin_init(){
		foreach( $this->menu_slug as $key => $menu_slug )
			if( ! $this->is_multi && ( $key === 'topic' ) )
				$this->hookname[$key] = get_plugin_page_hookname( $this->menu_slug['parent'], $this->menu_slug['parent'] );	
			else
				$this->hookname[$key] = get_plugin_page_hookname( $menu_slug, $this->menu_slug['parent'] );
	}	
}