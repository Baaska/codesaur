<?php
namespace cdn;

require 'Common.php';
require 'Request.php';
require 'Router.php';
require 'Session.php';
require 'Authentication.php';
require 'MySQL.php';
require 'Controller.php';
require 'Model.php';
require 'View.php';
require 'Response.php';

class Application
{
    public $request = NULL;
    public $router = NULL;
    public $session = NULL;
    public $auth = NULL;
    public $controller = NULL;
    public $model = NULL;
    public $view = NULL;
    public $response = NULL;
            
    function __construct($cdn = 'cdn', $public = 'public', $private = 'private')
    {        
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
    
    private function setEnvironment()
    {
        if (defined('DEVELOPMENT'))
        {
            ini_set('display_errors', 'On');
            error_reporting(E_ALL);
        }
        else
        {
            ini_set('display_errors', 0);
            error_reporting(0);
        }        
    }
    
    private function definePaths($cdn, $public, $private)
    {
        $realsyspath = realpath($cdn);
        if ( ! is_dir($realsyspath))
            exit("System folder path does not appear to be set correctly!");
        define('CDN_SYSTEM', str_replace("\\", "/", $realsyspath.'/'));
        
        define('CDN_FRONTEND', $public.'/');
        if ( ! is_dir(CDN_FRONTEND))
            exit("Application folder path does not appear to be set correctly!");
        
        define('CDN_BACKEND',  $private.'/');
        if ( ! is_dir(CDN_BACKEND))
            exit("Administrator folder path does not appear to be set correctly!");
    }
}