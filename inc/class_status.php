<?php
/**
 * Class for paiement status management
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Statuses
 */
class status extends common {
	protected $_table = 'status';
	protected $_relatedStashes = array();
	protected $_relatedTimestamps = array('payment');

	// Constructor
	public function __construct($id = null){
		//for "common" ($this->_db & co)
		parent::__construct($id);
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
			'name'			=> FILTER_SANITIZE_STRING,
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
			//errors are set to #name because #id is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('name', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('name', 'Identifiant du statut incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( self::existsById($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('name', 'Identifiant du statut inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//name
				if( is_null($name) || $name === false ){
					$errors[] = array('name', 'Libellé incorrect.', 'error');
				} elseif( empty($name) ){
					$errors[] = array('name', 'Le libellé est requis.', 'required');
				} else {
					$formData['name'] = trim($name);
				}

				//unicity
				if( empty($errors) ){
					$check = self::existsByLabel($name);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('name', 'Ce statut est déjà présent.', 'error');
						}
					}
				}
			}
		}
		$formData['errors'] = $errors;

		if( empty($errors) ){

			if( !empty($this->id) ){
				$this->load($id);
			}

			foreach( self::$_fields[$this->_table] as $k => $v ){
				$this->$k = $formData[$k];
			}
		}

		return $formData;
	}
}
?>