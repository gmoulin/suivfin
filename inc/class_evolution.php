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

	/**
	 * delete evolution for an origin and a type since given date
	 * @params date $date
	 * @params integer $origin: a key for payment origin, must be present in table limits (for currency link)
	 */
	public function deleteSince( $date, $origin ){
		try{
			$deleteSinceDate = $this->_db->prepare("
				DELETE
				FROM ".$this->_table."
				WHERE evolutionDate >= :date
				AND originFK = :origin
			");

			$deleteSinceDate->execute( array(
				':date' => $date,
				':origin' => $origin,
			) );

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * for each type and each origin presents in limits table
	 * sum the daily payment and add or substract, depending on type, it to previous day evolution
	 */
	public function calculateEvolution(){
		try{
			$oOwner = new owner();
			$limits = $oOwner->getLimits( true ); //all limitations
			$origins = array();
			foreach($limits as $limit){
				$origins[] = $limit['originFK'];
			}
			$origins = array_unique($origins);

			$lastDate = "
				SELECT evolutionDate, amount
				FROM ".$this->_table."
				WHERE originFK = :origin
				ORDER BY evolutionDate DESC
				LIMIT 1
			";
			$q = $this->_db->prepare($lastDate);

			// type = 1 means deposits, and for them it's the recipient
			// recipient and origin have the same ids for bank accounts and cash
			$sum = "
				(
					SELECT SUM(amount) AS 'sum', typeFK
					FROM payment
					WHERE originFK = :origin1
					AND paymentDate = :date1
					AND typeFK != 1
					GROUP BY typeFK
				) UNION (
					SELECT SUM(amount) AS 'sum', typeFK
					FROM payment
					WHERE recipientFK = :origin2
					AND paymentDate = :date2
					AND typeFK = 1
				)
			";
			$s = $this->_db->prepare($sum);

			foreach( $origins as $origin ){
				$q->execute( array(':origin' => $origin) );

				$this->originFK = $origin;
				$this->evolutionDate = '2011-01-01';
				$this->amount = 0;

				$rs = $q->fetch();
				if( !empty($rs) && !empty($rs['evolutionDate']) ){
					$this->evolutionDate = date("Y-m-d", strtotime("+1 day", strtotime( $rs['evolutionDate'] )) );
					$this->amount = $rs['amount'];
				}

				$today = date('Y-m-d');
				while( $this->evolutionDate <= $today ){
					$this->id = null; //force add

					$s->execute( array(
						':origin1' => $origin,
						':origin2' => $origin,
						':date1' => $this->evolutionDate,
						':date2' => $this->evolutionDate
					) );
					$rs = $s->fetchAll();
					if( !empty($rs) ){
						foreach( $rs as $data ){
							if( $data['typeFK'] == 1 ){
								$this->amount += $data['sum'];
							} else {
								$this->amount -= $data['sum'];
							}
						}
					}

					$this->save();

					//set the date to next day
					$this->evolutionDate = date("Y-m-d", strtotime("+1 day", strtotime( $this->evolutionDate )) );

					$s->closeCursor();
				}

				$q->closeCursor();
			}


		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>