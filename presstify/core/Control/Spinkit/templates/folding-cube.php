<?php
/**
 * @var string $container_id Id HTML du conteneur
 * @var string $container_class Classe HTML du conteneur
 * @var string $type Type de controleur spinkit
 */
?>
<div id="<?php echo $container_id; ?>" class="tiFyCoreControl-spinkit tiFyCoreControl-spinkit--<?php echo $type; ?><?php echo $container_class ? " {$container_class}" : '';?> sk-folding-cube">
    <div class="sk-cube1 sk-cube"></div>
    <div class="sk-cube2 sk-cube"></div>
    <div class="sk-cube4 sk-cube"></div>
    <div class="sk-cube3 sk-cube"></div>
</div>