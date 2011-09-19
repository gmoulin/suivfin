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
	 * @params date $date : payment date
	 * @params integer $origin: a key for payment origin, must be present in table limits (for currency link)
	 * @params integer $recipient: a key for payment recipient, in case of transfert between accounts and owners, must be present in table limits (for currency link)
	 */
	public function deleteSince( $date, $origin, $recipient ){
		try{
			$deleteSinceDate = $this->_db->prepare("
				DELETE
				FROM ".$this->_table."
				WHERE evolutionDate >= :date
				AND ( originFK = :origin OR originFK = :recipient )
			");

			$deleteSinceDate->execute( array(
				':date' => $date,
				':origin' => $origin,
				':recipient' => $recipient,
			) );

			//deleteSince is always followed by a calcultateEvolution, so a clean cache is not needed
			//$this->_cleanCaches();

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
				$this->evolutionDate = '2010-12-24';
				$this->amount = 0;

				$rs = $q->fetch();
				if( !empty($rs) && !empty($rs['evolutionDate']) ){
					$this->evolutionDate = date("Y-m-d", strtotime("+1 day", strtotime( $rs['evolutionDate'] )) );
					$this->amount = $rs['amount'];
				}

				$end = date("Y-m-d",strtotime("+2 months"));;
				while( $this->evolutionDate <= $end ){
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

					$this->save(false);

					//set the date to next day
					$this->evolutionDate = date("Y-m-d", strtotime("+1 day", strtotime( $this->evolutionDate )) );

					$s->closeCursor();
				}

				$q->closeCursor();
			}

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * compile evolution data for chart rendering
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 */
	public function getEvolutionData($returnTs = false, $tsOnly = false){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner());

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$result = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$oCurrency = new currency();
				$currenciesWSymbol = $oCurrency->loadList();

				$oOrigin = new origin();
				$origins = $oOrigin->loadListForFilter();

				$oOwner = new owner();
				$tmp = $oOwner->getLimits();
				$limits = array();
				foreach( $tmp as $limit ){
					$limits[ $limit['originFK'] ] = $currenciesWSymbol[ $limit['currencyFK'] ]['symbol'];
				}
				$owner = $this->getOwner();

				$q = $this->_db->prepare("
					SELECT e.originFK, amount
					FROM ".$this->_table." e
					INNER JOIN limits l ON l.originFK = e.originFK
					WHERE ownerFK = :owner
					AND evolutionDate >= '2011-02-01'
					ORDER BY e.originFK, evolutionDate
				");

				$q->execute( array(':owner' => $owner) );

				$result = array();
				$data = $q->fetchAll();

				if( !empty($data) ){

					foreach( $data as $d ){
						if( !isset($result['sums'][ $origins[ $d['originFK'] ] ]['symbol']) ){
							$result['sums'][ $origins[ $d['originFK'] ] ]['symbol'] = $limits[ $d['originFK'] ];
						}
						$result['sums'][ $origins[ $d['originFK'] ] ]['amounts'][] = $d['amount'];
					}
				}

				if( !empty($result) ){
					$stash->store($result, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			} elseif( $returnTs ){
				$ts = $stash->getTimestamp();
			}

			if( $returnTs ){
				return array($ts, $result);
			} else {
				return $result;
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * get the balance for the current owner accounts
	 * @param string $timestamp : unix timestamp for cache
	 */
	public function getTodayBalances( $timestamp ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $this->getOwner(), date('Y-m-d'));

			if( $timestamp >= 0 ){
				$ts = $stash->getTimestamp();
				if( !$stash->isMiss() && $ts == $timestamp ){
					return null;
				}
			}

			$result = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$oCurrency = new currency();
				$currenciesWSymbol = $oCurrency->loadList();

				$oOrigin = new origin();
				$origins = $oOrigin->loadListForFilter();

				$oOwner = new owner();
				$tmp = $oOwner->getLimits();
				$limits = array();
				foreach( $tmp as $limit ){
					$limits[ $limit['originFK'] ] = $currenciesWSymbol[ $limit['currencyFK'] ]['symbol'];
				}
				$owner = $this->getOwner();

				$q = $this->_db->prepare("
					SELECT e.originFK, amount
					FROM ".$this->_table." e
					INNER JOIN limits l ON l.originFK = e.originFK
					WHERE ownerFK = :owner
					AND evolutionDate = CURRENT_DATE()
				");

				$q->execute( array(':owner' => $owner) );

				$result = array();
				$data = $q->fetchAll();

				if( !empty($data) ){

					foreach( $data as $d ){
						$result[ $origins[ $d['originFK'] ] ]['balance'] = $d['amount'];
						$result[ $origins[ $d['originFK'] ] ]['symbol'] = $limits[ $d['originFK'] ];
					}
				}

				if( !empty($result) ){
					$expire = (24 - date("H")) * (60 - date("i")) * (60 - date("s")); //valid until midnight
					$stash->store($result, $expire);
				}
			}
			$ts = $stash->getTimestamp();

			return array('lastModified' => $ts, 'data' => $result);

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>
