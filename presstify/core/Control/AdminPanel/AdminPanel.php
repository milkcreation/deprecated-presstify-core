<?php 
namespace tiFy\Core\Control\AdminPanel;

use \tiFy\Statics\Tools as tiFyStaticsTools;

/**
 * @since 1.0.344 Première version du panneau d'administration
 * @todo Gestion des niveaux (parentalité) des noeuds
 * @example Ajout de noeuds et de champs dans le panneau
 *          'nodes' => array(
 *              'general    => array(
 *                  'title' => __('Informations générales', 'tify),
 *                  'fields => array(
 *                      array(
 *                          'type'  => 'input',
 *                          'title' => __('Titre du champ', 'tify'),
 *                          'desc'  => __('Ceci est la description du champ affiché en italique en dessous de celui-ci', 'tify'),
 *                          'name'  => 'tify_meta_post[fieldname]'
 *                          'value' => 'fieldvalue'
 *                      ),
 *                      array(
 *                          'type'  => 'custom',
 *                          'title' => __('Champ personnalisé', 'tify'),
 *                          'desc'  => __('Ceci est la description du champ personnalisé...', 'tify' ),
 *                          'cb'    => array( 'my_callback_function', array( $arg1; $arg2 ),
 *                          'name'  => 'tify_meta_post[fieldname]'
 *                          'value' => 'fieldvalue'
 *                      )
 *                  )
 *              )
 *          )
 * @author pitcho
 *
 */
class AdminPanel extends \tiFy\Core\Control\Factory
{
	// 	
	/**
	 * Identifiant de la classe	
	 * @var string
	 */
	protected $ID = 'admin_panel';
	
	/**
	 * Instance
	 * @var int
	 */
	private static $Instance;
	
	/**
	 * CONSTRUCTEUR
	 */
	public function __construct()
	{
		parent::__construct();
		
		self::$Instance++;
	}
	
	/**
	 * DÉCLENCHEURS
	 */
	/**
	 * Initialisation de WordPress
	 */
	final public function init()
	{
		wp_register_style('tify_control-admin_panel', self::tFyAppAssetsUrl('AdminPanel.css', get_class()), array('dashicons'), 170705);
		wp_register_script('tify_control-admin_panel', self::tFyAppAssetsUrl('AdminPanel.js', get_class()), array('jquery', 'jquery-ui-widget'), 170705);
	}
	
	/**
	 * Mise en file des scripts
	 */
	public function enqueue_scripts()
	{	
	    tify_control_enqueue('switch');
		wp_enqueue_style('tify_control-admin_panel');
		wp_enqueue_script('tify_control-admin_panel');
	}	
	
	/**
	 * MÉTHODES
	 */
	/**
	 * Traitement des arguments d'un noeud
	 * @param array $node Noeud
	 * @return array Noeud traité
	 */
	private static function parseNode($node = array())
	{
	    $defaults = array(
	        'title'    => '',
	        'fields'   => array()
	    );
	    return tiFyStaticsTools::parseArgsRecursive($node, $defaults);
	}
	
	/**
	 * Traitement des arguments des champs
	 * @param array $fields Champs
	 * @return array Champs traités
	 */
	private static function parseFields($fields = array())
	{
	    $defaults = array(
	        'type'         => 'input', // input | select | switch | items | custom
	        'title'        => '',
	        'desc'         => '',
	        'cb'           => '',
	        'id'           => '',
	        'class'        => '',
	        'name'         => '',
	        'value'        => '',
	        'placeholder'  => '',
	        'attrs'        => array(),
	        'choices'      => array(),
	        'choice_none'  => '',
	        // Contrôleur tiFy
	        'control_args' => array(),
	        // Ajout d'élèments ordonnable
	        'items'        => array(
	            'orderable'    => true,
	            'fields'       => array()
	        )
	    );
	    foreach($fields as $n => $field) :
	       $fields[$n] = tiFyStaticsTools::parseArgsRecursive($field, $defaults);
	    endforeach;
	    return $fields;
	}
	
	/**
	 * Affichage d'un champ texte simple
	 * @param array $field Champ
	 * @return string Code d'affichage du champ
	 */
	private static function displayInputField($field)
	{
	    if(!$field['id']) :
	       $field['id'] = "tiFyControl-adminPanelItemFieldInput--".self::$Instance;
	    endif;
	    $output = "<input 
                    type=\"text\" 
                    id=\"{$field['id']}\"
                    class=\"tiFyControl-adminPanelItemFieldInput {$field['class']}\"
                    name=\"{$field['name']}\"
                    value=\"{$field['value']}\"";
	    if(!empty($field['placeholder'])) :
	        $output .= "placeholder=\"{$field['placeholder']}\"";
	    endif;
	    foreach($field['attrs'] as $k => $v) :
            $output .= "data-{$k}=\"{$v}\"";	    
	    endforeach;
        $output .= ">";
	    return $output;
	}
	
	/**
	 * Affichage d'un champ caché
	 * @param array $field Champ
	 * @return string Code d'affichage du champ
	 */
	private static function displayInputHiddenField($field)
	{
	    if(!$field['id']) :
	       $field['id'] = "tiFyControl-adminPanelItemFieldInputHidden--".self::$Instance;
	    endif;
	    $output = "<input 
                    type=\"hidden\" 
                    id=\"{$field['id']}\"
                    name=\"{$field['name']}\"
                    value=\"{$field['value']}\"";
	    foreach($field['attrs'] as $k => $v) :
            $output .= "data-{$k}=\"{$v}\"";	    
	    endforeach;
        $output .= ">";
	    return $output;
	}
	
	/**
	 * Affichage d'un champ de type "liste déroulante"
	 * @param array $field Champ
	 * @return string Code d'affichage du champ
	 */
	private static function displayDropdownField($field)
	{
	    if(!$field['id']) :
	       $field['id'] = "tiFyControl-adminPanelItemFieldDropdown--".self::$Instance;
	    endif;
	    $output = "<select
                    id=\"{$field['id']}\" 
                    class=\"tiFyControl-adminPanelItemFieldDropdown {$field['class']}\"
                    name=\"{$field['name']}\"";
	    foreach($field['attrs'] as $k => $v) :
            $output .= "data-{$k}=\"{$v}\"";	    
	    endforeach;
        $output .= ">\n";
        
        if(!empty($field['choices'])) :
            if($field['choice_none']) :
                $output .= "\t<option value=\"-1\" ".selected($field['value'], '-1', false).">{$field['choice_none']}</option>\n";
            endif;
            $n = 1;
            foreach($field['choices'] as $value => $choice) :
                if(!empty($field['value'])) :
                    $selected = selected($field['value'], $value, false);
                elseif($n===1) :
                    $selected = selected(1,1,false);
                else :
                    $selected = null;
                endif;
                $output .= "\t<option value=\"".esc_attr($value)."\" {$selected}>{$choice}</option>";
                $n++;
            endforeach;
        else :
            $output .= "\t<option value=\"-1\" ".selected($field['value'], '-1', false).">{$field['choice_none']}</option>\n";
        endif;
        
        $output .= "</select>";
	    return $output;
	}
	
	/**
	 * Affichage d'un champ de type "switch"
	 * @param array $field Champ
	 * @return string Code d'affichage du champ
	 */
    private static function displaySwitchField($field)
	{
	    if(!$field['id']) :
	       $field['id'] = "tiFyControl-adminPanelItemFieldSwitch--".self::$Instance;
	    endif;
	    $defaults = array(
	        'id'				=> $field['id'],
			'class'				=> "tiFyControl-adminPanelItemFieldSwitch {$field['class']}",
		    'name'              => $field['name'],
			'checked'			=> $field['value'],
			'echo'				=> 0
	    );
	    $args = tiFyStaticsTools::parseArgsRecursive($field['control_args'], $defaults);
	    return tify_control_switch($args);
	}
	
	/**
	 * Affichage d'un champ de type "Ajout d'élèment"
	 * @todo À développer
	 * @param array $field Champ
	 * @return string Code d'affichage du champ
	 */
	private static function displayItemsField($field)
	{
	    $fields = self::parseFields($field['items']['fields']);
	    $items = $field['value'];
	    $is_empty = empty($field['value']) ? 'is-empty' : null;
		$count = (!empty($field['value'])) ? count($field['value']) : 0;
		$output = "<div class=\"tiFyControl-adminPanelItemChoices\">";
		$output .= "\n\t<button type=\"button\" class=\"tiFyControl-adminPanelItemChoicesButton button-secondary add-new-menu-item js-add-choice\"><span class=\"tiFyControl-adminPanelItemChoicesButtonAdd is-choices-plus dashicons dashicons-plus\"></span>".__( 'Ajouter un choix', 'tify' )."</button><span class=\"tiFyControl-adminPanelItemChoicesButtonSpinner is-choices-spinner spinner\"></span>";
		$output .= "\n\t<div class=\"tiFyControl-adminPanelItemChoicesInner\">";
		$output .= "\n\t\t<span class=\"tiFyControl-adminPanelItemChoicesInnerOverlay is-choices-overlay\"></span>";
		$output .= "\n\t\t<ul class=\"tiFyControl-adminPanelItemChoicesItems is-choices-list {$is_empty}\" data-title=\"".__( 'Aucun choix ajouté', 'tify' )."\" data-count=\"{$count}\">";
		if(!empty($field['value'])) :
			foreach($field['value'] as $n => $v) :
				$output .= self::itemRender($field['name'], $fields, $n, $v, false);
		    endforeach;
		endif;
		$output .= "\n\t\t</ul>";
		$output .= "\n\t</div>";
		$output .= "\n</div>";    
		
		return $output;
	}
	
	/**
	 * Affichage d'un élément
	 * @todo À développer
	 * @param string $name Nom d'enregistrement de l'élément
	 * @param array $fields Champs de l'élément
	 * @param int $n Index de l'élément
	 * @param array $values Valeurs de l'élément
	 * @param string $echo Affichage ou retour de fonction
	 * @return string Code d'affichage de l'élément
	 */
    private static function itemRender($name, $fields = array(), $n, $values = array(), $echo = false )
	{
		$defaults = array(
			'label'			=> sprintf(__('Choix %s', 'tify'), ($n+1))
		);
		$_values  = wp_parse_args($values, $defaults);
		
		$output = "<li class=\"tiFyControl-adminPanelItemChoicesItem is-choice\">";
		$output .= "\n\t<div class=\"tiFyControl-adminPanelItemChoicesItemHeader js-deploy-choice\">";
		$output .= "\n\t<span class=\"is-choice-label\">{$_values['label']}</span>";
		$output .= "\n\t<span class=\"tiFyControl-adminPanelItemChoicesItemDeploy dashicons dashicons-arrow-down\"></span>";
		$output .= "\n\t</div>";
		$output .= "\n\t<div class=\"tiFyControl-adminPanelItemChoicesItemSettings\">";
		$output .= "\n\t\t<div class=\"tiFyControl-adminPanelItemChoicesItemSettingsInner\">";
		
		if(!empty($fields)) :
		    foreach($fields as $field) :
		        /**
		         * @todo Appel d'un champ
		         */
		    endforeach;
		endif;
		/*
		$output .= "\n\t\t\t<label for=\"name\" class=\"tiFyControl-adminPanelItemChoicesItemLabel\">".__( 'Intitulé du choix', 'tify' )."</label>";
		$output .= "\n\t\t\t<input type=\"text\" data-save=\"#{$index}-choices-{$n}-label\" class=\"tiFyControl-adminPanelItemChoicesItemInput js-type-label\" value=\"{$_values['label']}\">";
		$output .= "\n\t\t\t<input type=\"hidden\" id=\"{$index}-choices-{$n}-label\" name=\"tify_meta_post[{$this->name}][{$index}][choices][{$n}][label]\" value=\"{$_values['label']}\">";
		$output .= "\n\t\t\t<span class=\"tiFyControl-adminPanelItemChoicesItemDesc\">";
		$output .= __( 'Texte utilisé pour l\'affichage des enregistrements et les messages d\'erreurs.', 'tify' );
		$output .= "\n\t\t\t</span>";*/
		$output .= "\n\t\t\t<button type=\"button\" class=\"tiFyControl-adminPanelItemChoicesItemDelete js-delete-choice\">".__( 'Supprimer', 'tify' )."</button>";
		$output .= "\n\t\t</div>";
		$output .= "\n\t</div>";
		$output .= "\n</li>";
		
		if( $echo ) :
			echo $output;
		else :
			return $output;
		endif;
	}
	
	/**
	 * Affichage d'un champ d'élément
	 * @todo À développer
	 * @param array $field Champ
	 */
	private static function displayItemField($field)
	{
	    
	}
	
	/**
	 * Affichage d'un champ
	 * @param array $field Champ
	 * @return string Code d'affichage du champ
	 */
	private static function displayField($field)
	{
	    $output = "<div class=\"tiFyControl-adminPanelItemField\">\n";
	    if($field['title']) :
	        $output .= "\t<label class=\"tiFyControl-adminPanelItemFieldLabel\">{$field['title']}</label>\n";
	    endif;
	    
	    switch($field['type']) :
    	    case 'input' :
	            $output .= self::displayInputField($field);
    	        break;
    	    case 'hidden' :
    	        $output .= self::displayInputHiddenField($field);
    	        break;
    	    case 'dropdown' :
    	        $output .= self::displayDropdownField($field);
    	        break;
    	    case 'switch' :
    	        $output .= self::displaySwitchField($field);
    	        break;
    	    case 'items' :
    	        //$output .= self::displayItemsField($field);
    	        break;
    	    case 'custom' :
    	        if(!empty($field['cb'])) :
    	            $output .= call_user_func($field['cb'], $field);
    	        endif;
    	        break;
	    endswitch;
	    
	    if($field['desc']) :
	        $output .= "\t<span class=\"tiFyControl-adminPanelItemFieldDesc\">{$field['desc']}</span>\n";
	    endif;
	    $output .= "</div>";
	    
	    return $output;
	}
	
	/**
	 * Affichage du contrôleur
	 * @param array $args Arguments du contrôleur
	 * @param string $echo Affchage ou retour de fonction
	 * @return string Code d'affichage du contrôleur
	 */
	public static function display( $args = array(), $echo = true )
	{	
	    $defaults = array(
	        'id'       => 'tiFyControl-adminPanel--'.self::$Instance,
	        'class'    => '',
	        'toggle'   => '',
	        'opened'   => false,
	        'attrs'    => array(),
	        'controls' => array(
	            'save'     => __('Enregistrer', 'tify'),
	            'remove'   => false
	        ),
	        'header'   => array(
	            'title'    => __("Panneau d'administration", 'tify'),
	            'icon'     => false
	        ),
	        'nodes'    => array()
	    );
	    
	    $args = tiFyStaticsTools::parseArgsRecursive( $args, $defaults );
		extract( $args );
		
		$output = "";
		
		/**
		 * Bouton de bascule
		 */
		if(!$toggle) :
		    $toggle = "tiFyControl-adminPanel--".self::$Instance;
		    $output .= "<button type=\"button\" class=\"open\" id=\"{$toggle}\"></button>";
		    $toggle = '#'.$toggle;
		elseif( preg_match( '/^[^#.]([\w.-]+)/', $toggle ) ):
	        $toggle = '#'.$toggle;
		endif;
		$opened = $opened ? 'tiFyControl-adminPanel--opened' : null;
	    $output .= "<div class=\"tiFyControl-adminPanel {$opened} {$class}\" id=\"{$id}\" data-toggle=\"{$toggle}\"";
	    if (!empty($attrs)) :
    	    foreach ($attrs as $k => $v) :
                $output .= "data-{$k}=\"{$v}\"";	    
    	    endforeach;
	    endif;
	    $output .= ">\n";
		$output .= "\t<ul class=\"tiFyControl-adminPanelControls\">\n";
		$output .= "\t\t<li class=\"tiFyControl-adminPanelControlsItem tiFyControl-adminPanelControlsItem--cancel\">\n";
		$output .= "\t\t\t<button type=\"button\" class=\"tiFyControl-adminPanelControlsItemCancel dashicons dashicons-no-alt\"></button>\n";
		$output .= "\t\t</li>\n";
	    $output .= "\t\t<li class=\"tiFyControl-adminPanelControlsItem tiFyControl-adminPanelControlsItem--save\">\n";
	    if($controls['remove']) :
    		$output .= "\t\t\t<a href=\"#\" class=\"tiFyControl-adminPanelControlsItemButton tiFyControl-adminPanelControlsItemButton--remove\">\n";
    		$output .= $controls['remove'];
    		$output .= "\t\t\t</a>\n";
	    endif;
	    if($controls['save']) :
    		$output .= "\t\t\t<button type=\"button\" class=\"tiFyControl-adminPanelControlsItemButton tiFyControl-adminPanelControlsItemButton--save button button-primary\">\n";
    		$output .= $controls['save'];
    		$output .= "\t\t\t</button>\n";
	    endif;
	    $output .= "\t\t\t\t<span class=\"tiFyControl-adminPanelControlsItemSpinner spinner\"></span>";
		$output .= "\t\t</li>\n";
		$output .= "\t</ul>\n";
		$output .= "\t<div class=\"tiFyControl-adminPanelWrap\">\n";
		$output .= "\t\t<div class=\"tiFyControl-adminPanelHeader\">\n";
		if($header['icon']) :
		    $output .= "\t\t\t<span class=\"tiFyControl-adminPanelHeaderIcon\">{$header['icon']}</span>\n";
		endif;
		$output .= "\t\t\t<span class=\"tiFyControl-adminPanelHeaderTitle\">{$header['title']}</span>\n";
		$output .= "\t\t</div>\n";
		
		/**
		 * Noeuds
		 */
		if(!empty($nodes)) :
    		$output .= "\t\t<ul class=\"tiFyControl-adminPanelItems\">\n";
		    foreach( $nodes as $node ) :
		        $node = self::parseNode($node);
		        if(!$node['title'])
		            continue;
		        $output .= "\t\t\t<li class=\"tiFyControl-adminPanelItem\">\n";
		        $output .= "\t\t\t\t<h3 class=\"tiFyControl-adminPanelItemTitle tiFyControl-adminPanelItemSlide\">{$node['title']}</h3>\n";
		        $output .= "\t\t\t\t\t<div class=\"tiFyControl-adminPanelItemPanel\">\n";
		        $output .= "\n\t\t\t\t\t<div class=\"tiFyControl-adminPanelItemPanelHeader\">";
        		$output .= "\n\t\t\t\t\t\t<button type=\"button\" class=\"tiFyControl-adminPanelItemPanelBack dashicons dashicons-arrow-left-alt2\"></button>";
        		$output .= "\n\t\t\t\t\t\t\t<span class=\"tiFyControl-adminPanelItemPanelTitle\">".__( 'Personnalisation', 'tify' )."</span>";
        		$output .= "\n\t\t\t\t\t\t\t<span class=\"tiFyControl-adminPanelItemPanelSubtitle\">{$node['title']}</span>";
        		$output .= "\n\t\t\t\t\t</div>";
        		$output .= "\n\t\t\t\t\t<div class=\"tiFyControl-adminPanelItemPanelContent\">";
        		if(!empty($node['fields'])):
        		     $fields = self::parseFields($node['fields']);
        		     foreach($fields as $field) :
        		         $output .= self::displayField($field);
        		     endforeach;
        		endif;
        		$output .= "\n\t\t\t\t\t</div>";
		        $output .= "\t\t\t\t\t</div>\n";
		        $output .= "\t\t\t</li>\n";
		    endforeach;
    		$output .= "\t\t</ul>\n";
		endif;
		
		$output .= "\t</div>\n";
		$output .= "</div>";
		
		if( $echo )
			echo $output;
			
		return $output;
	}
}