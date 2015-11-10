<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 29/04/2015
 * Time: 7:03 PM
 */
class Honeypot implements Serializable {
    private static $firstRequest = true;
    private $fields;
    private $pickedField;
    public static function getInstance() {
        if(! isset($_SESSION['educaskHP'])) {
            $_SESSION['educaskHP'] = new Honeypot();
            return $_SESSION['educaskHP'];
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $_SESSION['educaskHP'];
        }
        if(! self::$firstRequest) {
            return $_SESSION['educaskHP'];
        }
        $_SESSION['educaskHP']->regenerate();
        self::$firstRequest = false;
        return $_SESSION['educaskHP'];
    }
    private function __construct() {
        $this->initHoneypotFieldArray();
        $this->regenerate();
    }
    public function getHtmlElement() {
        if(! isset($this->pickedField)) {
            return "";
        }
        $subElement = $this->pickedField->getHtmlElement();
        return "<div class='educaskHP'><p>If you see this, leave the field for the following question empty.</p>{$subElement}</div>";
    }
    public function validate() {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        if(! isset($this->pickedField)) {
            return false;
        }
        $honeypotField = $this->pickedField->getPostName();
        if(! isset($_POST[$honeypotField])) {
            return false;
        }
        $answer = $_POST[$honeypotField];
        if($answer !== "") {
            return false;
        }
        return true;
    }
    private function regenerate() {
        for($i=0;$i<7;$i++) {
            shuffle($this->fields);
        }
        $randomPosition = rand(0, (count($this->fields) -1));
        $this->pickedField = $this->fields[$randomPosition];
    }
    private function initHoneypotFieldArray() {
        $this->fields = array(
            new honeyPotField("my_comment", "Comment"),
            new honeyPotField("my_telephone", "Telephone Number"),
            new honeyPotField("likeEducask", "Do you like Educask?"),
            new honeyPotField("loveCatsAndKitties", "Do you like cats and kitties?"),
            new honeyPotField("MyAddress", "Mailing Address:"),
            new honeyPotField("YourEmail", "email address"),
            new honeyPotField("visacard", "Visa number"),
            new honeyPotField("Favouritegame", "favourite Game?"),
            new honeyPotField("Good-At-Math", "Are You Good At Math?"),
            new honeyPotField("Favourite_Movie", "Favourite Movie:"),
            new honeyPotField("bestMovie", "Best Movie:"),
        );
    }
    public function __toString() {
        return '' . $this->getHtmlElement();
    }
    public function serialize() {
        return serialize($this->pickedField);
    }
    public function unserialize($data) {
        $this->initHoneypotFieldArray();
        $this->pickedField = unserialize($data);
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
        return "<label for='{$this->postName}'>{$this->labelText}</label><input type='text' name='{$this->postName}' id='{$this->postName}' autocomplete='off'/>";
    }
    public function __toString() {
        return '' . $this->getHtmlElement();
    }
}