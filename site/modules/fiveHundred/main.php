<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 16/06/2015
 * Time: 8:40 PM
 */

class fiveHundred implements IModule {
    public function __construct(Request $request) {}
    public function getResponse(){
        return Response::fiveHundred();
    }
}