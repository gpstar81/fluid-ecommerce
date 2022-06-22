<?php
// Michael Rajotte - 2017 Mars
// upload.csv.php
// File and image upload processing.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");

$fluid = new Fluid ();
//$fluid->php_debug(base64_decode($_REQUEST['f_delimiter_import']), TRUE);

$f_response = NULL;
foreach($_FILES as $key => $tmp) {
	//$fluid->php_debug($tmp, TRUE);
	$f_response = $fluid->php_process_csv_upload($tmp, $_REQUEST['f_delimiter_import']);
	break;
}

//echo json_encode(Array("fupload" => "success test");
echo json_encode(Array("response" => $f_response));
//echo json_encode(Array("response" => json_encode($_FILES)));

?>
