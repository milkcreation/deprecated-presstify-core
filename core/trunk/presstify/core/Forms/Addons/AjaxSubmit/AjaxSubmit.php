<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\AjaxSubmit;

class AjaxSubmit extends \tiFy\Core\Forms\Addons\Factory
{
    /* = CONSTRUCTEUR = */
    public function __construct()
    {        
        // Définition de l'identifiant
        $this->ID = 'ajax_submit';
        
        // Définition des fonctions de callback
        $this->callbacks = array(
            'handle_redirect'           => array( 'function' => array( $this, 'cb_handle_redirect' ), 'order' => 99 ),
            'form_after_display'        => array( $this, 'cb_form_after_display' )
        );
        
        add_action( 'wp_ajax_tify_forms_ajax_submit', array( $this, 'wp_ajax' ) );
        add_action( 'wp_ajax_nopriv_tify_forms_ajax_submit', array( $this, 'wp_ajax' ) );
        
        parent::__construct();
    }
    
    /* = CALLBACKS = */
    /** == Court-circuitage de la redirection après traitement == **/
    public function cb_handle_redirect( &$redirect )
    {
        $redirect = false;
    }    
    
    /** == Mise en queue du script de tratiement dans le footer == **/
    /* = @todo Limiter le nombre d'instance à 1 execution par formulaire =*/
    public function cb_form_after_display( $form )
    {    
        if( defined( 'DOING_AJAX' ) )
            return;
        
        $ID           = $form->getID();
        $html_id      = '#'. $form->getAttr( 'form_id' );
        
        $wp_footer = function() use ( $ID, $html_id )
        {
            ?><script type="text/javascript">/* <![CDATA[ */

            // @todo : tester de permettre de desactiver ex: $( document ).off( 'tify_forms.ajax_submit.success', tify_forms_ajax_submit_success ); **/     
            var tify_forms_ajax_submit_init     = function( e, data, ID )
                {

                },
                tify_forms_ajax_submit_before   = function( e, ID )
                {
                    $( e.target ).append( '<div class="tiFyForm-Overlay tiFyForm-Overlay--'+ ID +'" />' );
                },

                tify_forms_ajax_submit_success  = function( e, html, ID )
                {
                    $( e.target ).empty().html( html );
                },
                
                tify_forms_ajax_submit_after    = function( e, ID )
                {
                    
                };

            jQuery( document ).ready( function($){
                // Définition des variables        
                var ID          = '<?php echo $ID;?>',
                    $wrapper    = $( '#tiFyForm-'+ ID );
                                
                // Déclaration des événements
                /// A l'intialisation des données de la requête Ajax
                $( document ).on( 'tify_forms.ajax_submit.init', tify_forms_ajax_submit_init );
                /// Avant le lancement de la requête Ajax
                $( document ).on( 'tify_forms.ajax_submit.before', tify_forms_ajax_submit_before );
                /// Au retour de la requête Ajax 
                $( document ).on( 'tify_forms.ajax_submit.success', tify_forms_ajax_submit_success );
                /// Après le retour de la requête Ajax
                $( document ).on( 'tify_forms.ajax_submit.after', tify_forms_ajax_submit_after );    

                // Requête Ajax
                $( document ).on( 'submit', '<?php echo $html_id;?>', function(e){            
            		e.stopPropagation();
                	e.preventDefault();         

                    // Formatage des données
                    var data = new FormData(this);
                    /// Action Ajax 
                    data.append( 'action', 'tify_forms_ajax_submit' );
                    /// Traitement des fichiers
                    $( 'input[type="file"]', $(this) ).each( function(u, v){
                        if(  v.files !== undefined ){
                        	data.append( $(this).attr('name'), v.files );
                        }
                    });

                    // Evenement de traitement des données de la requête
                    $wrapper.trigger( 'tify_forms.ajax_submit.init', data, ID );
                    
                    $.ajax({
                        url             : tify_ajaxurl,
                        data            : data,
                        type            : 'POST',
                        dataType        : 'json',
                        processData     : false,
                        contentType     : false,
                        cache           : false,                       
                        beforeSend      : function(){
                            $wrapper.trigger( 'tify_forms.ajax_submit.before', ID );
                        },
                        success         : function( resp ){
                            $wrapper.trigger( 'tify_forms.ajax_submit.success', resp.data.html, ID );
                        },
                        complete        : function(){
                            $wrapper.trigger( 'tify_forms.ajax_submit.after', ID );  
                        }                    
                    });        
                    
                    return false;
                });            
            });
            /* ]]> */</script><?php
        };        
        add_action( 'wp_footer', $wp_footer, 99 );
    }
    
    /* = Traitement ajax = */
    final public function wp_ajax()
    {
        do_action( 'tify_form_loaded' );         
        wp_send_json_success( array( 'html' => $this->form()->display() ) );
    }
}