<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/12/14
 * Time: 2:45 PM
 */
class failedLoginRemovalOfLockout implements plugin{
    public static function init() {
        $hookEngine = hookEngine::getInstance();
        $hookEngine->addAction('userIsLoggingOut', new failedLoginRemovalOfLockout());
    }
    public static function run($inContent = '') {
        $lockoutEngine = lockoutEngine::getInstance();
        $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
        if($lockout === false) {
            return;
        }
        $lockoutEngine->removeLockout($lockout);
    }
    public static function getPriority() {
        return 5;
    }
}