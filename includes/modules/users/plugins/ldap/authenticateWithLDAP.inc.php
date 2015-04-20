<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/13/14
 * Time: 12:44 PM
 */
require_once(PLUGIN_INTERFACE_FILE);
require_once(HOOK_ENGINE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
require_once(VARIABLE_OBJECT_FILE);
require_once(CURRENT_USER_OBJECT_FILE);
require_once(VARIABLE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
require_once(DATABASE_OBJECT_FILE);
require_once(GENERAL_ENGINE_OBJECT_FILE);
require_once(USER_OBJECT_FILE);
require_once(SYSTEM_LOG_ENGINE_FILE);
require_once(LOG_ENTRY_OBJECT_FILE);

class authenticateWithLDAP implements plugin{
    public static function init() {
        $hookEngine = hookEngine::getInstance();
        $hookEngine->addAction('userLoggingIn', new authenticateWithLDAP());
    }
    public static function run($inContent = '') {
        $user = currentUser::getUserSession();
        if($user->isLoggedIn()) {
            return;
        }
        $pluginEnabled = variableEngine::getInstance()->getVariable('ldapEnabled');
        if($pluginEnabled === false) {
            return;
        }
        if($pluginEnabled->getValue() === 'false') {
            return;
        }
        $variableEngine = variableEngine::getInstance();
        $ldapServer = $variableEngine->getVariable('ldapServer');
        if($ldapServer === false) {
            return;
        }
        $ldapDomain = $variableEngine->getVariable('ldapDomain');
        if($ldapDomain === false) {
            return;
        }
        $ldapIsActiveDirectory = $variableEngine->getVariable('ldapIsActiveDirectory');
        if($ldapIsActiveDirectory === false) {
            return;
        }
        $ldapConnection = ldap_connect($ldapServer->getValue());
        if(! $ldapConnection) {
            return;
        }
        ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_start_tls($ldapConnection);
        $userName = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        if($userName === null) {
            return;
        }
        if($userName === '') {
            return;
        }
        if($password === null) {
            return;
        }
        if($password === '') {
            return;
        }
        $authenticated = ldap_bind($ldapConnection, $userName . '@' . $ldapDomain->getValue(), $password);
        die();
        unset($password);
        if (!$authenticated) {
            ldap_close($ldapConnection);
            return;
        }
        $database = database::getInstance();
        $userName = $database->escapeString($userName);
        $haveSeenBefore = $database->getData('userID', 'activeDirectory', 'WHERE adUsername=\'' . $userName . '\'');
        if($haveSeenBefore === null) {
            $ou = $variableEngine->getVariable('ldapOrganizationUnit');
            if($ou === false) {
                ldap_close($ldapConnection);
                return;
            }
            $dn = 'cn=' . $userName . ',ou=' . $ou->getValue();
            $domain = explode('.', $ldapDomain->getValue());
            $numberOfSubServers = count($domain);
            for($i=0;$i<$numberOfSubServers;$i++) {
                $dn .= ',dc=' . $domain[$i];
            }
            $search = ldap_read($ldapConnection, $dn, '(objectclass=*)', array('sn', 'givenname', 'mail'));
            if(! $search) {
                ldap_close($ldapConnection);
                return;
            }
            $info = ldap_get_entries($ldapConnection, $search);
            ldap_close($ldapConnection);
            if($info['count'] !== 1) {
                return;
            }
            $function = new general('generateRandomString');
            $password = $function->run(array('length' => 50));
            $defaultRoleID = $variableEngine->getVariable('ldapDefaultRoleID');
            if($defaultRoleID === false) {
                return;
            }
            $defaultRoleID = $defaultRoleID->getValue();
            //No email found in ad
            if($info[0]['count'] === 2) {
                if($info[0]['sn']['count'] !== 1) {
                    return;
                }
                if($info[0]['givenname']['count'] !== 1) {
                    return;
                }
                $firstName = $info[0]['givenname'][0];
                $lastName = $info[0]['sn'][0];
                if(! self::addUser($firstName, $lastName, $userName, $password, $defaultRoleID)) {
                    return;
                }
                self::logIn($userName);
                return;
            }
            //3 = the number of fields requested.
            if($info[0]['count'] !== 3) {
                ldap_close($ldapConnection);
                return;
            }
            if($info[0]['sn']['count'] !== 1) {
                ldap_close($ldapConnection);
                return;
            }
            if($info[0]['givenname']['count'] !== 1) {
                ldap_close($ldapConnection);
                return;
            }
            if($info[0]['mail']['count'] !== 1) {
                ldap_close($ldapConnection);
                return;
            }
            $firstName = $info[0]['givenname'][0];
            $lastName = $info[0]['sn'][0];
            $email = $info[0]['mail'][0];
            if(! self::addUser($firstName, $lastName, $userName, $password, $defaultRoleID, $email)) {
                return;
            }
            self::logIn($userName);
            return;
        }
        ldap_close($ldapConnection);
        self::logIn($userName);
    }
    //Had to make this due to permission check in the core.
    private static function addUser($firstName, $lastName, $adUsername, $password, $roleID, $email='not@entered.com') {
        if($firstName === '') {
            return false;
        }
        if($firstName === null) {
            return false;
        }
        if($lastName === '') {
            return false;
        }
        if($lastName === null) {
            return false;
        }
        if($adUsername === '') {
            return false;
        }
        if($adUsername === null) {
            return false;
        }
        if($password === '') {
            return false;
        }
        if($password === null) {
            return false;
        }
        if(! is_numeric($roleID)) {
            return false;
        }
        if(! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $database = database::getInstance();
        $database->connect();
        $firstName = $database->escapeString(htmlspecialchars($firstName));
        $lastName = $database->escapeString(htmlspecialchars($lastName));
        $adUsername = $database->escapeString(htmlspecialchars($adUsername));
        $password = $database->escapeString(htmlspecialchars($password));
        $email = $database->escapeString(htmlspecialchars($email));
        if(! $database->insertData('user', 'userName, firstName, lastName, email, password, roleID', '\'' . $adUsername . '\', \'' . $firstName . '\', \'' . $lastName . '\', \'' . $email . '\', \'' . $password . '\', ' . $roleID)) {
            return false;
        }
        if(! $database->insertData('activeDirectory', 'userID, adUsername', 'LAST_INSERT_ID(), \'' . $adUsername . '\'')) {
            return false;
        }
        return true;
    }
    private static function logIn($userName) {
        $user = currentUser::getUserSession();
        $database = database::getInstance();
        $database->connect();
        $userData = $database->getData('u.firstName, u.lastName, u.userID, u.roleID', 'users u, activeDirectory ad', 'WHERE u.userID = ad.userID AND ad.adUsername = \'' . $userName . '\'');
        if($userData === null) {
            return;
        }
        if(count($userData) > 1) {
            return;
        }
        $user->setLoggedIn(true);
        $user->setFirstName($userData[0]['firstName']);
        $user->setLastName($userData[0]['lastName']);
        $user->setUserID($userData[0]['userID']);
        $user->setRoleID($userData[0]['roleID']);
        currentUser::setUserSession($user);
        $database->updateTable('users', 'lastAccess = CURRENT_TIMESTAMP', 'userID = ' . $user->getUserID());
        $log = new logEntry(1, logEntryType::neutral, $user->getUserID(), $user->getFullName() . ' logged in using Active Directory from an IP of ' . $_SERVER['REMOTE_ADDR'] . '.', $user->getUserID);
        logger::getInstance()->logIt($log);
    }
    public static function getPriority() {
        return 5;
    }
}