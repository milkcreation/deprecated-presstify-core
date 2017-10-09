<?php 
namespace tiFy\Core\Templates\Traits\Form;

trait Params
{
    /** == Formatage du nom d'un paramètre == **/
    protected function sanitizeParam( $param )
    {
        return implode( array_map( 'ucfirst', explode( '_', $param ) ) );
    }

    /** == Récupération de la liste de paramètres permis == **/
    protected function allowedParams()
    {
        return $this->ParamsMap;
    }

    /** == Définition d'un paramètre == **/
    protected function setParam( $param, $value )
    {
        $param = self::sanitizeParam( $param );
        if( in_array( $param, $this->allowedParams() ) ) :
            $this->{$param} = $value;
        endif;
    }

    /** == Récupération d'un paramètre == **/
    protected function getParam( $param, $default = '' )
    {
        $param = self::sanitizeParam( $param );
        if( ! in_array( $param, $this->allowedParams() ) )
            return $default;

        if( method_exists( $this, 'get'. $param ) ) :
            return call_user_func( array( $this, 'get'. $param ) );
        elseif( isset( $this->{$param} ) ) :
            return $this->{$param};
        endif;

        return $default;
    }

    /** == Initialisation des paramètres de configuration de la table == **/
    protected function initParams()
    {
        $this->ParamsMap = $this->set_params_map();

        foreach( (array) $this->allowedParams() as $param ) :
            if( ! method_exists( $this, 'initParam' . $param ) )
                continue;
            call_user_func( array( $this, 'initParam' . $param ) );
        endforeach;
    }

    /**
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        return $this->ParamsMap;
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
		      $fields = array();	
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
	public function initParamNewItem()
	{
		$this->NewItem = (bool) $this->set_add_new_item();
	}	
	
	/** == Attributs par défaut de l'élément == **/
	public function initParamDefaultItemArgs()
	{
		$defaults = array( $this->db()->getPrimary() => 0 );
		
		$this->DefaultItemArgs = wp_parse_args( (array) $this->set_default_item_args(), $defaults );
	}
	
	/** == Initialisation des actions sur un élément de la liste == **/
	public function initParamPageTitle()
	{
		$this->PageTitle = ( $page_title = $this->set_page_title() ) ? $page_title : $this->label( 'all_items' );
	}
}