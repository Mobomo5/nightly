<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:54 PM
 */

require_once(NOTICE_ENGINE_OBJECT_FILE);

class setPermissionsBlock implements block {
    //@todo: can we find a way to override the region in the block?
    private $content;
    private $title;

    public function __construct() {

        $this->title = "Set Permissions";
        if (empty($_POST['permissionState'])) {
            $this->getStepOne();
            return;
        }
        switch ($_POST['permissionState']) {
            case "1":
                $this->getStepTwo();
                return true;
            case "2":
                $this->getStepThree();
                return true;
            default:
                $this->getStepOne();
        }
        return;
    }

    private function getStepOne() {
        $this->content = '<p>Select the roles you\'d like to set:</p><form method="post"><input type="hidden" name="permissionState" value="1">';
        $db = database::getInstance();
        $roleResults = $db->getData('roleName, roleID', 'role', '1 ORDER BY roleID');
        foreach ($roleResults as $role) {
            $this->content .= ucwords($role['roleName']) . '<input type="checkbox" name="permissionRole" value="' . $role['roleID'] . '"> <br>';
        }

        $this->content .= '<input type="submit"></form>';
        return;
    }

    private function getStepTwo() {
        if (empty($_POST['permissionRole'])) {
            $this->getStepOne();
            return false;
        }

        if (!is_numeric($_POST['permissionRole'])) {
            $this->getStepOne();
            return false;
        }

        // get permissions pertaining to the role selected
        $db = database::getInstance();
        $roleID = $db->escapeString($_POST['permissionRole']);
        $results = $db->getData('p.permissionID, p.humanName, p.permissionDescription, s.canDo, r.roleName', 'permission p, permissionSet s,  role r', "r.roleID = s.roleID AND p.permissionID = s.permissionID AND s.roleID = '$roleID'");

        $this->title .= ': ' . $results[0]['roleName'];

        $this->content = '<table><tr><th>Permission</th><th>Allowed</th><th>Not Allowed</th></tr><form method="post" id="setPerm">';
        // display them with radio buttons
        foreach ($results as $permission) {
            if ($permission['canDo'] == 1) {
                $this->content .= '<tr><td title="' . $permission['permissionDescription'] . '">' . $permission['humanName'] . '</td><td><input form="setPerm" type="radio" name="' . $permission['permissionID'] . '" value = "1"checked></td><td><input form="setPerm" type="radio" name="' . $permission['permissionID'] . '" value = "0"></td></tr>';

            } else {
                $this->content .= '<tr><td title="' . $permission['permissionDescription'] . '">' . $permission['humanName'] . '</td><td><input form="setPerm" type="radio" name="' . $permission['permissionID'] . '" value = "1"></td><td><input form="setPerm" type="radio" name="' . $permission['permissionID'] . '" value = "0" checked></td></tr>';
            }

        }

        $this->content .= '</table><input form="setPerm" type="hidden" name="roleID" value="' . $roleID . '"><input form="setPerm" type="hidden" name="permissionState" value="2"><input form="setPerm" type="submit"><input form="setPerm" type="reset" ><input type="button" value="Cancel" onclick="window.location.reload()" </form>';

    }

    private function getStepThree() {

        $db = database::getInstance();
        $roleID = $db->escapeString($_POST['roleID']);
        foreach ($_POST as $permissionID => $canDo) {

            if ($permissionID == 'roleID') {
                continue;
            }

            if ($permissionID == 'permissionState') {
                continue;
            }

            $permissionID = $db->escapeString($permissionID);
            $canDo = $db->escapeString($canDo);

            // see if it should be changed?

            $results = $db->getData('p.humanName, r.roleName', 'permissionSet s, permission p, role r', "r.roleID = s.roleID AND p.permissionID = s.permissionID AND s.permissionID = '$permissionID' AND s.roleID = '$roleID' AND s.canDo = '" . intval(!$canDo) . "'");
            if (!$results) {
                continue; // if it's in the results as above, that means it can be changed. This also allows us to use the permission name.
            }

            $humanName = $results[0]['humanName'];
            $roleName = $results[0]['roleName'];

            if (!$db->updateTable('permissionSet', "canDo =  '$canDo'", "roleID = '$roleID' AND permissionID ='$permissionID'")) {
                noticeEngine::getInstance()->addNotice(new notice(noticeType::error, "$humanName could not be updated for '$roleName'."));
            } else {
                $notice = new notice(noticeType::positive, "'$humanName' has been changed from " . intval(!$canDo) . " to " . intval($canDo) . " for '$roleName'.");
                noticeEngine::getInstance()->addNotice($notice);

            }
        }
        $this->getStepOne();
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
}