<?php

namespace tiFy\Core\Query\Controller;

use Illuminate\Support\Fluent;
use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractTermItem extends Fluent implements TermItemInterface
{
    use TraitsApp;

    /**
     * Objet Term Wordpress
     * @var \WP_Term
     */
    protected $object;

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

    /**
     * Récupération de la taxonomie relative
     *
     * @return string
     */
    public function getTaxonomy()
    {
        return (string)$this->get('taxonomy', '');
    }
}