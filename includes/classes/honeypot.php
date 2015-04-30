<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 29/04/2015
 * Time: 7:03 PM
 */

class honeypot {
    private $pickedField;
    public function __construct() {
        $fields = array(
            new honeyPotField("my_comment", "Comment"),
            new honeyPotField("my_telephone", "Telephone Number"),
            new honeyPotField("likeEducask", "Do you like Educask?"),
            new honeyPotField("loveCatsAndKitties", "Do you like cats and kitties?"),
            new honeyPotField("MyAddress", "Mailing Address:"),
            new honeyPotField("YourEmail", "email address"),
            new honeyPotField("visacard", "Visa number"),
            new honeyPotField("Favouritegame", "favourite Game?"),
            new honeyPotField("Good-At-Math", "Are You Good At Math?"),
        );
        $timesToShuffle = 7;
        for($i = 0; $i < $timesToShuffle; $i++) {
            shuffle($fields);
        }
        $this->pickedField = $fields[0];
        $_SESSION['educaskHoneypot'] = null;
    }
    public function getHtmlElement() {
        $_SESSION['educaskHoneypot'] = $this->pickedField->getPostName();
        $subElement = $this->pickedField->getHtmlElement();
        return "<div class='hide'><p>If you see this, leave the field for the following question empty.</p>{$subElement}</div>";
    }
    public static function validate() {
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            return false;
        }
        if(! isset($_SESSION['educaskHoneypot'])) {
            return false;
        }
        $honeypotField = $_SESSION['educaskHoneypot'];
        if($honeypotField === null) {
            return false;
        }
        if(! is_string($honeypotField)) {
            return false;
        }
        $_SESSION['educaskHoneypot'] = null;
        if(! isset($_POST[$honeypotField])) {
            return false;
        }
        $answer = $_POST[$honeypotField];
        if($answer !== "") {
            return false;
        }
        return true;
    }
    public function __toString() {
        return '' . $this->getHtmlElement();
    }
}
class honeyPotField {
    private $postName;
    private $labelText;
    public function __construct($inPostName, $inLabelText) {
        $this->postName = strip_tags($inPostName);
        $this->labelText = strip_tags($inLabelText);
    }
    public function getPostName() {
        return $this->postName;
    }
    public function getLabelText() {
        return $this->labelText;
    }
    public function getHtmlElement() {
        return "<label for='{$this->postName}'>{$this->labelText}</label><input type='text' name='{$this->postName}' id='{$this->postName}' />";
    }
    public function __toString() {
        return '' . $this->getHtmlElement();
    }
}