<?php
/**
 * Created by PhpStorm.
 * User: Keegan Laur
 * Date: 4/9/14
 * Time: 5:20 PM
 */

class bootstrap {
    private static $instance;
    private $blocks;
    private $site;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new bootstrap();
        }
        return self::$instance;
    }
    private function __construct() {
        if(! is_file(EDUCASK_ROOT . '/includes/config.php')) {
            header('Location: ' . EDUCASK_WEB_ROOT . '/install.php');
            exit();
        }
        if(is_file(EDUCASK_WEB_ROOT . '/update.php')) {
            header('Location: ' . EDUCASK_WEB_ROOT . '/update.php');
            exit();
        }
        $this->blocks = null;
        $this->site = null;
    }
    public function init() {
        $this->declareConstants();
        $this->doRequires();
        session_name('educaskSession');
        session_start();
        session_regenerate_id();
        $this->connectDatabase();
        $this->initializePlugins();
        $this->getVariables();
        //@TODO: Add Cron Stuff
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
        define('USER_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/user.php');
        define('NOTICE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/notice.php');
        define('NOTICE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/noticeEngine.php');
        define('PASSWORD_FUNCTIONS_FILE', EDUCASK_ROOT . '/thirdPartyLibraries/password/password.php');
        define('NODE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/nodeEngine.php');
        define('NODE_INTERFACE_FILE', EDUCASK_ROOT . '/includes/interfaces/node.php');
        define('MODULE_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/moduleEngine.php');
        define('BLOCK_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/blockEngine.php');
        define('BLOCK_INTERFACE_FILE', EDUCASK_ROOT . '/includes/interfaces/block.php');
        define('STATUS_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/status.php');
        define('STATUS_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/statusEngine.php');
        define('PERMISSION_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/permission.php');
        define('PERMISSION_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/permissionEngine.php');
        define('MENU_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/menu.php');
        define('MENU_ITEM_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/menuItem.php');
        define('MENU_ENGINE_OBJECT_FILE', EDUCASK_ROOT . '/includes/classes/menuEngine.php');
    }
    private function doRequires() {
        require_once(EDUCASK_ROOT . '/thirdPartyLibraries/twig/lib/Twig/Autoloader.php');
        require_once(DATABASE_OBJECT_FILE);
        require_once(VARIABLE_OBJECT_FILE);
        require_once(BLOCK_ENGINE_OBJECT_FILE);
        require_once(SITE_OBJECT_FILE);
        require_once(HOOK_ENGINE_OBJECT_FILE);
        require_once(USER_OBJECT_FILE);
        require_once(CURRENT_USER_OBJECT_FILE);
        require_once(NODE_ENGINE_OBJECT_FILE);
    }
    private function connectDatabase() {
        $database = database::getInstance();
        $database->connect();
        if(! $database->isConnected()) {
            die('<html><head><title>:( Database is a no-go | Educask</title></head><body><h1>:( The database is a no-go.</h1><p>Sorry, but I had problems connecting to the database.</p></body></html>');
        }
    }
    private function initializePlugins() {
        foreach (glob(EDUCASK_ROOT . '/includes/modules/*/plugins/*/*.inc.php') as $toInclude) {
            require_once($toInclude);
            $pluginPath = explode('/', $toInclude);
            //Get the name of the PHP file (the last element in the path array).
            $plugin = end($pluginPath);
            //Remove the .inc.php to get the class name.
            $plugin = str_replace('.inc.php', '', $plugin);
            //Initialize the plugin.
            $plugin::init();
        }
        $hookEngine = hookEngine::getInstance();
        $hookEngine->runAction('defineConstants');
    }
    private function getVariables() {
        $this->site = site::getInstance();
        define('GUEST_ROLE_ID',(int) $this->site->getGuestRoleID()->getValue());
        date_default_timezone_set($this->site->getTimeZone());
        $blockEngine = blockEngine::getInstance();
        $nodeEngine = nodeEngine::getInstance();
        $user = currentUser::getUserSession();
        $node = $nodeEngine->getNode();
        $this->blocks = $blockEngine->getBlocks($this->site->getTheme(), $nodeEngine->getParameters(), get_class($node), $user->getRoleID());
        database::getInstance()->bootstrapDisconnect();
    }
    //@TODO: Add Cron Stuff
    private function render() {
        Twig_Autoloader::register();
        $theme = EDUCASK_ROOT . '/includes/themes/' . $this->site->getTheme();
        str_replace('..', '', $theme);
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
        echo $twig->render('index.twig', array('site' => $this->site, 'blocks' => $this->blocks));
    }
}