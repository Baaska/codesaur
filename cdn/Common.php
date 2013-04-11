<?php

function debug_echo($var, $withComma = false)
{
    if (!defined('DEVELOPMENT'))
        return;
    
    if (is_array($var))
    {
        if ($withComma)
        {
            $withCommaStr = implode(',', $var);
            echo $withCommaStr;
        }
        else
        {
            foreach($var as $key => $value)
            {
                echo ' '.$key.'=>'. $value.' ';
            }
        }
    }
    else
        echo $var;
}