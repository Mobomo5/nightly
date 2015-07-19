<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/15/14
 * Time: 9:36 PM
 */
class MailTemplateEngine {
    private static $instance;
    private $retrievedTemplates;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new MailTemplateEngine();
        }
        return self::$instance;
    }
    private function __construct() {
        $this->retrievedTemplates = array();
    }
    public function getTemplate($inTemplateName) {
        if (isset($this->retrievedTemplates[$inTemplateName])) {
            return $this->retrievedTemplates[$inTemplateName];
        }
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $inTemplateName = $database->escapeString(htmlspecialchars($inTemplateName));
        $results = $database->getData('*', 'mailTemplate', "name='{$inTemplateName}'");
        if ($results === false) {
            return false;
        }
        if ($results === null) {
            return false;
        }
        if (count($results) > 1) {
            return false;
        }
        $template = new MailTemplate($results[0]['id'], $results[0]['name'], $results[0]['subject'], $results[0]['body'], $results[0]['senderEmail'], $results[0]['senderName'], $results[0]['modifier']);
        $this->retrievedTemplates = $template;
        return $template;
    }
    public function loadTemplate($inTemplateName) {
        $template = $this->getTemplate($inTemplateName);
        if ($template === null) {
            return false;
        }
        if ($template === false) {
            return false;
        }
        return new Mail($template->getSenderEmail(), $template->getSenderName(), array(), $template->getSubject(), $template->getBody(), false);
    }
    public function saveTemplate(MailTemplate $templateToSave) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $id = $templateToSave->getID();
        $name = $database->escapeString($templateToSave->getName());
        $subject = $database->escapeString($templateToSave->getSubject());
        $body = $database->escapeString($templateToSave->getBody());
        $senderName = $database->escapeString($templateToSave->getSenderName());
        $senderEmail = $database->escapeString($templateToSave->getSenderEmail());
        $modifier = $database->escapeString($templateToSave->getModifier());
        if (!$database->updateTable('mailTemplate', "name='{$name}', subject='{$subject}', body='{$body}, senderName='{$senderName}', senderEmail='{$senderEmail}', modifier={$modifier}", "id={$id}")) {
            return false;
        }
        return true;
    }
    public function addTemplate(MailTemplate $templateToAdd) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $name = $database->escapeString($templateToAdd->getName());
        $subject = $database->escapeString($templateToAdd->getSubject());
        $body = $database->escapeString($templateToAdd->getBody());
        $senderName = $database->escapeString($templateToAdd->getSenderName());
        $senderEmail = $database->escapeString($templateToAdd->getSenderEmail());
        $modifier = $database->escapeString($templateToAdd->getModifier());
        if (!$database->insertData('mailTemplate', 'name, subject, body, senderName, senderEmail, modifier', "'{$name}', '{$subject}', '{$body}', '{$senderName}', '{$senderEmail}', {$modifier}")) {
            return false;
        }
        return true;
    }
    public function deleteTemplate(MailTemplate $templateToDelete) {
        $database = Database::getInstance();
        if (!$database->isConnected()) {
            return false;
        }
        $id = $database->escapeString($templateToDelete->getID());
        if (!$database->removeData('mailTemplate', "id={$id}")) {
            return false;
        }
        return true;
    }
}