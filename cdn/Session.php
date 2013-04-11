<?php
namespace cdn;

class Session
{
    public $_id;
            
    function __construct()
    {
        $this->start();
    }
    
    public function start()
    {
        $this->_id = session_id();

        if (empty($this->_id))
        {
            session_start();
            $this->_id = session_id();
        }
    }

    public function check($varname)
    {
        return (isset($_SESSION[$varname]));
    }
}