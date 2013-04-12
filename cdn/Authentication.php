<?php
namespace cdn;

class Authentication
{
    public $_login_set = NULL;
    public $session = NULL;
            
    function __construct($hsession = NULL, $session_login = DEF_AUTH_SESS_SET)
    {
        if ($hsession)
            $this->session = $hsession;
        else
            $this->session = new Session();
        
        $this->_login_set = $session_login;
    }
    
    public function check()
    {
        return $this->session->check($this->_login_set);
    }
}