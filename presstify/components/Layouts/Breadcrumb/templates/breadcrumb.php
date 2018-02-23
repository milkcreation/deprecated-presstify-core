<?php
/**
 * @var string $id Identifiant de qualification du controleur d'affichage
 * @var int $index Instance d'appel du controleur d'affichage
 * @var string $container_id Id du conteneur HTML
 * @var string $container_class Classe du conteneur HTML
 * @var array $parts Liste des éléments contenus dans le fil d'ariane
 */
?>

<ol id="<?php echo $container_id;?>" class="<?php echo $container_class; ?>">
    <?php foreach ($parts as $part) : echo $part; endforeach;?>
</ol>