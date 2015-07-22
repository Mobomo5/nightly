<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/13/14
 * Time: 12:44 PM
 */

class clearOldForgotPasswords implements IPlugin {
    public static function init() {
        $hookEngine = HookEngine::getInstance();
        $hookEngine->addAction('cronRun', new clearOldForgotPasswords());
    }
    public static function run($inContent = '') {
        $forgotPasswordEngine = ForgotPasswordEngine::getInstance();
        $forgotPasswordEngine->removeExpiredTokens();
    }
    public static function getPriority() {
        return 5;
    }
}