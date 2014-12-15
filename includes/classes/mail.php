<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/15/14
 * Time: 2:19 PM
 */
require_once(EDUCASK_ROOT . 'thirdPartyLibraries/PHPMailer/PHPMailerAutoload.php');
require_once(VARIABLE_ENGINE_OBJECT_FILE);
class mail {
    private $senderEmail;
    private $senderName;
    private $recipients;
    private $subject;
    private $body;
    private $isBulkMail;
    private $allowedTags = "<p><a><img><ul><li>";
    private $replacements;
    private $errors;
    public function __construct($inSenderEmail = SITE_EMAIL, $inSenderName = SITE_TITLE, array $inRecipients = array(), $inSubject = 'Email', $inBody = '<p>This is an email.</p>', $isBulkMail = false) {
        if (!is_bool($isBulkMail)) {
            return;
        }
        if (!filter_var($inSenderEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        foreach ($inRecipients as $recipient) {
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                return;
            }
            $recipient = str_replace("\r\n", '', $recipient);
            $recipient = str_replace('\r\n', '', $recipient);
        }
        $inSenderEmail = str_replace("\r\n", '', $inSenderEmail);
        $inSenderEmail = str_replace('\r\n', '', $inSenderEmail);
        $inSenderName = str_replace("\r\n", '', $inSenderName);
        $inSenderName = str_replace('\r\n', '', $inSenderName);
        $inSubject = str_replace("\r\n", '', $inSubject);
        $inSubject = str_replace('\r\n', '', $inSubject);
        $this->senderEmail = $inSenderEmail;
        $this->senderName = trim(htmlspecialchars($inSenderName));
        $this->recipients = $inRecipients;
        $this->subject = trim(htmlspecialchars($inSubject));
        $this->body = strip_tags(trim($inBody), $this->allowedTags);
        $this->isBulkMail = $isBulkMail;
        $this->replacements = array();
        $this->errors = array();
    }
    public function addRecipient($inRecipient) {
        if (!filter_var($inRecipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $this->recipients[] = $inRecipient;
    }
    public function removeRecipient($inRecipient) {
        if (!filter_var($inRecipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $toRemove = array_search($inRecipient, $this->recipients);
        unset($this->recipients[$toRemove]);
    }
    public function getSenderEmail() {
        return $this->senderEmail;
    }
    public function getSenderName() {
        return $this->senderName;
    }
    public function changeSender($inSenderEmail = SITE_EMAIL, $inSenderName = SITE_TITLE) {
        if (!filter_var($inSenderEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $inSenderName = str_replace("\r\n", '', $inSenderName);
        $inSenderName = str_replace('\r\n', '', $inSenderName);
        $inSenderEmail = str_replace("\r\n", '', $inSenderEmail);
        $inSenderEmail = str_replace('\r\n', '', $inSenderEmail);
        $this->senderEmail = $inSenderEmail;
        $this->senderName = trim(htmlspecialchars($inSenderName));
    }
    public function isBulkMail() {
        return $this->isBulkMail;
    }
    public function setBulkMail($isBulkMail = false) {
        if (!is_bool($isBulkMail)) {
            return;
        }
        $this->isBulkMail = $isBulkMail;
    }
    public function getSubject() {
        return $this->subject;
    }
    public function setSubject($inSubject) {
        $inSubject = str_replace("\r\n", '', $inSubject);
        $inSubject = str_replace('\r\n', '', $inSubject);
        $this->subject = trim(htmlspecialchars($inSubject));
    }
    public function getBody() {
        return $this->body;
    }
    public function setBody($inBody) {
        $this->body = strip_tags(trim($inBody), $this->allowedTags);
    }
    public function sendMail() {
        $siteEmail = SITE_EMAIl;
        $variableEngine = variableEngine::getInstance();
        $smtpServer = $variableEngine->getVariable('smtpServer');
        if($smtpServer == false) {
            return false;
        }
        $smtpPort = $variableEngine->getVariable('smtpPort');
        if($smtpPort == false) {
            return false;
        }
        $smtpUserName = $variableEngine->getVariable('smtpUserName');
        if($smtpUserName == false) {
            return false;
        }
        $smtpPassword = $variableEngine->getVariable('smtpPassword');
        if($smtpPassword == false) {
            return false;
        }
        $smtpUseEncryption = $variableEngine->getVariable('smtpUseEncryption');
        if($smtpUseEncryption == false) {
            return false;
        }
        $smtpUseEncryption = $smtpUseEncryption->getValue();
        if($smtpUseEncryption == 'false') {
            $encryption = "";
        } else {
            $encryption = "tls";
        }
        $toSend = new PHPMailer();
        $toSend->isSMTP();
        $toSend->Host = $smtpServer->getValue();
        $toSend->SMTPAuth = true;
        $toSend->Username = $smtpUserName->getValue();
        $toSend->Password = $smtpPassword->getValue();
        $toSend->SMTPSecure = $encryption;
        $toSend->Port = intval($smtpPort->getValue());
        $toSend->From = $siteEmail;
        $toSend->FromName = $this->senderName;
        $toSend->addReplyTo($this->senderEmail, $this->senderName);
        $toSend->isHTML(true);
        $toSend->Subject = $this->subject;
        if ($this->isBulkMail) {
            foreach ($this->recipients as $recipient) {
                $toSend->addBCC($recipient);
            }
            $toSend->Body = $this->body;
            $toSend->AltBody = strip_tags($this->body);
            if (! $toSend->send()) {
                $this->errors[] = $toSend->ErrorInfo;
                return false;
            }
            return true;
        }
        $sent = true;
        foreach ($this->recipients as $recipient) {
            $body = $this->doReplacement($recipient);
            $altBody = strip_tags($body);
            $toSend->clearAddresses();
            $toSend->addAddress($recipient);
            $toSend->Body = $body;
            $toSend->AltBody = $altBody;
            if (! $toSend->send()) {
                $this->errors = $toSend->ErrorInfo;
                $sent = false;
            }
        }
        return $sent;
    }
    public function clearErrors() {
        $this->errors = array();
    }
    public function getErrors() {
        return $this->errors;
    }
    public function doReplacement($emailForReplacement) {
        if ($this->replacements == null) {
            return $this->body;
        }
        if (!filter_var($emailForReplacement, FILTER_VALIDATE_EMAIL)) {
            return $this->body;
        }
        $body = $this->body;
        foreach ($this->replacements as $pattern => $email) {
            $body = str_replace($pattern, $email[$emailForReplacement], $body);
        }
        return $body;
    }
    public function addReplacementValue($replacementPattern, $email, $replacement) {
        $firstChars = substr($replacementPattern, 0, 1);
        if ($firstChars != '[[') {
            return;
        }
        $lastChars = substr($replacementPattern, -2);
        if ($lastChars != ']]') {
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $replacement = strip_tags(trim($replacement), $this->allowedTags);
        $this->replacements[$replacementPattern][$email] = $replacement;
    }
    public function removeReplacementValue($replacementPattern, $email) {
        $firstChars = substr($replacementPattern, 0, 1);
        if ($firstChars != '[[') {
            return;
        }
        $lastChars = substr($replacementPattern, -2);
        if ($lastChars != ']]') {
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        unset($this->replacements[$replacementPattern][$email]);
    }
}