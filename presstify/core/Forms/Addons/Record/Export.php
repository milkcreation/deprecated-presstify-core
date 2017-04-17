<?php
namespace tiFy\Core\Forms\Addons\Record;

use \tiFy\Core\Forms\Forms;
use \tiFy\Core\Forms\Addons;

class Export extends \tiFy\Core\Templates\Admin\Model\Export\Export
{
    /* = ARGUMENTS = */
    // Liste des formulaires actifs 
    private $Forms          = array();

    // Formulaire courant
    private $Form           = null;

    /* = CONSTRUCTEUR = */
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
    
    /* = DECLARATION DES PARAMETRES = */
    /** == Définition des colonnes de la table == **/
    public function set_columns()
    {
        $cols = array();

        if( $this->Form ) :
            foreach( $this->Form->fields() as $field ) :
                if( ! $col = $field->getAddonAttr( 'record', 'export', false ) )
                    continue;
                $cols[$field->getSlug()] = ( is_bool( $col ) ) ? $field->getLabel() : $col;
            endforeach;        
        endif;
    
        return $cols;
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