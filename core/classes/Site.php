<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 17/12/13
 * Time: 8:01 PM
 */
class Site {
    private static $instance;
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
    private $maintenanceMode;
    private $cronToken;
    private $cronEnabled;
    private $cronRunning;
    private $lastCronRun;
    private $cronFrequency;
    private $maxSessionIdAge;
    //@TODO: Add logo and favicon.
    public static function getInstance() {
        if(isset(self::$instance)) {
            return self::$instance;
        }
        $objectCache = ObjectCache::getInstance();
        $instance = $objectCache->getEncryptedObject("EducaskSiteObject");
        if($instance === false) {
            $newInstance = new Site();
            self::setInstance($newInstance);
            return $newInstance;
        }
        self::$instance = $instance;
        return $instance;
    }
    private static function setInstance(Site $object) {
        self::$instance = $object;
        $objectCache = ObjectCache::getInstance();
        $objectCache->setEncryptedObject("EducaskSiteObject", $object, true);
    }
    private function __construct() {
        $variableEngine = VariableEngine::getInstance();
        $variablesWanted[] = 'siteTitle';
        $variablesWanted[] = 'siteEmail';
        $variablesWanted[] = 'siteTheme';
        $variablesWanted[] = 'siteWebAddress';
        $variablesWanted[] = 'siteWebAddressSecure';
        $variablesWanted[] = 'siteWebDirectory';
        $variablesWanted[] = 'educaskVersion';
        $variablesWanted[] = 'guestRoleID';
        $variablesWanted[] = 'cleanURLsEnabled';
        $variablesWanted[] = 'siteTimeZone';
        $variablesWanted[] = 'maintenanceMode';
        $variablesWanted[] = 'cronToken';
        $variablesWanted[] = 'cronEnabled';
        $variablesWanted[] = 'cronRunning';
        $variablesWanted[] = 'cronFrequency';
        $variablesWanted[] = 'lastCronRun';
        $variablesWanted[] = 'maxSessionIdAge';
        $variables = $variableEngine->getVariables($variablesWanted);
        $this->title = $variables['siteTitle'];
        $this->email = $variables['siteEmail'];
        $this->url = $variables['siteWebAddress'];
        $this->urlSecure = $variables['siteWebAddressSecure'];
        $this->baseDirectory = $variables['siteWebDirectory'];
        $this->theme = $variables['siteTheme'];
        $this->educaskVersion = $variables['educaskVersion'];
        $this->guestRoleID = $variables['guestRoleID'];
        $this->cleanURLs = $variables['cleanURLsEnabled'];
        $this->timeZone = $variables['siteTimeZone'];
        $this->maintenanceMode = $variables['maintenanceMode'];
        $this->cronToken = $variables['cronToken'];
        $this->cronEnabled = $variables['cronEnabled'];
        $this->cronRunning = $variables['cronRunning'];
        $this->cronFrequency = $variables['cronFrequency'];
        $this->lastCronRun = $variables['lastCronRun'];
        $this->maxSessionIdAge = $variables['maxSessionIdAge'];
    }
    public function getTitle() {
        return $this->title;
    }
    public function getEmail() {
        return $this->email;
    }
    public function setTitle($inTitle) {
        $inTitle = strip_tags($inTitle);
        if(! $this->title->setValue($inTitle)) {
            return false;
        }
        if(! $this->title->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function setEmail($inEmail) {
        $validator = new emailValidator();
        if (!$validator->validate($inEmail)) {
            return false;
        }
        if(! $this->email->setValue($inEmail)) {
            return false;
        }
        if(! $this->email->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function getTheme() {
        return $this->theme;
    }
    public function setTheme($inTheme) {
        $inTheme = str_replace('..', '', $inTheme);
        $tempName = '/includes/themes/' . $inTheme;
        $validator = new directoryValidator();
        if (!$validator->validate($tempName)) {
            return false;
        }
        if (!$this->theme->setValue($inTheme)) {
            return false;
        }
        if(! $this->theme->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function getWebAddress($secure = false, $withBaseDirectory = false) {
        if ($secure === true) {
            if ($withBaseDirectory === true) {
                return $this->urlSecure . $this->baseDirectory;
            }
            return $this->urlSecure;
        }
        if ($withBaseDirectory === true) {
            return $this->url . $this->baseDirectory;
        }
        return $this->url;
    }
    public function setWebAddress($inUrl) {
        $validator = new urlValidator();
        if (!$validator->validate($inUrl, array('mightBeIP', 'noDirectories', 'httpOnly'))) {
            return false;
        }
        if (!$this->url->setValue($inUrl)) {
            return false;
        }
        if(! $this->url->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function setSecureWebAddress($inUrl) {
        $validator = new urlValidator();
        if (!$validator->validate($inUrl, array('mightBeIP', 'noDirectories', 'httpsOnly'))) {
            return false;
        }
        if (!$this->urlSecure->setValue($inUrl)) {
            return false;
        }
        if(! $this->urlSecure->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function getBaseDirectory() {
        return $this->baseDirectory;
    }
    public function setBaseDirectory() {
        $directory = EDUCASK_WEB_ROOT;
        if (!$this->baseDirectory->setValue($directory)) {
            return false;
        }
        if(! $this->baseDirectory->save()) {
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
        if (!is_int($inID)) {
            return false;
        }
        if (!$this->guestRoleID->setValue($inID)) {
            return false;
        }
        if(! $this->guestRoleID->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function areCleanURLsEnabled() {
        if($this->cleanURLs->getValue() === 'false') {
            return false;
        }
        return true;
    }
    public function setCleanURLs($areEnabled = true) {
        if (!is_bool($areEnabled)) {
            return false;
        }
        if ($areEnabled === false) {
            if (!$this->cleanURLs->setValue('false')) {
                return false;
            }
            if(! $this->cleanURLs->save()) {
                return false;
            }
            self::setInstance($this);
            return;
        }
        if (!$this->cleanURLs->setValue('true')) {
            return false;
        }
        if(! $this->cleanURLs->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function getTimeZone() {
        return $this->timeZone;
    }
    public function setTimeZone($inTimeZone) {
        $validator = new phpTimeZoneValidator();
        if (!$validator->validate($inTimeZone)) {
            return false;
        }
        if (!$this->timeZone->setValue($inTimeZone)) {
            return false;
        }
        if(! $this->timeZone->save()) {
            return false;
        }
        self::setInstance($this);
    }
    public function isInMaintenanceMode() {
        if ($this->maintenanceMode->getValue() != 'true') {
            return false;
        }
        return true;
    }
    public function setMaintenanceMode($maintenanceMode = false) {
        if (!is_bool($maintenanceMode)) {
            return false;
        }
        if ($maintenanceMode === false) {
            if (!$this->timeZone->setValue('false')) {
                return false;
            }
            if(! $this->maintenanceMode->save()) {
                return false;
            }
            self::setInstance($this);
            return true;
        }
        if (!$this->timeZone->setValue('true')) {
            return false;
        }
        if(! $this->maintenanceMode->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
    public function getCronToken() {
        return $this->cronToken;
    }
    public function setCronToken($newToken) {
        if(! is_string($newToken)) {
            return false;
        }
        $this->cronToken->setValue($newToken);
        if(!$this->cronToken->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
    public function isCronEnabled() {
        if($this->cronEnabled->getValue() == 'false') {
            return false;
        }
        return true;
    }
    public function setCronEnabled($isEnabled = false) {
        if(! is_bool($isEnabled)) {
            return false;
        }
        if($isEnabled === false) {
            $this->cronRunning->setValue('false');
            if(! $this->cronEnabled->save()) {
                return false;
            }
            self::setInstance($this);
            return true;
        }
        $this->cronRunning->setValue('true');
        if(! $this->cronEnabled->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
    public function isCronRunning() {
        if($this->cronRunning->getValue() == 'false') {
            return false;
        }
        return true;
    }
    public function setCronRunning($isRunning = false) {
        if(! is_bool($isRunning)) {
            return false;
        }
        if($isRunning === false) {
            $this->cronRunning->setValue('false');
            if(! $this->cronRunning->save()) {
                return false;
            }
            self::setInstance($this);
            return true;
        }
        $this->cronRunning->setValue('true');
        if(! $this->cronRunning->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
    public function getCronFrequency() {
        return $this->cronFrequency;
    }
    public function setCronFrequency($frequency) {
        $this->cronFrequency->setValue($frequency);
        if(! $this->cronFrequency->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
    public function getLastCronRun() {
        return DateTime::createFromFormat('Y-m-d H:i:s', $this->lastCronRun->getValue());
    }
    public function setLastCronRun(DateTime $runTime) {
        $this->lastCronRun->setValue($runTime->format('Y-m-d H:i:s'));
        if(! $this->lastCronRun->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
    public function doesCronNeedToRun() {
        if(! $this->isCronEnabled()) {
            return false;
        }
        if($this->isCronRunning()) {
            return false;
        }
        $lastCronRun = $this->getLastCronRun();
        $currentTime = new DateTime();
        $cronFrequency = DateInterval::createFromDateString($this->cronFrequency);
        $lastCronRun->add($cronFrequency);
        if($currentTime < $lastCronRun) {
            return false;
        }
        return true;
    }
    public function getMaxSessionIdAge() {
        return $this->maxSessionIdAge;
    }
    public function setMaxSessionIdAge($inMaxAgeInMinutes) {
        if(! is_numeric($inMaxAgeInMinutes)) {
            return false;
        }
        $inMaxAgeInMinutes = intval($inMaxAgeInMinutes);
        $maxAge = $inMaxAgeInMinutes * 60;
        $this->maxSessionIdAge->setValue($maxAge);
        if(! $this->maxSessionIdAge->save()) {
            return false;
        }
        self::setInstance($this);
        return true;
    }
}