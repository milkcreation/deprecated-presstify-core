<?php
/**
 * @var string $page_title Titre de la page
 * @var string $option_group
 */
?>
<div class="wrap">
    <h2><?php echo $page_title; ?></h2>

    <form method="post" action="options.php">
        <div style="float:left; width: 100%;">
            <?php \settings_fields($option_group); ?>
            <?php \do_settings_sections($option_group); ?>
        </div>
        <div class="submit">
            <?php \submit_button(); ?>
        </div>
    </form>
</div>