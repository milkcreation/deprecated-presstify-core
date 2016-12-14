<?php
namespace tiFy\Core\Templates;

use tiFy\Core\Db\Db;
use tiFy\Core\Labels\Labels;

class Factory extends \tiFy\Environment\App
{
	/* = ARGUMENTS = */
	// Contexte d'execution
	protected static $Context					= null;
	
	// Liste des modèles prédéfinis
	protected static $Models					= array();
	
	// PARAMETRES GENERAUX
	/// Identifiant
	protected		$TemplateID					= null;

	/// Attributs du template
	protected		$Attrs						= array();
	
	/// Classe de rappel du template
	protected		$TemplateCb					= null;
	
	/// Classe modèle du template
	protected		$ModelName					= null;

	/// Classe de rappel de la base de donnée
	protected		$DbCb						= null;

	/// Class de rappel des intitulés
	protected		$LabelCb					= null;	
	
	/* = CONSTRUCTEUR = */
	public function __construct( $id, $attrs = array() )
	{
		parent::__construct();
		
		// Définition de l'identifiant
		$this->TemplateID = $id;
		
		// Initialisation des attributs
		$this->Attrs = $attrs;	
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération de l'identifiant == **/
	final public function getID()
	{
		return $this->TemplateID;
	}
	
	/** == Récupération de la nom de la classe modèle == **/
	final public function getModelName()
	{
		if( $this->ModelName )
			return $this->ModelName; 
					
		if( ! $model = $this->getTemplateModel( $this->TemplateCb ) )
			return;

		$parts = explode( '\\', $model );
		return $this->ModelName = end( $parts );
	}
	
	/** == Récupération du modèle de template == **/
	private function getTemplateModel( $class ) 
	{
	    if ( is_object( $class ) )
	        $class = get_class( $class );
	    
	   	$context = static::$Context;
		
		$models = array_map( 
			function( $model ) use ( $context ) {
				return "tiFy\\Core\\Templates\\". ucfirst( $context ) ."\\Model\\{$model}\\{$model}";
			},
			static::$Models
		);     
	     
		if( in_array( $class, $models ) ) :
	        return $class;	        
	   elseif( $parent = get_parent_class( $class ) ) :
	    	return $this->getTemplateModel( $parent );
	    else :
	    	return false;
	    endif;
	}
	
	/** == == **/
	final public function getTemplate()
	{
		return $this->TemplateCb;
	}
	
	/** == Récupération de la liste des  attributs == **/
	final public function getAttrs()
	{
		return $this->Attrs;
	}
	
	/** == Récupération de la valeur d'un attribut == **/
	final public function getAttr( $attr, $default = '' )
	{
		if( isset( $this->Attrs[$attr] ) )
			return $this->Attrs[$attr];
		
		return $default;
	}
	
	/** == Définition de la valeur d'un attribut == **/
	final public function setAttr( $attr, $value = '' )
	{
		return $this->Attrs[$attr] = $value;
	}	
		
	/** == Récupération des intitulées == **/
	final public function getLabel( $label = '' )
	{
		if( ! is_null( $this->LabelCb ) )
			return $this->LabelCb->Get( $label );
		
		if( $this->LabelCb = Labels::Get( $this->getAttr( 'labels', $this->getID() ) ) ) :	
		else :
			$this->LabelCb = Labels::Register( $this->getID() );
		endif;

		return $this->LabelCb->Get( $label );
	}
	
	/** == Récupération de la base de données == **/
	final public function db()
	{
		if( ! is_null( $this->DbCb ) )
			return $this->DbCb;
					
		if( $this->DbCb = Db::Get( $this->getAttr( 'db', $this->getID() ) ) ) :		
		else :
			$this->DbCb = Db::Get( 'posts' );
		endif;

		return 	$this->DbCb;
	}
		
	/* = AFFICHAGE = */
	/** == Page de l'interface d'administration == **/
	final public function render()
	{
		if( $this->TemplateCb )
			return $this->TemplateCb->render();	
	}
}