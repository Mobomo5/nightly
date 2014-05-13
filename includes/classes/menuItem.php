<?php
/**
 * User: Keegan Bailey
 * Date: 13/05/14
 * Time: 9:53 AM
 *
 * menuItem DB
 * -----------
 * menuItemID INT
 * menuID INT
 * linkText VARCHAR(50)
 * href VARCHAR(2000)
 * weight INT
 * hasChildren INT
 * enabled INT
 * parent INT
 */

require_once(DATABASE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

class menuItem {

    private $id;
    private $menuID;
    private $linkText;
    private $href;
    private $weight;
    private $hasChildren;
    private $enabled;
    private $parent;
    private $children;

    private function __construct($id,$menuID,$linkText,$href,$weight,$hasChildren,$enabled,$parent,$children) {

    }
}