<?php
namespace tiFy\Core\Ui\User;

use tiFy\Core\Ui\Ui;

class Factory extends \tiFy\Core\Ui\Factory
{
    /**
     * Récupération de la liste des classes de rappel des gabarits de traitement externe
     *
     * @return null|\tiFy\Core\Ui\User\Factory[]
     */
    final public function getHandleList()
    {
        if (!$handle_templates = $this->getAttr('handle')) :
            return [];
        endif;

        $handle = [];
        foreach ($handle_templates as $task => $id) :
            if ($factory = Ui::getUser($id)) :
                $handle[$task] = $factory;
            endif;
        endforeach;

        return $handle;
    }
}