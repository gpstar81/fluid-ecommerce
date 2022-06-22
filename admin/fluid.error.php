<?php
// fluid.error.php
// Michael Rajotte - 2017 Aout
// Loads ajax php code.

require_once (__DIR__ . "/fluid.admin.login.php");

if(empty($_SESSION['fluid_admin']))
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);

if(isset($_COOKIE[FLUID_COOKIE_ADMIN]) && isset($_SESSION['u_id_admin']) == FALSE)
	php_fluid_login_admin_cookie();

function php_fluid_error($err) {
	return json_encode(array("error" => count($err), "error_message" => base64_encode($err)));
}

?>
