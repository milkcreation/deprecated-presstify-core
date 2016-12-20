<?php 
namespace tiFy\Core\Templates\Traits\Form;

trait Params
{
	/* = INITIALISATION DES PARAMETRES = */
	/** == Initialisation des paramètres de configuration de la table == **/
	protected function initParams()
	{
		foreach( (array) $this->ParamsMap as $param ) :
			if( ! method_exists( $this, 'initParam' . $param ) ) 
				continue;
			call_user_func( array( $this, 'initParam' . $param ) );
		endforeach;
	}
	
	/** == Initialisation de l'url de la page d'administration == **/
	public function initParamBaseUri()
	{
		$this->BaseUri = $this->getConfig( 'base_url' );
	}
	
	/** == Initialisation de l'url d'affichage de la liste des éléments == **/
	public function initParamListBaseUri()
	{
		if( $this->ListBaseUri = $this->set_list_base_url() ) :
		elseif( $edit_template = $this->getConfig( 'list_template' ) ) :
			$Method = ( $this->template()->getContext() === 'admin' ) ? 'getAdmin' : 'getFront';

			$this->ListBaseUri = \tiFy\Core\Templates\Templates::$Method( $edit_template )->getAttr( 'base_url' );
		elseif( $this->ListBaseUri = $this->getConfig( 'list_base_url' ) ) :
		endif;
	}
	
	/** == Initialisation de l'intitulé des objets traités == **/
	public function initParamPlural()
	{
		if( ! $plural = $this->set_plural() )
			$plural = $this->template()->getID();
		
		$this->Plural = sanitize_key( $plural );
	}
	
	/** == Initialisation de l'intitulé d'un objet traité == **/
	public function initParamSingular()
	{
		if( ! $singular = $this->set_singular() )
			$singular = $this->template()->getID();
		
		$this->Singular = sanitize_key( $singular );
	}
		
	/** == Initialisation des notifications == **/
	public function initParamNotices()
	{
		$this->Notices = $this->parseNotices( $this->set_notices() );
	}
	
	/** == Initialisation des statuts == **/
	public function initParamStatuses()
	{
		$this->Statuses = $this->set_statuses();
	}
		
	/** == Initialisation des champs de saisie == **/
	public function initParamFields()
	{
		/// Déclaration des colonnes de la table		
		if( $fields = $this->set_fields() ) :
		elseif( $fields = $this->getConfig( 'fields' ) ) :
		else :			
			foreach( (array)  $this->db()->ColNames as $name ) :
				$fields[$name] = $name;
			endforeach;
		endif;
		
		$this->Fields = $fields;
	}
	
	/** == Initialisation des arguments de requête == **/
	public function initParamQueryArgs()
	{
		$this->QueryArgs = (array) $this->set_query_args();
	}
	
	/** == Initialisation du paramétre de permission d'ajout d'un nouvel élément == **/
	public function initNewItem()
	{
		$this->NewItem = (bool) $this->set_add_new_item();
	}	
	
	/** == Initialisation des actions sur un élément de la liste == **/
	public function initParamPageTitle()
	{
		$this->PageTitle = ( $page_title = $this->set_page_title() ) ? $page_title : $this->label( 'all_items' );
	}
}