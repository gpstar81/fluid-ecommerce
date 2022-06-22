<?php
// fluid.account.php
// Michael Rajotte - 2016 Octobre

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/../fluid.loader.php");

// Must include the name space in each php file that uses the facebook sdk for USE namespaces to work.
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;

// Fetches the address book html while in the account menu.
function php_address_book_account($f_data = NULL) {
	$html = php_html_account_address_book(FALSE, NULL, $f_data)['html'];
	$execute_functions[]['function'] = "js_html_insert";

	return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "bool_found_items" => TRUE, "div_id" => base64_encode("fluid-account-content"), "html" => base64_encode($html), "item_count" => base64_encode(0), "item_page_previous" => base64_encode(0), "item_page" => base64_encode(0), "item_page_next" => base64_encode(0), "total_items" => base64_encode(0), "item_start" => base64_encode(0)));
}

// Fetches the address book html while in the checkout page.
function php_address_book_checkout($data) {
	try {
		if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
			throw new Exception("session checkout mismatch error");

		$f_data = php_html_account_address_book(TRUE, $data);

		// Addresses found.
		if($f_data['f_addresses_found'] > 0) {
			//$f_back_button = "<button type=\"button\" class=\"btn btn-warning\" onClick='js_fluid_checkout_address();'><span class='glyphicon glyphicon-arrow-left' aria-hidden=\"true\"></span> Back</button>";
			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-back-button"), "html" => base64_encode(""))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($f_data['html']))));

			$execute_functions[]['function'] = "js_html_style_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => base64_encode("fluid-modal-trigger-button"))));

			$execute_functions[]['function'] = "js_html_style_display";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-close-button"), "div_style" => base64_encode("inline-block"))));

			//$execute_functions[]['function'] = "js_html_insert";
			//$execute_functions[]['function'] = "js_modal_show_data";

			//return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($f_data['html']), "modal_id" => base64_encode("#fluid-main-modal")));
		}
		else {
			$html = HTML_ADDRESS_CREATE_FORM;

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($html))));

			$execute_functions[]['function'] = "js_google_maps_init_auto_complete";

			$f_back_button = "<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\"><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Cancel</button>";

			$f_trigger_button = "<button type=\"button\" class=\"btn btn-info\" onClick='$(\"#f-address-submit-button\").click();'><span class='glyphicon glyphicon-check' aria-hidden=\"true\"></span> Create Address</button>";

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-back-button"), "html" => base64_encode($f_back_button))));

			$execute_functions[]['function'] = "js_html_style_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => base64_encode("fluid-modal-close-button"))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-trigger-button"), "html" => base64_encode($f_trigger_button))));

			$execute_functions[]['function'] = "js_html_style_display";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-trigger-button"), "div_style" => base64_encode("inline-block"))));
		}

		if(FLUID_SHIP_NON_BILLING == FALSE)
			$f_ship_message_header = "Shipping & Billing Address";
		else
			$f_ship_message_header = "Shipping Address";

		$execute_functions[]['function'] = "js_html_insert";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-header-div"), "html" => base64_encode("<i class=\"fa fa-home\"></i> " . $f_ship_message_header))));

		$execute_functions[]['function'] = "js_modal_show_data";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-main-modal"))));

		if($f_data['f_addresses_found'] == 0)
			$execute_functions[]['function'] = "js_google_maps_init_auto_complete";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_facebook_button() {
	// Facebook app info:
	// https://developers.facebook.com/apps/1655536804738049/settings/

	// init app with app id and secret
	FacebookSession::setDefaultApplication(FACEBOOK_CLIENT_LOGIN_ID, FACEBOOK_CLIENT_SECRET);

	// login helper with redirect_uri
	$helper = new FacebookRedirectLoginHelper(FACEBOOK_CLIENT_LOGIN_REDIRECT);

	if(isset($_SESSION['u_id'])) {
		$data['html'] = "<a href=\"logout.php?logout\" class=\"btn btn-primary\">Logout</a>";
	}
	else {
		$permissions = array(
			'scope' => 'email'
		);

		$data['script'] = "<script>
				var fluid_facebook_window;
				var fluid_facebook_checkout = 0;

				function js_fluid_account_login_refresh() {
					if(fluid_facebook_checkout == 0)
						location.reload();
					else
						js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});
				}

				function js_fluid_facebook_login() {
					fluid_facebook_window = window.open('" . $helper->getLoginUrl() . "', '_blank', 'width=600, height=450, top=200, left=250');

					var interval_facebook = window.setInterval(function() {
						try {
							if (fluid_facebook_window == null || fluid_facebook_window.closed) {
								window.clearInterval(interval_facebook);
								js_fluid_account_login_refresh();
							}
						}
						catch (e) {
						}
					}, 500);
				}

			</script>";
		$data['html'] = "<a onClick=\"js_fluid_facebook_login();\" class=\"btn btn-fb\" style='margin: 2px;' data-onsuccess=\"onSignIn\"><i class=\"fa fa-facebook\"></i> Facebook</a>";
	}

	return $data;
}

function php_fluid_account_update($data = NULL) {
	try {
		// Make sure session checking is put into the account page and shopping cart page to prevent exploits.
		if(isset($_SESSION['u_id']) == FALSE)
			throw new Exception("Your session has timed out.");
		else {
			$fluid = new Fluid ();

			$fluid->php_db_begin();

			$fluid->php_db_query("UPDATE " . TABLE_USERS . " SET u_first_name = '" . $fluid->php_escape_string($data->u_first_name) . "', u_last_name = '" . $fluid->php_escape_string($data->u_last_name) . "', u_modified = '" . date("Y-m-d H:i:s") . "' WHERE u_oauth_provider = '" . $fluid->php_escape_string(OAUTH_FLUID) . "' AND u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'");

			$fluid->php_db_commit();

			if(count($fluid->db_error) > 0)
				throw new Exception("There was a error updating your account. Please try again.");
			else {
				$_SESSION['u_first_name'] = $data->u_first_name;
				$_SESSION['u_last_name'] = $data->u_last_name;

				if(strlen($data->u_first_name . " " . $data->u_last_name) > 25)
					$html = utf8_decode(substr($data->u_first_name . " " . $data->u_last_name, 0, 25) . "...");
				else
					$html = utf8_decode($data->u_first_name . " " . $data->u_last_name);

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-profile-user-name"), "html" => base64_encode($html))));

				$execute_functions[]['function'] = "js_html_insert";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid_header_hello"), "html" => base64_encode("Hello " . utf8_decode(substr($data->u_first_name, 0, 15))))));

				$execute_functions[]['function'] = "js_html_style_display";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-settings-success-notification"), "div_style" => base64_encode("block"))));

				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
			}
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_account_update_password($data = NULL) {
	try {
		// Make sure session checking is put into the account page and shopping cart page to prevent exploits.
		if(isset($_SESSION['u_id']) == FALSE)
			throw new Exception("Your session has timed out.");
		else {
			$fluid = new Fluid ();

			$fluid->php_db_begin();

			$fluid->php_db_query("UPDATE " . TABLE_USERS . " SET u_password = AES_ENCRYPT('" . $fluid->php_escape_string($data->u_password) . "','" . HASH_KEY . "'), u_modified = '" . date("Y-m-d H:i:s") . "' WHERE u_oauth_provider = '" . $fluid->php_escape_string(OAUTH_FLUID) . "' AND u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'");

			$fluid->php_db_commit();

			if(count($fluid->db_error) > 0)
				throw new Exception("There was a error updating your password. Please try again.");
			else {
				$execute_functions[]['function'] = "js_html_style_display";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-password-success-notification"), "div_style" => base64_encode("block"))));

				$execute_functions[]['function'] = "js_fluid_password_validator_wipe";

				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
			}
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Check for temporary addresses for the cart ship estimator. (Not for use in the checkout).
function php_fluid_address_ship() {
	try {
		// 1. Check for user id, fetch addresses from address book, then store them in the $_SESSION['f_address_tmp']
		// 2. Check for $_SESSION['f_address_tmp'] then build mini cards.
		// 3. Return data and let anything know if anything was found.

		// 4. *** Need to build a feature to rebuild the $_SESSION['f_address_tmp_ship'] from the address book when a user adds or deletes a address from the address book. ***

		// A one time load if any addresses are in the database for a user and loaded.
		if(isset($_SESSION['u_id']) && empty($_SESSION['f_address_tmp_ship'])) {
			$fluid = new Fluid ();

			$fluid->php_db_begin();

			$fluid->php_db_query("SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "' ORDER BY a_id ASC");

			$fluid->php_db_commit();

			if(isset($fluid->db_array))
				$fa_data = $fluid->db_array;

			if(isset($fa_data)) {
				foreach($fa_data as $key => $data) {

					$data['a_country_iso3116'] = $fluid->php_country_name_to_ISO3166($data['a_country'], 'US');

					if($data['a_country_iso3116'] == "CA")
						 $data['a_province_code'] = $fluid->php_fluid_provincial_code($data['a_postalcode']);
					else if($data['a_country_iso3116'] == "US")
						$data['a_province_code'] = $fluid->php_fluid_state_abbr($data['a_province']);
					else
						$data['a_province_code'] = NULL;

					$_SESSION['f_address_tmp_ship'][] = $data;
				}
			}
		}

		$html = NULL;
		$i = 0;

		if(isset($_SESSION['f_address_tmp_ship'])) {
			$html .= "<div>";

			// Allow up to the creation of 3 times in the temporary session variable of temp addresses.
			if(count($_SESSION['f_address_tmp_ship']) >= VAR_MAX_ADDRESSES)
				$html .= "<div style='text-align: center; color:#717171; text-decoration: line-through;' onmouseover=\"JavaScript:this.style.cursor='not-allowed';\"><span class='glyphicon glyphicon-plus'></span><div style='padding-bottom: 10px;'>Click to add a address</div></div>";
			else
				$html .= "<div style='text-align: center;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_fluid_ship_editor(); js_google_maps_init_auto_complete_ship();'><span class='glyphicon glyphicon-plus'></span><div style='padding-bottom: 10px;'>Click to add a address</div></div>";

			$html .= "<div class='fluid-cart-scroll'>";

				// Build mini cards.
				foreach($_SESSION['f_address_tmp_ship'] as $key => $data) {
					$style = NULL;
					$java = NULL;
					$class = NULL;

					if(isset($_SESSION['f_address_tmp_ship_select'])) {
						if($_SESSION['f_address_tmp_ship_select'] == $key)
							$style = " background-color: rgba(45,255,93,0.5);";
					}

					$java = " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_fluid_address_ship_select(\"" . base64_encode($key) . "\");'";
					$class = " fluid-div-highlight";

					$html .= "<div class='well fluid-box-shadow-small-well" . $class . "' style='display: table; width: 100%;" . $style . "'" . $java . ">";

						$html .= "<div style='display: table-cell; padding: 0px; margin: 0px; vertical-align: middle;'>";
							$html .= "<div>";
								if(isset($data['a_number']))
									if(strlen($data['a_number']) > 0)
										$html .= utf8_decode($data['a_number'] .  " - ");
								$html .= utf8_decode($data['a_street']);
							$html .= "</div>";

							$html .= "<div>" . utf8_decode($data['a_city'] . " " . $data['a_province']) . "</div>";
							$html .= "<div>" . utf8_decode($data['a_country'] . " " . $data['a_postalcode']) . "</div>";
						$html .= "</div>";


						$html .= "<div class='pull-right' style='display table-row'>";
							$html .= "<div style='display: table-cell; text-align: center;'>";

								if(isset($_SESSION['f_address_tmp_ship_select'])) {
									if($_SESSION['f_address_tmp_ship_select'] == $key) {
										$html .= "<div id='fluid-address-ship-" . $key . "' style='font-style: italic;'><i class=\"fa fa-check\" aria-hidden='true'></i></div>";
									}
									else
										$html .= "<div id='fluid-address-ship-" . $key . "' style='font-style: italic;'><span class='glyphicon glyphicon-hand-up' aria-hidden='true'></span></div>";
								}
								else
									$html .= "<div id='fluid-address-ship-" . $key . "' style='font-style: italic;'><span class='glyphicon glyphicon-hand-up' aria-hidden='true'></span></div>";

							$html .= "</div>";
						$html .= "</div>";

					$html .= "</div>";

					$i++;
				}

			$html .= "</div>"; // fluid-cart-scroll
		$html .= "</div>";

		$html .= "
		<div style='table-cell; border-bottom: 0px; text-align:center; padding-top: 10px;'>
			<button type='button' class='btn btn-danger pull-left' aria-haspopup='true' aria-expanded='false' onClick='js_fluid_cart_editor_cancel(true);';><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> Cancel</button>
		</div>";
		}

		return Array("html" => $html, "total_addresses" => $i);
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Creates temporary addresses in the $_SESSION variable for all users, to get shipping quotes in the cart box. (Not for use in the checkout).
function php_fluid_address_create_ship($data) {
	try {
		if(isset($data)) {
			if(isset($_SESSION['f_address_tmp_ship'])) {
				if(count($_SESSION['f_address_tmp_ship']) >= VAR_MAX_ADDRESSES) {
					throw new Exception("Error: There are too many temporary addresses.");
				}
			}

			$data = (array)$data;

			// utf8 encode data for special characters and accents.
			foreach($data as $key => $t_data)
				$data[$key] = $t_data;

			if(isset($data['a_country'])) {
				$fluid = new Fluid ();

				$data['a_name'] = "";
				$data['a_phonenumber'] = "";

				$data['a_country_iso3116'] = $fluid->php_country_name_to_ISO3166($data['a_country'], 'US');

				if($data['a_country_iso3116'] == "CA")
					 $data['a_province_code'] = $fluid->php_fluid_provincial_code($data['a_postalcode']);
				else if($data['a_country_iso3116'] == "US")
					$data['a_province_code'] = $fluid->php_fluid_state_abbr($data['a_province']);
				else
					$data['a_province_code'] = NULL;

				$_SESSION['f_address_tmp_ship'][] = $data;

				require_once(FLUID_CART);

				$f_preload_data = php_html_cart(NULL, NULL, NULL, NULL, NULL, TRUE);

				$f_cart_html = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($f_preload_data)->js_execute_functions))[0]->data))->html);
				$f_cart_num_items = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($f_preload_data)->js_execute_functions))[1]->data))->html);
				$f_cart_html_editor = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($f_preload_data)->js_execute_functions))[2]->data))->html);
				$f_cart_html_ship = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($f_preload_data)->js_execute_functions))[6]->data))->html);
				$f_cart_html_ship_select = base64_decode(json_decode(base64_decode(json_decode(base64_decode(json_decode($f_preload_data)->js_execute_functions))[7]->data))->html);
				$execute_functions[]['function'] = "js_fluid_cart_reset";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("html" => base64_encode($f_cart_html), "html_editor" => base64_encode($f_cart_html_editor), "html_ship" => base64_encode($f_cart_html_ship), "html_ship_select" => base64_encode($f_cart_html_ship_select), "num_items" => base64_encode($f_cart_num_items))));

				$execute_functions[]['function'] = "js_fluid_ship_select";

				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
			}
			else
				throw new Exception("Invalid address data.");
		}
		else
			throw new Exception("Invalid address data.");

	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Creates a address into the database for users that are logged in, or for guest users, stores in a $_SESSION variable during the checkout process.
function php_fluid_address_create($data) {
	try {
		/*
			1. Compare $data->a_email and $data->a_emailagain to see if same. If different, return a error.
		*/

		if($data->tmp == "checkout")
			if(empty($_SESSION['f_checkout'][$data->f_checkout_id]))
				throw new Exception("session checkout mismatch error");

		$fluid = new Fluid();

		// Checkout mode and user is not logged in, so lets store the address into the $_SESSION variable.
		if($data->tmp == "checkout" && empty($_SESSION['u_id'])) {
			$_SESSION['f_checkout'][$data->f_checkout_id]['a_id'] = NULL;
			/*
			[f_address] => Array
				(
					[a_id] => 47
					[a_u_id] => 1
					[a_email] => socialmedia@leoscamera.com
					[a_name] => Michael Rajotte
					[a_number] => 1901
					[a_street] => 811 Helmcken Street
					[a_city] => Vancouver
					[a_province] => British Columbia
					[a_postalcode] => V6Z 1B1
					[a_country] => Canada
					[a_phonenumber] => 6045555555
					[a_default] => 1
					[a_country_iso3116] => CA
					[a_province_code] => BC
				)
            */
			if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_address_list']))
				$a_count = count($_SESSION['f_checkout'][$data->f_checkout_id]['f_address_list']);
			else
				$a_count = 0;

			if($a_count < VAR_MAX_ADDRESSES) {
				if(isset($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']))
					unset($_SESSION['f_checkout'][$data->f_checkout_id]['f_address']);

				if($data->a_country == "Canada") {
					$f_postal_code = preg_replace('/\s+/', '', $data->a_postalcode);
					$f_postal_code = str_replace('-', '', $f_postal_code);
					$f_postal_code = strtoupper($f_postal_code);
				}
				else if($data->a_country == "United States") {
					$f_postal_code = preg_replace('/\s+/', '', $data->a_postalcode);
					$f_postal_code = preg_replace('/\D/', '', $data->a_postalcode);
				}
				else {
					$f_postal_code = $data->a_postalcode;
				}

				$f_address = Array("a_id" => $a_count, "a_u_id" => NULL, "a_email" => $data->a_email, "a_name" => $data->a_name, "a_number" => $data->a_number, "a_street" => $data->a_street, "a_city" => $data->a_city, "a_province" => $data->a_province, "a_postalcode" => $f_postal_code, "a_country" => $data->a_country, "a_phonenumber" => $data->a_phonenumber, "a_default" => NULL);

				/*
				$f_address['a_country_iso3116'] = $fluid->php_country_name_to_ISO3166($data->a_country, 'US');

				if($f_address['a_country_iso3116'] == "CA")
					 $f_address['a_province_code'] = $fluid->php_fluid_provincial_code($data->a_postalcode);
				else if($f_address['a_country_iso3116'] == "US")
					$f_address['a_province_code'] = $fluid->php_fluid_state_abbr($data->a_province);
				else
					$f_address['a_province_code'] = NULL;

				$_SESSION['f_checkout'][$data->f_checkout_id]['f_address'] = $f_address;
				*/
				$_SESSION['f_checkout'][$data->f_checkout_id]['f_address_list'][$a_count] = $f_address; // Add to our lists of addresses.

				$execute_functions[]['function'] = "js_fluid_checkout_address_select"; // --> fluid.cart.php
				end($execute_functions);

				$fs_data = Array("a_id" => base64_encode($a_count), "a_add_to_cart" => NULL);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($fs_data));

				//$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode($a_count)));
			}
			else
				throw new Exception("Error: There are too many temporary addresses.");
		}
		else {
			if(empty($_SESSION['u_id'])) {
				throw new Exception("Error: There was a problem creating a address.");
			}
			else {
				$fluid->php_db_begin();

				$fluid->php_db_query("SELECT COUNT(a_id) AS total FROM " . TABLE_ADDRESS_BOOK . " WHERE a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id'])  . "'");

				if(isset($fluid->db_array))
					$total_items = $fluid->db_array[0]['total'];
				else
					$total_items = 0;

				if($total_items < VAR_MAX_ADDRESSES) {
					// Get a a_id for inserting.
					$fluid->php_db_query("SELECT a_id FROM " . TABLE_ADDRESS_BOOK . " ORDER BY a_id DESC LIMIT 1");

					if(isset($fluid->db_array))
						$a_id = $fluid->db_array[0]['a_id'] + 1;
					else
						$a_id = 1;

					if(isset($_SESSION['u_id']))
						$a_u_id = "a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'";
					else
						$a_u_id = "a_u_id = NULL"; // --> Not logged in, this can be accessed when in checkout via guest mode. It is ok to save these in the database under NULL user id. This is currently not used anymore as they are stored temporary into the $_SESSION variable instead ??

					if($data->a_country == "Canada") {
						$f_postal_code = preg_replace('/\s+/', '', $data->a_postalcode);
						$f_postal_code = str_replace('-', '', $f_postal_code);
						$f_postal_code = strtoupper($f_postal_code);
					}
					else if($data->a_country == "United States") {
						$f_postal_code = preg_replace('/\s+/', '', $data->a_postalcode);
						$f_postal_code = preg_replace('/\D/', '', $data->a_postalcode);
					}
					else {
						$f_postal_code = $data->a_postalcode;
					}

					$fluid->php_db_query("INSERT INTO " . TABLE_ADDRESS_BOOK . " SET a_id = '" . $fluid->php_escape_string($a_id) . "', " . $a_u_id . ", a_email = '" . $fluid->php_escape_string($data->a_email) . "', a_name = '" . $fluid->php_escape_string($data->a_name) . "', a_number = '" . $fluid->php_escape_string($data->a_number) . "', a_street = '" . $fluid->php_escape_string($data->a_street) . "', a_city = '" . $fluid->php_escape_string($data->a_city) . "', a_province = '" . $fluid->php_escape_string($data->a_province) . "', a_postalcode = '" . $fluid->php_escape_string($f_postal_code) . "', a_country = '" . $fluid->php_escape_string($data->a_country) . "', a_phonenumber = '" . $data->a_phonenumber . "', a_default = 0");

					$fluid->php_db_commit();

					// If we are in checkout, reload the checkout address information to the one we just selected.
					if($data->tmp == "checkout") { // --> fluid.cart.php
						$_SESSION['f_checkout'][$data->f_checkout_id]['a_id'] = base64_encode($a_id); // --> Extra security check, useful in guest checkout mode to prevent other addresses from loading.

						$execute_functions[]['function'] = "js_fluid_checkout_address_select"; // --> fluid.cart.php
						end($execute_functions);

						$fs_data = Array("a_id" => base64_encode($a_id), "a_add_to_cart" => NULL);
						$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($fs_data));
						//$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode($a_id)));
					}
					else {
						$fluid_account_address_book_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_address_book_account")));

						$execute_functions[]['function'] = "js_fluid_ajax";
						end($execute_functions);
						$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($fluid_account_address_book_link));
					}
				}
				else {
					$fluid->php_db_commit();
					throw new Exception("Error; There are too many temporary addresses.");
				}
			}
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Set or unset a address to default.
function php_fluid_address_default($data) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$fluid->php_db_query("UPDATE " . TABLE_ADDRESS_BOOK . " SET a_default = 0 WHERE a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'");

		if($data->a_default == 1)
			$fluid->php_db_query("UPDATE " . TABLE_ADDRESS_BOOK . " SET a_default = 1 WHERE a_id = '" . $fluid->php_escape_string($data->a_id) . "'");

		$fluid->php_db_commit();

		$fluid_account_address_book_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_address_book_account")));

		$execute_functions[]['function'] = "js_fluid_ajax";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($fluid_account_address_book_link));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_address_delete($data) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$fluid->php_db_query("DELETE FROM " . TABLE_ADDRESS_BOOK . " WHERE a_id = '" . $fluid->php_escape_string($data->a_id) . "'");

		$fluid->php_db_commit();

		$fluid_account_address_book_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_address_book_account")));

		$execute_functions[]['function'] = "js_fluid_ajax";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($fluid_account_address_book_link));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_login($data = NULL) {
	try {
		$fluid_login = new Fluid ();

		if(isset($data)) {
			if(isset($data->oauth_provider) == FALSE)
				$data->oauth_provider = NULL;
		}

		if(isset($_REQUEST['oauth_provider']))
			$oauth_provider_request = $_REQUEST['oauth_provider'];
		else
			$oauth_provider_request = NULL;

		if(isset($oauth_provider_request) || isset($data->oauth_provider)) {
			if($oauth_provider_request == OAUTH_FACEBOOK) {
				// Facebook login.

				FacebookSession::setDefaultApplication(FACEBOOK_CLIENT_LOGIN_ID, FACEBOOK_CLIENT_SECRET);
				$helper = new FacebookRedirectLoginHelper(FACEBOOK_CLIENT_LOGIN_REDIRECT);

				$session = $helper->getSessionFromRedirect();
				if(isset($session)) {
					// graph api request for user data
					$access_token = $session->getToken();
					$appsecret_proof = hash_hmac('sha256', $access_token, FACEBOOK_CLIENT_SECRET);
					$request = new FacebookRequest( $session, 'GET', '/me', array("appsecret_proof" =>  $appsecret_proof, "fields" => "id,name,email,first_name,last_name,website,locale,cover"));
					$response = $request->execute();

					// get response
					$graphObject = $response->getGraphObject();

					$oauth_provider = OAUTH_FACEBOOK;
					$fluid_login->php_db_begin();
					$fluid_login->php_db_query("SELECT * FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($graphObject->getProperty('id')) .  "'");

					if(isset($fluid_login->db_array)) {
						$_SESSION['u_id'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.

						$fluid_login->php_db_query("UPDATE " . TABLE_USERS . " SET u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "', u_oauth_uid = '" . $fluid_login->php_escape_string($graphObject->getProperty('id')) . "', u_first_name = '" . $fluid_login->php_escape_string($graphObject->getProperty('first_name')) . "', u_last_name = '" . $fluid_login->php_escape_string($graphObject->getProperty('last_name')) . "', u_email = '" . $fluid_login->php_escape_string($graphObject->getProperty('email')) . "', u_locale = '" . $fluid_login->php_escape_string($graphObject->getProperty('locale')) . "', u_modified = '" . date("Y-m-d H:i:s") . "' WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($graphObject->getProperty('id')) . "'");
					}
					else {

						$fluid_login->php_db_query("INSERT INTO " . TABLE_USERS . " SET u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "', u_oauth_uid = '" . $fluid_login->php_escape_string($graphObject->getProperty('id')) . "', u_first_name = '" . $fluid_login->php_escape_string($graphObject->getProperty('first_name')) . "', u_last_name = '" . $fluid_login->php_escape_string($graphObject->getProperty('last_name')) . "', u_email = '" . $fluid_login->php_escape_string($graphObject->getProperty('email')) . "', u_locale = '" . $fluid_login->php_escape_string($graphObject->getProperty('locale')) . "', u_created = '" . date("Y-m-d H:i:s") . "', u_modified = '" . date("Y-m-d H:i:s") . "'");

						$fluid_login->php_db_query("SELECT u_id FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $oauth_provider . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($graphObject->getProperty('id')) .  "'");

						$_SESSION['u_id'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.
					}

					$fluid_login->php_db_commit();

					$_SESSION['u_oauth_id'] = $graphObject->getProperty('id');
					$_SESSION['u_oauth_provider'] = $oauth_provider;
					$_SESSION['u_email'] = $graphObject->getProperty('email');
					$_SESSION['u_first_name'] = $graphObject->getProperty('first_name');
					$_SESSION['u_last_name'] = $graphObject->getProperty('last_name');
					$_SESSION['u_locale'] = $graphObject->getProperty('locale');
					$_SESSION['token_facebook'] = $session->getToken();
				}
				return "<script>window.close();</script>";
			}
			else if($oauth_provider_request == OAUTH_FLUID || $data->oauth_provider == OAUTH_FLUID) {
				// Fluid login.
				$oauth_provider = OAUTH_FLUID;

				$fluid_login->php_db_begin();

				$fluid_login->php_db_query("SELECT * FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_email = '" . $fluid_login->php_escape_string($data->u_email) .  "' AND AES_DECRYPT(u_password, '" . HASH_KEY . "') = '" . $fluid_login->php_escape_string($data->u_password) . "'");

				if(isset($fluid_login->db_array)) {
					$_SESSION['u_id'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.
					$_SESSION['u_oauth_id'] = $fluid_login->db_array[0]['u_oauth_uid'];
					$_SESSION['u_oauth_provider'] = $oauth_provider;
					$_SESSION['u_email'] = $fluid_login->db_array[0]['u_email'];
					$_SESSION['u_first_name'] = $fluid_login->db_array[0]['u_first_name'];
					$_SESSION['u_last_name'] = $fluid_login->db_array[0]['u_last_name'];

					// Create a cookie if the user wants to stay logged in.
					if(isset($data->u_remember_me)) {
						$fluid_token = bin2hex(mcrypt_create_iv(128, MCRYPT_DEV_URANDOM));

						$cookie = $fluid_login->db_array[0]['u_oauth_uid'] . ':' . $fluid_token;
						$mac = hash_hmac('sha256', $cookie, HASH_KEY);
						$cookie .= ':' . $mac;

						$fluid_login->php_db_query("UPDATE " . TABLE_USERS . " SET u_token = '" . $fluid_login->php_escape_string($fluid_token) . "' WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_email = '" . $fluid_login->php_escape_string($data->u_email) .  "' AND AES_DECRYPT(u_password, '" . HASH_KEY . "') = '" . $fluid_login->php_escape_string($data->u_password) . "'");

						setcookie(FLUID_COOKIE, $cookie, time() + (86400 * 30), "/"); // 30 days
						$_SESSION['fluid_token'] = $fluid_token;
					}

					$fluid_login->php_db_commit();

					$execute_functions[]['function'] = "js_redirect_url";

					if(isset($data->u_checkout)) {
						if($data->u_checkout == 1)
							$url = $_SESSION['fluid_uri'] . FLUID_CHECKOUT_REWRITE;
						else
							$url = $_SESSION['fluid_uri'];
					}
					else
						$url = $_SESSION['fluid_uri'];

					return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "url" => base64_encode($url), "error" => count($fluid_login->db_error), "error_message" => base64_encode($fluid_login->db_error)));
				}
				else {
					;
					$execute_functions[]['function'] = "js_html_insert";
					$execute_functions[]['function'] = "js_modal_show_data";

					$login_error_msg = "<span class='glyphicon glyphicon-warning-sign' aria-hidden='true'></span> Login failed. Email and or password incorrect. Please try again.";

					$fluid_login->php_db_commit();

					return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("modal-error-msg-div"), "modal_id" => base64_encode("#fluid-error-modal"), "html" => base64_encode($login_error_msg), "error" => count($fluid_login->db_error), "error_message" => base64_encode($fluid_login->db_error)));
				}
			}
		}
		else {
			// Google login.

			$gClient = new Google_Client();
			$gClient->setApplicationName('Login to Leos Camera Supply');
			$gClient->setClientId(GOOGLE_CLIENT_LOGIN_ID);
			$gClient->setClientSecret(GOOGLE_CLIENT_SECRET);
			$gClient->setRedirectUri(GOOGLE_WWW_FLUID_ACCOUNT);

			$google_oauthV2 = new Google_Oauth2Service($gClient);

			if(isset($_REQUEST['code'])){
				$gClient->authenticate();
				$_SESSION['token_google'] = $gClient->getAccessToken();
			}

			if(isset($_SESSION['token_google']))
				$gClient->setAccessToken($_SESSION['token_google']);

			if($gClient->getAccessToken()) {
				$userProfile = $google_oauthV2->userinfo->get();

				$oauth_provider = OAUTH_GOOGLE;
				$fluid_login->php_db_begin();
				$fluid_login->php_db_query("SELECT * FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($userProfile['id']) .  "'");

				if(isset($fluid_login->db_array)) {
					$_SESSION['u_id'] = $fluid_login->db_array[0]['u_id']; // Get the u_id to tell the site we are logged in.

					$fluid_login->php_db_query("UPDATE " . TABLE_USERS . " SET u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "', u_oauth_uid = '" . $fluid_login->php_escape_string($userProfile['id']) . "', u_first_name = '" . $fluid_login->php_escape_string($userProfile['given_name']) . "', u_last_name = '" . $fluid_login->php_escape_string($userProfile['family_name']) . "', u_email = '" . $fluid_login->php_escape_string($userProfile['email']) . "', u_locale = '" . $fluid_login->php_escape_string($userProfile['locale']) . "', u_picture = '" . $fluid_login->php_escape_string($userProfile['picture']) . "', u_modified = '" . date("Y-m-d H:i:s") . "' WHERE u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($userProfile['id']) . "'");
				}
				else {
					$fluid_login->php_db_query("INSERT INTO " . TABLE_USERS . " SET u_oauth_provider = '" . $fluid_login->php_escape_string($oauth_provider) . "', u_oauth_uid = '" . $fluid_login->php_escape_string($userProfile['id']) . "', u_first_name = '" . $fluid_login->php_escape_string($userProfile['given_name']) . "', u_last_name = '" . $fluid_login->php_escape_string($userProfile['family_name']) . "', u_email = '" . $fluid_login->php_escape_string($userProfile['email']) . "', u_locale = '" . $fluid_login->php_escape_string($userProfile['locale']) . "', u_picture = '" . $fluid_login->php_escape_string($userProfile['picture']) . "', u_created = '" . date("Y-m-d H:i:s") . "', u_modified = '" . date("Y-m-d H:i:s") . "'");

					$fluid_login->php_db_query("SELECT u_id FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $oauth_provider . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($userProfile['id']) .  "'");

					$_SESSION['u_id'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.
				}

				$fluid_login->php_db_commit();

				$_SESSION['u_oauth_id'] = $userProfile['id'];
				$_SESSION['u_oauth_provider'] = $oauth_provider;
				$_SESSION['u_email'] = $userProfile['email'];
				$_SESSION['u_first_name'] = $userProfile['given_name'];
				$_SESSION['u_last_name'] = $userProfile['family_name'];
				$_SESSION['u_picture'] = $userProfile['picture'];
				$_SESSION['u_locale'] = $userProfile['locale'];

				$u_img = $fluid_login->php_process_image_resize_remote($userProfile['picture'], "100", "100");
				$_SESSION['u_picture_width'] = $u_img['width'];
				$_SESSION['u_picture_height'] = $u_img['height'];
			}
			return "<script>window.close();</script>";
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Checks for the Fluid cookie. Check and verify the token in the cookie then log the user in automatically.
function php_fluid_login_cookie() {
    $cookie = isset($_COOKIE[FLUID_COOKIE]) ? $_COOKIE[FLUID_COOKIE] : '';

    if($cookie) {
        list ($u_oauth_id, $token, $mac) = explode(':', $cookie);

        if(!hash_equals(hash_hmac('sha256', $u_oauth_id . ':' . $token, HASH_KEY), $mac))
            return false;

        $fluid_login = new Fluid ();

		$fluid_login->php_db_begin();

		$fluid_login->php_db_query("SELECT * FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_login->php_escape_string(OAUTH_FLUID) . "' AND u_oauth_uid = '" . $fluid_login->php_escape_string($u_oauth_id) .  "'");

		$fluid_login->php_db_commit();

		if(isset($fluid_login->db_array)) {
			if(hash_equals($fluid_login->db_array[0]['u_token'], $token)) {
				$_SESSION['u_id'] = $fluid_login->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.
				$_SESSION['u_oauth_id'] = $fluid_login->db_array[0]['u_oauth_uid'];
				$_SESSION['u_oauth_provider'] = OAUTH_FLUID;
				$_SESSION['u_email'] = $fluid_login->db_array[0]['u_email'];
				$_SESSION['u_first_name'] = $fluid_login->db_array[0]['u_first_name'];
				$_SESSION['u_last_name'] = $fluid_login->db_array[0]['u_last_name'];
				$_SESSION['fluid_token'] = $fluid_login->db_array[0]['u_token'];

				return true;
			}
			else
				return false;
		}
		else
			return false;
    }
}

// http://bootsnipp.com/snippets/featured/user-profile-sidebar
function php_fluid_logout() {
	switch($_SESSION['u_oauth_provider']) {
		case OAUTH_FACEBOOK:
			$token_tmp = Array("oauth_provider" => $_SESSION['u_oauth_provider'], "token" => $_SESSION['token_facebook']);
			break;
		case OAUTH_GOOGLE:
			$token_tmp = Array("oauth_provider" => $_SESSION['u_oauth_provider'], "token" => $_SESSION['token_google']);
			break;
		case OAUTH_GOOGLE:
			$token_tmp = Array("oauth_provider" => $_SESSION['u_oauth_provider'], "token" => $_SESSION['token_fluid']);
			break;

		default:
			$token_tmp = NULL;
	}

	unset($_SESSION);

	// Destroy the cookie if the user logs out.
	if(isset($_COOKIE[FLUID_COOKIE]))
		setcookie(FLUID_COOKIE, '', time()-7000000, '/');

	session_destroy();

	session_start();

	if(isset($token_tmp))
		$_SESSION['token_' . $token_tmp['oauth_provider']] = $token_tmp['token'];

	$execute_functions[]['function'] = "js_redirect_url";

	if(isset($_SESSION['fluid_uri']))
		$redirect_url = $_SESSION['fluid_uri'];
	else
		$redirect_url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/";

	return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "url" => base64_encode($redirect_url), "error" => 0, "error_message" => base64_encode("no error")));
}

// Reset the password and log the user in.
function php_fluid_password_reset($data) {
	try {
		$data->oauth_provider = OAUTH_FLUID;

		//Check the token with the email to confirm, then updates the password and log the user in.
		$fluid_reset = new Fluid ();

		$fluid_reset->php_db_begin();

		$fluid_reset->php_db_query("SELECT * FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_reset->php_escape_string($data->oauth_provider) . "' AND u_email = '" .

		$fluid_reset->php_escape_string(base64_decode($data->u_email)) . "' AND u_token_reset = '" . $fluid_reset->php_escape_string($data->u_token_reset) . "'");

		// Token matches the email. Let's reset the password.
		if(isset($fluid_reset->db_array))
			$fluid_reset->php_db_query("UPDATE " . TABLE_USERS . " SET u_password = AES_ENCRYPT('" . $fluid_reset->php_escape_string($data->u_password) . "','" . HASH_KEY . "'), u_modified = '" . date("Y-m-d H:i:s") . "', u_token_reset = NULL WHERE u_id = '" . $fluid_reset->php_escape_string($fluid_reset->db_array[0]['u_id']) . "'");

		$fluid_reset->php_db_commit();

		$data->u_email = base64_decode($data->u_email);

		// Log the user after a password reset.
		return php_fluid_login($data);
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Fluid account registration. Does not include Facebook or Google login.
function php_fluid_register($data) {
	try {
		$fluid_register = new Fluid ();

		$oauth_provider = OAUTH_FLUID;
		$u_oauth_id = time() + rand(1,10000000);
		//$token_fluid = bin2hex(mcrypt_create_iv(128, MCRYPT_DEV_URANDOM));

		$fluid_register->php_db_begin();

		$fluid_register->php_db_query("SELECT u_id, u_email FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_register->php_escape_string($oauth_provider) . "' AND u_email = '" . $fluid_register->php_escape_string($data->u_email) .  "'");

		$f_safe = TRUE;
		if(isset($fluid_register->db_array)) {
			if($fluid_register->db_array[0]['u_email'] == $data->u_email) {
				$f_safe = FALSE;
			}
		}
		if($f_safe == TRUE) {
			$fluid_register->php_db_query("INSERT INTO " . TABLE_USERS . " SET u_oauth_provider = '" . $fluid_register->php_escape_string($oauth_provider) . "', u_oauth_uid = '" . $fluid_register->php_escape_string($u_oauth_id) . "', u_first_name = '" . $fluid_register->php_escape_string($data->u_first_name) . "', u_last_name = '" . $fluid_register->php_escape_string($data->u_last_name) . "', u_email = '" . $fluid_register->php_escape_string($data->u_email) . "', u_password = AES_ENCRYPT('" . $fluid_register->php_escape_string($data->u_password) . "','" . HASH_KEY . "'), u_created = '" . date("Y-m-d H:i:s") . "', u_modified = '" . date("Y-m-d H:i:s") . "'");

			$fluid_register->php_db_query("SELECT u_id FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_register->php_escape_string($oauth_provider) . "' AND u_oauth_uid = '" . $fluid_register->php_escape_string($u_oauth_id) .  "'");

			$_SESSION['u_id'] = $fluid_register->db_array[0]['u_id'];	// Get the u_id to tell the site we are logged in.

			$fluid_register->php_db_commit();

			$_SESSION['u_oauth_id'] = $u_oauth_id;
			$_SESSION['u_oauth_provider'] = $oauth_provider;
			$_SESSION['u_email'] = $data->u_email;
			$_SESSION['u_first_name'] = $data->u_first_name;
			$_SESSION['u_last_name'] = $data->u_last_name;

			// --> Send registration email.
			$f_email_message = "Thank you for registering on our website www.leoscamera.com<br><br>";
			$f_email_message .= "Take care to not lose your password. However, if this does happen you can regenerate them by clicking on \"Forget Password?\" in our login panel.<br><br><br>";

			$u_email_safe = $data->u_email;
			$f_email_data = addslashes(base64_encode(json_encode(Array("from" => "info@leoscamera.com", "to" => $u_email_safe, "subject" => "Welcome to Leo's Camera Supply", "message" => $f_email_message))));
			exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /dev/null &');

			// --> ****************** Special email signup coupon. ******************
			/*
			$f_email_special = base64_encode("<br>Hello,</br><br>Thank you for signing up at Leo's Camera Supply.</br><br>Here is a 50% off coupon towards a Passport Photo. Please print or show us this coupon in store to receive your passport photo discount.</br><br>");
			$f_emails[0]['u_email'] = $u_email_safe;
			$f_emails = json_decode(json_encode($f_emails));
			$f_subject_special = base64_encode("Leo's Camera 50% off a Passport Photo Coupon");
			$f_attach_files = array("Leos Passport Coupon.jpg", "Leos Passport Coupon.pdf");

			$f_email_data = addslashes(base64_encode(json_encode(Array("from" => "info@leoscamera.com", "multiple_emails" => $f_emails, "subject" => $f_subject_special, "message" => $f_email_special, "html_email" => TRUE, "attachments" => $f_attach_files))));
			exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /dev/null &');
			*/
			// --> ****************** End of special email signup coupon. ******************

			$execute_functions[]['function'] = "js_redirect_url";

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "url" => base64_encode($_SESSION['fluid_uri']), "error" => count($fluid_register->db_error), "error_message" => base64_encode($fluid_register->db_error)));
		}
		else {
			return json_encode(array("js_execute_array" => 0, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "url" => base64_encode($_SESSION['fluid_uri']), "error" => 10, "error_message" => base64_encode("Account already exists.")));
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Send back a password reset form.
function php_fluid_send_reset_password($data) {
	try {
		$modal = "
			<div class=\"panel-body\">
				<div class=\"text-center\">
					<h3><i class=\"fa fa-lock fa-4x\"></i></h3>
					<h2 class=\"text-center\">Password Reset</h2>
					<div class=\"panel-body\">
						<form id='fluid_form_password_reset' data-toggle='validator' role='form' onsubmit='js_fluid_password_reset();' accept-charset='UTF-8'>
							<div class=\"form-group has-feedback\">
								<label class=\"control-label\" for=\"fluid_password_reset\">Password</label>
								<input id=\"fluid_password_reset\" type=\"password\" maxlength=\"100\" class=\"form-control\" placeholder=\"Password\" data-minlength=\"6\" required>
								<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
								<div class=\"help-block with-errors\">Minimum of 6 characters</div>
							</div>
							<div class=\"form-group has-feedback\">
								<label class=\"control-label\" for=\"fluid_password_reset_confirm\">Password again</label>
								<input id=\"fluid_password_reset_confirm\" type=\"password\" maxlength=\"100\" class=\"form-control\" data-match=\"#fluid_password_reset\" data-match-error=\"Passwords do not match.\" required>
								<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
								<div class=\"help-block with-errors\"></div>
							</div>
							<div class=\"form-group\">
								<input type='hidden' id='u_email' value='" . htmlspecialchars($data->u_email) . "'>
								<input type='hidden' id='u_token_reset' value='" . htmlspecialchars($data->u_token_reset) . "'>
								<button id='fluid_forgot_password_reset_button' class=\"btn btn-info btn-block\" type=\"submit\">Reset Password</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		";

		$execute_functions[]['function'] = "js_html_insert";
		$execute_functions[]['function'] = "js_fluid_password_validator";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("fluid-reset-forget-div"), "html" => base64_encode($modal)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}

}

// Create a reset token code and send back a code token form.
function php_fluid_send_reset_token($data) {
	try {
		$fluid_register = new Fluid ();

		$fluid_token = rand(100000, 999999);

		$fluid_register->php_db_begin();

		$fluid_register->php_db_query("SELECT u_id, u_email FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . $fluid_register->php_escape_string(OAUTH_FLUID) . "' AND u_email = '" . $fluid_register->php_escape_string($data->u_email) .  "'");

		if($fluid_register->db_array[0]['u_email'] == $data->u_email) {
			$u_email_safe = $fluid_register->db_array[0]['u_email'];

			$fluid_register->php_db_query("UPDATE " . TABLE_USERS . " SET u_token_reset = '" . $fluid_register->php_escape_string($fluid_token) . "' WHERE u_oauth_provider = '" . $fluid_register->php_escape_string(OAUTH_FLUID) . "' AND u_email = '" . $fluid_register->php_escape_string($data->u_email) .  "'");

			$fluid_register->php_db_commit();

			//$fluid_register->php_send_email("info@leoscamera.com", $u_email_safe, "Leo's Camera account password reset", "You are receiving this email because you have requested that your Leo's Camera account password be reset.\n\nIn order to reset your password, please enter the following security code when prompted.\n\nCode: " . $fluid_token . "\n\nIf you have not requested a new password, please ignore this message. Your existing password will remain unchanged.\n\nRegards,\n\nLeo's Camera Supply\nwww.leoscamera.com");

			$f_email_data = addslashes(base64_encode(json_encode(Array("from" => "orders@leoscamera.com", "to" => $u_email_safe, "subject" => "Leo's Camera account password reset", "message" => "You are receiving this email because you have requested that your Leo's Camera account password to be reset.<br><br>In order to reset your password, please enter the following security code when prompted.<br><br>Code: " . $fluid_token . "<br><br>If you have not requested a new password, please ignore this message. Your existing password will remain unchanged.<br><br><br>"))));
			exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /dev/null &');

			// Write code here to send the reset token to the email with a url.
			/*
				1. Send a 4 digit Alphanumeric code to email. (later to sms when implemented).
				2. Ask for input of the 4 digit code.
				3. Ask and confirm the security questions.
				4. Display new password input forms to reset.
			*/

			// --> Send email with the token.

			$execute_functions[]['function'] = "js_html_style_hide";
			$execute_functions[]['function'] = "js_html_insert";
			$execute_functions[]['function'] = "js_html_style_display";
			$execute_functions[]['function'] = "js_fluid_code_validator";


			$modal = "
				<div class=\"panel-body\">
					<div class=\"text-center\">
						<h3><i class=\"fa fa-lock fa-4x\"></i></h3>
						<h2 class=\"text-center\">Security Code</h2>
						<p>Please check your email for your security code to reset your password.</p>
						<div class=\"panel-body\">
							<form id='fluid_form_security_code' data-toggle='validator' role='form' onsubmit='js_fluid_security_code();' accept-charset='UTF-8'>
								<div class='form-group has-feedback'>
									 <label class='sr-only'>Security code</label>
									 <input type='text' class='form-control' id='fluid_email_security_code' autofocus=\"autofocus\" name=\"fluid_email_security_code\" placeholder='security code' maxlength='50' data-remote=\"" . FLUID_ACCOUNT . "?load_func=true&fluid_function=php_validate_reset_code&email=" . base64_encode($data->u_email) . "\" data-error='Invalid code.' required>
									 <span class='glyphicon form-control-feedback' aria-hidden='true'></span>
									 <div class='help-block with-errors'></div>
								</div>
								<div class=\"form-group\">
									<input type='hidden' id='fluid_security_code_email' value='" . htmlspecialchars(base64_encode($data->u_email)) . "'>
									<button id='fluid_forgot_password_button' class=\"btn btn-info btn-block\" type=\"submit\">Validate</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			";

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id_hide" => base64_encode("forget-hide-id"), "div_id" => base64_encode("fluid-reset-forget-div"), "html" => base64_encode($modal), "div_style" => base64_encode("block"), "error" => count($fluid_register->db_error), "error_message" => base64_encode($fluid_register->db_error)));
		}
		else {
			return php_fluid_error("Invalid email address");
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_google_button() {
	// Google app info:
	// https://console.developers.google.com/apis/credentials
	// https://developers.google.com/identity/sign-in/web/sign-in

	$gClient = new Google_Client();
	$gClient->setApplicationName('Login to Leos Camera Supply');
	$gClient->setClientId(GOOGLE_CLIENT_LOGIN_ID);
	$gClient->setClientSecret(GOOGLE_CLIENT_SECRET);
	$gClient->setRedirectUri(GOOGLE_WWW_FLUID_ACCOUNT);

	$google_oauthV2 = new Google_Oauth2Service($gClient);

	if(isset($_SESSION['token_google'])) {
		$gClient->setAccessToken($_SESSION['token_google']);
		$google_url = GOOGLE_WWW_FLUID_ACCOUNT;
	}
	else
		$google_url = $gClient->createAuthUrl();

	if(isset($_SESSION['u_id'])) {
		$data['html'] = "<a href=\"logout.php?logout\" class=\"btn btn-primary\">Logout</a>";
	}
	else {
		$data['script'] = "<script>
				var fluid_google_window;
				var fluid_google_checkout = 0;

				function js_fluid_account_login_refresh_google() {
					if(fluid_google_checkout == 0)
						location.reload();
					else
						js_redirect_url({url:\"" . base64_encode(FLUID_CART) . "\"});
				}

				function js_fluid_google_login() {
					fluid_google_window = window.open('" . $google_url . "', '_blank', 'width=600, height=450, top=200, left=250');

					var interval = window.setInterval(function() {
						try {
							if (fluid_google_window == null || fluid_google_window.closed) {
								window.clearInterval(interval);
								js_fluid_account_login_refresh_google();
							}
						}
						catch (e) {
						}
					}, 500);
				}

			</script>";
		$data['html'] = "<a onClick=\"js_fluid_google_login();\" class=\"btn btn-google\" style='margin: 2px;' data-onsuccess=\"onSignIn\"><i class=\"fa fa-google\"></i> Google</a>";
	}

	return $data;
}

function php_google_maps_autofill($f_checkout_id = NULL) {
	// https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-addressform
	?>
	<script>
		function js_fluid_set_option(selectElement, value) {
			var options = selectElement.options;
			for (var i = 0, optionsLength = options.length; i < optionsLength; i++) {
				if (options[i].value == value) {
					selectElement.selectedIndex = i;
					return true;
				}
			}
			return false;
		}

		var placeSearch, autocomplete, autocompleteBilling;

		var componentForm = {
		  street_number: 'long_name',
		  route: 'long_name',
		  locality: 'long_name',
		  administrative_area_level_1: 'long_name',
		  country: 'long_name',
		  postal_code: 'long_name'
		};

		var dataArray = {
			street_number: '',
			route: '',
			locality: '',
			administrative_area_level_1: '',
			country: '',
			postal_code: ''
		};

		function js_google_maps_init_auto_complete() {
		  $("#fluid_form_address").validator();
  		  $('#fluid_form_address').validator('update');

  		  js_fluid_update_selectpicker('refresh');

			<?php
			/*
		  // Create the autocomplete object, restricting the search to geographical location types.
		  autocomplete = new google.maps.places.Autocomplete(
			  (document.getElementById('fluid-address-street')),
			  {types: ['geocode']});

		  // When the user selects an address from the dropdown, populate the address fields in the form.
		  autocomplete.addListener('place_changed', js_google_maps_fill_in_address);

		  var input = document.getElementById('fluid-address-street');
		  google.maps.event.addDomListener(input, 'keydown', function(event) {
			if (event.keyCode === 13) {
				event.preventDefault();
			}
		  });
		  */
		  ?>
		}

		// [START region_fillform]
		function js_google_maps_fill_in_address() {
		  // Get the place details from the autocomplete object.
		  var place = autocomplete.getPlace();

		  // Get each component of the address from the place details and fill the corresponding field on the form.
		  for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];

			if (componentForm[addressType]) {
			  var val = place.address_components[i][componentForm[addressType]];

			  dataArray[addressType] = val;
			}
		  }

		  document.getElementById('fluid-address-street').value = dataArray['street_number'] + " " + dataArray['route'];
		  document.getElementById('fluid-address-city').value = dataArray['locality'];
		  document.getElementById('fluid-address-province').value = dataArray['administrative_area_level_1'];

		  if($('#fluid-address-country').is('select')) {
			js_fluid_set_option(document.getElementById('fluid-address-country'), dataArray['country']);
			$('.selectpicker').selectpicker('refresh');
		  }
		  else
			document.getElementById('fluid-address-country').value = dataArray['country'];

		  document.getElementById('fluid-address-postal-code').value = dataArray['postal_code'];

		  $('#fluid_form_address').validator('validate');
		}
		// [END region_fillform]

		// [START region_geolocation]
		// Bias the autocomplete object to the user's geographical location, as supplied by the browser's 'navigator.geolocation' object.
		function js_google_maps_geo_locate() {
			<?php
			/*
		  if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
			  var geolocation = {
				lat: position.coords.latitude,
				lng: position.coords.longitude
			  };
			  var circle = new google.maps.Circle({
				center: geolocation,
				radius: position.coords.accuracy
			  });
			  autocomplete.setBounds(circle.getBounds());
			});
		  }
		  */
		  ?>
		}
		// [END region_geolocation]
	</script>

	<script>
		function js_fluid_country_change(country) {
			if(country == "Canada") {
				<?php //var f_div = document.getElementById('province-canada'); ?>
				document.getElementById('f-province-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_PROVINCE); ?>');
			}
			else if(country == "United States") {
				document.getElementById('f-province-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_STATES); ?>');
			}
			else {
				<?php //var f_div = document.getElementById('state-usa'); ?>
				document.getElementById('f-province-address').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_WORLD); ?>');
			}


			$('#fluid_form_address').validator('update');
			$('#fluid_form_address').validator('validate');
			 <?php
			 // --> ** updating the selectpickers has to be done after the validator is refreshed and updated. because the select picker creates new elements with same names and id's.
			 ?>
			 js_fluid_update_selectpicker('refresh');
		}

		function js_fluid_address_validator_create() {
			document.getElementById('modal-address-div').innerHTML = "";
			document.getElementById('modal-address-div').innerHTML = Base64.decode('<?php echo base64_encode(HTML_ADDRESS_CREATE_FORM);?>');

			js_modal_show('#fluid-address-modal');
			js_fluid_address_validator();
			js_google_maps_init_auto_complete();
		}

		function js_fluid_address_validator() {
			$('#fluid_form_address').validator('update');
			$('#fluid_form_address').validator('validate');
			$('#fluid_form_address')[0].reset();

			document.getElementById('fluid-address-name').value = "";
			document.getElementById('fluid-address-apt-number').value = "";
			document.getElementById('fluid-address-street').value = "";
			document.getElementById('fluid-address-city').value = "";
			document.getElementById('fluid-address-province').value = "";
			document.getElementById('fluid-address-country').value = "";
			document.getElementById('fluid-address-postal-code').value = "";
			document.getElementById('fluid-address-phone-number').value = "";
			document.getElementById('fluid-email-address').value = "<?php if(isset($_SESSION['u_id']) && isset($_SESSION['u_email'])) echo $_SESSION['u_email']; ?>";
			document.getElementById('fluid-email-again-address').value = "<?php if(isset($_SESSION['u_id']) && isset($_SESSION['u_email'])) echo $_SESSION['u_email']; ?>";
		}

		function js_fluid_address_create() {
			$('#fluid_form_address').validator().on('submit', function (e) {
			  if(e.keyCode == 13)
					e.isDefaultPrevented();

			  if (e.isDefaultPrevented()) {
				// handle the invalid form...
			  } else {
				// everything looks good!
				e.isDefaultPrevented();
				e.preventDefault(e);

				$('#fluid_form_address').validator('destroy');

				try {
					var FluidAddress = {};
						FluidAddress.a_name = document.getElementById('fluid-address-name').value;
						FluidAddress.a_number = document.getElementById('fluid-address-apt-number').value;
						FluidAddress.a_street = document.getElementById('fluid-address-street').value;
						FluidAddress.a_city = document.getElementById('fluid-address-city').value;
						FluidAddress.a_province = document.getElementById('fluid-address-province').value;

						<?php //FluidAddress.a_country = document.getElementById('fluid-address-country').value; ?>
						FluidAddress.a_country = document.getElementById('fluid-address-country').options[document.getElementById('fluid-address-country').selectedIndex].value;

						FluidAddress.a_postalcode = document.getElementById('fluid-address-postal-code').value;
						FluidAddress.a_phonenumber = document.getElementById('fluid-address-phone-number').value;
						FluidAddress.a_email = document.getElementById('fluid-email-address').value;
						FluidAddress.a_emailagain = document.getElementById('fluid-email-again-address').value;
						<?php
						if(isset($f_checkout_id))
							echo "FluidAddress.f_checkout_id = \"" .  $f_checkout_id . "\";";
						?>
						FluidAddress.tmp = FluidTemp.tmp;

					var data = Base64.encode(JSON.stringify(FluidAddress));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_address_create&data=" + data}));

					//$('#fluid_form_address').validator('destroy');

					js_fluid_ajax(data_obj);

					$(function () {
						$(FluidTemp.tmp_address_modal).modal('toggle');
					});
				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_address_default(checkbox) {
			try {
				var FluidDefault = {};
					FluidDefault.a_id = checkbox.id;
					FluidDefault.a_default = checkbox.checked;

				var data = Base64.encode(JSON.stringify(FluidDefault));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_address_default&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_address_delete(a_id) {
			try {
				var FluidData = {};
					FluidData.a_id = a_id;

				var data = Base64.encode(JSON.stringify(FluidData));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_address_delete&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

	</script>
<?php
}

function php_google_maps_autofill_ship() {
	?>
	<script>
		var placeSearchShip, autocompleteShip;

		var componentFormShip = {
		  street_number: 'long_name',
		  route: 'long_name',
		  locality: 'long_name',
		  administrative_area_level_1: 'long_name',
		  country: 'long_name',
		  postal_code: 'long_name'
		};

		var dataArrayShip = {
			street_number: '',
			route: '',
			locality: '',
			administrative_area_level_1: '',
			country: '',
			postal_code: ''
		};

		function js_google_maps_init_auto_complete_ship() {
			<?php
			/*
		  // Create the autocomplete object, restricting the search to geographical location types.
		  autocompleteShip = new google.maps.places.Autocomplete(
			  (document.getElementById('fluid-address-cart-ship')),
			  {types: ['geocode']});

		  // When the user selects an address from the dropdown, populate the address fields in the form.
		  autocompleteShip.addListener('place_changed', js_google_maps_fill_in_address_ship);

		  var input = document.getElementById('fluid-address-cart-ship');
		  google.maps.event.addDomListener(input, 'keydown', function(event) {
			if (event.keyCode === 13) {
				event.preventDefault();
			}
		  });
		  */
		  ?>
		}

		// [START region_fillform]
		function js_google_maps_fill_in_address_ship() {
		  // Get the place details from the autocomplete object.
		  var place = autocompleteShip.getPlace();

		  // Get each component of the address from the place details and fill the corresponding field on the form.
		  for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];

			if (componentFormShip[addressType]) {
			  var val = place.address_components[i][componentFormShip[addressType]];

			  dataArrayShip[addressType] = val;
			}
		  }

		  document.getElementById('fluid-address-cart-ship').value = dataArrayShip['street_number'] + " " + dataArrayShip['route'] + " " + dataArrayShip['locality'] + " " + dataArrayShip['administrative_area_level_1'] + " " + dataArrayShip['country'] + " " + dataArrayShip['postal_code'];

		  FluidTemp.shipping_tmp = {};
		  FluidTemp.shipping_tmp['a_street'] = dataArrayShip['street_number'] + " " + dataArrayShip['route'];;
		  FluidTemp.shipping_tmp['a_city'] = dataArrayShip['locality'];
		  FluidTemp.shipping_tmp['a_province'] = dataArrayShip['administrative_area_level_1'];
		  FluidTemp.shipping_tmp['a_country'] = dataArrayShip['country'];
		  FluidTemp.shipping_tmp['a_postalcode'] = dataArrayShip['postal_code'];

		  document.getElementById('fluid-address-ship-estimator-button').disabled = false;

		  $('#fluid_form_address_cart_ship').validator('validate');
		}
		// [END region_fillform]

		// [START region_geolocation]
		// Bias the autocomplete object to the user's geographical location, as supplied by the browser's 'navigator.geolocation' object.
		function js_google_maps_geo_locate_ship() {
			<?php
			/*
		  if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
			  var geolocation = {
				lat: position.coords.latitude,
				lng: position.coords.longitude
			  };
			  var circle = new google.maps.Circle({
				center: geolocation,
				radius: position.coords.accuracy
			  });
			  autocompleteShip.setBounds(circle.getBounds());
			});
		  }
		  */
		  ?>
		}
		// [END region_geolocation]

		function js_fluid_address_ship() {
			$('#fluid_form_address_cart_ship').validator().on('submit', function (e) {
			  if(e.keyCode == 13)
					e.isDefaultPrevented();

			  if(FluidTemp.shipping_tmp['a_valid'] == false || FluidTemp.shipping_tmp['a_valid'] == 'undefined')
					e.isDefaultPrevented();

			  if (e.isDefaultPrevented()) {
				// handle the invalid form...
			  } else {
				// everything looks good!
				e.isDefaultPrevented();
				e.preventDefault(e);

				try {
					var data = Base64.encode(JSON.stringify(FluidTemp.shipping_tmp));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_address_create_ship&data=" + data}));

					//$('#fluid_form_address').validator('destroy');

					js_fluid_ajax(data_obj);

					<?php
					/*
					$(function () {
						$(FluidTemp.tmp_address_modal).modal('toggle');
					});
					*/
					?>
				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_address_ship_select(data) {
			try {
				var f_data = Base64.encode(JSON.stringify(data));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_CART;?>", dataobj: "load_func=true&fluid_function=php_fluid_address_ship_select&data=" + f_data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}

	</script>
<?php
}

function php_html_account_address_book($checkout = FALSE, $f_data = NULL, $a_data = NULL) {
	try {
		if(isset($f_data)) {
			if(empty($_SESSION['f_checkout'][$f_data->f_checkout_id]))
				throw new Exception("session checkout mismatch error");
		}

		$fluid = new Fluid ();

		$detect = new Mobile_Detect;
		if($detect->isMobile() || $detect->isTablet()) {
			$f_mobile_touch = "Touch";
			$f_glyph = "glyphicon glyphicon-hand-up";
		}
		else {
			$f_mobile_touch = "Click";
			$f_glyph = "glyphicon glyphicon-plus";
		}

		$fa_data = NULL;

		if(isset($_SESSION['u_id'])) {
			$fluid->php_db_begin();

			$fluid->php_db_query("SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE a_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "' ORDER BY a_id ASC");

			$fluid->php_db_commit();

			if(isset($fluid->db_array))
				$fa_data = $fluid->db_array;
		}
		else if($checkout == TRUE) {
			if(isset($f_data))
				if(isset($_SESSION['f_checkout'][$f_data->f_checkout_id]))
					if(isset($_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address_list']))
						$fa_data = $_SESSION['f_checkout'][$f_data->f_checkout_id]['f_address_list'];
		}

		if($checkout == TRUE) {
			$shadow_css = " class='fluid-account-box-special'";
			$f_onclick = "onClick='js_fluid_address_checkout_creator();'"; // --> fluid.cart.php
		}
		else {
			$shadow_css = " class='fluid-box-shadow fluid-account-box-special'";
			$f_onclick = "data-toggle=\"modal\" data-backdrop=\"static\" data-keyboard=\"false\" onClick='js_fluid_address_validator_create();'";
		}

		$html = NULL;

		$f_addresses_found = 0;

		$html_s = "<div" . $shadow_css . ">";
		$f_padding = "padding-bottom: 10px;";
		$f_class_btn = NULL;

		if(isset($fa_data)) {
			foreach($fa_data as $key => $data) {
				$style = NULL;
				$java = NULL;
				$class = NULL;

				if($checkout == TRUE) {
					if(isset($f_data)) {
						if(base64_decode($f_data->a_id) == $data['a_id'])
							$style = " background-color: rgba(45,255,93,0.5);";
					}

					$java = " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_fluid_checkout_address_select({a_id : \"" . base64_encode($data['a_id']) . "\", a_add_to_cart : null}); $(function () { $(\"#fluid-main-modal\").modal(\"toggle\"); });'"; // --> fluid.cart.php
					$class = " fluid-div-highlight";
				}
				else if($data['a_default'] == 1)
					$style = " background-color: rgba(45,255,93,0.5);";

				$html .= "<div class='well fluid-box-shadow-small-well" . $class . "' style='display: table; width: 100%;" . $style . "'" . $java . ">";

					$html .= "<div style='display: table-cell; padding: 0px; margin: 0px; vertical-align: middle;'>";
						$html .= "<div>" . $data['a_name'] . "</div>";
						$html .= "<div>";

							if($data['a_number'] != "")
								$html .= $data['a_number'] .  " - ";
							$html .= $data['a_street'];

						$html .= "</div>";

						$html .= "<div>" . $data['a_city'] . " " . $data['a_province'] . "</div>";
						$html .= "<div>" . $data['a_country'] . " " . $data['a_postalcode'] . "</div>";
						$html .= "<div>" . $data['a_phonenumber'] . "</div>";
						$html .= "<div style='font-size: 12px; font-style: italic;'>" . $data['a_email'] . "</div>";
					$html .= "</div>";

					if($checkout == FALSE) {
						if($data['a_default'] == 1)
							$checked = " checked";
						else
							$checked = NULL;

						$html .= "<div style='display: table-cell; text-align: right;'>";
							$html .= "<div class=\"checkbox\">
										<label><input type=\"checkbox\" id=\"" . $data['a_id'] . "\"" . $checked . " onClick='js_fluid_address_default(this);'><span class=\"cr\"><i class=\"cr-icon fa fa-check\"></i></span></label></input><div style='display: inline-block; margin-bottom: 0px;'> Default</div>
									  </div>";
							$html .= "<div style='margin-top: 30px;'><button class='btn btn-danger' onClick='js_fluid_address_delete(\"" . $data['a_id'] . "\");'><span class='glyphicon glyphicon-remove'></span><div class='f-delete-hide'> Delete</div></button></div>";
						$html .= "</div>";
					}
					else {
						$html .= "<div class='pull-right' style='display table-row'>";
							$html .= "<div style='display: table-cell; text-align: center;'>";

								if(isset($f_data)) {
									if(base64_decode($f_data->a_id) == $data['a_id']) {
										$html .= "<div id='fluid-address-" . $data['a_id'] . "' style='font-style: italic;'><div class='fluid-desktop'>Selected</div> <i class=\"fa fa-check\" aria-hidden='true'></i></div>";
									}
									else
										$html .= "<div id='fluid-address-" . $data['a_id'] . "' style='font-style: italic;'><div class='fluid-desktop'>" . $f_mobile_touch . " to select</div> <span class='glyphicon glyphicon-hand-up' aria-hidden='true'></span></div>";
								}
								else
									$html .= "<div id='fluid-address-" . $data['a_id'] . "' style='font-style: italic;'><div class='fluid-desktop'>" . $f_mobile_touch . " to select</div> <span class='glyphicon glyphicon-hand-up' aria-hidden='true'></span></div>";

							$html .= "</div>";
						$html .= "</div>";
					}

				$html .= "</div>";

				$f_addresses_found++;
			}
		}
		else if($checkout == FALSE) {
			$html_s .= "<div class=\"form-group text-center\">
						<h3><i class=\"fa fa-2x fa-home\" aria-hidden=\"true\"></i></h3>
						<h2 class=\"text-center\">My Addresses</h2>
					</div>";
			$html_s .= "<div style='display: block; text-align: center; padding-top: 10px; padding-bottom: 50px;'></div>";

			$f_padding = "padding-bottom: 50px;";
			$f_class_btn = "btn btn-default";
		}

		$html .= "</div>";

		// --> Can only ship to billing addresses. Lets display a message while in the checkout.
		if($checkout == TRUE && FLUID_SHIP_NON_BILLING == FALSE) {
			$f_billing_message = "<div style='color: red; font-weight: 600; font-size: 80%; margin-bottom: 15px;'>For security purposes, shipping and billing addresses must match the address as it appears on your credit card statement; otherwise payment method and verification by PayPal will be required.</div>";
		}
		else
			$f_billing_message = NULL;

		if($f_addresses_found >= VAR_MAX_ADDRESSES)
			$html_s .= "<div style='display: flex; " . $f_padding . "'><div class='" . $f_class_btn . " disabled' style='margin: auto; text-align: center; color:#717171; text-decoration: line-through;' onmouseover=\"JavaScript:this.style.cursor='not-allowed';\"><span class='" . $f_glyph . "'></span><div>" . $f_mobile_touch . " to create a shipping address</div></div></div>" . $f_billing_message;
		else
			$html_s .= "<div style='display: flex; " . $f_padding . "'><div class='" . $f_class_btn . "' style='margin: auto; text-align: center;' onmouseover=\"JavaScript:this.style.cursor='pointer';\" " . $f_onclick . "><span class='" . $f_glyph . "'></span><div>" . $f_mobile_touch . " to create a shipping address</div></div></div>" . $f_billing_message;

		$html = $html_s . $html;

		return Array("html" => utf8_decode($html), "f_addresses_found" => $f_addresses_found);
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// --> View extra additional information on a order.
function php_html_view_order($f_data) {
	try {
		if(isset($_SESSION['u_id'])) {
			$fluid = new Fluid ();

			$fluid->php_db_begin();

			$fluid->php_db_query("SELECT `s_id`, `s_status`, `s_tracking`, `s_order_number`, `s_sale_time`, `s_u_id`, `s_u_email`, `s_total`, `s_sub_total`, `s_shipping_total`, `s_tax_total`, `s_taxes`, `s_address_name`, `s_address_number`, `s_address_street`, `s_address_city`, `s_address_province`, `s_address_postalcode`, `s_address_country`, `s_address_phonenumber`, `s_shipping_64`, `s_items_64`, `s_address_payment_64` FROM " . TABLE_SALES . " WHERE s_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "' AND s_id = '" . $fluid->php_escape_string(base64_decode($f_data)) . "' ORDER BY s_id DESC");

			$fluid->php_db_commit();

			$html = NULL;
			$html_header = NULL;
			$f_animate_id = NULL;

			if(isset($fluid->db_array)) {
				$fo_data = php_html_generate_order_data($fluid->db_array);

				$html = $fo_data['html'];
				$html_header = $fo_data['html_header'];
				$f_animate_id = $fo_data['f_animate_id'];
			}

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($html))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-header-div"), "html" => base64_encode($html_header))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-close-button-text"), "html" => base64_encode("Close"))));

			$execute_functions[]['function'] = "js_modal_show_data";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-main-modal"))));

			$execute_functions[]['function'] = "js_fluid_block_animate";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_animate_id))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else
			throw new Exception("Session error.");
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_html_view_single_order_lookup($f_data) {
	try {
		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$fluid->php_db_query("SELECT `s_id`, `s_status`, `s_tracking`, `s_order_number`, `s_sale_time`, `s_u_id`, `s_u_email`, `s_total`, `s_sub_total`, `s_shipping_total`, `s_tax_total`, `s_taxes`, `s_address_name`, `s_address_number`, `s_address_street`, `s_address_city`, `s_address_province`, `s_address_postalcode`, `s_address_country`, `s_address_phonenumber`, `s_shipping_64`, `s_items_64`, `s_address_payment_64` FROM " . TABLE_SALES . " WHERE s_u_email = '" . $fluid->php_escape_string($f_data->f_email) . "' AND concat('%-', s_order_number, '-%') LIKE '%" .  $fluid->php_escape_string($f_data->f_order_id) . "%' ORDER BY s_id DESC");

		$fluid->php_db_commit();

		$html = NULL;
		$html_header = NULL;
		$f_animate_id = NULL;

		if(isset($fluid->db_array)) {
			$fo_data = php_html_generate_order_data($fluid->db_array);

			$html = $fo_data['html'];
			$html_header = $fo_data['html_header'];
			$f_animate_id = $fo_data['f_animate_id'];

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-div"), "html" => base64_encode($html))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("modal-fluid-header-div"), "html" => base64_encode($html_header))));

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("fluid-modal-close-button-text"), "html" => base64_encode("Close"))));

			$execute_functions[]['function'] = "js_modal_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-main-modal-msg"));

			$execute_functions[]['function'] = "js_modal_show_data";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("modal_id" => base64_encode("#fluid-main-modal"))));

			$execute_functions[]['function'] = "js_fluid_block_animate";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_animate_id))));
		}
		else {
			// --> Didn't find any data.
			$f_order_check_error = "<div class=\"alert alert-danger\" role=\"alert\" style='margin-top: 20px;'>Sorry, Could not locate the order.</div>";

			$execute_functions[]['function'] = "js_html_insert";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id" => base64_encode("f-order-check-error"), "html" => base64_encode($f_order_check_error))));
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_html_generate_order_data($fo_data) {
	try {
		$html = NULL;
		$html_header = NULL;
		$f_animate_id = NULL;

		if(isset($fo_data)) {
			$fluid = new Fluid ();
			foreach($fo_data as $s_data) {

				$html_header = "Order Number: " . explode('-', $s_data['s_order_number'])[2];

				$tmp_ship = (array)json_decode(base64_decode($s_data['s_shipping_64']), true);

				foreach($tmp_ship as $t_ship) {
					$o_ship = $t_ship;
					break;
				}

				// Get the delivery method.
				if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
					$d_method = $o_ship['type'];

					if(isset($o_ship['data']))
						if(isset($o_ship['data']['ship_type'])) {
							if($o_ship['data']['ship_type'] == "FedEx Ground")
								$d_method .= " Ground";
							else
								$d_method .= " " . $o_ship['data']['ship_type'];
						}
				}
				else
					$d_method = $o_ship['data']['ship_type'];

				// --> Shipping / Billing Address
				$s_address_payment = json_decode(base64_decode($s_data['s_address_payment_64']), TRUE);
				$sa_info_html = "<div>" . utf8_decode(($s_address_payment['a_name'])) . "</div>";

				$sa_info_html .= "<div>";
						if($s_address_payment['a_number'] != "")
							$sa_info_html .= utf8_decode($s_address_payment['a_number']) .  " - ";
						$sa_info_html .= utf8_decode($s_address_payment['a_street']);
				$sa_info_html .= "</div>";

				$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_city']) . " " . utf8_decode($s_address_payment['a_province']) . "</div>";
				$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_country']) . " " . utf8_decode($s_address_payment['a_postalcode']) . "</div>";
				$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_phonenumber']) . "</div>";


				// --> Delivery Information.
				$sd_info_html_header = "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Delivery:</div><div style='display: inline-block; padding-left: 5px;'>" . $d_method . "</div></div>";

				if($o_ship['data']['ship_type'] != IN_STORE_PICKUP)
					$sd_info_html_header .= "<div style='padding-top: 10px; font-weight: 600; font-style: italic;'>Delivery address</div>";
				else
					$sd_info_html_header .= "<div style='padding-top: 10px; font-weight: 600; font-style: italic;'>Pickup information</div>";

				$sd_info_top_html = "<div>" . utf8_decode($s_data['s_address_name']) . "</div>";

				$sd_info_html = "<div>";
					if($s_data['s_address_number'] != "") {
						$sd_info_html .= utf8_decode($s_data['s_address_number']) .  " - ";
						$sd_info_html .= utf8_decode($s_data['s_address_street']);
					}
					else {
						$sd_info_html .= utf8_decode($s_data['s_address_street']);
					}
				$sd_info_html .= "</div>";

				$sd_info_html .= "<div>" . utf8_decode($s_data['s_address_city']) . " " . utf8_decode($s_data['s_address_province']) . "</div>";
				$sd_info_html .= "<div>" . utf8_decode($s_data['s_address_country']) . " " . utf8_decode($s_data['s_address_postalcode']) . "</div>";
				$sd_info_html .= "<div>" . utf8_decode($s_data['s_address_phonenumber']) . "</div>";
				$sd_info_html .= "<div style='font-size: 12px; font-style: italic;'>" . utf8_decode($s_data['s_u_email']) . "</div>";

				// --> Lets get the transaction data.
				$fluid_alt = new Fluid ();
				$fluid_alt->php_db_begin();
				$fluid_alt->php_db_query("SELECT * FROM " . TABLE_SALES_TRANSACTIONS . " WHERE st_s_order_number = '" . $fluid_alt->php_escape_string($s_data['s_order_number']) . "' ORDER BY st_id ASC");
				$fluid_alt->php_db_commit();

				if(isset($fluid_alt->db_array)) {
					foreach($fluid_alt->db_array as $st_data) {
						$paypal = json_decode(unserialize(base64_decode($st_data['st_s_transaction_serialize_64'])));
						break;
					}
				}

				$si_html = "<div class='f-extra-invoice' style='padding-bottom: 20px;'>";
					$si_html .= "<div class='f-thank-you'>Thank you for your order</div>";
					$si_html .= "<div>" . $html_header . "</div>";
					$si_html .= "<div>Order Date: " . $s_data['s_sale_time'] . "</div>";

					$si_html .= "<div style='padding-top: 30px; padding-bottom: 30px;'>";

						if(!isset($paypal->id)) {
							$si_html .= "<div class='f-invoice-format-billing' style='display: inline-block; vertical-align: top;'>";
								$si_html .= "<div style='display: table;'>";
									$si_html .= "<div style='display: table-row; font-weight: 600;'>Billing information:</div>";
									$si_html .= "<div style='display: table-row;'> " . $sa_info_html . "</div>";
								$si_html .= "</div>";
							$si_html .= "</div>";
						}

						$f_paypal_padding = NULL;
						if(isset($paypal->id))
							$f_paypal_padding = "padding-left: 0px; ";

						$si_html .= "<div style='display: inline-block; " . $f_paypal_padding . "vertical-align: top;'>";
							$si_html .= "<div style='display: table;'>";
								$si_html .= "<div style='display: table-row; font-weight: 600;'>Shipping information:</div>";
								$si_html .= "<div style='display: table-row;'> " . $sd_info_top_html . $sd_info_html . "</div>";
							$si_html .= "</div>";
						$si_html .= "</div>";
					$si_html .= "</div>";

				$si_html .= "</div>";

				$html = $si_html;

				$html .= "<div>";
					$html .= "<div class='divTable'>";
						$html .= "<div class='divTableBody'>";
							$html .= "<div class='divTableRow'>";
								$html .= "<div style='display: table; width:100%; height: 100%; vertical-align: middle; padding-bottom: 10px;' class='divTableCellOrders fluid-cart-header'>";
									$html .= "<div style='display: table-cell; vertical-align: middle;'>Status: " . $fluid->php_fluid_order_status($s_data['s_status']) . "</div>";
										//$html .= "<div style='display: table-cell; vertical-align: middle; padding-left: 5px; float:right;'><button type='button' class='btn btn-default pull-right fluid-cart-edit-top-button' aria-haspopup='true' aria-expanded='false' onClick='js_fluid_cart_editor();'><span class='glyphicon glyphicon-edit' aria-hidden='true'></span></button></div>";

								if(!empty($s_data['s_tracking'])) {
									$html .= "<div style='display: table-cell; vertical-align: middle; float:right;'>Tracking #: " . $s_data['s_tracking'] . "</div>";
								}

								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";
					$html .= "</div>";
				$html .= "</div>";

				$s_items = json_decode(base64_decode($s_data['s_items_64']), true);

				$html .= "<div name='fluid-cart-scroll' class=' fluid-cart-no-scroll'>";

					foreach($s_items as $data) {
						// Process the image.
						$width_height = $fluid->php_process_image_resize($data['p_image'], "60", "60");

						$html .= "<div class='fluid-cart'>";

						$html .= "<div class='divTable'>";
							$html .= "<div class='divTableBody'>";
								$html .= "<div class='divTableRow'>";
									$html .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px; '><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $data['m_name'] . " " . $data['p_name'] . "'></img></div>";
									$html .= "<div class='divTableCellOrders' style='vertical-align:middle; font-size: 14px; font-weight: 400;'>" . $data['m_name'] . " " . $data['p_name'];

									$html .= "<div style='padding-top: 1px; padding-bottom: 5px;'>";
									$html .= "<div style='display: inline-block; font-size: 9px;'>UPC # " . $data['p_mfgcode'] . "</div>";
										if(isset($data['p_mfg_number']))
											$html .= "<i class=\"fa fa-square\" style='font-size: 4px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 9px; font-weight: 300;'>MFR # " . $data['p_mfg_number'] . "</div>";
									$html .= "</div>";

									$html .= "<div style='padding-top: 5px;'><div class='pull-left' style='font-weight: 400;'>Qty: " . $data['p_qty'] . "</div><div class='pull-right' style='font-weight: 400;'>" . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.</div></div></div>";
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>";

						$html .= "</div>";
					}

				$html .= "</div>"; // fluid-cart-no-scroll

				$html .= "<div style='padding-top: 10px;'>";
					$html .= "<div class='divTable'>";
						$html .= "<div class='divTableBody'>";
							$html .= "<div id='fluid-cart-totals' class='divTableRow pull-right fluid-cart-subtotal' style='font-size: 14px; font-weight: 400 !important;'>";

							$html .= "<div style='display: table;'>";

								$f_animate_id = NULL;
								$f_animate_id[] = Array("id" => base64_encode("fluid-sub-total-row-order"), "delay" => 0, "colour" => "#0050FF");
								$html .= "<div name='fluid-sub-total-row-order' id='fluid-sub-total-row-order' style='text-align: right;'>"; // --> This div is used for animating.
									$html .= "<div style='display: table-row;'>";
										$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Sub Total:</div><div style='display: table-cell; text-align: right;'> " . HTML_CURRENCY . " " . number_format($s_data['s_sub_total'], 2, ".", ",") . "</div>";
									$html .= "</div>";
								$html .= "</div>";

								// Shipping information.
								if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
									$f_animate_id[] = Array("id" => base64_encode("fluid-shipping-row-order"), "delay" => 250, "colour" => "#5EFF00");

									$html .= "<div name='fluid-shipping-row-order' id='fluid-shipping-row-order' style='text-align: right;'>"; // --> This div is used for animating.

									if($s_data['s_shipping_total'] == 0)
										$f_ship_html = "FREE";
									else
										$f_ship_html = HTML_CURRENCY . " " . number_format($s_data['s_shipping_total'], 2, ".", ",");

										$html .= "<div style='display: table-row;'>";
											$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Shipping:</div><div style='display: table-cell; text-align: right;'>" . $f_ship_html  . " </div>";
										$html .= "</div>";
									$html .= "</div>";
								}

								// Break down the taxes.
								if(isset($s_data['s_tax_total'])) {
									$t_taxes = json_decode($s_data['s_taxes'], true);

									foreach($t_taxes as $t_key => $t_data) {
										$tmp_total = 0;

										foreach($t_data['f_rates'] as $t_f_rates => $t_f_rates_data)
											$tmp_total = round($tmp_total + $t_f_rates_data['t_total'], 2);

										$tmp_total = round($t_data['p_total'] + $tmp_total, 2);

										$f_animate_id[] = Array("id" => base64_encode("fluid-tax-row-order-" . $t_key), "delay" => 250, "colour" => "#FF006B"); // --> id name for the animation div. This will be procssed by js_fluid_block_animate();
										$html .= "<div name='fluid-tax-row-order-" . $t_key . "' id='fluid-tax-row-order-" . $t_key . "' style='text-align: right;'>"; // --> This div is used for animating.
											$html .= "<div id='tax-" . $t_key . "' style='display: table-row; text-align:right;'><div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $t_data['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " " . number_format($tmp_total, 2, '.', ',') . "</div></div>";
										$html .= "</div>";
									}
								}

								$f_total_final_price_html = HTML_CURRENCY . " " . number_format($s_data['s_total'], 2, ".", ",");

								$f_animate_id[] = Array("id" => base64_encode("fluid-total-row-order"), "delay" => 250, "colour" => "#FFD600");
								$html .= "<div name='fluid-total-row-order' id='fluid-total-row-order' style='text-align: right;'>"; // --> This div is used for animating.
									$html .= "<div style='display: table-row; text-align: right;'>";
										$html .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Total:</div><div id='fluid-total-cart-row' style='display: table-cell; text-align: right;'>" . $f_total_final_price_html . "</div>";
									$html .= "</div>";
								$html .= "</div>";

							$html .= "</div>"; //table


							$html .= "</div>";
						$html .= "</div>";
					$html .= "</div>";

				$html .= "</div>";
			}
		}

		return Array("html" => $html, "html_header" => $html_header, "f_animate_id" => $f_animate_id);
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Generate a card with order information.
function php_html_order_cards($data) {
	$tmp_ship = (array)json_decode(base64_decode($data['s_shipping_64']), true);

	foreach($tmp_ship as $t_ship) {
		$o_ship = $t_ship;

		break;
	}

	$java = " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_fluid_order_view(\"" . base64_encode($data['s_id']) . "\");'";

	$html = "<div class='well fluid-box-shadow-small-well fluid-div-highlight' style='display: table; width: 100%;'" . $java . ">";

		$html .= "<div style='display: table-cell; padding: 0px; margin: 0px; vertical-align: middle;'>";
			$html .= "<div><div style='display: inline-block; font-weight: 500; font-style: italic;'>Order number:</div><div style='display: inline-block; padding-left: 5px;'>" . explode('-', $data['s_order_number'])[2] . "</div></div>";
			$html .= "<div><div style='display: inline-block; font-weight: 500; font-style: italic;'>Order date:</div><div style='display: inline-block; padding-left: 5px;'>" . explode(' ', $data['s_sale_time'])[0] . "</div></div>";

			$fluid = new Fluid ();
			$html .= "<div><div style='display: inline-block; font-weight: 500; font-style: italic;'>Order Status:</div><div style='display: inline-block; padding-left: 5px;'>" . $fluid->php_fluid_order_status($data['s_status']) . "</div></div>";

			$html .= "<div><div style='display: inline-block; font-weight: 500; font-style: italic;'>Order total:</div><div style='display: inline-block; padding-left: 5px;'>" . HTML_CURRENCY . number_format($data['s_total'], '2', '.', ',') . "</div></div>";


			if(!empty($data['s_refund_total'])) {
				$html .= "<div><div style='display: inline-block; font-weight: 500; font-style: italic; color: red;'>Refund:</div><div style='display: inline-block; padding-left: 5px;'>" . HTML_CURRENCY . number_format($data['s_refund_total'], '2', '.', ',') . "</div></div>";
			}

			if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
				$d_method = $o_ship['type'];

				if(isset($o_ship['data']))
					if(isset($o_ship['data']['ship_type'])) {
						if($o_ship['data']['ship_type'] == "FedEx Ground")
							$d_method .= " Ground";
						else
							$d_method .= " " . $o_ship['data']['ship_type'];
					}
			}
			else
				$d_method = $o_ship['data']['ship_type'];

			$html .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 500; font-style: italic;'>Delivery:</div><div style='display: inline-block; padding-left: 5px;'>" . $d_method . "</div></div>";

			if(!empty($data['s_tracking'])) {
				$html .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 500; font-style: italic;'>Tracking #:</div><div style='display: inline-block; padding-left: 5px;'>" . $data['s_tracking'] . "</div></div>";
			}

			// Scan s_shipping_64 to find delivery method.
			// --> If pickup, then just display a name.
			// --> If shipped, then show full address.
			if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
				$html .= "<div style='padding-top: 5px; font-weight: 500; font-style: italic;'>Delivery Address</div>";
				$html .= "<div>" . utf8_decode($data['s_address_name']) . "</div>";

				$html .= "<div>";
						if($data['s_address_number'] != "")
							$html .= utf8_decode($data['s_address_number']) .  " - ";
						$html .= utf8_decode($data['s_address_street']);
				$html .= "</div>";

				$html .= "<div>" . utf8_decode($data['s_address_city']) . " " . utf8_decode($data['s_address_province']) . "</div>";
				$html .= "<div>" . utf8_decode($data['s_address_country']) . " " . utf8_decode($data['s_address_postalcode']) . "</div>";
				$html .= "<div>" . utf8_decode($data['s_address_phonenumber']) . "</div>";
				$html .= "<div style='font-size: 12px; font-style: italic;'>" . utf8_decode($data['s_u_email']) . "</div>";
			}

		$html .= "</div>";

		$html .= "<div class='pull-right' style='display table-row'>";
			$html .= "<div style='display: table-cell; text-align: center;'>";

				$html .= "<div id='fluid-order-" . $data['s_id'] . "' style='font-style: italic;'><div class='fluid-desktop'>" . $data['mobile_mode'] . " to view</div> <span class='glyphicon glyphicon-hand-up' aria-hidden='true'></span></div>";

			$html .= "</div>";
		$html .= "</div>";

	$html .= "</div>";

	return $html;
}

function php_html_account_my_orders($data_obj = NULL) {
	try {
		$html = NULL;
		$bool_found_items = FALSE;
		$i_item_count = 0;
		$new_item_count = 0;

		if(isset($_SESSION['u_id'])) {
			$fluid = new Fluid ();

			$fluid->php_db_begin();

			if(isset($data_obj->last_id))
				$last_id = $data_obj->last_id;
			else
				$last_id = 0;

			$fluid->php_db_query("SELECT COUNT(s_id) AS total FROM " . TABLE_SALES . " WHERE s_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "'");

			if(isset($fluid->db_array))
				$total_items = $fluid->db_array[0]['total'];
			else
				$total_items = 0;

			if(isset($data_obj->item_page))
				$item_start = ($data_obj->item_page - 1) * VAR_LISTING_MAX;  // The first item to display on this page.
			else
				$item_start = 0; // If no item_page var is given, set start to 0.

			$order_by = "ORDER BY s_id DESC LIMIT " . $item_start . "," . VAR_LISTING_MAX;

			$fluid->php_db_query("SELECT `s_id`, `s_status`, `s_tracking`, `s_refund_total`, `s_order_number`, `s_sale_time`, `s_u_id`, `s_u_email`, `s_total`, `s_sub_total`, `s_shipping_total`, `s_tax_total`, `s_taxes`, `s_address_name`, `s_address_number`, `s_address_street`, `s_address_city`, `s_address_province`, `s_address_postalcode`, `s_address_country`, `s_address_phonenumber`, `s_shipping_64`, `s_items_64` FROM " . TABLE_SALES . " WHERE s_u_id = '" . $fluid->php_escape_string($_SESSION['u_id']) . "' " . $order_by);

			$fluid->php_db_commit();

			$detect = new Mobile_Detect;
			if($detect->isMobile() || $detect->isTablet())
				$f_mobile_touch = "Touch";
			else
				$f_mobile_touch = "Click";

			if(isset($fluid->db_array)) {

				if($data_obj->divloader == TRUE) {
					$html = "<div class='fluid-box-shadow fluid-account-box-special' id='fluid-account-div-special'>";

					$html .= "
					<div class=\"form-group text-center\">
						<h3><i class=\"fa fa-gift fa-2x\"></i></h3>
						<h2 class=\"text-center\">My Orders</h2>
					</div>";
				}

				foreach($fluid->db_array as $data) {
					$data['mobile_mode'] = $f_mobile_touch;

					$last_id = $data['s_id'];
					$bool_found_items = TRUE;
					$i_item_count++;

					$html .= utf8_encode(php_html_order_cards($data));
				}

				if($data_obj->divloader == TRUE)
					$html .= "</div>";
			}
			else {
					$html = "<div class='fluid-box-shadow fluid-account-box-special' id='fluid-account-div-special'>";

					$html .= "
					<div class=\"form-group text-center\">
						<h3><i class=\"fa fa-gift fa-2x\"></i></h3>
						<h2 class=\"text-center\">My Orders</h2>
					</div>";

					$html .= "<div style='padding-top: 20px; min-height:30vh;'><div style='text-align: center; display: block;  width: 100%;'>You currently have no orders</div></div>";
			}

			// If we are loading via javascript ajax, then sleep for 1 second to display the loading gif.
			if($item_start > 0)
				sleep(1);

			$new_item_count = $i_item_count + $data_obj->item_count;
		}
		else {
			$html = "Session error";
		}

		$execute_functions[]['function'] = "js_html_insert";

		//return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("fluid-account-content"), "html" => base64_encode($html)));

		$execute_functions[]['function'] = "js_fluid_account_listings_update";

		return json_encode(array("html" => base64_encode(utf8_decode($html)), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("fluid-account-content"), "item_count" => base64_encode($new_item_count), "item_page" => base64_encode($data_obj->item_page), "item_page_next" => base64_encode($data_obj->item_page + 1), "item_page_previous" => base64_encode($data_obj->item_page - 1), "item_start" => base64_encode($item_start), "total_items" => base64_encode($total_items), "bool_found_items" => $bool_found_items, "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return json_encode(array("error" => 1, "error_message" => base64_encode($err)));
	}
}


function php_html_account_settings() {
	$html = "
		<div class='fluid-box-shadow fluid-account-box-special' style='padding-bottom: 40px;'>
			<div class=\"form-group text-center\">
				  <h3><i class=\"fa fa-user fa-2x\"></i></h3>
				  <h2 class=\"text-center\">Account Settings</h2>
			</div>

			<div class=\"alert alert-success\" role=\"alert\" id=\"fluid-settings-success-notification\" style=\"display:none;\">Settings updated.</div>
			<div style='width: 100%;'>
				<form class=\"fluid-settings-form\" id=\"fluid_form_account_update\" data-toggle=\"validator\" role=\"form\" onsubmit=\"js_fluid_account_update();\">
					<div class=\"form-group has-feedback\">
						<label class=\"control-label\" for=\"fluid_first_name_update\">First name</label>
						<input id=\"fluid_first_name_update\" type=\"text\" maxlength=\"50\" class=\"form-control\" required";
						if(isset($_SESSION['u_first_name']))
							$html .= " value='" . htmlspecialchars($_SESSION['u_first_name']) . "'";
				$html .=">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>

					<div class=\"form-group has-feedback\">
						<label class=\"control-label\" for=\"fluid_last_name_update\">Last name</label>
						<input id=\"fluid_last_name_update\" type=\"text\" maxlength=\"50\" class=\"form-control\" required";
						if(isset($_SESSION['u_last_name']))
							$html .= " value='" . htmlspecialchars($_SESSION['u_last_name']) . "'";
				$html .= ">
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
					</div>

					<div class=\"form-group\">
						<button type=\"submit\" class=\"btn btn-info btn-block\">Save changes</button>
					</div>
				</form>
			</div>
		</div>

		<div class='fluid-box-shadow fluid-account-box-special' style='padding-bottom: 40px; margin-top: 20px;'>
			<div class=\"form-group text-center\">
				<h3><i class=\"fa fa-lock fa-2x\"></i></h3>
				<h2 class=\"text-center\">Password Reset</h2>
			</div>

			<div class=\"alert alert-success\" role=\"alert\" id=\"fluid-password-success-notification\" style=\"display:none;\">Password has been updated.</div>
			<div style='width: 100%;'>
				<form class=\"fluid-settings-form\" id=\"fluid_form_password_update\" data-toggle=\"validator\" role=\"form\" onsubmit=\"js_fluid_password_update();\">
					<div class=\"form-group has-feedback\">
						<label class=\"control-label\" for=\"fluid_password_update\">New Password</label>
						<input id=\"fluid_password_update\" type=\"password\" maxlength=\"100\" class=\"form-control input-md\" placeholder=\"Password\" data-minlength=\"6\" required>
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						<div class=\"help-block with-errors\">Minimum of 6 characters</div>
					</div>
					<div class=\"form-group has-feedback\">
						<label class=\"control-label\" for=\"fluid_password_again_update\">Password again</label>
						<input id=\"fluid_password_again_update\" type=\"password\" maxlength=\"100\" class=\"form-control input-md\" data-match=\"#fluid_password_update\" data-match-error=\"Passwords do not match.\" required>
						<span class=\"glyphicon form-control-feedback\" aria-hidden=\"true\"></span>
						<div class=\"help-block with-errors\"></div>
					</div>
					<div class=\"form-group\">
						<button type=\"submit\" class=\"btn btn-info btn-block\">Update password</button>
					</div>
				</form>
			</div>
		</div>
	";


	$execute_functions[]['function'] = "js_html_insert";
	$execute_functions[]['function'] = "js_fluid_account_settings_validator";

	return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("fluid-account-content"), "html" => base64_encode(utf8_decode($html))));
}

function php_html_account_wish_list() {
	$execute_functions[]['function'] = "js_html_insert";

	return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "div_id" => base64_encode("fluid-account-content"), "html" => base64_encode("wish list page")));
}

function php_main_fluid_account() {
  if(isset($_SESSION['u_id']) == FALSE) {
	header("Location: " . $_SESSION['fluid_uri']);
	exit(0);
  }
  else {
	require_once("header.php");

?>
	<!DOCTYPE html>

	<html lang="en">
	<head>
		<?php
		if($_SERVER['SERVER_NAME'] != "local.leoscamera.com" && $_SERVER['SERVER_NAME'] != "dev.leoscamera.com") {
			//$_SESSION['u_oauth_id']
		?>
			<!-- Google Tag Manager -->
			<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','dataLayer','GTM-MTMBX6P');</script>
			<!-- End Google Tag Manager -->

			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-21150353-5"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', 'UA-21150353-5');

			  <?php
			  if(isset($_SESSION['u_oauth_id']))
				echo "gtag('set', {'user_id': '" . $_SESSION['u_oauth_id'] . "'});";
			  ?>
			</script>
		<?php
		}
		?>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php //<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags --> ?>

		<title>Leos Camera Supply</title>

	<?php
	php_load_pre_header();
	?>

	</head>

	<body>

	<?php
	php_load_header();

	?>

	<script>
		FluidTemp.tmp = "account";
		FluidTemp.tmp_address_modal = "#fluid-address-modal";

		var FluidAccount = {};
			FluidAccount.divloader = false;

		var is_loading = false; <?php // initialize is_loading by false to accept new loading ?>
		var footer_height = 0;
		var doc_width = parseInt($(document).width());
		var fluid_function = 'php_html_account_my_orders';

		<?php
		if(isset($_REQUEST['f'])) {
		if($_REQUEST['f'] == "address")
			echo "var fluid_disabled = true;";
		else if($_REQUEST['f'] == "orders")
			echo "var fluid_disabled = false;";
		}
		else
			echo "var fluid_disabled = false;";
		?>

		$(document).ready(function() {
			var navItems = $('#fluid-account-menu li > a');
			var navListItems = $('#fluid-account-menu li');

			navItems.click(function(e) {
				e.preventDefault();
				navListItems.removeClass('active');
				$(this).closest('li').addClass('active');
			});

			$(window).scroll(function() {
				footer_height = document.getElementById('footer_fluid').clientHeight;

				if(parseInt($(window).scrollTop()) + parseInt($(window).height() + footer_height) > parseInt($(document).height()) && FluidAccount.item_count_total < FluidAccount.total_items && FluidAccount.item_count > 0 && fluid_disabled == false) {
					<?php // stop loading many times for the same page ?>
					if (is_loading == false) {
						<?php // set is_loading to true to refuse new loading ?>
						is_loading = true;

						<?php // Set the item page to load to be the next one. ?>
						var item_page = FluidAccount.item_page;
						FluidAccount.item_page = FluidAccount.item_page_next;

						<?php // display the waiting loader ?>
						$('#fluid_loader').show();

						<?php // execute an ajax query to load more items. ?>
						$.ajax({
							url: '<?php echo FLUID_ACCOUNT; ?>',
							type: 'POST',
							data: {load_func:'1', data: Base64.encode(JSON.stringify(FluidAccount)), fluid_function: fluid_function},

							success:function(data){
								var data_obj = JSON.parse(data);

								$('#fluid_loader').hide();
								is_loading = false;

								if(data_obj['error'] > 0 && typeof data_obj['error'] != 'undefined') {
									FluidAccount.item_page = item_page;
									js_debug_error(Base64.decode(data_obj['error_message']));
								}
								else if(data_obj['bool_found_items'] == true) {
									<?php // append: add the new statements to the existing data ?>
									$('#fluid-account-div-special').append(Base64.decode(data_obj['html']));

									<?php // Update the select pickers. ?>
									$('select').selectpicker();

									js_fluid_account_listings_update(data_obj);
									<?php // set is_loading to false to accept new loading ?>

									<?php // If we are auto scrolling when new data is loaded, we need to reset the auto scroll math. ?>
									<?php
									/*
									if(fluid_scroll != null) {
										if(fluid_scroll == true) { // -> header
											$('body,html').stop();
											scrollTo('footer_fluid');
										}
									}
									*/
									?>
								}
							}
						});
					}
			   }
			});
		});

		<?php // Updates various data items. ?>
		function js_fluid_account_listings_update(data_obj) {
			try {
				FluidAccount.item_page = parseInt(Base64.decode(data_obj['item_page']));
				FluidAccount.item_page_next = parseInt(Base64.decode(data_obj['item_page_next']));
				FluidAccount.item_page_previous = parseInt(Base64.decode(data_obj['item_page_previous']));
				FluidAccount.item_start = parseInt(Base64.decode(data_obj['item_start']));
				FluidAccount.total_items = parseInt(Base64.decode(data_obj['total_items']));
				FluidAccount.item_count = parseInt(Base64.decode(data_obj['item_count']));
				FluidAccount.item_count_total = parseInt(Base64.decode(data_obj['item_count']));
			}
			catch(err) {
				js_debug_error(err);
			}
		}

		function js_fluid_account_settings_validator(data) {
			$('#fluid_form_account_update').validator();
			$('#fluid_form_password_update').validator();
		}

		function js_fluid_password_validator_wipe() {
			document.getElementById('fluid_password_update').value = "";
			document.getElementById('fluid_password_again_update').value = "";

			$('#fluid_form_password_update').validator('validate');
			$('#fluid_form_password_update')[0].reset();
		}

		function js_fluid_account_update() {
			$('#fluid_form_account_update').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				// handle the invalid form...
			  } else {
				// everything looks good!
				e.preventDefault(e);

				try {
					var FluidUpdate = {};
						FluidUpdate.u_first_name = document.getElementById('fluid_first_name_update').value;
						FluidUpdate.u_last_name = document.getElementById('fluid_last_name_update').value;

					var data = Base64.encode(JSON.stringify(FluidUpdate));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_account_update&data=" + data}));

					js_fluid_ajax(data_obj);

				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_password_update() {
			$('#fluid_form_password_update').validator().on('submit', function (e) {
			  if (e.isDefaultPrevented()) {
				// handle the invalid form...
			  } else {
				// everything looks good!
				e.preventDefault(e);

				try {
					var FluidUpdate = {};
						FluidUpdate.u_password = document.getElementById('fluid_password_update').value;

					var data = Base64.encode(JSON.stringify(FluidUpdate));

					var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_fluid_account_update_password&data=" + data}));

					js_fluid_ajax(data_obj);

				}
				catch(err) {
					js_debug_error(err);
				}
			  }
			})
		}

		function js_fluid_order_view(s_id) {
			try {
				var data = Base64.encode(JSON.stringify(s_id));

				var data_obj = Base64.encode(JSON.stringify({serverurl: "<?php echo $_SERVER['SERVER_NAME'] . '/' . FLUID_ACCOUNT;?>", dataobj: "load_func=true&fluid_function=php_html_view_order&data=" + data}));

				js_fluid_ajax(data_obj);
			}
			catch(err) {
				js_debug_error(err);
			}
		}
	</script>

	<div class="container-fluid container-search" style="margin-top: 0px; background-color: #f3f1f2;">
		<div class="container">
			<div class="row profile">
				<div class="col-sm-3 col-md-3 fluid-box-shadow">
					<div class="profile-sidebar">

						<!-- SIDEBAR USERPIC -->
						<div class="profile-userpic">
							<?php
							if(isset($_SESSION['u_picture']))
								echo "<img src='" . $_SESSION['u_picture'] . "' style='max-width:" . $_SESSION['u_picture_width'] . "px; max-height:" . $_SESSION['u_picture_height'] . " px;' class=\"img-responsive\">";
							else
								echo "<div style='display:table; margin: 0 auto;'><i class=\"fa fa-user fluid-user-font\"></i></div>";
							?>
						</div>
						<!-- END SIDEBAR USERPIC -->

						<!-- SIDEBAR USER TITLE -->
						<div class="profile-usertitle">
							<div class="profile-usertitle-name" id="fluid-profile-user-name">
								<?php
								if(strlen($_SESSION['u_first_name'] . " " . $_SESSION['u_last_name']) > 25)
									echo utf8_decode(substr($_SESSION['u_first_name'] . " " . $_SESSION['u_last_name'], 0, 25)) . "...";
								else
									echo utf8_decode($_SESSION['u_first_name'] . " " . $_SESSION['u_last_name']);
								?>
							</div>
						</div>
						<!-- END SIDEBAR USER TITLE -->

						<!-- SIDEBAR MENU -->
						<div class="profile-usermenu">
							<ul class="nav" id="fluid-account-menu">
								<?php
								// Only show the user settings option and panel to edit if the account is a FLUID account. Google and Facebook accounts do not need to have access to information since it is provided from there Google or Facebook accounts, and can be changed on those services websites instead.
								if($_SESSION['u_oauth_provider'] == OAUTH_FLUID) {
									echo "<li>";
										$fluid_account_settings_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_html_account_settings")));
										echo "<a onClick='js_fluid_ajax(\"" . $fluid_account_settings_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><i class=\"fa fa-cog\"></i>Account Settings</a>";
									echo "</li>";
								}
								?>

								<?php
								if(isset($_REQUEST['f']))
									if($_REQUEST['f'] == "address")
										echo "<li class='active'>";
									else
										echo "<li>";
								else
									echo "<li>";

									$fluid_account_address_book_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_address_book_account")));
									echo "<a onClick='fluid_disabled = true; js_fluid_ajax(\"" . $fluid_account_address_book_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><i class=\"fa fa-book\"></i>Address Book</a>";
								?>
								</li>

								<?php
								if(isset($_REQUEST['f']))
									if($_REQUEST['f'] == "orders")
										echo "<li class='active'>";
									else
										echo "<li>";
								else
									echo "<li>";

									$fluid_account_my_orders_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_html_account_my_orders&data=" . base64_encode(json_encode(Array("item_page" => 1, "item_count" => 0, "divloader" => TRUE))))));
									echo "<a onClick='fluid_disabled = false; js_fluid_ajax(\"" . $fluid_account_my_orders_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><i class=\"fa fa-shopping-bag\"></i>My Orders</a>";
								?>
								</li>
								<?php
								// --> Wish list, disable for now.
								/*
								<li>
								<?php
									$fluid_account_wish_list_link = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ACCOUNT, "dataobj" => "load_func=true&fluid_function=php_html_account_wish_list")));
									echo "<a onClick='js_fluid_ajax(\"" . $fluid_account_wish_list_link . "\");' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><i class=\"fa fa-flag\"></i>Wish List</a>";
								?>
								</li>
								*/
								?>
							</ul>
						</div>
						<!-- END MENU -->
					</div>
				</div>

				<?php
					// Prep data and load the category specific filters as required.
					$data = (object)Array("item_page" => 1, "item_count" => 0, "divloader" => TRUE);
					if(isset($_REQUEST['f'])) {
						if($_REQUEST['f'] == "address")
							$data_html = json_decode(php_address_book_account($data));
						else if($_REQUEST['f'] == "orders")
							$data_html = json_decode(php_html_account_my_orders($data));
					}


					if(isset($data_html)) {
						echo "<script> FluidAccount.item_count_total = " . base64_decode($data_html->item_count) . "; FluidAccount.item_count = " . base64_decode($data_html->item_count) . "; FluidAccount.item_page_previous = " . base64_decode($data_html->item_page_previous) . "; FluidAccount.item_page = " . base64_decode($data_html->item_page) . "; FluidAccount.item_page_next = " . base64_decode($data_html->item_page_next) . "; FluidAccount.total_items = " . base64_decode($data_html->total_items) . "; FluidAccount.item_start = " . base64_decode($data_html->item_start) . ";</script>";
					}

				?>

				<div class="col-sm-9 col-md-9 fluid-profile-content-padding">
					<div id="fluid-account-content" class="profile-content" style="background-color: transparent;">
					<?php
						if(isset($data_html->bool_found_items)) {
							if($data_html->bool_found_items) {
								echo base64_decode($data_html->html);
							}
							else if(isset($data_html->html))
								echo base64_decode($data_html->html);
						}
						else {
							//echo utf8_encode(base64_decode($data_html->html));
							echo "<div style='height: 40vh;'></div>";
						}
					?>
					</div>

					<div id="fluid_loader" style="margin-bottom: 20px; width: 100%; display:none;"><div style="width: 50px; margin: 0 auto;"><i class="fa fa-refresh fa-spin-fluid fa-3x fa-fw"></i><span class="sr-only">Loading...</span></div></div>

				</div>
			</div>
		</div>
	</div> <!-- container-search end -->


	<?php
	require_once("footer.php");

	echo HTML_MODAL_ADDRESS_BOOK;
	?>

	<?php
	// Load the google maps address auto fill.
	php_google_maps_autofill();
	?>

	</body>
	</html>
<?php
 }
}

/*
--> Potential abuse from brute force attacks to find valid emails. Need to design a system to combat this. Perhaps a token to validate?
*/
function php_validate_email() {
	$fluid = new Fluid ();

	$fluid->php_db_begin();

	$fluid->php_db_query("SELECT u_id FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . OAUTH_FLUID . "' AND u_email = '" . $fluid->php_escape_string($_REQUEST['fluid_email_register']) .  "'");

	$fluid->php_db_commit();

	if(isset($fluid->db_array[0]))
		return http_response_code(404); // Found email, return bad request.
	else
		return http_response_code(200); // Email not found. Return ok request.
}

function php_validate_email_reset() {
	$fluid = new Fluid ();

	$fluid->php_db_begin();

	$fluid->php_db_query("SELECT u_id, u_token FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . OAUTH_FLUID . "' AND u_email = '" . $fluid->php_escape_string($_REQUEST['fluid_email_forgot']) .  "'");

	$fluid->php_db_commit();

	if(isset($fluid->db_array[0]))
		return http_response_code(200); // Found email, return good request.
	else
		return http_response_code(404); // Email not found. Return bad request.
}

function php_validate_reset_code() {
	try {

		$fluid = new Fluid ();

		$fluid->php_db_begin();

		$fluid->php_db_query("SELECT u_id, u_token FROM " . TABLE_USERS . " WHERE u_oauth_provider = '" . OAUTH_FLUID . "' AND u_email = '" . $fluid->php_escape_string(base64_decode($_REQUEST['email'])) .  "' AND u_token_reset = '" . $fluid->php_escape_string($_REQUEST['fluid_email_security_code']) . "'");

		$fluid->php_db_commit();

		if(isset($fluid->db_array[0]))
			return http_response_code(200); // Found email and confirmed token, return ok request.
		else
			return http_response_code(404); // Email and token not found. Return bad request.

	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

?>
