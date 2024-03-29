<?php
set_time_limit(0);
ob_start();
session_start();
define('CURRENTLY_INSTALLING', true);
define('EDUCASK_ROOT', dirname(getcwd()));
define('EDUCASK_VERSION', '3.0Alpha3.2');
require_once(EDUCASK_ROOT . '/core/classes/Bootstrap.php');
Bootstrap::registerAutoloader();
function getErrorDiv() {
    if (!isset($_SESSION['errors'])) {
        return '';
    }
    if (empty($_SESSION['errors'])) {
        return '';
    }
    $toReturn = '<div id="errors"><ul>';
    foreach ($_SESSION['errors'] as $error) {
        $error = strip_tags($error, '<a>');
        $toReturn .= "<li>{$error}</li>";
    }
    $toReturn .= '</ul></div>';
    unset($_SESSION['errors']);
    return $toReturn;
}

function getDatabaseEngines() {
    $engines = array();
    $databaseEngines = EDUCASK_ROOT . '/core/providers/databases/*.php';
    foreach (glob($databaseEngines) as $dbEngine) {
        $name = explode('/', $dbEngine);
        $name = end($name);
        $name = str_replace('.php', '', $name);
        $engines[] = $name;
    }
    return $engines;
}

function getAction() {
    $action = preg_replace('/[^A-Za-z0-9\-\&\/\.]/', '', htmlspecialchars($_GET['action']));
    $action = preg_replace('/\s/', '', strip_tags($action));
    return $action;
}

function validateAction() {
    $action = getAction();
    if ($action == 'welcome') {
        return true;
    }
    if ($action == 'requirement') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'database') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'doDatabase') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'configure') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'doConfigure') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'install') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'doInstall') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    if ($action == 'finish') {
        define('IGNORE_CONFIG_PHP', true);
        return true;
    }
    return false;
}

function getCurrentCss($step) {
    $step = preg_replace('/\s/', '', strip_tags($step));
    $action = getAction();
    if ($action == $step) {
        return 'class="current"';
    }
    if ($action == 'do' . ucfirst($step)) {
        return 'class="current"';
    }
    return '';
}

function getContent() {
    //Comment the following three lines if you wish to overwrite an Educask install. You can also delete config.xml.
    $configFile = EDUCASK_ROOT . '/site/config.xml';
    if (is_file($configFile) && (filesize($configFile) != 0) && (! defined('IGNORE_CONFIG_PHP'))) {
        return "<p>Educask is already installed. If you wish to overwrite the install, please delete {$configFile}</p>";
    }
    $action = getAction();
    $function = $action . 'Content';
    if (! function_exists($function)) {
        return '<h1>Ooops</h1><p>Sorry, I didn\'t understand which page you wanted to see.</p>';
    }
    return $function();
}

function welcomeContent() {
    $toReturn = '<h1>Welcome</h1>';
    $toReturn .= '<p>Thank you for choosing Educask. This wizard will help you get Educask ready.</p>';
    $toReturn .= '<p>Please agree to the following license agreement before you continue. Once Educask is installed, it is assumed that you agreed to the license.</p>';
    $license = file_get_contents(EDUCASK_ROOT . '/LICENSE');
    if ($license == false) {
        $license = '<p>Please see <a href="https://github.com/educask/nightly/blob/master/LICENSE">this page</a> to read the license.</p>';
    }
    $toReturn .= "<div id=\"license\"><pre>{$license}</pre></div>";
    $toReturn .= '<p><a class="button" href="install.php?action=requirement">I Agree - Continue</a></p>';
    $_SESSION['welcomeComplete'] = true;
    return $toReturn;
}

function requirementContent() {
    if (!isset($_SESSION['welcomeComplete'])) {
        header('Location: install.php?action=welcome');
        return;
    }
    $requirements = array(
        array('name' => 'PHP', 'testFunction' => 'phpTest', 'explanationFunction' => 'phpTestExp'),
        array('name' => 'Memory Limit', 'testFunction' => 'memoryLimitTest', 'explanationFunction' => 'memoryLimitExp'),
        array('name' => 'register_globals', 'testFunction' => 'registerGlobalsTest', 'explanationFunction' => 'registerGlobalsExp'),
        array('name' => 'Magic Quotes', 'testFunction' => 'magicQuotesTest', 'explanationFunction' => 'magicQuotesExp'),
        array('name' => 'PHP Extensions', 'testFunction' => 'phpExtensionTest', 'explanationFunction' => 'phpExtensionExp'),
        array('name' => 'Database Support', 'testFunction' => 'databaseModuleTest', 'explanationFunction' => 'databaseModuleExp'),
        array('name' => 'Writeable File System', 'testFunction' => 'fileSystemTest', 'explanationFunction' => 'fileSystemExp'),
        array('name' => 'Writeable Cache Directory', 'testFunction' => 'cacheTest', 'explanationFunction' => 'cacheExp'),
        array('name' => 'Configuration File', 'testFunction' => 'configFileTest', 'explanationFunction' => 'configFileExp')
    );
    $anyFailed = false;
    $toReturn = '<h1>Verify the Requirements</h1>';
    $toReturn .= '<p>Below is a list of requirements. All rows need to be green in order to continue. If any row is red, please read the appropriate documentation.</p>';
    $toReturn .= '<table>';
    $toReturn .= '<tr><td>Requirement:</td><td>Explanation:</td></tr>';
    foreach ($requirements as $requirement) {
        if (!function_exists($requirement['testFunction'])) {
            continue;
        }
        if (!function_exists($requirement['explanationFunction'])) {
            continue;
        }
        $success = $requirement['testFunction']();
        $explanation = $requirement['explanationFunction']();
        $explanation = strip_tags($explanation, '<a> <ul> <li>');
        $name = $requirement['name'];
        $name = strip_tags($name);
        if ($success == false) {
            $anyFailed = true;
            $toReturn .= "<tr class=\"failed\"><td>{$name}</td><td>{$explanation}</td></tr>";
            continue;
        }
        $toReturn .= "<tr class=\"success\"><td>{$name}</td><td>{$explanation}</td></tr>";
    }
    $toReturn .= '</table>';
    if ($anyFailed == true) {
        return $toReturn;
    }
    $toReturn .= '<p><a class="button" href="install.php?action=database">Continue</a></p>';
    $_SESSION['requirementsComplete'] = true;
    return $toReturn;
}

function databaseContent() {
    if (!isset($_SESSION['requirementsComplete'])) {
        header('Location: install.php?action=requirement');
        return;
    }
    $toReturn = '<h1>Setup the Database</h1>';
    $toReturn .= getErrorDiv();
    $toReturn .= '<p>This form prepares the database for Educask.</p>';
    $toReturn .= '<form action="install.php?action=doDatabase" method="POST">';
    $toReturn .= '<p>Database Engine:</p>';
    $toReturn .= '<select class="formDrop" name="engine" id="engine">';
    $engines = getDatabaseEngines();
    foreach ($engines as $engine) {
        $canUse = testDatabaseModule($engine);
        if (!$canUse) {
            continue;
        }
        $toReturn .= "<option value=\"{$engine}\">{$engine}</option>";
    }
    $toReturn .= '</select>';
    $toReturn .= '<p>Server:</p>';
    $toReturn .= '<input type="text" id="server" name="server" value="localhost" class="formtext" required>';
    $toReturn .= '<p>Database:</p>';
    $toReturn .= '<input type="text" id="database" name="database" value="educask" class="formtext" required>';
    $toReturn .= '<p>Username:</p>';
    $toReturn .= '<input type="text" id="username" name="username" class="formtext" required>';
    $toReturn .= '<p>Password:</p>';
    $toReturn .= '<input type="password" id="password1" name="password1" class="formtext" required>';
    $toReturn .= '<p>Confirm Password:</p>';
    $toReturn .= '<input type="password" id="password2" name="password2" class="formtext" required>';
    $toReturn .= '<br><input type="submit" class="formbutton" value="Save">';
    $toReturn .= '</form>';
    $_SESSION['databaseComplete'] = true;
    return $toReturn;
}

function doDatabaseContent() {
    if (!isset($_SESSION['databaseComplete'])) {
        header('Location: install.php?action=database');
        return;
    }
    if (!isset($_POST['engine'])) {
        unset($_SESSION['databaseComplete']);
        header('Location: install.php?action=database');
        return;
    }
    if (!isset($_POST['server'])) {
        unset($_SESSION['databaseComplete']);
        header('Location: install.php?action=database');
        return;
    }
    if (!isset($_POST['database'])) {
        unset($_SESSION['databaseComplete']);
        header('Location: install.php?action=database');
        return;
    }
    if (!isset($_POST['username'])) {
        unset($_SESSION['databaseComplete']);
        header('Location: install.php?action=database');
        return;
    }
    if (!isset($_POST['password1'])) {
        unset($_SESSION['databaseComplete']);
        header('Location: install.php?action=database');
        return;
    }
    if (!isset($_POST['password2'])) {
        unset($_SESSION['databaseComplete']);
        header('Location: install.php?action=database');
        return;
    }
    if ($_POST['password1'] != $_POST['password2']) {
        unset($_SESSION['databaseComplete']);
        $_SESSION['errors'][] = 'The inputted passwords don\'t match.';
        header('Location: install.php?action=database');
        return;
    }
    $engine = str_replace('..', '', preg_replace('/\s+/', '', $_POST['engine']));
    $server = preg_replace('/\s+/', '', $_POST['server']);
    $database = preg_replace('/\s+/', '', $_POST['database']);
    $userName = preg_replace('/\s+/', '', $_POST['username']);
    $password = preg_replace('/\s+/', '', $_POST['password1']);
    $file = EDUCASK_ROOT . '/site/config.xml';
    $general = new generateRandomString(25);
    $appKey = $general->run();
    if($appKey === false) {
        $_SESSION['errors'][] = 'I couldn\'t generate an app key. Please try again by refreshing the page. If this problem persists, please see <a href="https://www.educask.com" target="_blank">this page</a>.'; //@ToDo: Make this link to actual help.
        header('Location: install.php?action=database');
        return;
    }
    $xmlstr = "<?xml version='1.0' encoding='UTF-8'?><educaskConfiguration></educaskConfiguration>";
    $xml = new SimpleXMLElement($xmlstr);
    $configTag = $xml->addChild("config");
    $configTag->addAttribute("appkey", $appKey);
    $databaseTag = $configTag->addChild("database");
    $databaseTag->addAttribute("server", $server);
    $databaseTag->addAttribute("name", $database);
    $databaseTag->addAttribute("username", $userName);
    $databaseTag->addAttribute("password", $password);
    $databaseTag->addAttribute("type", $engine);
    $sessionTag = $configTag->addChild("session");
    $sessionTag->addAttribute("provider", "secureSession");
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = TRUE;
    if (($dom->save($file)) === false) {
        unset($_SESSION['databaseComplete']);
        $_SESSION['errors'][] = 'I couldn\'t write the config file. Please make sure that site/config.xml is a file that can be written to by PHP.';
        header('Location: install.php?action=database');
        return;
    }
    chmod($file, 440);
    $database = Database::getInstance();
    $database->connect();
    if (!$database->isConnected()) {
        unset($_SESSION['databaseComplete']);
        $_SESSION['errors'][] = 'I couldn\'t connect to the database. Please try again.';
        header('Location: install.php?action=database');
        return;
    }
    $sqlScript = EDUCASK_ROOT . '/core/sql/educaskInstallSafe.sql';
    if (!is_file($sqlScript)) {
        unset($_SESSION['databaseComplete']);
        $_SESSION['errors'][] = 'I couldn\'t find the SQL script to create the needed tables. Please make sure that /core/sql/educaskInstallSafe.sql exists and is readable by PHP.';
        header('Location: install.php?action=database');
        return;
    }
    $sql = file_get_contents($sqlScript);
    if (!$sql) {
        unset($_SESSION['databaseComplete']);
        $_SESSION['errors'][] = 'I couldn\'t read the SQL script in order to create the needed tables. Please make sure PHP can read the file /core/sql/educaskInstallSafe.sql';
        header('Location: install.php?action=database');
        return;
    }
    $sqlStatements = explode(';', $sql);
    $noErrors = true;
    foreach ($sqlStatements as $sqlStatement) {
        $sqlStatement = trim($sqlStatement);
        if ($sqlStatement == '') {
            continue;
        }
        $success = $database->makeCustomQuery($sqlStatement);
        if ($success == true) {
            continue;
        }
        $noErrors = false;
        $table = array();
        if (!preg_match('/EXISTS \b([a-z]|[A-Z])+\b\s\(/', $sqlStatement, $table)) {
            $error = $database->getError();
            $_SESSION['errors'][] = "I couldn't create an unknown table. The database said: {$error}.";
            continue;
        }
        $table = $table[0];
        $table = str_replace('EXISTS ', '', $table);
        $table = str_replace(' (', '', $table);
        $table = trim($table);
        $error = $database->getError();
        $_SESSION['errors'][] = "I couldn't create the {$table} table. The database said: {$error}.";
    }
    if ($noErrors == false) {
        unset($_SESSION['databaseComplete']);
        $_SESSION['errors'][] = 'I couldn\'t create all of the needed tables. Please try again. If this keeps happening, please see <a href="https://www.educask.com" target="_blank">educask.com</a> for help.';
        header('Location: install.php?action=database');
        return;
    }
    header('Location: install.php?action=configure');
}

function configureContent() {
    if (!isset($_SESSION['databaseComplete'])) {
        header('Location: install.php?action=database');
        return;
    }
    $address = $_SERVER['HTTP_HOST'];
    $webDirectory = dirname($_SERVER['REQUEST_URI']);
    $toReturn = '<h1>Configure your Site</h1>';
    $toReturn .= getErrorDiv();
    $toReturn .= '<p>This form gathers the basic information for the site and prepares the first account.</p>';
    $toReturn .= '<form action="install.php?action=doConfigure" method="POST">';
    $toReturn .= '<fieldset>';
    $toReturn .= '<legend>Basic Site Information:</legend>';
    $toReturn .= '<p>Site name:</p>';
    $toReturn .= '<input type="text" id="siteName" name="siteName" class="formtext" value="Educask" required>';
    $toReturn .= '<p>Site email address:</p>';
    $toReturn .= "<input type=\"email\" id=\"siteEmail\" name=\"siteEmail\" class=\"formtext\" value=\"noreply@{$address}\" required>";
    $toReturn .= '<p>Non-secure web address:</p>';
    $toReturn .= "<input type=\"url\" id=\"nonSecureURL\" name=\"nonSecureURL\" class=\"formtext\" value=\"http://{$address}\" required>";
    $toReturn .= '<p>Secure web address:</p>';
    $toReturn .= "<input type=\"url\" id=\"secureURL\" name=\"secureURL\" class=\"formtext\" value=\"https://{$address}\" required>";
    $toReturn .= '<p>Site web directory:</p>';
    $toReturn .= "<input type=\"text\" id=\"webDirectory\" name=\"webDirectory\" class=\"formtext\" value=\"{$webDirectory}\">";
    $timeZones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    $toReturn .= '<p>The timezone for your website:</p>';
    $toReturn .= '<select class="formDrop" name="timeZone" id="timeZone">';
    foreach ($timeZones as $timeZone) {
        if ($timeZone == 'America/Vancouver') {
            $toReturn .= "<option value=\"{$timeZone}\" selected>{$timeZone}</option>";
            continue;
        }
        $toReturn .= "<option value=\"{$timeZone}\">{$timeZone}</option>";
    }
    $toReturn .= '</select>';
    $toReturn .= '</fieldset>';
    $toReturn .= '<fieldset>';
    $toReturn .= '<legend>Email Settings:</legend>';
    $toReturn .= '<p>SMTP Servers:</p>';
    $toReturn .= '<input type="text" id="smtpServer" name="smtpServer" class="formtext" value="smtp1.example.com;smtp2.example.com" required>';
    $toReturn .= '<p>SMTP Server Port:</p>';
    $toReturn .= '<input type="number" id="smtpPort" name="smtpPort" class="formtext" value="25" required>';
    $toReturn .= '<p>Username:</p>';
    $toReturn .= '<input type="text" id="smtpUserName" name="smtpUserName" class="formtext" required>';
    $toReturn .= '<p>Password:</p>';
    $toReturn .= '<input type="password" id="smtpPassword1" name="smtpPassword1" class="formtext" required>';
    $toReturn .= '<p>Confirm password:</p>';
    $toReturn .= '<input type="password" id="smtpPassword2" name="smtpPassword2" class="formtext" required>';
    $toReturn .= '<p>Encryption:</p>';
    $toReturn .= '<input type="checkbox" id="smtpUseEncryption" name="smtpUseEncryption" class="formtext" value="tls" style="width:5px">Encrypt email sending by using TLS (Transport Layer Security) to connect to the mail server.';
    $toReturn .= '</fieldset>';
    $toReturn .= '<fieldset>';
    $toReturn .= '<legend>First Account:</legend>';
    $toReturn .= '<p>Username:</p>';
    $toReturn .= '<input type="text" id="username" name="username" class="formtext" value="admin" required>';
    $toReturn .= '<p>First name:</p>';
    $toReturn .= '<input type="text" id="firstName" name="firstName" class="formtext" required>';
    $toReturn .= '<p>Last name:</p>';
    $toReturn .= '<input type="text" id="lastName" name="lastName" class="formtext" required>';
    $toReturn .= '<p>Email address:</p>';
    $toReturn .= '<input type="email" id="email" name="email" class="formtext" required>';
    $toReturn .= '<p>Password:</p>';
    $toReturn .= '<input type="password" id="password1" name="password1" class="formtext" required>';
    $toReturn .= '<p>Confirm password:</p>';
    $toReturn .= '<input type="password" id="password2" name="password2" class="formtext" required>';
    $toReturn .= '</fieldset>';
    $toReturn .= '<br><input type="submit" class="formbutton" value="Save">';
    $toReturn .= '</form>';
    $_SESSION['configureComplete'] = true;
    return $toReturn;
}

function doConfigureContent() {
    if (!isset($_SESSION['configureComplete'])) {
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['siteName'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['siteEmail'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['nonSecureURL'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['secureURL'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['webDirectory'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['timeZone'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['username'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['firstName'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['lastName'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['email'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['password1'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['password2'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if ($_POST['password1'] != $_POST['password2']) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'The inputted passwords for the first account don\'t match.';
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['smtpServer'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['smtpPort'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!is_numeric($_POST['smtpPort'])) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'Please enter a valid port for the SMTP Server.';
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['smtpUserName'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['smtpPassword1'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if (!isset($_POST['smtpPassword2'])) {
        unset($_SESSION['configureComplete']);
        header('Location: install.php?action=configure');
        return;
    }
    if ($_POST['smtpPassword1'] != $_POST['smtpPassword2']) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'The inputted passwords for the SMTP account don\'t match.';
        header('Location: install.php?action=configure');
        return;
    }
    $siteName = strip_tags(trim($_POST['siteName']));
    $siteEmail = strip_tags(trim($_POST['siteEmail']));
    $nonSecureURL = strip_tags(trim($_POST['nonSecureURL']));
    $secureURL = strip_tags(trim($_POST['secureURL']));
    $webDirectory = strip_tags(trim($_POST['webDirectory']));
    $timeZone = strip_tags(trim($_POST['timeZone']));
    $username = strip_tags(trim($_POST['username']));
    $firstName = strip_tags(trim($_POST['firstName']));
    $lastName = strip_tags(trim($_POST['lastName']));
    $email = strip_tags(trim($_POST['email']));
    $password = $_POST['password1'];
    $smtpServers = strip_tags(trim($_POST['smtpServer']));
    $smtpPort = intval($_POST['smtpPort']);
    $smtpUserName = strip_tags(trim($_POST['smtpUserName']));
    $enc = new Encrypter();
    $smtpPassword = $enc->encrypt(trim($_POST['smtpPassword1']));
    $smtpUseEncryption = isset($_POST['smtpUseEncryption']);
    $emailValidator = new emailValidator();
    if (!$emailValidator->validate($siteEmail)) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'The site email isn\'t a valid email address.';
        header('Location: install.php?action=configure');
        return;
    }
    if (!$emailValidator->validate($email)) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'The email address for the first user isn\'t valid.';
        header('Location: install.php?action=configure');
        return;
    }
    unset($emailValidator);
    $urlValidator = new urlValidator();
    $options = array('noDirectories', 'mightBeIP');
    $nonSecureOptions = array_merge($options, array('httpOnly'));
    $secureOptions = array_merge($options, array('httpsOnly'));
    if (!$urlValidator->validate($nonSecureURL, $nonSecureOptions)) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'The non-secure URL isn\'t valid. Please try again.';
        header('Location: install.php?action=configure');
        return;
    }
    if (!$urlValidator->validate($secureURL, $secureOptions)) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'The secure URL isn\'t valid. Please try again.';
        header('Location: install.php?action=configure');
        return;
    }
    unset($urlValidator);
    if ($webDirectory[0] != '/') {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t validate the web directory. Please try again.';
        header('Location: install.php?action=configure');
        return;
    }
    $timeZoneValidator = new phpTimeZoneValidator();
    if (!$timeZoneValidator->validate($timeZone)) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t validate the selected time zone. Please try again.';
        header('Location: install.php?action=configure');
        return;
    }
    unset($timeZoneValidator);
    $password = Hasher::generateHash($password);
    if ($password == false) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t properly hash your password. Please try again.';
        header('Location: install.php?action=configure');
        return;
    }
    $database = Database::getInstance();
    $database->connect();
    if (!$database->isConnected()) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t establish a connection to the database. Please try again. If you keep receiving this error, please delete the site/config.xml and start the installer again.';
        header('Location: install.php?action=configure');
        return;
    }
    if($smtpUseEncryption == 'tls') {
        $smtpEncryption = 'true';
    } else {
        $smtpEncryption = 'false';
    }
    if(substr($webDirectory, -1) !== "/") {
        $webDirectory .= '/';
    }
    $general = new generateRandomString(25);
    $cronToken = $general->run();
    if($cronToken === false) {
        $_SESSION['errors'][] = 'I couldn\'t generate a cron token. Please try again. If this problem persists, please see <a href="https://www.educask.com" target="_blank">this page</a>.'; //@ToDo: Make this link to actual help.
        header('Location: install.php?action=configure');
        return;
    }
    $variables = array(
        'cleanURLsEnabled'     => 'false',
        'educaskVersion'       => EDUCASK_VERSION,
        'guestRoleID'          => '1',
        'maintenanceMode'      => 'false',
        'siteEmail'            => $siteEmail,
        'siteTheme'            => 'default',
        'siteTimeZone'         => $timeZone,
        'siteTitle'            => $siteName,
        'siteWebAddress'       => $nonSecureURL,
        'siteWebAddressSecure' => $secureURL,
        'siteWebDirectory'     => $webDirectory,
        'smtpServer'           => $smtpServers,
        'smtpPort'             => $smtpPort,
        'smtpUserName'         => $smtpUserName,
        'smtpPassword'         => $smtpPassword,
        'smtpUseEncryption'    => $smtpEncryption,
        'cronEnabled'          => 'true',
        'cronToken'            => $cronToken,
        'lastCronRun'          => '2015-01-01 21:15:53',
        'cronRunning'          => 'false',
        'cronFrequency'        => '10 minutes',
        'minimumPasswordLength' => '5',
        'lockoutPeriod'        => '10',
        'numberOfAttemptsBeforeLockout' => '3',
        'maxSessionIdAge' => '600',
    );
    foreach ($variables as $name => $value) {
        $name = $database->escapeString($name);
        $value = $database->escapeString($value);
        if (!$database->insertData('variable', 'variableName, variableValue', "'{$name}', '{$value}'")) {
            $_SESSION['errors'][] = "I wasn't able to insert the variable {$name} with a value of {$value} into the variable table. You may want to manually add this row to the variable table in the database. For help on this, please see <a href=\"https://www.educask.com\" target=\"_blank\">this page</a>."; //@ToDo: make the link point to actual help
            continue;
        }
    }
    $database->updateTable('variable', 'readOnly=1', "variableName='educaskVersion'");
    $sqlScript = EDUCASK_ROOT . '/core/sql/defaultRolesInstallSafe.sql';
    if (!is_file($sqlScript)) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t find the SQL script to create the needed roles. Please make sure that ' . $sqlScript . ' exists and is readable by PHP.';
        header('Location: install.php?action=configure');
        return;
    }
    $sql = file_get_contents($sqlScript);
    if (!$sql) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t read the SQL script in order to create the needed roles. Please make sure PHP can read the file ' . $sqlScript;
        header('Location: install.php?action=configure');
        return;
    }
    $sqlStatements = explode(';', $sql);
    foreach ($sqlStatements as $sqlStatement) {
        $sqlStatement = trim($sqlStatement);
        if ($sqlStatement == '') {
            continue;
        }
        $database->makeCustomQuery($sqlStatement);
    }
    $username = $database->escapeString($username);
    $firstName = $database->escapeString($firstName);
    $lastName = $database->escapeString($lastName);
    $email = $database->escapeString($email);
    $password = $database->escapeString($password);
    $success = $database->insertData('user', 'userID, userName, firstName, lastName, email, password, roleID', "0, 'anonGuest', 'Anonymous', 'Guest', 'anon@anon.ca', '', 1");
    $success = $success && $database->updateTable("user", "userID=0", "userID=1");
    $success = $success && $database->insertData('user', 'userID, userName, firstName, lastName, email, password, roleID', "1, '{$username}', '{$firstName}', '{$lastName}', '{$email}', '{$password}', 4");
    if (!$success) {
        unset($_SESSION['configureComplete']);
        $_SESSION['errors'][] = 'I couldn\'t create the new user account. Please try again. For help on this, please see <a href="https://www.educask.com" target="_blank">this page</a>.'; //@ToDo: make the link point to actual help
        header('Location: install.php?action=configure');
        return;
    }
    $database->makeCustomQuery("ALTER TABLE user AUTO_INCREMENT=2");
    header('Location: install.php?action=install');
}

function installContent() {
    if (!isset($_SESSION['configureComplete'])) {
        header('Location: install.php?action=configure');
        return;
    }
    $general = new generateRandomString(30);
    $token = $general->run();
    $_SESSION['token'] = $token;
    $_SESSION['moduleStatus'] = 'Installing...';
    $_SESSION['moduleProgress'] = 0;
    $toReturn = '<h1>Installing Educask</h1>';
    $toReturn .= '<p>Please be patient while I install Educask.</p>';
    $toReturn .= '<div id="progressBar"><div id="progress" style="width: 0;"></div></div>';
    $toReturn .= '<p id="currentStep">Installing...</p>';
    $toReturn .= '<p id="addLink"></p>';
    $toReturn .= "<script type=\"text/javascript\">
                        var updateInt = setInterval(function(){doUpdate()}, 1000);
                        <!--  AJAXInteraction class from dev.fyicenter.com/Interview-Questions/AJAX/How_do_I_handle_concurrent_AJAX_requests_.html -->
                        function AJAXInteraction(url, callback) {
                            var req = init();
                            req.onreadystatechange = processRequest;

                            function init() {
                              if (window.XMLHttpRequest) {
                                return new XMLHttpRequest();
                              } else if (window.ActiveXObject) {
                                return new ActiveXObject('Microsoft.XMLHTTP');
                              }
                            }
                            function processRequest () {
                              if (req.readyState != 4) {
                                return;
                              }
                              if (req.status != 200) {
                                return;
                              }
                              if (callback) callback(req.responseText);
                            }
                            this.doGet = function() {
                              req.open('GET', url, true);
                              req.send(null);
                            }
                        }
                        function doUpdate() {
                             var progress = new AJAXInteraction('installModules.php?action=percent&token={$token}',
                              function(data) {
                                if(parseInt(data) >= 100) {
                                    clearInterval(updateInt);
                                }
                                var progress = document.getElementById('progress');
                                progress.style.width = data + '%';
                              });
                              progress.doGet();
                              var message = new AJAXInteraction('installModules.php?action=status&token={$token}',
                              function(data) {
                                 var statusElem = document.getElementById('currentStep');
                                 statusElem.innerHTML = data;
                              });
                              message.doGet();
                        }
                         var install = new AJAXInteraction('install.php?action=doInstall&token={$token}',
                            function() {
                                var statusElem = document.getElementById('currentStep');
                                 statusElem.innerHTML = 'Done';
                                 var addLink = document.getElementById('addLink');
                                 addLink.innerHTML = '<a href=\"install.php?action=finish\" class=\"button\">Continue</a>.';
                            }
                         );
                         install.doGet();
                  </script>";
    $_SESSION['installComplete'] = true;
    return $toReturn;
}

function doInstallContent() {
    $knownToken = strip_tags($_SESSION['token']);
    $givenToken = strip_tags($_GET['token']);
    if ($knownToken != $givenToken) {
        unset($_SESSION['token']);
        header("HTTP/1.1 404 Not Found");
        die();
    }
    $installers = glob(EDUCASK_ROOT . '/site/modules/*/install.php');
    $numberToDo = count($installers);
    if ($numberToDo == 0) {
        $_SESSION['moduleProgress'] = 100;
        $_SESSION['moduleStatus'] = 'Done';
        session_write_close();
        return;
    }
    $i = 0;
    foreach ($installers as $installer) {
        session_start();
        $_SESSION['moduleProgress'] = ($i / $numberToDo) * 100;
        $_SESSION['moduleStatus'] = 'Installing ' . str_replace('/install.php', '', $installer);
        session_write_close();
        require_once($installer);
        $i++;
    }
    session_start();
    $_SESSION['moduleProgress'] = 100;
    $_SESSION['moduleStatus'] = 'Done';
    session_write_close();
}

function finishContent() {
    if (!isset($_SESSION['installComplete'])) {
        header('Location: install.php?action=install');
        return;
    }
    $toReturn = '<h1>All Done</h1>';
    $toReturn .= '<p>Congratulations! Educask is now installed!</p><p>You should delete public_html/install.php, public_html/installModules.php, core/sql/educaskInstallsafe.sql, and core/sql/defaultRolesInstallSafe.sql.</p>';
    $toReturn .= '<p><a class="button" href="index.php">Visit the Site</a></p>';
    session_destroy();
    return $toReturn;
}

function phpTest() {
    if (version_compare(PHP_VERSION, '5.4') < 0) {
        return false;
    }
    return true;
}

function phpTestExp() {
    if (!phpTest()) {
        return 'Your PHP version is ' . PHP_VERSION . ' and it\'s too old. Please upgrade to the latest PHP version. Educask needs PHP newer than 5.2.4.';
    }
    return 'Your PHP version is ' . PHP_VERSION;
}

function registerGlobalsTest() {
    if (ini_get('register_globals') == 1) {
        return false;
    }
    return true;
}

function registerGlobalsExp() {
    if (!registerGlobalsTest()) {
        return 'Please turn register_globals off in the php.ini file or in a .htaccess file. Please see <a href="http://ca1.php.net/manual/en/security.registerglobals.php" target="_blank">this page</a> for more information.';
    }
    return 'register_globals is turned off.';
}

function magicQuotesTest() {
    if (ini_get('magic_quotes_gpc') == 1) {
        return false;
    }
    if (ini_get('magic_quotes_runtime') == 1) {
        return false;
    }
    if (ini_get('magic_quotes_sybase') == 1) {
        return false;
    }
    return true;
}

function magicQuotesExp() {
    if (!magicQuotesTest()) {
        return 'Please turn Magic Quotes off in the php.ini file or in a .htaccess file. Please see <a href="http://www.php.net/manual/en/security.magicquotes.php" target="_blank">this page</a> for more information.';
    }
    return 'Magic Quotes are disabled.';
}

function phpExtensionTest($returnArrayOfFailed = false) {
    if (!is_bool($returnArrayOfFailed)) {
        return false;
    }
    $extensionsToTest = array('xml', 'simplexml', 'zip', 'session', 'hash', 'Core', 'calendar', 'json', 'date', 'mcrypt');
    $failed = array();
    foreach ($extensionsToTest as $extension) {
        if (extension_loaded($extension) == true) {
            continue;
        }
        $failed[] = $extension;
    }
    if ($returnArrayOfFailed == true) {
        return $failed;
    }
    if (!empty($failed)) {
        return false;
    }
    return true;
}

function phpExtensionExp() {
    $extensionsFailed = phpExtensionTest(true);
    if (empty($extensionsFailed)) {
        return 'All needed PHP extensions are ready for use!';
    }
    $toReturn = 'I detected that some of the PHP extensions I need aren\'t available:';
    $toReturn .= '<ul>';
    foreach ($extensionsFailed as $extension) {
        $toReturn .= "<li>{$extension} is not installed or enabled.</li>";
    }
    $toReturn .= '</ul>';
    return $toReturn;
}

function memoryLimitTest() {
    $memoryLimit = ini_get('memory_limit');
    $intMemoryLimit = returnBytes($memoryLimit);
    $minMemory = 33554432;
    if ($intMemoryLimit < $minMemory) {
        return false;
    }
    return true;
}

function memoryLimitExp() {
    $memoryLimit = ini_get('memory_limit');
    if (!memoryLimitTest()) {
        return "Your current memory limit is set at {$memoryLimit}. This isn't enough - Educask needs 32M minimum. Please see <a href=\"http://www.php.net/manual/en/ini.core.php#ini.memory-limit\" target=\"_blank\">this page</a> for more information.";
    }
    return "Your current memory limit is set at {$memoryLimit}.";
}

function databaseModuleTest() {
    $engines = getDatabaseEngines();
    foreach ($engines as $engine) {
        if (testDatabaseModule($engine) == true) {
            return true;
        }
    }
    return false;
}

function databaseModuleExp() {
    if (!databaseModuleTest()) {
        return 'I couldn\'t find any supported database extensions on your server. May I <a href="http://www.php.net/manual/en/book.mysqli.php" target="_blank">recommend MySQLi</a>?';
    }
    return 'I found a supported database engine.';
}

function testDatabaseModule($inModule) {
    $inModule = str_replace(".", "", $inModule);
    if(! require_once(EDUCASK_ROOT . '/core/providers/databases/' . $inModule . '.php')) {
        return false;
    }
    $requiredModule = $inModule::getRequiredPHPDatabaseModule();
    if (extension_loaded($requiredModule) == false) {
        return false;
    }
    return true;
}

function fileSystemTest() {
    error_reporting(0);
    $directory = EDUCASK_ROOT . '/uploads';
    if (!is_dir($directory)) {
        $couldWrite = mkdir($directory);
        if (!$couldWrite) {
            return false;
        }
        if (!chmod($directory, 0755)) {
            return false;
        }
    }
    $couldWrite = file_put_contents($directory . '/test.txt', 'This is a test write.');
    if (!$couldWrite) {
        return false;
    }
    $couldDelete = unlink($directory . '/test.txt');
    if (!$couldDelete) {
        return false;
    }
    return true;
}

function fileSystemExp() {
    if (!fileSystemTest()) {
        return 'The uploads directory does not exist and an attempt to make it failed. Please make the directory and make sure it\'s writeable by PHP.';
    }
    return 'The uploads directory is a go. You can change the location of it later.';
}
function cacheTest() {
    error_reporting(0);
    $directory = EDUCASK_ROOT . '/cache';
    if (!is_dir($directory)) {
        $couldWrite = mkdir($directory);
        if (!$couldWrite) {
            return false;
        }
        if (!chmod($directory, 0755)) {
            return false;
        }
    }
    $couldWrite = file_put_contents($directory . '/test.txt', 'This is a test write.');
    if (!$couldWrite) {
        return false;
    }
    $couldDelete = unlink($directory . '/test.txt');
    if (!$couldDelete) {
        return false;
    }
    return true;
}
function cacheExp() {
    if (!cacheTest()) {
        return 'The cache directory does not exist and an attempt to make it failed. Please make the directory and make sure it\'s writeable by PHP.';
    }
    return 'The cache directory is a go.';
}
function configFileTest() {
    error_reporting(0);
    $config = EDUCASK_ROOT . '/site/config.xml';
    if (!is_file($config)) {
        $couldWrite = file_put_contents($config, 'Test Write');
        if (!$couldWrite) {
            return false;
        }
        file_put_contents($config, '');
        return true;
    }
    $couldWrite = file_put_contents($config, 'Test Write');
    if (!$couldWrite) {
        return false;
    }
    file_put_contents($config, '');
    return true;
}

function configFileExp() {
    if (!configFileTest()) {
        return 'The configuration file, site/config.xml, does not exist. An attempt to make it and write to it failed. Please make the file and make it writeable by PHP.';
    }
    return 'The configuration file is ready for settings.';
}

//Function borrowed from http://www.php.net/manual/en/function.ini-get.php#106518
function returnBytes($val) {
    if (empty($val)) {
        return 0;
    }
    $val = trim($val);
    preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);
    $last = '';
    if (isset($matches[2])) {
        $last = $matches[2];
    }
    if (isset($matches[1])) {
        $val = (int)$matches[1];
    }
    switch (strtolower($last)) {
        case 'g':
        case 'gb':
            $val *= 1024;
        case 'm':
        case 'mb':
            $val *= 1024;
        case 'k':
        case 'kb':
            $val *= 1024;
    }
    return (int)$val;
}

if (!isset($_GET['action'])) {
    header('Location: install.php?action=welcome');
}
if (!validateAction()) {
    header('Location: install.php?action=welcome');
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Install | Educask Development Core</title>
        <link rel="icon" type="image/png" href="images/educaskFavicon.ico">
        <style type="text/css">
            body {
                margin: 0;
                padding: 0;
                color: #333333;
                font-family: 'Lucida Sans Unicode', 'Bitstream Vera Sans', 'Trebuchet Unicode MS', 'Lucida Grande', Verdana, Helvetica, sans-serif;
            }

            #sidebar {
                position: fixed;
                float: left;
                width: 20%;
                min-width: 300px;
                height: 100%;
                margin: 0;
                background-color: #166A8A;
                box-shadow: 1px 1px 5px #777777;
            }

            #sidebar p, img {
                padding: 5px;
                color: white;
            }

            #sidebar ul {
                list-style-type: none;
            }

            #sidebar li {
                display: block;
                color: #FFF;
                padding: 5px;
                text-decoration: none;
            }

            #content {
                float: right;
                width: 79%;
                max-width: calc(100% - 310px);
                padding: 5px;
            }

            .current {
                font-weight: bold;
                background-color: #106383;
                box-shadow: 1px 1px 1px #333 inset;
            }

            #footer {
                position: absolute;
                display: block;
                bottom: 20px;
                left: 0;
                right: 0;
                width: 100%;
                height: 2.5em;
                list-style: none;
            }

            #footer ul {
                list-style: none;
                display: -webkit-box;
                display: -moz-box;
                display: -ms-flexbox;
                display: -webkit-flex;
                display: flex;
                flex-flow: row wrap;
                -webkit-flex-flow: row wrap;
                align-content: stretch;
                justify-content: flex-start;
                height: 100%;
                width: 100%;
                margin: 0;
            }

            #footer ul li {
                height: 2.4em;
                line-height: 2.4em;
            }

            #footer ul li img {
                height: 2.1em;
                width: auto;
                padding-top: 1px;
            }

            #footer ul li img:hover {
                padding-top: 0;
            }

            input {
                box-sizing: border-box;
            }

            a.button:link {
                padding: 10px;
                background: #2580a2;
                color: #ffffff;
                border: none;
                font-size: 18px;
                text-decoration: none;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, .5);
                -moz-border-radius: 5px;
                border-radius: 5px;
                -o-border-radius: 5px;
            }

            a.button:hover, a.button:active {
                text-shadow: 0 -1px 0 rgba(0, 0, 0, .5);
                background: #166A8A;
                color: #FFFFFF;
                cursor: pointer;
            }

            .formtext, .formtextarea, .formDrop {
                width: 762px;
                padding: 10px;
                font-size: 18px;
                border: 1px gray solid;
                color: #333;
                font-family: 'Lucida Sans Unicode', 'Bitstream Vera Sans', 'Trebuchet Unicode MS', 'Lucida Grande', Verdana, Helvetica, sans-serif;
                outline: none;
            }

            .formtext:hover, .formtext:focus, .formtextarea:hover, .formtextarea:focus, .formDrop:hover, .formDrop:focus {
                border: 2px #2580a2 solid;
                padding: 9px;
                color: black;
                outline: none;
            }

            .formtext.error {
                border-color: red;
                outline: none;
            }

            .formtextarea.error {
                border-color: red;
                outline: none;
            }

            .formbutton {
                padding: 10px;
                background: #2580a2;
                color: #ffffff;
                border: none;
                font-size: 18px;
                text-decoration: none;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, .5);
                -moz-border-radius: 5px;
                border-radius: 5px;
                -o-border-radius: 5px;
            }

            .formbutton:hover, .formbutton:focus {
                text-shadow: 0 -1px 0 rgba(0, 0, 0, .5);
                background: #166A8A;
                color: #FFFFFF;
                cursor: pointer;
            }

            td {
                padding: 5px;
            }

            .success {
                background-color: greenyellow;
            }

            .failed {
                background-color: red;
            }

            #errors {
                color: white;
                background-color: red;
                padding: 10px;
            }

            #progressBar {
                height: 25px;
                width: 100%;
                background-color: #eeeeee;
            }

            #progress {
                height: 100%;
                background-color: #166A8A;
                box-shadow: 1px 1px 1px #333 inset;
            }
        </style>
    </head>
    <body>
    <div id="sidebar">
        <div id="titleBar">
            <img src="images/educasklogo.png">

            <p>Educask <?php echo EDUCASK_VERSION ?> - Developer Preview</p>
        </div>
        <ul>
            <li <?php echo getCurrentCss('welcome'); ?>>Welcome</li>
            <li <?php echo getCurrentCss('requirement'); ?>>Verify Requirements</li>
            <li <?php echo getCurrentCss('database'); ?>>Set up Database</li>
            <li <?php echo getCurrentCss('configure'); ?>>Configure Site</li>
            <li <?php echo getCurrentCss('install'); ?>>Install</li>
            <li <?php echo getCurrentCss('finish'); ?>>Finish</li>
        </ul>
        <div id="footer">
            <ul>
                <!--icons borrowed from http://simpleicons.org/-->
                <li><a href="https://www.educask.com" target="_blank"><img
                            src="images/educasklogo-e.png"/></a></li>
                <li><a href="https://www.facebook.com/Educask" target="_blank"><img
                            src="images/facebook.png"/></a></li>
                <li><a href="https://github.com/educask" target="_blank"><img
                            src="images/github.png"/></a></li>
                <li><a href="https://twitter.com/educask" target="_blank"><img
                            src="images/twitter.png"/></a></li>
            </ul>
        </div>
    </div>
    <div id="content">
        <?php echo getContent(); ?>
    </div>
    </body>
    </html>
<?php ob_flush(); ?>