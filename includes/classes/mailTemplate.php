<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 5/15/14
 * Time: 9:38 PM
 */
class mailTemplate {
    private $id;
    private $name;
    private $subject;
    private $body;
    private $senderEmail;
    private $senderName;
    private $modifier;

    public function __construct($inID, $inName, $inSubject, $inBody, $inSenderEmail, $inSenderName, $inModifier) {
        if (!is_numeric($inID)) {
            return;
        }
        if ($inID < 1) {
            return;
        }
        if (preg_match('/\s/', $inName)) {
            return;
        }
        $inSubject = str_replace("\r\n", '', trim($inSubject));
        $inSubject = str_replace('\r\n', '', trim($inSubject));
        if (!filter_var($inSenderEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $inSenderName = str_replace("\r\n", '', trim($inSenderName));
        $inSenderName = str_replace('\r\n', '', trim($inSenderName));
        if (!is_numeric($inModifier)) {
            return;
        }
        if ($inModifier < 1) {
            return;
        }
        $this->id = $inID;
        $this->name = $inName;
        $this->subject = $inSubject;
        $this->body = $inBody;
        $this->senderEmail = $inSenderEmail;
        $this->senderName = $inSenderName;
        $this->modifier = $inModifier;
    }

    public function getID() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($inName) {
        if (preg_match('/\s/', $inName)) {
            return;
        }
        $this->name = $inName;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($inSubject) {
        $inSubject = str_replace("\r\n", '', trim($inSubject));
        $inSubject = str_replace('\r\n', '', trim($inSubject));
        $this->subject = $inSubject;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($inBody) {
        $this->body = $inBody;
    }

    public function getSenderEmail() {
        return $this->senderEmail;
    }

    public function setSenderEmail($inSenderEmail) {
        if (!filter_var($inSenderEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $this->senderEmail = $inSenderEmail;
    }

    public function getSenderName() {
        return $this->senderName;
    }

    public function setSenderName($inSenderName) {
        $inSenderName = str_replace("\r\n", '', $inSenderName);
        $inSenderName = str_replace('\r\n', '', $inSenderName);
        $this->senderName = $inSenderName;
    }

    public function getModifier() {
        return $this->modifier;
    }

    public function setModifier($inModifier) {
        if (!is_numeric($inModifier)) {
            return;
        }
        if ($inModifier < 1) {
            return;
        }
        $this->modifier = $inModifier;
    }

    public function __toString() {
        return $this->body;
    }
} 