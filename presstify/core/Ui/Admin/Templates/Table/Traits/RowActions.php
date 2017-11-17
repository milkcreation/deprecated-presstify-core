<?php
namespace tiFy\Core\Ui\Admin\Templates\Table\Traits;

trait RowActions
{
    /**
     * Vérification d'existance d'une action sur un élément
     *
     * @param string $row_action_name Identifiant de qualification d'une action sur un élément
     *
     * @return bool
     */
    public function hasRowAction($row_action_name)
    {
        if (!$row_actions = $this->getParam('row_actions', [])) :
            return false;
        endif;

        return in_array($row_action_name, $row_actions);
    }

    /**
     * Récupération de l'identifiant de qualification de la clef de sécurisation d'une action sur un élément.
     *
     * @param object $item Attributs de l'élément courant
     * @param string $row_action_name Identifiant de qualification d'une action sur un élément
     * @param bool|string $suffix Personnalisation du suffixe de l'identifiant de qualification de la clef de sécurisation
     *
     * @return string
     */
    public function createRowActionNonce($item, $row_action_name, $suffix = false)
    {
        $nonce_action = $this->getParam('singular') . $row_action_name;

        if ($suffix !== false) :
            $item_index = $this->getParam('item_index');
            if (($suffix === true) && isset($item->{$item_index})) :
                $nonce_action .= $item->{$item_index};
            else :
                $nonce_action .= (string)$suffix;
            endif;
        endif;

        return $nonce_action;
    }

    /**
     * Récupération des actions sur un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $row_action_names Tableau indexés de la liste des actions
     *
     * @return string
     */
    public function getRowActions($item, $row_action_names)
    {
        $row_action_links = [];
        foreach ($row_action_names as $row_action_name) :
            if ($row_action_link = $this->getRowActionLink($item, $row_action_name)) :
                $row_action_links[$row_action_name] = $row_action_link;
            endif;
        endforeach;

        return $this->row_actions($row_action_links/*$always_visible*/);
    }

    /**
     * Récupération du lien d'action sur un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param string $row_action_name Identifiant de qualification d'une action sur un élément
     * @param array $custom_attrs {
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return string
     */
    public function getRowActionLink($item, $row_action_name, $custom_attrs = [])
    {
        // Récupération de la liste des attributs de configuration du lien d'action sur un élément
        $args = $this->getRowActionLinkAttrs($item, $row_action_name, $custom_attrs);

        /**
         * @var array $attrs
         * @var string $content
         */
        extract($args);

        $output = "";
        $output .= "<a";
        if (!empty($attrs)) :
            foreach ($attrs as $k => $v) :
                $output .= " {$k}=\"{$v}\"";
            endforeach;
        endif;
        $output .= ">{$content}</a>";

        return $output;
    }

    /**
     * Récupération de la liste des attributs de configuration du lien d'action sur un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param string $row_action_name Identifiant de qualification d'une action sur un élément
     * @param array $custom_attrs {
     *      Liste des attributs de configuration personnalisés
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function getRowActionLinkAttrs($item, $row_action_name, $custom_attrs = [])
    {
        $defaults = [
            'content'    => $row_action_name,
            'title'      => '',
            'class'      => '',
            'attrs'      => [],
            'href'       => $this->getParam('base_uri'),
            'query_args' => [],
            'nonce'      => true,
            'referer'    => true
        ];

        if (method_exists($this, "get_row_action_link_attrs_{$row_action_name}")) :
            $args = call_user_func([$this, "get_row_action_link_attrs_{$row_action_name}"], $item, $custom_attrs);
            $args = \wp_parse_args($args, $defaults);
        else :
            $args = \wp_parse_args($custom_attrs, $defaults);
        endif;

        /**
         * @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
         * @var string $title Intitulé de l'attribut title  de la balise du lien
         * @var string $class Classes CSS de l'attribut class de la balise du lien
         * @var array $attrs Liste des attributs complémentaires de la balise du lien
         * @var string $href Url de l'attribut href de la balise du lien
         * @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
         * @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
         * @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
         */
        extract($args);

        // Traitement des arguments
        // Valeur par défaut de l'url du lien
        if (!$href) :
            $href = $this->getParam('base_uri');
        endif;

        // Arguments de requête passés dans l'url
        if($query_args) :
            $href = \add_query_arg($query_args, $href);
        endif;

        // Sécurisation - Vérification de provenance
        if ($nonce) :
            if ($nonce === true) :
                $nonce = $this->getParam('base_uri');
            endif;
            $href = wp_nonce_url($href, $nonce);
        endif;

        // Url de référence
        if ($referer) :
            if ($referer === true) :
                $referer = $this->getParam('base_uri');
            endif;
            $href = \add_query_arg(['_wp_http_referer' => urlencode(wp_unslash($referer))], $href);
        endif;

        // Argument de requête par défaut
        $default_query_args = [
            'action' => $row_action_name
        ];
        if (($item_index = $this->getParam('item_index')) && isset($item->{$item_index})) :
            $default_query_args[$item_index] = $item->{$item_index};
        endif;
        $href = \add_query_arg(
            $default_query_args,
            $href
        );

        // Formatage de l'url
        $href = esc_url($href);

        $attrs['href'] = $href;
        $attrs['class'] = $class;
        $attrs['title'] = $title;

        return compact('content', 'attrs');
    }

    /**
     * Récupération de la liste des attributs de configuration du lien d'activation d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_activate($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Activer', 'tify'),
                'title'   => __('Activation de l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'activate', true),
                'attrs'   => ['style' => 'color:#006505;']
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien de désactivation d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_deactivate($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Désactiver', 'tify'),
                'title'   => __('Désactivation de l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'deactivate', true),
                'attrs'   => ['style' => 'color:#D98500;']
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien de suppression d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_delete($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Supprimer définitivement', 'tify'),
                'title'   => __('Suppression définitive de l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'delete', true),
                'attrs'   => ['style' => 'color:#a00;']
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien de duplication d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_duplicate($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Dupliquer', 'tify'),
                'title'   => __('Dupliquer l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'duplicate', true)
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien d'édition d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_edit($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Modifier', 'tify'),
                'title'   => __('Modifier l\'élément', 'tify'),
                'href'    => $this->getParam('edit_base_uri'),
                'nonce'   => false,
                'referer' => false
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien de mise à la corbeille d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_trash($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Dupliquer', 'tify'),
                'title'   => __('Dupliquer l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'trash', true)
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien de récupération d'un élément à la corbeille
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_untrash($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Dupliquer', 'tify'),
                'title'   => __('Dupliquer l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'untrash', true)
            ],
            $attrs
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien de prévisualisation d'un élément
     *
     * @param object $item Attributs de l'élément courant
     * @param array $attrs{
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title  de la balise du lien
     *      @var string $class Classes CSS de l'attribut class de la balise du lien
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien
     *      @var string $href Url de l'attribut href de la balise du lien
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien
     *      @var bool|string $nonce Activation de la création de l'identifiant de qualification de la clef de sécurisation passé en requête dans l'url du lien ou identifiant de qualification de la clef de sécurisation
     *      @var bool|string $referer Activation de l'argument de l'url de référence passée en requête dans l'url du lien
     * }
     *
     * @return array
     */
    public function get_row_action_link_attrs_preview($item, $attrs = [])
    {
        return \wp_parse_args(
            [
                'content' => __('Dupliquer', 'tify'),
                'title'   => __('Dupliquer l\'élément', 'tify'),
                'nonce'   => $this->createRowActionNonce($item, 'preview', true)
            ],
            $attrs
        );
    }
}