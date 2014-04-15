<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/12/13
 * Time: 8:01 PM
 */
require_once(VARIABLE_OBJECT_FILE);
require_once(VARIABLE_ENGINE_OBJECT_FILE);
require_once(VALIDATOR_OBJECT_FILE);
class site {
    private $title;
    private $email;
    private $url;
    private $urlSecure;
    private $baseDirectory;
    private $theme;
    private $educaskVersion;
    private $currentPage;
    private $guestRoleID;
    private $cleanURLs;
    private $timeZone;
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new site();
        }

        return self::$instance;
    }
    private function __construct() {
        $variableEngine = variableEngine::getInstance();
        $variablesWanted[] = 'siteTitle';
        $variablesWanted[] = 'siteEmail';
        $variablesWanted[] = 'siteTheme';
        $variablesWanted[] = 'siteWebAddress';
        $variablesWanted[] = 'siteWebAddressSecure';
        $variablesWanted[] = 'siteWebDirectory';
        $variablesWanted[] = 'cmsVersion';
        $variablesWanted[] = 'guestRoleID';
        $variablesWanted[] = 'cleanURLsEnabled';
        $variablesWanted[] = 'siteTimeZone';
        $variables = $variableEngine->getVariables($variablesWanted);
        $this->title = $variables['siteTitle'];
        $this->email = $variables['siteEmail'];
        $this->url = $variables['siteWebAddress'];
        $this->urlSecure = $variables['siteWebAddressSecure'];
        $this->baseDirectory = $variables['siteWebDirectory'];
        $this->theme = $variables['siteTheme'];
        $this->cmsVersion = $variables['cmsVersion'];
        $this->guestRoleID = $variables['guestRoleID'];
        $this->cleanURLs = $variables['cleanURLsEnabled'];
        $this->timeZone = $variables['siteTimeZone'];
        if(! empty($_GET['p'])) {
            $this->currentPage = $_GET['p'];
            return;
        }
        $this->currentPage = 'home';
    }
    public function getTitle() {
        return $this->title;
    }
    public function getEmail() {
        return $this->email;
    }
    public function setTitle($inTitle) {
        if(! $this->title->setValue($inTitle)) {
            return false;
        }
    }
    public function setEmail($inEmail) {
        $validator = new validator('email');
        if(! $validator->validatorExists()) {
            return false;
        }
        if(! $validator->validate($inEmail)) {
            return false;
        }
        if(! $this->email->setValue($inEmail)) {
            return false;
        }
    }
    public function getTheme() {
        return $this->theme;
    }
    public function setTheme($inTheme) {
        $validator = new validator('dir');
        if(! $validator->validatorExists()) {
            return false;
        }
        if(! $validator->validate($inTheme)) {
            return false;
        }
        if(! $this->theme->setValue($inTheme)) {
            return false;
        }
    }
    public function getWebAddress($secure = false, $withBaseDirectory = false) {
        if ($secure == true) {
            if($withBaseDirectory == true) {
                return $this->urlSecure . $this->baseDirectory;
            }
            return $this->urlSecure;
        }
        if($withBaseDirectory == true) {
            return $this->url . $this->baseDirectory;
        }
        return $this->url;
    }
    public function setWebAddress($inUrl) {
        $validator = new validator('url');
        if(! $validator->validatorExists()) {
            return false;
        }
        if(! $validator->validate($inUrl, array('mightBeIP', 'noDirectories', 'httpOnly'))) {
            return false;
        }
        if(! $this->url->setValue($inUrl)) {
            return false;
        }
    }
    public function setSecureWebAddress($inUrl) {
        $validator = new validator('url');
        if(! $validator->validatorExists()) {
            return false;
        }
        if(! $validator->validate($inUrl, array('mightBeIP', 'noDirectories', 'httpsOnly'))) {
            return false;
        }
        if(! $this->urlSecure->setValue($inUrl)) {
            return false;
        }
    }
    public function getBaseDirectory() {
        return $this->baseDirectory;
    }
    public function setBaseDirectory($inDirectory) {
        if(! $this->baseDirectory->setValue($inDirectory)) {
            return false;
        }
    }
    public function getEducaskVersion() {
        return $this->educaskVersion;
    }
    public function getCurrentPage($asArray = false) {
        if($asArray == true) {
            return explode('/', $this->currentPage);
        }
        return $this->currentPage;
    }
    public function getGuestRoleID() {
        return $this->guestRoleID;
    }
    public function setGuestRoleID($inID) {
        if(! is_int($inID)) {
            return false;
        }
        if(! $this->guestRoleID->setValue($inID)) {
            return false;
        }
    }
    public function areCleanURLsEnabled() {
        return $this->cleanURLs;
    }
    public function setCleanURLs($areEnabled = true) {
        if($areEnabled == false) {
            if(! $this->cleanURLs->setValue(0)) {
                return false;
            }
            return;
        }
        if(! $this->cleanURLs->setValue(1)) {
            return false;
        }
    }
    public function getTimeZone() {
        return $this->timeZone;
    }
    public function setTimeZone($inTimeZone){
        $validator = new validator('phpTimeZone');
        if(! $validator->validatorExists()) {
            return false;
        }
        if(! $validator->validate($inTimeZone)) {
            return false;
        }
        if(! $this->timeZone->setValue($inTimeZone)) {
            return false;
        }
    }
}