<?php
namespace cdn;

require 'Def.php';

require 'Common.php';
require 'Exception.php';
require 'Request.php';
require 'Router.php';
require 'Session.php';
require 'Authentication.php';
require 'MySQL.php';
require 'Controller.php';
require 'Model.php';
require 'View.php';
require 'Response.php';

class Application {
    public $request = NULL;
    public $router = NULL;
    public $session = NULL;
    public $auth = NULL;
    public $controller = NULL;
    public $model = NULL;
    public $view = NULL;
    public $response = NULL;

    function __construct($cdn = DEF_SYSTEM, $public = DEF_FRONTEND, $private = DEF_BACKEND) {        
        $this->setEnvironment();
        $this->definePaths($cdn, $public, $private);

        $this->session = new Session();
        
        $this->request = new Request();
        $this->router = new Router($this->request);
        $this->auth = new Authentication($this->session);
        $this->controller = new Controller();
        $this->model = new Model();
        $this->view = new View();
        $this->response = new Response();
    }
    
    private function setEnvironment() {
        if (defined('DEVELOPMENT')) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'Off');
        }        
    }
    
    private function definePaths($cdn, $public, $private) {
        $realsyspath = realpath($cdn);
        
        if ( ! is_dir($realsyspath))
            exit("System folder path does not appear to be set correctly!");
        
        define('CDN_SYSTEM', str_replace("\\", DS, $realsyspath . DS));
        define('CDN_INDEX', dirname(CDN_SYSTEM));
        define('CDN_FRONTEND', $public . DS);
        define('CDN_BACKEND',  $private . DS);
        
        if ( ! is_dir(CDN_FRONTEND))
            exit("Application folder path does not appear to be set correctly!");
        
        if ( ! is_dir(CDN_BACKEND))
            exit("Administrator folder path does not appear to be set correctly!");
        
        if (defined('DEF_ERROR_LOG')) {
            ini_set('log_errors', 'On');
            ini_set('error_log', CDN_INDEX.DS.DEF_TEMP_DIR.DS.DEF_LOG_DIR.DS.DEF_ERROR_LOG);
        }
    }
}