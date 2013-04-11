<?php
namespace cdn;

class Request
{
    public $url_clean = '';
    public $url_dirty = '';
    public $url_base = '';
    
    function __construct()
    {
        $this->create_from_req();
        $this->url_base = $this->base_url();
    }
    
    public function create_from_req()
    {
        if (isset($_SERVER['REQUEST_URL']) && ! empty($_SERVER['REQUEST_URL']))
        {
            $url = $_SERVER['REQUEST_URL'];
        }
        else
        {
            $url = $_SERVER['REQUEST_URI'];
        }

        $this->url_dirty = $url;
        $this->url_clean = $this->get_clean_url($this->url_dirty);
    }

    public function base_url()
    {
        if ( ! isset($_SERVER['HTTP_HOST']))
            return 'http://localhost/';
        
        $base = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
        $base .= '://'. $_SERVER['HTTP_HOST'];
        $base .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        
        return $base;
    }
    
    protected function get_clean_url($url)
    {
        $url = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $url);
        $query_string = strpos( $url, '?' );
        
        if ($query_string !== FALSE)
        {
            $url = substr($url, 0, $query_string);
        }
      
        if (substr($url, 1, strlen(basename($_SERVER['SCRIPT_NAME']))) == basename($_SERVER['SCRIPT_NAME']))
        {
            $url = substr($url, strlen(basename($_SERVER['SCRIPT_NAME'])) + 1);
        }
        
        $url = rtrim( $url, '/' ) . '/';
        $url = preg_replace( '/\/+/', '/', $url );
        return $url;
    }
}