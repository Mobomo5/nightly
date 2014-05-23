<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/23/14
 * Time: 10:32 AM
 */
class logEntry {
    private $id;
    private $date;
    private $message;
    private $userID;

    public function __construct($id, $date, $message, $userID) {
        // clean
        if (!is_numeric($id)) {
            return false;
        }
        if (strlen($id) > 10) {
            return false;
        }
        //@todo: validate date
        $val = new validator('userID');
        if (!$val->validate($userID)) {
            return false;
        }

        //store
        $this->id = $id;
        $this->date = $date;
        $this->message = $message;
        $this->userID = $userID;

    }

    /**
     * @return mixed
     */
    public function getDate() {
        return $this->date;
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