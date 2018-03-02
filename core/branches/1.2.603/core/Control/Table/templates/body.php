<?php
?>

<div class="tiFyTableBody">
<?php if ($datas) : ?>
    <?php foreach ($datas as $row => $dr) : ?>
    <div class="tiFyTableBodyTr tiFyTableBodyTr--<?php echo $row; ?> tiFyTableTr tiFyTableTr-<?php echo ($num++ % 2 === 0) ? 'even' : 'odd'; ?>">
        <?php foreach ($columns as $name => $label) : ?>
        <div class="tiFyTableCell<?php echo $count; ?> tiFyTableBodyTd tiFyTableBodyTd--<?php echo $name; ?> tiFyTableTd">
            <span class="tiFyTableCell-label"><?php echo $label; ?></span>
            <?php echo $dr[$name];?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
<?php else : ?>
    <div class="tiFyTableBodyTr tiFyTableBodyTr--empty tiFyTableTr">
        <div class="tiFyTableCell1 tiFyTableBodyTd tiFyTableBodyTd--empty tiFyTableTd">
            <?php echo $none; ?>
        </div>
    </div>
<?php endif; ?>
</div>