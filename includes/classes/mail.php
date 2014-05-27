<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/15/14
 * Time: 2:19 PM
 */

//@ToDo: Change this to use a php mailer class, not the mail function.
class mail {
    private $senderEmail;
    private $senderName;
    private $recipients;
    private $subject;
    private $body;
    private $isBulkMail;
    private $allowedTags = "<p><a><img><ul><li>";
    private $replacements;

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
        $headers = "From: {$this->senderName}<{$this->senderEmail}>\r\n" .
            "Reply-To: {$this->senderName}<{$this->senderEmail}>\r\n" .
            'Content-Type: text/html; charset=ISO-8859-1\r\n' .
            'X-Mailer: PHP/' . phpversion();

        if ($this->isBulkMail) {
            $recipients = '';
            foreach ($this->recipients as $recipient) {
                $recipients .= $recipient . ', ';
            }
            $recipients = substr($recipients, 0, -2);

            if (!mail($recipients, $this->subject, $this->body, $headers)) {
                return false;
            }

            return true;
        }

        $sent = true;

        foreach ($this->recipients as $recipient) {
            $body = $this->doReplacement($recipient);
            if (!mail($recipient, $this->subject, $body, $headers)) {
                $sent = false;
            }
        }

        return $sent;
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