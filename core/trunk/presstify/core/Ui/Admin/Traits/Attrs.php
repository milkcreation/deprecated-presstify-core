<?php
namespace tiFy\Core\Ui\Admin\Traits;

use tiFy\Core\Ui\Ui;

trait Attrs
{
    /**
     * Ecran courant d'affichage de la page
     * @var null|\WP_Screen
     */
    protected $Screen = null;

    /**
     * Définition de l'écran courant
     *
     * @param string|WP_Screen
     *
     * @return void|WP_Screen
     */
    protected function setScreen($screen)
    {
        return $this->Screen = \convert_to_screen($screen);
    }

    /**
     * Récupération de l'écran courant
     *
     * @return void|WP_Screen
     */
    protected function getScreen()
    {
        return $this->Screen;
    }

    /**
     * Récupération de la liste des classes de rappel des gabarits de traitement externe
     *
     * @return array|\tiFy\Core\Ui\Admin\Factory[]
     */
    final public function getHandleList()
    {
        if (!$handle_templates = $this->getAttr('handle')) :
            return [];
        endif;

        $handle = [];
        foreach ($handle_templates as $task => $id) :
            if ($factory = Ui::getAdmin($id)) :
                $handle[$task] = $factory;
            endif;
        endforeach;

        return $handle;
    }

    /**
     * Récupération d'une classe de rappel de gabarit de traitement externe
     *
     * @param string $task Tâche du gabarit (edit|list|import ...)
     *
     * @return null|\tiFy\Core\Ui\Admin\Factory
     */
    public function getHandle($task)
    {
        if (!$handle_list = $this->getHandleList()) :
            return;
        endif;

        if(!isset($handle_list[$task])) :
            return;
        endif;

        return $handle_list[$task];
    }
}