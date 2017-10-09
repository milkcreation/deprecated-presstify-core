<?php
namespace tiFy\Core\Labels;

class Labels extends \tiFy\App\Core
{
    /**
     * Liste des classes de rappel des intitulès
     * @var \tiFy\Core\Labels\Factory[]
     */
    public static $Factories	= [];

    /**
     *  CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des actions de déclenchement
        self::tFyAppActionAdd('init', null, 9);

        // Déclaration
        foreach ((array)self::tFyAppConfig() as $id => $args) :
            self::Register($id, $args);
        endforeach;
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
	final public function init()
	{		
		do_action( 'tify_labels_register' );
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration == **/
	public static function Register( $id, $args = array() )
	{
		return self::$Factories[$id] = new Factory( $args );		
	}
	
	/** == Récupération == **/
	public static function Get( $id )
	{
		if( isset( self::$Factories[$id] ) )
			return self::$Factories[$id];
	}
}