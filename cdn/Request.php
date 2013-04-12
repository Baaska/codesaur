<?php
namespace cdn;

class Request
{
    private $_host;
    private $_url = '';
    private $_url_clean = '';
    private $_method = 'GET';
    private $_script = '';
    private $_base_http = '';
    private $_base_path = '';
    private $_secure_http = false;
    
    function __construct()
    {
        $this->initFromGlobals();
    }
    
    public function getHost()
    {
        return $this->_host;
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
    
    public function getCleanUrl()
    {
        return $this->_url_clean;
    }
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function getScript()
    {
        return $this->_script;
    }
    
    public function getBaseHttp()
    {
        return $this->_base_http;
    }
    
    public function getBasePath()
    {
        return $this->_base_path;
    }
    
    public function isSecure()
    {
        return $this->_secure_http;
    }

    protected function initFromGlobals()
    {
        $this->_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $this->_script = $this->cleanDoubleSlash((isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : '');
                
        if (isset($_SERVER['REQUEST_URL']) && ! empty($_SERVER['REQUEST_URL'])) {
            $url = $_SERVER['REQUEST_URL'];
        } else {
            $url = $_SERVER['REQUEST_URI'];
        }        
        $this->_url = $this->cleanDoubleSlash($url);
        $this->_url_clean = $this->cleanUrl($this->getUrl());
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['_method']) && (strtoupper($_POST['_method']) == 'PUT'
                    || strtoupper($_POST['_method']) == 'DELETE')) {
                $this->_method = strtoupper($_POST['_method']);
            } else {
                $this->_method = 'POST';
            }
        }
        
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $this->_secure_http = true;
            $base_http = 'https';
        } else {
            $base_http = 'http';
        }
        $base_http .= '://'. $this->getHost();
        $this->_base_http = $base_http;
        
        $base_path = str_replace(basename($this->getScript()), '', $this->getScript());
        $this->_base_path =$this->cleanDoubleSlash($base_path);
    }

    protected function cleanUrl($url)
    {
        $url = str_replace(dirname($this->getScript()), '', $url);
        $query_string = strpos($url, '?');
        
        if ($query_string !== FALSE) {
            $url = substr($url, 0, $query_string);
        }
      
        if (substr($url, 1, strlen(basename($this->getScript()))) == basename($this->getScript())) {
            $url = substr($url, strlen(basename($this->getScript())) + 1);
        }
        
        $url = rtrim($url, DS ) . DS;
        return $url;
    }
    
    public function cleanDoubleSlash($in)
    {
        return preg_replace('/\/+/', '\\1/', $in);
    }
}