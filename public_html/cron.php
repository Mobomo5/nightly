<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 23/07/2015
 * Time: 7:51 PM
 */
class Cron {
    private $cronToken;
    public function __construct($cronToken = "") {
        if(! is_string($cronToken)) {
            $this->cronToken = "";
            return;
        }
        $this->cronToken = $cronToken;
    }
    public static function init() {
        if(! isset($_GET['cronTok'])) {
            return;
        }
        define('EDUCASK_ROOT', dirname(getcwd()));
        require_once(EDUCASK_ROOT . '/core/classes/Bootstrap.php');
        Bootstrap::registerAutoloader();
        $database = Database::getInstance();
        $database->connect();
        if(! $database->isConnected()) {
            return;
        }
        Bootstrap::initializePlugins();
        $site = Site::getInstance();
        date_default_timezone_set($site->getTimeZone());
        $cron = new Cron($_GET['cronTok']);
        $cron->run();
    }
    public function run() {
        if(! isset($this->cronToken)) {
            return;
        }
        if($this->cronToken === "") {
            return;
        }
        if($this->cronToken !== Config::getInstance()->getCronToken()) {
            return;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            return;
        }
        $site = Site::getInstance();
        if(! $site->doesCronNeedToRun()) {
            return;
        }
        if($site->isCronRunning()) {
            return;
        }
        $site->setCronRunning(true);
        $site->setLastCronRun(new DateTime());
        $hookEngine = HookEngine::getInstance();
        $hookEngine->runAction('cronRun');
        $logger = Logger::getInstance();
        $logger->logIt(new LogEntry(1, logEntryType::info, "Cron ran.", 0, new DateTime()));
        $site->setCronRunning(false);
    }
}
Cron::init();