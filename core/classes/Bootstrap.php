<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 5:20 PM
 */
class Bootstrap {
    private function __construct() {}
    public static function init() {
        if(defined('CURRENTLY_INSTALLING')) {
            return;
        }
        if (!is_file(EDUCASK_ROOT . '/site/config.xml')) {
            header('Location: ' . EDUCASK_WEB_ROOT . '/install.php');
            exit();
        }
        if (is_readable(EDUCASK_ROOT . '/update.php')) {
            header('Location: ' . EDUCASK_WEB_ROOT . '/update.php');
            exit();
        }
        self::registerAutoloader();
        self::connectDatabase();
        Session::start();
        self::initializePlugins();
        self::getVariables();
        self::cron();
        Router::moveCurrentParametersToPrevious();
        Session::close();
        Database::getInstance()->bootstrapDisconnect();
        ObjectCache::saveInstance();
    }
    public static function registerAutoloader() {
        spl_autoload_register(function($class) {
            if(! is_string($class)) {
                return;
            }
            $class = str_replace(".", "", $class);
            $class = preg_replace('/\s+/', '', $class);
            $toCheck = array();
            $toCheck[] = EDUCASK_ROOT . "/core/classes/{$class}.php";
            $toCheck[] = EDUCASK_ROOT . "/core/interfaces/{$class}.php";
            $toCheck[] = EDUCASK_ROOT . "/core/providers/filters/{$class}.php";
            $toCheck[] = EDUCASK_ROOT . "/core/providers/generalFunctions/{$class}.php";
            $toCheck[] = EDUCASK_ROOT . "/core/providers/validators/{$class}.php";
            foreach($toCheck as $file) {
                if(! is_readable($file)) {
                    continue;
                }
                require_once($file);
                break;
            }
        });
    }
    private static function connectDatabase() {
        $database = Database::getInstance();
        $database->connect();
        if (!$database->isConnected()) {
            die('<html><head><title>:( Database is a no-go | Educask</title></head><body><h1>:( The database is a no-go.</h1><p>Sorry, but I had problems connecting to the database.</p></body></html>');
        }
    }
    private static function initializePlugins() {
        foreach (glob(EDUCASK_ROOT . '/site/modules/*/plugins/*/*.inc.php') as $toInclude) {
            require_once($toInclude);
            $pluginPath = explode('/', $toInclude);
            $plugin = end($pluginPath);
            $plugin = str_replace('.inc.php', '', $plugin);
            $plugin::init();
        }
        $hookEngine = HookEngine::getInstance();
        $hookEngine->runAction('defineConstants');
    }
    private static function getVariables() {
        ObjectCache::getInstance();
        $site = Site::getInstance();
        define('GUEST_ROLE_ID', (int)$site->getGuestRoleID()->getValue());
        define('SITE_EMAIL', $site->getEmail());
        define('SITE_TITLE', $site->getTitle());
        date_default_timezone_set($site->getTimeZone());
        if ($site->isInMaintenanceMode()) {
            if (!PermissionEngine::getInstance()->currentUserCanDo('bypasssMaintenanceMode')) {
                return;
            }
        }
        $blockEngine = BlockEngine::getInstance();
        $user = CurrentUser::getUserSession();
        $hookEngine = HookEngine::getInstance();
        $router = Router::getInstance();
        $hookEngine->runAction('addStaticRoutes');
        $moduleInCharge = $router->whichModuleHandlesRequest();
        $response = self::getResponse($moduleInCharge);
        http_response_code($response->getResponseCode());
        $headers = $response->getHeaders();
        foreach($headers as $header => $value) {
            header($header . ": " . $value, true);
        }
        define('PAGE_TYPE', $response->getPageType());
        $blocks = $blockEngine->getBlocks($site->getTheme(), PAGE_TYPE, $user->getRoleID());
        if($blocks === null) {
            $blocks = array();
        }
        self::render($site, $response, $blocks);
    }
    private static function getResponse($moduleInCharge) {
        $moduleEngine = ModuleEngine::getInstance();
        $moduleInCharge = $moduleEngine->includeModule($moduleInCharge);
        if ($moduleInCharge === false) {
            return Response::fourOhFour();
        }
        $module = new $moduleInCharge(Request::getInstance());
        $response = $module->getResponse();
        if(! is_object($response)) {
            return Response::fiveHundred();
        }
        if(get_class($response) !== "Response") {
            return Response::fiveHundred();
        }
        return $response;
    }
    private static function cron() {
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
    private static function render(Site $site, Response $response, array $blocks) {
        $redirectTo = $response->getRedirectTo();
        if($redirectTo !== null) {
            header("Location: " . $redirectTo, true, 303);
            die();
        }
        $rawContent = $response->getRawContent();
        if($rawContent !== "") {
            echo $rawContent;
            return;
        }
        require_once(EDUCASK_ROOT . "/core/thirdPartyLibraries/twig/lib/Twig/Autoloader.php");
        Twig_Autoloader::register();
        $theme = EDUCASK_ROOT . '/site/themes/' . $site->getTheme();
        str_replace('..', '', $theme);
        if (!is_dir($theme)) {
            $theme = EDUCASK_ROOT . '/site/themes/default';
        }
        $loader = new Twig_Loader_Filesystem(array($theme));
        $baseThemes = glob(EDUCASK_ROOT . '/core/baseThemes/*');
        foreach($baseThemes as $baseTheme) {
            $name = explode('/', $baseTheme);
            $name = end($name);
            $loader->addPath($baseTheme, $name);
        }
        $viewsDirectories = glob(EDUCASK_ROOT . '/site/modules/*/views');
        foreach($viewsDirectories as $viewDirectory) {
            $name = explode('/', $viewDirectory);
            $name = $name[count($name) - 2];
            $loader->addPath($viewDirectory, $name);
        }
        $twig = new Twig_Environment($loader, array('debug' => true,));
        $twig->addExtension(new Twig_Extension_Debug());
        $twig->addExtension(new TwigExtensions());
        if ($site->isInMaintenanceMode()) {
            if (!PermissionEngine::getInstance()->currentUserCanDo('bypasssMaintenanceMode')) {
                echo $twig->render('maintenance.twig', array('site' => $site));
                return;
            }
        }
        $noticeEngine = NoticeEngine::getInstance();
        $notices = $noticeEngine->getNotices();
        $noticeEngine->removeNotices();
        echo $twig->render('index.twig', array('site' => $site, 'model' => $response->getObjectToPassToView(), 'title' => $response->getPageTitle(), 'blocks' => $blocks, 'notices' => $notices, 'response' => $response));
    }
}