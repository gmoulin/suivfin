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
	public function deleteSinceDate( $date, $origin ){
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

			foreach( $origins as $origin ){
				$sql = "
					SELECT evolutionDate, amount
					FROM ".$this->_table."
					WHERE originFK = :origin
					ORDER BY evolutionDate DESC
					LIMIT 1
				";

				$q = $this->_db->prepare($sql);
				$q->execute( array(':origin' => $origin) );

				$this->originFK = $origin;
				$this->evolutionDate = '2011-01-01';
				$this->amount = 0;

				$rs = $q->fetch();
				if( !empty($rs) && !empty($rs['evolutionDate']) ){
					$this->evolutionDate = $rs['evolutionDate'];
					$this->amount = $rs['amount'];
				}

				$today = date('Y-m-d');
				while( $this->evolutionDate != $today ){
					$this->id = null; //force add

					$sql = "
						SELECT SUM(amount) AS 'sum', typeFK
						FROM payment
						WHERE originFK = :origin
						AND paymentDate = :date
						GROUP BY typeFK
					";

					$q = $this->_db->prepare($sql);
					$q->execute( array(':origin' => $origin, ':date' => $this->evolutionDate) );
					$rs = $q->fetchAll();
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
					$this->evolutionDate = date("Y-m-d", strtotime("+1 day", strtotime($this->evolutionDate)) );
				}
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>