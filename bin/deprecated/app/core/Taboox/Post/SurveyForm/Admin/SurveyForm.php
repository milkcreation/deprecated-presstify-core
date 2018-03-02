<?php
namespace tiFy\Core\Taboox\Post\SurveyForm\Admin;

use tiFy\Deprecated\Deprecated;

class SurveyForm extends \tiFy\Core\Taboox\PostType\SurveyForm\Admin\SurveyForm
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\SurveyForm\Admin\SurveyForm', '1.2.472', '\tiFy\Core\Taboox\PostType\SurveyForm\Admin\SurveyForm');
    }
}