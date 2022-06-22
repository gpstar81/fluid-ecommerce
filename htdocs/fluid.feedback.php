<?php
// fluid.feedback.php
// Michael Rajotte - 2018 Janvier

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

function php_fluid_feedback_record($f_data = NULL) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		//$fluid->php_db_query("SELECT u_id, u_token FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . OAUTH_FLUID . "' AND u_email = '" . $fluid->php_escape_string(base64_decode($_REQUEST['email'])) .  "' AND u_token_reset = '" . $fluid->php_escape_string($_REQUEST['fluid_email_security_code']) . "'");
		/*
			f_reason <-> f-feedback-reason
			f_comment <-> f-feedback-comment
			f_exit <-> f-feedback-exit
			f_find <-> f-feedback-find
			f_likely <-> f-feedback-likely (int)
			f_rate <-> f-feedback-rate (int)
			f_extra <-> f-feedback-extra
			f_ip_address
			f_created (datetime) (auto)
		*/

		if(isset($_SERVER['REMOTE_ADDR']))
			$f_ip_address = $fluid->php_escape_string($_SERVER['REMOTE_ADDR']);
		else
			$f_ip_address = "";

		$f_feedback_array = Array();
		$f_feedback_array['f_ip_address'] = "'" . $f_ip_address . "'";
		$f_feedback_array['f_reason'] = "'" . $fluid->php_escape_string($f_data->f_reason) . "'";
		$f_feedback_array['f_comment'] = "'" . $fluid->php_escape_string($f_data->f_comment) . "'";
		$f_feedback_array['f_exit'] = "'" . $fluid->php_escape_string($f_data->f_exit) . "'";
		$f_feedback_array['f_find'] = "'" . $fluid->php_escape_string($f_data->f_find) . "'";
		$f_feedback_array['f_likely'] = "'" . $fluid->php_escape_string($f_data->f_likely) . "'";
		$f_feedback_array['f_rate'] = "'" . $fluid->php_escape_string($f_data->f_rate) . "'";
		$f_feedback_array['f_extra'] = "'" . $fluid->php_escape_string($f_data->f_extra) . "'";

		$f_columns = implode(", ", array_keys($f_feedback_array));
		$f_values  = implode(", ", array_values($f_feedback_array));
		$f_feedback_query = "INSERT INTO " . TABLE_FEEDBACK . " (" . $f_columns . ") VALUES (" . $f_values . ");";

		$fluid->php_db_query($f_feedback_query);

		$fluid->php_db_commit();

		$_SESSION['f_feedback'] = TRUE;

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_feedback_none() {
	try {
		$_SESSION['f_feedback'] = TRUE;

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// --> Not used.
/*
function php_fluid_feedback_check() {
	try {
		if(isset($_SERVER['REMOTE_ADDR']))
			$f_ip_address = $fluid->php_escape_string($_SERVER['REMOTE_ADDR']);
		else
			$f_ip_address = "";
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}
*/
?>
