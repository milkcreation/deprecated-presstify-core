<?php
/**
 * @var string $container_id
 * @var string $container_class
 * @var bool|string $dismissible
 * @var string $text
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
