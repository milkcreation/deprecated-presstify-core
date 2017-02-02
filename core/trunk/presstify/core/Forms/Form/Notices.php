<?php
namespace tiFy\Core\Forms\Form;

class Notices
{
	/* = ARGUMENTS = */
	// CONFIGURATION
	/// Type d'erreur possible
	private $Codes 					= array(
		'error', 'info', 'success', 'warning'
	);
		
	// Paramètres
	/// Formulaire de référence
	private $Form					= null;
	
	/// Attributs de configuration
	private $Attrs					= array();
	
	/// Cartographie des messages
	private $MessagesMap			= array();	
	
	/// Liste des notices
	private $Notices				= array();
	
	///
	private $Datas					= null;
	
	
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\Forms\Form\Form $Form )
	{			
		// Définition du formulaire de référence
		$this->Form = $Form;
	}
	
	/* = PARAMETRAGES = */
	/** == Définition des attributs de configuration == **/
	public function setAttrs( $attrs = array() )
	{
		$this->Attrs = Helpers::parseArgs( $attrs, $this->Attrs );
		
		// 
		if( is_string( $this->Attrs['success'] ) ) :
			$MessagesMap['successful'] = $this->Attrs['success'];
		elseif( ! empty( $this->Attrs['success']['message'] ) ) :
			$MessagesMap['successful'] = $this->Attrs['success']['message'];
		endif;
				
		if( $this->Form->handle()->isSuccessful() )
			$this->add( 'success', $MessagesMap['successful'] );
	}
		    
	/* = CONTROLEURS = */
	/** == Vérifie l'existance de notice == **/
	public function has( $code = 'error' )
	{
		return ! empty( $this->Notices[ $code ] );
	}
	 
	/** == Récupération de notice == **/
	public function get( $code = 'error' )
	{
		if( isset( $this->Notices[ $code ] ) )
			return $this->Notices[ $code ];		
	}
	
	/** == Définition de notice == **/
	public function add( $code, $message, $data = '' )
	{
		$this->Notices[$code][] = $message;
		if ( ! empty( $data ) )
			$this->Notices[$code] = $data;
	}
		
	/** == Nombre de notice == **/
	public function count( $code = 'error' )
	{
		return count();		
	}
	
	/** == Affichage des notices == **/ 
	public function display( $code = 'error' )
	{
		$attrs = array( 'id', 'class', 'dismissible' );
		
		if( $_args = $this->Attrs[$code] ) :
			foreach( $_args as $k => $_arg ) :
				if( !in_array( $k, $attrs ) )
					continue;
				$args[$k] = $_arg;
			endforeach;
		endif;

		$text  = "";
		if( $this->has( $code ) ) :
			$text .= "<ol class=\"tiFyForm-NoticesMessages tiFyForm-NoticesMessages--{$code}\">\n";
			foreach( (array) $this->get( $code ) as $message ) :
				$text .= "\t<li class=\"tiFyForm-NoticesMessage tiFyForm-NoticesMessage--{$code}\">". $message ."</li>\n";
			endforeach;
			$text .= "</ol>\n";
		endif;
		
		$args['text'] = $text;
		$args['type'] = $code;
		
		$output = tify_control_notices( $args, false );
		
		return $output;		
	}	
}