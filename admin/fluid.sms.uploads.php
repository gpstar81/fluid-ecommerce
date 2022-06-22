<?php
// Michael Rajotte - 2018 Aout
// fluid.sms.uploads.php
// File and image upload processing.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");

$fluid = new Fluid ();

$filename = NULL;
foreach($_FILES as $key => $tmp) {
	$filename = "MMS-" . rand(0,900000);
	move_uploaded_file($tmp['tmp_name'], FOLDER_ROOT_ADMIN . "mms/" . $filename);
	break;
}

echo json_encode(Array("response" => $filename));
?>
