<?php
namespace tiFy\Components\Search;

use tiFy\Components\Search\Search;

class Query extends \WP_Query
{
    /**
     * Instance de la classe
     */
    private static $Instance = null;

    /**
     * Nombre de groupes pour la requête courante.
     * @var int
     */
    public $group_count = 0;

    /**
     * Indice du groupe courant.
     * @var int
     */
    public $current_group = 0;

    /**
     * Indice du groupe des posts courant.
     * @var int
     */
    public $current_posts_group = 0;

    /**
     * Récupération de l'instance globale
     * @return null|static
     */
    public static function getInstance()
    {
        global $wp_the_query;

        if (!self::$Instance) :
            $instance = new static;
            $instance->loadFromParentObj($wp_the_query);

            if ($global = Search::get('_global')) :
                $instance->group_count = count($global->getGroupsAttrList());
            endif;

            self::$Instance = $instance;
        endif;

        return self::$Instance;
    }

    /**
     * Clonage de la classe parente
     * @see https://stackoverflow.com/questions/119281/how-do-you-copy-a-php-object-into-a-different-object-type
     *
     * @param \WP_Query $WP_Query
     *
     * @return void
     */
    final public function loadFromParentObj($WP_Query)
    {
        $objValues = get_object_vars($WP_Query);
        foreach($objValues AS $key => $value) :
            $this->{$key} = $value;
        endforeach;
    }

    /**
     * Détermine s'il y a des posts disponibles dans la boucle.
     *
     * @return bool
     */
    public function have_posts()
    {
        if ( $this->current_post + 1 < $this->post_count ) :
            if ($this->current_posts_group() === $this->current_group) :
                return true;
            else :
                return false;
            endif;
        elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) :
            do_action_ref_array( 'loop_end', array( &$this ) );
            // Do some cleaning up after the loop
            $this->rewind_posts();
        endif;

        $this->in_the_loop = false;
        return false;
    }

    /**
     * Récupération du groupe courant des posts disponibles
     *
     * @return int
     */
    public function current_posts_group()
    {
        return $this->current_posts_group = (int)$this->posts[$this->current_post+1]->tFySearchGroup;
    }

    /**
     * Incrémentation du groupe courant
     */
    public function next_group()
    {
        $this->current_group++;
    }

    /**
     * Définition du groupe courant
     *
     * @return void
     */
    public function the_group()
    {
        $this->next_group();
        $this->current_posts_group = $this->current_group;
    }

    /**
     * Détermine s'il y a des groupes disponibles dans la boucle.
     *
     * @return bool
     */
    public function have_groups()
    {
        if ($this->current_group < $this->group_count) :
            return true;
        elseif ($this->current_post == $this->group_count && $this->group_count > 0) :
            $this->rewind_groups();
        endif;

        return false;
    }

    /**
     * Réinitialisation des groupes.
     *
     * @return void
     */
    public function rewind_groups()
    {
        $this->current_group = 0;
    }
}