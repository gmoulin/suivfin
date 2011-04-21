<?php
/**
 * Class for paiement owners management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Owners
 */
class owner extends common {
	protected $_table = 'owner';
	protected $_link = 'limits';

	// Constructor
	public function __construct( $id = null ){
		//for "common" ($this->_db & co)
		parent::__construct($id);
	}

	/**
	 * return the linked origins (bank accounts) and currencies for a given owner
	 */
	public function getLimits( $forAll = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, ( !$forAll ? $this->getOwner() : 'all' ));
			$list = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$q = $this->_db->prepare("
					SELECT ".( $forAll ? "ownerFK, " : "" )." originFK, currencyFK
					FROM ".$this->_link."
					".( !$forAll ? "WHERE ownerFK = :owner" : "" )."
				");
				if( !$forAll ){
					$q->execute( array(':owner' => $this->getOwner()) );
				} else {
					$q->execute();
				}

				$list = $q->fetchAll();

				if( !empty($list) ) $stash->store($list, STASH_EXPIRE);
			}

			return $list;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>