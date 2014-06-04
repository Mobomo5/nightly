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
            }
            #sidebar {
                position: absolute;
                width: 20%;
                height: 100%;
                background-color: #166A8A;
                margin: 0;
                box-shadow: 2px 0px 2px #777777;
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
                width: 79%;
                margin: 0;
                padding: 5px;
                float: right;
            }
            .current {
                font-weight: bold;
                background-color: #106383;
                box-shadow: 1px 1px 1px #333 inset;
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
        </div>
        <div id="content">
            <?php echo getContent();?>
        </div>
    </body>
</html>