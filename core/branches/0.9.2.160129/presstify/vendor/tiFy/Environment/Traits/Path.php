<?php
namespace tiFy\Environment\Traits;

trait Path
{
	/* = ARGUMENTS = */
	// Informations sur la classe
	private $ReflectionClass;
	
	// Chemin absolu vers le fichier de déclaration de la classe fille
	private $Filename;

	// Chemin absolu vers le dossier racine de la classe fille
	private $Dirname;

	// Nom du dossier racine de la classe fille
	private $Basename;	
	
	// Url absolue vers  la racine de la classe fille
	private $Url;
	
	// Paramètres de tiFy
	private $Params;
		
	// Liste des arguments pouvant être récupérés
	private $GetPathAttrs		= array( 'Filename', 'Dirname', 'Basename', 'Url', 'Params' );
				
	/** == == **/
	private function setReflectionClass( $class )
	{
		 $this->ReflectionClass = new \ReflectionClass( $class );
	}
		
	/** == Définition du chemin absolu vers le fichier de déclaration de la classe fille == **/
	private function setFilename( $class )
	{
		if( ! $this->ReflectionClass )
			$this->setReflectionClass( $class );
			
		return $this->Filename = $this->ReflectionClass->getFileName();
	}
	
	/** == Définition du chemin absolu vers le dossier racine de la classe fille == **/
	private function setDirname( $class )
	{
		$filename = $this->Filename ? $this->Filename : $this->setFilename( $class );
			
		return $this->Dirname = dirname( $filename );
	}
	
	/** == Définition du nom du dossier racine de la classe fille == **/
	private function setBasename( $class )
	{				
		return $this->Basename = basename( $this->setDirname( $class ) );
	}
	
	/** == Définition de l'url absolue vers le dossier racine de la classe fille == **/
	private function setUrl( $class )
	{		
		$dirname = $this->Dirname ? $this->Dirname : $this->setDirname( $class );
	
		return $this->Url = untrailingslashit( site_url() . '/'. \tify_get_relative_path( $dirname ) );
	}
	
	/** == Définition de l'url absolue vers le dossier racine de la classe fille == **/
	private function setParams( $class )
	{
		global $tiFy;					
		return $this->Params = $tiFy->params;
	}
	
	/* = RECUPERATION DE DONNÉES = */
	/** == Récupération des données accessibles == **/
	public function __get( $name ) 
	{			
		if ( in_array( $name, $this->GetPathAttrs ) ) :
			if( ! $this->{$name} ) :
				if( method_exists( $this, 'set'. $name ) ) :						
					return call_user_func( array( $this, 'set'. $name ), $this );
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
					return call_user_func( array( $this, 'set'. $name ), $this );
				endif;
			endif;
			return isset( $this->{$name} );
		endif;
		
		return false;
	}	
}