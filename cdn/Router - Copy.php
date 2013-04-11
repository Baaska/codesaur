<?php
namespace cdn;

class Router
{
    public $request;
    protected $callback = NULL;
    protected $default_route = NULL;
    protected $last_route = NULL;
    protected $params = array();
    protected $routes = array();
    protected $routes_original = array();
    protected $show_errors = TRUE;
            
    function __construct($_request = NULL)
    {
        if ($_request)
        {
            $this->request = $_request;
        }
        else
        {
            $this->request = new Request();
        }
    }
    
    public function show_errors()
    {
        $this->show_errors = TRUE;
    }
    
    public function hide_errors()
    {
        $this->show_errors = FALSE;
    }
    
    public function default_route($callback)
    {
        $this->default_route = $callback;
    }
    
    public function run()
    {
        $matched_route = FALSE;

        ksort($this->routes);
        
        foreach ($this->routes as $priority => $routes)
        {
            foreach ($routes as $route => $callback)
            {
                if (preg_match($route, $this->request->url_clean, $matches))
                {
                    $matched_route = TRUE;
                    $params = array($this->request->url_clean);
                    
                    foreach ($matches as $key => $match)
                    {
                        if (is_string($key))
                        {
                            $params[] = $match;
                        }
                    }
                    
                    $this->params = $params;
                    $this->callback = $callback;
                    
                    return array('callback' => $callback, 'params' => $params, 'route' => $route, 'original_route' => $this->routes_original[$priority][$route]);
                }
            }
        }
        
        if ( ! $matched_route && $this->default_route !== NULL)
        {
            return array('params' => $this->request->url_clean, 'callback' => $this->default_route, 'route' => FALSE, 'original_route' => FALSE);
        }
    }
    
    public function dispatch()
    {
        if ($this->callback == NULL || $this->params == NULL)
        {
            throw new Exception('No callback or parameters found, please run $router->run() before $router->dispatch()');
            return FALSE;
        }
        
        call_user_func_array($this->callback, $this->params);
        return TRUE;
    }
    
    public function execute()
    {
        $this->run();
        $this->dispatch();
    }
    
    public function route($route, $callback, $priority = 10)
    {
        $original_route = $route;
        
        $route = rtrim($route, '/') . '/';
        $route = preg_replace('/\<\:(.*?)\|(.*?)\>/', '(?P<\1>\2)', $route);
        $route = preg_replace('/\<\:(.*?)\>/', '(?P<\1>[A-Za-z0-9\-\_]+)', $route);
        $route = preg_replace('/\<\#(.*?)\>/', '(?P<\1>[0-9]+)', $route);
        $route = preg_replace('/\<\*(.*?)\>/', '(?P<\1>.+)', $route);
        $route = preg_replace('/\<\!(.*?)\>/', '(?P<\1>[^\/]+)', $route);
        $route = '#^' . $route . '$#';
        
        if (isset($this->routes[$priority][$route]))
        {
            if ($this->show_errors)
            {
                throw new Exception('The URI "' . htmlspecialchars($route) . '" already exists in the router table');
            }
            return FALSE;
        }
        
        $this->routes[$priority][$route] = $callback;
        $this->routes_original[$priority][$route] = $original_route;
        return TRUE;
    }
}
