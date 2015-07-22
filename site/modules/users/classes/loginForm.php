<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class loginForm implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 2) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(currentUser::getUserSession()->isLoggedIn()) {
            $this->response = Response::fourOhFour();
            return;
        }
        $lockoutEngine = LockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            $minutesLeft = $this->minutesLeftInLockout();
            $this->response = new Response(403, "@users/lockedOut.twig", "Locked Out", "lockedOut", $minutesLeft);
            return;
        }
        if($request->isPostRequest()) {
            $this->response = $this->doLogIn();
            return;
        }
        $this->response = new Response(200, "@users/login.twig", "Login", "login");
    }
    private function doLogIn() {
        if(! AntiForgeryToken::getInstance()->validate()) {
            return Response::fiveHundred();
        }
        if(! Honeypot::getInstance()->validate()) {
            return Response::fiveHundred();
        }
        $hookEngine = HookEngine::getInstance();
        $hookEngine->runAction('userIsLoggingIn');
        $user = CurrentUser::getUserSession();
        if($user->isLoggedIn()) {
            return Response::redirect(new Link(""));
        }
        $username = Request::getPostParameter("username");
        $password = Request::getPostParameter("password");
        if(! $username) {
            return $this->showErrorMessage();
        }
        if(! $password) {
            return $this->showErrorMessage();
        }
        $lockoutEngine = LockoutEngine::getInstance();
        if($lockoutEngine->isLockedOut($_SERVER['REMOTE_ADDR'])) {
            return Response::redirect(new Link("users/login"));
        }
        $logger = Logger::getInstance();
        $username = preg_replace('/\s+/', '', strip_tags($username));
        if (!$user->logIn($username, $password)) {
            $logger->logIt(new LogEntry(0, logEntryType::warning, 'Someone failed to log into ' . $username . '\'s account from IP:' . $_SERVER['REMOTE_ADDR'], 0, new DateTime()));
            return $this->showErrorMessage();
        }
        $user = CurrentUser::getUserSession();
        $logger->logIt( new LogEntry(0, logEntryType::info, 'A new session was opened for ' . $user->getFullName() . ', who has an IP of ' . $_SERVER['REMOTE_ADDR'] . '.', $user->getUserID(), new DateTime()));
        $hookEngine->runAction('userLoggedIn');
        return Response::redirect(new Link(""));
    }
    private function minutesLeftInLockout() {
        $lockoutEngine = LockoutEngine::getInstance();
        $lockout = $lockoutEngine->getLockout($_SERVER['REMOTE_ADDR']);
        if($lockout === false) {
            return $lockoutEngine->getLockoutPeriod();
        }
        $totalLockoutLength = $lockout->getNumberOfFailedAttempts() * $lockoutEngine->getLockoutPeriod();
        $lockoutStart = clone $lockout->lastUpdated();
        $lockedOutUntil = $lockoutStart->add(DateInterval::createFromDateString($totalLockoutLength . ' minutes'));
        $currentTime = new DateTime();
        $minutesLeft = $currentTime->diff($lockedOutUntil);
        $minutesLeft = ($minutesLeft->days * 24 * 60) + ($minutesLeft->h * 60) + $minutesLeft->i;
        $minutesLeft += 1;
        return $minutesLeft;
    }
    private function showErrorMessage() {
        $hookEngine = HookEngine::getInstance();
        $hookEngine->runAction('userFailedToLogIn');
        NoticeEngine::getInstance()->addNotice(new Notice(noticeType::warning, 'I couldn\'t log you in.'));
        return Response::redirect(new Link("users/login"));
    }
    public function getResponse() {
        return $this->response;
    }
}