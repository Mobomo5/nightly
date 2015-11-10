<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/06/2015
 * Time: 9:17 PM
 */
class home extends Controller {
    public function index() {
        $user = CurrentUser::getUserSession();
        if(! $user->isLoggedIn()) {
            return new Response(200, "@home/notLoggedIn.twig", "Welcome", "home");
        }
        return new Response(200, "@home/main.twig", "Hi {$user->getFirstName()}", "home", $user);
    }
}