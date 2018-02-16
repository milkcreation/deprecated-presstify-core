<?php
namespace tiFy\Core\Forms\Form;

class Notices
{
    /* = ARGUMENTS = */
    // CONFIGURATION
    /// Type d'erreur possible
    private $Codes                  = array(
        'error', 'info', 'success', 'warning'
    );
        
    // Paramètres
    /// Formulaire de référence
    private $Form                   = null;
    
    /// Attributs de configuration
    private $Attrs                  = array();
    
    /// Cartographie des messages
    private $MessagesMap            = array();    
    
    /// Liste des notices
    private $Notices                = array();
    
    ///
    private $Datas                  = null;    
    
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

    /**
     * Définition de notice
     *
     * @param $code
     * @param string $message
     * @param $data
     *
     * @return void
     */
    public function add($code, $message, $data = '')
    {
        $uid = uniqid();

        $this->Notices[$code][$uid] = $message;

        if (!empty($data)) :
            $defaults = [
                'slug'  => null,
                'type'  => '',
                'check' => '',
                'order' => 0
            ];
            $data = wp_parse_args((array)$data, $defaults);

            // Données protégées
            if (isset($data['_uid'])) :
                unset($data['_uid']);
            endif;
            $data['_uid'] = $uid;

            if (isset($data['_message'])) :
                unset($data['_message']);
            endif;
            $data['_message'] = $message;

            $this->Datas[$code][$uid] = $data;
        endif;
    }

    /** == Nombre de notice == **/
    public function count( $code = 'error' )
    {
        if( isset( $this->Notices[$code] ) ) :
            return count( $this->Notices[$code] );
        else :
            return 0;
        endif;
    }
    
    /** == Récupération de notice selon les attributs de data == **/
    public function getByData( $code = 'error', $args = array() )
    {
        if( ! isset( $this->Datas[ $code ] ) )
            return array();
        
        $errors = array();
        foreach( $this->Datas[ $code ] as $data ) :
           $exists = @array_intersect( $data, $args );

           if( $exists != $args )
               continue;
                            
           $errors[] = $data;
        endforeach;
       
       return $errors;
    }
            
    /** == Affichage des notices == **/ 
    public function display( $code = 'error' )
    {
        $attrs = array( 'id', 'class', 'dismissible' );
        
        if( $_args = $this->Attrs[$code] ) :
            foreach( (array) $_args as $k => $_arg ) :
                if( !in_array( $k, $attrs ) )
                    continue;
                $args[$k] = $_arg;
            endforeach;
        endif;

        $text  = "";
        if( $this->has( $code ) ) :
            $notices = $this->get( $code );
            $count = count( $notices );
            $order = array();
            foreach( $notices as $uid => $notice ) :
              if( ! empty( $this->Datas[$code][$uid]['order'] ) ) :
                $order[$uid] = (int) $this->Datas[$code][$uid]['order'];
              else :
                $order[$uid] = ++$count; 
              endif;
            endforeach;
            
            array_multisort( $order, $notices );
            
            $text .= "<ol class=\"tiFyForm-NoticesMessages tiFyForm-NoticesMessages--{$code}\">\n";
            foreach( (array) $notices as $message ) :
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