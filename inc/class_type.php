<?php
/**
 * Class for paiement type management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Types
 */
class type extends common {
	protected $_table = 'type';

	// Constructor
	public function __construct($id = null){
		//for "common" ($this->_db & co)
		parent::__construct($id);
	}
}
?>