<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 02/06/14
 * Time: 9:37 PM
 */
define('EDUCASK_ROOT', getcwd());
define('DATABASE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/database.php');
define('DATABASE_INTERFACE_FILE', EDUCASK_ROOT . '/includes/interfaces/databaseInterface.php');
require_once(DATABASE_OBJECT_FILE);
require_once(DATABASE_INTERFACE_FILE);
function getDatabaseEngines() {
    $engines = array();
    foreach(glob('includes/databases/*.php') as $dbEngine) {
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
        return true;
    }
    if ($action == 'database') {
        return true;
    }
    if ($action == 'configure') {
        return true;
    }
    if ($action == 'install') {
        return true;
    }
    if ($action == 'finish') {
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
    return '';
}
function getContent() {
    //Comment the following three lines if you wish to overwrite an Educask install. You can also delete config.php.
    //@ToDo: Uncomment these lines later.
    //if(is_file('includes/config.php')) {
    //    return '<p>Educask is already installed. If you wish to overwrite the install, please delete includes/config.php</p>';
    //}
    $action = getAction();
    $function = $action . 'Content';
    if (function_exists($function)) {
        return $function();
    }
    return '<h1>Ooops</h1><p>Sorry, I didn\'t understand which page you wanted to see.</p>';
}
function welcomeContent() {
    $toReturn = '<h1>Welcome</h1>';
    $toReturn .= '<p>Thank you for choosing Educask. This wizard will help you get Educask ready.</p>';
    $toReturn .= '<p>Please agree to the following license agreement before you continue. Once Educask is installed, it is assumed that you agreed to the license.</p>';
    $license = file_get_contents('LICENSE');
    if ($license == false) {
        $license = '<p>Please see <a href="https://github.com/educask/nightly/blob/master/LICENSE">this page</a> to read the license.</p>';
    }
    $toReturn .= "<div id=\"license\"><pre>{$license}</pre></div>";
    $toReturn .= '<p><a class="button" href="install.php?action=requirement">I Agree - Continue</a></p>';
    return $toReturn;
}
function requirementContent() {
    $requirements = array(
        array('name' => 'PHP', 'testFunction' => 'phpTest', 'explanationFunction' => 'phpTestExp'),
        array('name' => 'Memory Limit', 'testFunction' => 'memoryLimitTest', 'explanationFunction' => 'memoryLimitExp'),
        array('name' => 'register_globals', 'testFunction' => 'registerGlobalsTest', 'explanationFunction' => 'registerGlobalsExp'),
        array('name' => 'Magic Quotes', 'testFunction' => 'magicQuotesTest', 'explanationFunction' => 'magicQuotesExp'),
        array('name' => 'PHP Extensions', 'testFunction' => 'phpExtensionTest', 'explanationFunction' => 'phpExtensionExp'),
        array('name' => 'Database Support', 'testFunction' => 'databaseModuleTest', 'explanationFunction' => 'databaseModuleExp'),
        array('name' => 'Writeable File System', 'testFunction' => 'fileSystemTest', 'explanationFunction' => 'fileSystemExp'),
        array('name' => 'Configuration File', 'testFunction' => 'configFileTest', 'explanationFunction' => 'configFileExp')
    );
    $anyFailed = false;
    $toReturn = '<h1>Requirements</h1>';
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
    return $toReturn;
}
function databaseContent() {
    $toReturn = '<h1>Database</h1>';
    $toReturn .= '<p><a class="button" href="install.php?action=configure">Continue</a></p>';
    return $toReturn;
}
function configureContent() {
    $toReturn = '<h1>Configure</h1>';
    $toReturn .= '<p><a class="button" href="install.php?action=install">Continue</a></p>';
    return $toReturn;
}
function installContent() {
    $toReturn = '<h1>Install</h1>';
    $toReturn .= '<p><a class="button" href="install.php?action=finish">Continue</a></p>';
    return $toReturn;
}
function finishContent() {
    $toReturn = '<h1>All Done</h1>';
    $toReturn .= '<p>Congratulations! Educask is now installed!</p>';
    $toReturn .= '<p><a class="button" href="index.php">Visit the Site</a></p>';
    return $toReturn;
}
function phpTest() {
    if (version_compare(PHP_VERSION, '5.2.4') < 0) {
        return false;
    }
    return true;
}
function phpTestExp() {
    if (!phpTest()) {
        return 'Your PHP version is ' . PHP_VERSION . ' and it\'s too old. Please upgrade to the latest PHP. Educask needs PHP newer than 5.2.4.';
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
    $extensionsToTest = array('xml', 'zip', 'session', 'hash', 'Core', 'calendar', 'json', 'date');
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
    if($intMemoryLimit < $minMemory) {
        return false;
    }
    return true;
}
function memoryLimitExp() {
    $memoryLimit = ini_get('memory_limit');
    if(! memoryLimitTest()) {
        return "Your current memory limit is set at {$memoryLimit}. This isn't enough - Educask needs 32M minimum. Please see <a href=\"http://www.php.net/manual/en/ini.core.php#ini.memory-limit\" target=\"_blank\">this page</a> for more information.";
    }
    return "Your current memory limit is set at {$memoryLimit}.";
}
function databaseModuleTest() {
    $engines = getDatabaseEngines();
    foreach($engines as $engine) {
        require_once('includes/databases/' . $engine . '.php');
        $requiredModule = $engine::getRequiredPHPDatabaseModule();
        if(testDatabaseModule($requiredModule) == true) {
            return true;
        }
    }
    return false;
}
function databaseModuleExp() {
    if(! databaseModuleTest()) {
        return 'I couldn\'t find any supported database extensions on your server. May I <a href="http://www.php.net/manual/en/book.mysqli.php" target="_blank">recommend MySQLi</a>?';
    }
    return 'I found a supported database engine.';
}
function testDatabaseModule($inModule) {
    if (extension_loaded($inModule) == false) {
        return false;
    }
    return true;
}
function fileSystemTest() {
    error_reporting(0);
    $directory = EDUCASK_ROOT . '/uploads';
    if(! is_dir($directory)) {
        $couldWrite = mkdir($directory);
        if(! chmod($directory, 0755)) {
            return false;
        }
        if(! $couldWrite) {
            return false;
        }
    }
    $couldWrite = file_put_contents($directory . '/test.txt', 'This is a test write.');
    if(! $couldWrite) {
        return false;
    }
    $couldDelete = unlink($directory . '/test.txt');
    if(! $couldDelete) {
        return false;
    }
    return true;
}
function fileSystemExp() {
    if(! fileSystemTest()) {
        return 'The uploads directory does not exist and an attempt to make it failed. Please make the directory and make sure it\'s writeable by PHP.';
    }
    return 'The uploads directory is a go. You can change the location of it later.';
}
function configFileTest() {
    error_reporting(0);
    $config = EDUCASK_ROOT . '/includes/config.php';
    if(! is_file($config)) {
        $couldWrite = file_put_contents($config, 'Test Write');
        if(! $couldWrite) {
            return false;
        }
    }
    //@ToDo: Uncomment these lines.
    /*$couldWrite = file_put_contents($config, 'Test Write');
    if(! $couldWrite) {
        return false;
    }*/
    return true;
}
function configFileExp() {
    if(! configFileTest()) {
        return 'The configuration file, includes/config.php, does not exist. An attempt to make it and write to it failed. Please make the file and make it writeable by PHP.';
    }
    return 'The configuration file is ready for settings.';
}
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
    <link rel="icon" type="image/png" href="includes/images/favicon.png">
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

        a.button:link {
            padding: 10px;
            background: #2580a2;
            color: #ffffff;
            border: none;
            font-size: 18px;
            text-decoration: none;
            text-shadow: 0px -1px 0px rgba(0, 0, 0, .5);
            -moz-border-radius: 5px;
            border-radius: 5px;
            -o-border-radius: 5px;
        }

        a.button:hover, a.button:active {
            text-shadow: 0px -1px 0px rgba(0, 0, 0, .5);
            background: #166A8A;
            color: #FFFFFF;
            cursor: pointer;
        }

        .formtext, .formtextarea {
            width: 762px;
            padding: 10px;
            font-size: 18px;
            border: 1px gray solid;
            color: #333;
            font-family: 'Lucida Sans Unicode', 'Bitstream Vera Sans', 'Trebuchet Unicode MS', 'Lucida Grande', Verdana, Helvetica, sans-serif;
            outline: none;
        }

        .formtext:hover, .formtext:focus, .formtextarea:hover, .formtextarea:focus {
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
            text-shadow: 0px -1px 0px rgba(0, 0, 0, .5);
            -moz-border-radius: 5px;
            border-radius: 5px;
            -o-border-radius: 5px;
        }

        .formbutton:hover, .formbutton:focus {
            text-shadow: 0px -1px 0px rgba(0, 0, 0, .5);
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
    </style>
</head>
<body>
<div id="sidebar">
    <div id="titleBar">
        <img src="includes/images/educasklogo.png">

        <p>Educask 3.0 Alpha 9 - Developer Preview</p>
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
                        src="includes/images/educasklogo-e.png"/></a></li>
            <li><a href="https://www.facebook.com/Educask" target="_blank"><img
                        src="includes/images/facebook.png"/></a></li>
            <li><a href="https://github.com/educask" target="_blank"><img
                        src="includes/images/github.png"/></a></li>
            <li><a href="https://twitter.com/educask" target="_blank"><img
                        src="includes/images/twitter.png"/></a></li>
            <li><a href="https://plus.google.com/+Educask/posts" target="_blank"><img
                        src="includes/images/googleplus.png"/></a></li>
        </ul>
    </div>
</div>
<div id="content">
    <?php echo getContent(); ?>
</div>
</body>
</html>