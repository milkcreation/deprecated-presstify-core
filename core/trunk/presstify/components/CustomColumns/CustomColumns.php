<?php
namespace tiFy\Components\CustomColumns;

class CustomColumns extends \tiFy\App\Component
{
    /**
     * Liste des classes de rappel
     *
     * @var \tiFy\Components\CustomColumns\Factory[]
     */
    public static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements de déclenchement
        $this->tFyAppActionAdd('admin_init', null, 99);
        $this->tFyAppActionAdd('current_screen');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {
        // Récupération des colonnes personnalisées déclarées dans les fichiers de configuration
        foreach (['post_type', 'taxonomy', 'custom'] as $object) :
            if(!self::tFyAppConfig($object)) :
                continue;
            endif;
            foreach (self::tFyAppConfig($object) as $object_type => $custom_columns) :
                foreach ($custom_columns as $id => $args) :
                    $args = (array) $args;
                    $args['object'] = $object;
                    $args['object_type'] = $object_type;

                    self::register($id, $args);
                endforeach;
            endforeach;
        endforeach;

        // Récupérations des colonnes personnalisées déclarées en action
        do_action('tify_custom_columns_register');

        // Instanciation des colonnes personnalisées déclarées
        foreach (['post_type', 'taxonomy', 'custom'] as $env) :
            if (!isset(self::$Factory[$env])) :
                continue;
            endif;

            foreach (self::$Factory[$env] as $object_type => $callbacks) :
                foreach ($callbacks as $cb) :
                    if (is_callable([$cb, 'admin_init'])) :
                        call_user_func([$cb, 'admin_init']);
                    endif;
                endforeach;
            endforeach;
        endforeach;
    }

    /** == Affichage de l'écran courant == **/
    final public function current_screen($current_screen)
    {
        switch ($current_screen->base) :
            case 'edit' :
                if (!isset(self::$Factory['post_type'][$current_screen->post_type])) :
                    return;
                endif;

                foreach (self::$Factory['post_type'][$current_screen->post_type] as $object_type => $cb) :
                    call_user_func([$cb, 'current_screen'], $current_screen);
                    if (is_callable([$cb, 'admin_enqueue_scripts'])) :
                        add_action('admin_enqueue_scripts', [$cb, 'admin_enqueue_scripts']);
                    endif;
                endforeach;
                break;
            case 'edit-tags' :
                if (!isset(self::$Factory['taxonomy'][$current_screen->taxonomy])) :
                    return;
                endif;
                foreach ((array)self::$Factory['taxonomy'][$current_screen->taxonomy] as $object_type => $cb) :
                    call_user_func([$cb, 'current_screen'], $current_screen);
                    if (is_callable([$cb, 'admin_enqueue_scripts'])) :
                        add_action('admin_enqueue_scripts', [$cb, 'admin_enqueue_scripts']);
                    endif;
                endforeach;
                break;
        endswitch;
    }

    /**
     * Déclaration de colonne personnalisée
     *
     * @param string $id Identifiant de qualification unique
     * @param array $attrs Attributs de configuration
     * @param string $object post_type|taxonomy (optionnel si le paramètre est inclus dans les attributs)
     * @param string $object_type Identifiant du post_type ou de la taxonomy (optionnel si le paramètre est inclus dans les attributs)
     *
     * @return null|\tiFy\Components\CustomColumns\Factory
     */
    public static function register($id, $attrs = [])
    {
        // Traitement des attributs
        if (!isset($attrs['object'])) :
            if (!$attrs['object'] = func_get_arg(2)) :
                return;
            endif;
        endif;
        if (!isset($attrs['object_type'])) :
            if (!$attrs['object_type'] = func_get_arg(3)) :
                return;
            endif;
        endif;

        if(!isset($attrs['cb'])) :
            $attrs['cb'] = $id;
        endif;

        $classname = false;
        switch ($attrs['object']) :
            case 'post_type' :
                $classname = '\tiFy\Components\CustomColumns\PostType';
                $Object = 'PostType';
                break;
            case 'taxonomy' :
                $classname = '\tiFy\Components\CustomColumns\Taxonomy';
                $Object = 'Taxonomy';
                break;
            case 'custom' :
                $classname = '\tiFy\Components\CustomColumns\Custom';
                $Object = 'Custom';
                break;
        endswitch;

        // Classe de rappel
        if (is_string($attrs['cb']) && class_exists($attrs['cb'])) :
            $classname = $attrs['cb'];

            return self::$Factory[$attrs['object']][$attrs['object_type']][] = new $classname($attrs);

        // Méthode ou fonction de rappel
        elseif (is_callable($attrs['cb'])) :
            $attrs['content_cb'] = $attrs['cb'];
            return self::$Factory[$attrs['object']][$attrs['object_type']][] = new $classname($attrs);

        // Classe native
        elseif (class_exists("\\tiFy\\Components\\CustomColumns\\{$Object}\\" . $attrs['cb'] . "\\" . $attrs['cb'])) :
            $classname = "\\tiFy\\Components\\CustomColumns\\{$Object}\\" . $attrs['cb'] . "\\" . $attrs['cb'];

            return self::$Factory[$attrs['object']][$attrs['object_type']][] = new $classname($attrs);
        endif;
    }
}