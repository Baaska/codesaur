<?php
namespace cdn;

class Authentication {
    private $_session = NULL;
    private $_login = '';
            
    function __construct($session = NULL, $session_login = DEF_AUTH_SESS_SET) {
        $this->setSession($session);
        $this->setLogin($session_login);
    }
    
    public function check() {
        return $this->session->check($this->getLogin());
    }
    
    public function setSession($session) {
        if ($session) {
            $this->_session = $session;
        } else {
            $this->_session = new Session();
        }
    }
    
    public function getSession() {
        return $this->_session;
    }

    public function setLogin($new_set) {
        $this->_login = $new_set;
    }
    
    public function getLogin() {
        return $this->_login;
    }

}