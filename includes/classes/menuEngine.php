<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 */


require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

class menuEngine {
    /* Checking to see if the instance variable is holding onto to status engine object
     * and if it's not create one.
     */
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new menuEngine();
        }

        return self::$instance;
    }

    //Constructor Start -- Get database and permissions engine.
    private $db;
    private $permissionObject;

    private function __construct() {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }

    public function getMenu($inMenuID){
        //get a single menu from the database based off of ID
        $results = $this->db->getData("*", "menu", "'menuID' = $inMenuID");

        $menu = new menu($results[0]['menuID'],$results[0]['menuName'], $results[0]['themeRegion'] , $this->getMenuItem() ,  $results[0]['enabled']);



    }

    public function getMenuItem(){
        //get a single menuItem from DB based off of ID

        return 0;
    }

    public function setMenu(){
        //takes in a menu object and updates DB

    }

    public function setMenuItem(){
        //take sin a menuItem object and updates DB

    }

    public function addMenu(){
        //Adds a new menu to the database
    }

    public function addMenuItem(){
        //Adds a new meneItem to the database
    }

    public function deleteMenu(){
        //deletes a menu from the DB
    }

    public function deleteMenuItem(){
        //deletes a menuItem from database
    }
}