<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 9/5/2015
 * Time: 12:56 PM
 */
class Request {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Request();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing
    }
    public static function getParameter($inParameterName, $parameterType = parameterType::all, IFilter $filter = null){
        if($parameterType === parameterType::GET) {
            return self::getGetParameter($inParameterName, $filter);
        }
        if($parameterType === parameterType::POST) {
            return self::getPostParameter($inParameterName, $filter);
        }
        if($parameterType === parameterType::SESSION) {
            return self::getSessionParameter($inParameterName, $filter);
        }
        if($parameterType === parameterType::COOKIE) {
            return self::getCookieParameter($inParameterName, $filter);
        }
        if($parameterType !== parameterType::all) {
            return false;
        }
        $toReturn = array();
        $get = self::getGetParameter($inParameterName, $filter);
        $post = self::getPostParameter($inParameterName, $filter);
        $session = self::getSessionParameter($inParameterName, $filter);
        if($get !== false) {
            $toReturn['GET'] = $get;
        }
        if($post !== false) {
            $toReturn['POST'] = $post;
        }
        if($session !== false) {
            $toReturn['SESSION'] = $session;
        }
        return $toReturn;
    }
    public static function getGetParameter($name, IFilter $filter = null) {
        if(! isset($_GET[$name])) {
            return false;
        }
        return self::filter($_GET[$name], $filter);
    }
    public static function getPostParameter($name, IFilter $filter = null) {
        if(! isset($_POST[$name])) {
            return false;
        }
        return self::filter($_POST[$name], $filter);
    }
    public static function getSessionParameter($name, IFilter $filter = null) {
        if(! isset($_SESSION[$name])) {
            return false;
        }
        return self::filter($_SESSION[$name], $filter);
    }
    public static function getCookieParameter($name, IFilter $filter = null) {
        if(! isset($_COOKIE[$name])) {
            return false;
        }
        return self::filter($_COOKIE[$name], $filter);
    }
    private static function filter($variable, IFilter $filter = null) {
        if($filter === null) {
            return $variable;
        }
        return $filter->run($variable);
    }
    public static function requestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    public static function isPostRequest() {
        if(self::requestMethod() !== "POST") {
            return false;
        }
        return true;
    }
    public static function getParameters($asArray = false, $decoded = true) {
        $router = Router::getInstance();
        if(! is_bool($decoded)) {
            return $router->getDecodedParameters($asArray);
        }
        if($decoded) {
            return $router->getDecodedParameters($asArray);
        }
        return $router->getParameters($asArray);
    }
    public static function getPreviousParameters($asArray = false) {
        $router = Router::getInstance();
        return $router->getPreviousParameters($asArray);
    }
}
abstract class parameterType {
    const all = 'all';
    const GET = 'GET';
    const POST = 'POST';
    const SESSION = 'SESSION';
    const COOKIE = 'COOKIE';
}