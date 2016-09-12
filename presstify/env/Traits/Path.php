<?php
namespace tiFy\Environment\Traits;

trait Path
{
	/* = ARGUMENTS = */
	// Informations sur la classe
	private $ReflectionClass;
	
	// Chemin absolu vers le fichier de déclaration de la classe
	private $Filename;
	
	// Url absolue vers  la racine de la classe
	protected static $_Filename;
		
	// Chemin absolu vers le dossier racine de la classe
	private $Dirname;
	
	// Chemin absolu vers le dossier racine de la classe
	protected static $_Dirname;

	// Nom du dossier racine de la classe
	private $Basename;	
	
	// Url absolue vers la racine de la classe
	private $Url;
	
	// Url absolue vers la racine de la classe
	protected static $_Url;
	
	// Chemin relatif la racine de la classe
	protected static $_RelPath;
	
	// Liste des arguments pouvant être récupérés
	private $GetPathAttrs		= array( 'ReflectionClass', 'Filename', 'Dirname', 'Basename', 'Url' );
	
	/* = CONTRÔLEURS = */
	/** == == **/
	private function setReflectionClass()
	{
		return $this->ReflectionClass = new \ReflectionClass( get_called_class() );
	}
		
	/** == Définition du chemin absolu vers le fichier de déclaration de la classe fille == **/
	private function setFilename()
	{
		return $this->Filename = self::getFilename();
	}
	
	/** == Définition du chemin absolu vers le dossier racine de la classe fille == **/
	private function setDirname()
	{
		return $this->Dirname = self::getDirname();
	}
	
	/** == Définition du nom du dossier racine de la classe fille == **/
	private function setBasename()
	{				
		return $this->Basename = basename( self::getDirname() );
	}
	
	/** == Définition de l'url absolue vers le dossier racine de la classe fille == **/
	private function setUrl()
	{		
		return $this->Url = self::getUrl();
	}
		
	/* = RECUPERATION DE DONNÉES = */
	/** == Récupération des données accessibles == **/
	public function __get( $name ) 
	{			
		if ( in_array( $name, $this->GetPathAttrs ) ) :
			if( ! $this->{$name} ) :
				if( method_exists( $this, 'set'. $name ) ) :						
					return call_user_func( array( $this, 'set'. $name ) );
				endif;
			else :
				return $this->{$name};
			endif;
		endif;
		
		return false;
	}
	
	/* = VERIFICATION DE DONNÉES = */
	/** == Vérification d'existance des données accessibles == **/
	public function __isset( $name )
	{
		if ( in_array( $name, $this->GetPathAttrs ) ) :
			if( ! $this->{$name} ) :				
				if( method_exists( $this, 'set'. $name ) ) :					
					return call_user_func( array( $this, 'set'. $name ) );
				endif;
			endif;
			return isset( $this->{$name} );
		endif;
		
		return false;
	}
	
	/** == Récupération du répertoire de déclaration de la classe == **/
	public static function getFilename( $CalledClass = null )
	{
		if( ! $CalledClass )
			$CalledClass = get_called_class();
		
		if( ! isset( self::$_Filename[$CalledClass] ) ) :
			$reflection = new \ReflectionClass( $CalledClass );
			self::$_Filename[$CalledClass] = $reflection->getFileName();
		endif;
		
		return self::$_Filename[$CalledClass];
	}
	
	/** == Récupération du répertoire de déclaration de la classe == **/
	public static function getDirname( $CalledClass = null )
	{
		if( ! $CalledClass )
			$CalledClass = get_called_class();
		
		if( ! isset( self::$_Dirname[$CalledClass] ) ) :
			self::$_Dirname[$CalledClass] = dirname( self::getFileName( $CalledClass ) );
		endif;
		
		return self::$_Dirname[$CalledClass];
	}
	
	/** == Récupération du répertoire de déclaration de la classe == **/
	public static function getUrl( $CalledClass = null )
	{
		if( ! $CalledClass )
			$CalledClass = get_called_class();
		
		if( ! isset( self::$_Url[$CalledClass] ) ) :
			self::$_Url[$CalledClass] = untrailingslashit( \tiFy\Lib\Utils::get_filename_url( self::getDirname( $CalledClass ), \tiFy\tiFy::$AbsPath ) );
		endif;
		
		return self::$_Url[$CalledClass];
	}
	
	/** == Récupération du répertoire de déclaration de la classe == **/
	public static function getRelPath( $CalledClass = null )
	{
		if( ! $CalledClass )
			$CalledClass = get_called_class();
		
		if( ! isset( self::$_RelPath[$CalledClass] ) ) :
			self::$_RelPath[$CalledClass] = untrailingslashit( \tiFy\Lib\Utils::get_rel_filename( self::getDirname( $CalledClass ), \tiFy\tiFy::$AbsPath ) );
		endif;
		
		return self::$_RelPath[$CalledClass];
	}
}