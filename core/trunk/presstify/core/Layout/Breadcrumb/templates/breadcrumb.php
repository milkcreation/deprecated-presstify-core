<?php
/**
 * @var string $container_id Id du conteneur HTML
 * @var string $container_class Classe du conteneur HTML
 * @var $parts Liste des éléments contenus dans le fil d'ariane
 */
?>

<ol id="<?php echo $container_id;?>" class="tiFyCore-layoutBreadcrumb <?php echo $container_class;?>">
    <?php foreach ($parts as $part) :?>
    <li class="<?php echo $part['class']; ?>">
        <?php echo $part['content']; ?>
    </li>
    <?php endforeach;?>
</ol>
