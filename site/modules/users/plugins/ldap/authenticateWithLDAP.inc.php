<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/13/14
 * Time: 12:44 PM
 */

class authenticateWithLDAP implements IPlugin {
    public static function init() {
        $hookEngine = HookEngine::getInstance();
        $hookEngine->addAction('userIsLoggingIn', new authenticateWithLDAP());
    }
    public static function run($inContent = '') {
        $user = currentUser::getUserSession();
        if($user->isLoggedIn()) {
            return;
        }
        $pluginEnabled = VariableEngine::getInstance()->getVariable('ldapEnabled');
        if($pluginEnabled === false) {
            return;
        }
        if($pluginEnabled->getValue() === 'false') {
            return;
        }
        $variableEngine = VariableEngine::getInstance();
        $ldapServer = $variableEngine->getVariable('ldapServer');
        if($ldapServer === false) {
            return;
        }
        $ldapDomain = $variableEngine->getVariable('ldapDomain');
        if($ldapDomain === false) {
            return;
        }
        $ldapPort = $variableEngine->getVariable('ldapServerPort');
        if($ldapPort === false) {
            return;
        }
        $ldapConnection = ldap_connect("ldap://{$ldapServer->getValue()}", (int) $ldapPort->getValue());
        if(! $ldapConnection) {
            return;
        }
        ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_start_tls($ldapConnection);
        $userName = htmlspecialchars(str_replace('@', '', $_POST['username']));
        $userName = str_replace("(", "", $userName);
        $userName = str_replace(")", "", $userName);
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
        unset($password);
        if (!$authenticated) {
            ldap_close($ldapConnection);
            return;
        }
        $database = Database::getInstance();
        $userName = $database->escapeString($userName);
        $haveSeenBefore = $database->getData("userID", "user", "userName='{$userName}' AND isExternalAuthentication=1");
        if(is_array($haveSeenBefore)) {
            ldap_close($ldapConnection);
            self::logIn($userName);
            return;
        }
        $ldapMemberGroup = $variableEngine->getVariable('ldapMemberGroup');
        if($ldapMemberGroup === false) {
            ldap_close($ldapConnection);
            return;
        }
        $dn="";
        $domain = explode('.', $ldapDomain->getValue());
        foreach($domain as $subDomain) {
            if($dn === "") {
                $dn = "dc={$subDomain}";
                continue;
            }
            $dn .= ",dc={$subDomain}";
        }
        $filter = "(&(objectclass=*)(samaccountname={$userName}))";
        $search = ldap_search($ldapConnection, $dn, $filter, array('sn', 'givenname', 'mail', 'memberOf'));
        if(! $search) {
            ldap_close($ldapConnection);
            return;
        }
        $info = ldap_get_entries($ldapConnection, $search);
        ldap_close($ldapConnection);
        if($info['count'] !== 1) {
            return;
        }
        if(strpos($info[0]['memberof'][0], "CN={$ldapMemberGroup->getValue()}") === false) {
            return;
        }
        $password = new generateRandomString(50, true, 30, 75);
        $password = $password->run();
        $defaultRoleID = $variableEngine->getVariable('ldapDefaultRoleID');
        if($defaultRoleID === false) {
            return;
        }
        $defaultRoleID = $defaultRoleID->getValue();
        //No email found in ad
        if($info[0]['count'] === 3) {
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
        //4 = the number of fields requested.
        if($info[0]['count'] !== 4) {
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
        if(! $database->insertData('user', 'userName, firstName, lastName, email, password, roleID, active, isExternalAuthentication', "'{$adUsername}', '{$firstName}', '{$lastName}', '{$email}', '{$password}', {$roleID}, 1, 1")) {
            return false;
        }
        return true;
    }
    private static function logIn($userName) {
        $database = Database::getInstance();
        $database->connect();
        $userData = $database->getData("userID, roleID, userName, givenIdentifier, password, firstName, lastName, email, profilePictureLocation, birthday", "user", "active=1 AND isExternalAuthentication=1 AND userName='{$userName}'");
        if($userData === null) {
            return;
        }
        if(count($userData) > 1) {
            return;
        }
        $profilePictureLocation = new Link($userData[0]['profilePictureLocation'], true);
        $birthday = new DateTime($userData[0]['birthday']);
        $currentUser = new CurrentUser($userData[0]['userID'], $userData[0]['roleID'], $userData[0]['givenIdentifier'], $userData[0]['userName'], $userData[0]['firstName'], $userData[0]['lastName'], $userData[0]['email'], $profilePictureLocation, $birthday, true, true, true);
        CurrentUser::setUserSession($currentUser);
        $database->updateTable('user', 'lastAccess=CURRENT_TIMESTAMP', 'userID=' . $currentUser->getUserID());
        Logger::getInstance()->logIt(new LogEntry(0, logEntryType::info, $currentUser->getFullName() . ' logged in using Active Directory from an IP of ' . $_SERVER['REMOTE_ADDR'] . '.', $currentUser->getUserID(), new DateTime()));
    }
    public static function getPriority() {
        return 5;
    }
}