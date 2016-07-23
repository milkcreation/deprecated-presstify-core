<?php
namespace tiFy\Plugins\Emailing\Db\Subscriber;

use tiFy\Core\Db\Factory as DbFactory;

class Factory extends DbFactory
{	
	/* == == **/
	public function select(){
		return new Select( $this );
	}
}