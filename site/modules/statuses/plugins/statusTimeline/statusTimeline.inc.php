<?php

/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/11/2015
 * Time: 8:01 PM
 */
class statusTimeline implements IPlugin {
    public static function init() {
        $hookEngine = HookEngine::getInstance();
        $hookEngine->addFilter('buildTimeline', new statusTimeline());
    }
    public static function run($inContent = '') {
        $statusEngine = StatusEngine::getInstance();
        $statuses = $statusEngine->getStatusesByUser(CurrentUser::getUserSession()->getUserID());
        if(! $statuses) {
            return array();
        }
        $userEngine = UserEngine::getInstance();
        foreach($statuses as $status) {
            if(! ($status instanceof Status)) {
                continue;
            }
            $user = $userEngine->getUser($status->getPosterID());
            if(! ($user instanceof User)) {
                continue;
            }
            $status->setPoster($user);
        }
        return $statuses;
    }
    public static function getPriority() {
        return 5;
    }
}