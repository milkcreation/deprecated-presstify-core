<?php
namespace tiFy\Core\Ui\Admin\Templates\Form\Traits;

trait Actions
{
    /** == Éxecution de l'action - mise à jour == **/
    protected function process_bulk_action_update()
    {
        check_admin_referer($this->get_item_nonce_action($this->current_action(), $this->current_item()));

        $data = $this->parse_postdata($_POST);

        $sendback = remove_query_arg(array('action', 'action2'), wp_get_referer());
        $sendback = add_query_arg(array($this->db()->Primary => $this->current_item()), $sendback);
        if (is_wp_error($data)) :
            $sendback = add_query_arg(array('message' => $data->get_error_code()), $sendback);
        else :
            $this->db()->handle()->record($data);
            $sendback = add_query_arg(array('message' => 'updated'), $sendback);
        endif;

        wp_redirect($sendback);
        exit;
    }

    /** == Éxecution de l'action - mise à la corbeille == **/
    protected function process_bulk_action_trash()
    {
        check_admin_referer($this->get_item_nonce_action($this->current_action(), $this->current_item()));

        // Traitement de l'élément
        /// Conservation du statut original
        if ($this->db()->hasMeta() && ($original_status = $this->db()->select()->cell_by_id($this->item_id,
                'status'))) {
            $this->db()->meta()->update($this->item_id, '_trash_meta_status', $original_status);
        }
        /// Modification du statut
        $this->db()->handle()->update($this->item_id, array('status' => 'trash'));

        // Traitement de la redirection
        $sendback = remove_query_arg(array('action', 'action2'), wp_get_referer());
        $sendback = add_query_arg('message', 'trashed', $sendback);

        wp_redirect($sendback);
        exit;
    }
}