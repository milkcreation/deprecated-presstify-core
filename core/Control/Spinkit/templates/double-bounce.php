<?php
/**
 * @var string $container_id Id HTML du conteneur
 * @var string $container_class Classe HTML du conteneur
 * @var string $type Type de controleur spinkit
 */
?>
<div id="<?php echo $container_id; ?>" class="tiFyCoreControl-spinkit tiFyCoreControl-spinkit--<?php echo $type; ?><?php echo $container_class ? " {$container_class}" : '';?> sk-double-bounce">
    <div class="sk-child sk-double-bounce1"></div>
    <div class="sk-child sk-double-bounce2"></div>
</div>