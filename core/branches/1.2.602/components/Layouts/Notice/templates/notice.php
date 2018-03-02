<?php
/**
 * @var string $id Identifiant de qualification du controleur d'affichage.
 * @var int $index Instance d'appel du controleur d'affichage.
 * @var string $container_id ID de la balise HTML de conteneur.
 * @var string $container_class Classe de la balise HTML de conteneur.
 * @var bool|string $dismissible Bouton de masquage du controleur d'affichage.
 * @var string $text Texte du message de notification.
 */
?>

<div id="<?php echo $container_id; ?>" class="<?php echo $container_class; ?>">
    <?php if ($dismissible !== false) : ?>
    <button type="button" data-dismiss="tiFyLayout-notice">
        <?php echo $dismissible; ?>
    </button>
    <?php endif; ?>

    <div><?php echo $text; ?></div>
</div>
