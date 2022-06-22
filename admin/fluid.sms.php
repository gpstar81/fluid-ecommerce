<?php
// fluid.accounts.php
// Michael Rajotte - 2018 Aout
// SMS system that integrates into Twilio api.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");

if(empty($_SESSION['fluid_admin']))
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load']))
	if(function_exists($_REQUEST['function']))
		echo call_user_func($_REQUEST['function']);
	else
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");

function php_sms_panel_load($data = NULL) {
	$fluid = new Fluid();
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

			$sms_array = php_sms_panel_html($f_data);

			$f_modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
				<div class='modal-content'>

					<div class='panel-default'>
					  <div class='panel-heading'>SMS System<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
					</div>

				  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px; padding-top: 0px;'>
					<div id='sms-innerhtml' class='panel panel-default fluid-sms-innerhtml'>

						<div id='smstabs'>
							<div id='smstabs-1'>
								<div id='sms-panel-div'>
									";

									if(TWILIO_ENABLED == TRUE) {
										$f_modal .= $sms_array['smsdata'];
									}
									else {
										$f_modal .= "<div class='container' style='margin-top: 20px;'>SMS System is currently disabled.<br><br>You can configure the system under the Settings->SMS menu.</div>";
									}

								$f_modal .= "
								</div>
							</div>
						</div>

					</div>

				  </div>

				  <div id='f-footer-sms' class='modal-footer'>
					  <div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Close</button></div>
				  </div>

				  </div>
				</div>
			  </div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal"), "innerHTML" => base64_encode($f_modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_sms_panel_reset_css";

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

function php_sms_panel_reload($f_data = NULL) {
    try {
        if(isset($_REQUEST['data']))
            $f_data = json_decode(base64_decode($_REQUEST['data']));
        else if(isset($f_data))
            $f_data = (object)json_decode(base64_decode($f_data));
        else
            $f_data = NULL;

        $innerHTML = php_sms_panel_html($f_data);

        $execute_functions[]['function'] = "js_html_insert_element";
        end($execute_functions);
        $execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("sms-panel-div"), "innerHTML" => base64_encode($innerHTML['smsdata']))));

        return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
    }
    catch (Exception $err) {
        return php_fluid_error($err);
    }
}

function php_sms_timer_check($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$fluid = new Fluid();

		$fluid->php_db_begin();
		$count_query = "SELECT COUNT(smsnum_unread) AS count FROM " . TABLE_SMS_NUMBERS . "  WHERE smsnum_unread > 0";
		$fluid->php_db_query($count_query);
		$fluid->php_db_commit();

		$count_total = 0;

		if(isset($fluid->db_array)) {
			$count_total = $fluid->db_array[0]['count'];
		}

		if(isset($f_data->f_load)) {
			return $count_total;
		}
		else {
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("sms-notification-icon"), "innerHTML" => base64_encode($count_total))));

			$execute_functions[]['function'] = "js_sms_refresh_count";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("count" => $count_total)));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_search($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$sms_data = php_sms_panel_html($f_data);

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("sms-panel-div"), "innerHTML" => base64_encode($sms_data['smsdata']))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Takes a phone number checks if it's a valid number with the Twilio API. The returned phone number is formatted in the E.164 number format standard. Then we send a SMS message to it with the Twilio api.
function php_sms_create_message($f_data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$f_array = NULL;

		$f_phone_number = str_replace(" ", "", $f_data->phone_number);
		$f_phone_number = trim($f_phone_number);

		$base_url = "https://lookups.twilio.com/v1/PhoneNumbers/" . $f_phone_number;

		$ch = curl_init($base_url);
		$sid = TWILIO_ACCOUNT_SID;
		$token = TWILIO_AUTH_TOKEN;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");

		$response = curl_exec($ch);
		$response = json_decode($response);

		if(isset($response->status) == "404") {
			throw new Exception("Not a valid phone number. Please try again.");
		}
		else {
			$fluid = new Fluid();
			$fluid->php_db_begin();

			// Insert the phone number into the database.
			$fluid->php_db_query("INSERT IGNORE INTO " . TABLE_SMS_NUMBERS . " (smsnum_phonenumber, smsnum_snippet, smsnum_name, smsnum_unread) VALUES ('" . $fluid->php_escape_string($response->phone_number) . "', '', '', '0')");

			// Select the ID of the phone number.
			$fluid->php_db_query("SELECT * FROM " . TABLE_SMS_NUMBERS . " WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($response->phone_number) . "'");

			$fluid->php_db_commit();

			if(isset($fluid->db_array)) {
				$f_array = base64_encode(json_encode(Array("f_id" => $fluid->db_array[0]['smsnum_id'], "f_number" => $fluid->db_array[0]['smsnum_phonenumber'], "f_name" => $fluid->db_array[0]['smsnum_name'], "f_mode" => "sms")));
			}

			if(empty($f_array)) {
				throw new Exception("Error, please try again.");
			}
			else {
				$execute_functions[]['function'] = "js_sms_load_sms";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_array));

				return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
			}
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_panel_html($f_data = NULL) {
	try {
		if(empty($f_data->f_team_sms)) {
			if(isset($f_data->f_data)) {
				$f_data = json_decode(base64_decode($f_data->f_data));
			}
		}
		
		$mode = "sms";

		$f_phone_pad = "
		<div id=\"wrapper\">
		    <div class=\"dialpad compact\">
		        <div id='f_sms_number_create' class=\"number\"></div>
		        <div class=\"dials\">
		            <ol style='list-style: none; padding: 0px;'>
		                <li class=\"digits\"><p><strong>1</strong></p></li>
		                <li class=\"digits\"><p><strong>2</strong><sup>abc</sup></p></li>
		                <li class=\"digits\"><p><strong>3</strong><sup>def</sup></p></li>
		                <li class=\"digits\"><p><strong>4</strong><sup>ghi</sup></p></li>
		                <li class=\"digits\"><p><strong>5</strong><sup>jkl</sup></p></li>
		                <li class=\"digits\"><p><strong>6</strong><sup>mno</sup></p></li>
		                <li class=\"digits\"><p><strong>7</strong><sup>pqrs</sup></p></li>
		                <li class=\"digits\"><p><strong>8</strong><sup>tuv</sup></p></li>
		                <li class=\"digits\"><p><strong>9</strong><sup>wxyz</sup></p></li>
		                <li class=\"digits\"><p><strong>*</strong></p></li>
		                <li class=\"digits\"><p><strong>0</strong><sup>+</sup></p></li>
		                <li class=\"digits\"><p><strong>#</strong></p></li>
		                <li class=\"digits\"><p><strong><i class=\"fa fa-refresh\"></i></strong><sup>Clear</sup></p></li>
		                <li class=\"digits\"><p><strong><i class=\"fa fa-times\"></i></strong><sup>Delete</sup></p></li>
		                <li class=\"digits pad-action\"><p><strong><i class=\"fa fa-phone\"></i></strong> <sup>Create</sup></p></li>
		            </ol>
		        </div>
		    </div>
		</div>
		";

		$fluid = new Fluid();
		$fluid->php_db_begin();
		$dateFilter = date("Y-m-d H:i:s", strtotime("-30 days"));
		$where_query = NULL;
		$limit = 50;
		$pagination = NULL;
		$f_search = NULL;
		
		if(isset($f_data->f_page)) {
			$f_page = $f_data->f_page;
		}
		else {
			$f_page = 1;
		}
		
		if(empty($f_data->f_team_sms)) {
			//$data_query = "SELECT * FROM " . TABLE_SMS_NUMBERS . " WHERE smsnum_date >= '" . $dateFilter . "'";
			$data_query = "SELECT * FROM " . TABLE_SMS_NUMBERS;
			
			if(isset($f_data->f_sms_search)) {
				if(strlen($f_data->f_sms_search) > 2) {
					$where_query .= " WHERE (smsnum_name LIKE '%" . $fluid->php_escape_string($f_data->f_sms_search) . "%' OR smsnum_phonenumber LIKE '%" . $fluid->php_escape_string($f_data->f_sms_search) . "%')";
					
					$f_search = $f_data->f_sms_search;
				}
			}
		
			$pagequery = "SELECT COUNT(*) AS num FROM " . TABLE_SMS_NUMBERS. " c " . $where_query;

			$totalpages = 0;
			$fluid->php_db_query($pagequery);

			if(isset($fluid->db_array)) {
				$totalpages = $fluid->db_array[0]['num'];
			}

			//how many items to show per page
			if(isset($f_data->f_page)) {
				$page = $f_data->f_page;
				$start = ($f_data->f_page - 1) * $limit;  // first item to display on this page
			}
			else {
				$page = 0;
				$start = 0; // if no page var is given, set start to 0
			}

			$f_data = Array("f_page" => $page, "f_sms_search" => $f_search);

			$pagination = php_sms_pagination($totalpages, $limit, $f_data, NULL, "js_sms_panel_reload");
			
			$data_query .= $where_query . " ORDER BY smsnum_unread DESC, smsnum_date DESC LIMIT " . $start . ", " . $limit;
		}
		else {
			$where = "WHERE c.id IN (";
			$i = 0;
			foreach($f_data->team_data as $t_data) {
				if($i != 0)
					$where .= ", ";

				$where .= $fluid->php_escape_string($t_data['id']);

				$i++;
			}
			$where .= ")";

			$data_query = "SELECT * FROM " . TABLE_SMS_NUMBERS . " " . $where;
		}

		$fluid->php_db_query($data_query);
		$fluid->php_db_commit();

		$i = 0;
		$smsdataTmp = NULL;

		$smsdata = "<div class=\"panel panel-default\" style='border-radius: 0px; margin-bottom: 0px; border-left: 0px; border-right: 0px; border-top: 0px;'>";
				//if(empty($f_data->f_team_sms)) {
					$smsdata .= "<div class=\"panel-heading\">";
						$search_id = 'sms_search_' . $mode;
						$search_id_hidden = 'sms_search_hidden_' . $mode;
						$search_id_flag_hidden = 'sms_search_flag_hidden_' . $mode;
						$smsdata .= "<input hidden='text' name='" . $search_id_hidden . "' id='" . $search_id_hidden . "'>";
						$smsdata .= "<input hidden='text' name='" . $search_id_flag_hidden . "' id='" . $search_id_flag_hidden . "' value='0'>";

						$smsdata .= "<div class=\"form-group input-group-sm\">";
							$smsdata .= "<div style='display: inline-block;'><input type=\"text\" class=\"form-control f-form-mobile\" placeholder=\"Search\" name='" . $search_id . "' id='" . $search_id . "' size='20' onkeydown='if (event.keyCode == 13)js_sms_search(\"" . $mode . "\");' onblur='if(document.getElementById(\"f_sms_number_create\") != null){js_sms_number_input_init();}' onfocus='$(document).off(\"keydown\"); $(document).off(\"keypress\");'></div>";

							$smsdata .= "<div style='display: inline-block; padding-left: 10px;'><button class=\"btn btn-primary\" id='" . $search_id . "-btn' name='" . $search_id . "-btn' onclick=\"js_sms_search('" . $mode . "');\"><span class=\"glyphicon glyphicon-search\" aria-hidden=\"true\"></span> <div class='f-span-hide-mobile'>Search</div></button></div>";

							$smsdata .= "<div style='float: right;'>";
								$smsdata .= "<button class='btn btn-success' onClick='document.getElementById(\"fluid-sms-window\").innerHTML = Base64.decode(\"" . base64_encode($f_phone_pad) . "\"); js_sms_number_input_init();'><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> <div class='f-span-hide-mobile'>New Message</div></button>";
							$smsdata .= "</div>";
						$smsdata .= "</div>";

						$smsdata .= "<div style='text-align: center;'>" . $pagination . "</div>";
					$smsdata .= "</div>"; // panel-heading
				//}

				$smsdata .= "<div class=\"panel-body\" style='padding: 0 0 0 0;'>";
					$smsdata .= "<div id='fluid-sms-window' style='height: 50%; width: auto; overflow:auto; margin: 0 0 0 0;'>";

						$smsdata .= "<div class=\"list-group\">";
												
								if(isset($fluid->db_array)) {
									foreach($fluid->db_array as $row) {

										$smsmsg = substr($row['smsnum_snippet'], 0, 200); //50 old defaults
										$dateSnippet = date('Y-m-d g:i:s a', strtotime($row['smsnum_date']));
										$dateSnippetRaw = $row['smsnum_date'];

										if(empty($f_data->f_team_sms)) {
											$smsMode = "onclick=\"FluidSMS.f_data_tmp = '" . base64_encode(json_encode(Array("f_number" => $row['smsnum_phonenumber'], "f_id" => $row['smsnum_id'], "f_name" => $row['smsnum_name'], "f_mode" => $mode, "f_page" => $f_page))) . "'; js_sms_load_sms('" . base64_encode(json_encode(Array("f_number" => $row['smsnum_phonenumber'], "f_id" => $row['smsnum_id'], "f_name" => $row['smsnum_name'], "f_mode" => $mode, "f_page" => $f_page, "f_data" => $f_data))) . "')\"";
										}
										else {
											$smsMode = "onclick=\"FluidSMS.f_data_tmp = '" . base64_encode(json_encode(Array("f_number" => $row['smsnum_phonenumber'], "f_id" => $row['smsnum_id'], "f_name" => $row['smsnum_name'], "f_mode" => "team", "f_data" => $f_data->f_data))) . "'; js_sms_load_sms('" . base64_encode(json_encode(Array("f_number" => $row['smsnum_phonenumber'], "f_id" => $row['smsnum_id'], "f_name" => $row['smsnum_name'], "f_mode" => "team", "f_data" => $f_data->f_data, "f_page" => $f_page))) . "')\"";
										}

										$smsdataTmp .= "<a name='tag" . $row['smsnum_id'] . "' class=\"panel-title list-group-item f-sms-no-radius";
											if($row['smsnum_unread'] == 1) {

												if($dateSnippetRaw == $row['smsnum_date'])
													$smsdataTmp .= " active";
											}
										$smsdataTmp .= "\" onmouseover=\"JavaScript:this.style.cursor='pointer';\" style='min-height:40px;' " . $smsMode . ">";

											$smsdataTmp .= "<div class=\"panel-title list-group-item-heading fluid-sms-heading\">";

													if(strlen($row['smsnum_name']) > 0)
														$smsdataTmp .= $row['smsnum_name'];
													else
														$smsdataTmp .= $row['smsnum_phonenumber'];
												$smsdataTmp .= "<div style='float:right;'>" . $dateSnippet . "</div>";
											$smsdataTmp .= "</div>";

											$smsdataTmp .= "<p class=\"list-group-item-text fluid-sms-text\">";

												if(strlen($smsmsg) > 195) //45 old defaults
													$smsmsg .= "...";

												$smsdataTmp .= $smsmsg;

											$smsdataTmp .= "</p>";

										$smsdataTmp .= "</a>";
										$i++;

									}
								}
								$smsdata .= $smsdataTmp . "</div>"; //list-group well


					$smsdata .= "</div>";
				$smsdata .= "</div>"; //panel-body well

		$smsdata .= "</div>"; //panel well

		return Array("smsdata" => $smsdata, "pagination" => $pagination);
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_load($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($f_data->multiplier))
			$multiplier = $f_data->multiplier;
		else
			$multiplier = NULL;

		if(isset($f_data->f_anchor_date))
			$anchordate = $f_data->f_anchor_date;
		else
			$anchordate = NULL;

		$f_data = json_decode(base64_decode($f_data->f_data));

		$phonenumber = $f_data->f_number;
		$clientid = $f_data->f_id;
		$clientname = $f_data->f_name;

		if(empty($clientname))
			$clientname = $phonenumber;

		$mode = 0;

			$smsdata = "<div class='well well-sm' style='border-radius: 0px; margin-bottom: 0px; border-bottom: 0px; border: 0px; padding-bottom: 2px;'>";

					$smsdata .= "<div class=\"pull-right f-sms-top-width\">";

					if($f_data->f_mode == "team") {
						$onclick = "onClick='js_calendar_team_edit(\"" . $f_data->f_data->f_data->f_tid . "\", \"" . $f_data->f_data->f_data->f_date . "\", \"sms\");'";
						$smsdata .= "<button type=\"button\" class=\"btn btn-primary btn-sm\" " . $onclick . " style='float: right;'><span class=\"glyphicon glyphicon-arrow-left\"></span> <div class='f-span-hide-mobile'>Back</div></button>";

						$onclickUnread = "onClick='js_sms_set_unread(\"" . $clientid . "\", \"team\", \"" . base64_encode(json_encode($f_data->f_data)) . "\");'";
						$smsdata .= "<button style='margin-right:15px; float: right;' type=\"button\" class=\"btn btn-danger btn-sm\" " . $onclickUnread . "><span class=\"glyphicon glyphicon-folder-close\"></span> <div class='f-span-hide-mobile'>Unread</div></button>";
					}
					else {
						$onclick = "onClick='js_sms_panel_load(\"1\", \"" . base64_encode(json_encode($f_data->f_data)) . "\");'";
						$smsdata .= "<button type=\"button\" class=\"btn btn-primary btn-sm\" " . $onclick . " style='float: right;'><span class=\"glyphicon glyphicon-arrow-left\"></span> <div class='f-span-hide-mobile'>Back</div></button>";

						$onclickUnread = "onClick='js_sms_set_unread(\"" . $clientid . "\", \"all\", \"" . base64_encode(json_encode($f_data->f_data)) . "\");'";
						$smsdata .= "<button style='margin-right:15px; float: right;' type=\"button\" class=\"btn btn-danger btn-sm\" " . $onclickUnread . "><span class=\"glyphicon glyphicon-folder-close\"></span> <div class='f-span-hide-mobile'>Unread</div></button>";
					}

					$smsdata .= "<button id='f-sms-name-edit-button' class='btn btn-success btn-sm' style='margin-right: 15px; float: right;' onClick='js_sms_name_edit();'><span class=\"glyphicon glyphicon-pencil\" aria-hidden=\"true\"></span> <div class='f-span-hide-mobile'>Edit</div></button>";

					$smsdata .= "</div>";


					$smsdata .= "<div class=\"input-group input-group-sm\">";
						$smsdata .= "<span class=\"input-group-addon f-span-sms-mobile\" id=\"basic-addon1\"><div class='f-span-hide-mobile'>Send From:</div></span>";
						//$smsdata .= "<div>";
							$smsdata .= "<select id='sms_panel_from' class=\"form-control selectpicker\" onchange='document.getElementById(\"hidden-sms-company\").value=(this.options[this.selectedIndex].value);'>";
							$smsdata .= "<option value='0' selected data-content=\"<span><img src='" .  FLUID_COMPANY_ICON . "' style='float:left; padding-right:3px; width: 24px;'></img> " . FLUID_COMPANY_NAME . "</span>\"";
							$smsdata .= "><img src='" . FLUID_COMPANY_ICON . "' style='float:left; padding-right:3px;'></img> <h3 style='float:right;' class='panel-title'>" . FLUID_COMPANY_NAME . "</h3>";
							$smsdata .= "</option>";
							$smsdata .= "</select>";
						//$smsdata .= "</div>";
					$smsdata .= "</div>";

			$smsdata .= "</div>	";

		if($f_data->f_mode == "team")
			$fp_style = "style='border: 0px; padding-bottom: 0px;'";
		else
			$fp_style = "style='border: 0px; padding-bottom: 0px; margin-left: -1px; margin-right: -1px;'";

		$smsdata .= "<div class=\"panel panel-default f-sms-radius-none\" " . $fp_style . ">";

			$smsdata .= "<div class=\"panel-heading f-sms-radius-none\" style='padding-bottom: 0px; padding-top: 2px;'>";
				$smsdata .= "<div class='row'>";

					$smsdata .= "<div style='display: inline-block; padding-left: 10px;'>";
						$smsdata .= "<div style='display: table; width: 100%;'><div style='display: table-cell; height: 30px; vertical-align: middle;'><h3 class=\"panel-title\" style='margin: 0px; font-size: 90%;'><div id='f-sms-name-text' style='display: inline-block;'>To: " . $clientname . "</div><div id='f-sms-name-edit' style='display: none;'><div style='display: table-cell'><div style='display: inline-block;'>To:</div> <div style='display: inline-block;'><input id='f-sms-name-input-edit' class='form-control' style='padding: 4px 4px; height: auto;' type='text' value='" . $clientname . "'></input></div></div><div style='display: table-cell; vertical-align: top;'><button class='btn btn-danger btn-sm' style='margin-left: 10px;' onClick='js_sms_name_edit_cancel();'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> <div class='f-span-hide-mobile'>Cancel</div></button> <button id='f-sms-name-edit-save-btn' class='btn btn-success btn-sm' style='margin-left: 10px;' onClick='js_sms_name_edit_save(\"" . $clientid . "\", \"" . base64_encode(json_encode($f_data)) . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> <div class='f-span-hide-mobile'>Save</div></button></div></div></h3></div></div>";
					$smsdata .= "</div>";

					$smsdata .= "<div id='sms-display-number-div' style='display: inline-block; float: right; padding-right: 10px;'>";
						$smsdata .= "<div style='display: table; width: 100%;'><div style='display: table-cell; height: 30px; vertical-align: middle; text-align: right;'><h3 class=\"panel-title\" id='sms_display_number' name='sms_display_number' style='margin: 0px; font-size: 90%;'>" . $phonenumber . "</h3></div></div>";
					$smsdata .= "</div>";

				$smsdata .= "</div>"; // row
			$smsdata .= "</div>"; // End of panel header

			$smsdata .= "<div class=\"panel-body\" style='width:auto; margin-left:0px; margin-right:0px; margin-top:0px; padding-top:0px; padding-bottom:0px; margin-bottom:0px; padding-right:0px; padding-left:0px;'>";

				// Right frame, lets load the sms message into it.
				$smsdata.= "<div class='sms sms-window' id='sms_scroll' name='sms_scroll'>"; //680 x 800

					// Now lets load the sms message into the right frame.
					if($mode == 0)
						$smsdata.= "<div id='smspopwindow' name='smspopwindow'>";
					else if($mode > 0)
						$smsdata.= "<div id='smspopwindowteam' name='smspopwindowteam'>";

						//$smsdata .= php_sms_window($phonenumber, $clientid, $mode, $multiplier, $anchordate);
						$sms_message_data = php_sms_window($phonenumber, $clientid, $mode, $multiplier, $anchordate);
						$sms_img_swiper_data = $sms_message_data['swiper_array'];
						$smsdata .= $sms_message_data['html'];

						$smsdata.= "</div>";
					$smsdata.= "</div>";

			$smsdata .= "</div>"; // End of panel body.

			// Message Send Box
			$smsdata .= "<div class=\"panel-footer\" style='border-radius: 1px;'>";
				$dataid = "sendsms";

					$smsdata.= "<textarea name='" . $dataid . "-message' id='" . $dataid . "-message' class=\"form-control panel-body\" rows=\"4\" style='border: 1px solid #DADADA !important; width:100%; resize:none; padding: 5px;'></textarea>";

					$smsdata .= "<div class=\"input-group input-group-sm\" style='margin-top:10px; margin-bottom:0px; width: 100%;'>";

				$smsdata .= "<div style='float: left;'>";
				$smsdata .= "<input type='hidden' id='smspopup_phone_number' name='smspopup_phone_number' value='" . base64_encode($phonenumber) . "'>";
				$smsdata .= "<input type='hidden' id='smspopup_client_name' name='smspopup_client_name' value='" . $clientname . "'>";
				$smsdata .= "<input type='hidden' id='smspopup_client_id' name='smspopup_client_id' value='" . $clientid . "'>";

				$smsdata .= "<input type='hidden' id='hidden-sms-company' value='0'>"; // pristine default
				$smsdata .= "</div>";

								$f_html = "<form id=\"f-csv-form\" action=\"" . FLUID_SMS_UPLOADS . "\" method=\"POST\">";
									$f_html .= "<label class='btn btn-primary btn-sm fileinput-button' style='padding: 6px 12px; max-height: 30px;'><input type=\"file\" id=\"f_csv_file_select\" name=\"f_csv_file_select\"/ onChange=\"$('#f_import_file_selected').attr('placeholder', $(this).val());\"><i class='glyphicon glyphicon-picture'></i><div class='btn-txt-hide' style='padding-left: 5px;'>Add Image</div></label> <input id=\"f_import_file_selected\" type='text' class='input-group input-group-sm form-control f-control-width-file' placeholder='Choose image' disabled=\"disabled\" style='float:right; height: 30px;'></input>";

									$f_html .= "<div style='padding-top: 10px; display: none;'><button class='btn btn-primary' type=\"submit\" name='f_upload_button' id=\"f_upload_button\" style='display: none;'>Upload</button></div>";
								$f_html .= "</form>";

					// Preset message button with file upload button.
					//$smsdata .= "<div style='display: inline-block; padding-left: 0px;'><div style='display: inline-block; margin-right: 5px; float: left;'><button type='button' class='btn btn-sm btn-primary' onClick='js_fluid_sms_preset_messages_load();'><span class=\"glyphicon glyphicon-blackboard\" aria-hidden=\"true\"></span> <div class='btn-txt-hide'>Preset Message</div></button></div><div style='display: inline-block;'>" . $f_html . "</div></div>";
					$smsdata .= "<div style='display: inline-block; padding-left: 0px;'><div style='display: inline-block;'>" . $f_html . "</div></div>";

					$smsdata .= "<div style='display: inline-block; float: left; margin-right: 5px;'><button type='button' class='btn btn-sm btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> <div class='btn-txt-hide'>Close</div></button></div>";

					$smsdata .= "<div class=\"input-group input-group-sm\" style='float:right;'>";

					$smsdata .= "<div style='float:right;'><button class=\"btn btn-success btn-sm\" id='sms_send_button_" . $mode . "' name='sms_send_button_" . $mode . "' onclick='$(\"#f_upload_button\").click();'><span class=\"glyphicon glyphicon-send\"></span> Send</button></div>";

					$smsdata .= "</div>";
				$smsdata .= "</div>";

			$smsdata .= "</div>"; // End of panel footer.
		$smsdata .= "</div>"; // End of panel.

		$execute_functions[]['function'] = "js_sms_message_set";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("html" => base64_encode($smsdata), "f_number" => base64_encode($phonenumber), "f_client_id" => $clientid, "f_client_name" => base64_encode($clientname), "f_multiplier" => $multiplier, "f_mode" => $mode, "f_swiper_array" => base64_encode(json_encode($sms_img_swiper_data)), "f_anchor_date" => $anchordate, "f_team" => $f_data->f_mode)));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_name_edit($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));
		if(isset($f_data->f_data64))
			$f_data->f_data64 = json_decode(base64_decode($f_data->f_data64));

		if(isset($f_data->f_client_id)) {
			$fluid = new Fluid();

			$fluid->php_db_begin();
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_name = '" . $fluid->php_escape_string(trim($f_data->f_name)) . "', smsnum_date = smsnum_date WHERE smsnum_id = '" . $fluid->php_escape_string($f_data->f_client_id) . "'");
			$fluid->php_db_commit();

			if(isset($f_data->f_data64)) {
				$f_data->f_data64->f_name = trim($f_data->f_name);

				$execute_functions[]['function'] = "js_sms_load_sms";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_data->f_data64))));
			}
			else
				$execute_functions[]['function'] = "js_sms_panel_load"; // Load the SMS panel as a fail safe if the f_data was never sent.

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else
			throw new Exception("Error saving the new name. Please try again.");
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_set_unread($data = NULL) {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));
		if(isset($f_data->f_data64))
			$f_data->f_data64 = json_decode(base64_decode($f_data->f_data64));

		if(isset($f_data->f_client_id)) {
			$fluid = new Fluid();

			$fluid->php_db_begin();
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_unread = 1, smsnum_date = smsnum_date WHERE smsnum_id = '" . $fluid->php_escape_string($f_data->f_client_id) . "'");
			$fluid->php_db_commit();

			//if(isset($f_data->f_data64)) {
			if($f_data->f_mode == "team") {
				$execute_functions[]['function'] = "js_calendar_team_edit_reload";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($f_data->f_data64->f_data));
			}
			else {
				//$execute_functions[]['function'] = "js_sms_panel_load";
				$execute_functions[]['function'] = "js_sms_panel_load_reset"; // Load the SMS panel as a fail safe if the f_data was never sent.
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_data->f_data64))));
			}

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else
			throw new Exception("Error setting unread for user");
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_window($phone_number, $client_id, $mode, $multiplier = NULL, $smsanchordate = NULL) {
	try {
		$fluid = new Fluid();
		$fluid->php_db_begin();

		$msgdisplaymax = 30;
		$swiper_array = NULL;

		if(empty($multiplier))
			$multiplier = 0;

		$msgstart = $msgdisplaymax * $multiplier;

		// Update account flag that unread messages have been read. Only if it has been read / loaded on the screen.
		if(isset($client_id)) {
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_unread = 0, smsnum_date = smsnum_date WHERE smsnum_id = '" . $fluid->php_escape_string($client_id) . "'");
		}

		$data_query = "SELECT a.*, b.* FROM " . TABLE_SMS . " a, " . TABLE_SMS_NUMBERS . " b WHERE a.sms_num_id = '" . $fluid->php_escape_string($client_id) . "' AND b.smsnum_id = '" . $fluid->php_escape_string($client_id) . "'";

		if(isset($smsanchordate)) {
			if(strlen($smsanchordate) > 1) {
				$datefilter = explode(" ", $smsanchordate);
				$datefilter[0] = date("Y-m-d", strtotime($datefilter[0] . "+ 1 day"));
				$data_query .= " AND a.sms_date < '" . str_replace("/", "-", $datefilter[0]) . "'";
			}
		}

		$data_query .= " ORDER by a.sms_id DESC LIMIT " . $fluid->php_escape_string($msgstart) . ", " . $fluid->php_escape_string($msgdisplaymax);

		$fluid->php_db_query($data_query);
		$fluid->php_db_commit();

		$smsdata = "<table class='sms' border=0 cellspacing=8 cellpadding=8 width=100%>";

		$colour_tag = "#EEE390";
		$colour_selected = "#288528";

		if($msgstart > 0) {
			$smsdata.= "<tr class='sms'>";
			$smsdata.= "<td class='sms' align='center' style='padding-top:10px; padding-bottom:10px; padding-right:10px; padding-left:10px;'>";

				$smsdata.= "<table class='sms' border=0 cellpadding=0 cellspacing=0><tr class='sms'>";
				$smsdata.= "<td class='sms' onclick=\"js_sms_load_more('" . $mode . "', '0')\"; onmouseover='JavaScript:this.style.cursor=\"pointer\";' style='background-color: " . $colour_tag . "; border:1px solid #2F2F2F; border-radius: 5px 5px 5px 5px; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px;'>";
				$smsdata.= "<i>... load newer messages ...</i>";
				$smsdata.= "</td></tr></table>";

			$smsdata.= "</td>";
			$smsdata.= "</tr>";
		}

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $row) {

				$smsdata.= "<tr class='sms'>";

				if($row['sms_from'] == $phone_number) {
					$align = "left";
					$colour = "#D1FF8B";
					if(strlen($row['smsnum_name']) > 0) {
						$name = $row['smsnum_name'];
					}
					else {
						$name = $row['sms_from'];
					}
				}
				else {
					$align = "right";
					$colour = "#8BD6FF";
					$name = SMS_MESSAGE_NAME_TAG;
				}
				$smsdata.= "<td class='sms' align='" . $align . "' style='padding-top:10px; padding-bottom:10px; padding-right:10px; padding-left:10px;'>";
					$smsdata.= "<table class='sms' border=0 cellpadding=0 cellspacing=0><tr class='sms'>";
					$smsdata.= "<td class='sms' style='width: 200px; background-color: " . $colour . "; border:1px solid #2F2F2F; border-radius: 15px 15px 15px 15px; padding-top:10px; padding-bottom:10px; padding-left:10px; padding-right:10px;'>";
						$smsdata.= "<table class='sms' border=0 cellspacing=0 cellpadding=0 width=100%><tr class='sms'><td class='sms' style='font-size:10px;'>";
							$smsdata.= $name;
						$smsdata.= "</td>";

						if($row['sms_status'] == "sent")
							$smsStatus = "<span class=\"glyphicon glyphicon-plane\"></span>";
						else if($row['sms_status'] == "queued")
							$smsStatus = "<span class=\"glyphicon glyphicon-time\"></span>";
						else if($row['sms_status'] == "received")
							$smsStatus = "<span class=\"glyphicon glyphicon-ok-circle\"></span>";
						else if($row['sms_status'] == "delivered")
							$smsStatus = "<span class=\"glyphicon glyphicon-ok-circle\"></span>";
						else if($row['sms_status'] == "undelivered")
							$smsStatus = "<span class=\"glyphicon glyphicon-warning-sign\"></span>";
						else
							$smsStatus = "<span class=\"glyphicon glyphicon-exclamation-sign\"></span>";

						$translated = "";
						$translatedIcon = "";
						if($row['sms_from'] == $phone_number)	{
							$smsdata .= "<td align='right'><small style='font-size:10px; font-style: italic;'>" . $smsStatus . " " . $row['sms_status'] . "</small></td>";
							$translated = $row['sms_body'];
						}
						else {
							$smsdata .= "<td align='right'><small style='font-size:10px; font-style: italic;'>" . $smsStatus . " " . $row['sms_status'] . "</small></td>";
							$translated = $row['sms_body'];
						}


						$smsdata .= "</tr><tr class='sms'><td class='sms' style='padding-top:3px;' width=100% colspan='2'>";

						$smsdata .= $translated;

							if($row['sms_media_url']) {
								$smsdata .= "<a target='_blank' href=\"" . $_SESSION['fluid_uri'] . "mms/" . $row['sms_media_url'] . "\"><img class=\"img-responsive\" src='" . $_SESSION['fluid_uri'] . "mms/" . $row['sms_media_url'] . "'/></a>";

								//$swiper_array[] = $row['id'];
							}
						$smsdata.= "</td></tr><tr class='sms'><td class='sms' style='font-size:10px; padding-top:3px;' width=100% colspan='2'>";
							$smsdata .= "<table class='sms' cellspacing=0 cellpadding=0 width=100%><tr class='sms'><td class='sms' align='left' style='vertical-align:middle;'>";
							$smsdata.= date('Y-m-d g:i:s a', strtotime($row['sms_date']));
							$smsdata .= "</td>";
							/*
							if($row['sms_from'] == $phone_number) {
								$smsdata .= "<td class='sms' align='right' valign='top' style='vertical-align:middle;'>" . FLUID_COMPANY_NAME . "</td><td style='padding-left:3px;'><img src='" . FLUID_COMPANY_ICON . "'></img></td>";
							}
							*/
							$smsdata .= "</tr></table>";
						$smsdata.= "</td></tr></table>";
					$smsdata.= "</td>";
					$smsdata.= "</tr></table>";
				$smsdata.= "</td>";

				$smsdata.= "</tr>";
			}
		}

		$fluid->php_db_begin();
		$count_query = "SELECT COUNT(sms_id) AS count FROM " . TABLE_SMS . " WHERE sms_num_id = '" . $fluid->php_escape_string($client_id) . "'";
		$fluid->php_db_query($count_query);
		$fluid->php_db_commit();

		$count_total = 0;

		if(isset($fluid->db_array))
			$count_total = $fluid->db_array[0]['count'];

		if($count_total > ($msgdisplaymax * ($multiplier + 1))) {
			$smsdata.= "<tr class='sms'>";
			$smsdata.= "<td class='sms' align='center' style='padding-top:10px; padding-bottom:10px; padding-right:10px; padding-left:10px;'>";

				$smsdata.= "<table class='sms' border=0 cellpadding=0 cellspacing=0><tr class='sms'>";
				$smsdata.= "<td class='sms' onclick=\"js_sms_load_more('" . $mode . "', '1')\"; onmouseover='JavaScript:this.style.cursor=\"pointer\";' style='background-color: " . $colour_tag . "; border:1px solid #2F2F2F; border-radius: 5px 5px 5px 5px; padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px;'>";
				$smsdata.= "<i>... load older messages ...</i>";
				$smsdata.= "</td></tr></table>";

			$smsdata.= "</td>";
			$smsdata.= "</tr>";
		}

		$smsdata.= "</table>";

		return Array("html" => $smsdata, "swiper_array" => $swiper_array);;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_send($data = NULL) {
	$f_data = json_decode(base64_decode($_REQUEST['data']));
	$f_data->f_data_tmp = json_decode(base64_decode($f_data->f_data_tmp));

	$fluid = new Fluid();

	// Initiate a new Twilio Rest Client
	$client = new Services_Twilio(TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN);

	$company = $f_data->company;

	// Your Twilio Number or Outgoing Caller ID
	$from = TWILIO_NUMBER;

	$to = base64_decode($f_data->phone_number);
	$body = base64_decode($f_data->sms_data);
	$options = array('StatusCallback' => WWW_ADMIN_SITE . FLUID_SMS_CALLBACK);

	$name = $f_data->f_data_tmp->f_name;

	$image = NULL;
	if(strlen($f_data->f_files) > 0)
		$image = $f_data->f_files;

	$client_id = $f_data->id;

	try {
		// If sending a image, lets send it.
		if(isset($image)) {
			$imagesend = WWW_ADMIN_SITE . "mms/" . $image;

			$client->account->messages->create(array(
				'To' => $to,
				'From' => $from,
				'Body' => $body,
				'MediaUrl' => $imagesend,
				'StatusCallback' => WWW_ADMIN_SITE . FLUID_SMS_CALLBACK,
			));
		}
		else {
			$client->account->messages->create(array(
				'To' => $to,
				'From' => $from,
				'Body' => $body,
				'StatusCallback' => WWW_ADMIN_SITE . FLUID_SMS_CALLBACK,
			));
		}

		/*
		// Example response data from a Twilio request.
			[sid] => SMf13bcc1af5114937c320e87f94ae9f9c
			[date_created] => Tue, 09 Dec 2014 17:16:38 +0000
			[date_updated] => Tue, 09 Dec 2014 17:16:38 +0000
			[date_sent] =>
			[account_sid] => AC0b523d9f288d8c5954b6764b5ba65b06
			[to] => +17029081880
			[from] => +17023235443
			[body] => sms server test 299
			[status] => queued
			[direction] => outbound-api
			[api_version] => 2010-04-01
			[price] =>
			[price_unit] => USD
			[uri] => /2010-04-01/Accounts/AC0b523d9f288d8c5954b6764b5ba65b06/SMS/Messages/SMf13bcc1af5114937c320e87f94ae9f9c.json
			[num_segments] => 1
		*/

		$fluid->php_db_begin();

		// Insert message into message database.
		$fluid->php_db_query("INSERT INTO `" . TABLE_SMS . "` (sms_num_id, sms_account_id, sms_message_id, sms_status, sms_body, sms_from, sms_to, sms_media_url) VALUES ('" . $fluid->php_escape_string($client_id) . "', '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->account_sid) . "', '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->sid) . "', '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->status) . "', '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->body) . "', '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->from) . "', '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->to) . "', '" . $fluid->php_escape_string($image) . "')");

		// Update last message snippet, store in address book table.
		if($name != NULL)
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_snippet = '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->body) . "', smsnum_name = '" . $fluid->php_escape_string($name) . "' WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->to) . "'");
		else
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_snippet = '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->body) . "' WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($client->account->sms_messages->client->last_response->to) . "'");

		$fluid->php_db_commit();

		$execute_functions[]['function'] = "js_sms_load_sms";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_data->f_data_tmp))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));

	} catch (Exception $e) {
		$fluid->php_db_begin();
		$fluid->php_db_query("INSERT INTO `" . TABLE_SMS . "` (sms_num_id, sms_account_id, sms_message_id, sms_status, sms_body, sms_from, sms_to, sms_media_url) VALUES ('" . $client_id . "', '', '', 'error', '" . $fluid->php_escape_string($body) . "', '" . $from . "', '" . $to . "', '" . $image . "')");

		// Update last message snippet, store in address book table.
		if($name != NULL)
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_snippet = '" . $fluid->php_escape_string($body) . "', smsnum_name = '" . $fluid->php_escape_string($name) . "' WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($to) . "'");
		else
			$fluid->php_db_query("UPDATE `" . TABLE_SMS_NUMBERS . "` SET smsnum_snippet = '" . $fluid->php_escape_string($body) . "' WHERE smsnum_phonenumber = '" . $fluid->php_escape_string($to) . "'");


		$fluid->php_db_commit();

		return php_fluid_error($e->getMessage());
	}
}

function php_sms_preset_messages_load($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$fluid = new Fluid();

		$fluid->php_db_begin();

		//$fluid->php_db_query("SELECT id, message FROM preset_messages ORDER BY id DESC");

		$fluid->php_db_commit();

		$f_preset_html = "<div id='messagelistupdate'>";
			$f_preset_html .= "<div class=\"input-group input-group-sm\">";
				$f_preset_html .= "<select class=\"form-control selectpicker\" name='presetmessages' id='presetmessages' onchange='document.getElementById(\"messageeditor\").value = Base64.decode(this.value);' data-width=\"auto\">";
				$f_preset_html .= "<option value='" . base64_encode('') . "' data-id=null data-content=\"<span class='panel-title'>-Select a message to edit-</span>\"><h3 class='panel-title'>-Select a message to edit-</h3></option>";

				if(isset($fluid->db_array)) {
					foreach($fluid->db_array as $f_preset) {
						$f_preset_html .= "<option data-id=\"" . $f_preset['id'] . "\" data-content=\"<span class='panel-title'>" . substr($f_preset['message'], 0, 30) . "...</span>\" value='" . base64_encode($f_preset['message']) . "'><h3 class='panel-title'>" . substr($f_preset['message'], 0, 30) . "...</span></option>";

					}
				}

				$f_preset_html .= "</select>";
			$f_preset_html .= "</div>";
		$f_preset_html .= "</div>";

		$f_modal = "<div class='modal-dialog f-dialog' id='preset-editing-dialog' role='document'>
				<div class='modal-content'>

					<div class='panel-default'>
					  <div class='panel-heading'><span class=\"glyphicon glyphicon-blackboard\"></span> Preset SMS Messages<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
					</div>

				  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px; padding-top: 0px;'>
					<div id='pastdue-innerhtml' class='panel panel-default fluid-sms-innerhtml'>

						<div id='pastduetabs'>
							<div id='pastduetabs-1'>
								<div id='pastdue-panel-div' style='margin: 10px;'>
									" . $f_preset_html . "
								</div>

								<div style='margin: 10px;'>
									<textarea name=\"messageeditor\" id=\"messageeditor\" class=\"form-control panel-body\" rows=\"6\" style='border: 1px solid #DADADA !important; width:100%; resize: none; margin-top:10px; min-height: 240px;'></textarea>
								</div>

								<div style='text-align: right; margin: 10px;'>
									<button id=\"updatemessagebutton\"  class=\"btn btn-primary\" onclick=\"js_fluid_preset_update_message();\"><span class=\"glyphicon glyphicon-save\" aria-hidden=\"true\"></span> Update</button>
								</div>

							</div>
						</div>

					</div>

				  </div>

				  <div id='f-footer-sms' class='modal-footer'>
					  <div style='float:left;'><button type='button' class='btn btn-danger' onClick='js_modal_hide(\"#fluid-confirm-modal\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
					   <div style='float:right;'><button type='button' class='btn btn-success' onClick='document.getElementById(\"sendsms-message\").value = document.getElementById(\"messageeditor\").value; js_modal_hide(\"#fluid-confirm-modal\"); js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Use Message</button></div>
				  </div>

				  </div>
				</div>
			  </div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-confirm-modal"), "innerHTML" => base64_encode($f_modal))));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-confirm-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_preset_messages_update($data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		if(empty($f_data->f_id))
			throw new Exception("Error updating preset message. Please try again.");
		else if(is_numeric($f_data->f_id) == FALSE)
			throw new Exception("Error updating preset message. Please try again.");
		else {
			$fluid = new Fluid();

			$fluid->php_db_begin();

			//$fluid->php_db_query("UPDATE `preset_messages` SET message = '" . $fluid->php_escape_string($f_data->f_message) . "' WHERE id = '" . $fluid->php_escape_string($f_data->f_id) . "'");

			$fluid->php_db_commit();

			$execute_functions[]['function'] = "js_modal_hide";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-confirm-modal"));

			$execute_functions[]['function'] = "js_fluid_sms_preset_messages_load";

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_sms_pagination($total_pages, $limit, $f_data, $targetpage, $functionname) {
    // Setup page vars for display.
	$page = $f_data['f_page'];

    if ($page == 0)
        $page = 1;								//if no page var is given, default to 1.

    $prev = $page - 1;							//previous page is page - 1
    $next = $page + 1;							//next page is page + 1
    $lastpage = ceil($total_pages/$limit);		//lastpage is = total pages / items per page, rounded up.
    $lpm1 = $lastpage - 1; 						//last page minus 1
    $adjacents = 2;

    // Now we apply our rules and draw the pagination object. We're actually saving the code to a variable in case we want to draw it more than once.
    $pagination = "";
    if($lastpage > 1) {
        $pagination .= "<div class=\"fluid-pagination\">";
        //previous button
        if($page > 1) {
			$f_tmp = $f_data;
			$f_tmp['f_page'] = 1;
			$f_tmp = base64_encode(json_encode($f_tmp));

			$f_tmp_prev = $f_data;
			$f_tmp_prev['f_page'] = $prev;
			$f_tmp_prev = base64_encode(json_encode($f_tmp_prev));

            $pagination.= "<div style='display: inline-block;'><a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">« <div class='f-pagination-hide'>First</div></a><a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp_prev . "');\">« <div class='f-pagination-hide'>Previous</div></a></div>";
		}
        else {
            $pagination.= "<div style='display: inline-block;'><span class=\"disabled f-pagination-block\">« <div class='f-pagination-hide'>First</div></span><span class=\"disabled f-pagination-block\">« <div class='f-pagination-hide'>Previous</div></span></div>";
		}

        $pagination .= "<div class='f-hide-pagination'>";
        // pages
        // not enough pages to bother breaking it up
        if ($lastpage < 7 + ($adjacents * 2)) {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page) {
                    $pagination.= "<span class=\"current\">$counter</span>";
				}
                else {
					$f_tmp = $f_data;
					$f_tmp['f_page'] = $counter;
					$f_tmp = base64_encode(json_encode($f_tmp));

                    $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">$counter</a>";
				}
            }
        }
        else if($lastpage > 5 + ($adjacents * 2)) { // enough pages to hide some

            //close to beginning; only hide later pages
            if($page < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $page) {
                        $pagination.= "<span class=\"current\">$counter</span>";
					}
                    else {
						$f_tmp = $f_data;
						$f_tmp['f_page'] = $counter;
						$f_tmp = base64_encode(json_encode($f_tmp));

                        $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">$counter</a>";
					}
                }
                $pagination.= "...";

				$f_tmp_lpm = $f_data;
				$f_tmp_lpm['f_page'] = $lpm1;
				$f_tmp_lpm = base64_encode(json_encode($f_tmp_lpm));

                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp_lpm . "');\">$lpm1</a>";

				$f_tmp_last = $f_data;
				$f_tmp_last['f_page'] = $lastpage;
				$f_tmp_last = base64_encode(json_encode($f_tmp_last));

                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp_last . "');\">$lastpage</a>";
            }
            else if($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) { // in middle; hide some front and some back
				$f_tmp = $f_data;
				$f_tmp['f_page'] = 1;
				$f_tmp = base64_encode(json_encode($f_tmp));
                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">1</a>";

				$f_tmp = $f_data;
				$f_tmp['f_page'] = 2;
				$f_tmp = base64_encode(json_encode($f_tmp));
                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">2</a>";

                $pagination.= "...";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                    if ($counter == $page) {
                        $pagination.= "<span class=\"current\">$counter</span>";
					}
                    else {
						$f_tmp = $f_data;
						$f_tmp['f_page'] = $counter;
						$f_tmp = base64_encode(json_encode($f_tmp));

                        $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">$counter</a>";
					}
                }
                $pagination.= "...";

				$f_tmp = $f_data;
				$f_tmp['f_page'] = $lpm1;
				$f_tmp = base64_encode(json_encode($f_tmp));
                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">$lpm1</a>";

				$f_tmp = $f_data;
				$f_tmp['f_page'] = $lastpage;
				$f_tmp = base64_encode(json_encode($f_tmp));
                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">$lastpage</a>";
            }
            else { //close to end; only hide early pages
				$f_tmp = $f_data;
				$f_tmp['f_page'] = 1;
				$f_tmp = base64_encode(json_encode($f_tmp));
                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">1</a>";

				$f_tmp = $f_data;
				$f_tmp['f_page'] = 2;
				$f_tmp = base64_encode(json_encode($f_tmp));
                $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">2</a>";
                $pagination.= "...";
                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $pagination.= "<span class=\"current\">$counter</span>";
					}
                    else {
						$f_tmp = $f_data;
						$f_tmp['f_page'] = $counter;
						$f_tmp = base64_encode(json_encode($f_tmp));
                        $pagination.= "<a onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\">$counter</a>";
					}
                }
            }
        }
        $pagination .= "</div>";
        // last button
        if ($page < $counter - 1) {
			$f_tmp = $f_data;
			$f_tmp['f_page'] = $next;
			$f_tmp = base64_encode(json_encode($f_tmp));

			$f_tmp_last = $f_data;
			$f_tmp_last['f_page'] = $lastpage;
			$f_tmp_last = base64_encode(json_encode($f_tmp_last));

            $pagination.= "<div style='display: inline-block;'><a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp . "');\"><div class='f-pagination-hide'>Next</div> »</a>&nbsp;<a class=\"f-pagination-block\" onmouseover=\"JavaScript:this.style.cursor='pointer'\" onclick=\"" . $functionname . "('" . $f_tmp_last . "');\"><div class='f-pagination-hide'>Last</div> »</a></div>";
		}
        else {
            $pagination.= "<div style='display: inline-block;'><span class=\"disabled f-pagination-block\"><div class='f-pagination-hide'>Next</div> »</span><span class=\"disabled f-pagination-block\"><div class='f-pagination-hide'>Last</div> »</span></div>";
		}

        $pagination.= "</div>";

        if($page == $lastpage)
            $f_items_on_page = $total_pages;
        else
            $f_items_on_page = $limit * $page;

        $pagination .= "<div class='f-pagination-footer'>Page " . $page . " of " . $lastpage . " | " . (($limit * $page) - $limit + 1) . " - " . $f_items_on_page . " of " . $total_pages . " items</div>";
    }

    return $pagination;
}
?>
