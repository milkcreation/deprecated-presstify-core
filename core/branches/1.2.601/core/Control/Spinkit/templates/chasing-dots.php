<?php
/**
 * @var string $container_id Id HTML du conteneur
 * @var string $container_class Classe HTML du conteneur
 * @var string $type Type de controleur spinkit
 */
?>
<div id="<?php echo $container_id; ?>" class="tiFyCoreControl-spinkit tiFyCoreControl-spinkit--<?php echo $type; ?><?php echo $container_class ? " {$container_class}" : '';?> sk-chasing-dots">
    <div class="sk-child sk-dot1"></div>
    <div class="sk-child sk-dot2"></div>
</div>