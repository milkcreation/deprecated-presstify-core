<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Helpers
{
    /**
     * Récupération des la liste des arguments de requête de l'url de la page d'affichage du gabarit
     *
     * @return array
     */
    public function getBaseUriQueryVars()
    {
        $query_vars = [];
        if ($base_uri = $this->getAttr('base_uri')) :
            parse_str(parse_url($base_uri, PHP_URL_QUERY), $query_vars);
        endif;

        return $query_vars;
    }

    /**
     * Récupération de la liste des arguments de requête
     *
     * @return array
     */
    public function getRequestQueryVars()
    {
        $query_vars = [];
        if (isset($_REQUEST)) :
            $query_vars = $_REQUEST;
        endif;

        return $query_vars;
    }

    /**
     * Récupération d'un argument de requête
     *
     * @param string $var Identifiant de qualification de la variable de requête à récuperer
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    public function getRequestQueryVar($var, $default = '')
    {
        if (!$query_vars = $this->getRequestQueryVars()) :
            return $default;
        endif;

        if (isset($query_vars[$var])) :
            return $query_vars[$var];
        endif;

        return $default;
    }

    /**
     * Récupération de l'argument de requête d'identification des éléments
     *
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    public function getRequestItemIndex()
    {
        if (!$item_index_name = $this->getParam('item_index_name')) :
            return;
        endif;

        return $this->getRequestQueryVar($item_index_name, null);
    }
}