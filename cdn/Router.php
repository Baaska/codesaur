<?php
namespace cdn;

class Route {
    private $_url = '';
    private $_methods = array('GET', 'POST', 'PUT', 'DELETE');
    private $_target;
    private $_name;
    private $_filters = array();
    private $_params = array();
    
    public function getUrl() {
        return $this->_url;
    }
    
    public function setUrl($url) {
        $url = (string) $url;
        
        if (substr($url, -1) !== DS)
            $url .= DS;
        
        $this->_url = $url;
    }
    
    public function getTarget() {
        return $this->_target;
    }
    
    public function setTarget($target) {
        $this->_target = $target;
    }
    
    public function getMethods() {
        return $this->_methods;
    }
    
    public function setMethods(array $methods) {
        $this->_methods = $methods;
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function setName($name) {
        $this->_name = (string) $name;
    }
    
    public function setFilters(array $filters) {
        $this->_filters = $filters;
    }
    
    public function getFilters() {
        return $this->_filters;
    }
    
    public function getRegex() {
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->getUrl());
    }
    
    private function substituteFilter($matches) {
        if (isset($matches[1]) && isset($this->_filters[$matches[1]]))
            return $this->_filters[$matches[1]];

        return "([\w-]+)";
    }
    
    public function getParameters() {
        return $this->_params;
    }
    
    public function setParameters(array $parameters) {
        $this->_params = $parameters;
    }
}

class Router {
    private $request = NULL;
    private $routes = array();
    private $named_routes = array();
    
    function __construct($_Request = NULL) {
        if ($_Request)
            $this->request = $_Request;
        else
            $this->request = new Request();
    }
    
    public function getRequest() {
        return $this->request;
    }

    public function map($route_url, $target = '', array $args = array()) {
        $route_url = $this->request->cleanDoubleSlash($route_url);
        
        $route = new Route();
        
        $route->setUrl($route_url);
        
        $route->setTarget($target);
        
        if (isset($args['methods'])) {
            $methods = explode(',', $args['methods']);
            $route->setMethods($methods);
        }
        
        if (isset($args['filters']))
            $route->setFilters($args['filters']);
        
        if (isset($args['name'])) {
            $route->setName($args['name']);
            
            if ( ! isset($this->named_routes[$route->getName()]))
                $this->named_routes[$route->getName()] = $route;
        }
        
        $this->routes[] = $route;
    }
    
    public function matchCurrentRequest() {
        $request_url = $this->request->getCleanUrl();
        
        if (($pos = strpos($request_url, '?')) !== false)
            $request_url =  substr($request_url, 0, $pos);
        
        return $this->match($request_url, $this->request->getMethod());
    }
    
    protected function match($request_url, $request_method = 'GET') {
        foreach ($this->routes as $route)
        {
            if ( ! in_array($request_method, $route->getMethods()))
                    continue;
            
            if ( ! preg_match("@^" . $route->getRegex() . "*$@i", $request_url, $matches))
                    continue;

            $params = array();

            if (preg_match_all("/:([\w-]+)/", $route->getUrl(), $argument_keys)) {
                $argument_keys = $argument_keys[1];
                foreach ($argument_keys as $key => $name) {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }
            }
            
            $route->setParameters($params);
            
            return $route;
        }
        return FALSE;
    }
    
    public function generate($route_name, array $params = array()) {
        try {
            if ( ! isset($this->named_routes[$route_name]))
                throw new Exception("No route with the name $route_name has been found.");
            
            $route = $this->named_routes[$route_name];
            $url = $route->getUrl();
            
            if ($params && preg_match_all("/:(\w+)/", $url, $param_keys)) {
                $param_keys = $param_keys[1];
                foreach ($param_keys as $i => $key) {
                    if (isset($params[$key]))
                        $url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
                }
            }
            
            $url = ltrim($url, DS);
        }
        catch (Exception $e)
        {
            $url = '';
        }
        
        return $this->with_path($url);
    }
    
    public function generate_http($route_name, array $params = array()) {
        return $this->with_http($this->generate($route_name, $params));
    }
    
    private function with_http($url) {
        return $this->getRequest()->getBaseHttp().$url;
    }

    private function with_path($url) {
        return $this->getRequest()->getBasePath().$url;
    }
}