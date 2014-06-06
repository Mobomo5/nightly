<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 02/06/14
 * Time: 9:37 PM
 */
function getAction() {
    $action = preg_replace('/[^A-Za-z0-9\-\&\/\.]/', '', htmlspecialchars($_GET['action']));
    $action = preg_replace('/\s/', '', strip_tags($action));
    return $action;
}
function validateAction() {
    $action = getAction();
    if($action == 'welcome') {
        return true;
    }
    if($action == 'requirement') {
        return true;
    }
    if($action == 'database') {
        return true;
    }
    if($action == 'configure') {
        return true;
    }
    if($action == 'install') {
        return true;
    }
    if($action == 'finish') {
        return true;
    }
    return false;
}
function getCurrentCss($step) {
    $step = preg_replace('/\s/', '', strip_tags($step));
    $action = getAction();
    if($action == $step) {
        return 'class="current"';
    }
    return '';
}
function getContent() {
    //Comment the following three lines if you wish to overwrite an Educask install. You can also delete config.php.
    //if(is_file('includes/config.php')) {
    //    return '<p>Educask is already installed. If you wish to overwrite the install, please delete includes/config.php</p>';
    //}
    if (! phpTest()) {
        return '<h1>Please Update PHP</h1><p>Sorry, I can\'t make Educask work on your version of PHP. Please consider updating to the latest version of PHP.</p>';
    }
    $action = getAction();
    $function = $action . 'Content';
    if(function_exists($function)) {
        return $function();
    }
    return '<h1>Ooops</h1><p>Sorry, I didn\'t understand which page you wanted to see.</p>';
}
function welcomeContent() {
    $toReturn = '<h1>Welcome</h1>';
    $toReturn .= '<p>Thank you for choosing Educask. This wizard will help you get Educask ready.</p>';
    $toReturn .= '<p>Please agree to the following license agreement before you continue. Once Educask is installed, it is assumed that you agreed to the license.</p>';
    $license = file_get_contents('LICENSE');
    if($license == false) {
        $license = '<p>Please see <a href="https://github.com/educask/nightly/blob/master/LICENSE">this page</a> to read the license.</p>';
    }
    $toReturn .= "<div id=\"license\"><pre>{$license}</pre></div>";
    $toReturn .= '<p><a class="button" href="install.php?action=requirement">I Agree - Continue</a></p>';
    return $toReturn;
}
function requirementContent() {
    $requirements = array(
        array('name' => 'PHP', 'testFunction' => 'phpTest', 'explanationFunction' => 'phpTestExp'),
        array('name' => 'register_globals', 'testFunction' => 'registerGlobalsTest', 'explanationFunction' => 'registerGlobalsExp')
    );
    $anyFailed = false;
    $toReturn = '<h1>Requirements</h1>';
    $toReturn .= '<table>';
    $toReturn .= '<tr><td>Requirement:</td><td>Explanation:</td></tr>';
    foreach ($requirements as $requirement) {
        if(! function_exists($requirement['testFunction'])) {
            continue;
        }
        if(! function_exists($requirement['explanationFunction'])) {
            continue;
        }
        $success = $requirement['testFunction']();
        $explanation = $requirement['explanationFunction']();
        $name = $requirement['name'];
        if($success == false) {
            $anyFailed = true;
            $toReturn .= "<tr class=\"failed\"><td>{$name}</td><td>{$explanation}</td></tr>";
            continue;
        }
        $toReturn .= "<tr class=\"success\"><td>{$name}</td><td>{$explanation}</td></tr>";
    }
    $toReturn .= '</table>';
    if($anyFailed == true) {
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
    if(version_compare(PHP_VERSION, '5.2.4') < 0) {
        return false;
    }
    return true;
}
function phpTestExp() {
    if(! phpTest()) {
        return 'Your PHP version is '. PHP_VERSION . ' and it\'s too old. Please upgrade to the latest PHP. Educask needs PHP newer than 5.2.4.';
    }
    return 'Your PHP version is ' . PHP_VERSION;
}
function registerGlobalsTest() {
    if(ini_get('register_globals') == 1) {
        return false;
    }
    return true;
}
function registerGlobalsExp() {
    if(! registerGlobalsTest()) {
        return 'Please turn register_globals off in the php.ini file or in a .htaccess file. Please see <a href="http://ca1.php.net/manual/en/security.registerglobals.php">this page</a> for more information.';
    }
    return 'register_globals is turned off.';
}
if(! isset($_GET['action'])) {
    header('Location: install.php?action=welcome');
}
if(! validateAction()) {
    header('Location: install.php?action=welcome');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Install | Educask Development Core</title>
        <link rel="icon" type="image/png" href="includes/images/favicon.png">
        <style type="text/css">
            body{
                margin: 0;
                padding: 0;
                color: #333333;
                font-family: 'Lucida Sans Unicode','Bitstream Vera Sans','Trebuchet Unicode MS','Lucida Grande',Verdana,Helvetica,sans-serif;
            }
            #sidebar {
                position: fixed;
                float:left;
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
                text-shadow: 0px -1px 0px rgba(0,0,0,.5);
                -moz-border-radius: 5px;
                border-radius: 5px;
                -o-border-radius: 5px;
            }
            a.button:hover, a.button:active {
                text-shadow: 0px -1px 0px rgba(0,0,0,.5);
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
                text-shadow: 0px -1px 0px rgba(0,0,0,.5);
                -moz-border-radius: 5px;
                border-radius: 5px;
                -o-border-radius: 5px;
            }
            .formbutton:hover, .formbutton:focus {
                text-shadow: 0px -1px 0px rgba(0,0,0,.5);
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
                <li <?php echo getCurrentCss('welcome') ;?>>Welcome</li>
                <li <?php echo getCurrentCss('requirements') ;?>>Verify Requirements</li>
                <li <?php echo getCurrentCss('database') ;?>>Set up Database</li>
                <li <?php echo getCurrentCss('configure') ;?>>Configure Site</li>
                <li <?php echo getCurrentCss('install') ;?>>Install</li>
                <li <?php echo getCurrentCss('finish') ;?>>Finish</li>
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
            <?php echo getContent();?>
        </div>
    </body>
</html>