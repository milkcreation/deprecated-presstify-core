<?php

namespace tiFy\Core\Query\Controller;

use tiFy\App\Traits\App as TraitsApp;
use Illuminate\Support\Fluent;

abstract class AbstractTerm extends Fluent implements TermInterface
{
    use TraitsApp;

    /**
     * Objet Term Wordpress
     * @var \WP_Term
     */
    protected $object;

    /**
     * Type d'objet Wordpress
     * @var string
     */
    private $objectType = 'term';

    /**
     * Controleur
     * @var string
     */
    const CONTROLLER = '';

    /**
     * Identifiant de qualification de la taxonomy Wordpress relative
     * @var array|string
     */
    const OBJECTNAME = '';

    /**
     * CONSTRUCTEUR
     *
     * @param \WP_Term $wp_term
     *
     * @return void
     */
    public function __construct(\WP_Term $wp_term)
    {
        $this->object = $wp_term;

        parent::__construct($this->object->to_array());
    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __clone()
    {

    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __wakeup()
    {

    }

    /**
     * Instanciation
     *
     * @param string|int|\WP_Term|null $id Nom de qualification (slug)|Identifiant de term Wordpress|Objet terme Wordpress|Terme de ma page courante
     *
     * @return null|static|object
     */
    public static function make($id = null)
    {
        // Bypass
        if (!$objectName = static::getObjectName()) :
            return null;
        endif;

        if (!$id) :
            $term = get_queried_object();
        elseif (is_int($id)) :
            if ((!$term = \get_term($id, $objectName)) || is_wp_error($term)) :
                return null;
            endif;
        elseif (is_string($id)) :
            return static::by('slug', $id);
        else :
            $term = $id;
        endif;

        if (!$term instanceof \WP_Term) :
            return null;
        endif;

        if ($term->taxonomy !== $objectName) :
            return null;
        endif;

        $name = 'tify.query.term.' . $term->term_id;
        if (self::tFyAppHasContainer($name)) :
            return self::tFyAppGetContainer($name);
        endif;

        $Instance = ($controller = static::getController()) ? new $controller($term): new static($term);
        self::tFyAppShareContainer($name, $Instance);

        return $Instance;
    }

    /**
     * Instanciation selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut name.
     * @param string $value Valeur de l'attribut
     *
     * @return null|static|object
     */
    public static function by($key = 'slug', $value)
    {
        switch ($key) :
            default :
                if (($term = get_term_by($key, $value, static::OBJECTNAME)) && !is_wp_error($term)) :
                    return static::make($term);
                endif;
                break;
        endswitch;

        return null;
    }

    /**
     * Récupération du controleur
     *
     * @return string
     */
    public static function getController()
    {
        return static::CONTROLLER;
    }

    /**
     * Récupération de l'identifiant de qualification du rôle de l'utilisateur Wordpress relatif
     *
     * @return string|array
     */
    public static function getObjectName()
    {
        return static::OBJECTNAME;
    }

    /**
     * Récupération de l'object Post Wordpress associé
     *
     * @return \WP_Term
     */
    public function getTerm()
    {
        return $this->object;
    }

    /**
     * Récupération de l'identifiant de qualification Wordpress du terme
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->get('term_id', 0);
    }

    /**
     * Récupération du nom de qualification Wordpress du terme
     *
     * @return string
     */
    public function getSlug()
    {
        return (string)$this->get('slug', '');
    }

    /**
     * Récupération de l'intitulé de qualification
     *
     * @return string
     */
    public function getName()
    {
        return (string)$this->get('name', '');
    }
}