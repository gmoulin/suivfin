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

		return $this;
	}

	/**
	 * global method override
	 * for timestamps, the id column contain the list name
	 * so it is never empty and the "global" save method will do an update
	 */
	public function save(){
		try {
			$sql = "REPLACE INTO :table (id, stamp) VALUES (':id', NOW())";
			$q = $this->_db->prepare($sql);

			$r = $q->execute(array(':table' => $this->_table, ':id' => $this->_data['id']);

			if( $r != 1 ) throw new PDOException('Error while refreshing timestamp for list '.$this->_data['id']);

			return true;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * search for timestamps which id like $id and update them
	 */
	public function refresh($id){
		try {
			//timestamp already present ?
			$getByList = $this->db->prepare("
				SELECT id FROM :table WHERE id LIKE :id
			");

			$getByList->execute(array(':table' => $this->_table, ':id' => '%'.$this->_data['id'].'%'));

			while( $ts = $getByList->fetchObject() ){
				$this->_data['id'] = $ts->id;
				$this->save();
			}

			//always do the refresh for the exact name,
			//with the "LIKE" a stamp can be found but not be the one needed, so the one needed is not created
			$this->_data['id'] = $ts->id;
			$this->save();

			return true;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

}
?>