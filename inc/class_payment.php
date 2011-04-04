<?php
/**
 * Class for paiement management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Payements
 */
class payment extends common {
	protected $_table = 'payment';
	protected $_relatedStashes = array();
	protected $_relatedTimestamps = array('payment');

	// Constructor
	public function __construct() {
		//for "common" ($this->_db & co)
		parent::__construct();

		return $this;
	}

	/**
	 * check and parse form data for add or update
	 * errors are returned with form inputs ids as (id, text, type)
	 *
	 * @return array[]
	 */
	public function checkAndPrepareFormData(){
		$formData = array();
		$errors = array();

		$args = array(
			'action'		=> FILTER_SANITIZE_STRING,
			'id'			=> FILTER_SANITIZE_NUMBER_INT,
			'label'			=> FILTER_SANITIZE_STRING,
			'paiementDate'	=> FILTER_SANITIZE_STRING,
			'amount'		=> FILTER_SANITIZE_NUMBER_FLOAT,
			'comment'		=> FILTER_SANITIZE_STRING,
			'recurrent'		=> FILTER_SANITIZE_NUMBER_INT,
			'recipientFK'	=> FILTER_SANITIZE_STRING,
			'typeFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'currencyFK'	=> FILTER_SANITIZE_NUMBER_INT,
			'methodFK'		=> FILTER_SANITIZE_STRING,
			'originFK'		=> FILTER_SANITIZE_STRING,
			'statusFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'ownerFK'		=> FILTER_SANITIZE_NUMBER_INT,
			'locationFK'	=> FILTER_SANITIZE_STRING,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//id
			//errors are set to #label because #id is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('label', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('label', 'Identifiant du paiement incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( self::existsById($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('label', 'Identifiant du paiement inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//label
					if( is_null($label) || $label === false ){
						$errors[] = array('label', 'Libellé incorrect.', 'error');
					} elseif( empty($name) ){
						$errors[] = array('label', 'Le libellé est requis.', 'required');
					} else {
						$formData['label'] = trim($label);
					}

				//paiementDate, format dd/mm/yyyy
					if( is_null($paiementDate) || $paiementDate === false ){
						$errors[] = array('paiementDate', 'Date incorrecte.', 'error');
					} elseif( empty($name) ){
						$errors[] = array('paiementDate', 'La date est requise.', 'required');
					} else {
						$regexp = array("options"=>array("regexp"=>"^([012][123456789]|[3][01])\/([0][123456789]|[1][12])\/[20][0-9]{2}$"))));
						$paiementDate = filter_var($paiementDate, FILTER_VALIDATE_REGEXP, $regexp);
						if( $paiement === false ){
							$errors[] = array('paiementDate', 'Date incorrecte.', 'error');
						} else {
							$tmp = explode('/', $paiementDate);
							if( strtotime($tmp[1].'/'.$tmp[0].'/'.$tmp[2]) === false ){ //strtotime understand only english date format
								$errors[] = array('paiementDate', 'Date incorrecte.', 'error');
							} else {
								$formData['paiementDate'] = trim($paiementDate);
							}
						}
					}

				//amount
					if( is_null($amount) || $amount === false ){
						$errors[] = array('amount', 'Montant incorrect.', 'error');
					} else {
						$amount = filter_var($amount, FILTER_VALIDATE_FLOAT);
						if( $amount === false ){
							$errors[] = array('amount', 'Montant incorrect.', 'error');
						} else {
							$formData['amount'] = $amount;
						}
					}

				//comment
					if( is_null($comment) || $comment === false ){
						$errors[] = array('comment', 'Commentaire incorrect.', 'error');
					} else {
						$formData['comment'] = trim($comment);
					}

				//recurrent
					if( is_null($recurrent) || $recurrent === false ){
						$errors[] = array('recurrent', 'Récurrence incorrecte.', 'error');
					} else {
						$recurrent = filter_var($recurrent, FILTER_VALIDATE_BOOLEAN);
						if( $amount === false ){
							$errors[] = array('recurrent', 'Récurrence incorrecte.', 'error');
						} else {
							$formData['recurrent'] = $recurrent;
						}
					}

				//recipientFK
					if( is_null($recipientFK) || $recipientFK === false ){
						$errors[] = array('recipientFK', 'Bénéficiaire incorrecte.', 'error');
					} else {
						$recipientFK = filter_var($recipientFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $recipientFK === false ){ //not an id

							//new method ?
							$check = recipient::existsByLabel($recipientFK);
							if( $check ) $formData['recipientFK'] = $check;
							else {
								$eRecipient = new recipient();
								$eRecipient->name = $recipientFK;
								$eRecipient->save();

								$formData['recipientFK'] = $eRecipient->id;
							}
						} else {
							//check if id exists in DB
							$check = recipient::existsById($recipientFK)
							if( $check ){
								$formData['recipientFK'] = $check;
							} else {
								$errors[] = array('recipientFK', 'Identifiant du bénificiaire inconnu.', 'error');
							}
						}
					}

				//typeFK
					if( is_null($typeFK) || $typeFK === false ){
						$errors[] = array('typeFK', 'Type incorrect.', 'error');
					} else {
						$typeFK = filter_var($typeFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $typeFK === false ){
							$errors[] = array('typeFK', 'Identifiant du type incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( type::existsById($typeFK) ){
								$formData['typeFK'] = $typeFK;
							} else {
								$errors[] = array('typeFK', 'Identifiant du type inconnu.', 'error');
							}
						}
					}

				//statusFK
					if( is_null($statusFK) || $statusFK === false ){
						$errors[] = array('statusFK', 'Statut incorrect.', 'error');
					} else {
						$statusFK = filter_var($statusFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $statusFK === false ){
							$errors[] = array('statusFK', 'Identifiant du statut incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( status::existsById($statusFK) ){
								$formData['statusFK'] = $statusFK;
							} else {
								$errors[] = array('statusFK', 'Identifiant du statut inconnu.', 'error');
							}
						}
					}

				//ownerFK
					if( is_null($ownerFK) || $ownerFK === false ){
						$errors[] = array('ownerFK', 'Personne incorrecte.', 'error');
					} else {
						$ownerFK = filter_var($ownerFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $ownerFK === false ){
							$errors[] = array('ownerFK', 'Identifiant de la personne incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( owner::existsById($ownerFK) ){
								$formData['ownerFK'] = $ownerFK;
							} else {
								$errors[] = array('ownerFK', 'Identifiant de la personne inconnu.', 'error');
							}
						}
					}

				//currencyFK
					if( is_null($currencyFK) || $currencyFK === false ){
						$errors[] = array('currencyFK', 'Monnaie incorrecte.', 'error');
					} else {
						$currencyFK = filter_var($currencyFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $currencyFK === false ){
							$errors[] = array('currencyFK', 'Identifiant de la monnaie incorrect.', 'error');
						} else {
							//check if id exists in DB
							if( currency::existsById($currencyFK) ){
								$formData['currencyFK'] = $currencyFK;
							} else {
								$errors[] = array('currencyFK', 'Identifiant de la monnaie inconnu.', 'error');
							}
						}
					}

				//methodFK
					if( is_null($methodFK) || $methodFK === false ){
						$errors[] = array('methodFK', 'Méthode incorrecte.', 'error');
					} else {
						$methodFK = filter_var($methodFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $methodFK === false ){ //not an id

							//new method ?
							$check = method::existsByLabel($methodFK);
							if( $check ) $formData['methodFK'] = $check;
							else {
								$eMethod = new method();
								$eMethod->name = $methodFK;
								$eMethod->save();

								$formData['methodFK'] = $eMethod->id;
							}
						} else {
							//check if id exists in DB
							$check = method::existsById($methodFK)
							if( $check ){
								$formData['methodFK'] = $check;
							} else {
								$errors[] = array('methodFK', 'Identifiant de la méthode inconnu.', 'error');
							}
						}
					}

				//originFK
					if( is_null($originFK) || $originFK === false ){
						$errors[] = array('originFK', 'Origine incorrecte.', 'error');
					} else {
						$originFK = filter_var($originFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $originFK === false ){ //not an id

							//new origin ?
							$check = origin::existsByLabel($originFK);
							if( $check ) $formData['originFK'] = $check;
							else {
								$eOrigin = new origin();
								$eOrigin->name = $originFK;
								$eOrigin->save();

								$formData['originFK'] = $eOrigin->id;
							}
						} else {
							//check if id exists in DB
							$check = origin::existsById($originFK);
							if( $check ){
								$formData['originFK'] = $check;
							} else {
								$errors[] = array('originFK', 'Identifiant de l\'origine inconnu.', 'error');
							}
						}
					}

				//locationFK
					if( is_null($locationFK) || $locationFK === false ){
						$errors[] = array('locationFK', 'Localisation incorrecte.', 'error');
					} else {
						$locationFK = filter_var($locationFK, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $locationFK === false ){ //not an id

							//new origin ?
							$check = origin::existsByLabel($locationFK);
							if( $check ) $formData['locationFK'] = $check;
							else {
								$eLocation = new location();
								$eLocation->name = $locationFK;
								$eLocation->save();

								$formData['locationFK'] = $eLocation->id;
							}
						} else {
							//check if id exists in DB
							$check = location::existsById($locationFK);
							if( $check ){
								$formData['locationFK'] = $check;
							} else {
								$errors[] = array('locationFK', 'Identifiant de la localisation inconnu.', 'error');
							}
						}
					}
			}
		}
		$formData['errors'] = $errors;

		if( empty($errors) ){

			$this->hasAmountBeenModified = false;
			if( !empty($this->id) ){
				$this->load($id);

				if( $this->amount != $formData->amout ) $this->hasAmountBeenModified = true;
			}

			foreach( self::$_fields[$this->_table] as $k => $v ){
				$this->$k = $formData[$k];
			}
		}

		return $formData;
	}

	public function save(){
		if( $this->hasAmountBeenModified ){
			//update balance lastUpdate field for the batch
			$eBalance = new balance();
			$eBalance->loadByFKs($this->_data['currencyFK'], $this->_data['originFK'], $this->_data['typeFK']);

			if( !empty($eBalance->id) ){
				$eBalance->lastUpdate = $this->creationDate;

				$eBalance->save();
			}
		}
	}

	/**
	 * overload of common function to manage balance table cleaning
	 */
	public function delete(){
		try {
			if( parent::delete() === true ){
				$q = $this->_db->prepare('DELETE FROM balance where typeFK = :id');
				$q->excute(array(
					':id' => $this->_data['id'],
				));
			}

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	public function loadForCurrentMonth(){
		try {
			$q = $this->_db->prepare("
				SELECT * FROM :table
				WHERE paymentDate
				BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
				AND DATE_FORMAT(LAST_DAY(CURDATE(), '%Y-%m-%d')
				ORDER BY paymentDate desc
			");
			$q->excute(array(
				':id' => $this->_data['id'],
			));

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>