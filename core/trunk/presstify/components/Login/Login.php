<?php
namespace tiFy\Components\Login;

final class Login extends \tiFy\App\Component
{
    /**
     * Liste des classes de rappel des interfaces d'authentification
     * @var \tiFy\Components\Login\Factory[]
     */
    private static $Factory   = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Déclaration des événenements de déclenchement
        $this->tFyAppActionAdd('init');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des interfaces d'authentification configurées
        if ($logins = self::tFyAppConfig()) :
            foreach ($logins as $id => $attrs) :
                self::register($id, $attrs);
            endforeach;
        endif;

        // Déclaration des interfaces d'authentification ponctuelles
        do_action('tify_login_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un formulaire d'authentification
     *
     * @param string $id Identification de qualification du formulaire d'authentification
     * @param array $attrs Attributs de configuration
     *
     * @return \tiFy\Components\Login\Factory
     */
    /** ==  == **/
    public static function register( $id, $attrs = [])
    {
        $defaults = [
            'cb'    => ''
        ];

        // Rétrocompatibilité
        if ((func_num_args() === 3) || is_string(func_get_arg(1))) :
            $cb = func_get_arg(1);
            $attrs = [];
            $attrs['cb'] = $cb;
        endif;

        $attrs = \wp_parse_args($attrs, $defaults);

        if ($attrs['cb']) :
            $path[] = $attrs['cb'];
        endif;

        $path[] = "\\". self::getOverrideNamespace() . "\\Login\\". self::sanitizeControllerName( $id );    

        $callback = self::getOverride('\tiFy\Components\Login\Factory', $path);

        return self::$Factory[$id] = new $callback($id, $attrs);
    }

    /**
     * Récupération d'une classe de rappel de formulaire d'authentification déclaré
     *
     * @param $id string Identification de qualification d'un formulaire d'authentification déclaré
     *
     * @return mixed
     */
    public static function get($id)
    {
        if (isset( self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }

    /**
     * Affichage d'un élément de gabarit
     *
     * @param string $id Identification de qualification d'un formulaire d'authentification déclaré
     * @param string $template Méthode de la classe \tiFy\Components\Login\Factory d'affichage
     *
     * @return string
     */
    public static function display($id, $template, $attrs = [], $echo = true)
    {
        if (!$factory = self::get($id)) :
            return '';
        endif;

        $output = call_user_func_array([$factory, 'display'], [$template, $attrs, $echo]);

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}