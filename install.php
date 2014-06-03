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
    if($action == 'requirements') {
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
    if(is_file('includes/config.php')) {
        return '<p>Educask is already installed. If you wish to overwrite the install, please delete includes/config.php</p>';
    }
}
if(! isset($_GET['action'])) {
    header('Location: install.php?action=welcome');
}
if(! validateAction()) {
    header('Location: install.php?action=welcome');
}
?>
<html>
    <head>
        <title>Install | Educask Development Core</title>
        <style type="text/css">
            body{
                margin: 0;
                padding: 0;
                background-color: #166A8A;
            }
            #titleBar {
                background-color: #166A8A;
                width: 25%;
                margin-bottom: -16;
            }
            #main {
                position: fixed;
                height: 80%;
                width: 80%;
                top: 10%;
                left: 10%;
                background-color: white;
                padding: 0;
                margin: 0;
            }
            #sidebar {
                width: 25%;
                background-color: #C2C2C2;
                height: 100%;
                margin-top: 0;
                margin-left: 0;
            }
            #versionBar {
                padding: 5px;
                width: 74%;
                text-align: right;
                margin: 0;
                float: right;
            }
            #content {
                width: 74%;
                margin: 0;
                padding: 5px;
                float: right;
            }
            .current {
                font-weight: bold;
            }
            ul {
                margin: 0;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div id="titleBar">
            <img src="includes/images/educasklogo.png">
        </div>
        <div id="main">
            <div id="versionBar">
                <p>Educask 3.0 Developer Preview Alpha 9</p>
            </div>
            <div id="content">
                <?php echo getContent();?>
            </div>
            <div id="sidebar">
                <ul>
                    <li <?php echo getCurrentCss('welcome') ;?>>Welcome</li>
                    <li <?php echo getCurrentCss('requirements') ;?>>Verify Requirements</li>
                    <li <?php echo getCurrentCss('database') ;?>>Set up Database</li>
                    <li <?php echo getCurrentCss('configure') ;?>>Configure Site</li>
                    <li <?php echo getCurrentCss('install') ;?>>Install</li>
                    <li <?php echo getCurrentCss('finish') ;?>>Finish</li>
                </ul>
            </div>
        </div>
    </body>
</html>