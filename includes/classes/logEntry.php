<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 10:32 AM
 */
class logEntry {
    /**
     * @var int|string
     */
    private $id;
    /**
     * @var
     */
    private $type;
    /**
     * @var
     */
    private $message;
    /**
     * @var
     */
    private $userID;
    /**
     * @param $id
     * @param $type string from logEntryType
     * @param $message
     * @param $userID
     */
    public function __construct($id, $type, $message, $userID) {
        // clean
        if (!is_numeric($id)) {
            return false;
        }
        if (!is_numeric($userID)) {
            return false;
        }
        // is the type in logEntryType
        if (!logEntryType::validateType($type)) {
            return false;
        }
        //store
        $this->id = $id;
        $this->type = $type;
        $this->message = strip_tags($message);
        $this->userID = $userID;
    }
    /**
     * @return \logEntryType
     */
    public function getType() {
        return $this->type;
    }
    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }
    /**
     * @return mixed
     */
    public function getMessage() {
        return $this->message;
    }
    /**
     * @return mixed
     */
    public function getUserID() {
        return $this->userID;
    }
}