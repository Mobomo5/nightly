<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 29/07/2015
 * Time: 9:03 PM
 */
class cron extends Controller {
    public function index() {
        return Response::fourOhFour();
    }
    public function run() {
        $parameters = Request::getParameters(true);
        if(count($parameters) !== 3) {
            return Response::fourOhFour();
        }
        $cronToken = $parameters[2];
        if($cronToken !== Config::getInstance()->getCronToken()) {
            return Response::fourOhFour();
        }
        $response = Response::raw("", 204);
        $site = Site::getInstance();
        if(! $site->doesCronNeedToRun()) {
            return $response;
        }
        if($site->isCronRunning()) {
            return $response;
        }
        $site->setCronRunning(true);
        $site->setLastCronRun(new DateTime());
        $hookEngine = HookEngine::getInstance();
        $hookEngine->runAction('cronRun');
        $logger = Logger::getInstance();
        $logger->logIt(new LogEntry(1, logEntryType::info, "Cron ran.", 0, new DateTime()));
        $site->setCronRunning(false);
        return $response;
    }
}