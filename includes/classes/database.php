<?php
echo "j";

require_once(DATABASE_INTERFACE_FILE);


/**
 * Created by JetBrains PhpStorm.
 * User: Craig
 * Date: 4/10/14
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */
class database implements databaseInterface {

    /**
     * @var
     */
    private static $instance;
    private $dbUsername;
    private $dbPassword;
    private $db;
    private $dbServer;
    private $dbObject;
    private $dbType;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new database();
            self::$instance->construct();
        }

        return self::$instance;
    }

    protected function __construct() {
        //Thou shalt not construct that which is unconstructable!
    }

    protected function __clone() {
        //Me not like clones! Me smash clones!
    }
    
    public function __destruct() {
        $this->disconnect();
    }

    public function isConnected() {
        if (empty($this->dbObject)) {
            return false;
        }
        return $this->dbObject->isConnected();
    }

    public function construct() {

        require_once(EDUCASK_ROOT . '/includes/config.php');
        $this->dbUsername = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->db = 'educaskOld';
        $this->dbServer = $dbServer;
        $this->dbType = $dbType;

        // Dynamically create the new database object, if possible.
        if (!require_once(EDUCASK_ROOT . "/includes/databases/" . $this->dbType . ".php")) {
            // @todo: ERROR the file isn't there.

        }

        $this->dbObject = $dbType::getInstance();
        if (!($this->dbObject->connect($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db))) {
            // @todo: error
        }
    }

    public function disconnect() {
        $this->dbObject->disconnect();
    }

    public function select($select, $from, $where = 1) {

        if (!($result = $this->dbObject->select($select, $from, $where))) {
            // @todo: error
        }
        return $result;
    }

    public function query($inQuery) {

        if (!($result = $this->dbObject->query($inQuery))) {
            // @todo: error
        }
        return $result;
    }

    public function insert($into, $columns, $values) {
        // TODO: Implement insert() method.
    }

    public function update($table, $set, $values) {
        // TODO: Implement update() method.
    }

    public function getUserByName($firstName, $lastName) {
        if (!($result = $this->dbObject->getUserByName($firstName, $lastName))) {
            // Todo: error
        }

        return $result;
    }

    public function getUserByNumber($studentNumber) {
        if (!($result = $this->dbObject->getUserByNumber($studentNumber))) {
            // todo: error;
        }

        return $result;
    }

    public function getUserByEmail($email) {
        if (!($result = $this->dbObject->getUserByEmail($email))) {

        }

        return $result;
    }

    public function getUserByUserID($userID) {

        if (!($result = $this->dbObject->getUserByID($userID))) {
            // @todo: error
        }

        return $result;
    }

    public function connect($dbServer, $userName, $password, $db) {
        // TODO: Implement connect() method.
    }
}