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
    private $children = array();

    private function __construct($inID, $inMenuID, $inLinkText, $inHref, $inWeight,
                                 $inHasChildren, $inEnabled, $inParent, $inChildren) {

        if(!is_numeric((int) $inID)) return false;
        if(!is_numeric((int) $inMenuID)) return false;
        if(!is_string($inLinkText)) return false;
        if(!is_string($inHref)) return false;
        if(!is_numeric((int) $inWeight)) return false;
        if(!is_numeric((int) $inHasChildren)) return false;
        if(!is_numeric((int) $inEnabled)) return false;
        if(!is_numeric((int) $inParent)) return false;
        if(!is_numeric((int) $inChildren)) return false;

        $this->id = (int) $inID;
        $this->menuID =  (int) $inMenuID;
        $this->linkText = $inLinkText;
        $this->href = $inHref;
        $this->weight = (int) $inWeight;
        $this->hasChildren = (int) $inHasChildren;
        $this->enabled = (int) $inEnabled;
        $this->parent = (int) $inParent;
        $this->children += (int) $inChildren;

        return true;
    }

    public function getID(){
        return $this->id;
    }

    public function getMenuID(){
        return $this->menuID;
    }

    public function setMenuID($inMenuID){
        $this->menuID = $inMenuID;
    }

    public function getLinkText(){
        return $this->linkText;
    }

    public function setLinkText($inLinkText){
        $this->linkText = $inLinkText;
    }

    public function getHref(){
        return $this->href;
    }

    public function setHref($inHref){
        if(!is_string($inHref)) return;
        $this->href = $inHref;
    }

    public function getWeight(){
        return $this->weight;
    }

    public function setWeight($inWeight){
        $this->weight = $inWeight;
    }

    public function hasChildren(){
        if($this->hasChildren == 0){
            return false;
        }
        return true;
    }

    public function setHasChildren($inHasChildren){
        if($inHasChildren == false || $inHasChildren == 0){
            $this->hasChildren = 0;
        } else if($inHasChildren == true || $inHasChildren == 1){
            $this->hasChildren = 1;
        } else {
            return;
        }
    }

    public function isEnabled(){
        if($this->enabled == 0){
            return false;
        }

        return true;
    }

    public function setEnabled($inSetEnabled){
        if($inSetEnabled == false || $inSetEnabled == 0){
            $this->enabled = 0;
        } else if($inSetEnabled == true || $inSetEnabled == 1){
            $this->enabled = 1;
        } else {
            return;
        }
    }

    public function getParent(){
        return $this->parent;
    }

    public function setParent($inParent){
        $this->parent = $inParent;
    }

    public function getChildren(){
        return $this->children;
    }

    public function addChild($inChild){
        $this->children += $inChild;
    }

    public function removeChild($inChild){
        foreach($this->children as $child){
            if($child == $inChild){
                $this->children -= $child;
                return;
            }
        }
    }
}