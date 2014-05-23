<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 5/16/14
 * Time: 10:16 AM
 */
require_once(USER_OPTION_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(PERMISSION_ENGINE_OBJECT_FILE);

/**
 * Class userOptionEngine
 */
class userOptionEngine {
    /**
     * @var
     */
    private static $instance;
    /**
     * @var
     */
    private $checkedOptions;

    /**
     * @return userOptionEngine
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new userOptionEngine();
        }
        return self::$instance;
    }

    /**
     *
     */
    public function setInstance() {

    }

    public function __construct() {
        $this->checkedOptions = array();
    }

    /**
     * @param string $inOptionName
     * @param int $userID
     *
     * @return bool
     */
    public function getOption($inOptionName, $userID) {
        // check the name
        $nameVal = new validator('optionName');

        if (!$nameVal->validate($inOptionName)) {
            return false;
        }

        // validate userID
        $idVal = new validator('userID');

        if (!$idVal->validate($userID)) {
            return false;
        }

        // open the db
        $db = database::getInstance();
        // run the query

        $results = $db->getData('*', 'userOptions', 'optionName = \'' . $inOptionName . '\'');
        if (!$results) {
            return false;
        }
        if (sizeof($results) == 0) {
            return false;
        }

        //get the first value and return the object
        $id = $results[0]['id'];
        $computerName = $results[0]['optionName'];
        $humanName = $results[0]['humanName'];
        $description = $results[0]['description'];
        $option = new userOption($id, $computerName, $humanName, $description);
        $this->checkedOptions[$inOptionName] = $option;
        return $option;
    }

    /**
     * @param userOption $inOption
     * @param int $userID
     *
     * @return bool
     * @internal param int $inRoleID
     */
    public function checkOption(userOption $inOption, $userID) {
        // check permissions
        $permEng = permissionEngine::getInstance();
        $perm = $permEng->getPermission('canCheckOption');
        if (!$permEng->checkPermission($perm, currentUser::getUserSession()->getRoleID())) {
            return false;
        }
        // check the input
        $idVal = new validator('userID');
        if (!$idVal->validate($userID)) {
            return false;
        }

        // is it stored already?
        if (isset($this->checkedOptions[$inOption->getComputerName()][$userID])) {
            return $this->checkedOptions[$inOption->getComputerName()][$userID];
        }

        // get the db
        $db = database::getInstance();

        // is the option enabled?
        $result = $db->getData('s.enabled', 'userOptions u, userOptionSet s', 'u.optionID = s.optionID AND u.optionID = \'' . $inOption->getId() . '\' AND s.userID = \'' . $userID . '\'');
        $enabled = $result[0]['enabled'];
        if (($enabled != true) OR ($enabled != false)) {
            return false;
        }

        // store the value
        $this->checkedOptions[$inOption->getComputerName()][$userID] = $enabled;

        //return the value
        return $enabled;

    }

    /**
     * @param userOption $option
     * @param int $userID
     * @param bool $value
     *
     * @internal param $optionName
     * @return bool
     */
    public function setOption(userOption $option, $userID, $value) {

        // check permissions
        $permEng = permissionEngine::getInstance();
        $perm = $permEng->getPermission('canSetOption');
        if (!$permEng->checkPermission($perm, currentUser::getUserSession()->getRoleID())) {
            return false;
        }
        // check the input

        $idVal = new validator('userID');
        if (!$idVal->validate($userID)) {
            return false;
        }

        // get db

        $db = database::getInstance();

        if ($value) {
            $store = 1;
        } else {
            $store = 0;
        }
        $results = $db->updateTable('userOptionSet', 'enabled = \'' . $store . '\'', 'optionID = \'' . $option->getId() . '\'');
        if (!$results) {
            return false;
        }
        return true;
    }

    /**
     * Adds an option to the table
     * on success, returns the optionID of the new option
     *
     * @param $inOptionName
     * @param $inHumanName
     * @param $inOptionDescription
     *
     * @return bool | int optionID of new option
     */
    public function addOption($inOptionName, $inHumanName, $inOptionDescription) {

        // check permissions
        $permEng = permissionEngine::getInstance();
        $perm = $permEng->getPermission('canAddOption');
        if (!$permEng->checkPermission($perm, currentUser::getUserSession()->getRoleID())) {
            return false;
        }

        // validate
        $nameVal = new validator('optionName');
        if (!$nameVal->validate($inOptionName)) {
            return false;
        }

        // get db
        $db = database::getInstance();
        // escape
        $inOptionName = $db->escapeString($inOptionName);
        $inHumanName = $db->escapeString($inHumanName);
        $inOptionDescription = $db->escapeString($inOptionDescription);

        $results = $db->insertData('userOptions', 'optionName, humanName, description', '\'' . $inOptionName . '\', \'' . $inHumanName . '\', \'' . $inOptionDescription . '\'');
        if (!$results) {
            return false;
        }

        $results = $db->getData('optionID', 'userOptions', 'optionName = \'' . $inOptionName . '\'');
        if (!$results) {
            return false;
        }
        return $results[0]['optionID'];
    }

    /**
     *
     *
     * @param userOption $option
     * @param int $userRole
     *
     * @internal param string $optionName
     * @return bool
     */
    public function deleteOption(userOption $option, $userRole = GUEST_ROLE_ID) {

        // check permissions
        $permEng = permissionEngine::getInstance();
        $perm = $permEng->getPermission('canDeleteOption');
        if (!$permEng->checkPermission($perm, currentUser::getUserSession()->getRoleID())) {
            return false;
        }

        // get db
        $db = database::getInstance();
        $results = $db->removeData('userOptions', 'optionName = \'' . $option->getComputerName() . ' AND optionID = \'' . $option->getId() . '\'');
        if (!$results) {
            return false;
        }
        return true;

    }

}