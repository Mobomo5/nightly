<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 26/05/2015
 * Time: 8:36 PM
 */
class Session {
    private function __construct() {}
    public static function start() {
        $sessionProvider = Config::getInstance()->getSessionProvider();
        $sessionProvider = str_replace(".", "", $sessionProvider);
        $sessionProviderFile = EDUCASK_ROOT . '/core/providers/sessions/' . $sessionProvider . '.php';
        if(! is_readable($sessionProviderFile)) {
            return;
        }
        require_once($sessionProviderFile);
        if(! class_exists($sessionProvider)) {
            return;
        }
        $implementations = class_implements($sessionProvider);
        if(! in_array('ISession', $implementations)) {
            return;
        }
        if(! in_array('SessionHandlerInterface', $implementations)) {
            return;
        }
        session_set_save_handler(new $sessionProvider());
        session_name("educask");
        session_start();
        if(! isset($_SESSION['lastSessionGenerationTime'])) {
            $_SESSION['lastSessionGenerationTime'] = time();
            return;
        }
        $maxSessionAgeAgo = (int) Site::getInstance()->getMaxSessionIdAge()->getValue();
        $maxSessionIdAge = time() - $maxSessionAgeAgo;
        if($_SESSION['lastSessionGenerationTime'] > $maxSessionIdAge) {
            return;
        }
        $randomPerformSessionIdRegeneration = (rand(0, 1000) % 6 === 0);
        if(! $randomPerformSessionIdRegeneration) {
            return;
        }
        session_regenerate_id(true);
        $_SESSION['lastSessionGenerationTime'] = time();
    }
    public static function close() {
        session_write_close();
    }
}