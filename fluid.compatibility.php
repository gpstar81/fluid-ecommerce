<?php
// Michael Rajotte - 2018 Mars
// fluid.compatibility.php
// Fluid required file. For compatability with older versions of php.

// Doesn't exist in PHP older than v.5.6
if(!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        }
        else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--) {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}

?>
