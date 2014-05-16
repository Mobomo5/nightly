<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/15/14
 * Time: 9:36 PM
 */
require_once(MAIL_OBJECT_FILE);
require_once(DATABASE_OBJECT_FILE);
class mailTemplateEngine {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new mailTemplateEngine();
        }

        return self::$instance;
    }
    private function __construct() {
        //Do nothing.
    }
    public function getTemplate($inTemplateName) {

    }
    public function loadTemplate($inTemplateName) {

    }
    public function saveTemplate(mailTemplate $templateToSave) {

    }
    public function addTemplate(mailTemplate $templateToAdd) {

    }
    public function deleteTemplate(mailTemplate $templateToDelete) {

    }
}