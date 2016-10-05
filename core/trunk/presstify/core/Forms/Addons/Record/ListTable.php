<?php
namespace tiFy\Core\Forms\Addons\Record;

use \tiFy\Core\Admin\Model\ListTable\ListTable as tiFYCoreAdminModelListTable;
use \tiFy\Core\Forms\Forms;
use \tiFy\Core\Forms\Addons;


class ListTable extends tiFYCoreAdminModelListTable
{
	/* = ARGUMENTS = */
	// 
	private $activeForms	= array();
	// 
	private $Form		= null;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Liste des formulaires actifs
		$this->activeForms = Addons::activeForms( 'record' );		
		
		// Définition de la vue filtré
		if( ! empty( $_REQUEST['form_id'] ) && Forms::has( $_REQUEST['form_id'] ) ) :
			$this->Form = Forms::get( $_REQUEST['form_id'] );
		elseif( count( $this->activeForms ) === 1 ) :
			$this->Form = current( $this->activeForms );
		endif;	
	}
	
	/* = DECLARATION DES PARAMETRES = */
	/** == Définition des colonnes de la table == **/
	public function set_columns()
	{
		$cols = array(
			'cb' 			=> "<input id=\"cb-select-all-1\" type=\"checkbox\" />",
			'form_infos'	=> __( 'Formulaire' )			
		);
		
		if( $this->Form ) :
			foreach( $this->Form->fields() as $field ) :
				if( ! $field->getAddonAttr( 'record', 'show_column', false ) )
					continue;
				$cols[$field->getSlug()] = $field->getLabel();					
			endforeach;		
		endif;
			
		return $cols;
	}
	
	/** == Définition des actions sur un élément == **/
	public function set_row_actions()
	{
		return array( 'delete' );
	}
	
	/** == Définition des actions groupées == **/
	public function set_bulk_actions()
	{
		return array( 'delete' => __( 'Supprimer' ) );
	}
	
	/* = TRAITEMENT = */
	/** == Récupération des éléments == **/
	public function parse_query_arg_form_id() 
	{					
		// Définition 
		if( $this->Form )
			$this->QueryArgs['form_id'] =  $this->Form->getID();
	}
	
	/* = INTERFACE D'AFFICHAGE = */
	/** == Liste de filtrage du formulaire courant == **/
	public function extra_tablenav( $which ) 
	{
		if( count( $this->activeForms ) < 2 )
			return;
	
			
		$output = "<div class=\"alignleft actions\">";
		if ( 'top' == $which ) :
			$output  .= "\t<select name=\"form_id\" autocomplete=\"off\">\n";
			$output  .= "\t\t<option value=\"0\" ". selected( ! $this->Form, true, false ).">". __( 'Tous les formulaires', 'tify' ) ."</option>\n";
			foreach( (array) $this->activeForms as $form ) :
				$output  .= "\t\t<option value=\"". $form->getID() ."\" ". selected( ( $this->Form && ( $this->Form->getID() == $form->getID() ) ), true, false ) .">". $form->getTitle() ."</option>\n";
			endforeach;
			$output  .= "\t</select>";

			$output  .= get_submit_button( __( 'Filtrer', 'tify' ), 'secondary', false, false );
		endif;
		$output .= "</div>";

		echo $output;
	}
	
	/** == Contenu des colonnes par défaut == **/
	public function column_default( $item, $column_name )
	{
		//var_dump( $this->View->getDb()->Primary );
		return $this->View->getDb()->meta()->get( $item->ID, $column_name );
	}
	
	/** == Colonne des informations d'enregistrement == **/
	public function column_form_infos( $item )
	{
		$form = Forms::get( $item->form_id );
		
		$output  = $form->getTitle();
		$output .= "<ul style=\"margin:0;font-size:0.8em;font-style:italic;color:#666;\">";
		$output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'Identifiant : %s', 'tify' ), $item->record_session ) ."</li>";
		$output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'posté le : %s', 'tify' ), $item->record_date ) ."</li>";
		$output .= "</ul>";
		
		return $output;
	}
}