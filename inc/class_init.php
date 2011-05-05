<?php
/**
 * application initialization class
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Init
 * @category Commun
 */
class init {
	private static $_instance = null;

	//database connexion handler
	private $_dbh;

	//mysql connexion data
	private $_host;
	private $_dbname;
	private $_user;
	private $_pass;

	//account
	private $_ownerID = null;

	//months translation
	private $_monthsTranslation = array(
		'01' => 'Janvier',
		'02' => 'Février',
		'03' => 'Mars',
		'04' => 'Avril',
		'05' => 'Mai',
		'06' => 'Juin',
		'07' => 'Juillet',
		'08' => 'Août',
		'09' => 'Septembre',
		'10' => 'Octobre',
		'11' => 'Novembre',
		'12' => 'Décembre',
	);


	//constructor
	private function __construct(){
		try {
			$this->initialize();

			$this->_dbh = null;
			$this->_dbh = new DBI( 'mysql:host='.$this->_host.';dbname='.$this->_dbname, $this->_user, $this->_pass );

			$this->_dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$this->_dbh->exec( 'SET CHARACTER SET utf8' );

		} catch (PDOException $e) {
			die( 'Connection failed or database cannot be selected : ' . $e->getMessage() );
		}
	}

	/**
	 * @return singleton
	 */
	public static function getInstance(){

		if( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * @return database handler
	 */
	public function dbh() {
		return $this->_dbh;
	}

	/**
	 * @return database handler
	 */
	public function getOwner(){
		if( empty($this->_ownerID) ){
			if( isset($_SESSION['_ownerID']) ) $this->setOwner($_SESSION['_ownerID']);
		}
		return $this->_ownerID;
	}

	/**
	 * set database handler
	 */
	public function setOwner( $id ){
		$this->_ownerID = $id;
		$_SESSION['_ownerID'] = $id; //always refresh it in case of chosen owner switch
	}

	/**
	 * @return database handler
	 */
	public function getMonthsTranslation(){
		return $this->_monthsTranslation;
	}

	/**
	 * ini file parsing
	 */
	private function initialize(){
		$suivfin_infos = parse_ini_file( SF_PATH . "/inc/suivfin.ini", true );

		$this->_host	= $suivfin_infos[ ENV ]['host'];
		$this->_dbname	= $suivfin_infos[ ENV ]['dbname'];
		$this->_user	= $suivfin_infos[ ENV ]['user'];
		$this->_pass	= $suivfin_infos[ ENV ]['pass'];
	}
}

