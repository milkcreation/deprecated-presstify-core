<?php
/**
 * @var string $page_title Titre de la page
 * @var string $option_group
 */
?>
<div class="wrap">
    <h2><?php echo $page_title; ?></h2>

    <form method="post" action="options.php">
        <div style="margin-right:300px; margin-top:20px;">
            <div style="float:left; width: 100%;">
                <?php \settings_fields($option_group); ?>
                <?php \do_settings_sections($option_group); ?>
            </div>
            <div style="margin-right:-300px; width: 280px; float:right;">
                <div id="submitdiv">
                    <h3 class="hndle"><span><?php _e('Enregistrer', 'tify'); ?></span></h3>
                    <div style="padding:10px;">
                        <div class="submit">
                            <?php \submit_button(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
