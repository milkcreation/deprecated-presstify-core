<?php
/**
 * @var array $columns
 */
?>

<div class="tiFyTableFoot">
    <div class="tiFyTableFootTr tiFyTableTr">
    <?php foreach ($columns as $name => $label) : ?>
        <div class="tiFyTableCell<?php echo $count; ?> tiFyTableFootTh tiFyTableFootTh--<?php echo $name; ?> tiFyTableTh tiFyTableTh--<?php echo $name; ?>">
            <?php echo $label; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

