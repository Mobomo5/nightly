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

    private $menu;
    private $menuItem;

    private function __construct() {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }

    public function getMenu($inMenuID){
        //get a single menu from the database based off of ID
        $results = $this->db->getData("*", "menu", "'menuID' = $inMenuID");

        $menu = new menu($results[0]['menuID'],
                        $results[0]['menuName'],
                        $results[0]['themeRegion'],
                        $this->getMenuItem($results[0]['menuID']),
                        $results[0]['enabled']);

        return $menu;

    }

    public function getMenuItem($inMenuID){
        //get a single menuItem from DB based off of ID
        $results = $this->db->getData("*", "menuItem", "'menuID' = $inMenuID");

        $menuItem = new menuItem($results[0]['menuID'],
                                $results[0]['menuItemID'],
                                $results[0]['linkText'],
                                $results[0]['href'],
                                $results[0]['weight'],
                                $results[0]['hasChildren'],
                                $results[0]['enabled'],
                                $results[0]['parent']);


        return $menuItem;
    }

    public function setMenu($inMenu){
        //takes in a menu object and updates DB
        $this->menu = $inMenu;
    }

    public function setMenuItem($inMenuItem){
        //take sin a menuItem object and updates DB
        $this->menuItem  = $inMenuItem;
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