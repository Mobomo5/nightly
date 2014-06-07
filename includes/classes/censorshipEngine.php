<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 */
require_once(DATABASE_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

class censorshipEngine
{

    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new censorshipEngine();
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

    public function addWordToDatabase($inString, $inReplacement = null)
    {
        $this->db->insertData("censorship", "word, replacement, banned", "$inString, $inReplacement, 1");
    }

    public function censorString($inString)
    {
        $results = $this->db->getData("word, replacement, banned", "censorship", "");

        $toReturn = $inString;
        $badWords = array();

        foreach ($results as $row) {
            if ($row['banned'] = 0) {
                break;
            }

            if ($row['replacement'] == null) {
                $repWord = "*" * strlen($row['word']);
            } else {
                $repWord = $row['replacement'];
            }

            $badWords[$row['word']] = $repWord;
        }

        //This IS case sensitive
        return strtr($toReturn, $badWords);
    }

    public function removeWordFromDatabase($inString)
    {
        $this->db->removeData("censorship", "word = $inString");
    }
}