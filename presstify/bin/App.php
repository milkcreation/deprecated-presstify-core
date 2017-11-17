<?php
namespace tiFy;

abstract class App
{
    use App\Traits\App;

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