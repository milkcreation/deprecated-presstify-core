<?php
/**
 * @name tiFy Users Library
 * @desc Librairies de commande d'aides à la manipulation des utilisateurs sous Wordpress
 * @package presstiFy
 * @namespace tiFy\Lib\Users
 * @subpackage Lib
 * @version 1.1
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Lib\User;

class User extends \tiFy\App
{
    /**
     * Récupération d'une liste d'utilisateur sous la forme d'un tableau indexé au format clé => valeur
     * Tous les arguments de requête sont disponibles à l'exception de fields qui est court-circuité par la méthode
     *
     * @param string $key Champ utilisé comme clé du tableau de sortie
     * @param string $value Champ utilisé comme valeur du tableau de sortie
     * @param array $query_args Liste des arguments de requête (hors fields)
     *
     * @return array
     */
    public static function userQueryKeyValue($key = 'ID', $value = 'display_name', $query_args = [])
    {
        // Limite la liste des champs de récupération à la clé et la valeur
        $query_args['fields'] = [$key, $value];

        // Lancement de la requête
        $user_query = new \WP_User_Query($query_args);

        // Bypass - Aucun résultat ne correspond à la requête
        if (empty($user_query->get_results())) :
            return [];
        endif;

        $users = [];
        foreach($user_query->get_results() as $user) :
            $users[$user->{$key}] = $user->{$value};
        endforeach;

        return $users;
    }

    /**
     * Récupération du nom d'affichage d'un rôle
     *
     * @return string
     */
    public static function roleDisplayName($role)
    {
        $wp_roles = new \WP_Roles();
        $roles = $wp_roles->get_names();

        if (!isset($roles[$role])) :
            return $role;
        endif;

        return \translate_user_role($roles[$role]);
    }
}