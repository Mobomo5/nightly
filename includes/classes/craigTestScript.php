<?php

/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 6/6/14
 * Time: 5:05 PM
 */
require_once(ROLE_ENGINE_FILE);

class craigTestScript {

    // notices

    public function testNoticeEngine() {

        echo "this will create 100 error notices.<br>";
        for ($i = 0; $i < 100; $i++) {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::error, 'This is error ' . $i, true));
        }

        if (sizeof(noticeEngine::getInstance()->getNotices()) == 100) {
            echo "Created 100 notices<br>";
        }

    }

    public function testRemoveNotices() {
        noticeEngine::getInstance()->removeNotices();

        if (sizeof(noticeEngine::getInstance()->getNotices()) == 0) {
            echo 'Removed all notices<br>';
        }
    }

    public function testGetSpecificNotice() {
        // add 3 notices
        for ($i = 0; $i < 3; $i++) {
            noticeEngine::getInstance()->addNotice(new notice(noticeType::neutral, $i));
        }

        $toFind = new notice(noticeType::neutral, '2');

        if (noticeEngine::getInstance()->findNotice($toFind) == 2) {
            echo 'Found the correct notice<br>';
        }

        if (noticeEngine::getInstance()->findNotice(new notice(noticeType::error, '2')) == -1) {
            echo 'Couldn\'t find the bad notice as intended.<br>';
        }

        noticeEngine::getInstance()->removeNotices();

    }

    // role engine

    public function testGetRoleByNameAndID() {

        $role = roleEngine::getInstance()->getRoleByID(1);
        if ($role->getName() == 'Guest') {
            echo "Found guest as intented.<br>";
        }

        $roleByName = roleEngine::getInstance()->getRoleByName('Guest');

        if ($roleByName->getId() == 1) {
            echo "Found roleid 1 by name as intended<br>";
        }

        $falseRole = roleEngine::getInstance()->getRoleByName('Floobur');
        if (!$falseRole->getId()) {
            echo 'Couldn\'t find the the \'Floobur\' role as intended<br>';
        }

        $falseRole = roleEngine::getInstance()->getRoleByID(2462);
        if (!$falseRole->getId()) {
            echo 'Couldn\'t find the the \'2462\' role as intended<br>';
        }

    }

    // variableEngine

    public function testVariables() {
        if (!variableEngine::getInstance()->getVariable('ShouldNotBeInThere')) {
            echo "Couldn't find 'ShouldNotBeInTHere' variable as intended<br>";
        }

        $var = new variable("ShouldNotBeInThere", "ButItIS");

        if (variableEngine::getInstance()->addVariable($var)) {
            echo "Inserted the new variable successfully<br>";
        }
        $var = variableEngine::getInstance()->getVariable("ShouldNotBeInThere");
        if ($var->getName() == "ShouldNotBeInThere") {
            echo "Found the proper variable.<br>";
        }
        if (variableEngine::getInstance()->deleteVariable($var)) {
            echo "Variable deleted<br>";
        }
    }

}

$test = new craigTestScript();
$test->testNoticeEngine();
$test->testRemoveNotices();
$test->testGetSpecificNotice();
$test->testGetRoleByNameAndID();
$test->testVariables();
