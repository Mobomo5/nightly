<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:54 AM
 *
 * menu DB
 * -------
 *
 * menuID
 * menuName
 * themeRegion
 * enabled
 */

require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

class menuItem {
    //Constructor Start -- Get database and permissions engine.
    private $db;
    private $permissionObject;

    //vars for the menu based of db schema
    private $menuID;
    private $menuName;
    private $themeRegion;
    private $enabled;

    private function __construct() {
        $this->permissionObject = permissionEngine::getInstance();
        $this->db = database::getInstance();
    }

    public function setMenuByID($inMenuID){
        //setup the menu by the menuID
        $results = $this->db->getData("*", "menu", "'menuID' = $inMenuID");

        $this->menuID = $results['menuID'];
        $this->menuName = $results['menuName'];
        $this->themeRegion = $results['themeRegion'];
        $this->enabled = $results['enabled'];
    }

    public function setMenuByName($inMenuName){
        //setup the menu by the menuID
        $results = $this->db->getData("*", "menu", "'menuName' = $inMenuName");

        $this->menuID = $results['menuID'];
        $this->menuName = $results['menuName'];
        $this->themeRegion = $results['themeRegion'];
        $this->enabled = $results['enabled'];
    }

    public function setMenuByThemeRegion($inThemeRegion){
        //setup the menu by the menuID
        $results = $this->db->getData("*", "menu", "'themeRegion' = $inThemeRegion");

        $this->menuID = $results['menuID'];
        $this->menuName = $results['menuName'];
        $this->themeRegion = $results['themeRegion'];
        $this->enabled = $results['enabled'];
    }

    public function getMenu(){
        //Assemble the menu into an array from the class and return it for whatever use is required
        $menu = Array();

        $menu[0] = $this->menuID;
        $menu[1] = $this->menuName;
        $menu[2] = $this->themeRegion;
        $menu[3] = $this->enabled;

        return $menu;
    }

    public function isEnabled(){
        //return a boolean for if the menu is enabled or not
        //TODO: find out what is needed to enable the menu
        if($this->enabled == 0){
            return false;
        } else {
            return true;
        }
    }
}