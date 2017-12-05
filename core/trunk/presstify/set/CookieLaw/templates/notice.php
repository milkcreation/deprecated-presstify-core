<?php
/**
 * @var string $text Texte de notification
 * @var $rules_url Lien vers la page des règles d'utilisation des cookies du site
 * @var $valid_text Texte du bouton de validation
 * @var $rules_text Texte du bouton d'affichage des règles de cookie
 * @var $close_text Texte du bouton de fermeture
 */
?>
<div class="tiFySet-CookieLawText">
<?php echo $text; ?>
</div>

<a href="#<?php echo $container_id; ?>" class="tiFySet-CookieLawButton tiFySet-CookieLawValidLink" data-cookie_notice="#<?php echo $container_id; ?>" data-handle="valid">
    <?php echo $valid_text; ?>
</a>

<?php if ($rules_url) : ?>
<a href="<?php echo $rules_url; ?>" class="tiFySet-CookieLawButton tiFySet-CookieLawRulesLink" target="_blank">
    <?php echo $rules_text; ?>
</a>
<?php endif; ?>

<a href="#<?php echo $container_id; ?>" class="tiFySet-CookieLawCloseLink" data-cookie_notice="#<?php echo $container_id; ?>" data-handle="close">
    <?php echo $close_text; ?>
</a>

