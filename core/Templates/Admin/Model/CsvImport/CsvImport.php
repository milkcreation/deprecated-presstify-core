<?php
namespace tiFy\Core\Templates\Admin\Model\CsvImport;

use tiFy\Lib\Csv\Csv;

class CsvImport extends \tiFy\Core\Templates\Admin\Model\FileImport\FileImport
{
    /**
     * Délimiteur de colonnes du fichier CSV
     * @var string
     */
    protected $Delimiter        = ',';

    /**
     * PARAMETRAGE
     */    
    /** 
     * Définition de la cartographie des paramètres autorisés
     *
     * @return array
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();
        array_push($params, 'Delimiter');
        
        return $params;
    }

    /**
     * Définition du délimiteur de colonnes du fichier d'import
     *
     * @return string
     */
    public function set_delimiter()
    {
        return ',';
    }

    /**
     * Définition des champs d'options du formulaire d'import
     */
    public function set_options_fields()
    {
        return [
            [
                'label' => __('Le fichier d\'import comporte une entête', 'tify'),
                'type'  => 'checkbox',
                'attrs' => [
                    'name'      => 'has_header',
                    'value'     => 'on'
                ]
            ]
        ];
    }

    /**
     * Initialisation du délimiteur du fichier d'import
     *
     * @return string
     */
    public function initParamDelimiter()
    {               
        return $this->Delimiter = $this->set_delimiter();
    }

    /**
     * TRAITEMENT
     */
    /**
     * Récupération de la réponse
     *
     * @return object[]
     */
    protected function getResponse()
    {
        $params = $this->parse_query_args();
        
        if (empty( $params['filename'])) :
            return;
        endif;

        // Attributs de récupération des données CSV
        if ($this->current_item()) :
            $attrs = array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter
            );
            $Csv = Csv::getRow(current($this->current_item()), $attrs);
        else :
            $attrs = array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter,
                'query_args'    => array(
                    'paged'         => isset( $params['paged'] ) ? (int) $params['paged'] : 1,
                    'per_page'      => $this->PerPage
                ),            
            );
            
            /// Trie
            if (! empty($params['orderby'])) :
                $attrs['orderby'] = $params['orderby'];
            endif;
            
            /// Recherche
            if (! empty($params['search'])) :
                $attrs['search'] = array(
                    array(
                        'term'      => $params['search']
                    )
                );
            endif;
            // Traitement du fichier d'import
            $Csv = Csv::getResults( $attrs );
        endif;
                
        $items = array();
        
        foreach($Csv->getItems() as $import_index => $item) :
            $item['_import_row_index'] = $import_index;
            $items[] = (object) $item;
        endforeach;
                
        $this->TotalItems = $Csv->getTotalItems();
        $this->TotalPages = $Csv->getTotalPages();

        return $items;
    }
}
