<?php
namespace tiFy\Core\Forms\Addons\Record;

use \tiFy\Core\Forms\Forms;
use \tiFy\Core\Forms\Addons;

class ListTable extends \tiFy\Core\Templates\Admin\Model\ListTable\ListTable
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
		
		add_action( 'wp_ajax_tiFyCoreFormsAddonsRecordListTableInlinePreview', array( $this, 'wp_ajax' ) );
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
				if( ! $col = $field->getAddonAttr( 'record', 'column', false ) )
					continue;
				$cols[$field->getSlug()] = ( is_bool( $col ) || ! isset( $col['title'] ) ) ? $field->getLabel() : $col['title'];
			endforeach;		
		endif;
			
		return $cols;
	}
	
	/** == Définition des actions sur un élément == **/
	public function set_row_actions()
	{
		return array( 
			'inline-preview' => array(
				'title'	=> 	__( 'Aperçu de l\'élément', 'tify' ),
				'label'	=> __( 'Afficher' ),
				'class'	=> 'inline-preview'
			),
			'delete'
		);
	}
	
	/** == Définition des actions groupées == **/
	public function set_bulk_actions()
	{
		return array( 'delete' => __( 'Supprimer' ) );
	}
	
	/* = DECLENCHEURS = */
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{
		wp_enqueue_script( 'tiFyCoreFormsAddonsRecordListTable', self::getUrl( get_class() ) .'/ListTable.js', array( 'jquery'), '161130', true );
	}
	
	/** == == **/
	public function wp_ajax()
	{
		if( ! $item = $this->db()->select()->row_by_id( $_REQUEST['record_id'] ) )
			wp_send_json_error( __( 'Impossible de définir le formulaire', 'tify' ) );
				
		if( ! $this->Form = Forms::get( $item->form_id ) )
			wp_send_json_error( __( 'Le formulaire n\'existe pas', 'tify' ) );	
			
		$output  = "";	
		$output .= "\n<table class=\"form-table\">";
		$output .= "\n\t<tbody>";					
								
		foreach( $this->Form->fields() as $field ) :
			if( ! $preview = $field->getAddonAttr( 'record', 'preview', false ) )
				continue;
				
			$label = ( is_bool( $preview ) || ! isset( $preview['label'] ) ) ? $field->getLabel() : $preview['title'];	
				
			$output .= "\n\t\t<tr valign=\"top\">";
			if( $label ) :
				$output .= "\n\t\t\t<th scope=\"row\">";
				$output .= "\n\t\t\t\t<label><strong>{$label}</strong></label>";
				$output .= "\n\t\t\t</th>";			
				$output .= "\n\t\t\t<td>";
			else :
				$output .= "\n\t\t\t<td colspan=\"2\">";
			endif;
			
			if( ! empty( $preview['cb'] ) && is_callable( $preview['cb'] ) ) :
				$output .= call_user_func( $preview['cb'], $item );
			elseif( method_exists( $this, 'preview_' . $field->getSlug() ) ) :
				$output .= call_user_func( array( $this, 'preview_' . $field->getSlug() ), $item );
			else :
				$output .= $this->preview_default( $item, $field->getSlug() );
			endif;
			$output .= "\n\t\t\t</td>";
		endforeach;
		$output .= "\n\t</tbody>";
		$output .= "\n</table>";
		$output .= "\n<div class=\"clear\"></div>";
				
		echo $output;
		exit;
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
		if( ! $field = $this->Form->getField( $column_name ) )
			return;
		$values = (array) $this->db()->meta()->get( $item->ID, $column_name );
		
		foreach( $values as &$value ) :		
			if( ( $choices = $field->getAttr( 'choices' ) ) && isset( $choices[$value] ) ) :
				$value = $choices[$value];
			endif;
		endforeach;
		
		return join( ', ', $values );		
	}
	
	/** == Colonne des informations d'enregistrement == **/
	public function column_form_infos( $item )
	{
		$form_title = ( $form = Forms::get( $item->form_id ) ) ? $form->getTitle() : __( '(Formulaire introuvable)', 'tify' );
				
		$output  = $form_title;
		$output .= "<ul style=\"margin:0;font-size:0.8em;font-style:italic;color:#666;\">";
		$output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'Identifiant: %s', 'tify' ), $item->form_id ) ."</li>";
		$output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'Session : %s', 'tify' ), $item->record_session ) ."</li>";
		$output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'posté le : %s', 'tify' ), $item->record_date ) ."</li>";
		$output .= "</ul>";
		
		return $output;
	}
	
	/** == Contenu de l'aperçu par défaut == **/
	public function preview_default( $item, $column_name )
	{
		if( ! $field = $this->Form->getField( $column_name ) )
			return;
		$values = (array) $this->db()->meta()->get( $item->ID, $column_name );
		
		foreach( $values as &$value ) :		
			if( ( $choices = $field->getAttr( 'choices' ) ) && isset( $choices[$value] ) ) :
				$value = $choices[$value];
			endif;
		endforeach;
		
		return join( ', ', $values );		
	}
		
	/** == == **/
	public function inline_preview()
	{
		list( $columns, $hidden ) = $this->get_column_info();
		$colspan = count($columns);
	?>
		<table style="display: none">
			<tbody id="inlinepreview">
				<tr style="display: none" class="inline-preview" id="inline-preview">
					<td class="colspanchange" colspan="<?php echo $colspan;?>">
						<h3><?php _e( 'Chargement en cours ...', 'tify' );?></h3>
					</td>
				</tr>	
			</tbody>
		</table>
	<?php	
	}
	
	/** == Rendu de la page  == **/
    public function render()
    {
    ?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->label( 'all_items' );?>
    			
    			<?php if( $this->EditBaseUri ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->EditBaseUri;?>"><?php echo $this->label( 'add_new' );?></a>
    			<?php endif;?>
    		</h2>
    		
    		<?php $this->views(); ?>
    		
    		<form method="get" action="">
    			<?php parse_str( parse_url( $this->BaseUri, PHP_URL_QUERY ), $query_vars ); ?>
    			<?php foreach( (array) $query_vars as $name => $value ) : ?>
    				<input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>" />
    			<?php endforeach;?>
    		
    			<?php $this->search_box( $this->label( 'search_items' ), $this->template()->getID() );?>
    			<?php $this->display();?>
    			<?php $this->inline_preview();?>
			</form>
    	</div>
    <?php
    }
}