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

    private function __construct()
    {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }

    public function getMessage()
    {

    }

    public function setMessage()
    {

    }

    public function sendMessage()
    {

    }

    public function deleteMessage()
    {

    }
}