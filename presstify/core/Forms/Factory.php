<?php
namespace tiFy\Core\Forms;

use \tiFy\Core\Forms\Forms;

class Factory
{
	/* ARGUMENTS */
	// Controleurs
	/// Formulaires
	private $Form;	
	
	/* = CONSTRUCTEUR */
	public function __construct( $id, $attrs = array() )
	{
		$this->Form = new \tiFy\Core\Forms\Form\Form( $id, $attrs );
		
		add_action( 'tify_form_loaded', array( $this, 'tify_form_loaded') );
	}
		
	/* = DECLENCHEURS = */
	/** == Chargement complet des  formulaire == **/
	final public function tify_form_loaded()
	{
	    tify_control_enqueue( 'notices' );
	    
	    Forms::setCurrent( $this );	    
		$this->Form->handle()->proceed();		
		Forms::resetCurrent();
	}
	
	/* = CONTROLEURS = */
	/** == Récupération de la classe de formulaire == **/
	final public function getForm()
	{
		return $this->Form;
	}
	
	/** == Récupération d'un champs == **/
	final public function getField( $field_slug )
	{
		return $this->getForm()->getField( $field_slug );
	}
	
    /** == Traitement des variables de requête à la soumission == **/
    final public function parseQueryVar( $field_slug, $value )
    {
        if( method_exists( $this, 'parse_query_var_' . $field_slug ) ) :
            return call_user_func( array( $this, 'parse_query_var_' . $field_slug ), $value );
        endif;        
        
        return $value;
    }
    
    /** == Vérification d'intégrité des variables de requêtes == **/ 
    final public function checkQueryVar( $field_obj, $errors )
	{	
        if( method_exists( $this, 'check_query_var_' . $field_obj->getSlug() ) ) :
            return call_user_func( array( $this, 'check_query_var_' . $field_obj->getSlug() ), $errors, $field_obj );
        endif;        
        
        return $errors;
    }
    
	/* = SURCHARGE = */	
	/** == Affichage du formulaire == **/
	public function display( $echo = false )
	{
	    $output = $this->getForm()->display();
		if( $echo )
			echo $output;
		
		return $output;
	}	
}