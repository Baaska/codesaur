<?php

function debugEcho($var, $with_comma = false) {
    if (!defined('DEVELOPMENT'))
        return;
    
    if (is_array($var)) {
        if ($with_comma) {
            $comma_inline = implode(',', $var);
            echo $comma_inline;
        } else {
            foreach($var as $key => $value) {
                echo ' ' . $key . '=>' . $value. ' ';
            }
        }
    }
    else
        echo $var;
}