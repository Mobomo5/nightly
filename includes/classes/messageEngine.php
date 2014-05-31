<?php
/**
 * Created by PhpStorm.
 * User: Keegan
 * Date: 29/05/14
 * Time: 1:02 PM
 */

require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

class messageEngine
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new messageEngine();
        }
        return self::$instance;
    }

    //Constructor Start -- Get database and permissions engine.
    private $db;
    private $permissionObject;
    private $statusEngine;

    private function __construct()
    {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
        $this->statusEngine = statusEngine::getInstance();
    }

    public function getMessage($inID)
    {
        if (!is_numeric($inID)) {
            return;
        }
        try {
            $results = $this->db->getData("messageID, trashed, isRead, statusID, senderID, nodeID", "message", "'messageID' = $inID");
            $message = new message($results[0]['messageID'],
                $results[0]['trashed'],
                $results[0]['isRead'],
                $results[0]['statusID'],
                $results[0]['senderID'],
                $results[0]['nodeID']);
            return $message;
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function setMessage(status $inStatus)
    {
        try {
            $results = $this->db->insertData("message",
                "trashed, isRead, statusID, senderID, nodeID",
                "false, false, {$inStatus->getStatusID()}, {$inStatus->getPosterID()}, {$inStatus->getNodeID()}");
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }

    public function sendMessage(status $inStatus, $inParentStatus = null)
    {
        //store rest of status object
        $statusMsg = $inStatus->getStatus();
        $posterID = $inStatus->getPosterID();
        $nodeID = $inStatus->getNodeID();
        $votes = $inStatus->getUpVotes();

        //adds the status to the database
        $this->statusEngine->addStatusToDatabase($posterID, $inParentStatus, $votes, $nodeID, $statusMsg);

        //get a status object based off the poster ID, this may be a huge problem though.
        $status = $this->statusEngine->retrieveStatusFromDatabaseByUser($posterID);

        //insert message into db
        $this->setMessage($status);
    }

    public function deleteMessage($inID)
    {
        if (!is_numeric($inID)) {
            return;
        }

        try {
            $results = $this->db->removeData("message", "'messageID' = $inID");
        } catch (exception $ex) {
            return $ex->getMessage();
        }
    }
}