<?php 
namespace tiFy\Core\Ui\Admin\Templates\UserListTable\Traits;

trait Params
{
    /**
     * Définition de la liste des roles à afficher
     *
     * @param array $roles Liste des roles à afficher définis en paramètre
     *
     * @return array
     */
    public function set_param_roles($roles = [])
    {
        return $roles;
    }

    /**
     * Initialisation de la liste des roles à afficher
     *
     * @param array $roles Liste des roles à afficher existants
     *
     * @return array
     */
    public function init_param_roles($roles = [])
    {
        if ($editable_roles = array_reverse(\get_editable_roles())) :
            $editable_roles = array_keys($editable_roles);
        endif;

        $_roles = [];
        if ($roles) :
            foreach ($roles as $role) :
                if (!in_array($role, $editable_roles)) :
                    continue;
                endif;
                array_push($_roles, $role);
            endforeach;
        else :
            $_roles = $editable_roles;
        endif;

        return $_roles;
    }
}