<?php
namespace tiFy\Core\Cron\Admin;

use tiFy\Core\Cron\Cron;

class ViewList extends \tiFy\Core\Templates\Admin\Model\Custom
{
    public function render()
    {
        $tasks = Cron::getList();
?>
<div class="wrap">
    <h2><?php _e('Tâches planifiées', 'tiFy');?></h2>
    <?php if ($tasks) : ?>
    <ul>
        <?php foreach ($tasks as $task => $attrs) :?>
        <li style="margin:0 0 20px;">
            <h3 style="margin:0 0 5px;"><?php echo $attrs['title'];?></h3>
            <div><em><?php echo $attrs['desc'];?></em></div>
            <?php if ($next_timestamp = wp_next_scheduled($attrs['hook'], [$attrs])) :?>
            <div><?php printf( __('<b>Prochaine exécution de la tâche :</b> %s', 'tify'), mysql2date(sprintf(__('%s à %s', 'tify'), get_option('date_format'), get_option('time_format')), get_date_from_gmt(date('Y-m-d H:i:s', $next_timestamp), 'Y-m-d H:i:s')), true);?></div>
            <?php endif;?>
        </li>
        <?php endforeach;?>
    </ul>
    <?php endif;?>
</div>
<?php
    }
}