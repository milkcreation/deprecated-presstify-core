<?php 
namespace tiFy\Core\Templates\Admin\Model\AjaxListTable;

class AjaxListTable extends \tiFy\Core\Templates\Admin\Model\Table
{			
	/* = DECLENCHEURS = */
	/** == initialisation globale == **/
	public function _init(){}
			
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function _admin_enqueue_scripts()
	{		
		// Configuration	
		wp_localize_script( 
			'tiFyCoreAdminAjaxListTable',
			'tiFyCoreAdminAjaxListTable', 
			array(
				'data'			=> $this->getDatatablesData(),	
				'columns'		=> $this->getDatatablesColumns(),
				'language'		=> array( 
					'url' 		=> $this->getDatatablesLanguageUrl(),
				),
				'viewID'		=> $this->template()->getID(),
			    'action_prefix' => $this->template()->getID() .'_'. self::classShortName(), 
				'total_items'	=> $this->get_pagination_arg( 'total_items' ),
				'total_pages'	=> $this->get_pagination_arg( 'total_pages' ),
				'per_page'		=> $this->get_pagination_arg( 'per_page' )
			) 
		);
	}
		
	/* = CONFIGURATION DE DATATABLES = */
	/** == == **/
	public function getDatatablesData()
	{
		return array();
	}

	/** == Définition du fichier de traduction == **/
	private function getDatatablesLanguageUrl()
	{
		if( ! function_exists( 'wp_get_available_translations' ) )
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		
		$AvailableTranslations 	= wp_get_available_translations();		
		$version				= tify_script_get_attr( 'datatables', 'version' );
		$language_url 			= "//cdn.datatables.net/plug-ins/{$version}/i18n/English.json";
		
		if( isset( $AvailableTranslations[ get_locale() ] ) ) :
			$file = preg_replace( '/\s\(.*\)/', '', $AvailableTranslations[ get_locale() ]['english_name'] );
			if( curl_init( "//cdn.datatables.net/plug-ins/{$version}/i18n/{$file}.json" ) ) :
				$language_url = "//cdn.datatables.net/plug-ins/{$version}/i18n/{$file}.json";
			endif;
		endif;
		
		return $language_url;
	}
	
	/** == Définition des propriétés de colonnes de la table == **/
	private function getDatatablesColumns()
	{
		$columns = array();

		foreach( $this->Columns as $name => $title ) :
			array_push( 
				$columns, 
				array( 
					'data'		=> $name,
					'name'		=> $name,	
					'title'		=> $title,
					'orderable'	=> false,
					'visible'	=> ! in_array( $name, $this->HiddenColumns ),
					'className'	=> "{$name} column-{$name}". ( $this->PrimaryColumn  === $name ? ' has-row-actions column-primary' : '' )
				)
			);
		endforeach;
		
		return $columns;
	}
}