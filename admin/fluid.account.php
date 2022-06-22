<?php
// fluid.accounts.php
// Michael Rajotte - 2017 Octobre
// Loads ajax php code.

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


function php_load_accounts($data = NULL) {
	$fluid = new Fluid();
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$mode = "accounts";
		if(isset($f_data->f_page_num)) {
			$f_page = $f_data->f_page_num;
			$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
		}
		else {
			$f_page = 0;
			$f_start = 0;
		}

		$f_tmp_count = 0;

		$fluid->php_db_begin();
		if(isset($f_data->query_count))
			$f_query_count = $f_data->query_count;
		else
			$f_query_count = "SELECT COUNT(*) AS tmp_u_count FROM " . TABLE_USERS;

		$fluid->php_db_query($f_query_count);

		if(isset($fluid->db_array))
			$f_tmp_count = $fluid->db_array[0]['tmp_u_count'];

		if(isset($f_data->query))
			$f_query = $f_data->query;
		else
			$f_query = "SELECT * FROM " . TABLE_USERS . " ORDER BY u_created ASC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;

		$fluid->php_db_query($f_query);

		$fluid->php_db_commit();

		$tmp_array = Array();
		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $value)
				$tmp_array[] = $value;
		}

		$tmp_selection_array = NULL;
		if(isset($f_data->f_selection)) {
			$selection_data = $f_data->f_selection;

			if(isset($selection_data))
				foreach($selection_data->p_selection as $product) {
					$tmp_selection_array[$product->p_id] = $product->p_id;
				}
		}
		else
			$selection_data = NULL;

		if(isset($f_data->pagination_function))
			$f_pagination_function = $f_data->pagination_function;
		else
			$f_pagination_function = "js_fluid_load_accounts";

		$return = "<div id='fluid-category-listing' class='list-group'>";
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-users'><li>";

				if(isset($tmp_selection_array))
					$return .= "<div id='category-a-accounts' style='height: 40px; background-color: " . COLOUR_SELECTED_CATEGORY . ";' class='list-group-item'>";
				else
					$return .= "<div id='category-a-accounts' style='height: 40px;' class='list-group-item'>";

					$return .= "<span id='category-badge-count-accounts' class='badge'>" . $f_tmp_count . "</span>";

					if(isset($tmp_selection_array))
						$return .= "<span id='category-badge-select-count-accounts' class='badge'>" . count($tmp_selection_array) . " selected</span>";
					else
						$return .= "<span id='category-badge-select-count-accounts' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-accounts' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$return .= " <span id='category-span-open-accounts' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Accounts</div></span>";
				$return .= "</div>";
			$return .= "</li></ul>";

			$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode) . "</div>";

			$return .= "<div id='category-div-users'>";
			$return .= php_html_accounts($tmp_array, $selection_data, "accounts");
			$return .= "</div>";

		$return .= "</div>";

		$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode) . "</div>";

		if(isset($f_data->f_refresh)) {
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("content-div"), "innerHTML" => base64_encode($return))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else {
			$breadcrumbs = "<li><a href='index.php'>Home</a></li>";
			$breadcrumbs .= "<li class='active'>Accounts</li>";

			// Follow up functions to execute on a server response back to the user.
			$execute_functions[]['function'] = "js_clear_fluid_selection";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

			$execute_functions[]['function'] = "js_html_style_show";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("div_id_hide" => "navbar-menu-right")));

			/*
			$sort_return['categories'][base64_encode('banners')]['div'] = base64_encode("#cat-banners");
			$execute_functions[]['function'] = "js_sortable_banners";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($sort_return));
			*/

			return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input("accounts")), "navbarright" => base64_encode(php_html_navbar_right("accounts")), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// This takes a array of data and displays them in a neatly formatted html table.
function php_html_accounts($data_array, $selection_array = NULL, $mode = NULL) {
	$fluid = new Fluid();
	try {
		$fluid_mode = new Fluid_Mode($mode);

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();
		if(isset($selection_array))
			foreach($selection_array->p_selection as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		// Used for passing the orders in to the select all products js functions.
		$tmp_product_array = Array();
		$p_catmfgid = NULL;

		if(isset($data_array)) {
			//if(isset($data_array[0]) && $fluid_mode->mode != "orders")
				//$p_catmfgid = (int)$data_array[0][$fluid_mode->p_catmfg_id];
			//else
				$p_catmfgid = $fluid_mode->mode;

			//foreach($data_array as $value)
				//$tmp_product_array[$value['b_id']] = 1;//$value['p_enable']; --> Perhaps use $value['s_status'] instead?
		}

		$return = "<div class='table-responsive panel panel-default'>";

		$return .= "<table class='table table-hover' id='cat-" . $p_catmfgid . "'>";

		if(count($data_array) == 0)
			$return .= "<tr><td>" . $fluid_mode->msg_no_products . "</td></tr>";
		else {
			$return .= "<thead>";
			$return .= "<tr style='font-weight: bold;'>";

			//$return .= "<td style='text-align:center;'>" . $select_button . "</td>";

			$return .= "<td style='text-align:center;'>uAuth</td><td style='text-align:center;'>Name</td><td style='text-align:center;'>Email</td><td>Created</td></tr>";
			$return .= "</thead>";
			$return .= "<tbody>";

			$return .= php_html_account_rows($data_array, $selection_array, $mode);

			$return .= "</tbody>";
		}

		$return .= "</table>";
		$return .= "</div>";

		return $return;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Generates each individual row of data.
function php_html_account_rows($data_array = NULL, $selection_array = NULL, $mode = NULL, $td_only = FALSE) {
	try {
		$fluid = new Fluid();
		$return = NULL;

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();
		if(isset($selection_array))
			foreach($selection_array->p_selection as $product) {
				$tmp_selection_array[$product->p_id] = $product->p_id;
			}

		foreach($data_array as $value) {
			if(in_array($value['u_id'], $tmp_selection_array)) {
				$d_colour = " data-colour='transparent';";

				//if($value['b_enable'] == 0)
					//$d_colour = " data-colour='" . COLOUR_DISABLED_ITEMS . "';";
				//else
					$d_colour = " data-colour='transparent';";

				$style = "style='font-style: italic; background-color: " . COLOUR_SELECTED_ITEMS . ";' " . $d_colour;
				$checked = "checked";
			}
			else {
				$d_colour = " data-colour='transparent';";
				$o_colour = "transparent";
				/*
				if($value['b_enable'] == 0) {
					$d_colour = " data-colour='" . COLOUR_DISABLED_ITEMS . "';";
					$o_colour = COLOUR_DISABLED_ITEMS;
				}
				else
				*/
					$d_colour = " data-colour='transparent';";

				$style = "style='background-color: " . $o_colour . ";' " . $d_colour;
				$checked = "";
			}

			$p_catmfgid = $mode;

			if($td_only == FALSE)
				$return .= "<tr id='p_id_tr_" . $value['u_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_cancel_event(event); js_accounts_select(\"" . $value['u_id'] . "\", \"" . $p_catmfgid . "\", \"1\");'>";

			// Hide this column. It contains data used for sortable updates.
			$return .= "<td class='f-td' style='display:none;' id='p_id_tr_" . $value['u_id'] . "_td'>" . base64_encode(json_encode(Array('u_id' => $value['u_id'], 'u_oauth_provider' => $value['u_oauth_provider']))) . "</td>";

			$return .= "<td id='bo-td-uauth-" . $value['u_id'] . "' style='text-align:center;'>" . $value['u_oauth_provider'] . "</td><td style='text-align:center;'>" . utf8_decode($value['u_first_name']) . " " . utf8_decode($value['u_last_name']) . "</td><td style='text-align:center;'>" . utf8_decode($value['u_email']) . "</td><td>" . $value['u_created'] . "</td>";

			//$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_BANNER, "dataobj" => "load=true&function=php_load_banners_creator&data=" . json_encode(base64_encode($value['b_id'])))));

			//$return .= "<td style='text-align:center;'><button type='button' class='btn btn-primary' onClick='js_cancel_event(event); js_fluid_ajax(\"" . $temp_url . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> <div class='f-btn-text'>Edit</div></button></td>";

			if($td_only == FALSE)
				$return .= "</tr>";
		}

		return $return;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_load_email_creator($f2_data = NULL) {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($f2_data))
			$f_data = (object)json_decode(base64_decode($f2_data));
		else
			$f_data = NULL;

		$fluid = new Fluid();

		$f_emails = NULL;
		if(isset($f_data->f_email_list)) {
			$where = "WHERE u_id IN (";
			$i = 0;

			$fluid->php_db_begin();

			foreach($f_data->f_email_list as $f_users) {

				if($i != 0)
					$where .= ", ";

				$where .= $fluid->php_escape_string($f_users->p_id);

				$i++;
			}
			$where .= ")";

			$fluid->php_db_query("SELECT u_first_name, u_last_name, u_email, u_id FROM " . TABLE_USERS . " " . $where . " ORDER BY u_id ASC");
			$fluid->php_db_commit();

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $f_users) {
					$f_emails[$f_users['u_id']] = $f_users;
				}
			}
			else
				throw new Exception("ERROR. Please select some accounts to send this email to.");
		}
		else if(isset($f_data->s_email)) {
			// Do nothing, continue loading the editor.
		}
		else
			throw new Exception("ERROR. Please select some accounts to send this email to.");

		$editor = FALSE;
		$data = NULL;
		$modal_title = "Email Creator";

		if(isset($f_data->s_modal))
			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-primary' onClick='document.getElementById(\"desc-button-save\").click(); js_emails_confirm(\"" . $f_data->s_modal . "\");' ><span class='glyphicon glyphicon-envelope' aria-hidden='true'></span> Send Email</button></div>";
		else
			$modal_footer_confirm_button_html = "<div style='float:right;'><button type='button' class='btn btn-primary' onClick='document.getElementById(\"desc-button-save\").click(); js_emails_confirm(null);' ><span class='glyphicon glyphicon-envelope' aria-hidden='true'></span> Send Email</button></div>";

		$modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div style='display: inline-block; width: 100%;' class='panel-heading'>" . $modal_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>
					<ul style='padding-left: 15px;' class='nav nav-tabs' id='emailcreatetabs'>
						<li role='presentation' class='active' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#emailcreateinformation' data-target='#emailcreateinformation' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Information</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#emailcreateimages' data-target='#emailcreateimages' data-toggle='tab'><span class='glyphicon glyphicon-picture'></span> Attachments</a></li>
					</ul>


				<div id='email-create-innerhtml' class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>

					<div id='emailcreateevents' class='tab-content'>
						<div id='emailcreateinformation' class='tab-pane fade in active'>
							<div id='email-create-information-div' style='margin-left:10px; margin-right: 10px;'></div>
						</div>

						<div id='emailcreateimages' class='tab-pane fade in'>
							<div id='email-create-image-div' style='margin-right: 10px; margin-left:10px;'></div>
						</div>
					</div>

				</div>
			  </div>

			  <div class='modal-footer'>";

				// Looks like we are loading from another module, so lets return to that if we decide to cancel and leave the email editor.
				if(isset($f_data->s_modal))
				 $modal .= "<div style='float:left;'><button type='button' class='btn btn-warning' onClick='js_modal_hide(\"#" . $f_data->s_modal . "\"); document.getElementById(\"" . $f_data->s_modal . "\").innerHTML = \"\"; js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>" . $modal_footer_confirm_button_html;
			    else
				 $modal .= "<div style='float:left;'><button type='button' class='btn btn-warning' onClick='js_modal_hide(\"#fluid-modal\"); document.getElementById(\"fluid-modal\").innerHTML = \"\";'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Discard</button></div>" . $modal_footer_confirm_button_html;

			 $modal .= " </div>

			</div>
		  </div>";

		$image_html = '
		<div style="padding-top: 20px;">
    <form id="fileuploader" action="" method="POST" enctype="multipart/form-data">
        <div class="row fileupload-buttonbar">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Add files...</span>
                    <input type="file" name="files[]" multiple>
                </span>
                <button type="submit" class="btn btn-primary start">
                    <i class="glyphicon glyphicon-upload"></i>
                    <span>Start upload</span>
                </button>
                <button type="reset" class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>Cancel upload</span>
                </button>

                <input type="checkbox" class="toggle"><div style="display: inline-block; padding-left: 5px;">Select All</div>
                <!-- The global file processing state -->
                <span class="fileupload-process"></span>
            </div>
    <div class="col-lg-7">
<div style="padding-top: 5px; padding-left: 3px; font-size: 80%;"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Upload file, and select file(s) to attach to email.</div>
</div>
            <!-- The global progress state -->
            <div class="col-lg-5 fileupload-progress fade">
                <!-- The global progress bar -->
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-success" style="width:0%;"></div>
                </div>
                <!-- The extended global progress state -->
                <div class="progress-extended">&nbsp;</div>
            </div>
        </div>
        <!-- The table listing the files available for upload/download -->
        <table role="presentation" class="table table-striped"><tbody class="files"></tbody></table>
    </form>
    </div>


		';

		$output = "<div style='margin-top:15px;'>";

			// Status
			$output .= "<div class=\"input-group\">";
			$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>HTML Email</div></span>";

				$output .= "<select id='email-html-select' class=\"form-control selectpicker show-menu-arrow show-tick\" ";
					if(isset($f_data->s_modal))
						$output .= "data-container=\"#" . $f_data->s_modal . "\"";
					else
						$output .= "data-container=\"#fluid-modal\"";

					$output .= " data-size=\"10\" data-width=\"50%\"' disabled>";

					$output .= "<option selected value='1' style='background: #5cb85c; color: #fff;' data-content=\"<span class='label label-success' style='font-size:12px;'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</span>\"";
					$output .= "><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> Yes</option>";

					$output .= "<option value='0' style='background: #D9534F; color: #fff;' data-content=\"<span class='label label-danger' style='font-size:12px;'><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</span>\">";
					$output .= "<span class='glyphicon glyphicon-remove' aria-hidden='true'></span> No</option>";

				$output .= "</select><div style='display: inline-block; padding-top: 5px; padding-left: 3px; font-size: 80%;'><span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span> Email format, HTML or raw text.</div>";
			$output .= "</div>";

			// Email from.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>From</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Title\" aria-describedby=\"basic-addon1\" id='email-from'";
				  $output .= " value='" . FLUID_EMAIL . " ' disabled>
				</div>";

			// Recipients
			if(isset($f_emails)) {
				$output .= "<div class=\"input-group\" style='padding-top:5px;'>";
				$output .= "<span class=\"input-group-addon\" id=\"basic-addon1\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Recipients</div></span>";

					$output .= "<select id='email-recipients-select' class=\"form-control selectpicker show-menu-arrow\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

						foreach($f_emails as $f_users) {
							// u_first_name u_last_name, u_id
							$output .= "<option value=\"" . $f_users['u_id'] . "\">" . utf8_decode($f_users['u_first_name']) . " " . utf8_decode($f_users['u_last_name']) . " : " . utf8_decode($f_users['u_email']) . "</option>";
						}

					$output .= "</select>";
				$output .= "</div>";
			}
			else if(isset($f_data->s_email)) {
				$output .= "<div class=\"input-group\" style='padding-top:5px;'>
					  <span class=\"input-group-addon\" id=\"f-email-recipient\"><div style='width:120px !important;'>Recipient</div></span>
					  <input disabled type=\"text\" class=\"form-control\" placeholder=\"Title\" aria-describedby=\"basic-addon1\" id='email-recipient'";

						if(isset($f_data->s_email))
							$output .= " value=\"" . $f_data->s_email . "\"";

					  $output .= ">
					</div>";
			}

			// Email subject.
			$output .= "<div class=\"input-group\" style='padding-top:5px;'>
				  <span class=\"input-group-addon\" id=\"basic-addon1\"><div style='width:120px !important;'>Subject</div></span>
				  <input type=\"text\" class=\"form-control\" placeholder=\"Title\" aria-describedby=\"basic-addon1\" id='email-subject'";

					if(isset($f_data->s_email_title))
						$output .= " value=\"" . utf8_decode(base64_decode($f_data->s_email_title)) . "\"";

				  $output .= ">
				</div>";

			// Email content
			$output .= "<div class=\"panel panel-default\" style='margin-top:5px;'>
			  <div class=\"panel-heading\">
				<div style='display:inline-block;'><h5 style='font-weight:bold;'>Email Message</h5></div>
				<div style='float:right; display:inline-block;'>
					<button id='desc-button-edit' class='btn btn-primary' style='display:inline-block;' onclick='$(\"#email-html\").fluidnote({height:600, focus: true}); document.getElementById(\"desc-button-save\").style.display=\"inline-block\"; document.getElementById(\"desc-button-edit\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> Edit</button>
					<button id='desc-button-save' class='btn btn-primary' style='display:none;' onclick='$(\"#email-html\").fluidnote(\"destroy\"); document.getElementById(\"desc-button-edit\").style.display=\"inline-block\"; document.getElementById(\"desc-button-save\").style.display=\"none\";' type='button'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Save</button>
				</div>
			 </div>
				<div class=\"panel-body\" style='border:0px; padding-top:0px; padding-bottom:0px; padding-left:0px; padding-right:0px;'>";
					$output .= "<div class='fluid-editor-wsyg' id='email-html' style='min-height: 600px;'>";

					if(isset($f_data->s_email_message))
						$output .= base64_decode($f_data->s_email_message);

					$output .= "</div>";
				$output .= "</div>
			</div>";
		$output .= "</div>";

		$data_array['html'] = base64_encode($output);

		$execute_functions[]['function'] = "js_modal_emails_create";
		end($execute_functions);

		if(isset($f_data->s_modal))
			$execute_functions[key($execute_functions)]['data']	= base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "modal" => $f_data->s_modal, "info_html" => $data_array['html'], "image_html" => base64_encode($image_html))));
		else
			$execute_functions[key($execute_functions)]['data']	= base64_encode(json_encode(array("f_session_id" => base64_encode($_SESSION['fluid_admin']), "modal_html" => base64_encode($modal), "modal" => "fluid-modal", "info_html" => $data_array['html'], "image_html" => base64_encode($image_html))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);

		if(isset($f_data->s_modal))
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#" . $f_data->s_modal));
		else
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_banners_uploader";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_email_confirm() {
	try {
		$fluid = new Fluid();

		$f_data = json_decode(base64_decode($_REQUEST['data']));

		$f_email_modal = "<div class='modal-dialog f-dialog' id='confirmation-f-email-send' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Email Confirmation</div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

				<div id='f-email-modal-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; padding-left: 30px; padding-right: 30px; margin-bottom: 0px; max-height:70vh; overflow-y: scroll;'>
				</div>

			  </div>

			  <div class='modal-footer'>";

			  if(isset($f_data->f_modal))
				$f_email_modal .= "<div style='display: inline-block; float:left;'><button type=\"button\" class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal-overflow\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button></div><div style='display: inline-block; float:right;'><button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_emails_send(\"fluid-modal\");'><span class=\"glyphicon glyphicon-send\" aria-hidden=\"true\"></span> Send Email</button></div></div>";
			  else
				$f_email_modal .= "<div style='display: inline-block; float:left;'><button type=\"button\" class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button></div><div style='display: inline-block; float:right;'><button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_emails_send(null);'><span class=\"glyphicon glyphicon-send\" aria-hidden=\"true\"></span> Send Email</button></div></div>";

			  $f_email_modal .= "
			  </div>
			</div>
		  </div>";

		$f_plural = NULL;
		if(count($f_data->f_email) > 1) {
			$f_to = count($f_data->f_email);
			$f_plural = "s";
		}
		else
			$f_to = count($f_data->f_email);

		if(isset($f_data->f_recipient))
			$f_email_message = "<div class=\"alert alert-danger\" role=\"alert\">WARNING: You are about to send a email to " . $f_data->f_recipient . "</div>";
		else
			$f_email_message = "<div class=\"alert alert-danger\" role=\"alert\">WARNING: You are about to send a email to " . $f_to . " account" . $f_plural . ".</div>";

		$f_email_message .= "<div class='well'>";
		$f_email_message .= base64_decode($f_data->f_email) . EMAIL_FOOTER;
		if(isset($f_data->f_attach)) {
			$f_attach_files = NULL;
			foreach($f_data->f_attach as $files) {
				$f_attach_files .= "<br>" . $files;
			}

			if(isset($f_attach_files))
				$f_email_message .= "<br><br>Files attached:" . $f_attach_files;
		}

		$f_email_message .= "</well>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-msg"), "innerHTML" => base64_encode($f_email_modal))));

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-email-modal-innerhtml"), "innerHTML" => base64_encode($f_email_message))));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);

		if(isset($f_data->f_modal))
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#" . $f_data->f_modal));
		else
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-msg"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_send_email() {
	try {
		$fluid = new Fluid();

		$f_data = json_decode(base64_decode($_REQUEST['data']));

		$f_emails = NULL;

		if(isset($f_data->f_recipient))
			$f_emails[] = Array("u_email" => $f_data->f_recipient);
		else if(isset($f_data->f_accounts)) {
			$where = "WHERE u_id IN (";
			$i = 0;

			$fluid->php_db_begin();

			foreach($f_data->f_accounts as $f_users) {

				if($i != 0)
					$where .= ", ";

				$where .= $fluid->php_escape_string($f_users->p_id);

				$i++;
			}
			$where .= ")";

			$fluid->php_db_query("SELECT u_first_name, u_last_name, u_email, u_id FROM " . TABLE_USERS . " " . $where . " ORDER BY u_id ASC");
			$fluid->php_db_commit();

			if(isset($fluid->db_array)) {
				foreach($fluid->db_array as $f_users) {
					$f_emails[$f_users['u_id']] = $f_users;
				}
			}
			else
				throw new Exception("ERROR. Please select some accounts to send this email to.");
		}
		else
			throw new Exception("ERROR. Please select some accounts to send this email to.");

		$f_email_modal = "<div class='modal-dialog f-dialog' id='confirmation-f-email-send' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Confirmation</div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

				<div id='f-email-modal-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; padding-left: 30px; padding-right: 30px; margin-bottom: 0px; max-height:70vh; overflow-y: scroll;'>
				</div>

			  </div>

			  <div class='modal-footer'>
				  <div id='f-modal-button-confirm-emails' style='float:right;'></div></div>
			  </div>
			</div>
		  </div>";

		if($f_data->f_html_email == 0)
			$html_email = FALSE;
		else
			$html_email = TRUE;

		$f_attach_files = NULL;
		if(isset($f_data->f_attach))
			foreach($f_data->f_attach as $files)
				$f_attach_files[]= $files;

		$f_email_data = addslashes(base64_encode(json_encode(Array("from" => $f_data->f_email_from, "multiple_emails" => $f_emails, "subject" => base64_encode($f_data->f_subject), "message" => $f_data->f_email, "html_email" => $html_email, "attachments" => $f_attach_files))));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-msg"));

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-msg"), "innerHTML" => base64_encode($f_email_modal))));

		exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /dev/null &');

		$f_plural = NULL;
		if(count($f_data->f_email) > 1) {
			$f_to = count($f_data->f_email);
			$f_plural = "s";
		}
		else
			$f_to = count($f_data->f_email);

		if(isset($f_data->f_recipient))
			$f_success_message = "<div class=\"alert alert-success\" role=\"alert\">Email sent successfully to " . $f_data->f_recipient . ".</div>";
		else
			$f_success_message = "<div class=\"alert alert-success\" role=\"alert\">Email sent successfully to " . $f_to . $f_plural . " accounts. Please be aware there is a few second delay between each email that is sent by this system.</div>";

		$f_success_message .= "<div class='well'>";
		$f_success_message .= print_r(base64_decode($f_data->f_email), TRUE);
		$f_success_message .= EMAIL_FOOTER;

		if(isset($f_data->f_attach)) {
			$f_attach_files_html = NULL;
			foreach($f_data->f_attach as $files) {
				$f_attach_files_html .= "<br>" . $files;
			}

			if(isset($f_attach_files))
				$f_success_message .= "<br><br>Files attached:" . $f_attach_files_html;
		}

		$f_success_message .= "</div>";

		if(isset($f_data->f_modal))
			$f_modal_button = "<button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_modal_hide(\"#fluid-modal-msg\"); document.getElementById(\"" . $f_data->s_modal . "\").innerHTML = \"\"; js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Continue</button>";
		else
			$f_modal_button = "<button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_modal_hide(\"#fluid-modal-msg\"); document.getElementById(\"fluid-modal\").innerHTML = \"\";'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Continue</button>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-email-modal-innerhtml"), "innerHTML" => base64_encode($f_success_message))));

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-modal-button-confirm-emails"), "innerHTML" => base64_encode($f_modal_button))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-msg"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}
?>
