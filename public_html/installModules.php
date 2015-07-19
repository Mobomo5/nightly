<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 09/06/14
 * Time: 2:24 PM
 */
session_start();
$knownToken = strip_tags($_SESSION['token']);
$givenToken = strip_tags($_GET['token']);
if($knownToken != $givenToken) {
    unset($_SESSION['token']);
    die();
}
if(! isset($_GET['action'])) {
    echo $_SESSION['moduleProgress'];
    die();
}
if($_GET['action'] == 'status') {
    echo $_SESSION['moduleStatus'];
    die();
}
echo $_SESSION['moduleProgress'];