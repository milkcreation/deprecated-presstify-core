<?php
/**
 * @var array $columns
 */
?>

<div class="tiFyTableHead">
    <div class="tiFyTableHeadTr tiFyTableTr">
    <?php foreach ($columns as $name => $label) : ?>
        <div class="tiFyTableCell<?php echo $count; ?> tiFyTableHeadTh tiFyTableHeadTh--<?php echo $name; ?> tiFyTableTh tiFyTableTh--<?php echo $name; ?>">
            <?php echo $label;?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
