<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 12:52 PM
 */

session_start();
header('X-Powered-By: Educask 3.0');
header('X-Frame-Options: DENY');
define('EDUCASK_ROOT', getcwd());
define('EDUCASK_WEB_ROOT', dirname($_SERVER['SCRIPT_NAME']) . '/');

//Start EHQ Simple CMS
echo "before the require.\n";
require_once(EDUCASK_ROOT . '\includes\classes\database.php');
echo "after the require";
$bootstrap = bootstrap::getInstance();
$bootstrap->init();