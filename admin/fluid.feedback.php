<?php
// fluid.feedback.php
// Michael Rajotte - 2018 Janvier

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


function php_load_feedback($data = NULL) {
	$fluid = new Fluid();
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

		$mode = "feedback";
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
			$f_query_count = "SELECT COUNT(*) AS tmp_u_count FROM " . TABLE_FEEDBACK;

		$fluid->php_db_query($f_query_count);

		if(isset($fluid->db_array))
			$f_tmp_count = $fluid->db_array[0]['tmp_u_count'];

		if(isset($f_data->query))
			$f_query = $f_data->query;
		else
			$f_query = "SELECT * FROM " . TABLE_FEEDBACK . " ORDER BY f_created DESC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;

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
			$f_pagination_function = "js_fluid_load_feedback";

		$return = "<div id='fluid-category-listing' class='list-group'>";
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-users'><li>";

				if(isset($tmp_selection_array))
					$return .= "<div id='category-a-feedback' style='height: 40px; background-color: " . COLOUR_SELECTED_CATEGORY . ";' class='list-group-item'>";
				else
					$return .= "<div id='category-a-feedback' style='height: 40px;' class='list-group-item'>";

					$return .= "<span id='category-badge-count-feedback' class='badge'>" . $f_tmp_count . "</span>";

					if(isset($tmp_selection_array))
						$return .= "<span id='category-badge-select-count-feedback' class='badge'>" . count($tmp_selection_array) . " selected</span>";
					else
						$return .= "<span id='category-badge-select-count-feedback' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-feedback' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$return .= " <span id='category-span-open-feedback' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Feedback</div></span>";
				$return .= "</div>";
			$return .= "</li></ul>";

			$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode) . "</div>";

			$return .= "<div id='category-div-feedback'>";
			$return .= php_html_feedback($tmp_array, $selection_data, "feedback");
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

			return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input("feedback")), "navbarright" => base64_encode(php_html_navbar_right("feedback")), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		$fluid->php_db_rollback(); // Is this really needed?
		return php_fluid_error($err);
	}
}

// This takes a array of data and displays them in a neatly formatted html table.
function php_html_feedback($data_array, $selection_array = NULL, $mode = NULL) {
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

			$return .= "<td>Date</td><td>Why come to our site?</td><td>Comments</td><td>What stage you exit the site & why?</td><td style='text-align:center;'>Find what you wanted?</td><td style='text-align:center;'>How likely to recommend us?</td><td style='text-align:center;'>Site experience</td><td>What would you like to see?</td></tr>";
			$return .= "</thead>";
			$return .= "<tbody>";

			$return .= php_html_feedback_rows($data_array, $selection_array, $mode);

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
function php_html_feedback_rows($data_array = NULL, $selection_array = NULL, $mode = NULL, $td_only = FALSE) {
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
			if(in_array($value['f_id'], $tmp_selection_array)) {
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
				$return .= "<tr id='p_id_tr_" . $value['f_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_cancel_event(event); js_feedback_select(\"" . $value['f_id'] . "\", \"" . $p_catmfgid . "\", \"1\");'>";

			// Hide this column. It contains data used for sortable updates.
			$return .= "<td class='f-td' style='display:none;' id='p_id_tr_" . $value['f_id'] . "_td'>" . base64_encode(json_encode(Array('f_id' => $value['f_id'], 'f_ip_address' => $value['f_ip_address']))) . "</td>";

			$return .= "<td id='bo-td-fid-" . $value['f_id'] . "'>" . $value['f_created'] . "</td><td>" . $value['f_reason'] . "</td><td>" . $value['f_comment'] . "</td><td>" . $value['f_exit'] . "</td><td style='text-align:center;'>" . $value['f_find'] . "</td><td style='text-align:center;'>" . $value['f_likely'] . "</td><td style='text-align:center;'>" . $value['f_rate'] . "</td><td>" . $value['f_extra'] . "</td>";

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
?>
