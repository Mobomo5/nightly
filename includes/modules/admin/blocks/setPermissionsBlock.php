<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/4/14
 * Time: 10:54 PM
 */
class setPermissionsBlock implements block {
    //@todo: can we find a way to override the region in the block?
    private $content;
    private $title;

    public function __construct() {
        $this->title = "Set Permissions";
        if (empty($_POST['permissionState'])) {
            $this->content = '<p>Select the roles you\'d like to set:</p><form method="post"><input type="hidden" name="permissionState" value="1">';
            $db = database::getInstance();
            $roleResults = $db->getData('roleName, roleID', 'role', '1 ORDER BY roleID');
            foreach ($roleResults as $role) {
                $this->content .= ucwords($role['roleName']) . '<input type="checkbox" name="permissionRole" value="' . $role['roleID'] . '"> <br>';
            }

            $this->content .= '<input type="submit"></form>';
            return;
        } elseif ($_POST['permissionState'] == '1') {
            unset($_POST['permissionState']);

            if (!is_numeric($_POST['permissionRole'])) {
                return false;
            }

            // get permissions pertaining to the role selected
            $db = database::getInstance();
            $roleID = $db->escapeString($_POST['permissionRole']);
            $results = $db->getData('p.permissionID, p.humanName, p.permissionDescription, s.canDo, r.roleName', 'permission p, permissionSet s,  role r', "r.roleID = s.roleID AND p.permissionID = s.permissionID AND s.roleID = '$roleID'");

            $this->title .= ': ' . $results[0]['roleName'];

            $this->content = '<table><tr><th>Permission</th><th>Allowed</th><th>Not Allowed</th></tr><form method="post">';
            // display them with radio buttons
            foreach ($results as $permission) {
                if ($permission['canDo'] == 1) {
                    $this->content .= '<tr><td title="' . $permission['permissionDescription'] . '">' . $permission['humanName'] . '</td><td><input type="radio" name="' . $permission['permissionID'] . '" value = "1"checked></td><td><input type="radio" name="' . $permission['permissionID'] . '" value = "0"></td></tr>';

                } else {
                    $this->content .= '<tr><td title="' . $permission['permissionDescription'] . '">' . $permission['humanName'] . '</td><td><input type="radio" name="' . $permission['permissionID'] . '" value = "1"></td><td><input type="radio" name="' . $permission['permissionID'] . '" value = "0" checked></td></tr>';
                }

            }

            $this->content .= '</table><input type="hidden" name="roleID" value="' . $roleID . '"><input type="hidden" name="permissionState" value="2"><input type="submit"><input type="reset" ><input type="button" value="Cancel" onclick="window.location.reload()" </form>';

        } elseif ($_POST['permissionState'] == '2') {
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
                if ($canDo) {
                    $canDo = 1;
                } else {
                    $canDo = 0;
                }

                if (!$db->updateTable('permissionSet', "canDo =  '$canDo'", "roleID = '$roleID' AND permissionID ='$permissionID'")) {
                    echo 'nope';
                    noticeEngine::getInstance()->addNotice(new notice(noticeType::error, "$permissionID could not be updated"));
                } else {
                    $notice = new notice(noticeType::positive, "$permissionID has been changed from " . intval(!$canDo) . " to $canDo");
                    noticeEngine::getInstance()->addNotice($notice);
                }
            }

        } else {
            return false;
        }
    }

    public
    function getTitle() {
        return $this->title;
    }

    public
    function setTitle($inTitle) {
        // TODO: Implement setTitle() method.
    }

    public
    function getContent() {
        return $this->content;
    }

    public
    function getType() {
        return get_class($this);
    }
}