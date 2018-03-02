<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\AjaxSubmit;

class AjaxSubmit extends \tiFy\Core\Forms\Addons\Factory
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {        
        // Définition de l'identifiant
        $this->ID = 'ajax_submit';
        
        // Définition des fonctions de callback
        $this->callbacks = [
            'handle_redirect'           => ['function' => [$this, 'cb_handle_redirect'], 'order' => 99],
            'form_after_display'        => [$this, 'cb_form_after_display']
        ];
        
        add_action('wp_ajax_tify_forms_ajax_submit', [$this, 'wp_ajax']);
        add_action('wp_ajax_nopriv_tify_forms_ajax_submit', [$this, 'wp_ajax']);

        parent::__construct();
    }

    /**
     * DECLENCHEURS
     */
    /** == Court-circuitage de la redirection après traitement == **/
    public function cb_handle_redirect(&$redirect)
    {
        $redirect = false;
    }    
    
    /** == Mise en queue du script de tratiement dans le footer == **/
    /* = @todo Limiter le nombre d'instance à 1 execution par formulaire =*/
    public function cb_form_after_display($form)
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

                tify_forms_ajax_submit_response  = function( e, resp, ID )
                {
                    if( resp.data.html !== undefined )
                        $( e.target ).empty().html( resp.data.html );
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
                /// Au retour de la requête Ajax avec succès 
                $( document ).on( 'tify_forms.ajax_submit.response', tify_forms_ajax_submit_response );
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
                            $wrapper.trigger( 'tify_forms.ajax_submit.response', resp, ID );                                 
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
        remove_filter(current_filter(), __METHOD__);
        do_action( 'tify_form_loaded' );
        
        $data = array( 'html' => $this->form()->display() );
        
        if( $this->form()->handle()->hasError() ) :
            wp_send_json_error( $data );
        else :
            wp_send_json_success( $data );
        endif;
        
    }
}