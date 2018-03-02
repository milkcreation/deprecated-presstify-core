<?php
namespace tiFy;

use tiFy\App\Traits\App as TraitsApp;

abstract class App
{
    use TraitsApp;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        $this->tFyAppOnInit();
    }
}