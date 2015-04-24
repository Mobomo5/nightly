<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 6/13/14
 * Time: 12:44 PM
 */
require_once(PLUGIN_INTERFACE_FILE);
require_once(HOOK_ENGINE_OBJECT_FILE);
require_once(VARIABLE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
require_once(DATABASE_OBJECT_FILE);
require_once(FORGOT_PASSWORD_ENGINE_OBJECT_FILE);

class clearOldForgotPasswords implements plugin{
    public static function init() {
        $hookEngine = hookEngine::getInstance();
        $hookEngine->addAction('cronRun', new clearOldForgotPasswords());
    }
    public static function run($inContent = '') {
        $forgotPasswordEngine = forgotPasswordEngine::getInstance();
        $forgotPasswordEngine->removeExpiredTokens();
    }
    public static function getPriority() {
        return 5;
    }
}