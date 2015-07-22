<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/12/14
 * Time: 12:43 PM
 */

class failedLoginMonitorForLockout implements IPlugin {
    public static function init() {
        $hookEngine = HookEngine::getInstance();
        $hookEngine->addAction('userFailedToLogIn', new failedLoginMonitorForLockout());
    }
    public static function run($inContent = '') {
        $lockoutEngine = LockoutEngine::getInstance();
        $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
        if($lockout === false) {
            $attempts = $lockoutEngine->getNumberOfAttemptsBeforeLockout();
            $lockout = new Lockout($_SERVER['REMOTE_ADDR'], 1, new DateTime(), $attempts);
            $lockoutEngine->addLockout($lockout);
            $period = $lockoutEngine->getLockoutPeriod();
            $notice = new Notice('warning', "You have {$attempts} attempts left before you're locked out for {$period} minutes.");
            NoticeEngine::getInstance()->addNotice($notice);
            return;
        }
        $lockout->failedAttemptMade();
        $lockoutEngine->setLockout($lockout);
        $attempts = $lockout->getNumberOfAttemptsLeft();
        $period = $lockout->getNumberOfFailedAttempts() * $lockoutEngine->getLockoutPeriod();
        if($attempts === 1) {
            $notice = new Notice('warning', "You have {$attempts} attempt left before you're locked out for {$period} minutes.");
        } else {
            $notice = new Notice('warning', "You have {$attempts} attempts left before you're locked out for {$period} minutes.");
        }
        NoticeEngine::getInstance()->addNotice($notice);
    }
    public static function getPriority() {
        return 5;
    }
} 