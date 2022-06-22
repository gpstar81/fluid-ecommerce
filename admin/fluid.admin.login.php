<?php
// fluid.login.admin.php
// Michael Rajotte - 2017 Decembre
// Loads ajax php code.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

if(empty($_SESSION['fluid_admin']))
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['adminaccount']))
	if(function_exists($_REQUEST['function']))
		echo call_user_func($_REQUEST['function']);
	else
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");


function php_fluid_login_admin($data = NULL) {
	try {
		$fluid_login = new Fluid();

		// Fluid login.
		$oauth_provider = OAUTH_FLUID;

		$fluid_login->php_db_begin();

		$fluid_login->php_db_query("SELECT * FROM " . TABLE_USERS_ADMIN . " WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_email = '" . $fluid_login->php_escape_string($data['f_email']) .  "' AND AES_DECRYPT(u_password, '" . HASH_KEY . "') = '" . $fluid_login->php_escape_string($data['f_password']) . "'");

		if(isset($fluid_login->db_array)) {
			$_SESSION['u_id_admin'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.
			$_SESSION['u_oauth_id_admin'] = $fluid_login->db_array[0]['u_oauth_uid'];
			$_SESSION['u_oauth_provider_admin'] = $oauth_provider;
			$_SESSION['u_email_admin'] = $fluid_login->db_array[0]['u_email'];
			$_SESSION['u_first_name_admin'] = $fluid_login->db_array[0]['u_first_name'];
			$_SESSION['u_last_name_admin'] = $fluid_login->db_array[0]['u_last_name'];
			$_SESSION['u_access_admin'] = $fluid_login->db_array[0]['u_access'];

			// Create a cookie to keep the user logged in.
			if(function_exists('random_bytes')) {
				// PHP > 7.0
				$fluid_token = bin2hex(random_bytes(32));
			}
			else {
				// PHP < 7.2
				$fluid_token = bin2hex(mcrypt_create_iv(128, MCRYPT_DEV_URANDOM));
			}

			$cookie = $fluid_login->db_array[0]['u_oauth_uid'] . ':' . $fluid_token;

			$mac = hash_hmac('sha256', $cookie, HASH_KEY);
			$cookie .= ':' . $mac;

			$fluid_login->php_db_query("UPDATE " . TABLE_USERS_ADMIN . " SET u_token = '" . $fluid_login->php_escape_string($fluid_token) . "' WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_email = '" . $fluid_login->php_escape_string($data['f_email']) .  "' AND AES_DECRYPT(u_password, '" . HASH_KEY . "') = '" . $fluid_login->php_escape_string($data['f_password']) . "'");

			// --> Set expiry on the cookie.
			setcookie(FLUID_COOKIE_ADMIN, $cookie, time() + (86400 * 365), "/"); // --> Good for 1 year the cookie.
			$_SESSION['fluid_token_admin'] = $fluid_token;

			$fluid_login->php_db_commit();

			return TRUE;
		}
		else
			return FALSE;
	}
	catch (Exception $err) {
		return FALSE;
	}
}

// Checks for the Fluid cookie. Check and verify the token in the cookie then log the user in automatically.
function php_fluid_login_admin_cookie() {
    $cookie = isset($_COOKIE[FLUID_COOKIE_ADMIN]) ? $_COOKIE[FLUID_COOKIE_ADMIN] : '';

    if($cookie) {
        list ($u_oauth_id, $token, $mac) = explode(':', $cookie);

        if(!hash_equals(hash_hmac('sha256', $u_oauth_id . ':' . $token, HASH_KEY), $mac))
            return TRUE;

        $fluid_login = new Fluid ();

		$fluid_login->php_db_begin();

		$fluid_login->php_db_query("SELECT * FROM " . TABLE_USERS_ADMIN . " WHERE u_oauth_provider = '" . $fluid_login->php_escape_string(OAUTH_FLUID) . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($u_oauth_id) .  "'");

		$fluid_login->php_db_commit();

		if(isset($fluid_login->db_array)) {
			if(hash_equals($fluid_login->db_array[0]['u_token'], $token)) {
				$_SESSION['u_id_admin'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.
				$_SESSION['u_oauth_id_admin'] = $fluid_login->db_array[0]['u_oauth_uid'];
				$_SESSION['u_oauth_provider_admin'] = OAUTH_FLUID;
				$_SESSION['u_email_admin'] = $fluid_login->db_array[0]['u_email'];
				$_SESSION['u_first_name_admin'] = $fluid_login->db_array[0]['u_first_name'];
				$_SESSION['u_last_name_admin'] = $fluid_login->db_array[0]['u_last_name'];
				$_SESSION['fluid_token_admin'] = $fluid_login->db_array[0]['u_token'];
				$_SESSION['u_access_admin'] = $fluid_login->db_array[0]['u_access'];

				return TRUE;
			}
			else
				return FALSE;
		}
		else
			return FALSE;
    }
    return FALSE;
}

function php_fluid_admin_logout() {
	try {
		unset($_SESSION);

		// Destroy the cookie if the user logs out.
		if(isset($_COOKIE[FLUID_COOKIE_ADMIN]))
			setcookie(FLUID_COOKIE_ADMIN, '', time()-7000000, '/');

		session_destroy();

		$redirect_url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/";

		$execute_functions[]['function'] = "js_redirect_url";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "url" => base64_encode($redirect_url), "error" => 0, "error_message" => base64_encode("no error")));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}
?>
