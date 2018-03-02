<?php
namespace tiFy\Core\Ui\Common\Traits;

trait Notices
{
    /**
     * Récupération de la notification courante
     *
     * @return array
     */
    protected function getNotice()
    {
        if (!$current_notice_id = $this->getRequestQueryVar('message')) :
            return;
        endif;

        // Récupération de la liste des notices définies en paramètre
        $notices = $this->getParam('notices');

        // Récupération de la liste des notices prédéfinies grâce aux méthodes de surcharge
        if ($method_exists = preg_grep('#^get_notice_attrs_#', get_class_methods($this))) :
            $method_exists = array_map(function($method){ return preg_replace('#^get_notice_attrs_#', '', $method);}, $method_exists);
            foreach ($method_exists as $id) :
                if (isset($notices[$id])) :
                    continue;
                endif;
                $notices[$id] = [];
            endforeach;
        endif;

        if ($notices) :
            foreach ($notices as $id => $attrs) :
                if($id !== $current_notice_id) :
                    continue;
                endif;
                return $this->parseNoticeAttrs($id, $attrs);
            endforeach;
        endif;
    }

    /**
     * Traitement des attributs de configuration de notification
     *
     * @param string $id Identification de qualification d'une notice
     * @param array $custom_attrs Attributs personnalisés
     *
     * @return array
     */
    protected function parseNoticeAttrs($id, $custom_attrs = [])
    {
        // Attributs par défaut
        $defaults = [
            'id'          => $id,
            'message'     => '',
            'notice'      => 'info',
            'dismissible' => false
        ];

        if (method_exists($this, "get_notice_attrs_{$id}")) :
            $attrs = call_user_func([$this, "get_notice_attrs_{$id}"], $custom_attrs);
            $attrs = \wp_parse_args($attrs, $defaults);
        else :
            $attrs = \wp_parse_args($custom_attrs, $defaults);
        endif;

        return $attrs;
    }


    /**
     * Récupération de la liste des attributs de configuration de la notice d'activation d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_activated($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été activé avec succès.', 'Les éléments ont été activés avec succès', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration de la notice de création d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_created($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été créé avec succès.', 'Les éléments ont été créés avec succès', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration de la notice de désactivation d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_deactivated($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été désactivé avec succès.', 'Les éléments ont été désactivés avec succès', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration de la notice de suppression définitive d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_deleted($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été supprimé définitivement.', 'Les éléments ont été supprimés définitivement.', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration de la notice de mise à jour d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_updated($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été mis à jour.', 'Les éléments ont été mis à jour.', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration de la notice de mise à la corbeille d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_trashed($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été placé dans la corbeille.', 'Les éléments ont été placés dans la corbeille.', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration de la notice de restauration d'éléments
     *
     * @param array $attrs{
     *      Liste des attributs de configuration personnalisés de la notice

     * }
     * @param $int Nombre d'éléments concernés par la notice
     *
     * @return array
     */
    public function get_notice_attrs_untrashed($attrs = [], $n = 1)
    {
        $defaults = [
            'message'			=> _n('L\'élément a été restauré.', 'Les éléments ont été restaurés.', $n, 'tify'),
            'notice'			=> 'success',
            'dismissible' 		=> false
        ];

        return \wp_parse_args($attrs, $defaults);
    }
}