<?php
// fluid.logs.php
// Michael Rajotte - 2017 Novembre
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

function php_logs_delete() {
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));

		if(isset($f_data->f_selection->p_selection)) {
			$fluid = new Fluid();
			$fluid->php_db_begin();

			$where = "WHERE l_ud IN (";
			$i = 0;
			foreach($f_data->f_selection->p_selection as $log) {
				if($i != 0)
					$where .= ", ";

				$where .= $fluid->php_escape_string($log->p_id);

				$i++;
			}
			$where .= ")";

			$fluid->php_db_query("DELETE FROM " . TABLE_LOGS . " " . $where);

			$fluid->php_db_commit();

			$execute_functions[]['function'] = "js_fluid_logs_delete_cleanup";

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else
			throw new Exception("Please select some logs to delete.");
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_logs_load($data = NULL) {
	$fluid = new Fluid();
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$mode = "logs";
		if(isset($f_data->f_page_num)) {
			$f_page = $f_data->f_page_num;
			$f_start = ($f_page - 1) * FLUID_ADMIN_LISTING_LIMIT;
		}
		else {
			$f_page = 0;
			$f_start = 0;
		}

		if(isset($_REQUEST['d_mode']))
			$f_d_mode = $_REQUEST['d_mode'];
		else if(isset($f_data->d_mode))
			$f_d_mode = $f_data->d_mode;
		else
			$f_d_mode = NULL;

		$f_where = NULL;

		if(isset($f_d_mode)) {
			switch ($f_d_mode) {
				case 1:
					$f_where = " WHERE l_type = 'search'";
					break;
				case 2:
					$f_where = " WHERE l_type = 'checkout visit' OR l_type = 'shipping query'";
					break;
				case 3:
					$f_where = " WHERE l_type = 'placing order' OR l_type = 'shipping query' OR l_type = 'charging order' OR l_type = 'verifying order' OR l_type = 'voiding order' OR l_type = 'paypal create' OR l_type = 'paypal charge' OR l_type = 'order error'";
					break;
				case 4:
					$f_where = " WHERE l_type = 'ADMIN: scan update'";
					break;
				case 5:
					$f_where = " WHERE l_type = 'cart update'";
					break;
				case 6:
					$f_where = " WHERE l_type = 'shipping query'";
					break;
				default:
					$f_where = NULL;
			}
		}

		$f_tmp_count = 0;

		$fluid->php_db_begin();
		if(isset($f_data->query_count))
			$f_query_count = $f_data->query_count;
		else
			$f_query_count = "SELECT COUNT(*) AS tmp_l_count FROM " . TABLE_LOGS . $f_where;

		$fluid->php_db_query($f_query_count);

		if(isset($fluid->db_array))
			$f_tmp_count = $fluid->db_array[0]['tmp_l_count'];

		if(isset($f_data->query))
			$f_query = $f_data->query;
		else
			$f_query = "SELECT * FROM " . TABLE_LOGS . $f_where . " ORDER BY l_date DESC, l_ud DESC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;

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
			$f_pagination_function = "js_fluid_logs_load";

		$return = "<div id='fluid-category-listing' class='list-group'>";
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-logs'><li>";

				if(isset($tmp_selection_array))
					$return .= "<div id='category-a-logs' style='height: 40px; background-color: " . COLOUR_SELECTED_CATEGORY . ";' class='list-group-item'>";
				else
					$return .= "<div id='category-a-logs' style='height: 40px;' class='list-group-item'>";

					$return .= "<span id='category-badge-count-logs' class='badge'>" . $f_tmp_count . "</span>";

					if(isset($tmp_selection_array))
						$return .= "<span id='category-badge-select-count-logs' class='badge'>" . count($tmp_selection_array) . " selected</span>";
					else
						$return .= "<span id='category-badge-select-count-logs' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-logs' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$return .= " <span id='category-span-open-logs' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>logs</div></span>";
				$return .= "</div>";
			$return .= "</li></ul>";

			$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode, $f_d_mode) . "</div>";

			$return .= "<div id='category-div-users'>";
			$return .= php_logs_html($tmp_array, $selection_data, "logs");
			$return .= "</div>";

		$return .= "</div>";

		$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode, $f_d_mode) . "</div>";

		if(isset($f_data->f_refresh)) {
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("content-div"), "innerHTML" => base64_encode($return))));

			return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
		}
		else {
			$breadcrumbs = "<li><a href='index.php'>Home</a></li>";
			$breadcrumbs .= "<li class='active'>Logs</li>";

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

			return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input("logs")), "navbarright" => base64_encode(php_html_navbar_right("logs")), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// This takes a array of data and displays them in a neatly formatted html table.
function php_logs_html($data_array, $selection_array = NULL, $mode = NULL) {
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

			$return .= "<td style='text-align:center;' class='f-tr-date-logs'>Date</td><td style='text-align:center;'>Type</td><td style='text-align:left;'>Data</td></tr>";
			$return .= "</thead>";
			$return .= "<tbody>";

			$return .= php_logs_rows_html($data_array, $selection_array, $mode);

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

// Generates each individual row of data for logs.
function php_logs_rows_html($data_array = NULL, $selection_array = NULL, $mode = NULL, $td_only = FALSE) {
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
			if(in_array($value['l_ud'], $tmp_selection_array)) {
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
				$return .= "<tr id='p_id_tr_" . $value['l_ud'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_cancel_event(event); js_logs_select(\"" . $value['l_ud'] . "\", \"" . $p_catmfgid . "\", \"1\");'>";

			// Hide this column. It contains data used for sortable updates.
			$return .= "<td class='f-td' style='display:none;' id='p_id_tr_" . $value['l_ud'] . "_td'>" . base64_encode(json_encode(Array('l_ud' => $value['l_ud'], 'l_date' => $value['l_date']))) . "</td>";

			$return .= "<td id='bo-td-ldate-" . $value['l_ud'] . "' style='text-align:center;'>" . $value['l_date'] . "</td><td style='text-align:center;'>" . $value['l_type'] . "</td>";

			$return .= "<td>";

			//if($value['l_type'] == "checkout visit" || $value['l_type'] == "placing order" || $value['l_type'] == "charging order" || $value['l_type'] == "verifying order" || $value['l_type'] == "voiding order" || $value['l_type'] == "order error" || $value['l_type'] == "paypal create" || $value['l_type'] == "paypal charge" || $value['l_type'] == "cart update") {
			if($value['l_type'] != "search") {
				//$f_array = unserialize($value['l_query']);
				/*
					checkout visit
					placing order
					charging order
					verifiying order
					voiding order
					paypal create
					paypal charge
					order error
					cart update
				*/

				$return .= substr($value['l_query'], 0, 100) . "<button style='margin-left: 5px;' onClick='js_cancel_event(event); js_logs_view_data(\"" . $value['l_ud'] . "\");' class='btn btn-sm btn-primary'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> <div class='f-btn-text'>View Data</div></button>";
				//$return .= "<pre>" . print_r($f_array, TRUE) . "</pre>";
			}
			else
				$return .= $value['l_query'];

			$return .=  "</td>";

			if($td_only == FALSE)
				$return .= "</tr>";
		}

		return $return;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_logs_data_view() {
	try {
		$modal_title = "Data Viewer";

		$modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div style='display: inline-block; width: 100%;' class='panel-heading'>" . $modal_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i style='margin-right: 10px;' class=\"fa fa-print\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='document.getElementById(\"fluid-print-div\").innerHTML = document.getElementById(\"product-create-innerhtml\").innerHTML; window.print();'></i> <i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>

				<div id='product-create-innerhtml' class='panel panel-default' style='border-radius-top-right: 0px; border-radius-top-left: 0px; border-top: 0px; border-bottom: 0px; margin-bottom: 0px; min-height: 400px; max-height:60vh; overflow-y: scroll;'>";

				$f_data = json_decode(base64_decode($_REQUEST['data']));

				if(isset($f_data->l_ud)) {

					$fluid = new Fluid();

					$fluid->php_db_begin();

					$fluid->php_db_query("SELECT * FROM " . TABLE_LOGS . " WHERE l_ud = '" . $fluid->php_escape_string($f_data->l_ud) . "' ORDER BY l_date DESC LIMIT 1");

					$fluid->php_db_commit();

					if(isset($fluid->db_array)) {
						foreach($fluid->db_array as $f_log) {
							//print_r(unserialize($f_log['l_query']));
							$modal .= "<pre>";
								$modal .= print_r(unserialize($f_log['l_query']), TRUE);
							$modal .= "</pre>";
						}
					}

				}




			$modal .= "</div>
			  </div>

			  <div class='modal-footer'>
				  <div style='float:right;'><button type='button' class='btn btn-danger' onClick='js_modal_hide(\"#fluid-modal\");' ><span class='glyphicon glyphicon-remove' aria-hidden=\"true\"></span> Close</button></div>
			  </div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal"), "innerHTML" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}


?>
