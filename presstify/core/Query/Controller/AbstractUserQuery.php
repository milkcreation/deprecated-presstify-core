<?php

namespace tiFy\Core\Query\Controller;

use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractUserQuery
{
    use TraitsApp;

    /**
     * Role(s) utilisateur Wordpress
     * @var string|array
     */
    protected $objectName = [];

    /**
     * Controleur de données d'un élément
     * @var string
     */
    protected $itemController = 'tiFy\Core\Query\Controller\UserItem';

    /**
     * Controleur de données d'une liste d'éléments
     * @var string
     */
    protected $listController = 'tiFy\Core\Query\Controller\UserList';

    /**
     * Récupération du(es) role(s) utilisateur Wordpress du controleur
     *
     * @return string|array
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Récupération du controleur de données d'un élément
     *
     * @return string
     */
    public function getItemController()
    {
        return $this->itemController;
    }

    /**
     * Récupération du controleur de données d'une liste d'éléments
     *
     * @return string
     */
    public function getListController()
    {
        return $this->listController;
    }

    /**
     * Récupération d'un élément
     *
     * @param string|int|\WP_User|null $id Login utilisateur Wordpress|Identifiant de qualification Wordpress|Objet utilisateur Wordpress|Utilisateur Wordpress courant
     *
     * @return null|object|UserItemInterface
     */
    public function get($id = null)
    {
        if (!$id) :
            $user = \wp_get_current_user();
        elseif (is_int($id)) :
            $user = get_userdata($id);
        elseif (is_string($id)) :
            return $this->getBy('login', $id);
        else :
            $user = $id;
        endif;

        if (!$user instanceof \WP_User) :
            return null;
        endif;

        if ($this->getObjectName() && !array_intersect($user->roles, (array) $this->getObjectName())) :
            return null;
        endif;

        $name = 'tify.query.user.' . $user->ID;
        if (! $this->appHasContainer($name)) :
            $controller = $this->getItemController();
            $this->appAddContainer($name, new $controller($user));
        endif;

        return $this->appGetContainer($name);
    }

    /**
     * Récupération d'un élément selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut name.
     * @param string $value Valeur de l'attribut
     *
     * @return null|object|UserItemInterface
     */
    public function getBy($key = 'login', $value)
    {
        $args = [
            'search' => $value,
            'number' => 1
        ];

        switch($key) :
            default :
            case 'user_login' :
            case 'login':
                $args['search_columns'] = ['user_login'];
                break;
            case 'user_email' :
            case 'email' :
                $args['search_columns'] = ['user_email'];
                break;
        endswitch;

        $user_query = new \WP_User_Query($args);
        if ($users = $user_query->get_results()) :
            return $this->get(reset($users));
        endif;

        return null;
    }

    /**
     * Récupération des données d'une liste d'élément selon des critères de requête
     *
     * @param array $query_args Liste des arguments de requête
     *
     * @return array|UserItemInterface[]
     */
    public function getList($query_args = [])
    {
        $user_query = new \WP_User_Query($query_args);

        if ($user_query->get_total()) :
            $items = array_map([$this, 'get'], $user_query->get_results());
        else :
            $items = [];
        endif;

        $controller = $this->getListController();

        return new $controller($items);
    }
}

