<?php

namespace Theme\tiFy\Core\Taboox\PostType\SurveyForm\Helpers;

class SurveyForm extends \tiFy\Core\Taboox\Helpers
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('wp');
    }

    /**
     * EVENENEMENTS
     */
    /* = WP = */
    public function wp()
    {
        // Bypass
        if (!tify_taboox_content_hook_get('current_survey')) :
            return;
        endif;
        if (!tify_taboox_content_hook_is('current_survey_page')) :
            return;
        endif;
        add_action('ehgode_single_post_bottom', [$this, 'display']);
    }

    /* = AFFICHAGE DU FORMULAIRE = */
    public function display()
    {
        ?>
        <section class="ContactArea Section" id="contact-area">
            <div class="Section-inner">
                <h2 class="Section-title"><span
                            class="Section-titleInner"><?php echo get_the_title(tify_taboox_content_hook_get('current_survey')); ?></span>
                </h2>
                <?php tify_form_display('CurrentSurveyForm'); ?>
            </div>
        </section>
        <?php
    }
}