<?php
/**
 * @var string $id Identifiant de qualification du controleur d'affichage
 * @var int $index Instance d'appel du controleur d'affichage
 * @var string $tag Balise HTML
 * @var string $tag_attrs Liste des attribitus de balise HTML
 * @var string $_tag_attrs Liste des attributs de balise HTML linéarisés
 * @var string $content Contenu de la balise
 */
?>

<<?php echo $tag; ?><?php echo $_tag_attrs; ?>><?php echo $content; ?></<?php echo $tag; ?>>
