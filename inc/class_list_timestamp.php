<?php
/**
 * Class for list timestamps
 * used for cache with 304 Not Modified and 'Last-Modified' / 'If-Modified-Since' header pair
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Evolutions
 */
class list_timestamp extends common {
	protected $_table = 'list_timestamp';

	// Constructor
	public function __construct() {
		//for "common" ($this->_db & co)
		parent::__construct();
	}
}
?>