<?php
// Michael Rajotte - 2016 June
// upload.php
// File and image upload processing.
// --> This file could be DELETED as it is no longer USED!!

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");

$fluid = new Fluid ();

echo base64_encode(json_encode($fluid->php_process_file_uploads($_FILES)));

?>
