<?php
namespace cdn;

class Route
{
    private $url = '';
    private $methods = array('GET', 'POST', 'PUT', 'DELETE');
    private $target;
    private $name;
    private $filters = array();
    private $params = array();
    
    public function get_url()
    {
        return $this->url;
    }
    
    public function set_url($url)
    {
        $url = (string) $url;
        
        if (substr($url, -1) !== '/')
            $url .= '/';
        
        $this->url = $url;
    }
    
    public function get_target()
    {
        return $this->target;
    }
    
    public function set_target($target)
    {
        $this->target = $target;
    }
    
    public function get_methods()
    {
        return $this->methods;
    }
    
    public function set_methods(array $methods)
    {
        $this->methods = $methods;
    }
    
    public function get_name()
    {
        return $this->name;
    }
    
    public function set_name($name)
    {
        $this->name = (string) $name;
    }
    
    public function set_filters(array $filters)
    {
        $this->filters = $filters;
    }
    
    public function get_regex()
    {
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substitute_filter'), $this->url);
    }
    
    private function substitute_filter($matches)
    {
        if (isset($matches[1]) && isset($this->filters[$matches[1]]))
        {
            return $this->filters[$matches[1]];
        }
        return "([\w-]+)";
    }
    
    public function get_parameters()
    {
        return $this->params;
    }
    
    public function set_parameters(array $parameters)
    {
        $this->params = $parameters;
    }
}

class Router
{
    public $request = NULL;
    private $routes = array();
    private $named_routes = array();
    
    function __construct($_Request = NULL)
    {
        if ($_Request)
        {
            $this->request = $_Request;
        }
        else
        {
            $this->request = new Request();
        }
    }
    
    public function map($route_url, $target = '', array $args = array())
    {
        $route = new Route();
        
        $route->set_url($this->request->url_base_path . $route_url);
        
        $route->set_target($target);
        
        if (isset($args['methods']))
        {
            $methods = explode(',', $args['methods']);
            $route->set_methods($methods);
        }
        
        if (isset($args['filters']))
        {
            $route->set_filters($args['filters']);
        }
        
        if (isset($args['name']))
        {
            $route->set_name($args['name']);
            if ( ! isset($this->named_routes[$route->get_name()]))
            {
                $this->named_routes[$route->get_name()] = $route;
            }
        }
        $this->routes[] = $route;
    }
    
    public function match_current_request()
    {
        $request_method = (isset($_POST['_method']) && ($_method = strtoupper($_POST['_method'])) && in_array($_method, array('PUT', 'DELETE'))) ? $_method : $_SERVER['REQUEST_METHOD'];
        $request_url = $this->request->url_dirty;
        
        if (($pos = strpos($request_url, '?')) !== false)
        {
            $request_url =  substr($request_url, 0, $pos);
        }
        
        return $this->match($request_url, $request_method);
    }
    
    public function match($request_url, $request_method = 'GET')
    {
        foreach ($this->routes as $route)
        {
            if ( ! in_array($request_method, $route->get_methods()))
                    continue;
            
            if ( ! preg_match("@^".$route->get_regex()."*$@i", $request_url, $matches))
                    continue;

            $params = array();

            if (preg_match_all("/:([\w-]+)/", $route->get_url(), $argument_keys))
            {
                $argument_keys = $argument_keys[1];
                foreach ($argument_keys as $key => $name)
                {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }
            }
            
            $route->set_parameters($params);
            return $route;
        }
        return false;
    }
    
    public function generate($route_name, array $params = array())
    {
        if ( ! isset($this->named_routes[$route_name]))
            throw new Exception("No route with the name $route_name has been found.");
        
        $route = $this->named_routes[$route_name];
        $url = $route->get_url();
        
        if ($params && preg_match_all("/:(\w+)/", $url, $param_keys))
        {
            $param_keys = $param_keys[1];
            foreach ($param_keys as $i => $key)
            {
                if (isset($params[$key]))
                    $url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
            }
        }
        return $url;
    }
}