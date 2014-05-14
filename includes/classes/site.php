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
    private $guestRoleID;
    private $cleanURLs;
    private $timeZone;
    //@TODO: Add Cron Stuff
    //@TODO: Add logo and favicon.

    public static function getInstance() {
        if (!isset($_SESSION['educaskSite'])) {
            self::setInstance(new site());
        }

        return $_SESSION['educaskSite'];
    }
    private static function setInstance(site $object) {
        //verify the variable given is a site object. If it is not, get out of here.
        if (get_class($object) != 'site') {
            return;
        }
        $_SESSION['educaskSite'] = $object;
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
        //@TODO: Add Cron Stuff
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
        //@TODO: Add Cron Stuff
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
        self::setInstance($this);
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
        self::setInstance($this);
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
        self::setInstance($this);
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
        self::setInstance($this);
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
        self::setInstance($this);
    }
    public function getBaseDirectory() {
        return $this->baseDirectory;
    }
    public function setBaseDirectory($inDirectory) {
        if(! $this->baseDirectory->setValue($inDirectory)) {
            return false;
        }
        self::setInstance($this);
    }
    public function getEducaskVersion() {
        return $this->educaskVersion;
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
        self::setInstance($this);
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
        self::setInstance($this);
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
        self::setInstance($this);
    }
    //@TODO: Add Cron Stuff
}