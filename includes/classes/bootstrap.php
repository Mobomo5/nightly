<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 5:20 PM
 */

class bootstrap {
    private static $instance;
    private $database;
    private $site;
    private $blockEngine;
    private $blocks;
    private $user;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new bootstrap();
        }
        return self::$instance;
    }
    private function __construct() {
        //Do nothing
    }
    private function construct() {
        $this->database = database::getInstance();
        $this->site = site::getInstance();
        $this->blockEngine = blockEngine::getInstance();
        $this->user = currentUser::getUserSession();
    }
    public function init() {
        $this->declareConstants();
        $this->doRequires();
        $this->construct();
        $this->connectDatabase();
        $this->initializePlugins();
        $this->getVariables();
        $this->render();
    }
    private function declareConstants() {
        define('DATABASE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/database.php');
        define('DATABASE_INTERFACE_FILE', EDUCASK_ROOT . '/includes/interfaces/databaseInterface.php');
        define('VARIABLE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/variable.php');
        define('VARIABLE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/variableEngine.php');
        define('GENERAL_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/general.php');
        define('GENERAL_FUNCTION_INTERFACE_FILE', EDUCASK_ROOT . '/includes/interfaces/generalFunction.php');
        define('VALIDATOR_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/validator.php');
        define('VALIDATOR_INTERFACE_FILE', EDUCASK_ROOT . '/includes/interfaces/validator.php');
        define('SYSTEM_LOGGER_OBJET_FILE', EDUCASK_ROOT . '/includes/classes/systemLog.php');
        define('SITE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/site.php');
        define('LINK_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/link.php');
        define('HASHER_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/hasher.php');
        define('HOOK_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/hookEngine.php');
        define('CURRENT_USER_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/currentUser.php');
        define('HONEYPOT_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/honeypot.php');
        define('NOTICE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/notice.php');
        define('NOTICE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/noticeEngine.php');
        define('PASSWORD_FUNCTIONS_FILE', EDUCASK_ROOT . '/thirdPartyLibraries/password/password.php');
        define('NODE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/nodeEngine.php');
        define('MODULE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/moduleEngine.php');
    }
    private function doRequires() {
        require_once(EDUCASK_ROOT . '/thirdPartyLibraries/twig/lib/Twig/Autoloader.php');
        require_once(DATABASE_OBJECT_FILE);
        require_once(VARIABLE_OBJECT_FILE);
        require_once(SITE_OBJECT_FILE);
        require_once(HOOK_ENGINE_OBJECT_FILE);
        require_once(CURRENT_USER_OBJECT_FILE);
        require_once(NODE_ENGINE_OBJECT_FILE);
    }
    private function connectDatabase() {
        $this->database->connect();
        if(! $this->database->isConnected()) {
            die('<html><head><title>:( Database is a no-go | Educask</title></head><body><h1>:( The database is a no-go.</h1><p>Sorry, but I had problems connecting to the database.</p></body></html>');
        }
    }
    private function initializePlugins() {
        foreach (glob(EDUCASK_ROOT . '/includes/modules/*/plugins/*.php') as $plugin) {
            require_once($plugin);
        }
    }
    private function getVariables() {
        define('GUEST_ROLE_ID', $this->site->getGuestRoleID());
        date_default_timezone_set($this->site->getTimeZone());
        $nodeEngine = nodeEngine::getInstance();

        $blocks = NULL; //@ToDo: Fix this: $this->blockEngine->getBlocks($site->getTheme(), $site->getCurrentPage(),,$this->user->getRoleID());
        $this->database->bootstrapDisconnect();
    }
    private function render() {
        Twig_Autoloader::register();
        $theme = EDUCASK_ROOT . '/includes/themes/' . $this->site->getTheme() . '/';
        if(! is_dir($theme)) {
            $theme = EDUCASK_ROOT . '/includes/themes/default';
        }
        $loader = new Twig_Loader_Filesystem(array($theme));
        foreach(glob(EDUCASK_ROOT . '/includes/baseThemes/*') as $baseTheme) {
            $name = explode('/', $baseTheme);
            $name = end($name);
            $loader->addPath($baseTheme, $name);
        }
        $twig = new Twig_Environment($loader);
        echo $twig->render('index.twig', array('site' => $site, 'blocks' => $blocks));
    }
}