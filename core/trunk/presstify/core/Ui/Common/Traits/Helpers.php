<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Helpers
{
    /**
     * Récupération des arguments de requête de l'url de la page d'affichage du gabarit
     *
     * @return array
     */
    public function get_url_query_vars()
    {
        $query_vars = [];
        if ($base_uri = $this->getAttr('base_uri')) :
            parse_str(parse_url($base_uri, PHP_URL_QUERY), $query_vars);
        endif;

        return $query_vars;
    }
}