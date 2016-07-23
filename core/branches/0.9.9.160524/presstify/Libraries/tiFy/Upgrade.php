<?php
namespace tiFy\Lib;

use \tiFy\Environment\App;

abstract class Upgrade extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions		= array(
		'admin_init'
	);

	// Variable de stockage du numéro de version
	private $StorageVar;
	// Numéro de version courante
	private $Current;
	// Liste des mises à jour
	private $Upgraded = array();
	// Message d'alerte des mises à jour effectuées
	private $Verbose = true;
	// Url de redirection
	private $Location;

	/* = CONSTRUCTEUR = */
	public function __construct( $StorageVar = null )
	{
		parent::__construct();
		$this->StorageVar = $StorageVar;
	}

	/* = ACTIONS DE DECLENCHEMENT = */
	/* = Initialisation de Wordpress = */
	final public function admin_init()
	{
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		// Contrôle s'il s'agit d'une execution de page via ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		// Vérifie si l'utilisateur est authentifié
		if( ! is_user_logged_in() )
			return;
		
		$this->Current = get_option( $this->StorageVar, 0 );		
		$_current = $this->FormatVersion( $this->Current );
		
		foreach( (array) $this->getUpdateMethods() as $version => $method ) :	
			$_version = $this->FormatVersion( $version );
			// Vérification du numéro de version
			if( version_compare( $_current, $_version, '>=' ) )
				continue;

			if( ! isset( $_REQUEST['tify_upgrade'] ) || ( $_REQUEST['tify_upgrade'] !== $this->StorageVar ) ) :
				$tify_upgrade = $this->StorageVar;
				add_action( 'admin_notices', function() use ($tify_upgrade){
					?>
				    <div class="notice notice-info">
				        <p><?php printf( __( 'Des mises à jour sont disponibles %s', 'tify' ), "<a href=\"". esc_attr( add_query_arg( 'tify_upgrade', $tify_upgrade, admin_url() ) ) ."\">". __( 'Mettre à jour', 'tify' ) ."</a>" ); ?></p>
				    </div>
				    <?php
				});
				break;
			else :
				// Lancement de la mise à jour		
				$return = call_user_func( array( $this, $method ) );
	
				if( is_wp_error( $return ) ) :
					\wp_die( $return->get_error_message(), __( 'Erreur rencontrée lors de la mise à jour', 'tify' ), 500 );
					exit;
				elseif( $return ) :
					$this->UpgradeStorageVersion( $version );
					$this->Upgraded[$version] = $return;
				endif;
			endif;
		endforeach;

		if( $this->Upgraded )
			$this->Redirect();
	}
	
	private function FormatVersion( $str )
	{
		return implode( '.', str_split(  $str, 2 ) );
	}
	/** == Récupération de la liste des methodes de mise à jour == **/
	private function getUpdateMethods()
	{
		$updates = array();
		foreach( (array) get_class_methods( $this ) as $method ) :
			// Test de correspondance de la méthode
			if( ! preg_match( '/^update_([\d]*)/', $method, $version ) )
				continue;
		
			$updates[(int) $version[1]] = $method;				
		endforeach;
		
		ksort( $updates );
		
		return $updates;
	}

	/* = = */
	private function UpgradeStorageVersion( $version )
	{
		\update_option( $this->StorageVar, $version );
	}

	/* = = */
	private function Redirect()
	{
		if( ! $this->Location )
			$this->Location = ( stripos( $_SERVER['SERVER_PROTOCOL'], 'https' ) === true ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if( ! $this->Verbose ) :
			\wp_redirect( $this->Location );
			exit;
		else :
			// Composition du message
			$message = 	"<h2>". __( 'Mise à jour effectuée avec succès', 'tify' ) ."</h2>".
						"<ol>";
			foreach( $this->Upgraded as $version => $result ) :
				$message .= "<li>". sprintf( __( 'version : %d', 'tify' ), $version );
				if( is_string( $result ) )
					$message .= "<br><em style=\"color:#999;font-size:0.8em;\">{$result}</em>";
				$message .= "</li>";
			endforeach;
		
			$message .=	"</ol>".
					"<hr style=\"border:none;background-color:rgb(238, 238, 238);height:1px;\">".
					"<a href=\"{$this->Location}\" title=\"". __( 'Retourner sur le site', 'tify' ) ."\" style=\"font-size:0.9em\">&larr; ". __( 'Retour au site', 'tify' )."</a>";
			// Titre
			$title = __( 'Mise à jour réussie', 'tify' );
				
			\wp_die( $message, $title, 426 );
			exit;
		endif;
	}
}