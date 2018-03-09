<?php

namespace tiFy\Core\Forms\Form;

class Callbacks extends AbstractDependency
{
    /**
     * Liste des fonctions de rappel déclarées
     * @var array
     */
    public $Registered = [];

    /**
     * Appel d'une méthode de court-circuitage
     *
     * @param string $hookname Nom de qualification du court-circuitage
     * @param array $args Liste des arguments passés dans l'appel de la méthode
     *
     * @return null|callable
     */
    public function call($hookname, $args = [])
    {
        if ($this->getForm()->factory() !== null) :
            $this->getForm()->factory()->call($hookname, $args);
        endif;

        if (!isset($this->Registered[$hookname])) :
            return null;
        endif;

        ksort($this->Registered[$hookname]);

        foreach ($this->Registered[$hookname] as $priority => $functions) :
            foreach ($functions as $attrs) :
                if (is_callable($attrs)) :
                    $callable = $attrs;
                elseif (isset($attrs['cb'])) :
                    $callable = $attrs['cb'];
                else :
                    continue;
                endif;
                call_user_func_array($callable, $args);
            endforeach;
        endforeach;
    }

    /**
     * Définition d'une méthode de rappel de court-circuitage
     *
     * @param string $hookname Nom de qualification du court-circuitage
     * @param array $callable Méthode ou fonction de rappel
     * @param int Ordre de priorité d'éxecuction
     *
     * @return void
     */
    public function set($hookname, $callable, $priority = 10)
    {
        $this->Registered[$hookname][(int)$priority][] = $callable;
    }

    /** == Définition des fonctions de callback == **/
    private function _set($hookname, $id, $callback, $priority, $type = 'core')
    {
        $this->Registered[$hookname][$priority][] = ['id' => $id, 'type' => $type, 'cb' => $callback];
    }

    /** == Définition des fonctions de rappel des addons == **/
    public function setAddons($hookname, $addon_id, $callback, $priority = 10)
    {
        $this->_set($hookname, $addon_id, $callback, $priority, 'addons');
    }

    /** == Définition des fonctions de rappel des contrôleurs == **/
    public function setCore($hookname, $controller_id, $callback, $priority = 10)
    {
        $this->_set($hookname, $controller_id, $callback, $priority, 'core');
    }

    /** == Définition des fonctions de rappel des types de champ == **/
    public function setFieldType($hookname, $field_type_id, $callback, $priority = 10)
    {
        $this->_set($hookname, $field_type_id, $callback, $priority, 'field_type');
    }
}