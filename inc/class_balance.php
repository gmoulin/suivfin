<?php
/**
 * Class for paiement balance management
 * entries are created and deleted in connection to payement table entries using an automated batch
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Balance
 * @category Balances
 */
class balance extends common {
	protected $_table = 'balance';

	// Constructor
	public function __construct($id = null){
		//for "common" ($this->_db & co)
		parent::__construct($id);
	}

	public function loadByFKs( $currency, $origin, $type ){
		try{
			$loadByFKs = $this->_db->prepare("
				SELECT * FROM ".$this->_table." WHERE currencyFK = :currency AND originFK = :origin AND typeFK = :type
			");

			$loadByFKs->execute(array(
				':currency' => $currency,
				':origin' => $origin,
				':type' => $type,
			));

			$entry = $loadByFKs->fetch();
			if( !empty($entry) ){
				foreach( self::$_fields[$this->_table] as $k=>$v ){
					$this->_data[$k] = $entry[$v];
				}
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>