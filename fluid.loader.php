<?php	
// fluid.loader.php
// Michael Rajotte - 2016 Aout
// Fluid loader for the front end website.

$f_session_valid = TRUE;

if(isset($_SESSION['fluid_last_activity']) && (time() - $_SESSION['fluid_last_activity'] > 600)) {
	// Only need to reset the session if it was a ajax request. Otherwise it will start automatically on it's own.
	if(isset($_REQUEST['load_func'])) {
		// last request was more than 10 minutes ago
		session_unset();     // unset $_SESSION variable for the run-time 
		session_destroy();   // destroy session data in storage
    
		$f_session_valid = FALSE;
	}
}

if(!isset($_SESSION))
	session_start();
	
$_SESSION['fluid_last_activity'] = time(); // update last activity time stamp
$_SESSION['fluid_uri'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/";
$_SESSION['fluid_ip'] = $_SERVER['REMOTE_ADDR'];

require_once ("fluid.define.html.php");	

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load_func']))
	if($f_session_valid == FALSE)
		echo php_fluid_error("Your web session has timed out.", TRUE);
	else if(function_exists($_REQUEST['fluid_function'])) {
		if(isset($_REQUEST['data']))
			echo call_user_func_array($_REQUEST['fluid_function'], Array("data" => json_decode(utf8_encode(base64_decode($_REQUEST['data'])))));
		else
			echo call_user_func_array($_REQUEST['fluid_function'], Array("data" => NULL));
	}
	else
		echo php_fluid_error("Function not found : " . $_REQUEST['fluid_function'] . "();");
else if(function_exists("php_main_" . str_replace('.', '_', str_replace('.php', '', basename($_SERVER["SCRIPT_FILENAME"], '.php')))))
		if(isset($_REQUEST['data']))
			call_user_func_array("php_main_" . str_replace('.', '_', str_replace('.php', '', basename($_SERVER["SCRIPT_FILENAME"], '.php'))), Array("data" => json_decode(utf8_encode(base64_decode($_REQUEST['data'])))));
		else
			call_user_func_array("php_main_" . str_replace('.', '_', str_replace('.php', '', basename($_SERVER["SCRIPT_FILENAME"], '.php'))), Array("data" => NULL));

function php_fluid_error($err, $redirect = FALSE, $redirect_url = NULL) {
	if($redirect == TRUE) {
		if(empty($redirect_url))
			$redirect_url = WWW_SITE;

		$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode($redirect_url) . "\"});'>Ok</button></div>";

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("error-modal-footer"), "html" => base64_encode($redirect_html))));
					
		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-error-msg-div"), "html" => base64_encode($err->getMessage()))));
					
		$execute_functions[]['function'] = "js_modal_show_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-error-modal"))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	else
		return json_encode(array("error" => count($err), "error_message" => base64_encode($err->getMessage())));
}

function php_fluid_error_cart($err, $redirect = FALSE, $redirect_url = NULL, $refresh_page = TRUE) {
	if($redirect == TRUE) {
		if(empty($redirect_url))
			$redirect_url = WWW_SITE;

		if($refresh_page == TRUE)
			$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal' onClick='js_redirect_url({url:\"" . base64_encode($redirect_url) . "\"});'>Ok</button></div>";
		else
			$redirect_html = "<div style='float:right;'><button type='button' class='btn btn-primary' data-dismiss='modal'>Ok</button></div>";

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("error-modal-footer"), "html" => base64_encode($redirect_html))));
					
		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-error-msg-div"), "html" => base64_encode($err))));
					
		$execute_functions[]['function'] = "js_modal_show_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-error-modal"))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	else
		return json_encode(array("error" => count($err), "error_message" => base64_encode($err)));
}	

