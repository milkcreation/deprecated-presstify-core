<?php
namespace tiFy\Set;

class Factory extends \tiFy\Environment\Set
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		$this->_init();
	}
	
	/* = CONTROLEURS = */
	/** == Initialisation == **/
	protected function _init(){}
}