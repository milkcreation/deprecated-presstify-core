<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Actions
{
    /**
     * Récupération de l'action courante a éxecuter
     *
     * @return string
     */
    public function current_action()
    {
        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) :
            return $_REQUEST['action'];
        endif;
        if (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) :
            return $_REQUEST['action2'];
        endif;

        return false;
    }
}