<?php
/**
 * Class for balance evolution management
 * entries are created and deleted in connection to payement table entries using an automated batch
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Evolutions
 */
class evolution extends common {
	protected $_table = 'evolution';

	// Constructor
	public function __construct( $id = null ){
		//for "common" ($this->_db & co)
		parent::__construct($id);
	}

	public function deleteSinceDate( $date ){
		try{
			$deleteSinceDate = $this->_db->prepare("
				DELETE FROM ".$this->_table." WHERE evolutionDate >= :date
			");

			$deleteSinceDate->execute( array(':date' => $date) );

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>