<?php
namespace tiFy\Lib;

abstract class Upgrade extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Variable de stockage du numéro de version
	private $StorageVar;
	
	// Page d'accroche de la proposition de mise à jour
	private $Hookname;
	
	// Numero de version majeur
	private $MajorVersion;
	
	// Numéro de sous version courante
	private $SubVersion;
	
	// Numéro de version courante formatée
	private $FormatedSubVersion;
	
	// Liste des méthodes de la classe
	private $Methods		= array();
	
	// Liste des mises à jour
	private $Upgraded 		= array();
	
	// Message d'alerte des mises à jour effectuées
	private $Verbose 		= true;
	
	// Url de redirection
	private $Location;
	
	// Version courante
	private static $CurrentVersion;

	/* = CONSTRUCTEUR = */
	public function __construct( $storage_var = null, $hookname = null, $major_version = 0 )
	{
		parent::__construct();
		
		// Définition des variable d'environnement
		if( $storage_var )
			$this->StorageVar = $storage_var;
		if( $hookname )
			$this->Hookname = $hookname;
		if( $major_version )
			$this->MajorVersion = $major_version;
		
		if( $this->StorageVar ) :
			add_action( 'admin_init', array( $this, 'admin_init' ), 25 );
			add_action( 'current_screen', array( $this, 'current_screen' ) );
		endif;
	}

	/* = DECLENCHEUR = */
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
			
		// Définition des variables d'environnement
		if( $this->MajorVersion )
			$this->SubVersion = preg_replace( '/'.preg_quote( $this->MajorVersion ) .'\./', '', get_option( $this->StorageVar, $this->MajorVersion .'.'. 0 ) );
		else
			$this->SubVersion = get_option( $this->StorageVar, 0 );	
	
		$this->FormatedSubVersion = $this->formatVersion( $this->SubVersion );
		
		// Vérifie si un appel de mise à jour est lancé
		if( ! isset( $_REQUEST['tify_upgrade'] ) || ( $_REQUEST['tify_upgrade'] !== $this->StorageVar ) )
			return;
	
		foreach( (array) $this->getUpdateMethods() as $version => $method ) :	
			// Lancement de la mise à jour		
			$return = call_user_func( array( $this, $method ) );

			if( is_wp_error( $return ) ) :
				\wp_die( $return->get_error_message(), __( 'Erreur rencontrée lors de la mise à jour', 'tify' ), 500 );
				exit;
			else :
				$this->UpgradeStorageVersion( $version );
				$this->Upgraded[$version] = $return;
			endif;
		endforeach;

		if( $this->Upgraded )
			$this->Redirect();
	}
	
	/** == == **/
	final public function current_screen( $current_screen )
	{
		if( ! $this->hasUpdateMethods() )
			return;
		if( $this->Hookname && ( $this->Hookname !== $current_screen->id ) )
			return;
		
		$tify_upgrade = $this->StorageVar;
		add_action( 'admin_notices', function() use ( $tify_upgrade )
		{
			?>
		    <div class="notice notice-info">
		        <p><?php printf( __( 'Des mises à jour sont disponibles %s', 'tify' ), "<a href=\"". esc_attr( add_query_arg( 'tify_upgrade', $tify_upgrade, admin_url() ) ) ."\">". __( 'Mettre à jour', 'tify' ) ."</a>" ); ?></p>
		    </div>
		    <?php
		});
	}
	
	/* = CONTRÔLEUR = */
	/** == Formatage du numéro de version == **/
	private function formatVersion( $str )
	{
		return implode( '.', str_split(  $str, 2 ) );
	}
	
	/** == Récupération des méthodes de la classe == **/
	private function getClassMethods()
	{
		if( empty( $this->Methods ) )
			$this->Methods = get_class_methods( $this );
		
		return $this->Methods;
	} 
		
	/** == Vérification d'existance de méthode de mise à jour == **/
	private function hasUpdateMethods()
	{
		foreach( (array) $this->getClassMethods() as $method ) :	
			if( ! preg_match( '/^update_([\d]*)/', $method, $version ) )
				continue;
			$_version = $this->formatVersion( $version[1] );
			if( version_compare( $this->FormatedSubVersion, $_version, '>=' ) )
				continue;
				
			return true;			
		endforeach;
	}	
	
	/** == Récupération de la liste des methodes de mise à jour == **/
	private function getUpdateMethods()
	{
		$updates = array();
		foreach( (array) $this->getClassMethods() as $method ) :
			// Test de correspondance de la méthode
			if( ! preg_match( '/^update_([\d]*)/', $method, $version ) )
				continue;
			$_version = $this->formatVersion( $version[1] );
			if( version_compare( $this->FormatedSubVersion, $_version, '>=' ) )
				continue;
			
			$updates[(int) $version[1]] = $method;				
		endforeach;
		
		ksort( $updates );
		
		return $updates;
	}

	/* = = */
	private function UpgradeStorageVersion( $version )
	{
		\update_option( $this->StorageVar, ( $this->MajorVersion ? $this->MajorVersion .'.'. $version : $version ) );
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