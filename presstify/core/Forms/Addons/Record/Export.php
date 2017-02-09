<?php
namespace tiFy\Core\Forms\Addons\Record;

use \tiFy\Core\Forms\Forms;
use \tiFy\Core\Forms\Addons;

class Export extends \tiFy\Core\Templates\Admin\Model\Export\Export
{
	/* = PARAMETRES = */
	// Liste des formulaires actifs 
	private $Forms		= array();
	// Formulaire courant
	private $Form		= null;
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Liste des formulaires actifs
		$forms = Addons::activeForms( 'record' );		
		
		foreach( $forms as $id => $form ) :
			if( $form->getAddonAttr( 'record', 'export', false ) ) :
				$this->Forms[$form->getID()] = $form;
			endif;	
		endforeach;
		
		// Définition de la vue filtrée
		if( $this->Forms ) :
    		if( ! empty( $_REQUEST['form_id'] ) && isset( $this->Forms[$_REQUEST['form_id']] ) ) :
    			$this->Form = $this->Forms[$_REQUEST['form_id']]->getForm();
    		else :		      
    			$this->Form = current( $this->Forms );
    		endif;
    	endif;
	}
	
	/* = PARAMETRAGE = */
	/** == Définition des colonnes de la table == **/
	public function set_columns()
	{
		$cols = array();
		
		if( $this->Form ) :		
			$cols['record_session'] = __( 'Identifiant de session', 'tify' );
			$cols['record_date'] = __( 'Date d\'enregistrement', 'tify' );
			
			foreach( $this->Form->fields() as $field ) :
				if( ! $col = $field->getAddonAttr( 'record', 'export', false ) )
					continue;
				$cols[$field->getSlug()] = ( is_bool( $col ) || ! isset( $col['title'] ) ) ? $field->getLabel() : $col['title'];
			endforeach;		
		endif;
			
		return $cols;
	}
	
	/** == Définition des arguments de requête == **/
	public function set_query_args()
	{
		$query_args = array();
		
		if( $this->Form ) :
			$query_args['form_id'] = $this->Form->getID();
		endif;

		return $query_args;
	}
	
	/* = TRAITEMENT = */
	/** == Récupération des éléments == **/
	public function prepare_items() 
	{				
		// Récupération des items
		if( $this->Forms ) :
		  parent::prepare_items();
		endif;
	}
	
	
	/* = AFFICHAGE = */
	/** == Liste de filtrage du formulaire courant == **/
	public function extra_tablenav( $which ) 
	{
		if( count( $this->Forms ) <= 1 )
			return;
				
		$output = "<div class=\"alignleft actions\">";
		if ( 'top' == $which ) :
			$output  .= "\t<select name=\"form_id\" autocomplete=\"off\">\n";
			$output  .= "\t\t<option value=\"0\" ". selected( ! $this->Form, true, false ).">". __( 'Tous les formulaires', 'tify' ) ."</option>\n";
			foreach( (array) $this->Forms as $form ) :
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
		switch( $column_name ) :
            default:
				if( ! $field = $this->Form->getField( $column_name ) )
					return;
						
        		$values = (array) $this->db()->meta()->get( $item->ID, $column_name );
		
				foreach( $values as &$value ) :		
					if( ( $choices = $field->getAttr( 'choices' ) ) && isset( $choices[$value] ) ) :
						$value = $choices[$value];
					endif;
				endforeach;
				
				return join( ', ', $values );	
				break;
			case 'record_session' :
				return $item->{$column_name};
				break;
			case 'record_date' :	
				return mysql2date( get_option( 'date_format') .' @ '.get_option( 'time_format' ), $item->{$column_name} );
				break;
		endswitch;				
	}
}