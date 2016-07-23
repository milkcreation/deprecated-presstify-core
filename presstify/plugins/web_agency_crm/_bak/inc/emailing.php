<?php
/**
 *
 */
function mkcrm_email_senders_info( $key = '', $address = '', $period ='' ){	
	try {
	    $mandrill = new Mandrill('KvKexkDJZCl4j9Nam6nQgQ');
		var_dump( $mandrill );
	    $address = 'lephenix.scenenationale@gmail.com';
		$result = $mandrill->senders->info($address);
		$stats = $result['stats']['last_30_days'];	
		$stats['delivered'] = $stats['sent'] - $stats['hard_bounces'] - $stats['soft_bounces'];
		$stats['open_rate'] = (float)($stats['unique_opens']/$stats['delivered'])*100;
		$stats['click_rate'] = (float)($stats['unique_clicks']/$stats['delivered'])*100;
		$stats['deliverability'] = (float)($stats['delivered']/$stats['sent'])*100;
		return $stats;	
	} catch(Mandrill_Error $e) {
	    // Mandrill errors are thrown as exceptions
	    return 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
	    // A mandrill error occurred: Mandrill_Invalid_Key - Invalid API key
	    throw $e;
	}
}
