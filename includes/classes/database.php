<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Craig
 * Date: 4/10/14
 * Time: 5:29 PM
 * To change this template use File | Settings | File Templates.
 */

class database
{

    /**
     * @var
     */
    private $dbUsername;
    private $dbPassword;
    private $db;
    private $dbServer;
    private $con;
    private $mysqli;
    private $resultArray;

    public static function getInstance()
    {

    }

    function __construct()
    {

        require_once(EDUCASK_ROOT . '/includes/config.php');
        $this->dbUsername = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->db = 'educaskOld';
        $this->dbServer = $dbServer;


        try {
            $this->connect();

        } catch (Exception $e) {
            var_dump($e->getCode() . ' ' . $e->getMessage());
            exit;
        }

    }

    function __destruct()
    {
        $this->disconnect();
    }

    /**
     * private boolean used to connect to the db using credentials stored earlier.
     */
    private function connect()
    {

        if (!$this->con = mysqli_connect($this->dbServer, $this->dbUsername, $this->dbPassword, $this->db)) {
            throw new Exception(mysqli_error($this->con));
        }
    }

    private function disconnect()
    {
        if (!isset($this->con)) {
            throw new Exception("No database object connected.");
        }

        mysqli_close($this->con);
    }

    /**
     * returns an associative array of values stored as $result[row][column]=>value
     * @param string $select
     * @param string $from
     * @param mixed|string $where
     * @throws Exception
     * @return array
     */
    public function select($select, $from, $where = '1')
    {

        $select = mysqli_real_escape_string($this->con, $select);
        $from = mysqli_real_escape_string($this->con, $from);
        $where = mysqli_real_escape_string($this->con, $where);

        $query = "SELECT $select FROM $from WHERE $where;";
        echo $query;

        if (!$results = mysqli_query($this->con, $query)) {
            echo mysqli_error($this->con);
            throw new Exception(mysqli_error($this->con));
        }
        while ($resultArray[] = $results->fetch_assoc()) {
        }
        var_dump($resultArray);
        return $resultArray;
    }
}