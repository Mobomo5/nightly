<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 21/04/2015
 * Time: 7:18 PM
 */
class listForm implements IModule {
    private $response;
    public function __construct(Request $request) {
        if(count($request->getParameters(true)) > 2) {
            $this->response = Response::fourOhFour();
            return;
        }
        if(! PermissionEngine::getInstance()->currentUserCanDo("canListUsers")) {
            $this->response = Response::fourOhThree();
            return;
        }
        $database = Database::getInstance();
        if(! $database->isConnected()) {
            $this->response = Response::fiveHundred();
            return;
        }
        $results = $database->getData("*", "user");
        if($results === false) {
            $this->response = Response::fiveHundred();
            return;
        }
        if($results === null) {
            $results = array();
        }
        $users = array();
        foreach($results as $rawData) {
            if($rawData['userID'] == 0) {
                continue;
            }
            $profilePictureLocation = new Link($rawData['profilePictureLocation']);
            $birthday = new DateTime($rawData['birthday']);
            if($rawData['active'] == 1) {
                $active = true;
            } else {
                $active = false;
            }
            if($rawData['isExternalAuthentication']) {
                $externalAuth = true;
            } else {
                $externalAuth = false;
            }
            $users[] = new User($rawData['userID'], $rawData['roleID'], $rawData['givenIdentifier'], $rawData['userName'], $rawData['firstName'], $rawData['lastName'], $rawData['email'], $profilePictureLocation, $birthday, $active, $externalAuth);
        }
        $this->response = new Response(200, "@users/list.twig", "All Users", "user", $users);
    }
    public function getResponse() {
        return $this->response;
    }
}