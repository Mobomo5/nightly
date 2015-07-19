    <?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 05/01/14
 * Time: 9:44 PM
 */
class generateRandomString implements IGeneralFunction {
    private $length;
    public function __construct($length = 5, $randomLength = false, $minLength = 256, $maxLength = 1024) {
        if(! is_numeric($length)) {
            $this->length = 5;
            return;
        }
        $this->length = $length;
        if(! is_bool($randomLength)) {
            return;
        }
        if(! $randomLength) {
            return;
        }
        if(! is_numeric($minLength)) {
            return;
        }
        if(! is_numeric($maxLength)) {
            return;
        }
        if($maxLength < $minLength) {
            return;
        }
        if($minLength < 1) {
            return;
        }
        $this->length = mt_rand($minLength, $maxLength);
    }
    public function run() {
        $availableCharacters = "qwertyuioplkjhgfdsazxcvbnm1234567890POIUYTREWQASDFGHJKLMNBVCXZ-_";
        for($i=0;$i<7;$i++) {
            $availableCharacters = str_shuffle($availableCharacters);
        }
        $numberOfCharacters = strlen($availableCharacters) - 1;
        $randomString = "";
        for($x=0;$x<$this->length;$x++) {
            $randomString .= $availableCharacters[mt_rand(0, $numberOfCharacters)];
        }
        return $randomString;
    }
}