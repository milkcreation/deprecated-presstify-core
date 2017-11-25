<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Actions
{
    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'action courante a éxecuter
     *
     * @return string
     */
    public function current_action()
    {
        if ($this->getRequestQueryVar('action', -1) != -1) :
            return $_REQUEST['action'];
        endif;
        if ($this->getRequestQueryVar('action2', -1) != -1) :
            return $_REQUEST['action2'];
        endif;

        return false;
    }

    /**
     * Récupération de l'identifiant de qualification de l'élément à traiter
     *
     * @return null|string
     */
    protected function getActionItemIndexes()
    {
        if(!$item_indexes = $this->current_item_index()) :
        elseif(!is_array($item_indexes)) :
            $item_indexes = array_map('trim', explode(',', $item_indexes));
        endif;

        return $item_indexes;
    }

    /**
     * Récupération de l'identifiant de qualification de la clef de sécurisation d'une action.
     *
     * @param string $action_name Identifiant de qualification de l'action
     * @param null|string|array $item_indexes Valeur de l'index d'un, plusieurs voire aucun élément
     *
     * @return string
     */
    public function getActionNonce($action_name, $item_indexes = null)
    {
        if(!$item_indexes) :
        elseif(!is_array($item_indexes)) :
            $item_indexes = array_map('trim', explode(',', $item_indexes));
        endif;

        if (!$item_indexes || (count($item_indexes) === 1)) :
            $nonce_action = $this->getParam('singular') . '-' . $action_name;
        else :
            $nonce_action = $this->getParam('plural') . '-' . $action_name;
        endif;

        if ($item_indexes && count($item_indexes) === 1) :
            $nonce_action .= '-' . reset($item_indexes);
        endif;

        return sanitize_title($nonce_action);
    }

    /**
     * Vérification de provenance de l'appel à l'action
     *
     * @param string $action_name Identifiant de qualification de l'action
     * @param string|array $item_indexes Valeur de l'index d'un, plusieurs éléments
     */
    public function checkActionNonce($action_name, $item_indexes)
    {
        if(!is_array($item_indexes)) :
            $item_indexes = array_map('trim', explode(',', $item_indexes));
        endif;

        if (count($item_indexes) === 1) :
            check_admin_referer($this->getActionNonce($action_name, reset($item_indexes)));
        else :
            check_admin_referer($this->getActionNonce($action_name, $item_indexes));
        endif;
    }

    /**
     * Traitement de l'action d'activation d'éléments
     *
     * @return void
     */
    protected function process_action_activate()
    {
        if (!$item_indexes = $this->getActionItemIndexes()) :
            return;
        endif;

        if (!$db = $this->getDb()) :
            return;
        endif;

        if ($db->getPrimary() !== $this->getParam('item_index_name')) :
            return;
        endif;

        if(!$db->isCol('active')) :
            return;
        endif;

        // Traitement des éléments
        foreach ($item_indexes as $item_index) :
            $db->handle()->update($item_index, ['active' => 1]);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg(['message' => 'activated'], $sendback);

        wp_redirect($sendback);
        exit;
    }

    /**
     * Traitement de l'action de désactivation d'éléments
     *
     * @return void
     */
    protected function process_action_deactivate()
    {
        if (!$item_indexes = $this->getActionItemIndexes()) :
            return;
        endif;

        if (!$db = $this->getDb()) :
            return;
        endif;

        if ($db->getPrimary() !== $this->getParam('item_index_name')) :
            return;
        endif;

        if(!$db->isCol('active')) :
            return;
        endif;

        // Traitement des éléments
        foreach ($item_indexes as $item_index) :
            $db->handle()->update($item_index, ['active' => 0]);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg(['message' => 'deactivated'], $sendback);

        wp_redirect($sendback);
        exit;
    }

    /**
     * Traitement de l'action de suppression d'éléments
     *
     * @return void
     */
    protected function process_action_delete()
    {
        if (!$item_indexes = $this->getActionItemIndexes()) :
            return;
        endif;

        if (!$db = $this->getDb()) :
            return;
        endif;

        if ($db->getPrimary() !== $this->getParam('item_index_name')) :
            return;
        endif;

        // Traitement des éléments
        foreach ($item_indexes as $item_index) :
            $db->handle()->delete_by_id($item_index);

            /// Conservation du statut original
            if ($db->hasMeta()) :
                $db->meta()->delete_all($item_index);
            endif;
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg(['message' => 'deleted'], $sendback);

        wp_redirect($sendback);
        exit;
    }

    /**
     * Traitement de l'action de mise à la corbeille d'éléments
     *
     * @return void
     */
    protected function process_action_trash()
    {
        if (!$item_indexes = $this->getActionItemIndexes()) :
            return;
        endif;

        if (!$db = $this->getDb()) :
            return;
        endif;

        if ($db->getPrimary() !== $this->getParam('item_index_name')) :
            return;
        endif;

        if(!$db->isCol('status')) :
            return;
        endif;

        // Traitement des éléments
        foreach ($item_indexes as $item_index) :
            /// Conservation du statut original
            if ($db->hasMeta()) :
                $original_status = $db->select()->cell_by_id($item_index, 'status');
                $db->meta()->update($item_index, '_trash_meta_status', $original_status);
            endif;

            $db->handle()->update($item_index, ['status' => 'trash']);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg(['message' => 'trashed'], $sendback);

        wp_redirect($sendback);
        exit;
    }

    /**
     * Traitement de l'action de restauration d'éléments
     *
     * @return void
     */
    protected function process_action_untrash()
    {
        if (!$item_indexes = $this->getActionItemIndexes()) :
            return;
        endif;

        if (!$db = $this->getDb()) :
            return;
        endif;

        if ($db->getPrimary() !== $this->getParam('item_index_name')) :
            return;
        endif;

        if(!$db->isCol('status')) :
            return;
        endif;

        // Traitement des éléments
        foreach ($item_indexes as $item_index) :
            // Récupération du statut à affecter
            if ($db->hasMeta() && ($status = $db->meta()->get($item_index, '_trash_meta_status', true))) :
            else :
                $status = ($_default = $db->getColAttr('status', 'default')) ? $_default : 'publish';
            endif;

            /// Suppréssion de la métadonnées de conservation de statut original
            if ($db->hasMeta()) :
                $db->meta()->delete($item_index, '_trash_meta_status');
            endif;

            $db->handle()->update($item_index, ['status' => 'publish']);
        endforeach;

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg(['message' => 'untrashed'], $sendback);

        wp_redirect( $sendback );
        exit;
    }
}