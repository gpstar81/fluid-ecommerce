<?php
// fluid.orders.php
// Michael Rajotte - 2018 Janvier
// Loads ajax php code.

require_once (__DIR__ . "/../fluid.required.php");
require_once (__DIR__ . "/../fluid.class.php");
require_once (__DIR__ . "/fluid.mode.class.php");
require_once (__DIR__ . "/../fluid.define.html.php");
require_once (__DIR__ . "/fluid.error.php");


if(empty($_SESSION['fluid_admin'])) {
	$_SESSION['fluid_admin'] = date('His') . rand(100, 999999999);
}

// A little added security to prevent eval and other little nasty functions from running.
if(isset($_REQUEST['load'])) {
	if(function_exists($_REQUEST['function'])) {
		echo call_user_func($_REQUEST['function']);
	}
	else {
		echo php_fluid_error("Function not found : " . $_REQUEST['function'] . "();");
	}
}

function php_load_orders($data = NULL) {
	$fluid = new Fluid();
	try {
		if(isset($_REQUEST['data']))
			$f_data = json_decode(base64_decode($_REQUEST['data']));
		else if(isset($data))
			$f_data = (object)json_decode(base64_decode($data));
		else
			$f_data = NULL;

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

		$f_tmp_count = 0;

		$fluid->php_db_begin();

		$f_where = NULL;
		/*
			s_status:
			0 = error
			1 = processing
			2 = shipped
			3 = ready for pickup
			4 = pre-ordered
			5 = refund
			6 = cancelled
		*/

		if(isset($f_d_mode)) {
			if($f_d_mode == 0)
				$f_where = " WHERE s_status = '0'";
			else if($f_d_mode == 1)
				$f_where = " WHERE s_status = '1'";
			else if($f_d_mode == 2)
				$f_where = " WHERE s_status = '2'";
			else if($f_d_mode == 3)
				$f_where = " WHERE s_status = '3'";
			else if($f_d_mode == 4)
				$f_where = " WHERE s_status = '5'"; //$f_where = " WHERE s_refund_total > 0";
			else if($f_d_mode == 5)
				$f_where = " WHERE s_status = '6'";
			else if($f_d_mode == 6)
				$f_where = " WHERE s_status = '4'";
			else if($f_d_mode == 10) // Shipped or pickup.
				$f_where = " WHERE s_status = '2' OR s_status = '3'";
		}

		if(isset($f_data->query_count))
			$f_query_count = $f_data->query_count;
		else
			$f_query_count = "SELECT COUNT(*) AS tmp_c_order_count FROM " . TABLE_SALES . " s " . $f_where;

			$fluid->php_db_query($f_query_count);

		if(isset($fluid->db_array))
			$f_tmp_count = $fluid->db_array[0]['tmp_c_order_count'];

		if(isset($f_data->query))
			$f_query = $f_data->query;
		else
			$f_query = "SELECT `s_id`, `s_status`, `s_order_number`, `s_sale_time`, `s_u_id`, `s_u_email`, `s_total`, `s_sub_total`, `s_shipping_total`, `s_tax_total`, `s_taxes`, `s_address_name`, `s_address_number`, `s_address_street`, `s_address_city`, `s_address_province`, `s_address_postalcode`, `s_address_country`, `s_address_phonenumber`, `s_shipping_64`, `s_items_64`, `s_refund_total` FROM " . TABLE_SALES . $f_where . " ORDER BY s_id DESC LIMIT " . $f_start . ", " . FLUID_ADMIN_LISTING_LIMIT;
			//$fluid->php_db_query("SELECT `s_id`, `s_status`, `s_order_number`, `s_sale_time`, `s_u_id`, `s_u_email`, `s_total`, `s_sub_total`, `s_shipping_total`, `s_tax_total`, `s_taxes`, `s_address_name`, `s_address_number`, `s_address_street`, `s_address_city`, `s_address_province`, `s_address_postalcode`, `s_address_country`, `s_address_phonenumber`, `s_shipping_64`, `s_items_64`, `s_refund_total` FROM " . TABLE_SALES . $f_where . " ORDER BY s_id DESC");

		$fluid->php_db_query($f_query);

		$fluid->php_db_commit();

		$tmp_array = Array();
		if(isset($fluid->db_array))
			foreach($fluid->db_array as $value) {
				$tmp_array[] = $value;
			}

		$mode = "orders";
		$fluid_mode = new Fluid_Mode($mode);

		/*
		if(isset($_REQUEST['selection']))
			$selection_data = $_REQUEST['selection'];
		else
			$selection_data = NULL;
		*/

		$tmp_selection_array = NULL;
		if(isset($f_data->f_selection)) {
			$selection_data = base64_encode(json_encode($f_data->f_selection->p_selection));

			if(isset($f_data->f_selection->p_selection))
				foreach($f_data->f_selection->p_selection as $product) {
					$tmp_selection_array[$product->p_id] = $product->p_id;
				}
		}
		else
			$selection_data = NULL;

		if(isset($f_data->pagination_function))
			$f_pagination_function = $f_data->pagination_function;
		else
			$f_pagination_function = "js_fluid_load_orders";

		$return = "<div id='fluid-category-listing' class='list-group'>";
			$return .= "<ul style='list-style: none; padding-left:0px;' id='category-list-div-" . $fluid_mode->mode . "'><li>";

				if(isset($tmp_selection_array))
					$return .= "<div id='category-a-" . $fluid_mode->mode . "' style='height: 40px; background-color: " . COLOUR_SELECTED_CATEGORY . ";' class='list-group-item'>";
				else
					$return .= "<div id='category-a-" . $fluid_mode->mode . "' style='height: 40px;' class='list-group-item'>";

					$return .= "<span id='category-badge-count-" . $fluid_mode->mode . "' class='badge'>" . $f_tmp_count . "</span>";

					if(isset($tmp_selection_array))
						$return .= "<span id='category-badge-select-count-" . $fluid_mode->mode . "' class='badge'>" . count($tmp_selection_array) . " selected</span>";
					else
						$return .= "<span id='category-badge-select-count-" . $fluid_mode->mode . "' class='badge' style='display:none;'></span>";

					$disable_style = "none";

					$return .= "<span id='category-badge-select-lock-" . $fluid_mode->mode . "' class='badge' style='display:" . $disable_style . ";'><span class=\"glyphicon glyphicon-eye-close\" aria-hidden=\"true\" style='font-size:10px;'></span> disabled</span>";

					$return .= " <span id='category-span-open-" . $fluid_mode->mode . "' class=\"glyphicon glyphicon-collapse-down\" aria-hidden=\"true\" style='display: block; padding-right:5px;'> <div style='display:inline; ' class='dropdown'>Orders</div></span>";
				$return .= "</div>";
			$return .= "</li></ul>";

			$return .= "<div class='f-pagination'>" . $fluid->php_pagination($f_tmp_count, FLUID_ADMIN_PAGINATION_LIMIT, $f_page, $f_pagination_function, $mode, $f_d_mode) . "</div>";

			$return .= "<div id='category-div-" . $fluid_mode->mode . "'>";
			$return .= php_html_orders($tmp_array, $selection_data, $mode);
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
			$breadcrumbs .= "<li class='active'>" . $fluid_mode->breadcrumb . "</li>";

			// Follow up functions to execute on a server response back to the user.
			$execute_functions[0]['function'] = "js_clear_fluid_selection";
			$execute_functions[0]['data'] = base64_encode(json_encode(""));

			$execute_functions[1]['function'] = "js_html_style_show";
			$execute_functions[1]['data'] = base64_encode(json_encode(Array("div_id_hide" => "navbar-menu-right")));

			return json_encode(array("breadcrumbs" => base64_encode($breadcrumbs), "innerhtml" => base64_encode($return), "navbarsearch" => base64_encode(php_html_admin_search_input($mode)), "navbarright" => base64_encode(php_html_navbar_right($mode)), "js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
		}
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Update our order and resend back a new updated TR data.
function php_update_order() {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']));

		$fluid = new Fluid();

		$fluid->php_db_begin();

		if(isset($f_data->s_tracking))
			$s_tracking = ", s_tracking = '" . $fluid->php_escape_string($f_data->s_tracking) . "'";
		else
			$s_tracking = ", s_tracking = NULL";

		$fluid->php_db_query("UPDATE " . TABLE_SALES . " SET s_status = '" . $fluid->php_escape_string($f_data->s_status) . "'" . $s_tracking . " WHERE s_id = '" . $fluid->php_escape_string($f_data->s_id) . "'");

		$fluid->php_db_query("SELECT * FROM " . TABLE_SALES . " WHERE s_id = '" . $fluid->php_escape_string($f_data->s_id) . "' ORDER BY s_id ASC");

		$f_html = php_html_order_rows($fluid->db_array, $f_data->s_selection, "orders", TRUE);

		$fluid->php_db_commit();

		$tmp_selection_array = Array();
		if(isset($f_data->s_selection))
			foreach(json_decode(base64_decode($f_data->s_selection)) as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		if(in_array($f_data->s_id, $tmp_selection_array))
			$style_colour = COLOUR_SELECTED_ITEMS;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_SHIPPED || $fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_PICKUP)
			$style_colour = COLOUR_ORDER_SHIPPED;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_ERROR)
			$style_colour = COLOUR_ORDER_ERROR;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_CANCELLED)
			$style_colour = COLOUR_ORDER_CANCELLED;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_REFUND)
			$style_colour = COLOUR_ORDER_REFUND;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_PREORDERED)
			$style_colour = COLOUR_ORDER_PREORDER;
		else
			$style_colour = "transparent";

		if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_SHIPPED || $fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_PICKUP)
			$d_colour = COLOUR_ORDER_SHIPPED;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_ERROR)
			$d_colour = COLOUR_ORDER_ERROR;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_CANCELLED)
			$d_colour = COLOUR_ORDER_CANCELLED;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_REFUND)
			$d_colour = COLOUR_ORDER_REFUND;
		else if($fluid->php_fluid_order_status($f_data->s_status) == ORDER_STATUS_PREORDERED)
			$d_colour = COLOUR_ORDER_PREORDER;
		else
			$d_colour = "transparent";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("p_id_tr_" . $f_data->s_id), "innerHTML" => base64_encode($f_html))));

		$execute_functions[]['function'] = "js_update_order_rows";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("id" => base64_encode("p_id_tr_" . $f_data->s_id), "s_colour" => base64_encode($style_colour), "d_colour" => base64_encode($d_colour))));

		$execute_functions[]['function'] = "js_modal_toggle";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// This takes a array of product data and displays them in a neatly formatted html table.
function php_html_orders($data_array, $selection_array = NULL, $mode = NULL) {
	$fluid = new Fluid();
	try {
		$fluid_mode = new Fluid_Mode($mode);

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();
		if(isset($selection_array))
			foreach(json_decode(base64_decode($selection_array)) as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		// Used for passing the orders in to the select all products js functions.
		$tmp_product_array = Array();
		$p_catmfgid = NULL;

		if(isset($data_array)) {
			//if(isset($data_array[0]) && $fluid_mode->mode != "orders")
				//$p_catmfgid = (int)$data_array[0][$fluid_mode->p_catmfg_id];
			//else
				$p_catmfgid = $fluid_mode->mode;

			foreach($data_array as $value)
				$tmp_product_array[$value['s_id']] = 1;//$value['p_enable']; --> Perhaps use $value['s_status'] instead?
		}

		$return = "<div class='table-responsive panel panel-default'>";

		/*
		$select_button = "<div class='dropdown'>
		<a class='dropdown-toggle' data-toggle='dropdown' href='#' role='button' aria-haspopup='true' aria-expanded='false'>
		  Select <span class='caret'></span>
		</a>
		  <ul class='dropdown-menu' aria-labelledby='dropdownMenu1'>
			<li><a onClick='js_product_select_all(\"" . $p_catmfgid . "\", \"" . base64_encode(json_encode($tmp_product_array)) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Select all</a></li>
			<li><a onClick='js_select_clear_p_selection_category(\"" . base64_encode(json_encode(array($p_catmfgid))) . "\");' onmouseover=\"JavaScript:this.style.cursor='pointer';\"><span class=\"glyphicon glyphicon-minus\" aria-hidden=\"true\"></span> Un-select all</a></li>
		  </ul>
		  </div>";
		*/

		$return .= "<table class='table table-hover' id='cat-" . $p_catmfgid . "'>";

		if(count($data_array) == 0)
			$return .= "<tr><td>" . $fluid_mode->msg_no_products . "</td></tr>";
		else {
			$return .= "<thead>";
			$return .= "<tr style='font-weight: bold;'>";

			//$return .= "<td style='text-align:center;'>" . $select_button . "</td>";


			//$fluid->php_db_query("SELECT `s_id`, `s_status`, `s_order_number`, `s_sale_time`, `s_u_id`, `s_u_email`, `s_total`, `s_sub_total`, `s_shipping_total`, `s_tax_total`, `s_taxes`, `s_address_name`, `s_address_number`, `s_address_street`, `s_address_city`, `s_address_province`, `s_address_postalcode`, `s_address_country`, `s_address_phonenumber`, `s_shipping_64`, `s_items_64` FROM " . TABLE_SALES . " ORDER BY s_id ASC");

			$return .= "<td>Order #</td><td style='text-align:center;'>Status</td><td style='text-align:center;'>Date</td><td style='text-align:center;'>Customer</td><td style='text-align:center;'>Email</td><td style='text-align:center;'>Phone #</td><td style='text-align:right;'>Refunds</td><td style='text-align:right;'>Total</td><td style='text-align:center;'></td></tr>";
			$return .= "</thead>";
			$return .= "<tbody>";

			$return .= php_html_order_rows($data_array, $selection_array, $mode);

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

// Generates each individual row of data for orders.
function php_html_order_rows($data_array = NULL, $selection_array = NULL, $mode = NULL, $td_only = FALSE) {
	try {
		$fluid = new Fluid();
		$return = NULL;

		// Used for keeping track which items are already selected.
		$tmp_selection_array = Array();
		if(isset($selection_array))
			foreach(json_decode(base64_decode($selection_array)) as $product)
				$tmp_selection_array[$product->p_id] = $product->p_id;

		foreach($data_array as $value) {
			if(in_array($value['s_id'], $tmp_selection_array)) {
				if($fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_SHIPPED || $fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_PICKUP)
					$d_colour = " data-colour='" . COLOUR_ORDER_SHIPPED . "';";
				else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_CANCELLED)
					$d_colour = " data-colour='" . COLOUR_ORDER_CANCELLED . "';";
				else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_REFUND)
					$d_colour = " data-colour='" . COLOUR_ORDER_REFUND . "';";
				else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_ERROR)
					$d_colour = " data-colour='" . COLOUR_ORDER_ERROR . "';";
				else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_PREORDERED)
					$d_colour = " data-colour='" . COLOUR_ORDER_PREORDER . "';";
				else
					$d_colour = " data-colour='transparent';";

				$style = "style='font-style: italic; background-color: " . COLOUR_SELECTED_ITEMS . ";' " . $d_colour;
				$checked = "checked";
			}
			else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_SHIPPED || $fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_PICKUP) {
				$style = "style='background-color: " . COLOUR_ORDER_SHIPPED . ";' data-colour='" . COLOUR_ORDER_SHIPPED . "';";
				$checked = "";
			}
			else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_CANCELLED) {
				$style = "style='background-color: " . COLOUR_ORDER_CANCELLED . ";' data-colour='" . COLOUR_ORDER_CANCELLED . "';";
				$checked = "";
			}
			else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_REFUND) {
				$style = "style='background-color: " . COLOUR_ORDER_REFUND . ";' data-colour='" . COLOUR_ORDER_REFUND . "';";
				$checked = "";
			}
			else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_ERROR) {
				$style = "style='background-color: " . COLOUR_ORDER_ERROR . ";' data-colour='" . COLOUR_ORDER_ERROR . "';";
				$checked = "";
			}
			else if($fluid->php_fluid_order_status($value['s_status']) == ORDER_STATUS_PREORDERED) {
				$style = "style='background-color: " . COLOUR_ORDER_PREORDER . ";' data-colour='" . COLOUR_ORDER_PREORDER . "';";
				$checked = "";
			}
			else {
				$style = "style='background-color: transparent;' data-colour='transparent'";
				$checked = "";
			}

			$p_catmfgid = $mode;

			if($td_only == FALSE)
				$return .= "<tr id='p_id_tr_" . $value['s_id'] . "' " . $style . " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_cancel_event(event); js_order_select(\"" . $value['s_id'] . "\", \"" . $p_catmfgid . "\", \"0\");'>";
			//$return .= "<tr id='p_id_tr_" . $value['s_id'] . "' " . $style . ">";

			//$return .= "<td style='text-align:center;'><input id='p_id_" . $value['s_id'] . "' onClick='js_cancel_event(event); js_order_select(\"" . $value['s_id'] . "\", \"" . $p_catmfgid . "\", \"" . $value['s_status'] . "\");' type=\"checkbox\" " . $checked . "></td>";

			$return .= "<td>" . explode('-', $value['s_order_number'])[2] . "</td>";

			$return .= "<td id='so-td-status-" . $value['s_id'] . "' style='text-align:center;'>" . $fluid->php_fluid_order_status($value['s_status']) . "</td><td style='text-align:center;'>" . $value['s_sale_time'] . "</td><td style='text-align:center;'>" . utf8_decode($value['s_address_name']) . "</td><td style='text-align:center;'>" . utf8_decode($value['s_u_email']) . "</td><td style='text-align:center;'>" . utf8_decode($value['s_address_phonenumber']) . "</td><td id='p_id_td_refund_" . $value['s_id'] . "' style='text-align:right;'>" . number_format($value['s_refund_total'], 2, '.', ',') . "</td><td style='text-align:right;'>" .  number_format($value['s_total'], 2, '.', ',') . "</td>";

			$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . base64_encode($value['s_id']))));

			$return .= "<td style='text-align:center;'><button type='button' class='btn btn-primary' onClick='js_cancel_event(event); js_fluid_ajax(\"" . $temp_url . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></span> <div class='f-btn-text'>View</div></button></td>";

			if($td_only == FALSE)
				$return .= "</tr>";
		}

		return $return;
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_refund() {
	$fluid = new Fluid ();
	$fluid_alt = new Fluid();
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		// --> Select the transaction for the sale. Order by ASC, we want to pull #1 as it's the original sale.
		// --> Process refund.
		// --> Set the items in sales_items to refund = 1 when refund response a success.
		// --> Somehow set a flag on the sales shipping if required to refund if shipping was refunded when refund response a success.

		// Lets build the transaction data. This can be the sales and refunds etc.
		$fluid->php_db_begin();
		$fluid->php_db_query("SELECT * FROM " . TABLE_SALES_TRANSACTIONS . " WHERE st_s_order_number = '" . $fluid->php_escape_string(base64_decode($f_data['rf_id'])) . "' ORDER BY st_id ASC LIMIT 1");
		$fluid->php_db_commit();

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $st_data) {
				// Refund successful, lets save and update our data.
				$fluid_alt->php_db_begin();

				$i = 0;
				$case = "`si_p_refund` = CASE";
				$where = "WHERE si_id IN (";
				$s_refund = FALSE;
				foreach($f_data['rf_items'] as $key => $f_items) {
					if($key != 'ship') {
						if($i > 0) {
							$where .= ", ";
						}
						$case .= " WHEN (`si_s_order_number`, `si_id`) = ('" . $fluid_alt->php_escape_string($st_data['st_s_order_number']) . "', '" . $key . "') THEN '1'";

						$where .= "'" . $key . "'";
						$i++;
					}

					if($key == 'ship')
						$s_refund = TRUE;
				}
				$case .= " END";
				$where .= ")";

				// Only update the items if any where found. If a shipping was refunded only, there are no items.
				if($i > 0)
					$fluid_alt->php_db_query("UPDATE " . TABLE_SALES_ITEMS . " SET " . $case . " " . $where);

				// Only update the shipping refund flag is shipping was refunded.
				if($s_refund == TRUE)
					$fluid_alt->php_db_query("UPDATE " . TABLE_SALES . " SET s_ship_refund = '" . $fluid_alt->php_escape_string("1") . "', s_refund_total = IFNULL(s_refund_total, 0) + " . base64_decode($f_data['rf_total']) . " WHERE s_id = '" . $fluid_alt->php_escape_string(base64_decode($f_data['rf_sid'])) . "'");
				else
					$fluid_alt->php_db_query("UPDATE " . TABLE_SALES . " SET s_refund_total = IFNULL(s_refund_total, 0) + " . base64_decode($f_data['rf_total']) . " WHERE s_id = '" . $fluid_alt->php_escape_string(base64_decode($f_data['rf_sid'])) . "'");

				$fluid_alt->php_db_query("SELECT `s_refund_total` FROM " . TABLE_SALES . " WHERE s_id = '" . $fluid_alt->php_escape_string(base64_decode($f_data['rf_sid'])) . "'");

				$f_refund_html = NULL;
				if(isset($fluid_alt->db_array)) {
					if(!empty($fluid_alt->db_array[0]['s_refund_total']))
						$f_refund_html = number_format($fluid_alt->db_array[0]['s_refund_total'], 2, ".", ",");
				}

				$fluid_alt->php_db_commit();

				if(!empty($f_refund_html)) {
					$execute_functions[]['function'] = "js_html_insert_element";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("p_id_td_refund_" . base64_decode($f_data['rf_sid'])), "innerHTML" => base64_encode($f_refund_html))));
				}

				$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . $f_data['rf_sid'])));

				$execute_functions[]['function'] = "js_fluid_ajax";
				end($execute_functions);
				$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_url));
			}
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_refund_moneris() {
	$fluid = new Fluid ();
	$fluid_alt = new Fluid();
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		// --> Select the transaction for the sale. Order by ASC, we want to pull #1 as it's the original sale.
		// --> Process refund.
		// --> Set the items in sales_items to refund = 1 when refund response a success.
		// --> Somehow set a flag on the sales shipping if required to refund if shipping was refunded when refund response a success.

		// Lets build the transaction data. This can be the sales and refunds etc.
		$fluid->php_db_begin();
		$fluid->php_db_query("SELECT * FROM " . TABLE_SALES_TRANSACTIONS . " WHERE st_s_order_number = '" . $fluid->php_escape_string(base64_decode($f_data['rf_id'])) . "' ORDER BY st_id ASC LIMIT 1");
		$fluid->php_db_commit();

		if(isset($fluid->db_array)) {
			require_once(MONERIS_API);

			foreach($fluid->db_array as $st_data) {
				// Lets build the transaction html data.
				$simpleXmlElem = simplexml_load_string(unserialize(base64_decode($st_data['st_s_transaction_serialize_64'])));

				// Refund Process
				/*
					https://developer.moneris.com/More/Testing/Penny%20Value%20Simulator
					https://developer.moneris.com/More/Testing/Testing%20a%20Solution
					https://developer.moneris.com/Documentation/NA/E-Commerce%20Solutions/API/Response%20Fields?lang=php
					Test credit card numbers always approved:
					--> Visa: 4502285070000007

					Test credit card numbers always declined:
					--> Visa: 4355310002576375

					Various other test card numbers:
					--> Visa: 4242424242424242
					--> Master Card: 5105105105105100
					--> Visa: 4111111111111111
					--> Amex: 378282246310005
				*/
				if(FLUID_PAYMENT_SANDBOX == TRUE) {
					$moneris_refund = Moneris::create(
						array(
							'api_key' => MONERIS_API_KEY_SANDBOX,
							'store_id' => MONERIS_STORE_ID_SANDBOX,
							'environment' => Moneris::ENV_TESTING
						)
					);
				}
				else {
					$moneris_refund = Moneris::create(
						array(
							'api_key' => MONERIS_API_KEY,
							'store_id' => MONERIS_STORE_ID,
							'environment' => Moneris::ENV_LIVE
						)
					);
				}

				$result_refund = $moneris_refund->refund((string)$simpleXmlElem->receipt->TransID, (string)$simpleXmlElem->receipt->ReceiptId, (string)base64_decode($f_data['rf_total']));

				// Reponse code of >= 50 if the refund was NOT successful.
				if($result_refund->response()->receipt->ResponseCode >= 50 || $result_refund->response()->receipt->ResponseCode == "null" || $result_refund->response()->receipt->ResponseCode == NULL) {
					$execute_functions = NULL;
					$fluid->db_error = "ERROR: Refund declined. There was a error. Moneris error code: " . $result_refund->response()->receipt->ResponseCode . " - " . $result_refund->response()->receipt->Message;
				}
				else {
					// Refund successful, lets save and update our data.
					$fluid_alt->php_db_begin();

					$f_transactions_array = Array();
					$f_transactions_array['st_s_order_number'] = "'" . $fluid_alt->php_escape_string($st_data['st_s_order_number']) . "'";
					$f_transactions_array['st_s_transaction_serialize_64'] = "'" . $fluid_alt->php_escape_string(base64_encode(serialize($result_refund->response()->asXML()))) . "'";

					$f_columns_trans = implode(", ", array_keys($f_transactions_array));
					$f_values_trans  = implode(", ", array_values($f_transactions_array));
					$f_sales_trans_query = "INSERT INTO " . TABLE_SALES_TRANSACTIONS . " (" . $f_columns_trans . ") VALUES (" . $f_values_trans . ");";

					$fluid_alt->php_db_query($f_sales_trans_query);

					$i = 0;
					$case = "`si_p_refund` = CASE";
					$where = "WHERE si_id IN (";
					$s_refund = FALSE;
					foreach($f_data['rf_items'] as $key => $f_items) {
						if($key != 'ship') {
							if($i > 0) {
								$where .= ", ";
							}
							$case .= " WHEN (`si_s_order_number`, `si_id`) = ('" . $fluid_alt->php_escape_string($st_data['st_s_order_number']) . "', '" . $key . "') THEN '1'";

							$where .= "'" . $key . "'";
							$i++;
						}

						if($key == 'ship')
							$s_refund = TRUE;
					}
					$case .= " END";
					$where .= ")";

					// Only update the items if any where found. If a shipping was refunded only, there are no items.
					if($i > 0)
						$fluid_alt->php_db_query("UPDATE " . TABLE_SALES_ITEMS . " SET " . $case . " " . $where);

					// Only update the shipping refund flag is shipping was refunded.
					if($s_refund == TRUE)
						$fluid_alt->php_db_query("UPDATE " . TABLE_SALES . " SET s_ship_refund = '" . $fluid_alt->php_escape_string("1") . "', s_refund_total = IFNULL(s_refund_total, 0) + " . base64_decode($f_data['rf_total']) . " WHERE s_id = '" . $fluid_alt->php_escape_string(base64_decode($f_data['rf_sid'])) . "'");
					else
						$fluid_alt->php_db_query("UPDATE " . TABLE_SALES . " SET s_refund_total = IFNULL(s_refund_total, 0) + " . base64_decode($f_data['rf_total']) . " WHERE s_id = '" . $fluid_alt->php_escape_string(base64_decode($f_data['rf_sid'])) . "'");

					$fluid_alt->php_db_query("SELECT `s_refund_total` FROM " . TABLE_SALES . " WHERE s_id = '" . $fluid_alt->php_escape_string(base64_decode($f_data['rf_sid'])) . "'");

					$f_refund_html = NULL;
					if(isset($fluid_alt->db_array)) {
						if(!empty($fluid_alt->db_array[0]['s_refund_total']))
							$f_refund_html = number_format($fluid_alt->db_array[0]['s_refund_total'], 2, ".", ",");
					}

					$fluid_alt->php_db_commit();

					if(!empty($f_refund_html)) {
						$execute_functions[]['function'] = "js_html_insert_element";
						end($execute_functions);
						$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("p_id_td_refund_" . base64_decode($f_data['rf_sid'])), "innerHTML" => base64_encode($f_refund_html))));
					}

					$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . $f_data['rf_sid'])));

					$execute_functions[]['function'] = "js_fluid_ajax";
					end($execute_functions);
					$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_url));
				}
			}
		}

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_refund_confirm() {
	$fluid = new Fluid ();
	$fluid_alt = new Fluid();

	try {
		$fluid->php_db_begin();
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		// Warning Message
		$output = "<div style='margin-bottom:20px;'>";
		$output .= "<div class='alert alert-danger' role='alert'>";

		$output_files = "<div class='well' style='max-height: 65vh !important; overflow-y: scroll;'>";

		$fluid->php_db_query("SELECT * FROM " . TABLE_SALES . " WHERE s_id = '" . $fluid->php_escape_string(base64_decode($f_data['rf_sid'])) . "' ORDER BY s_id DESC");
		$fluid->php_db_commit();
		$i = 0;
		$f_rv_items = NULL;

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $s_data) {
				$tmp_ship = (array)json_decode(base64_decode($s_data['s_shipping_64']), true);

				foreach($tmp_ship as $t_ship) {
					$o_ship = $t_ship;
					break;
				}

				$fluid_alt->php_db_begin();

				$where = " WHERE si_id IN (";
				$i = 0;
				foreach($f_data['rf_items'] as $key => $product) {
					if($key != "ship") {
						if($i != 0)
							$where .= ", ";

						$where .= $fluid_alt->php_escape_string($key);

						$i++;
					}
				}
				$where .= ")";

				if($i > 0)
					$where .= " AND si_s_order_number = '" . $fluid_alt->php_escape_string($s_data['s_order_number']) . "'";
				else
					$where = " WHERE si_s_order_number = '" . $fluid_alt->php_escape_string($s_data['s_order_number']) . "'";


				$fluid_alt->php_db_query("SELECT * FROM " . TABLE_SALES_ITEMS . $where . " ORDER BY si_id ASC");
				$fluid_alt->php_db_commit();

				// Lets build the refund / void html data.
				$eq = new eqEOS();

				$t_taxes = json_decode($s_data['s_taxes'], TRUE);
				$f_ship['total'] = $s_data['s_shipping_total']; // Holding shipping tax information and shipping totals.
				$r_items = NULL; // --> Used for processing refunds and returns.
				$r_total = 0;

				// Storing tax data into each item.
				if(isset($fluid_alt->db_array)) {
					$r_items = $fluid_alt->db_array;

					// Record shipping tax breakdown if required.
					if(isset($f_data['rf_items']['ship'])) {
						if(isset($f_data['rf_items']['ship']))
							$r_total = $r_total + $f_ship['total'];
					}

					// Record item tax breakdown.
					foreach($r_items as $p_key => $p_data) {
						if(isset($f_data['rf_items'][$p_data['si_id']]))
							$r_total = $r_total + $p_data['si_p_price'];
					}

					$r_total_tmp = $r_total;

					foreach($t_taxes as $t_key => $t_data) {
						// Record shipping tax breakdown.
						if(isset($f_data['rf_items']['ship'])) {
							$equation = str_replace("[f_item]", $f_ship['total'], $t_data['t_math']);
							$f_ship['taxes'][$t_data['t_name']] = round($eq->solveIF($equation), 2);
						}

						// Record item tax breakdown.
						foreach($r_items as $p_key => $p_data) {
							$equation = str_replace("[f_item]", $p_data['si_p_price'], $t_data['t_math']);
							$r_items[$p_key]['si_p_taxes'][$t_data['t_name']] = round($eq->solveIF($equation), 2);
						}

						$equation = str_replace("[f_item]", $r_total_tmp, $t_data['t_math']);
						$r_tmp = round($eq->solveIF($equation), 2);

						$r_total = $r_total + $r_tmp;
					}
				}

				$f_rv_items = NULL;
				$i = 0;
				$border_top = NULL;
				if(isset($r_items)) {
					foreach($r_items as $f_item_key => $data) {
						if(isset($f_data['rf_items'][$data['si_id']])) {
							$width_height = $fluid->php_process_image_resize($data['si_p_image'], "60", "60");
							// Build the item list for the refund section.
							if($i == 0)
								$border_top = " border-top: 1px solid #bbb;";
							else
								$border_top = NULL;

							$f_rv_items .= "<div class='fluid-cart'>";

								$f_rv_items .= "<div class='divTable'>";
									$f_rv_items .= "<div class='divTableBody'>";
										$f_rv_items .= "<div class='divTableRow'>";
											$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt='Buy " . $data['si_m_name'] . " " . $data['si_p_name'] . "'></img></div>";

											$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $data['si_m_name'] . " " . $data['si_p_name'];

											$f_rv_items .= "<div style='padding-top: 10px;'>";
													$f_rv_items .= "<div class='pull-left' style='font-weight: 400; font-size: 10px;'>";
													$f_rv_items .= "<div style='display: inline-block;'>Price: " . HTML_CURRENCY . " " . $data['si_p_price'] . "</div>";

													$si_total = $data['si_p_price'];
													
													if($f_data['tax_toggle'] == true) {
														foreach($data['si_p_taxes'] as $si_key => $si_taxes) {
															$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>" . $si_key . ": " . HTML_CURRENCY . " " . number_format($si_taxes, 2, '.', ',') . "</div>";

															$si_total = $si_total + $si_taxes;
														}
													}
													if(isset($data['si_serial_numbers'])) {
														$f_rv_items .= "<div style='display: table;'>";
															$f_serials = json_decode($data['si_serial_numbers'], TRUE);

															foreach($f_serials as $serial_key => $serials) {
																$f_rv_items .= "<div style='display: table-cell; padding: 5px;'>" . $serials . "</div>";
															}

														$f_rv_items .= "</div>";
													}

													$f_rv_items .= "</div>"; // pull-left;
													$f_rv_items .= "<div class='pull-right' style='font-weight: 400; font-size: 10px;' data-total='" . number_format($si_total, 2, '.', '') . "'>Total: " . HTML_CURRENCY . " " . number_format($si_total, 2, '.', ',') . "</div>";
											$f_rv_items .= "</div>";

											$f_rv_items .= "</div>"; //div table cell orders #2

										$f_rv_items .= "</div>";
									$f_rv_items .= "</div>";
								$f_rv_items .= "</div>";

							$f_rv_items .= "</div>"; // fluid-cart

							$i++;
						}
					}
				}

				if($i > 0)
					$border_top = NULL;
				else
					$border_top = " border-top: 1px solid #bbb;";

				if($o_ship['data']['ship_type'] != IN_STORE_PICKUP && isset($f_data['rf_items']['ship'])) {
					$width_height = $fluid->php_process_image_resize(FOLDER_FILES . IMG_NO_IMAGE , "60", "60");
						$f_rv_items .= "<div class='fluid-cart'>";

							$f_rv_items .= "<div class='divTable'>";
								$f_rv_items .= "<div class='divTableBody'>";
									$f_rv_items .= "<div id='row-rid-ship' class='divTableRow' onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_refund_p_select(\"ship\");'>";
										$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px; width: " . $width_height['width'] . "px; height: " . $width_height['height'] . "px;' alt='Shipping'></img></div>";

										$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . utf8_decode($o_ship['type'] . " " . $o_ship['data']['ship_type']);

										$f_rv_items .= "<div style='padding-top: 10px;'>";
												$f_rv_items .= "<div class='pull-left' style='font-weight: 400; font-size: 10px;'>";
												$f_rv_items .= "<div style='display: inline-block;'>Price: " . HTML_CURRENCY . " " . $f_ship['total'] . "</div>";

												$si_total = $f_ship['total'];

												if($f_data['tax_toggle'] == true) {
													foreach($f_ship['taxes'] as $si_key => $si_taxes) {
														$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>" . $si_key . ": " . HTML_CURRENCY . " " . number_format($si_taxes, 2, '.', ',') . "</div>";

														$si_total = $si_total + $si_taxes;
													}
												}
												
												$f_rv_items .= "</div>"; // pull-left;
												$f_rv_items .= "<div id='f-rv-div-ship' class='pull-right' style='font-weight: 400; font-size: 10px;' data-total='" . number_format($si_total, 2, '.', '') . "'>Total: " . HTML_CURRENCY . " " . number_format($si_total, 2, '.', ',') . "</div>";
										$f_rv_items .= "</div>";

										$f_rv_items .= "</div>"; //div table cell orders #2

									$f_rv_items .= "</div>";
								$f_rv_items .= "</div>";
							$f_rv_items .= "</div>";

						$f_rv_items .= "</div>"; // fluid-cart

					$i++; // --> Required if no items are selected, we need to tell the code below that we found a shipping refund at least.
				}

			}
		}

		$output_files .= $f_rv_items . "</div>";

		if($i > 0) {
			$output .= "WARNING: The items listed below will be refunded for a total of: " . HTML_CURRENCY . " " . number_format($r_total, 2, '.', ',') . "</div>" . $output_files;
			$output .= "<input type='hidden' id='f-refund-id-total' value='" . base64_encode(number_format($r_total, 2, '.', '')) . "'>";

			$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_refund();'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");
		}
		else {
			/*
			$output .= "</div>"; //alert-danger div
			$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button>");
			*/

			// --> No items were selected, we are in manual mode.
			$r_total = $f_data['rf_total'];

			$output .= "WARNING: You are abount to refund for a total of: " . HTML_CURRENCY . " " . number_format($r_total, 2, '.', ',') . "</div>";
			$output .= "<input type='hidden' id='f-refund-id-total' value='" . base64_encode(number_format($r_total, 2, '.', '')) . "'>";

			$confirm_footer = base64_encode("<button type=\"button\" style='float:left;' class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button><button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\" onClick='js_refund();'><span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span> Yes</button>");
		}

		$output .= "</div>";

		$confirm_message = base64_encode("<div class='alert alert-danger' role='alert'>Are you sure?</div>");

		$modal = "<div class='modal-dialog f-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Refund</div>
				</div>

				<div class='modal-body'>";
				$modal .= $output;
				$modal .= "</div>

			 <div class='modal-footer'>
				  <div style='float:left;'><button type='button' class='btn btn-warning' data-dismiss='modal' onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>
				  <div style='float:right;'><button type='button' class='btn btn-danger' onClick='js_modal_confirm(Base64.decode(\"" . base64_encode('#fluid-modal-msg') . "\"), Base64.decode(\"" . $confirm_message . "\"), Base64.decode(\"" . $confirm_footer . "\"));' >Continue <span class=\"glyphicon glyphicon-arrow-right\" aria-hidden=\"true\"></span></button></div>
			 </div>

			</div>
		  </div>";

		// Follow up functions to execute on a server response back to the user.
		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-msg"), "innerHTML" => base64_encode($modal))));
		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-msg"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Save a new serial number.
function php_serial_save() {
	try {
		$fluid = new Fluid();
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		if(isset($f_data['s_number'])) {
			if(strlen($f_data['s_number']) > 0) {
				$fluid->php_db_begin();
				$fluid->php_db_query("SELECT si_serial_numbers FROM " . TABLE_SALES_ITEMS . " WHERE si_id = '" . $fluid->php_escape_string($f_data['si_id']) . "' ORDER BY si_id ASC LIMIT 1;");

				$f_serial = NULL;
				if(isset($fluid->db_array)) {
					if(!empty($fluid->db_array[0]['si_serial_numbers']))
						$f_serial = json_decode($fluid->db_array[0]['si_serial_numbers'], TRUE);
				}

				$f_serial[] = $f_data['s_number'];
				$f_serial = json_encode($f_serial);

				$fluid->php_db_query("UPDATE " . TABLE_SALES_ITEMS . " SET `si_serial_numbers` = '" . $f_serial ."' WHERE si_id = '" . $fluid->php_escape_string($f_data['si_id']) . "';");

				$fluid->php_db_commit();
			}
		}

		$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . base64_encode($f_data['s_id']) . "&f_tab=serial")));

		$execute_functions[]['function'] = "js_fluid_ajax";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_url));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Update serial numbers.
function php_serial_update() {
	try {
		$fluid = new Fluid();
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		if(isset($f_data['s_serials'])) {
			if(count($f_data['s_serials']) > 0) {
				$fluid->php_db_begin();
				$fluid->php_db_query("SELECT si_serial_numbers FROM " . TABLE_SALES_ITEMS . " WHERE si_id = '" . $fluid->php_escape_string($f_data['si_id']) . "' ORDER BY si_id ASC LIMIT 1;");

				$f_serial = NULL;
				if(isset($fluid->db_array)) {
					if(!empty($fluid->db_array[0]['si_serial_numbers']))
						$f_serial = json_decode($fluid->db_array[0]['si_serial_numbers'], TRUE);
				}

				if(isset($f_serial)) {
					foreach($f_serial as $f_key => $s_data) {
						if(array_key_exists($f_key, $f_data['s_serials']))
							unset($f_serial[$f_key]);
					}

					if(count($f_serial) > 0) {
						$f_serial = array_values($f_serial);
						$f_serial = json_encode($f_serial);
					}
					else
						$f_serial = NULL;

					if(isset($f_serial))
						$fluid->php_db_query("UPDATE " . TABLE_SALES_ITEMS . " SET `si_serial_numbers` = '" . $f_serial ."' WHERE si_id = '" . $fluid->php_escape_string($f_data['si_id']) . "';");
					else
						$fluid->php_db_query("UPDATE " . TABLE_SALES_ITEMS . " SET `si_serial_numbers` = NULL WHERE si_id = '" . $fluid->php_escape_string($f_data['si_id']) . "';");
				}

				$fluid->php_db_commit();
			}
		}

		$temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . base64_encode($f_data['s_id']) . "&f_tab=serial")));

		$execute_functions[]['function'] = "js_fluid_ajax";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode($temp_url));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => count($fluid->db_error), "error_message" => base64_encode($fluid->db_error)));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// Serial number creator.
function php_serial_modal() {
	try {
		$f_data = json_decode(base64_decode($_REQUEST['data']), TRUE);

		if($f_data['s_mode'] == "create")
			$s_title = "creator";
		else
			$s_title = "editor";

		$modal = "
		<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div class='modal-content'>

				<div class='panel-default'>
				  <div class='panel-heading'>Serial number " . $s_title . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

				<div class='modal-body' style='padding: 0px;'>

					<div id='serial-innerhtml' class='panel panel-default' style='border-top: 0px; border-bottom: 0px; margin-bottom: 0px; max-height:60vh; overflow-y: scroll;'>
						<div style='padding-top: 15px;'>";

							if($f_data['s_mode'] == "create") {
								$f_html = "
								<div class=\"alert alert-danger\" role=\"alert\" style='margin-left:10px; margin-right: 10px;'>

									<div style='font-weight: 600; padding-bottom: 5px;'>Enter a serial number:</div>
									<div>Scan the serial number on the box of the item or manually enter it yourself.</div>
								</div>

								<div style='margin-left:10px; margin-right: 10px; padding-top: 10px; padding-bottom: 30px;'>

									<div class='list-group'>

											<div class=\"input-group\" style='padding-top: 10px;'>
												<span class=\"input-group-addon\"><div style='width:120px !important;'>Serial number</div></span>
												<input id=\"f_serial_input\" type=\"text\" class=\"form-control\" placeholder=\"Serial number\">
											</div>
									</div>
								</div>";
							}
							else {
								$fluid = new Fluid();

								$fluid->php_db_begin();
								$fluid->php_db_query("SELECT si_serial_numbers FROM " . TABLE_SALES_ITEMS . " WHERE si_id = '" . $fluid->php_escape_string($f_data['si_id']) . "' ORDER BY si_id ASC LIMIT 1;");
								$fluid->php_db_commit();

								if(isset($fluid->db_array)) {
									$f_serial = json_decode($fluid->db_array[0]['si_serial_numbers'], TRUE);

									$f_html = "
									<div class=\"alert alert-danger\" role=\"alert\" style='margin-left:10px; margin-right: 10px;'>

										<div style='font-weight: 600; padding-bottom: 5px;'>Serial number removal:</div>
										<div>Click on the red delete button to remove a serial number, then click save to update the changes.</div>
									</div>

									<div style='margin-left:10px; margin-right: 10px; padding-top: 10px; padding-bottom: 30px;'>";

									// --> 1. Have onClick on the red button to delete the dom, but store the key id into a fluid global variable. Have the fluid global variable wipe itself everytime this modal loads.
									// --> 2. Add a save button when in edit mode onto this modal.
									foreach($f_serial as $s_key => $s_data) {
										$f_html .= "<div id='s_serial_div_" . $s_key . "' class='list-group'>";
											$f_html .= "<div class=\"input-group\" style='padding-top: 10px;'>";
												$f_html .= "<div style='display: inline-block; float: left; padding-right: 15px;'><button class='btn btn-danger btn-sm' onClick='FluidVariables.s_serial[\"" . $s_key . "\"] = {\"s_key\" : \"" . $s_key . "\", \"s_data\" : \"" . $s_data . "\"}; js_html_remove_element(\"s_serial_div_" . $s_key . "\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></button></div>";
												$f_html .= "<div style='display: inline-block;'><input type=\"text\" class=\"form-control\" placeholder=\"Serial number\" value=\"" . $s_data . "\" disabled></div>";
											$f_html .= "</div>";

											//$f_html .= $s_key . " | " . $s_data;
										$f_html .= "</div>";
									}


									$f_html .= "</div>";
								}

								$f_html .= NULL;
							}

							$modal .= $f_html;
						$modal .= "</div>
					</div>

				</div>";

			  $modal .= "<div class='modal-footer'>";

			  $temp_url = base64_encode(json_encode(Array("serverurl" => $_SERVER['SERVER_NAME'] . "/" . FLUID_ORDERS_ADMIN, "dataobj" => "load=true&function=php_view_order&s_id=" . base64_encode($f_data['s_id']) . "&f_tab=serial")));

			  $footer_save_html = "<div style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal' onClick='js_fluid_ajax(\"" . $temp_url . "\");'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Cancel</button></div>";

			  if($f_data['s_mode'] == "create")
				$footer_save_html .= "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_serial_save(\"" . $f_data['si_id'] . "\", \"" . $f_data['s_id'] . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>";
 			  else
				$footer_save_html .= "<div style='float:right;'><button type='button' class='btn btn-success' onClick='js_serial_update(\"" . $f_data['si_id'] . "\", \"" . $f_data['s_id'] . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>";

			  $modal .= $footer_save_html;

			  $modal .= "</div>

			</div>
		  </div>";

		$execute_functions[]['function'] = "js_modal";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(array("modal_html" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

function php_fluid_email_order_status_confirm() {
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

			  <div class='modal-footer'>
				  <div style='display: inline-block; float:left;'><button type=\"button\" class=\"btn btn-warning\" data-dismiss=\"modal\" onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-arrow-left\" aria-hidden=\"true\"></span> Back</button></div><div style='display: inline-block; float:right;'><button type='button' class='btn btn-info' data-dismiss='modal' onClick='js_order_email_editor(\"" . $f_data->s_id . "\", \"" . base64_encode(utf8_encode("Leo's Camera Supply order # " . $f_data->s_id)) . "\");'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span> Edit Email</button> <button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_email_order_status(\"" . $f_data->s_id . "\");'><span class=\"glyphicon glyphicon-send\" aria-hidden=\"true\"></span> Send Email</button></div></div>
			  </div>
			</div>
		  </div>";

		$f_email_message = "<div class=\"alert alert-danger\" role=\"alert\">WARNING: You are about to send a email to: " . $f_data->s_email . "</div><div class='well'><div id='f-email-message'>Leo's Camera Supply order update.<br><br>";
		$f_email_message .= "Hello, the status of your order has been updated.<br><br>";
		$f_email_message .= "Order number: " . explode('-', $f_data->s_order_number)[2] . "<br>";
		$f_email_message .= "Order date: " . $f_data->s_date . "<br>";
		$f_email_message .= "Order status: " . $fluid->php_fluid_order_status($f_data->s_status) . "<br>";

		if($f_data->s_status == 1)
			$f_email_message .= "<br>";
		else if($f_data->s_status == 2) {
			$f_email_message .= "Shipping type: " . $f_data->s_shipping_type . "<br>";
			if($f_data->s_tracking != "")
				$f_email_message .= "Tracking number: " . $f_data->s_tracking . "<br><br>";
			else
				$f_email_message .= "<br>";
		}
		else if($f_data->s_status == 3)
			$f_email_message .= "Important reminder: For in store pickups, the credit card holder must be present, provide the credit card used for the purchase and show government identification matching the cardholders address.<br><br>";

		$f_email_message .= "You can check the status of your order anytime, by going to My Orders in your account.<br><br>";
		$f_email_message .= "If you have any questions to this order, or the items it relates to, please contact us at 1-604-685-5331 or email at orders@leoscamera.com<br></div>";

		$f_email_message .= "<br><br>" . EMAIL_FOOTER . "</div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-msg"), "innerHTML" => base64_encode($f_email_modal))));

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-email-modal-innerhtml"), "innerHTML" => base64_encode($f_email_message))));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
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

function php_fluid_email_order_status() {
	try {
		$fluid = new Fluid();

		$f_data = json_decode(base64_decode($_REQUEST['data']));

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
				  <div style='float:right;'><button type='button' class='btn btn-success' data-dismiss='modal' onClick='js_modal_show(\"#fluid-modal\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Continue</button></div></div>
			  </div>
			</div>
		  </div>";

		/*
		$f_email_message = "Leo's Camera Supply order update.<br><br>";
		$f_email_message .= "Hello, the status of your order has been updated.<br><br>";
		$f_email_message .= "Order number: " . explode('-', $f_data->s_order_number)[2] . "<br>";
		$f_email_message .= "Order date: " . $f_data->s_date . "<br>";
		$f_email_message .= "Order status: " . $fluid->php_fluid_order_status($f_data->s_status) . "<br>";
		if($f_data->s_status == 1)
			$f_email_message .= "<br>";
		else if($f_data->s_status == 2) {
			$f_email_message .= "Shipping type: " . $f_data->s_shipping_type . "<br>";
			if($f_data->s_tracking != "")
				$f_email_message .= "Tracking number: " . $f_data->s_tracking . "<br><br>";
			else
				$f_email_message .= "<br>";
		}
		else if($f_data->s_status == 3)
			$f_email_message .= "Important reminder: For in store pickups, the credit card holder must present during in store pickups and must present valid identification.<br><br>";

		$f_email_message .= "You can check the status of your order anytime, by going to My Orders in your account.<br><br>";
		$f_email_message .= "If you have any questions to this order, or the items it relates to, please contact us at 1-604-685-5331 or email at orders@leoscamera.com<br><br><br>";
		*/

		$f_email_message = base64_decode($f_data->s_email_message);

		$f_email_data = addslashes(base64_encode(json_encode(Array("from" => "orders@leoscamera.com", "to" => $f_data->s_email, "subject" => "Leo's Camera Supply order # " . explode('-', $f_data->s_order_number)[2] . " update", "message" => $f_email_message))));

		// Lets send a copy to our order email address for safe keeping.
		$f_email_data_self = addslashes(base64_encode(json_encode(Array("from" => "orders@leoscamera.com", "to" => "orders@leoscamera.com", "subject" => "Leo's Camera Supply order # " . explode('-', $f_data->s_order_number)[2] . " update", "message" => $f_email_message))));

		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-msg"));

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal-msg"), "innerHTML" => base64_encode($f_email_modal))));

		/*
			0 = error
			1 = processing
			2 = shipped
			3 = ready for pickup
			4 = pre ordered
			5 = refund
			6 = cancelled
		*/
		//exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /var/log/fluid.debug.log &');
		exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /dev/null &');
		
		//exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data_self . '" > /dev/null &');
		$f_success_message = "<div class=\"alert alert-success\" role=\"alert\">Email sent successfully to: " . $f_data->s_email . "</div>";
		$f_success_message .= "<div class='well'>";
		$f_success_message .= print_r($f_email_message . "<br>" . EMAIL_FOOTER, TRUE);
		$f_success_message .= "</div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-email-modal-innerhtml"), "innerHTML" => base64_encode($f_success_message))));

		/*
		if($f_data->s_status != 0) {
			exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data . '" > /var/log/fluid.debug.log &');
			//exec('/usr/bin/php ' . FOLDER_ROOT . '../fluid.sendmail.php "' . $f_email_data_self . '" > /dev/null &');
			$f_success_message = "<div class=\"alert alert-success\" role=\"alert\">Email sent successfully to: " . $f_data->s_email . "</div>";
			$f_success_message .= "<div class='well'>";
			$f_success_message .= print_r($f_email_message . "<br>" . EMAIL_FOOTER, TRUE);
			$f_success_message .= "</div>";

			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-email-modal-innerhtml"), "innerHTML" => base64_encode($f_success_message))));
		}

		else {
			$f_success_message = "<div class=\"alert alert-danger\" role=\"alert\">Email not sent. Reasons for not being sent are, a error in the delivery system or the order status set to ERROR, PREORDER or REFUND.</div>";
			$execute_functions[]['function'] = "js_html_insert_element";
			end($execute_functions);
			$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("f-email-modal-innerhtml"), "innerHTML" => base64_encode($f_success_message))));
		}
		*/

		/*
		$execute_functions[]['function'] = "js_modal_hide";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));
		*/

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal-msg"));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions))));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

// View a order in extra detail.
function php_view_order() {
	$fluid = new Fluid();
	$fluid_alt = new Fluid();
	try {
		$fluid->php_db_begin();

		$s_id = base64_decode($_REQUEST['s_id']);

		if(isset($_REQUEST['f_tab']))
			$f_tab = $_REQUEST['f_tab'];
		else
			$f_tab = NULL;

		$fluid->php_db_query("SELECT * FROM " . TABLE_SALES . " WHERE s_id = '" . $fluid->php_escape_string($s_id) . "' ORDER BY s_id DESC");

		$fluid->php_db_commit();

		$html = NULL;
		$html_header = NULL;
		$s_status = NULL;
		$si_html = NULL;
		$si_html_customer = NULL;
		$si_header = NULL;
		$invoice_items = NULL;
		$invoice_customer = NULL;
		$si_i_print_html = "<div class='f-print-notify' style='width: 100%; text-align: center; font-weight: 600px; padding-bottom: 5px;'>-------------------- Store Copy --------------------</div>";

		if(isset($fluid->db_array)) {
			foreach($fluid->db_array as $s_data) {
				$s_status = $s_data['s_status'];
				$html_header = "Order Number: " . explode('-', $s_data['s_order_number'])[2];

				$tmp_ship = (array)json_decode(base64_decode($s_data['s_shipping_64']), true);

				foreach($tmp_ship as $t_ship) {
					$o_ship = $t_ship;
					break;
				}

				$s_items = json_decode(base64_decode($s_data['s_items_64']), true);

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

				$s_html = NULL;

				$s_box_counter = 0;
				// Build the html to show the boxes and packaging data for telling the employee how to package the order.
				foreach($o_ship['data']['f_package'] as $s_ship) {
					$s_box_counter++;

					$s_html .= "<div class='f-card-group-align-box-shadow-transparent'>";
						$s_html .= "<div style='font-weight: 600; padding: 10px;'>" . $s_ship['f_box_type'] . " - ";
							$s_html .= "<div style='display: inline-block; font-weight: 400; font-size: 12px;''>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>Length: " . $s_ship['length'] . " cm</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>Width: " . $s_ship['width'] . " cm</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>Height: " . $s_ship['height'] . " cm</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>Girth: " . $s_ship['girth'] . " cm</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>Weight: " . $s_ship['weight'] . " kg</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div>";
								$s_html .= "<div style='display: inline-block; padding-left: 5px;'>Value: " . HTML_CURRENCY . " " . number_format($s_ship['price'], 2, '.', ',') . "</div>";
							$s_html .= "</div>";
						$s_html .= "</div>";

						// Put more box information here: size / weight. Build a border around each box, like a card box shadow on listing page?
						$s_html .= "<div name='fluid-cart-scroll' class=' fluid-cart-no-scroll'>";

						$i = 0;
						foreach($s_ship['items'] as $p_items) {
							if($i == 0)
								$border_top = " border-top: 1px solid #bbb;";
							else
								$border_top = NULL;

							// Process the image.
							$width_height = $fluid->php_process_image_resize($p_items['p_image'], "60", "60");
							$s_html .= "<div class='fluid-cart'>";

								$s_html .= "<div class='divTable'>";
									$s_html .= "<div class='divTableBody'>";
										$s_html .= "<div class='divTableRow'>";
											$s_html .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $p_items['m_name'] . " " . $p_items['p_name'] . "'></img></div>";
											$s_html .= "<div class='divTableCellOrders' style='width: 100%; vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $p_items['m_name'] . " " . $p_items['p_name'] . "<div style='padding-top: 5px; font-size: 10px;'>";

											$s_html .= "<div style='display: inline-block; font-size: 10px;'>UPC # " . $p_items['p_mfgcode'] . "</div>";
												if(isset($p_items['p_mfg_number']))
													$s_html .= "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $p_items['p_mfg_number'] . "</div>";
											$s_html .= "</div>";

											$s_html .= "<div style='padding-top: 5px;'><div class='pull-left' style='font-weight: 400; font-size: 10px;'><div style='display: inline-block;'>Length: " . $p_items['p_length'] . " cm</div><div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>Width: " . $p_items['p_width'] . " cm</div><div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>Height: " . $p_items['p_height'] . " cm</div><div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px; font-weight: 400; font-size: 10px;'>Weight: " . $p_items['p_weight'] . " kg</div></div><div class='pull-right' style='font-weight: 400; font-size: 10px;'>Price: " . HTML_CURRENCY . " " . $p_items['p_price'] . "</div></div></div>";
										$s_html .= "</div>";
									$s_html .= "</div>";
								$s_html .= "</div>";

							$s_html .= "</div>"; // fluid-cart

							$i++;
						}

						$s_html .= "</div>"; // fluid-cart-no-scroll
					$s_html .= "</div>"; // f-card-group-align-box-shadow-transparent

					$i++;
				}

				// --> *********************** REFUND SECTION *******************************
				$fluid_alt->php_db_begin();
				$fluid_alt->php_db_query("SELECT * FROM " . TABLE_SALES_ITEMS . " WHERE si_s_order_number = '" . $fluid_alt->php_escape_string($s_data['s_order_number']) . "' ORDER BY si_id ASC");
				$fluid_alt->php_db_commit();

				// Lets build the refund / void html data.
				$eq = new eqEOS();

				$t_taxes = json_decode($s_data['s_taxes'], TRUE);
				$f_ship['total'] = $s_data['s_shipping_total']; // Holding shipping tax information and shipping totals.
				$r_items = NULL; // --> Used for processing refunds and returns.

				// Storing tax data into each item.
				if(isset($fluid_alt->db_array)) {
					$r_items = $fluid_alt->db_array;

					if(isset($t_taxes)) {
						foreach($t_taxes as $t_key => $t_data) {
							// Record shipping tax breakdown.
							if($f_ship['total'] > 0) {
								$equation = str_replace("[f_item]", $f_ship['total'], $t_data['t_math']);
								$f_ship['taxes'][$t_data['t_name']] = round($eq->solveIF($equation), 2);
							}

							// Record item tax breakdown.
							foreach($r_items as $p_key => $p_data) {
								$equation = str_replace("[f_item]", $p_data['si_p_price'], $t_data['t_math']);
								$r_items[$p_key]['si_p_taxes'][$t_data['t_name']] = round($eq->solveIF($equation), 2);
							}
						}
					}
				}
				/*
				 // --> Tax array data format. key => is the tax name. value => is the total amount of tax for that item.
					[si_p_taxes] => Array
					(
						[GST] => 1.5
						[PST] => 2.1
					)
				*/

				$html = "<div name='fluid-cart-scroll' class=' fluid-cart-no-scroll'>";
				$f_rv_items = NULL;
				$i = 0;
				$f_rebate_claim = FALSE;
				$invoice_items_customer = NULL;

				if(isset($r_items)) {
					$f_inv_items = NULL;

					foreach($r_items as $data) {
						if(isset($f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])])) {
							if($f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])]['si_serial_numbers'] != $data['si_serial_numbers']) {
								$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price']) . '-' . $i] = $data;
								$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price']) . '-' . $i]['p_qty'] = 1;
							}
							else {
								if(empty($f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])]['p_qty'])) {
									$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])]['p_qty'] = 1;
								}
								else {
									$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])]['p_qty']++;
								}
							}
						}
						else if(isset($data['si_serial_numbers'])) {
							$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price']) . '-' . $i] = $data;
							$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price']) . '-' . $i]['p_qty'] = 1;
						}
						else {
							$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])] = $data;
							$f_inv_items[base64_encode($data['si_p_id'] . $data['si_p_price'])]['p_qty'] = 1;
						}

						$width_height = $fluid->php_process_image_resize($data['si_p_image'], "60", "60");
						// Build the item list for the refund section.
						if($i == 0)
							$border_top = " border-top: 1px solid #bbb;";
						else
							$border_top = NULL;

						$f_rv_items .= "<div class='fluid-cart'>";

							$f_rv_items .= "<div class='divTable'>";
								$f_rv_items .= "<div class='divTableBody'>";

								$class_style = NULL;
								$onClick = NULL;
								if($data['si_p_refund'] == NULL) {
									if(isset($_SESSION['u_access_admin'])) {
										if($_SESSION['u_access_admin'] == 'all') {
											$onClick = " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_refund_p_select(\"" . $data['si_id'] . "\");'";
										}
									}
								}
								else
									$class_style = " style='text-decoration: line-through; background-color: " . COLOUR_DISABLED_ITEMS . ";'";

									$f_rv_items .= "<div id='row-rid-" . $data['si_id'] . "' class='divTableRow'" . $onClick . $class_style . ">";
										$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $data['si_m_name'] . " " . $data['si_p_name'] . "'></img></div>";

										$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $data['si_m_name'] . " " . $data['si_p_name'];

											$f_rv_items .= "<div style='padding-top: 5px;'>";
											$f_rv_items .= "<div style='display: inline-block; font-size: 10px;'>UPC # " . $data['si_p_mfgcode'] . "</div>";
												if(isset($data['si_p_mfg_number']))
													$f_rv_items .= "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $data['si_p_mfg_number'] . "</div>";
											$f_rv_items .= "</div>";

										$f_rv_items .= "<div style='padding-top: 10px;'>";
												$f_rv_items .= "<div class='pull-left' style='font-weight: 400; font-size: 10px;'>";
												$f_rv_items .= "<div style='display: inline-block;'>Price: " . HTML_CURRENCY . " " . $data['si_p_price'] . "</div>";

												$si_total = $data['si_p_price'];
												$si_tax = 0;
												if(isset($data['si_p_taxes'])) {
													foreach($data['si_p_taxes'] as $si_key => $si_taxes) {
														$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>" . $si_key . ": " . HTML_CURRENCY . " " . number_format($si_taxes, 2, '.', ',') . "</div>";

														$si_total = $si_total + $si_taxes;
														$si_tax = $si_tax + $si_taxes;
													}
												}

												if($data['si_p_refund'] != NULL)
													$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>REFUNDED</div>";

												if($data['si_p_rebate_claim'] == TRUE)
													$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='color: #FF009F; display: inline-block; padding-left: 5px;'>Rebate / Claim item</div>";

												if(isset($data['si_serial_numbers'])) {
													$f_rv_items .= "<div style='display: table;'>";
														$f_serials = json_decode($data['si_serial_numbers'], TRUE);

														foreach($f_serials as $serial_key => $serials) {
															$f_rv_items .= "<div style='display: table-cell; padding: 5px;'>" . $serials . "</div>";
														}

													$f_rv_items .= "</div>";
												}

												$f_rv_items .= "</div>"; // pull-left;
												$f_rv_items .= "<div id='f-rv-div-" . $data['si_id'] . "' class='pull-right' style='font-weight: 400; font-size: 10px;' data-tax='" . number_format($si_tax, 2, '.', '') . "' data-total='" . number_format($data['si_p_price'], 2, '.', '') . "'>Total: " . HTML_CURRENCY . " " . number_format($si_total, 2, '.', ',') . "</div>";
										$f_rv_items .= "</div>";

										$f_rv_items .= "</div>"; //div table cell orders #2

									$f_rv_items .= "</div>";
								$f_rv_items .= "</div>";
							$f_rv_items .= "</div>";

						$f_rv_items .= "</div>"; // fluid-cart

						// Let's build html item listing showing rebate info, etc. // --> For the item and invoice tabs.
						$html .= "<div class='fluid-cart'>";
							$html .= "<div class='divTable'>";
								$html .= "<div class='divTableBody' style='width: 100%;'>";

									$html .= "<div class='divTableRow' style='width: 100%;'>";
										$html .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $data['si_m_name'] . " " . $data['si_p_name'] . "'></img></div>";

										$html .= "<div class='divTableCellOrders' style='width: 100%; vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $data['si_m_name'] . " " . $data['si_p_name'];

										$html .= "<div style='padding-top: 5px;'>";
										$html .= "<div style='display: inline-block; font-size: 10px;'>UPC # " . $data['si_p_mfgcode'] . "</div>";
											if(isset($data['si_p_mfg_number']))
												$html .= "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $data['si_p_mfg_number'] . "</div>";
										$html .= "</div>";

										$html .= "<div style='padding-top: 10px;'>";
												$html .= "<div class='pull-left' style='font-weight: 400; font-size: 10px;'>";
												$html .= "<div style='display: inline-block;'>Price: " . HTML_CURRENCY . " " . $data['si_p_price'] . "</div>";

												$si_total = $data['si_p_price'];
												$si_tax = 0;
												if(isset($data['si_p_taxes'])) {
													foreach($data['si_p_taxes'] as $si_key => $si_taxes) {
														$html .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>" . $si_key . ": " . HTML_CURRENCY . " " . number_format($si_taxes, 2, '.', ',') . "</div>";

														$si_total = $si_total + $si_taxes;
														$si_tax = $si_tax + $si_taxes;
													}
												}

												if($data['si_p_rebate_claim'] == TRUE) {
													$html .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='color: #FF009F; display: inline-block; padding-left: 5px;'>Rebate / Claim item</div>";

													$f_rebate_claim = TRUE;
												}

												if(isset($data['si_serial_numbers'])) {
													$html .= "<div style='display: table;'>";
														$f_serials = json_decode($data['si_serial_numbers'], TRUE);

														foreach($f_serials as $serial_key => $serials) {
															$html .= "<div style='display: table-cell; padding: 5px;'>" . $serials . "</div>";
														}

													$html .= "</div>";
												}

												$html .= "</div>"; // pull-left;
												$html .= "<div class='pull-right' style='font-weight: 400; font-size: 10px;' data-tax='" . number_format($si_tax, 2, '.', '') . "' data-total='" . number_format($data['si_p_price'], 2, '.', '') . "'>Total: " . HTML_CURRENCY . " " . number_format($si_total, 2, '.', ',') . "</div>";
										$html .= "</div>";

										$html .= "</div>"; //div table cell orders #2

									$html .= "</div>";
								$html .= "</div>";
							$html .= "</div>";
						$html .= "</div>"; // fluid-cart

						$i++;
					}

					//echo "<pre>";
						//print_r($f_inv_items);
					//echo "</pre>";


					foreach($f_inv_items as $data) {
						// Lets build some items to show on the invoice page.
						$inv_tmp_items = "<div class='fluid-cart'>";
							$inv_tmp_items .= "<div class='divTable'>";
								$inv_tmp_items .= "<div class='divTableBody' style='width: 100%;'>";

									$inv_tmp_items .= "<div class='divTableRow' style='width: 100%;'>";

										$si_total = $data['si_p_price'];
										$si_tax = 0;
										if(isset($data['si_p_taxes'])) {
											foreach($data['si_p_taxes'] as $si_key => $si_taxes) {
												$si_total = $si_total + $si_taxes;
												$si_tax = $si_tax + $si_taxes;
											}
										}
										$width_height = $fluid->php_process_image_resize($data['si_p_image'], "60", "60");

										// --> Print version.
										$inv_tmp_items .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $data['si_m_name'] . " " . $data['si_p_name'] . "'></img></div>";

										$inv_tmp_items .= "<div class='divTableCellOrders' style='width: 100%; vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $data['si_m_name'] . " " . $data['si_p_name'];

										$inv_tmp_items .= "<div style='padding-top: 5px;'>";
										//$inv_tmp_items .= "<div style='display: inline-block; font-size: 10px;'>QTY: " . $data['p_qty'] . "</div><i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i>";
										$inv_tmp_items .= "<div style='display: inline-block; font-size: 10px;'>UPC # " . $data['si_p_mfgcode'] . "</div>";
											if(isset($data['si_p_mfg_number']))
												$inv_tmp_items .= "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $data['si_p_mfg_number'] . "</div>";
										$inv_tmp_items .= "</div>";

										if(isset($data['si_serial_numbers'])) {
											$inv_tmp_items .= "<div style='padding-top: 5px;'>";
												$inv_tmp_items .= "<div class='' style='font-weight: 400; font-size: 10px;'>";


												if(isset($data['si_serial_numbers'])) {
													$f_serials = json_decode($data['si_serial_numbers'], TRUE);

													foreach($f_serials as $serial_key => $serials) {
														$inv_tmp_items .= "<div style='display: table-cell; padding: 5px;'>" . $serials . "</div>";
													}
												}

												$inv_tmp_items .= "</div>"; // pull-left;
											$inv_tmp_items .= "</div>";
										}

										$inv_tmp_items .= "<div style='padding-top: 5px;'>";
											$inv_tmp_items .= "<div class='' style='font-weight: 400; font-size: 10px;'>";
													$inv_tmp_items_customer = "<div style='display: table-cell; padding: 5px;'>QTY: " . $data['p_qty'] . "</div>";
													$s_tmp_stock = "?";
													
													if(empty($s_items[$data['si_p_id']])) {
														if(isset($s_items[$data['si_p_id'] . "-disc"])) {
															$s_tmp_stock = $s_items[$data['si_p_id'] . "-disc"]['p_stock'];
														}
													}
													else if(isset($s_items[$data['si_p_id']])) {
														$s_tmp_stock = $s_items[$data['si_p_id']]['p_stock'];
													}
													
													$f_component_message = NULL;
													if(isset($s_items[$data['si_p_id']]['p_component'])) {
														if($s_items[$data['si_p_id']]['p_component'] == TRUE) {
															$f_component_message = ".<br>NOTE: Component items required to build this item. Please scan those components out after item assembly.";
														}
													}
													
													$inv_tmp_items_stock = "<div style='display: table-cell; padding: 5px; color: red; font-style: italic; font-weight: bold;'>QTY: " . $data['p_qty'] . " | STOCK: " . $s_tmp_stock . " (at time of order)" . $f_component_message . "</div>";
											$inv_tmp_items_end = "</div>"; // pull-left;
										$inv_tmp_items_end .= "</div>";
										
										$inv_tmp_items_end .= "</div>"; //div table cell orders #2

										$inv_tmp_items_end .= "<div class='divTableCellOrders' style='min-width: 110px; text-align: right; vertical-align: bottom; font-size: 14px; font-weight: 400;" . $border_top . "'>";
											$inv_tmp_items_end .= "<div class='' style='min-width: 110px; text-align: right; font-weight: 400; font-size: 14px;' data-tax='" . number_format($si_tax, 2, '.', '') . "' data-total='" . number_format($data['si_p_price'], 2, '.', '') . "'>" . HTML_CURRENCY . " " . number_format($data['si_p_price'], 2, '.', ',') . " ea.</div>";
										$inv_tmp_items_end .= "</div>";

									$inv_tmp_items_end .= "</div>";
								$inv_tmp_items_end .= "</div>";
							$inv_tmp_items_end .= "</div>";
						$inv_tmp_items_end .= "</div>"; // fluid-cart

						// --> Build data now.
						$invoice_items .= $inv_tmp_items . $inv_tmp_items_stock . $inv_tmp_items_end;
						$invoice_items_customer .= $inv_tmp_items . $inv_tmp_items_customer . $inv_tmp_items_end;
					}
				}

				$html .= "</div>"; // fluid-cart-no-scroll

				// --> Lets build the shipping data.
				$sp_html = NULL;
					if($o_ship['data']['ship_type'] != IN_STORE_PICKUP && isset($o_ship['data']['s_data'])) {
						$sp_html = "<div style='display: table; width: 100%;'>";
							$sp_html .= "<div class='well f-card-group-align-box-shadow-transparent'>";
								$sp_html .= "<div style='font-weight: 600; font-style: italic;'></div>";
								$sp_html .= "<div style='display: table; width: 100%;'>";
									$sp_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";
										$sp_html .= "<div style='padding: 10px 10px 10px 10px;'><pre>";

										$sp_html .= print_r($o_ship['data']['s_data'], TRUE);
										//$sp_html .= $fluid->php_format_array($o_ship['data']['s_data']);

											//$sp_array = $fluid->php_array_flatten($o_ship['data']['s_data']);
											/*
											foreach($sp_array as $ship_key => $ship_d) {
												$i = 0;

												if($i == 0)
													$sp_padding = "style='padding-top:5px;'";
												else
													$sp_padding = "style='padding-top:5px;'";

												$sp_html .= "<div class=\"input-group\" " . $sp_padding . ">
													  <span class=\"input-group-addon\"><div style='width:120px !important;'>" . $ship_key . "</div></span>
													  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Order number\" aria-describedby=\"basic-addon1\" id='st-" . $ship_key . "' value=\"" . htmlspecialchars($ship_d) . "\">
													</div>";

												$i++;
											}
											*/
										$sp_html .= "</pre></div>";
									$sp_html .= "</div>";
								$sp_html .= "</div>";
							$sp_html .= "</div>";
						$sp_html .= "</div>";
					}
					else {
						$sp_html = "No data.";
					}
				// End of shipping data.

				// --> Lets build the transaction data.
				// Lets build the transaction data. This can be the sales and refunds etc.
				$fluid_alt->php_db_begin();
				$fluid_alt->php_db_query("SELECT * FROM " . TABLE_SALES_TRANSACTIONS . " WHERE st_s_order_number = '" . $fluid_alt->php_escape_string($s_data['s_order_number']) . "' ORDER BY st_id ASC");
				$fluid_alt->php_db_commit();

				$st_html = NULL;
				$f_moneris_obj = NULL;

				if(isset($fluid_alt->db_array)) {
					$it = 1;
					$style_st = NULL;

					foreach($fluid_alt->db_array as $st_data) {
						if($it > 1)
							$style_st = " style='margin-top: 0px;'";

						$st_html .= "<div style='display: table; width: 100%;'>";
							$st_html .= "<div class='well f-card-group-align-box-shadow-transparent'" . $style_st . ">";
								$st_html .= "<div style='font-weight: 600; font-style: italic;'>Transaction # " . $it . "</div>";
								$st_html .= "<div style='display: table; width: 100%;'>";
									$st_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";
										$st_html .= "<div style='padding: 10px 10px 10px 10px;'>";
											$paypal = NULL;
											$f_moneris_data = NULL;
											$auth_net_data = NULL;
											// Lets build the transaction html data. It is used later throughout for building the menus. It needs to be here so we can determine which refund methods we are going to use. (PayPal or Moneris, etc....).
											if(json_decode(unserialize(base64_decode($st_data['st_s_transaction_serialize_64'])))) {
												$paypal = json_decode(unserialize(base64_decode($st_data['st_s_transaction_serialize_64'])));
												
												if(isset($paypal->id)) {
													$f_paypal_data = print_r($paypal, TRUE);
													$st_html .= "<pre style='background-color: #F0F0F0;'>" . $f_paypal_data . "</pre>";
												}
											}
											else {
												if(simplexml_load_string(unserialize(base64_decode($st_data['st_s_transaction_serialize_64'])))) {
													// Lets build a moneris transaction html data.
													$simpleXmlElem = simplexml_load_string(unserialize(base64_decode($st_data['st_s_transaction_serialize_64'])));

													$f_moneris_data = print_r($simpleXmlElem, TRUE);
													$st_html .= "<pre style='background-color: #F0F0F0;'>" . $f_moneris_data . "</pre>";
													// A moneris transaction.
													if(isset($simpleXmlElem->receipt)) {
														foreach($simpleXmlElem->receipt as $key => $xml_data) {
															foreach($xml_data as $f_key => $f_data2) {
																$f_moneris_obj[$f_key] = $f_data2;
															}

															break; // --> We are getting the first object, which will be the original purchase only.

															/*$i = 0;
															foreach($xml_data as $f_key => $f_data2) {
																if($i == 0)
																	$st_padding = "style='padding-top:5px;'";
																else
																	$st_padding = "style='padding-top:5px;'";

																if($f_key == "TransType") {
																	switch ($xml_data->$f_key) {
																		case 00:
																			$fv_data = "00 : Purchase";
																		break;

																		case 01:
																			$fv_data = "01 : Pre-Authorization";
																		break;

																		case 02:
																			$fv_data = "02 : Pre-Authorization Completion";
																		break;

																		case 04:
																			$fv_data = "04 : Refund";
																		break;

																		case 11:
																			$fv_data = "11 : Purchase Correction";
																		break;

																		default:
																			$fv_data = $xml_data->$f_key;
																	}
																}
																else
																	$fv_data = $xml_data->$f_key;

																$st_html .= "<div class=\"input-group\" " . $st_padding . ">
																	  <span class=\"input-group-addon\"><div style='width:120px !important;'>" . $f_key . "</div></span>
																	  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Order number\" aria-describedby=\"basic-addon1\" id='st-" . $f_key . "' value=\"" . htmlspecialchars($fv_data) . "\">
																	</div>";

																$i++;
															}
															*/
														}
													}
												}
												else {
													$auth_net_data = json_decode(base64_decode($st_data['st_s_transaction_serialize_64']));
													
													$st_html .= "<pre style='background-color: #F0F0F0;'>" . print_r($auth_net_data, TRUE) . "</pre>";
												}
											}
										$st_html .= "</div>";
									$st_html .= "</div>";
								$st_html .= "</div>";
							$st_html .= "</div>";
						$st_html .= "</div>";

						$it++;
					}
				}
				// --> End of transaction data building.

				if($o_ship['data']['ship_type'] != IN_STORE_PICKUP && $f_ship['total'] > 0) {
					$width_height = $fluid->php_process_image_resize(FOLDER_FILES . IMG_NO_IMAGE , "60", "60");
						$f_rv_items .= "<div class='fluid-cart'>";

							$f_rv_items .= "<div class='divTable'>";
								$f_rv_items .= "<div class='divTableBody'>";

								$class_style = NULL;
								$onClick = NULL;
								if($s_data['s_ship_refund'] == NULL) {
									if(isset($_SESSION['u_access_admin'])) {
										if($_SESSION['u_access_admin'] == 'all') {
											$onClick = " onmouseover=\"JavaScript:this.style.cursor='pointer';\" onClick='js_refund_p_select(\"ship\");'";
										}
									}
								}
								else
									$class_style = " style='text-decoration: line-through; background-color: " . COLOUR_DISABLED_ITEMS . ";'";

									$f_rv_items .= "<div id='row-rid-ship' class='divTableRow'" . $onClick . $class_style . ">";
										$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Shipping'></img></div>";

										$f_rv_items .= "<div class='divTableCellOrders' style='vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $o_ship['type'] . " " . $o_ship['data']['ship_type'];

										$f_rv_items .= "<div style='padding-top: 10px;'>";
												$f_rv_items .= "<div class='pull-left' style='font-weight: 400; font-size: 10px;'>";
												$f_rv_items .= "<div style='display: inline-block;'>Price: " . HTML_CURRENCY . " " . $f_ship['total'] . "</div>";

												$si_total = $f_ship['total'];
												$si_tax = 0;
												foreach($f_ship['taxes'] as $si_key => $si_taxes) {
													$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>" . $si_key . ": " . HTML_CURRENCY . " " . number_format($si_taxes, 2, '.', ',') . "</div>";

													$si_total = $si_total + $si_taxes;
													$si_tax = $si_tax + $si_taxes;
												}

												if($s_data['s_ship_refund'] != NULL)
													$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>REFUNDED</div>";

												$f_rv_items .= "</div>"; // pull-left;
												$f_rv_items .= "<div id='f-rv-div-ship' class='pull-right' style='font-weight: 400; font-size: 10px;' data-tax='" . number_format($si_tax, 2, '.', '') . "' data-total='" . number_format($f_ship['total'], 2, '.', '') . "'>Total: " . HTML_CURRENCY . " " . number_format($si_total, 2, '.', ',') . "</div>";
										$f_rv_items .= "</div>";

										$f_rv_items .= "</div>"; //div table cell orders #2

									$f_rv_items .= "</div>";
								$f_rv_items .= "</div>";
							$f_rv_items .= "</div>";

						$f_rv_items .= "</div>"; // fluid-cart
				}

				$f_rv = "<div style='display: table; width: 100%;'>";
					$f_rv  .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";
						$f_rv  .= "<div style='padding: 10px 10px 10px 10px;'>";

							$f_rv .= "<div style='display: inline-block; max-width: 80%;'>";
								$f_rv  .= "<div class=\"input-group\">
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Refund</div></span>
									  <input id='rv-refund-input' type=\"text\" onblur=\"js_refund_manual_input();\" class=\"form-control\" placeholder=\"Refund amount\" aria-describedby=\"basic-addon1\" disabled id='rv-" . $s_data['s_id'] . "'>
									</div>";
							$f_rv .= "</div>";
							
							/*
							if(isset($paypal->id)) {
								$f_rv .= "<div style='display: inline-block;'><button id='f-refund-button' type='button' onClick='alert(\"Refunds with PayPal currently disabled at the moment. You can refund manually by logging into the PayPal account to do it.\");' style='float: left; margin-left: 20px;' class='btn btn-primary' disabled><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Refund</button></div>";
							}
							else {
							*/
								$f_refund_on_click = NULL;

								if(isset($_SESSION['u_access_admin'])) {
									if($_SESSION['u_access_admin'] == 'all') {
										$f_refund_on_click = "onClick='js_refund_confirm();'";
									}
								}

								$f_rv .= "<div style='display: inline-block;'><button id='f-refund-button' type='button' " . $f_refund_on_click . " style='float: left; margin-left: 20px;' class='btn btn-primary' disabled><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> Refund</button></div>";
							//}
							
							$f_refund_unlock = NULL;
							$f_refund_lock = NULL;
							$f_refund_disable = " disabled";
							
							if(isset($_SESSION['u_access_admin'])) {
								if($_SESSION['u_access_admin'] == 'all') {
									$f_refund_unlock = "onClick='js_refund_unlock();'";
									$f_refund_lock = "onClick='js_refund_lock();'";
									$f_refund_disable = NULL;
								}
							}

							$f_rv .= "<div style='display: inline-block;'><button id='f-refund-button-unlock' type='button' " . $f_refund_unlock . " style='float: left; margin-left: 20px; display: block;' class='btn btn-success" . $f_refund_disable . "'><i class='fa fa-lock' aria-hidden='true'></i> Locked</button></div>";

							$f_rv .= "<div style='display: inline-block;'><button id='f-refund-button-lock' type='button' " . $f_refund_lock . " style='float: left; margin-left: 20px; display: none;' class='btn btn-danger" . $f_refund_disable . "'><i class='fa fa-unlock' aria-hidden='true'></i> Unlocked</button></div>";

							$f_rv .= "<div style='display: inline-block;'><button id='f-refund-button-no-tax' type='button' style='float: left; margin-left: 20px;' class='btn btn-danger' onClick='js_refund_toggle_taxes();'><div id='f-refund-button-no-tax-div'>$ Without Tax</div></button></div>";

							$f_rv .= "<input type='hidden' id='f-s-order-number-id' value='" . htmlspecialchars(base64_encode($s_data['s_order_number'])) . "'>";
							$f_rv .= "<input type='hidden' id='f-s-id' value='" . base64_encode($s_data['s_id']) . "'>";

							$f_rv .= "<div id='f-refund-item-holder-div' style='padding-top: 10px;'>";
							$f_rv .= $f_rv_items;
							$f_rv .= "</div>";

						$f_rv  .= "</div>";
					$f_rv  .= "</div>";
				$f_rv  .= "</div>";
			// --> *********************** END OF  REFUND SECTION *******************************

			// --> *********************** SERIAL NUMBER SECTION ********************************
				$serial_html = NULL;
				$i = 0;
				if(isset($r_items)) {
					foreach($r_items as $data) {
						$width_height = $fluid->php_process_image_resize($data['si_p_image'], "60", "60");
						// Build the item list for the refund section.
						//if($i == 0)
							//$border_top = " border-top: 1px solid #bbb;";
						//else
							$border_top = NULL;

						$serial_html .= "<div class='fluid-cart'>";

							$serial_html .= "<div class='divTable'>";
								$serial_html .= "<div class='divTableBody'>";

								$class_style = NULL;
								$onClick = NULL;
								if($data['si_p_refund'] != NULL)
									$class_style = " style='text-decoration: line-through; background-color: " . COLOUR_DISABLED_ITEMS . ";'";

									$serial_html .= "<div id='row-rid-" . $data['si_id'] . "' class='divTableRow'" . $onClick . $class_style . ">";
										$serial_html .= "<div class='divTableCellOrders' style='vertical-align: middle; width:140px;'>";

										$serial_onClick = NULL;
										$f_click_serial_disabled = " disabled";
										
										if(isset($_SESSION['u_access_admin'])) {
											if($_SESSION['u_access_admin'] == 'all') {
												$serial_onClick = "onClick='js_serial_modal(\"" . $data['si_id'] . "\", \"" . $s_id . "\", \"create\");'";
												$f_click_serial_disabled = NULL;
											}
										}

											$serial_html .= "<div style='display:inline-block;'><button class='btn btn-default btn-sm" . $f_click_serial_disabled . "' " . $serial_onClick . "><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span></button></div>";
											if(isset($data['si_serial_numbers'])) {
												if($_SESSION['u_access_admin'] == 'all') {
													$serial_edit_click = "onClick='js_serial_modal(\"" . $data['si_id'] . "\", \"" . $s_id . "\", \"edit\");'";
													$f_serial_disabled = NULL;
												}
												else {
													$serial_edit_click = NULL;
													$f_serial_disabled = " disabled";
												}

												$serial_html .= "<div style='display: inline-block; padding-left: 10px;'><button class='btn btn-default btn-sm" . $f_serial_disabled . "' " . $serial_edit_click . "><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></button></div>";
											}
											else
												$serial_html .= "<div style='display: inline-block; padding-left: 10px;'><button class='btn btn-default btn-sm disabled'><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></button></div>";


										$serial_html .= "</div>";

										$serial_html .= "<div class='divTableCellOrders' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px;" . $border_top . "'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $data['si_m_name'] . " " . $data['si_p_name'] . "'></img></div>";

										$serial_html .= "<div class='divTableCellOrders' style='vertical-align:middle; font-size: 14px; font-weight: 400;" . $border_top . "'>" . $data['si_m_name'] . " " . $data['si_p_name'];

										$serial_html .= "<div style='padding-top: 5px;'>";
										$serial_html .= "<div style='display: inline-block; font-size: 10px;'>UPC # " . $data['si_p_mfgcode'] . "</div>";
											if(isset($data['si_p_mfg_number']))
												$serial_html .= "<i class=\"fa fa-square\" style='font-size: 5px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 10px; font-weight: 300;'>MFR # " . $data['si_p_mfg_number'] . "</div>";
										$serial_html .= "</div>";

										$serial_html .= "<div style='padding-top: 10px;'>";
												$serial_html .= "<div class='pull-left' style='font-weight: 400; font-size: 10px;'>";
												$serial_html .= "<div style='display: inline-block;'>Price: " . HTML_CURRENCY . " " . $data['si_p_price'] . "</div>";

												$si_total = $data['si_p_price'];
												$si_tax = 0;
												if(isset($data['si_p_taxes'])) {
													foreach($data['si_p_taxes'] as $si_key => $si_taxes) {
														$serial_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>" . $si_key . ": " . HTML_CURRENCY . " " . number_format($si_taxes, 2, '.', ',') . "</div>";

														$si_total = $si_total + $si_taxes;
														$si_tax = $si_tax + $si_taxes;
													}
												}

												if($data['si_p_refund'] != NULL)
													$serial_html .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='display: inline-block; padding-left: 5px;'>REFUNDED</div>";

												if($data['si_p_rebate_claim'] == TRUE)
													$f_rv_items .= "<div style='display: inline-block; padding-left: 5px;'>|</div><div style='color: #FF009F; display: inline-block; padding-left: 5px;'>Rebate / Claim item</div>";

												if(isset($data['si_serial_numbers'])) {
													$serial_html .= "<div style='display: table;'>";
														$f_serials = json_decode($data['si_serial_numbers'], TRUE);

														foreach($f_serials as $serial_key => $serials) {
															$serial_html .= "<div style='display: table-cell; padding: 5px;'>" . $serials . "</div>";
														}

													$serial_html .= "</div>";
												}

												$serial_html .= "</div>"; // pull-left;

												$serial_html .= "<div id='f-rv-div-" . $data['si_id'] . "' class='pull-right' style='font-weight: 400; font-size: 10px;' data-tax='" . number_format($si_tax, 2, '.', '') . "' data-total='" . number_format($data['si_p_price'], 2, '.', '') . "'>Total: " . HTML_CURRENCY . " " . number_format($si_total, 2, '.', ',') . "</div>";
										$serial_html .= "</div>";

										$serial_html .= "</div>"; //div table cell orders #2

									$serial_html .= "</div>";
								$serial_html .= "</div>";
							$serial_html .= "</div>";

						$serial_html .= "</div>"; // fluid-cart

						$i++;
					}
				}
				// --> *********************** END OF SERIAL NUMBER SECTION *******************************


				// Lets build the information html data now.
				$i_html = "<div style='display: table; width: 100%;'>";
					$i_html .= "<div class='well f-card-group-align-box-shadow-transparent'>";
						$i_html .= "<div style='width: 100%; padding: 0px; margin: 0px; vertical-align: middle;'>";

							$i_html .= "<div style='padding: 5px 20px 20px 20px;'>";
								$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 20px;'>Order information</div>";

								$i_html .= "<div class=\"input-group\">
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Order number</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Order number\" aria-describedby=\"basic-addon1\" id='order-number' value=\"" . htmlspecialchars(explode('-', $s_data['s_order_number'])[2]) . "\">
									</div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Order date</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Order date\" aria-describedby=\"basic-addon1\" id='order-date' value=\"" . htmlspecialchars($s_data['s_sale_time']) . "\">
									</div>";

								// Order status
								$i_html .= "<div style='padding-top: 5px;' class='f-print-hide'>";
									$i_html .= "<div class=\"input-group\">";
									$i_html .= "<span class=\"input-group-addon\"><div style='padding-top:3px; height: 20px; width:120px !important;'>Status</div></span>";
									
									$o_status = "disabled ";
									if(isset($_SESSION['u_access_admin'])) {
										if($_SESSION['u_access_admin'] == 'all') {
											$o_status = NULL;
										}
									}

										$i_html .= "<select id='s-id-status-" . $s_data['s_id'] . "' " . $o_status . "class=\"form-control selectpicker show-menu-arrow show-tick\" data-container=\"#fluid-modal\" data-size=\"10\" data-width=\"50%\"'>";

										for($i = 0; $i < 7; $i++) {
											if($s_data['s_status'] == $i)
												$selected = "selected";
											else
												$selected = "";

											$i_html .= "<option " . $selected . " value='" . $i . "'";
											$i_html .= "><span class='glyphicon glyphicon-ok-circle' aria-hidden='true'></span> " . $fluid->php_fluid_order_status($i) . "</option>";
										}
										$i_html .= "</select>";
									
									if(isset($_SESSION['u_access_admin'])) {
										if($_SESSION['u_access_admin'] == 'all') {
											$i_html .= "<div style='padding-left: 10px; display: inline-block;'><input id='s-email-" . $s_data['s_id'] . "' type='hidden' value='" . $s_data['s_u_email'] . "'></input><input id='s-order-number-" . $s_data['s_id'] . "' type='hidden' value='" . $s_data['s_order_number'] . "'></input><input id='s-name-" . $s_data['s_id'] . "' type='hidden' value='" . $s_data['s_address_name'] . "'></input><input id='s-date-" . $s_data['s_id'] . "' type='hidden' value='" . $s_data['s_sale_time'] . "'></input><button class='btn btn-primary' onClick='js_email_order_status_confirm(\"" . $s_data['s_id'] . "\");'><span class='glyphicon glyphicon-send' aria-hidden='true'></span> Email Status Update</button></div>";
										}
									}

									$i_html .= "</div>";
								$i_html .= "</div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Shipping</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Shipping\" aria-describedby=\"basic-addon1\" id='s-shipping-type-" . $s_data['s_id'] . "' value=\"" . htmlspecialchars($d_method) . "\">
									</div>";

								if($o_ship['data']['ship_type'] == IN_STORE_PICKUP)
									$s_hide = " display: none;";
								else
									$s_hide = NULL;

								$i_html .= "<div class=\"input-group\" style='padding-top:5px; " . $s_hide . "' class='f-print-hide'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Tracking #</div></span>
									  <input " . $o_status . "type=\"text\" class=\"form-control\" placeholder=\"Tracking #\" aria-describedby=\"basic-addon1\" id='s-tracking-" . $s_data['s_id'] . "' value=\"" . htmlspecialchars($s_data['s_tracking']) . "\">
									</div>";

							$i_html .= "<input type='hidden' value='" . $s_data['s_id'] . "' id='s-data-" . $s_data['s_id'] . "'>";
							$i_html .= "</div>";

							// Lets build the total breakdowns.
							$i_html .= "<div name='fo-order-totals' style='padding: 20px; border-top: 1px solid rgba(128, 128, 128, 0.52); border-bottom: 1px solid rgba(128, 128, 128, 0.52);'>";
								$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 20px;'>Order totals</div>";

								$i_html .= "<div class=\"input-group\">
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Sub total</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Sub total\" aria-describedby=\"basic-addon1\" id='order-subtotal' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($s_data['s_sub_total'], 2, ".", ",")) . "\">
									</div>";

								//if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
									if($s_data['s_shipping_total'] == 0)
										$f_ship_html = "FREE";
									else
										$f_ship_html = HTML_CURRENCY . " " . number_format($s_data['s_shipping_total'], 2, ".", ",");

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
										  <span class=\"input-group-addon\"><div style='width:120px !important;'>Shipping</div></span>
										  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Shipping\" aria-describedby=\"basic-addon1\" id='order-shipping' value=\"" . htmlspecialchars($f_ship_html) . "\">
										</div>";
								//}

								// Break down the taxes.
								if(isset($s_data['s_tax_total'])) {
									$t_taxes = json_decode($s_data['s_taxes'], true);

									foreach($t_taxes as $t_key => $t_data) {
										$tmp_total = 0;

										foreach($t_data['f_rates'] as $t_f_rates => $t_f_rates_data)
											$tmp_total = round($tmp_total + $t_f_rates_data['t_total'], 2);

										$tmp_total = round($t_data['p_total'] + $tmp_total, 2);

										$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
											  <span class=\"input-group-addon\"><div style='width:120px !important;'>" . $t_data['t_name'] . "</div></span>
											  <input type=\"text\" class=\"form-control\" disabled placeholder=\"" . $t_data['t_name'] . "\" aria-describedby=\"basic-addon1\" id='order-tax-" . $t_key . "' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($tmp_total, 2, '.', ',')) . "\">
											</div>";
									}
								}

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Total</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Total\" aria-describedby=\"basic-addon1\" id='order-total' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($s_data['s_total'], 2, ".", ",")) . "\">
									</div>";

							$i_html .= "</div>"; // fo-order-totals

							// If shipping was free and not a instore pickup, lets display some extra information.
							if($o_ship['data']['ship_type'] != IN_STORE_PICKUP && $s_data['s_shipping_total'] == 0) {
								$i_html .= "<div name='fo-free-shipping-information' style='padding: 20px; border-bottom: 1px solid rgba(128, 128, 128, 0.52);'>";
									$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 20px;'>Free Shipping Information</div>";

									$i_html .= "<div class=\"input-group\">
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Shipping cost</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Shipping cost\" aria-describedby=\"basic-addon1\" id='order-free-shipping-subtotal' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($o_ship['free']['price'], 2, ".", ",")) . "\">
									  </div>";

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
										  <span class=\"input-group-addon\"><div style='width:120px !important;'>Shipping Taxes</div></span>
										  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Shipping taxes\" aria-describedby=\"basic-addon1\" id='order-free-shipping-tax' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($o_ship['free']['tax'], 2, ".", ",")) . "\">
										</div>";

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
										  <span class=\"input-group-addon\"><div style='width:120px !important;'>Shipping total</div></span>
										  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Shipping total\" aria-describedby=\"basic-addon1\" id='order-free-shipping-total' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($o_ship['free']['total'], 2, ".", ",")) . "\">
										</div>";
								$i_html .= "</div>"; // fo-free-shipping-information
							}

							// Extra information such as cost, profit and margin.
							$i_html .= "<div name='fo-extra-information' style='padding: 20px; border-bottom: 1px solid rgba(128, 128, 128, 0.52);'>";
								$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 20px;'>Extra Information</div>";

								if($s_data['s_shipping_total'] == 0)
									$f_cost = $s_data['s_cost_total'] + $o_ship['free']['total'];
								else
									$f_cost = $s_data['s_cost_total'];

								$f_profit = $s_data['s_sub_total'] - $f_cost;
								$f_margin = ($f_profit / $s_data['s_sub_total']) * 100;

								if($s_data['s_ship_split'] > 0) {
									$i_html .= "<div class=\"input-group\" style='margin-bottom: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Split Shipping</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Split Shipping\" aria-describedby=\"basic-addon1\" style='color: green;' id='split-shipping' value=\"Yes. Ship in stock items first.\">
									  </div>";
								}

								$i_html .= "<div class=\"input-group\">
								  <span class=\"input-group-addon\"><div style='width:120px !important;'>Cost</div></span>
								  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Cost\" aria-describedby=\"basic-addon1\" id='order-cost-info' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($f_cost, 2, ".", ",")) . "\">
								  </div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Profit</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Profit\" aria-describedby=\"basic-addon1\" id='order-profit-info' value=\"" . HTML_CURRENCY . " " . htmlspecialchars(number_format($f_profit, 2, ".", ",")) . "\">
									</div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>Margin</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Margin\" aria-describedby=\"basic-addon1\" id='order-margin-info' value=\"" . htmlspecialchars(number_format($f_margin, 2, ".", ",")) . " %\">
									</div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>IP Address</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"IP Address\" aria-describedby=\"basic-addon1\" id='order-ip-address' value=\"" . htmlspecialchars($s_data['s_ip_address']) . "\">
									</div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:120px !important;'>IP Address Forward</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"IP Address Forward\" aria-describedby=\"basic-addon1\" id='order-ip-address-forward' value=\"" . htmlspecialchars($s_data['s_ip_address_forward']) . "\">
									</div>";

							$i_html .= "</div>"; // fo-extra-information

							// Fraud detection scores.
							$i_html .= "<div id='invoice-page-break' class='f-page-break'></div>" . $si_i_print_html; // --> For printing purposes.
							$i_html .= "<div name='fo-fraud' style='padding: 20px; border-bottom: 1px solid rgba(128, 128, 128, 0.52);'>";
								$i_html .= "<div style='font-weight: 600; font-style: italic;'>Fraud Score</div>";
								$i_html .= "<div style='padding-bottom: 20px; font-style: italic;'>Higher percentage means a higher chance of fraud.</div>";

								$f_fraud_check = new Fluid();
								$f_fraud_score_data = $f_fraud_check->php_fluid_fraud_score($s_data, $f_moneris_obj, $o_ship, $paypal, $auth_net_data);

								$f_fraud_score = $f_fraud_score_data['fraud_score'];
								$f_avs = $f_fraud_score_data['f_avs'];
								$f_cvd = $f_fraud_score_data['f_cvd'];
								$f_address_match = $f_fraud_score_data['f_address_match'];
								$f_paypal_protection = $f_fraud_score_data['f_paypal_protection'];
								$f_verify = $f_fraud_score_data['f_verify'];
								$f_paypal_email_check = $f_fraud_score_data['f_paypal_email_check'];
								$f_paypal_country = $f_fraud_score_data['f_paypal_country'];
								$f_purchase_email = $f_fraud_score_data['f_purchase_email'];
								$f_purchase_address = $f_fraud_score_data['f_purchase_address'];
								$f_paypal_protection_types = $f_fraud_score_data['f_paypal_protection_types'];

								if($f_fraud_score > 10)
									$f_fraud_score_html = "100%";
								else if($f_fraud_score < 0)
									$f_fraud_score_html = "0%";
								else
									$f_fraud_score_html = $f_fraud_score * 10 . "%";

								if($f_fraud_score * 10 > 90)
									$f_colour = "red";
								else if($f_fraud_score * 10 >= 80)
									$f_colour = "#FF3600";
								else if($f_fraud_score * 10 >= 70)
									$f_colour = "#FF7500";
								else if($f_fraud_score * 10 >= 60)
									$f_colour = "#E19604";
								else if($f_fraud_score * 10 >= 50)
									$f_colour = "#af8c02";
								else if($f_fraud_score * 10 >= 40)
									$f_colour = "#9DAF02";
								else if($f_fraud_score * 10 >= 30)
									$f_colour = "#84AF02";
								else
									$f_colour = "#6baf02";

								if(isset($paypal->id)) {
									$f_payment_type = "PayPal";
								}
								else if(isset($auth_net_data)) {
									if(isset($auth_net_data->STATUS)) {
										$f_payment_type = "Customer will pay for order during pickup";
									}
									else {
										$f_payment_type = "Authorize.net";
									}
								}
								else if(isset($f_moneris_data)) {
									$f_payment_type = "Moneris";
								}
								else {
									$f_payment_type = "Unknown";
								}

								$i_html .= "<div class=\"input-group\">
								  <span class=\"input-group-addon\"><div style='width:140px !important;'>Score</div></span>
								  <input type=\"text\" style='font-weight: 600; color:" . $f_colour . ";' class=\"form-control\" disabled placeholder=\"Fraud score\" aria-describedby=\"basic-addon1\" id='fraud_score' value=\"" . htmlspecialchars($f_fraud_score_html) . "\">
								  </div>";

								if(isset($paypal->id)) {
									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>PayPal Email</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"PayPal email match\" aria-describedby=\"basic-addon1\" id='paypal-email-match' value=\"" . $f_paypal_email_check . "\">
									  </div>";

									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>PayPal Verified</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"PayPal account verified?\" aria-describedby=\"basic-addon1\" id='paypal-verify' value=\"" . $f_verify . "\">
									  </div>";

									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>PayPal Protection</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"PayPal protection?\" aria-describedby=\"basic-addon1\" id='paypal-protection' value=\"" . $f_paypal_protection . "\">
									  </div>";

									  $i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
										<span class=\"input-group-addon\"><div style='width:140px !important;'>Protection Types</div></span>
										<input type=\"text\" class=\"form-control\" disabled placeholder=\"PayPal protection types?\" aria-describedby=\"basic-addon1\" id='paypal-protection-types' value=\"" . $f_paypal_protection_types . "\">
										</div>";

									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>PayPal Country</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"PayPal country\" aria-describedby=\"basic-addon1\" id='paypal-country' value=\"" . $f_paypal_country . "\">
									  </div>";
								}
								else {
									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>Card Type</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Card Type\" aria-describedby=\"basic-addon1\" id='card-info' value=\"";

										$f_card_type = NULL;
										if(isset($f_moneris_obj['CardType'])) {
											$f_card_type = $f_moneris_obj['CardType'];

											switch($f_moneris_obj['CardType']) {
													case "V":
														$i_html .= "Visa";
														break;
													case "M":
														$i_html .= "Master Card";
														break;
													case "AX":
														$i_html .= "American Express";
														break;
													case "NO":
														$i_html .= "Novus/Discover";
														break;
													case "DS":
														$i_html .= "Discover";
														break;
													case "C":
														$i_html .= "JCB";
														break;
													case "C1":
														$i_html .= "JCB";
														break;
													case "SE":
														$i_html .= "Sears";
														break;
													case "CQ":
														$i_html .= "ACH";
														break;
													case "P":
														$i_html .= "Pin Debit";
														break;
													case "D":
														$i_html .= "Debit Card";
														break;
													default:
														$i_html .= "Unknown";
											}
										}
										
										if(isset($auth_net_data->STATUS)) {
											$i_html .= "Customer will pay for order during pickup";
										}
										else if(isset($auth_net_data->accountType)) {
											$i_html .= $auth_net_data->accountType;
										}
										
								    $i_html .= "\"></div>";

									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>AVS</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"AVS\" aria-describedby=\"basic-addon1\" id='avs-info' value=\"" . htmlspecialchars($f_avs) . "\">
									  </div>";

									$i_html .= "<div class=\"input-group\" style='padding-top: 5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>AVS Code</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Card Type\" aria-describedby=\"basic-addon1\" id='avs-code' value=\"";

									$f_avs_message = "Unknown";
									
									if(isset($auth_net_data->STATUS)) {
										$i_html .= "Customer will pay for order during pickup";
										$f_avs_message = "Customer will pay for order during pickup";
									}
									
										if(isset($auth_net_data->avsResultCode)) {
											switch ($auth_net_data->avsResultCode) {
												case "A":
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "The street address matched, but the postal code did not.";
													break;
												
												case 'B':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "No address information was provided.";
													break;
													
												case 'E':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "The AVS check returned an error.";
													break;
													
												case 'G':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "The card was issued by a bank outside the U.S. and does not support AVS.";
													break;
															
												case 'N':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "Neither the street address nor postal code matched.";
													break;
													
												case 'P':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "AVS is not applicable for this transaction.";
													break;
												
												case 'R':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "Retry — AVS was unavailable or timed out.";
													break;
													
												case 'S':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "AVS is not supported by card issuer.";
													break;
													
												case 'U':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "Address information is unavailable.";
													break;
													
												case 'W':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "The US ZIP+4 code matches, but the street address does not.";
													break;
													
												case 'X':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "Both the street address and the US ZIP+4 code matched.";
													break;
													
												case 'Y':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "The street address and postal code matched.";
													break;
													
												case 'Z':
													$i_html .= $auth_net_data->avsResultCode;
													$f_avs_message = "The postal code matched, but the street address did not.";
													break;		
																																						
												default:
													$i_html .= "Unknown.";
											}
										}
										
										if(isset($f_moneris_obj['AvsResultCode'])) {
											switch($f_moneris_obj['AvsResultCode']) {
													case "A":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Address matches, postal code / zip code does not. Acquirer rights not implied.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "Address matches, postal code does not.";
														else
															$f_avs_message = "Billing address matches, postal code / zip code does not.";

														break;
													case "B":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Street addresses match. Postal code not verified due to incompatible formats. (Acquirer sent both street address and postal code.)";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "N/A";

														break;
													case "C":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Street addresses not verified due to incompatible formats. (Acquirer sent both street address and postal code.)";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "N/A";

														break;
													case "D":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Street addresses and postal codes match.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name incorrect, zip code / postal code matches.";

														break;
													case "E":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name incorrect, billing address and postal code match.";

														break;
													case "F":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Street address and postal code match. Applies to U.K. only.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name incorrect, billing address matches.";

														break;
													case "G":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Address information not verified for international transaction. Issuer is not an AVS participant, or AVS data was present in the request but issuer did not return an AVS result, or Visa performs AVS on behalf of the issuer and there was no address record on file for this account.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "N/A";

														break;
													case "I":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Address information not verified.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "N/A";

														break;
													case "K":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name matches.";

														break;
													case "L":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name and postal code match.";

														break;
													case "M":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Street address and postal code match.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name, billing address, and postal code match.";

														break;
													case "N":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "No match. Acquirer sent postal/ZIP code only, or street address only, or both postal code and street address. Also used when acquirer requests AVS but sends no AVS data.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "Neither address nor postal code matches.";
														else
															$f_avs_message = "Billing address and postal code do not match.";

														break;
													case "O":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "Customer name and billing address match.";

														break;
													case "P":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Postal code match. Acquirer sent both postal code and street address but street address not verified due to incompatible formats.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "N/A";
														else
															$f_avs_message = "N/A";

														break;
													case "R":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Retry: system unavailable or timed out. Issuer ordinarily performs AVS but was unavailable. The code R is used by Visa when issuers are unavailable. Issuers should refrain from using this code.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "Retry; system unable to process.";
														else
															$f_avs_message = "System unavailable; retry.";

														break;
													case "S":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "AVS currently not supported.";
														else
															$f_avs_message = "AVS currently not supported.";

														break;
													case "T":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "Nine-digit zip code matches, address does not match.";
														else
															$f_avs_message = "N/A";

														break;
													case "U":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Address not verified for domestic transaction. Issuer is not an AVS participant, or AVS data was present in the request but issuer did not return an AVS result, or Visa performs AVS on behalf of the issuer and there was no address record on file for this account.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "No data from Issuer/Authorization system.";
														else
															$f_avs_message = "Information is unavailable.";

														break;
													case "W":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Not applicable. If present, replaced with ‘Z’ by Visa. Available for U.S. issuers only.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "For U.S. Addresses, nine-digit postal code matches, address does not; for address outside the U.S. postal code matches, address does not.";
														else
															$f_avs_message = "Customer name, billing address, and postal code are all correct.";

														break;
													case "X":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "N/A";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "For U.S. addresses, nine-digit postal code and addresses matches; for addresses outside the U.S., postal code and address match.";
														else
															$f_avs_message = "N/A";

														break;
													case "Y":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Street address and postal code match.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "Billing address and postal code both match.";
														else
															$f_avs_message = "N/A";

														break;
													case "Z":
														$i_html .= $f_moneris_obj['AvsResultCode'];

														if($f_card_type == "V")
															$f_avs_message = "Postal/Zip matches; street address does not match or street address not included in request.";
														else if($f_card_type == "M" || $f_card_type == "NO" || $f_card_type == "DS")
															$f_avs_message = "For U.S. addresses, five-digit zip code matches, address does not match.";
														else
															$f_avs_message = "Postal code matches, billing address does not.";

														break;
													default:
														$i_html .= "Unknown.";
											}
										}

								    $i_html .= "\"></div>";

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>AVS Message</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"AVS Message\" aria-describedby=\"basic-addon1\" id='avs-message-info' value=\"" . $f_avs_message . "\">
									  </div>";

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>CVD</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"CVD\" aria-describedby=\"basic-addon1\" id='cvd-info' value=\"" . htmlspecialchars($f_cvd) . "\">
									  </div>";

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>CVD Code</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"CVD Code\" aria-describedby=\"basic-addon1\" id='cvd-code-info' value=\"";

										if(isset($f_moneris_obj['CvdResultCode'])) {
											$i_html .= $f_moneris_obj['CvdResultCode'];
										}
										else if(isset($auth_net_data->cvvResultCode)) {
											$i_html .= $auth_net_data->cvvResultCode;

										}
										else {
											if(isset($auth_net_data->STATUS)) {
												$i_html .= "Customer will pay for order during pickup";
											}
											else {
												$i_html .= "Unknown";
											}
										}

									$i_html .= "\"></div>";

									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>CVD Message</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"CVD Message\" aria-describedby=\"basic-addon1\" id='cvd-message-info' value=\"";
									  	
									  if(isset($auth_net_data->STATUS)) {
										  $i_html .= "Customer will pay for order during pickup";
									  }
									  
										if(isset($auth_net_data->cvvResultCode)) {
											switch($auth_net_data->cvvResultCode) {
												case "M":
													$i_html .= "Match.";
													break;
												case "N":
													$i_html .= "No match.";
													break;
												case "P":
													$i_html .= "Not processed.";
													break;
												case "S":
													$i_html .= "CVD should be on the card, but Merchant has indicated that CVD is not present.";
													break;
												case "U":
													$i_html .= "Issuer is not a CVD participant.";
													break;
													
												default:
													$i_html .= "Unknown.";
											}
										
										}
										
										if(isset($f_moneris_obj['CvdResultCode'])) {
											$f_cvd_code_split = substr($f_moneris_obj['CvdResultCode'], 1, 2);

											switch($f_cvd_code_split) {
													case "M":
														$i_html .= "Match.";
														break;
													case "N":
														$i_html .= "No match.";
														break;
													case "P":
														$i_html .= "Not processed.";
														break;
													case "S":
														$i_html .= "CVD should be on the card, but Merchant has indicated that CVD is not present.";
														break;
													case "U":
														$i_html .= "Issuer is not a CVD participant.";
														break;
													case "Y":
														$i_html .= "Match for AmEx/JCB only.";
														break;
													case "D":
														$i_html .= "Invalid security code for AmEx/JCB.";
														break;
													default:
														$i_html .= "Unknown.";
											}
										}

									$i_html .= "\"></div>";
								}

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
								  <span class=\"input-group-addon\"><div style='width:140px !important;'>Email found</div></span>
								  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Previous purchases from this email address\" aria-describedby=\"basic-addon1\" id='prev-email-purchases' value=\"" . htmlspecialchars($f_purchase_email) . "\">
								  </div>";

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
								  <span class=\"input-group-addon\"><div style='width:140px !important;'>Address found</div></span>
								  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Previous purchases from this address\" aria-describedby=\"basic-addon1\" id='prev-address-purchases' value=\"" . htmlspecialchars($f_purchase_address) . "\">
								  </div>";

								if(!isset($paypal->id)) {
									$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
									  <span class=\"input-group-addon\"><div style='width:140px !important;'>Shipping / Billing match</div></span>
									  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Shipping and billing addresses are the same?\" aria-describedby=\"basic-addon1\" id='prev-address-match' value=\"" . htmlspecialchars($f_address_match) . "\">
									  </div>";
								}

								$i_html .= "<div class=\"input-group\" style='padding-top:5px;'>
								  <span class=\"input-group-addon\"><div style='width:140px !important;'>Payment Processor</div></span>
								  <input type=\"text\" class=\"form-control\" disabled placeholder=\"Paid by?\" aria-describedby=\"basic-addon1\" id='payment-processor' value=\"" . $f_payment_type . "\">
								  </div>";

							$i_html .= "</div>";

							$i_html .= "<div class='well f-order-well' style='padding: 20px;'>";
								$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Shipping information</div>";
								$sd_info_html_header = "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Delivery:</div><div style='display: inline-block; padding-left: 5px;'>" . $d_method . "</div></div>";

								if($s_data['s_ship_split'] == 1) {
									$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Customer Request:</div><div style='display: inline-block; padding-left: 5px;'>Split order. Ship in stock items first separately.</div></div>";
								}
								else {
									$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Note:</div><div style='display: inline-block; padding-left: 5px;'>Ship when all items are in stock.</div></div>";
								}

								// --> Display some of the shipping options.
								// --> Scan for some Canada Post data.
								if(isset($o_ship['data']['s_data']['price-details']['options']['option'])) {
									foreach($o_ship['data']['s_data']['price-details']['options']['option'] as $f_options) {
										if(isset($f_options['option-code'])) {
											// Insurance data.
											if($f_options['option-code'] == 'COV') {
												if(isset($f_options['cov-value'])) {
													$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Insured value:</div><div style='display: inline-block; padding-left: 5px;'>" . HTML_CURRENCY . number_format($f_options['cov-value'], 2, '.', ',')  . "</div></div>";
												}
											}
											// Signature data.
											if($f_options['option-code'] == 'SO') {
												$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Signature required: </div><div style='display: inline-block; padding-left: 5px;'>Yes</div></div>";
											}
										}
									}	
								}
								
								// --> Scan for some FedEx insurance data.
								if(isset($o_ship['data']['s_data']['insured_value'])) {
									$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Insured value:</div><div style='display: inline-block; padding-left: 5px;'>" . HTML_CURRENCY . number_format($o_ship['data']['s_data']['insured_value'], 2, '.', ',')  . "</div></div>";
								}
								
								// --> Scan for some FedEx signature data.
								if(isset($o_ship['data']['s_data']['SignatureOption'])) {
									if($o_ship['data']['s_data']['SignatureOption'] == "DIRECT" || $o_ship['data']['s_data']['SignatureOption'] == "INDIRECT" || $o_ship['data']['s_data']['SignatureOption'] == "ADULT") {
										$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Signature required:</div><div style='display: inline-block; padding-left: 5px;'>Yes</div></div>";
										
										$sd_info_html_header .= "<div style='padding-top: 5px;'><div style='display: inline-block; font-weight: 600; font-style: italic;'>Signature type: </div><div style='display: inline-block; padding-left: 5px;'>" . $o_ship['data']['s_data']['SignatureOption']  . "</div></div>";
									}
								}

								// Scan s_shipping_64 to find delivery method.
								// --> If pickup, then just display a name.
								// --> If shipped, then show full address.
								if($o_ship['data']['ship_type'] != IN_STORE_PICKUP)
									$sd_info_html_header .= "<div style='padding-top: 10px; font-weight: 600; font-style: italic;'>Delivery address</div>";
								else
									$sd_info_html_header .= "<div style='padding-top: 10px; font-weight: 600; font-style: italic;'>Pickup information</div>";

								$sd_info_top_html = "<div>" . utf8_decode($s_data['s_address_name']) . "</div>";
								$sd_info_ship = "<div class='f-print-invoice-center f-print-invoice-font-name'>" . utf8_decode($s_data['s_address_name']) . "</div>";

								$sd_info_html = "<div>";
										if($s_data['s_address_number'] != "") {
											$sd_info_html .= utf8_decode($s_data['s_address_number']) .  " - ";
											$sd_info_html .= utf8_decode($s_data['s_address_street']);
											$sd_info_ship .= "<div class='f-print-invoice-center f-print-invoice-font'>" . utf8_decode($s_data['s_address_number']) .  " - " . utf8_decode($s_data['s_address_street']) . "</div>";
										}
										else {
											$sd_info_html .= utf8_decode($s_data['s_address_street']);
											$sd_info_ship .= "<div class='f-print-invoice-center f-print-invoice-font'>" . utf8_decode($s_data['s_address_street']) . "</div>";
										}
								$sd_info_html .= "</div>";

								$sd_info_html .= "<div>" . utf8_decode($s_data['s_address_city']) . " " . utf8_decode($s_data['s_address_province']) . "</div>";
								$sd_info_ship .= "<div class='f-print-invoice-center f-print-invoice-font'>" . utf8_decode($s_data['s_address_city']) . " " . utf8_decode($s_data['s_address_province']) . "</div>";

								$sd_info_html .= "<div>" . utf8_decode($s_data['s_address_country']) . " " . utf8_decode($s_data['s_address_postalcode']) . "</div>";
								$sd_info_ship .= "<div class='f-print-invoice-center f-print-invoice-font'>" . utf8_decode($s_data['s_address_country']) . " " . utf8_decode($s_data['s_address_postalcode']) . "</div>";

								$sd_info_html .= "<div>" . utf8_decode($s_data['s_address_phonenumber']) . "</div>";
								$sd_info_ship .= "<div class='f-print-invoice-center f-print-invoice-phone'>Ph #: " . utf8_decode($s_data['s_address_phonenumber']) . "</div>";

								$sd_info_html .= "<div style='font-size: 12px; font-style: italic;'>" . utf8_decode($s_data['s_u_email']) . "</div>";

								$s_ship_card = NULL;
								if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
									//$s_ship_card = "<div>Leo's Camera Supply</div>";
									//$s_ship_card .= "<div>1055 Granville Street</div>";
									//$s_ship_card .= "<div>Vancouver, B.C. V6Z1L4</div>";
									// class='f-leos-info-div'
									$s_ship_card .= "<div>
										<div><span class=\"icon-leos-logo-rotate\" style=\"font-size: 40px; color: red !important;\"></span> <div style='font-size: 22px; display: inline-block; vertical-align: super; padding-left: 3px;'>Camera Supply</div></div>
										<div>1055 Granville Street</div>
										<div>Vancouver, British Columbia</div>
										<div>CANADA, V6Z1L4</div>
									</div>";


									$s_ship_card .= "<div style='padding-top: 30px; padding-bottom: 40px;'>" . $sd_info_ship . "</div>";
									$s_ship_card .= "<div>" . $d_method;

									// --> Display some of the shipping options.
									// --> Scan for some Canada Post Data.
									if(isset($o_ship['data']['s_data']['price-details']['options']['option']))
										foreach($o_ship['data']['s_data']['price-details']['options']['option'] as $f_options)
											if(isset($f_options['option-code']))
												if($f_options['option-code'] == 'COV')
													if(isset($f_options['cov-value']))
														$s_ship_card .= " | Insured: " . HTML_CURRENCY . number_format($f_options['cov-value'], 2, '.', ',');

									// --> Scan for some FedEx Data
									if(isset($o_ship['data']['s_data']['insured_value']))
										$s_ship_card .= " | Insured: " . HTML_CURRENCY . number_format($o_ship['data']['s_data']['insured_value'], 2, '.', ',');

									$s_ship_card .= "<div style='float: right;'>#: " . htmlspecialchars(explode('-', $s_data['s_order_number'])[2]) . "</div>";

									$s_ship_card .= "</div>";

									$s_ship_card = "<div style='width: 100%; border: 1px dotted black; padding: 20px; margin-top: 20px;'><div style='padding: 10px; border: 2px solid black;'>" . $s_ship_card . "</div></div>";
								}

								$i_html .= $sd_info_html_header . $sd_info_top_html . $sd_info_html;
							$i_html .= "</div>";

							$s_address_payment = json_decode(base64_decode($s_data['s_address_payment_64']), TRUE);

							$sa_info_html = NULL;
							if(!isset($paypal->id)) {
								$i_html .= "<div class='well f-order-well' style='padding: 20px;'>";
									$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>Billing address</div>";

									$sa_info_html = "<div>" . utf8_decode(($s_address_payment['a_name'])) . "</div>";

									$sa_info_html .= "<div>";
											if($s_address_payment['a_number'] != "")
												$sa_info_html .= utf8_decode($s_address_payment['a_number']) .  " - ";
											$sa_info_html .= utf8_decode($s_address_payment['a_street']);
									$sa_info_html .= "</div>";

									$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_city']) . " " . utf8_decode($s_address_payment['a_province']) . "</div>";
									$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_country']) . " " . utf8_decode($s_address_payment['a_postalcode']) . "</div>";
									$sa_info_html .= "<div>" . utf8_decode($s_address_payment['a_phonenumber']) . "</div>";

									$i_html .= $sa_info_html;
								$i_html .= "</div>";
							}
							else if(isset($paypal->id)) {
								// --> They paid with PayPal, lets put some PayPal info here.
								$i_html .= "<div class='well f-order-well' style='padding: 20px;'>";
									$i_html .= "<div style='font-weight: 600; font-style: italic; padding-bottom: 10px;'>PayPal Information</div>";

									$i_html .= "<div>PayPal email: " . $paypal->payer->payer_info->email . "</div>";
									$i_html .= "<div>PayPal name: " . $paypal->payer->payer_info->first_name . "</div>";
									$i_html .= "<div>PayPal name: " . $paypal->payer->payer_info->last_name . "</div>";
									$i_html .= "<div>PayPal phone: " . $paypal->payer->payer_info->phone . "</div>";
									$i_html .= "<div>PayPal country: " . $paypal->payer->payer_info->country_code . "</div>";

									if(isset($paypal->payer->payer_info->shipping_address)) {
										$i_html .= "<div style='padding-top: 10px;'>";
											$i_html .= "<div style='padding-bottom: 5px; font-weight: 600; font-style: italic;'>Paypal address</div>";

										foreach($paypal->payer->payer_info->shipping_address as $paypal_key => $p_ship)
											$i_html .= "<div>" . $paypal_key . " : " . $p_ship . "</div>";

										$i_html .= "</div>";
									}

									/*
									$i_html .= "<div style='padding-top: 15px; padding-bottom: 5px; font-weight: 600; font-style: italic;'>PayPal Protections</div>";
									$i_html .= "<div>Paypal protection: " . $f_paypal_protection_html . "</div>";

									if(isset($f_protection_types))
										$i_html .= "<div>Paypal protection types: " . $f_protection_types . "</div>";

									if(isset($f_payment_mode))
										$i_html .= "<div>Paypal payment type: " . $f_payment_mode . "</div>";
									*/

								$i_html .= "</div>";
							}

						$i_html .= "</div>";
					$i_html .= "</div>";
				$i_html .= "</div>";

				// *******************************
				// Order Invoice from the time the order was made. Does not include refunds or corrections.
				$si_html_start = "<div class='f-extra-invoice' style='padding-bottom: 20px;'>";
					$si_html_start .= "<div class='f-thank-you'>Thank you for your order</div><div style='float: right;'>GST# R103057535</div>";
					$si_html_start .= "<div>" . $html_header . "</div>";
					$si_html_start .= "<div>Order Date: " . $s_data['s_sale_time'] . "</div>";

					$si_html_start .= "<div style='padding-top: 30px; padding-bottom: 30px;'>";
						if(!isset($paypal->id)) {
							$si_html_start .= "<div style='display: inline-block; vertical-align: top;'>";
								$si_html_start .= "<div style='display: table;'>";
									$si_html_start .= "<div style='display: table-row; font-weight: 600;'>Billing information:</div>";
									$si_html_start .= "<div style='display: table-row;'> " . $sa_info_html . "</div>";
								$si_html_start .= "</div>";
							$si_html_start .= "</div>";
						}

						if(!isset($paypal->id))
							$f_padding_left = " padding-left: 80px;";
						else
							$f_padding_left = NULL;

						$si_html_start .= "<div style='display: inline-block;" . $f_padding_left . " vertical-align: top;'>";
							$si_html_start .= "<div style='display: table;'>";
								$si_html_start .= "<div style='display: table-row; font-weight: 600;'>Shipping information:</div>";
								$si_html_start .= "<div style='display: table-row;'> " . $sd_info_top_html . $sd_info_html . "</div>";
							$si_html_start .= "</div>";
						$si_html_start .= "</div>";
					$si_html_start .= "</div>";

				$si_html_start .= "</div>";

				$si_html_start .= "<div name='fluid-cart-scroll' class='fluid-cart-no-scroll'>";

					/*
					foreach($s_items as $data) {
						// Process the image.
						$width_height = $fluid->php_process_image_resize($data['p_image'], "60", "60");

						$si_html .= "<div class='fluid-cart'>";

						$si_html .= "<div class='divTable'>";
							$si_html .= "<div class='divTableBody'>";
								$si_html .= "<div class='divTableRow'>";
									$si_html .= "<div class='divTableCellOrders div-table-cell-print' style='vertical-align:middle; width: " . $width_height['width'] . "px; min-width: 80px; max-width: 80px; '><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px;' alt='Buy " . $data['m_name'] . " " . $data['p_name'] . "'></img></div>";
									$si_html .= "<div class='divTableCellOrders div-table-cell-print' style='vertical-align:middle; font-size: 14px; font-weight: 400;'>" . $data['m_name'] . " " . $data['p_name'];

									$si_html .= "<div style='padding-top: 1px; padding-bottom: 5px;'>";
									$si_html .= "<div style='display: inline-block; font-size: 9px;'>UPC # " . $data['p_mfgcode'] . "</div>";
										if(isset($data['p_mfg_number']))
											$si_html .= "<i class=\"fa fa-square\" style='font-size: 4px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 9px; font-weight: 300;'>MFR # " . $data['p_mfg_number'] . "</div>";
									$si_html .= "</div>";

									$si_html .= "<div style='padding-top: 2px;'><div class='pull-left' style='font-weight: 400;'>Qty: " . $data['p_qty'] . "</div><div class='pull-right' style='font-weight: 400;'>" . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.</div></div></div>";

									$si_html .= "<div class='divTableCellOrders f-print-img-hide' style='vertical-align:middle; font-weight: 400; width: 100%;'><div style='vertical-align:middle; display: inline-block;'><img src='" . $_SESSION['fluid_uri'] . $width_height['image'] . "' style='padding: 5px; min-width: 60px; max-width: 60px;' alt='Buy " . $data['m_name'] . " " . $data['p_name'] . "'></img></div><div style='display: inline-block; vertical-align:middle; width: 90%; padding-left: 5px;'><div style='vertical-align:middle; padding-top: 5px; width: 100%;'>" . $data['m_name'] . " " . $data['p_name'];

									$si_html .= "<div style='padding-top: 1px; padding-bottom: 5px;'>";
									$si_html .= "<div style='display: inline-block; font-size: 9px;'>UPC # " . $data['p_mfgcode'] . "</div>";
										if(isset($data['p_mfg_number']))
											$si_html .= "<i class=\"fa fa-square\" style='font-size: 4px; color: #a1a1a1; vertical-align: middle;  padding-left: 5px; padding-right: 5px;' aria-hidden=\"true\"></i><div style='display: inline-block; font-size: 9px; font-weight: 300;'>MFR # " . $data['p_mfg_number'] . "</div>";
									$si_html .= "</div>";

									$si_html .= "</div><div style='padding-top: 2px;'><div class='pull-left' style='font-weight: 400;'>Qty: " . $data['p_qty'] . "</div><div class='pull-right' style='font-weight: 400;'>" . HTML_CURRENCY . " " . number_format($data['p_price'], 2, ".", ",") . " ea.</div></div></div></div>";
								$si_html .= "</div>";
							$si_html .= "</div>";
						$si_html .= "</div>";

						$si_html .= "</div>";
					}
					*/
				$si_html_end = "</div>"; // fluid-cart-no-scroll

				$si_html_end .= "<div style='padding-top: 10px;'>";
					$si_html_end .= "<div class='divTable'>";
						$si_html_end .= "<div class='divTableBody'>";
							$si_html_end .= "<div id='fluid-cart-totals' class='divTableRow pull-right fluid-cart-subtotal' style='font-size: 14px; font-weight: 400 !important;'>";

							$si_html_end .= "<div style='display: table;'>";

								$f_animate_id = NULL;
								$f_animate_id[] = Array("id" => base64_encode("fluid-sub-total-row-order"), "delay" => 0, "colour" => "#0050FF");
								$si_html_end .= "<div name='fluid-sub-total-row-order' id='fluid-sub-total-row-order' style='text-align: right;'>"; // --> This div is used for animating.
									$si_html_end .= "<div style='display: table-row;'>";
										$si_html_end .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Sub Total:</div><div style='display: table-cell; text-align: right;'> " . HTML_CURRENCY . " " . number_format($s_data['s_sub_total'], 2, ".", ",") . "</div>";
									$si_html_end .= "</div>";
								$si_html_end .= "</div>";

								// Shipping information.
								if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
									$f_animate_id[] = Array("id" => base64_encode("fluid-shipping-row-order"), "delay" => 250, "colour" => "#5EFF00");

									$si_html_end .= "<div name='fluid-shipping-row-order' id='fluid-shipping-row-order' style='text-align: right;'>"; // --> This div is used for animating.

									if($s_data['s_shipping_total'] == 0)
										$f_ship_html = "FREE";
									else
										$f_ship_html = HTML_CURRENCY . " " . number_format($s_data['s_shipping_total'], 2, ".", ",");

										$si_html_end .= "<div style='display: table-row;'>";
											$si_html_end .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Shipping:</div><div style='display: table-cell; text-align: right;'>" . $f_ship_html  . " </div>";
										$si_html_end .= "</div>";
									$si_html_end .= "</div>";
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
										$si_html_end .= "<div name='fluid-tax-row-order-" . $t_key . "' id='fluid-tax-row-order-" . $t_key . "' style='text-align: right;'>"; // --> This div is used for animating.
											$si_html_end .= "<div id='tax-" . $t_key . "' style='display: table-row; text-align:right;'><div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>" . $t_data['t_name'] . ":</div><div style='display: table-cell; text-align: right;'>" . HTML_CURRENCY . " " . number_format($tmp_total, 2, '.', ',') . "</div></div>";
										$si_html_end .= "</div>";
									}
								}

								$f_total_final_price_html = HTML_CURRENCY . " " . number_format($s_data['s_total'], 2, ".", ",");

								$f_animate_id[] = Array("id" => base64_encode("fluid-total-row-order"), "delay" => 250, "colour" => "#FFD600");
								$si_html_end .= "<div name='fluid-total-row-order' id='fluid-total-row-order' style='text-align: right;'>"; // --> This div is used for animating.
									$si_html_end .= "<div style='display: table-row; text-align: right;'>";
										$si_html_end .= "<div style='display: table-cell; text-align: right; padding-right: 10px; font-weight: 300;'>Total:</div><div id='fluid-total-cart-row' style='display: table-cell; text-align: right;'>" . $f_total_final_price_html . "</div>";
									$si_html_end .= "</div>";
								$si_html_end .= "</div>";

							$si_html_end .= "</div>"; //table


							$si_html_end .= "</div>";
						$si_html_end .= "</div>";
					$si_html_end .= "</div>";

				$si_html_end .= "</div>";

				$si_html_end .= "<div class='f-extra-invoice'>";

					$si_html_end .= "
						<div class='f-leos-info-div'>
							<div style='font-size: 18px; font-weight: 500; padding-bottom: 5px;'>Contact Us</div>
							<div><span class=\"icon-leos-logo-rotate\" style=\"font-size: 40px; color: red !important;\"></span></div>
							<div>Leo's Camera Supply</div>
							<div>1055 Granville Street</div>
							<div>Vancouver, British Columbia</div>
							<div>CANADA, V6Z1L4</div>
							<div>Phone #: 1-604-685-5331</div>
							<div>Email: sales@leoscamera.com</div>
						</div>
					";

				// --> First page of invoice. For store use.
				$si_html .= $si_html_start . $invoice_items . $si_html_end;

				// --> Second page of invoice. for customer use.
				$si_html_customer .= $si_html_start . $invoice_items_customer . $si_html_end;

				$si_i_print_html_store  = $si_html;

				$si_i_customer_html = "<div style='display: block; width: 100%; text-align: center; font-weight: 600px; padding-bottom: 5px;'>-------------------- Customer Copy --------------------</div>";

				$si_policy_html = "<div id='invoice-page-break' class='f-page-break'></div>";
					$si_policy_html .= $si_i_customer_html;
					$si_policy_html .= "<div class='f-refund-div' id='f-row-invoice-refund'>";
						$si_policy_html .= "<div style='font-size: 18px; font-weight: 500; padding-bottom: 5px;'>Return Policy</div>";
						$si_policy_html .= HTML_RETURN_POLICY;
					$si_policy_html .= "</div>";

				$si_policy_html .= "</div>";

				$si_html .= $si_policy_html;

				$si_html_header = "<div class='f-print-receipt' style='padding-top: 10px; padding-bottom: 10px; float: left;'><a class='btn btn-primary' onClick='document.getElementById(\"fluid-print-div\").innerHTML = Base64.decode(\"" . base64_encode($si_i_customer_html . $si_html_customer . $si_policy_html) . "\");' href=\"javascript:window.print();\"><span class='glyphicon glyphicon-print' aria-hidden='true'></span> Print Receipt</a></div>";
				// *****************************

				// --> Print Stuff
				$i_print_html = $si_i_print_html . $si_i_print_html_store . "<div id='invoice-page-break' class='f-page-break'></div>" . $si_i_customer_html . $si_html_customer . $si_policy_html .  "<div id='invoice-page-break' class='f-page-break'></div>" . $si_i_print_html . $i_html . "<div id='invoice-page-break' class='f-page-break'></div>" . $si_i_print_html . $s_html;
				$si_html = $si_html_header . $si_html;

				if($o_ship['data']['ship_type'] != IN_STORE_PICKUP) {
					$i = 1;
					$s_count = 1;
					while($i <= $s_box_counter) {
						if($s_count == 1)
							$i_print_html .= "<div id='invoice-page-break' class='f-page-break'></div>" . $si_i_print_html;

						if(isset($s_ship_card)) {
							if($s_count == 2) {
								//$i_print_html .= "<div id='invoice-page-break' class='f-page-break'></div>" . $si_i_print_html;
								$i_print_html .= "<div style='padding-top: 20px;'></div>" . $s_ship_card;
								$s_count = 1;
							}
							else {
								$i_print_html .= $s_ship_card;
								$s_count++;
							}
						}

						$i++;
					}
				}

				// Some of the items sold are rebate / claim items. --> For office use only, to claim the rebate credits.
				if($f_rebate_claim == TRUE) {
					$i_print_html .= "<div id='invoice-page-break' class='f-page-break'></div>";
					$i_print_html .= $si_i_print_html;
					$i_print_html .= "<div>Item list with rebate / claimed items marked.</div>";
					$i_print_html .= $html;
				}

				$i_html = "<div class= f-print-receipt' style='padding-top: 15px; padding-bottom: 0px; float: left;'><a class='btn btn-primary' onClick='document.getElementById(\"fluid-print-div\").innerHTML = Base64.decode(\"" . base64_encode($i_print_html) . "\");' href=\"javascript:window.print();\"><span class='glyphicon glyphicon-print' aria-hidden='true'></span> Print Order</a></div>" . $i_html;
			}
		}

		if($f_tab == "serial")
			$a_active = NULL;
		else
			$a_active = " active";

		if($f_tab == "serial")
			$s_active = " active";
		else
			$s_active = NULL;

		$modal = "<div class='modal-dialog f-dialog' id='editing-dialog' role='document'>
			<div id='modal-content-invoice' class='modal-content'>

				<div class='panel-default'>
				  <div id='invoice-panel-heading' class='panel-heading'><span class=\"icon-leos-logo-rotate\" style=\"font-size: 20px; color: red !important; padding-right: 4px; display: inline-block;\"></span> " . $html_header . "<div style='display: inline-block; float: right;'><i class=\"fa fa-arrows fluid-panel-drag\" style='margin-right: 10px;' aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"move\"'></i><i id='f-window-maximize' class=\"fa fa-window-maximize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_maximize();'></i><i id='f-window-minimize' style='display: none;' class=\"fa fa-window-minimize\" aria-hidden=\"true\" onmouseover='JavaScript:this.style.cursor=\"pointer\"' onClick='js_fluid_minimize();'></i></div></div>
				</div>

			  <div class='modal-body' style='padding-left: 0px; padding-bottom: 0px; padding-right:0px;'>
					<ul style='padding-left: 15px;' class='nav nav-tabs' id='ordertabs'>
						<li role='presentation' class='" . $a_active . "' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#orderinformation' data-target='#orderinformation' data-toggle='tab'><span class='glyphicon glyphicon-list-alt'></span> Order</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#orderinvoice' data-target='#orderinvoice' data-toggle='tab'><span class='glyphicon glyphicon-list'></span> Receipt</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#refunds' data-target='#refunds' data-toggle='tab'><span class='glyphicon glyphicon-edit'></span> Refunds</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#orderitems' data-target='#orderitems' data-toggle='tab'><span class='glyphicon glyphicon-th-list'></span> Items</a></li>
						<li role='presentation' class='" . $s_active . "' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a href='#orderitems' data-target='#orderserial' data-toggle='tab'><span class='glyphicon glyphicon-tasks'></span> Serial #</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#orderpacking' data-target='#orderpacking' data-toggle='tab'><span class='glyphicon glyphicon-modal-window'></span> Packing</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#transactiondetails' data-target='#transactiondetails' data-toggle='tab'><span class='glyphicon glyphicon-briefcase'></span> Transaction</a></li>
						<li role='presentation' onmouseover='JavaScript:this.style.cursor=\"pointer\"'><a class='f-li-a-padding' href='#shippingdetails' data-target='#shippingdetails' data-toggle='tab'><span class='fa fa-truck'></span> Shipping</a></li>
					</ul>

				<div id='order-innerhtml' class='panel panel-default'>
					<div id='orders-div' class='tab-content'>";

						$modal .= "<div id='orderinformation' class='tab-pane fade in" . $a_active . "'>
							<div id='orderinformation-div' style='margin-left:10px; margin-right: 10px;'>" . $i_html . "</div>
						</div>

						<div id='orderinvoice' class='tab-pane fade in'>
							<div id='orderinvoice-div' style='margin-right: 10px; margin-left:10px;'>" . $si_html . "</div>
						</div>

						<div id='refunds' class='tab-pane fade in'>
							<div id='refunds-div' style='margin-right: 10px; margin-left:10px;'>" . $f_rv . "</div>
						</div>
						<div id='orderitems' class='tab-pane fade in'>
							<div id='orderitems-div' style='margin-right: 10px; margin-left:10px;'>" . $html . "</div>
						</div>
						";

						$modal .= "<div id='orderserial' class='tab-pane fade in" . $s_active . "'>
							<div id='orderserial-div' style='margin-right: 10px; margin-left:10px;'>" . $serial_html . "</div>
						</div>

						<div id='orderpacking' class='tab-pane fade in'>
							<div id='orderpacking-div' style='margin-right: 10px; margin-left:10px;'>" . $s_html . "</div>
						</div>

						<div id='transactiondetails' class='tab-pane fade in'>
							<div id='transactiondetails-div' style='margin-right: 10px; margin-left:10px;'>" . $st_html . "</div>
						</div>

						<div id='shippingdetails' class='tab-pane fade in'>
							<div id='shippingdetails-div' style='margin-right: 10px; margin-left:10px;'>" . $sp_html . "</div>
						</div>
					</div>
				</div>
			  </div>

			  <div class='modal-footer' id='invoice-footer'>";
			  	
				if(isset($_SESSION['u_access_admin'])) {
					if($_SESSION['u_access_admin'] == 'all') {
					  $modal .= "<div id='button-left-cancel' style='float:left;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Close</button></div><div id='button-right-save' style='float:right;'><button type='button' class='btn btn-success' onClick='js_update_order(\"" . $s_id . "\");'><span class=\"glyphicon glyphicon-check\" aria-hidden=\"true\"></span> Save</button></div>";
				  	}
					else {
						$modal .= "<div id='button-left-cancel' style='float:right;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Close</button></div>";
					}
				}
				else {
					$modal .= "<div id='button-left-cancel' style='float:right;'><button type='button' class='btn btn-danger' data-dismiss='modal'><span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> Close</button></div>";
				}

			  $modal .= "</div>
			  </div>
			</div>
		  </div>";

		$execute_functions[]['function'] = "js_html_insert_element";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(Array("parent" => base64_encode("fluid-modal"), "innerHTML" => base64_encode($modal))));

		$execute_functions[]['function'] = "js_clear_fluid_refund_selection";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(""));

		$execute_functions[]['function'] = "js_modal_show";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode("#fluid-modal"));

		$execute_functions[]['function'] = "js_fluid_block_animate";
		end($execute_functions);
		$execute_functions[key($execute_functions)]['data'] = base64_encode(json_encode(base64_encode(json_encode($f_animate_id))));

		return json_encode(array("js_execute_array" => 1, "js_execute_functions" => base64_encode(json_encode($execute_functions)), "error" => 0, "error_message" => base64_encode("no error")));
	}
	catch (Exception $err) {
		return php_fluid_error($err);
	}
}

?>
