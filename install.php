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
<!DOCTYPE html>
<html>
    <head>
        <title>Install | Educask Development Core</title>
        <link rel="icon" type="image/png" href="includes/images/favicon.png">
        <style type="text/css">
            body{
                margin: 0;
                padding: 0;
            }
            #sidebar {
                position: absolute;
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