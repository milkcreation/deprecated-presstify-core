<?php
namespace tiFy\Core\Meta;

final class Post extends \tiFy\App
{
    /**
     * Liste des meta_keys declarées par type de post
     * @var array
     */
    private static $MetaKeys = [];

    /**
     * Status unique/multiples des meta_keys declarées par type de post
     * @var array
     */
    private static $Single = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->tFyAppAddAction('save_post', 'save_post', 10, 2);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Enregistrement d'un post
     *
     * @param int $post_id Identifiant de qualification du post
     * @param \WP_Post $post Objet Post Wordpress
     *
     * @return array|null|\WP_Post
     */
    final public function save_post($post_id, $post)
    {
        // Bypass - S'il s'agit d'une routine de sauvegarde automatique.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) :
            return;
        endif;

        // Bypass - Si le script est executé via Ajax.
        if (defined('DOING_AJAX') && DOING_AJAX) :
            return;
        endif;

        // Bypass - Si l'argument de requête renseignant l'indication de type de post est manquant
        if (!$post_type = self::tFyAppGetRequestVar('post_type', '', 'POST')) :
            return;
        endif;

        // Bypass - Si l'utilisateur courant n'est pas habilité  à modifié le contenu.
        if (('page' === $post_type) && !current_user_can('edit_page', $post_id)) :
            return;
        endif;
        if (('page' !== $post_type) && !current_user_can('edit_post', $post_id)) :
            return;
        endif;

        // Bypass - Si la vérification de l'existance du post est en échec.
        if ((!$post = get_post($post_id))) :
            return;
        endif;

        // Bypass - Si le type de post définit dans la requête est différent du type de post du contenu a éditer
        if($post_type !== $post->post_type) :
            return;
        endif;

        // Bypass - Si aucune métadonnée n'est déclarée le type de post
        if (empty(self::$MetaKeys[$post_type])) :
            return;
        endif;

        // Récupération des metadonnés en $_POST
        $request = self::tFyAppGetRequestVar('tify_meta_post', [], 'POST');
        foreach (self::$MetaKeys[$post_type] as $key) :
            if (!self::tFyAppHasRequestVar($key, 'POST')) :
                continue;
            endif;
            $request[$key] = self::tFyAppGetRequestVar($key, '', 'POST');
        endforeach;



        // Variables
        $postmeta = [];
        $meta_keys = self::$MetaKeys[$post_type];
        $meta_ids = [];
        $meta_exists = [];

        foreach ((array)$meta_keys as $meta_key) :
            // Vérification d'existance de la metadonnées en base
            if ($_meta = self::get($post_id, $meta_key)) {
                $meta_exists += $_meta;
            }

            if (!isset($request[$meta_key])) {
                continue;
            }

            // Récupération des meta_ids de metadonnées unique
            if (self::isSingle($post_type, $meta_key)) :
                $meta_id = $_meta ? key($_meta) : uniqid();
                array_push($meta_ids, $meta_id);
                $postmeta[$meta_key][$meta_id] = $request[$meta_key];
            // Récupération des meta_ids de metadonnées multiple
            elseif (self::isSingle($post_type, $meta_key) === false) :
                $meta_ids += array_keys($request[$meta_key]);
                $postmeta[$meta_key] = $request[$meta_key];
            endif;
        endforeach;

        // Suppression des metadonnées absente du processus de sauvegarde
        foreach ((array)$meta_exists as $meta_id => $meta_value) :
            if (!in_array($meta_id, $meta_ids)) :
                delete_metadata_by_mid('post', $meta_id);
            endif;
        endforeach;

        // Sauvegarde des metadonnées (mise à jour ou ajout)
        foreach ((array)$meta_keys as $meta_key) :
            if (!isset($postmeta[$meta_key])) {
                continue;
            }

            $order = [];
            foreach ((array)$postmeta[$meta_key] as $meta_id => $meta_value) :
                $meta_value = apply_filters("tify_sanitize_meta_post_{$post_type}_{$meta_key}", $meta_value);

                if (is_int($meta_id) && get_post_meta_by_id($meta_id)) :
                    $_meta_id = $meta_id;
                    update_metadata_by_mid('post', $meta_id, $meta_value);
                else :
                    $_meta_id = add_post_meta($post_id, $meta_key, $meta_value);
                endif;
                // Récupération de l'ordre des metadonnées multiple
                if (self::isSingle($post_type, $meta_key) === false) {
                    $order[] = $_meta_id;
                }
            endforeach;

            // Sauvegarde de l'ordre
            if (!empty($order)) {
                update_post_meta($post_id, '_order_' . $meta_key, $order);
            }
        endforeach;

        return $post;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'une métadonnée
     *
     * @param $post_type
     * @param $meta_key
     * @param bool $single
     * @param string $sanitize_callback
     *
     * return void
     */
    final public static function register($post_type, $meta_key, $single = false, $sanitize_callback = 'wp_unslash')
    {
        // Bypass
        if (!empty(self::$MetaKeys[$post_type]) && in_array($meta_key, self::$MetaKeys[$post_type])) :
            return;
        endif;

        self::$MetaKeys[$post_type][] = $meta_key;
        self::$Single[$post_type][$meta_key] = $single;

        if ($sanitize_callback !== '') :
            add_filter("tify_sanitize_meta_post_{$post_type}_{$meta_key}", $sanitize_callback);
        endif;
    }

    /**
     * Récupération d'une métadonnée
     *
     * @param $post_id
     * @param $meta_key
     *
     * @return array|void
     */
    final public static function get($post_id, $meta_key)
    {
        global $wpdb;
        $query = "SELECT meta_id, meta_value" . " FROM {$wpdb->postmeta}" . " WHERE 1" . " AND {$wpdb->postmeta}.post_id = %d" . " AND {$wpdb->postmeta}.meta_key = %s";

        if ($order = get_post_meta($post_id, '_order_' . $meta_key, true)) :
            $query .= " ORDER BY FIELD( {$wpdb->postmeta}.meta_id," . implode(',', $order) . ")";
        endif;

        if (!$metas = $wpdb->get_results($wpdb->prepare($query, $post_id, $meta_key))) :
            return;
        endif;

        $_metas = [];
        foreach ((array)$metas as $index => $args) :
            $_metas[$args->meta_id] = maybe_unserialize($args->meta_value);
        endforeach;

        return $_metas;
    }

    /**
     * Ajout d'une metadonnée
     *
     * @param int $post_id Identifiant de qualification du post
     * @param $meta_key
     * @param mixed $meta_value
     *
     * @return bool|int
     */
    final public static function add($post_id, $meta_key, $meta_value)
    {
        if (!$post_type = \get_post_type($post_id)) :
            return false;
        endif;

        $unique = self::isSingle($post_type, $meta_key);

        if ($unique=== null):
            return false;
        endif;

        return \add_post_meta($post_id, $meta_key, $meta_value, $unique);
    }

    /**
     * Mise à jour d'une metadonnée
     *
     * @param int $post_id Identifiant de qualification du post
     * @param $meta_key
     * @param mixed $meta_value
     *
     * @return bool|int
     */
    final public static function update($post_id, $meta_key, $meta_value)
    {
        if (!$post_type = \get_post_type($post_id)) :
            return false;
        endif;

        $unique = self::isSingle($post_type, $meta_key);

        if ($unique=== null):
            return false;
        endif;

        return \update_post_meta($post_id, $meta_key, $meta_value, $unique);
    }

    /* = VERIFICATION = */
    final public static function isSingle($post_type, $meta_key)
    {
        return isset(self::$Single[$post_type][$meta_key]) ? self::$Single[$post_type][$meta_key] : null;
    }
}