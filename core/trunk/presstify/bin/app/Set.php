<?php
namespace tiFy\App;

abstract class Set extends \tiFy\App\Factory
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        self::initOverrideAutoloader();
    }
}