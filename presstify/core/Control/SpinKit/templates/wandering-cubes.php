<?php
/**
 * @var string $container_id Id HTML du conteneur
 * @var string $container_class Classe HTML du conteneur
 * @var string $type Type de controleur spinkit
 */
?>
<div id="<?php echo $container_id; ?>" class="tiFyCoreControl-Spinner tiFyCoreControl-Spinner--<?php echo $type; ?><?php echo $container_class ? " {$container_class}" : '';?> sk-wandering-cubes">
    <div class="sk-cube sk-cube1"></div>
    <div class="sk-cube sk-cube2"></div>
</div>