<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/12/14
 * Time: 12:43 PM
 */
require_once(PLUGIN_INTERFACE_FILE);
require_once(LOCKOUT_ENGINE_OBJECT_FILE);
require_once(LOCKOUT_OBJECT_FILE);
require_once(HOOK_ENGINE_OBJECT_FILE);
require_once(NOTICE_ENGINE_OBJECT_FILE);
require_once(NOTICE_OBJECT_FILE);

class failedLoginMonitorForLockout implements plugin{
    public static function init() {
        $hookEngine = hookEngine::getInstance();
        $hookEngine->addAction('userFailedToLogIn', new failedLoginMonitorForLockout());
    }
    public static function run($inContent = '') {
        $lockoutEngine = lockoutEngine::getInstance();
        $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
        if($lockout == false) {
            $attempts = $lockoutEngine->getNumberOfAttemptsBeforeLockout();
            $lockout = new lockout($_SERVER['REMOTE_ADDR'], 1, new DateTime(), $attempts);
            $lockoutEngine->addLockout($lockout);
            $period = $lockoutEngine->getLockoutPeriod();
            $notice = new notice('warning', "You have {$attempts} attempts left before you're locked out for {$period} minutes.");
            noticeEngine::getInstance()->addNotice($notice);
            return;
        }
        $lockout->failedAttemptMade();
        $lockoutEngine->setLockout($lockout);
        $attempts = $lockout->getNumberOfAttemptsLeft();
        $period = $lockout->getNumberOfFailedAttempts() * $lockoutEngine->getLockoutPeriod();
        if($attempts == 1) {
            $notice = new notice('warning', "You have {$attempts} attempt left before you're locked out for {$period} minutes.");
        } else {
            $notice = new notice('warning', "You have {$attempts} attempts left before you're locked out for {$period} minutes.");
        }
        noticeEngine::getInstance()->addNotice($notice);
    }
    public static function getPriority() {
        return 5;
    }
} 