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
if(! isset($_GET['action'])) {
    header('Location: install.php?action=welcome');
}
if(! validateAction()) {
    header('Location: install.php?action=welcome');
}
//Comment the following three lines if you wish to overwrite an Educask install. You can also delete config.php.
//if(is_file('includes/config.php')) {
//    die('Educask is already installed. If you wish to overwrite, please delete the config.php file or modify the install.php file.');
//}
?>
<html>
    <head>
        <title>Install | Educask Development Core</title>
        <script type="text/css">
            .current {
                font-weight: bold;
            }
        </script>
    </head>
    <body>
        <div class="titleBar">
            <img src="includes/images/educasklogo.png">
        </div>
        <div class="sidebar">
            <ul>
                <li <?php echo getCurrentCss('welcome') ;?>>Welcome</li>
                <li <?php echo getCurrentCss('requirements') ;?>>Verify Requirements</li>
                <li <?php echo getCurrentCss('database') ;?>>Set up Database</li>
                <li <?php echo getCurrentCss('configure') ;?>>Configure Site</li>
                <li <?php echo getCurrentCss('install') ;?>>Install</li>
                <li <?php echo getCurrentCss('finish') ;?>>Finish</li>
            </ul>
        </div>
        <div class="main">

        </div>
    </body>
</html>