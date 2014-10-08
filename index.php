<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 12:52 PM
 */
error_reporting(E_ALL);

header('X-Powered-By: Educask 3.0');
header('X-Generator: Educask 3.0 (http://www.educask.com)');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

ini_set('session.use_only_cookies', true);
ini_set('session.cookie_httponly', true);

define('EDUCASK_ROOT', getcwd());
define('EDUCASK_WEB_ROOT', dirname($_SERVER['REQUEST_URI']));

//Start Educask
require_once(EDUCASK_ROOT . '/includes/classes/bootstrap.php');
$bootstrap = bootstrap::getInstance();
$bootstrap->init();