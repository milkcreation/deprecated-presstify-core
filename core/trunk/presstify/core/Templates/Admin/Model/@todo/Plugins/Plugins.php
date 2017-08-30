<?php
namespace tiFy\Core\Admin;

use tiFy\App\Factory;
use tiFy\tiFy;

class Plugins extends App
{
	/* = ARGUMENTS = */
	private	$plugins = array();
		
	/* = Récupération de la liste des Plugins = */
	public function get_plugins()
	{
		if( $this->plugins )
			return $this->plugins;
	
			if( ! function_exists( 'get_plugins' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
				$plugin_folder = '/'. basename( tiFy::$AbsDir ). '/plugins';
	
				if ( ! empty( $this->plugins ) )
					return $this->plugins;
	
					$wp_plugins = array ();
					$plugin_root = WPMU_PLUGIN_DIR;
					if ( ! empty( $plugin_folder ) )
						$plugin_root .= $plugin_folder;
	
						// Files in wp-content/plugins directory
						$plugins_dir = @ opendir( $plugin_root );
						$plugin_files = array();
	
						if ( $plugins_dir ) {
							while (($file = readdir( $plugins_dir ) ) !== false ) {
								if ( substr($file, 0, 1) == '.' )
									continue;
									if ( is_dir( $plugin_root.'/'.$file ) ) {
										$plugins_subdir = @ opendir( $plugin_root.'/'.$file );
										if ( $plugins_subdir ) {
											while (($subfile = readdir( $plugins_subdir ) ) !== false ) {
												if ( substr($subfile, 0, 1) == '.' )
													continue;
													if ( substr($subfile, -4) == '.php' )
														$plugin_files[] = "$file/$subfile";
											}
											closedir( $plugins_subdir );
										}
									} else {
										if ( substr($file, -4) == '.php' )
											$plugin_files[] = $file;
									}
							}
							closedir( $plugins_dir );
						}
	
						if ( empty($plugin_files) )
							return $wp_plugins;
	
							foreach ( $plugin_files as $plugin_file ) {
								if ( ! is_readable( "$plugin_root/$plugin_file" ) )
									continue;
	
									$plugin_data = get_plugin_data( "$plugin_root/$plugin_file", true, true );
	
									if ( empty ( $plugin_data['Name'] ) )
										continue;
	
										$wp_plugins[plugin_basename( $plugin_file )] = $plugin_data;
							}
	
							uasort( $wp_plugins, '_sort_uname_callback' );
	
							$this->plugins = $wp_plugins;
	
							return $wp_plugins;
	}
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFY $master ){
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	public function admin_init(){
		$this->plugins_list_table = new tiFy_plugins_list_table( $this->master );		
	}
	
	/** == Menu d'administration == **/
	public function admin_menu(){
		add_submenu_page( 'tify', __( 'Extensions', 'tify' ) , __( 'Extensions', 'tify' ), $this->master->capabilities->cap, 'tify', array( $this, 'admin_render' ) );		
	}
	
	/* = AFFICHAGE = */
	/** == Page d'administration des plugins == **/
	public function admin_render(){
		$this->plugins_list_table->prepare_items();    
	?>
	    <div class="wrap">
	        <h2><?php _e( 'Extensions PresstiFy', 'tiFy' );?></h2>
	
	        <form method="post">
	            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $this->plugins_list_table->display(); ?>
	        </form>     
	    </div>
	<?php	
	}
}

if( ! is_admin () ) 
	return;

if( ! class_exists( 'WP_List_Table' ) ) :
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require_once( ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php' );
endif;

/**
 * Classe de la table des enregistrements
 */
class tiFy_plugins_list_table extends WP_Plugin_Install_List_Table {
	var $master,
		$args;
		
	function __construct(tiFY $master ){	
		// Définition des arguments par défault de la classe parente
       	parent::__construct( array(
            'singular'  => 'plugin',
            'plural'    => 'plugins',
            'ajax'      => true,
            'screen'	=> 'toplevel_page_tify'
        ) );	
		// Héritage de la classe maîtresse
		$this->master = $master;
		
		// Définition des arguments complémentaires
		$this->args['paged'] 		= $this->get_pagenum();		 				
		$this->args['per_page'] 	= 20;
		
		$this->process_bulk_action();
	}
	
	
	/**
	 * Affichage du contenu par defaut des colonnes
	 */
	function column_default( $item, $column_name ){
		return $item->$column_name;
    }
		
	/**
	 * Contenu de la colonne
	 */
	function column_plugin_title( $item ){
		if( ! $this->master->plugins->is_active_plugin( $item->ID ) )
			$actions['activate'] = 	"<a href=\"" 
									. wp_nonce_url( 
										add_query_arg( 
											array( 
												'page' => $_REQUEST['page'], 
												'action' => 'activate', 
												'plugin' => $item->ID
											),
											admin_url('admin.php') ),
											'tify_activate_plugin_'. esc_attr( $item->ID ) ) 
									."\">". __( 'Activer', 'tify' ) . "</a>";
		else
			$actions['deactivate'] = 	"<a href=\"" 
									. wp_nonce_url( 
										add_query_arg( 
											array( 
												'page' => $_REQUEST['page'], 
												'action' => 'deactivate', 
												'plugin' => $item->ID
											),
											admin_url('admin.php') ),
											'tify_deactivate_plugin_'. esc_attr( $item->ID ) ) 
									."\">". __( 'Désactiver', 'tify' ) . "</a>";
			
     	return ( $this->master->plugins->is_active_plugin( $item->ID ) ) 
     		? sprintf( '<strong>%1$s</strong>%2$s', $item->Name, $this->row_actions( $actions, true ) ) 
			: sprintf( '%1$s%2$s', $item->Name, $this->row_actions( $actions, true ) );
    }	

	/**
	 * Contenu de la colonne "date"
	 */
	function column_description( $item ){
		$output  = "<div class=\"plugin-description\">{$item->Description}</div>";
		$output .= "<div class=\"second plugin-version-author-uri\">";
		$plugin_meta = array();
		if ( !empty( $item->Version ) )
			$plugin_meta[] = sprintf( __( 'Version %s', 'tify' ), $item->Version );
		if ( !empty( $item->Author ) ) :
			$author = $item->Author;
			if ( !empty( $item->AuthorURI ) )
				$author = '<a href="' . $item->AuthorURI . '">' . $item->Author . '</a>';
			$plugin_meta[] = sprintf( __( 'Par %s', 'tify' ), $author );
			endif;
		if ( ! empty( $item->PluginURI ) ) :
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>',
				esc_url( $item->PluginURI ),
				__( 'Allez sur la page de l\'extension', 'tify' )
			);
		endif;
		
		$output .= implode( ' | ', $plugin_meta );
		$output .= "</div>";

		if( ! empty( $this->master->plugins->addons[ $item->ID ] ) ) :
			$output .= "<h4 style=\"margin:0 0 5px;\">". __( 'Activation/Désactivation des modules de l\'extension', 'tify' ) ."</h4>";
		   	$output .= "<ul class=\"tierce plugin-addons\" style=\"margin:0;\">";
			foreach( $this->master->plugins->addons[ $item->ID ] as $addon_path => $addon_data ) :
				$output .= 	"<li>"
							."<label for=\"{$item->ID}". esc_attr( $addon_path ) ."\" title=\"\">"
							."<input type=\"checkbox\" class=\"tify_addon-toggle\" id=\"{$item->ID}". esc_attr( $addon_path ) ."\" "
								." value=\"{$item->ID}|$addon_path\""
								. checked( $this->master->plugins->is_active_addon( dirname( $item->ID ) ."/". $addon_path ), true, false ) ." "
								. ( ! $this->master->plugins->is_active_plugin( $item->ID ) ? "disabled=\"disabled\"" : "" )
								." autocomplete=\"off\" />"
							."<strong>". $addon_data['Name'] ."</strong> | <em>". $addon_data['Description'] ."</em> | v.". $addon_data['Version'] 
							."</label>"
							."</li>";
			endforeach;
			$output .= "</ul>";
		endif;
		
	   return $output;
    }
	
	/**
	 * Action groupées
	 */
    function get_bulk_actions() {
        $actions = array(
            'activate-selected'    	=> __( 'Activer', 'tify' ),
            'deactivate-selected'   => __( 'Désactiver', 'tify' ),
        );
        return $actions;
    }
    
	/**
	 * Execution des actions groupées
	 */
	function process_bulk_action() {
		if( ! isset( $_REQUEST['page'] ) || ( $_REQUEST['page'] != 'tify' ) )
			return;
		
        switch( $this->current_action() ) :
			case 'activate' :
				if ( ! current_user_can( $this->master->capability ) )
					wp_die( __( '<h1>Action non permise</h1><p>Vous n\'avez pas les habilitations suffisantes pour effectuer cette action.</p>', 'tify' ) );
				
				if( ! $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '' )
					return;
				
				check_admin_referer('tify_activate_plugin_' . $plugin);
				
				$this->master->plugins->activate_plugin( $plugin );
				wp_redirect( self_admin_url("admin.php?activate=true&page={$_REQUEST['page']}&paged={$this->args['paged']}" ) );
				exit;
				break;
			case 'deactivate' :
				if ( ! current_user_can( $this->master->capability ) )
					wp_die( __( '<h1>Action non permise</h1><p>Vous n\'avez pas les habilitations suffisantes pour effectuer cette action.</p>', 'tify' ) );
				
				if( ! $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '' )
					return;
				
				check_admin_referer('tify_deactivate_plugin_' . $plugin);
				
				$this->master->plugins->deactivate_plugin( $plugin );
				wp_redirect( self_admin_url("admin.php?deactivate=true&page={$_REQUEST['page']}&paged={$this->args['paged']}" ) );
				break;
			case 'activate-selected' :
				if ( ! current_user_can( $this->master->plugins->capability ) )
					wp_die( __( '<h1>Action non permise</h1><p>Vous n\'avez pas les habilitations suffisantes pour effectuer cette action.</p>', 'tify' ) );
				
				if( ! $plugins = isset( $_POST['plugin'] ) ? (array) $_POST['plugin'] : array() )
					return;
			
				check_admin_referer('bulk-plugins');
				
				foreach( $plugins as $plugin )
					$this->master->plugins->activate_plugin( $plugin );
					
				wp_redirect( self_admin_url("admin.php?activate-multi=true&page={$_REQUEST['page']}&paged={$this->args['paged']}" ) );	
				break;
			case 'deactivate-selected' :
				if ( ! current_user_can( $this->master->plugins->capability ) )
					wp_die( __( '<h1>Action non permise</h1><p>Vous n\'avez pas les habilitations suffisantes pour effectuer cette action.</p>', 'tify' ) );
				
				if( ! $plugins = isset( $_POST['plugin'] ) ? (array) $_POST['plugin'] : array() )
					return;
				
				check_admin_referer('bulk-plugins');
			
				foreach( $plugins as $plugin )
					$this->master->plugins->deactivate_plugin( $plugin );
					
				wp_redirect( self_admin_url("admin.php?deactivate-multi=true&page={$_REQUEST['page']}&paged={$this->args['paged']}" ) );
				break;		
		endswitch;            
    }
	
	/**
	 * Récupération des items
	 */
	function prepare_items() {
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( $columns, $hidden, $sortable );	
				
		$this->items = array();		
		if( $plugins = $this->master->plugins->get_plugins() ) :
			foreach( $plugins as $plugin_path => $plugin_data )	:
				$plugin_data['ID'] 					= 	$plugin_path;
				$plugin_data['name'] 				= 	$plugin_data['Name'];
				$plugin_data['slug'] 				= 	$plugin_data['PluginURI'];
				$plugin_data['version'] 			= 	$plugin_data['Version'];
				$plugin_data['author'] 				= 	$plugin_data['Author'];
				$plugin_data['short_description'] 	= 	$plugin_data['Description'];
				$plugin_data['rating'] 				= 	100;
				$plugin_data['num_ratings'] 		= 	1000;
				$plugin_data['active_installs']		= 	1;
				$plugin_data['last_updated']		= 	'2015-11-28';				
				$this->items[] = (object) $plugin_data;
			endforeach;
		endif;	

		$total_items = count( $this->items );
			
		$this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => 50,                     
            'total_pages' => ceil( $total_items/$this->args['per_page'] ) 
        ) );
		$GLOBALS['tab'] = 'featured';		
	}

	/**
	 * @global string $wp_version
	 */
	public function display_rows() {
		$plugins_allowedtags = array(
			'a' => array( 'href' => array(),'title' => array(), 'target' => array() ),
			'abbr' => array( 'title' => array() ),'acronym' => array( 'title' => array() ),
			'code' => array(), 'pre' => array(), 'em' => array(),'strong' => array(),
			'ul' => array(), 'ol' => array(), 'li' => array(), 'p' => array(), 'br' => array()
		);

		$plugins_group_titles = array(
			'Performance' => _x( 'Performance', 'Plugin installer group title' ),
			'Social'      => _x( 'Social',      'Plugin installer group title' ),
			'Tools'       => _x( 'Tools',       'Plugin installer group title' ),
		);

		$group = null;

		foreach ( (array) $this->items as $plugin ) {
			if ( is_object( $plugin ) ) {
				$plugin = (array) $plugin;
			}

			// Display the group heading if there is one
			if ( isset( $plugin['group'] ) && $plugin['group'] != $group ) {
				if ( isset( $this->groups[ $plugin['group'] ] ) ) {
					$group_name = $this->groups[ $plugin['group'] ];
					if ( isset( $plugins_group_titles[ $group_name ] ) ) {
						$group_name = $plugins_group_titles[ $group_name ];
					}
				} else {
					$group_name = $plugin['group'];
				}

				// Starting a new group, close off the divs of the last one
				if ( ! empty( $group ) ) {
					echo '</div></div>';
				}

				echo '<div class="plugin-group"><h3>' . esc_html( $group_name ) . '</h3>';
				// needs an extra wrapping div for nth-child selectors to work
				echo '<div class="plugin-items">';

				$group = $plugin['group'];
			}
			$title = wp_kses( $plugin['name'], $plugins_allowedtags );

			// Remove any HTML from the description.
			$description = strip_tags( $plugin['short_description'] );
			$version = wp_kses( $plugin['version'], $plugins_allowedtags );

			$name = strip_tags( $title . ' ' . $version );

			$author = wp_kses( $plugin['author'], $plugins_allowedtags );
			if ( ! empty( $author ) ) {
				$author = ' <cite>' . sprintf( __( 'By %s' ), $author ) . '</cite>';
			}

			$action_links = array();

			/*if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
				$status = install_plugin_install_status( $plugin );

				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] ) {
				
							$action_links[] = '<a class="install-now button" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Install Now' ) . '</a>';
						}

						break;
					case 'update_available':
						if ( $status['url'] ) {
				
							$action_links[] = '<a class="update-now button" data-plugin="' . esc_attr( $status['file'] ) . '" data-slug="' . esc_attr( $plugin['slug'] ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Update Now' ) . '</a>';
						}

						break;
					case 'latest_installed':
					case 'newer_installed':
						$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( 'This plugin is already installed and is up to date' ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
						break;
				}
			}*/

			$details_link   = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] .
								'&amp;TB_iframe=true&amp;width=600&amp;height=550' );

			/* translators: 1: Plugin name and version. */
			$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';

			if ( !empty( $plugin['icons']['svg'] ) ) {
				$plugin_icon_url = $plugin['icons']['svg'];
			} elseif ( !empty( $plugin['icons']['2x'] ) ) {
				$plugin_icon_url = $plugin['icons']['2x'];
			} elseif ( !empty( $plugin['icons']['1x'] ) ) {
				$plugin_icon_url = $plugin['icons']['1x'];
			} else {
				$geopattern = new \RedeyeVentures\GeoPattern\GeoPattern();
				$geopattern->setString( $plugin['name'] );
				$plugin_icon_url = $geopattern->toDataURI();
			}

			/**
			 * Filter the install action links for a plugin.
			 *
			 * @since 2.7.0
			 *
			 * @param array $action_links An array of plugin action hyperlinks. Defaults are links to Details and Install Now.
			 * @param array $plugin       The plugin currently being listed.
			 */
			$action_links = apply_filters( 'plugin_install_action_links', $action_links, $plugin );

			$date_format = __( 'M j, Y @ H:i' );
			$last_updated_timestamp = strtotime( $plugin['last_updated'] );
		?>
		<div class="plugin-card plugin-card-<?php echo sanitize_html_class( $plugin['slug'] ); ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox">
							<?php echo $title; ?>
							<img src="<?php echo esc_attr( $plugin_icon_url ) ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<?php
						if ( $action_links ) {
							echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
						}
					?>
				</div>
				<div class="desc column-description">
					<p><?php echo $description; ?></p>
					<p class="authors"><?php echo $author; ?></p>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<div class="vers column-rating">
					<?php wp_star_rating( array( 'rating' => $plugin['rating'], 'type' => 'percent', 'number' => $plugin['num_ratings'] ) ); ?>
					<span class="num-ratings">(<?php echo number_format_i18n( $plugin['num_ratings'] ); ?>)</span>
				</div>
				<div class="column-updated">
					<strong><?php _e( 'Last Updated:' ); ?></strong> <span title="<?php echo esc_attr( date_i18n( $date_format, $last_updated_timestamp ) ); ?>">
						<?php printf( __( '%s ago' ), human_time_diff( $last_updated_timestamp ) ); ?>
					</span>
				</div>
				<div class="column-downloaded">
					<?php
					if ( $plugin['active_installs'] >= 1000000 ) {
						$active_installs_text = _x( '1+ Million', 'Active plugin installs' );
					} else {
						$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
					}
					printf( __( '%s Active Installs' ), $active_installs_text );
					?>
				</div>
				<div class="column-compatibility">
					<?php
					if ( ! empty( $plugin['tested'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['tested'] ) ), $plugin['tested'], '>' ) ) {
						echo '<span class="compatibility-untested">' . __( 'Untested with your version of WordPress' ) . '</span>';
					} elseif ( ! empty( $plugin['requires'] ) && version_compare( substr( $GLOBALS['wp_version'], 0, strlen( $plugin['requires'] ) ), $plugin['requires'], '<' ) ) {
						echo '<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> avec votre version de PressTiFy', 'tify' ) . '</span>';
					} else {
						echo '<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> avec votre version de PressTiFy', 'tify' ) . '</span>';
					}
					?>
				</div>
			</div>
		</div>
		<?php
		}

		// Close off the group divs of the last one
		if ( ! empty( $group ) ) {
			echo '</div></div>';
		}
	}
}
