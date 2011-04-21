<?php
/**
 * Class for paiement status management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Statuses
 */
class status extends common {
	protected $_table = 'status';

	// Constructor
	public function __construct($id = null){
		//for "common" ($this->_db & co)
		parent::__construct($id);
	}
}
?>