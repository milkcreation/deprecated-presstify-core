<?php
/**
 * @Overridable
 */
namespace tiFy\Core\Forms\Addons\Record;

use \tiFy\Core\Forms\Forms;
use \tiFy\Core\Forms\Addons;

class ListTable extends \tiFy\Core\Templates\Admin\Model\ListTable\ListTable
{
    /**
     * Liste des formulaires actifs
     */
    protected $Forms          = array();

    /**
     * Formulaire courant
     */
    protected $Form           = null;

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Liste des formulaires actifs
        $forms = Addons::activeForms( 'record' );
       
        foreach( $forms as $id => $form ) :
            $this->Forms[$form->getID()] = $form;
        endforeach;

        // Définition de la vue filtré
        if( ! empty( $_REQUEST['form_id'] ) && isset( $this->Forms[$_REQUEST['form_id']] ) ) :
            $this->Form = $this->Forms[$_REQUEST['form_id']];
        elseif( count( $this->Forms ) === 1 ) :
            $this->Form = current( $this->Forms );
        endif;
    }
    
    /**
     * PARAMETRAGE
     */
    /**
     * Définition des vues filtrées
     */
    public function set_views()
    {
        return array(
            'any'        => array(
                'label'                => __( 'Tous (hors corbeille)', 'tify' ),
                'current'            => empty( $_REQUEST['record_status'] ) ? true : null,
                'add_query_args'    => array( 'record_status' => array( 'publish' ) ),
                'remove_query_args'    => array( 'record_status' ),
                'count'                => $this->count_items( array( 'record_status' => array( 'publish' ) ) )
            ),
            'trash'         => array( 
                'label'                => __( 'Corbeille', 'tify' ),
                'add_query_args'    => array( 'record_status' => 'trash' ),
                'count'                => $this->count_items( array( 'record_status' => 'trash' ) ),
                'hide_empty'        => true
            )
        );
    }
    
    /**
     * Définition des colonnes de la table
     */
    public function set_columns()
    {
        $cols = array(
            'cb'             => "<input id=\"cb-select-all-1\" type=\"checkbox\" />",
            'form_infos'    => __( 'Formulaire' )            
        );

        if( $this->Form ) :
            foreach($this->Form->fields() as $field) :
                if( ! $col = $field->getAddonAttr( 'record', 'column', false ) )
                    continue;
                $cols[$field->getSlug()] = ( is_bool( $col ) ) ? $field->getLabel() : $col;
            endforeach;
        endif;

        return $cols;
    }
    
    /**
     * Définition des colonnes de prévisualisation
     */
    public function set_preview_columns()
    {
        if( ! $this->Form ) 
            return array();
        
        $cols = array();
        foreach( $this->Form->fields() as $field ) :
            if( ! $col = $field->getAddonAttr( 'record', 'preview', false ) )
                continue;
            $cols[$field->getSlug()] = ( is_bool( $col ) ) ? $field->getLabel() : $col;
        endforeach;

        return $cols;
    }
    
    /**
     * Définition du mode de prévisualisation
     */
    public function set_preview_mode()
    {
        return 'row';
    }
    
    /**
     * Définition des données passées dans la requête Ajax de prévisualisation 
     */
    public function set_preview_ajax_datas()
    {
        if( ! $this->Form ) 
            return array();
        
        return array(
            'form_id'    => $this->Form->getId()  
        );
    }
    
    /**
     * Définition des actions groupées
     */
    public function set_bulk_actions()
    {
        if( empty( $_REQUEST['record_status'] ) ) :
            $bulk_actions['trash'] = __( 'Mettre à la corbeille', 'tify' );
        else :
            $bulk_actions['untrash'] = __( 'Restaurer', 'tify' );
            $bulk_actions['delete'] = __( 'Supprimer définitivement', 'tify' );
        endif;
        
        return $bulk_actions;
    }
    
    /**
     * Définition des actions sur un élément
     */
    public function set_row_actions()
    {
        $actions = array();

        if( $this->Form ) :
            foreach( $this->Form->fields() as $field ) :
                if( ! $col = $field->getAddonAttr( 'record', 'preview', false ) )
                    continue;
                array_push( $actions, 'previewinline' );  break;
            endforeach;        
        endif;

        array_push( $actions, 'trash', 'untrash', 'delete' );

        return $actions;
    }
    
    /**
     * Définition de l'ajout automatique des actions sur l'élément à la colonne principale
     */
    public function set_handle_row_actions()
    {
        return false;
    }
    
    /**
     * TRAITEMENT
     */
    /**
     * Données de récupération des éléments
     */
    public function parse_query_arg_form_id() 
    {
        if( $this->Form ) :
            $this->QueryArgs['form_id'] =  $this->Form->getID();
        endif;
    }
    
    /**
     * AFFICHAGE
     */
    /**
     * Interface de navigation complémentaire
     */
    public function extra_tablenav( $which ) 
    {
        if( count( $this->Forms ) <= 1 )
            return;
        
        // Liste de filtrage du formulaire courant
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
    
    /**
     * Contenu des colonnes par défaut
     */
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
    
    /**
     * Colonne - Informations d'enregistrement
     */
    public function column_form_infos( $item )
    {
        $form_title = ( $form = Forms::get( $item->form_id ) ) ? $form->getForm()->getTitle() : __( '(Formulaire introuvable)', 'tify' );
                
        $output  = "<strong>". $form_title ."</strong>";
        $output .= "<ul style=\"margin:0;font-size:0.8em;font-style:italic;color:#666;\">";
        $output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'Identifiant: %s', 'tify' ), $item->form_id ) ."</li>";
        $output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'Session : %s', 'tify' ), $item->record_session ) ."</li>";
        $output .= "\t<li style=\"margin:0;\">" . sprintf( __( 'posté le : %s', 'tify' ), $item->record_date ) ."</li>";
        $output .= "</ul>";
        
        $actions = $this->RowActions;
        
        if( $item->record_status == 'trash' ) :
            unset( $actions['trash'] );            
            $row_actions =  $this->row_actions( $this->item_row_actions( $item, array_keys( $actions ) ) );
        else :
            unset( $actions['untrash'], $actions['delete'] );
            $row_actions =  $this->row_actions( $this->item_row_actions( $item, array_keys( $actions ) ) );
        endif;
    
        return sprintf('%1$s %2$s', $output, $row_actions );
    }
    
    /**
     * Contenu de l'aperçu par défaut
     */
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
}