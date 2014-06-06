<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/5/14
 * Time: 6:44 PM
 */
class addPermissionBlock implements block {

    private $title;
    private $content;

    public function __construct() {
        $perm = permissionEngine::getInstance()->getPermission('userCanAddPermissions');
        if (!$perm->canDo()) {
            return false;
        }

        $this->title = 'Add permissions';

        if (empty($_POST['addPermissionState'])) {
            $this->stepOne();
            return;
        }

        switch ($_POST['addPermissionState']) {
            case "1":
                $this->stepTwo();
                return;
            case "2":
                $this->stepThree();
                return;
            default:
                $this->stepOne();
                return;
        }

    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public function getContent() {
        return $this->content;
    }

    public function getType() {
        return get_class($this);
    }

    private function stepOne() {
        $this->content = '<p>Here is where you can add new permissions to the database. This feature is for plug-in developers only.</p>
                            <form method="post" id="addPerm">
                                <label>Permission Name:</label><input form="addPerm" name="permissionName" type="text" required="yes"/><br/>
                                <label>Human Name:</label><input  form="addPerm" type="text" name="humanName" required="yes"/><br/>
                                <label>description:</label><input  form="addPerm" type="text" name="description" required="yes"/><br/>
                                <input  form="addPerm" type="hidden" name="addPermissionState" value="1"/><input  form="addPerm" type="submit">
                            </form>';
    }

    private function stepTwo() {
        unset($_POST['addPermissionState']);

        $name = $_POST['permissionName'];
        $db = database::getInstance();

        $val = new validator('optionName');
        if (!$val->validate($name)) {
            echo 'oops';
            return false; // throw error
        }
        $name = $db->escapeString($name);
        $humanName = $db->escapeString($_POST['humanName']);
        $description = $db->escapeString($_POST['description']);

        $results = $db->insertData('permission', 'permissionName, humanName, permissionDescription', "'$name', '$humanName', '$description'");
        if (!$results) {
            echo $db->getError();
            return false;
        }
        // get new permission id
        $newID = $db->getData('permissionID', 'permission', "permissionName = '$name'");
        if (!$newID) {
            echo $db->getError();
            return false;
        }

        $newID = $newID[0]['permissionID'];
        // insert into db;
        if (!$db->makeCustomQuery("INSERT INTO permissionSet (canDo, roleID, permissionID) SELECT '0', roleID, '$newID' FROM role WHERE 1;")) {
            echo $db->getError();
            return false;
        }

        noticeEngine::getInstance()->addNotice(new notice(noticeType::positive, "'$humanName' has been added to the database."));
        $this->stepOne();

    }

    private function stepThree() {
    }
}