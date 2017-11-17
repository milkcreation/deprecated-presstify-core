<?php
/*

// Éxecution de l'action - creation
protected function process_bulk_action_create()
{
check_admin_referer($this->get_item_nonce_action('create'));

$data = edit_user(0);
$sendback = remove_query_arg(['action', 'action2'], wp_get_referer());

if (is_wp_error($data)) :
add_action('admin_notices', function () use ($data) {
foreach ($data->get_error_messages() as $message) {
printf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message);
}
});
else :
$sendback = add_query_arg([$this->db()->Primary => $data], $sendback);
$sendback = add_query_arg(['message' => 'created'], $sendback);
wp_redirect($sendback);
exit;
endif;
}

// Éxecution de l'action - mise à jour
protected function process_bulk_action_update()
{
check_admin_referer($this->get_item_nonce_action('update', $this->current_item()));

$data = edit_user($this->current_item());
$sendback = remove_query_arg(['action', 'action2'], wp_get_referer());

if (is_wp_error($data)) :
add_action('admin_notices', function () use ($data) {
foreach ($data->get_error_messages() as $message) {
printf('<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $message);
}
});
else :
$sendback = add_query_arg([$this->db()->Primary => $data], $sendback);
$sendback = add_query_arg(['message' => 'updated'], $sendback);
wp_redirect($sendback);
exit;
endif;
}

*/