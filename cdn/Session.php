<?php
namespace cdn;

class Session
{
    private $_ID;
            
    function __construct()
    {
        $this->start();
    }
    
    public function start()
    {
        $this->_ID = session_id();

        if (empty($this->_ID))
        {
            session_start();
            $this->_ID = session_id();
        }
    }
    
    public function getID()
    {
        return $this->_ID;
    }

    public function check($varname)
    {
        return (isset($_SESSION[$varname]));
    }
}