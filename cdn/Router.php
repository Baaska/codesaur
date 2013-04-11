<?php
namespace cdn;

class Route
{
    private $url;
    private $methods = array('GET', 'POST', 'PUT', 'DELETE');
    private $target;
    private $name;
    private $filters = array();
    private $params = array();
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setUrl($url)
    {
        $url = (string) $url;
        
        if (substr($url, -1) !== '/') $url .= '/';
        $this->url = $url;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
    }
    
    public function getMethods()
    {
        return $this->methods;
    }
    
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = (string) $name;
    }
    
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }
    
    public function getRegex()
    {
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->url);
    }
    
    private function substituteFilter($matches)
    {
        if (isset($matches[1]) && isset($this->filters[$matches[1]]))
        {
            return $this->filters[$matches[1]];
        }
        return "([\w-]+)";
    }
    
    public function getParameters()
    {
        return $this->parameters;
    }
    
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}

class Router
{
    public $request;
    
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
    
    private $routes = array();
    private $namedRoutes = array();
    private $basePath = '';
    
    public function setBasePath($basePath)
    {
        $this->basePath = (string) $basePath;
    }
    
    public function map($routeUrl, $target = '', array $args = array())
    {
        $route = new Route();
        
        $route->setUrl($this->basePath . $routeUrl);
        
        $route->setTarget($target);
        
        if (isset($args['methods']))
        {
            $methods = explode(',', $args['methods']);
            $route->setMethods($methods);
        }
        
        if (isset($args['filters']))
        {
            $route->setFilters($args['filters']);
        }
        
        if (isset($args['name']))
        {
            $route->setName($args['name']);
            if ( ! isset($this->namedRoutes[$route->getName()]))
            {
                $this->namedRoutes[$route->getName()] = $route;
            }
        }
        $this->routes[] = $route;
    }
    
    public function matchCurrentRequest()
    {
        $requestMethod = (isset($_POST['_method']) && ($_method = strtoupper($_POST['_method'])) && in_array($_method,array('PUT','DELETE'))) ? $_method : $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REQUEST_URI'];
        
        if(($pos = strpos($requestUrl, '?')) !== false)
        {
            $requestUrl =  substr($requestUrl, 0, $pos);
        }
        
        return $this->match($requestUrl, $requestMethod);
    }
    
    public function match($requestUrl, $requestMethod = 'GET')
   {
        foreach($this->routes as $route)
        {
            if ( ! in_array($requestMethod, $route->getMethods()))
                    continue;
            
            if ( ! preg_match("@^".$route->getRegex()."*$@i", $requestUrl, $matches))
                    continue;

            $params = array();

            if (preg_match_all("/:([\w-]+)/", $route->getUrl(), $argument_keys))
            {
                $argument_keys = $argument_keys[1];
                foreach ($argument_keys as $key => $name)
                {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }
            }
            
            $route->setParameters($params);
            return $route;
        }
        return false;
    }
    
    public function generate($routeName, array $params = array())
    {
        if ( ! isset($this->namedRoutes[$routeName]))
            throw new Exception("No route with the name $routeName has been found.");
        
        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();
        
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
